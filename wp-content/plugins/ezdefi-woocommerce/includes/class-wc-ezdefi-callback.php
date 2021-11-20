<?php

defined( 'ABSPATH' ) or exit;

class WC_Ezdefi_Callback
{
    const EXPLORER_URL = 'https://explorer.nexty.io/tx/';

    protected $api;

    protected $db;

    /**
     * WC_Ezdefi_Callback constructor.
     */
    public function __construct()
    {
        add_action( 'woocommerce_api_ezdefi', array(
            $this, 'handle'
        ) );

        $this->api = new WC_Ezdefi_Api();
        $this->db = new WC_Ezdefi_Db();
    }

    /**
     * Handle callback from gateway
     */
    public function handle()
    {
        if( $this->is_payment_callback( $_GET ) ) {
            $this->handle_payment_callback( $_GET );
        }

        if( $this->is_transaction_callback( $_GET ) ) {
            $this->handle_transaction_callback( $_GET );
        }
    }

    /**
     * Handle callback when payment is DONE or EXPIRED_DONE
     *
     * @param array $data
     */
    protected function handle_payment_callback( $data )
    {
        global $woocommerce;

        $data = array_map( 'sanitize_key', $data );

        $order_id = ezdefi_sanitize_uoid( $data['uoid'] );

        if( ! $order = wc_get_order( $order_id ) ) {
            wp_send_json_error();
        }

        $payment = $this->api->get_ezdefi_payment( $data['paymentid'] );

        if( ! $this->is_valid_payment( $payment ) ) {
            wp_send_json_error();
        }

        $status = $payment['status'];
        $uoid = (int) ezdefi_sanitize_uoid( $payment['uoid'] );
        $payment_method = ezdefi_is_pay_any_wallet( $payment ) ? 'amount_id' : 'ezdefi_wallet';
        $explorer_url = $payment['explorer']['tx'] . $payment['transactionHash'];

        if( $status != 'DONE' && $status != 'EXPIRED_DONE' ) {
            wp_send_json_error();
        }

        if( $status === 'DONE' ) {
            $order->update_status( $this->db->get_order_status() );
            $order->add_order_note( "Ezdefi Explorer URL: $explorer_url" );
            $order->save_meta_data();
            $woocommerce->cart->empty_cart();

            if( $payment_method === 'ezdefi_wallet' ) {
                $this->db->delete_exceptions( array(
                    'order_id' => $uoid
                ) );

                wp_send_json_success();
            }
        }

        $value = ( $payment_method === 'amount_id' ) ? $payment['originValue'] : ( $payment['value'] / pow( 10, $payment['decimal'] ) );

        $this->db->update_exceptions(
            array(
                'order_id' => $uoid,
                'payment_method' => $payment_method,
            ),
            array(
                'amount_id' => ezdefi_sanitize_float_value( $value ),
                'currency' => $payment['token']['symbol'],
                'status' => strtolower( $status ),
                'explorer_url' => $explorer_url
            ),
            1
        );

        $this->db->delete_exceptions( array(
            'order_id' => $uoid,
            'explorer_url' => null,
        ) );

        wp_send_json_success();
    }

    /**
     * Handle callback when there's unknown transaction
     *
     * @param $data
     */
    protected function handle_transaction_callback( $data )
    {
        $value = sanitize_key( $data['value'] );
        $decimal = sanitize_key( $data['decimal'] );
        $value = $value / pow( 10, $decimal );
        $value = ezdefi_sanitize_float_value( $value );
        $explorerUrl = sanitize_text_field( $data['explorerUrl'] );
        $currency = sanitize_text_field( $data['currency'] );
        $id = sanitize_key( $data['id'] );

        $transaction = $this->api->get_transaction( $id );

        if( is_null( $transaction ) || $transaction['status'] != 'ACCEPTED' ) {
            wp_send_json_error();
        }

        $exception_data = array(
            'amount_id' => str_replace( ',', '', $value ),
            'currency' => $currency,
            'explorer_url' => $explorerUrl,
        );

        $this->db->add_exception( $exception_data );

        wp_send_json_success();
    }

    /**
     * Check whether callback is for payment or not
     *
     * @param $data
     *
     * @return bool
     */
    protected function is_payment_callback( $data )
    {
        if( ! is_array( $data ) ) {
            return false;
        }

        return ( isset( $data['uoid'] ) && isset( $data['paymentid'] ) );
    }

    /**
     * Check whether callback is for unknown transaction or not
     *
     * @param $data
     *
     * @return bool
     */
    protected function is_transaction_callback( $data )
    {
        if( ! is_array( $data ) ) {
            return false;
        }

        return (
            isset( $data['value'] ) && isset( $data['explorerUrl'] ) &&
            isset( $data['currency'] ) && isset( $data['id'] ) &&
            isset( $data['decimal'] )
        );
    }

    /**
     * Check if payment is valid or not.
     *
     * @param $payment
     *
     * @return bool
     */
    protected function is_valid_payment( $payment )
    {
        if( is_null( $payment ) ) {
            return false;
        }

        $status = $payment['status'];

        if( $status === 'PENDING' || $status === 'EXPIRED' ) {
            return false;
        }

        return true;
    }
}

new WC_Ezdefi_Callback();