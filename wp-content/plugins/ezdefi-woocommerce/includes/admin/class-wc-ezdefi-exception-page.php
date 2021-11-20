<?php

defined( 'ABSPATH' ) or exit;

class WC_Ezdefi_Exception_Page
{
	public function __construct()
	{
		add_action( 'admin_menu', array( $this, 'add_ezdefi_exception_page_link' ), 10 );
		add_action( 'admin_enqueue_scripts', array( $this, 'register_scripts' ) );
	}

	public function register_scripts()
	{
		wp_register_style( 'wc_ezdefi_select2', plugins_url( 'assets/css/select2.min.css', WC_EZDEFI_MAIN_FILE ) );
		wp_register_script( 'wc_ezdefi_select2', plugins_url( 'assets/js/select2.min.js', WC_EZDEFI_MAIN_FILE ), array( 'jquery' ), WC_EZDEFI_VERSION, true );
		wp_register_style( 'wc_ezdefi_assign', plugins_url( 'assets/css/ezdefi-assign.css', WC_EZDEFI_MAIN_FILE ) );
		wp_register_script( 'wc_ezdefi_assign', plugins_url( 'assets/js/ezdefi-assign.js', WC_EZDEFI_MAIN_FILE ), array( 'jquery' ), WC_EZDEFI_VERSION, true );
	}

	public function add_ezdefi_exception_page_link()
	{
		add_submenu_page( 'woocommerce', __( 'ezDeFi Exception Management', 'woocommerce-gateway-ezdefi' ), __( 'ezDeFi Exception', 'woocommerce-gateway-ezdefi' ), 'manage_woocommerce', 'wc-ezdefi-exception', array( $this, 'ezdefi_exception_page' ) );

	}

	public function ezdefi_exception_page()
	{
		wp_enqueue_style( 'wc_ezdefi_select2' );
		wp_enqueue_script( 'wc_ezdefi_select2' );
		wp_enqueue_style( 'wc_ezdefi_assign' );
		wp_enqueue_script( 'wc_ezdefi_assign' );
		wp_localize_script( 'wc_ezdefi_assign', 'wc_ezdefi_data',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
			)
		);

		include_once dirname( __FILE__ ) . '/views/html-admin-page-ezdefi-exception.php';
	}
}

new WC_Ezdefi_Exception_Page();