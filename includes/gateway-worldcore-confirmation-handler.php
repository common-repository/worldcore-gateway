<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles confirmations from Worldcore
 */
class WC_Gateway_Worldcore_Confirmation_Handler {

	public function __construct($account, $api_key, $api_password) {

		$this->account = $account;		
                $this->api_key = $api_key;		
                $this->api_password = $api_password;
                
	}

	public function check_response() {
            
            $headers=apache_request_headers();
            $json_body=file_get_contents('php://input');

            $hash_check=strtoupper(hash('sha256', $json_body.$this->api_password));

            if($headers['WSignature']==$hash_check){ 
                
                $decoded_response=json_decode($json_body, true);
                
                $order = wc_get_order( $decoded_response['invoiceId'] );
                
      		if ( $order->has_status( 'completed' ) ) {
			WC_Gateway_Worldcore::log( 'Aborting, Order #' . $order->id . ' is already complete.' );
			exit;
		}
                
		$this->validate_amount( $order, $decoded_response['amount'] );
                                WC_Gateway_Worldcore::log(print_r($decoded_response, true));
		$this->validate_account( $order, $decoded_response['account'] );
                                WC_Gateway_Worldcore::log(print_r($decoded_response, true));

		if ($decoded_response['status']=='Completed' || $decoded_response['status']=='Paid') {
                    WC_Gateway_Worldcore::log('3');
                    $order->add_order_note( __('WC payment completed', 'woocommerce') );
                    $order->payment_complete( $decoded_response['invoiceId'] );
                    WC_Gateway_Worldcore::log('4');
		} else {
                    WC_Gateway_Worldcore::log('5');
                    $this->payment_on_hold($order, sprintf( __('Payment pending', 'woocommerce')));
		}    

            }else{

                WC_Gateway_Worldcore::log('Hash mismatch: '.$headers['WSignature'].' vs. '.$hash_check);

            }

	}


	protected function validate_amount( $order, $amount ) {
            
		if ( number_format( ($order->get_total() - round( $order->get_total_shipping() + $order->get_shipping_tax())), 2 ) !=  $amount ) {
                    
			WC_Gateway_Worldcore::log('Payment error: Amounts do not match');

			// Put this order on-hold for manual checking.
			$order->update_status('on-hold', __( 'Validation error: Worldcore amounts do not match.', 'woocommerce'));
			exit;
                        
		}
                
	}
        

	protected function validate_account( $order, $account ) {
            
		if ($account!=$this->account) {
                    
			WC_Gateway_Worldcore::log("Accounts does not match");

			// Put this order on-hold for manual checking.
			$order->update_status('on-hold', __( 'Accounts does not match', 'woocommerce'));
			exit;
                        
		}
                
	}


}
