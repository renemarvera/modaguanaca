<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

return apply_filters(
    'wc_ezdefi_settings',
    array(
        'enabled' => array(
            'title' => __( 'Enable/Disable', 'woocommerce-gateway-ezdefi' ),
            'label' => __( 'Enable ezDeFi', 'woocommerce-gateway-ezdefi' ),
            'type' => 'checkbox',
            'description' => '',
            'default' => 'no',
        ),
        'title' => array(
	        'title' => __( 'Title', 'woocommerce-gateway-ezdefi' ),
	        'type' => 'text',
	        'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce-gateway-ezdefi' ),
	        'default' => __( 'Pay with Cryptocurrencies', 'woocommerce-gateway-ezdefi' ),
	        'desc_tip' => true,
        ),
        'description' => array(
	        'title' => __( 'Description', 'woocommerce-gateway-ezdefi' ),
	        'type' => 'text',
	        'description' => __( 'This controls the description which the user sees during checkout.', 'woocommerce-gateway-ezdefi' ),
	        'default' => __( 'Using BTC, ETH or any kinds of cryptocurrency. Handled by ezDeFi', 'woocommerce-gateway-ezdefi' ),
	        'desc_tip' => true,
        ),
        'api_url' => array(
            'title' => __( 'API Url', 'woocommerce-gateway-ezdefi' ),
            'type' => 'text',
            'description' => __( 'Description' ),
            'default' => 'https://merchant-api.ezdefi.com/api/',
            'placeholder' => 'https://merchant-api.ezdefi.com/api/',
            'desc_tip' => true,
        ),
        'api_key' => array(
            'title' => __( 'API Key', 'woocommerce-gateway-ezdefi' ),
            'type' => 'text',
            'description' => sprintf( __( '<a target="_blank" href="%s">Register to get API Key</a>', 'woocommerce-gateway-ezdefi' ), 'https://merchant.ezdefi.com/register?utm_source=woocommerce-download' ),
        ),
        'public_key' => array(
            'title' => __( 'Site ID', 'woocommerce-gateway-ezdefi' ),
            'type' => 'text'
        ),
        'order_status' => array(
            'title' => __( 'Order status', 'woocommerce-gateway-ezdefi' ),
            'type' => 'select',
            'options' => wc_get_order_statuses(),
            'default' => 'wc-processing',
            'description' => __( 'Order status when payment received', 'woocommerce-gateway-ezdefi' )
        )
    )
);