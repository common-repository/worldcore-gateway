<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings for Worldcore Gateway.
 */
return array(
	'enabled' => array(
		'title'   => __( 'Enable/Disable', 'woocommerce' ),
		'type'    => 'checkbox',
		'label'   => __( 'Enable Worldcore payment method', 'woocommerce' ),
		'default' => 'yes'
	),
	'title' => array(
		'title'       => __( 'Title', 'woocommerce' ),
		'type'        => 'text',
		'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
		'default'     => __( 'Worldcore', 'woocommerce' ),
		'desc_tip'    => true,
	),
	'account' => array(
		'title'       => __( 'Worldcore Account', 'woocommerce' ),
		'type'        => 'text',
		'desc_tip'    => true,
	),
	'debug' => array(
		'title'       => __( 'Debug Log', 'woocommerce' ),
		'type'        => 'checkbox',
		'label'       => __( 'Enable logging', 'woocommerce' ),
		'default'     => 'no',
		'description' => sprintf( __( 'Log Worldcore events, such as payment confirmations, inside <code>%s</code>', 'woocommerce' ), wc_get_log_file_path( 'worldcore' ) )
	),
	'api_key' => array(
		'title'       => __( 'API Key', 'woocommerce' ),
		'type'        => 'text',
		'description' => __( 'Get your API credentials from Worldcore.', 'woocommerce' ),
		'default'     => '',
		'desc_tip'    => true,
	),
	'api_password' => array(
		'title'       => __( 'API Password', 'woocommerce' ),
		'type'        => 'text',
		'description' => __( 'Get your API credentials from Worldcore.', 'woocommerce' ),
		'default'     => '',
		'desc_tip'    => true,
	),
);
