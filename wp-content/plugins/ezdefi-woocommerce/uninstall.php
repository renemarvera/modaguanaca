<?php

defined( 'ABSPATH' ) or exit;

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

$table_name = $wpdb->prefix . 'woocommerce_ezdefi_exception';
$wpdb->query( "DROP TABLE IF EXISTS $table_name" );
$wpdb->query( "DROP EVENT IF EXISTS `wc_ezdefi_clear_exception_table`" );

delete_option( 'woocommerce_ezdefi_settings' );