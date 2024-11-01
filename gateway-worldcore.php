<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * WC_Gateway_Worldcore Class.
 */
class WC_Gateway_Worldcore extends WC_Payment_Gateway {

	/** @var bool Whether or not logging is enabled */
	public static $log_enabled = false;

	/** @var WC_Logger Logger instance */
	public static $log = false;
        
        public $confirmation_handler;

	/**
	 * Constructor for the gateway.
	 */
	public function __construct() {
		$this->id                 = 'worldcore';
		$this->has_fields         = false;
		$this->order_button_text  = __( 'Proceed to Worldcore', 'woocommerce' );
		$this->method_title       = __( 'Worldcore', 'woocommerce' );
		$this->supports           = array(
			'products'
		);

		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();

		// Define user set variables.
		$this->title          = $this->get_option( 'title' );
		$this->debug          = 'yes' === $this->get_option( 'debug', 'no' );
		$this->account          = $this->get_option( 'account', $this->account );
		$this->api_key = $this->get_option('api_key');
		$this->api_password = $this->get_option('api_password');
                
		self::$log_enabled    = $this->debug;

		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

                include_once( 'includes/gateway-worldcore-confirmation-handler.php' );
		$this->confirmation_handler=new WC_Gateway_Worldcore_Confirmation_Handler($this->account, $this->api_key, $this->api_password );

	}

	/**
	 * Logging method.
	 * @param string $message
	 */
	public static function log( $message ) {
		if ( self::$log_enabled ) {
			if ( empty( self::$log ) ) {
				self::$log = new WC_Logger();
			}
			self::$log->add( 'worldcore', $message );
		}
	}

	/**
	 * Get gateway icon.
	 * @return string
	 */
	public function get_icon() {

		$icon_html='<img src="' . WC_HTTPS::force_https_url(content_url()) . '/plugins/worldcore-gateway/assets/images/worldcore.png' . '" alt="' . esc_attr__( 'Worldcore', 'woocommerce' ) . '" />';

		return apply_filters( 'woocommerce_gateway_icon', $icon_html, $this->id );
	}




	/**
	 * Initialise Gateway Settings Form Fields.
	 */
	public function init_form_fields() {
		$this->form_fields = include( 'includes/settings-worldcore.php' );
	}

	/**
	 * Process the payment and return the result.
	 * @param  int $order_id
	 * @return array
	 */
	public function process_payment( $order_id ) {
            
		$order          = wc_get_order( $order_id );
                
                $post_str=json_encode(array(
                        'account' => $this->account,
                        'amount' => number_format( ($order->get_total() - round( $order->get_total_shipping() + $order->get_shipping_tax())), 2 ),
                        'invoiceId' => $order_id
                ));
                
                
                $hash_in=strtoupper(hash('sha256', $post_str.$this->api_password));
                $auth_header='Authorization: wauth key='.$this->api_key.', hash='.$hash_in;

                $curl = curl_init();
                curl_setopt($curl, CURLOPT_URL, 'https://api.worldcore.eu/v1/merchant');
                curl_setopt($curl, CURLOPT_HEADER, true);
                curl_setopt($curl, CURLOPT_POST, 1);
                curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8', $auth_header));
                curl_setopt($curl, CURLOPT_POSTFIELDS, $post_str);
                curl_setopt($curl, CURLOPT_TIMEOUT, 10);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                
                $curl_response = curl_exec($curl);

                if($curl_response==false){

                        $error_msg = curl_error($curl);
                        WC_Gateway_Worldcore::log('CURL error: ' . $error_msg);

                }else{

                        list($response_headers, $json_response)=explode("\r\n\r\n", $curl_response, 2);

                        preg_match("/^WSignature: ([A-Z0-9]{64})\r$/m", $response_headers, $hash_outputed);

                        $hash_check=strtoupper(hash('sha256', $json_response.$this->api_password));

                        if($hash_outputed[1]!=$hash_check){
                                WC_Gateway_Worldcore::log("Hash not match!");
                        }else{

                                $decoded_response=json_decode($json_response, true);

                                if(isset($decoded_response['error'])){

                                        WC_Gateway_Worldcore::log('Error occurred: '.print_r($decoded_response['error'], true));

                                }else{

                                    return array(
                                            'result'   => 'success',
                                            'redirect' => $decoded_response['data']['url']
                                    );
                                        
                                }

                        }

                }

                curl_close($curl);


	}

	/**
	 * Can the order be refunded via Worldcore?
	 * @param  WC_Order $order
	 * @return bool
	 */
	public function can_refund_order( $order ) {
		return false;
	}

}