<?php

class WC_Ezdefi_Admin_Notices
{
    protected $db;

    protected $notices = array();

    public function __construct()
    {
        $this->db = new WC_Ezdefi_Db();

        add_action( 'admin_notices', array( $this, 'admin_notices' ) );
    }

    public function admin_notices()
    {
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            return;
        }

        $options = $this->db->get_options();

        $this->check_environment( $options );

        $this->render_notices();
    }

    protected function check_environment( $options )
    {
        if( empty( $options ) || ! is_array( $options ) ) {
            return;
        }

        if ( ! isset( $options['enabled'] ) && 'yes' != $options['enabled'] ) {
            return;
        }

        $this->check_gateway_config( $options );
    }

    protected function check_gateway_config( $options )
    {
        $setting_link = $this->get_setting_link();

        if( empty( $options['api_url'] ) || empty( $options['api_key'] ) || empty( $options['public_key'] ) ) {
            $this->notices[] = sprintf( __( 'Ezdefi is almost ready. <a href="%s">Click here</a> to finish setting up this integration.', 'woocommerce-gateway-ezdefi' ), $setting_link );
        }
    }

    protected function render_notices()
    {
        foreach( $this->notices as $notice ) {
            echo '<div class="notice notice-error is-dismissible"><p>' . wp_kses( $notice, array( 'a' => array( 'href' => array(), 'target' => array() ) ) )  . '</p></div>';
        }
    }

    protected function get_setting_link() {
        return admin_url( 'admin.php?page=wc-settings&tab=checkout&section=ezdefi' );
    }
}

new WC_Ezdefi_Admin_Notices();