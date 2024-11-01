<?php
/*
Plugin Name: Worldcore
Plugin URI: http://worldcore.eu
Version: 1.0
*/

add_action('plugins_loaded', 'woocommerce_worldcore_init', 0);

function woocommerce_worldcore_init() {

	if ( ! class_exists( 'WC_Payment_Gateway' ) ) { return; }
	
	$plugin_dir = plugin_dir_path(__FILE__);
	
	require_once $plugin_dir . 'gateway-worldcore.php';

	/**
 	* Add WorldCore payment gateway to WooCommerce
 	**/
	function add_worldcore_gateway($methods) {
		$methods[] = 'WC_Gateway_Worldcore';
		return $methods;
	}

	/**
 	* Handle WorldCore automatic payment confirmations
 	**/
	function wc_confirmation_proxy_function($query) {  
		
		if ($query->is_main_query()){

			if($query->query['pagename']=='wc_confirmation'){

				include('status_url.php');

			}

		}

	}

	add_filter('woocommerce_payment_gateways', 'add_worldcore_gateway' );
	add_action('pre_get_posts', 'wc_confirmation_proxy_function');

} 


/**
* Register WorldCore payment confirmations handling
**/
function wc_payment_confirmation(){

	function wc_confirmation(){

		return;

	}
	
	add_rewrite_endpoint('wc_confirmation', EP_PERMALINK);
	flush_rewrite_rules();

}

register_activation_hook(__FILE__, 'wc_payment_confirmation');
