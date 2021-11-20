<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
    return;
}

/**
 * Class WC_Gateway_Ezdefi
 *
 * @extends WC_Payment_Gateway
 */
class WC_Gateway_Ezdefi extends WC_Payment_Gateway
{
    public $api_url;

    public $api_key;

    public $api;

    public $db;

	/**
	 * Constructs the class
	 */
    public function __construct()
    {
        $this->set_general_property();

        $this->init_form_fields();

        $this->init_settings();

        $this->set_settings_value();

        $this->api = new WC_Ezdefi_Api( $this->api_url, $this->api_key );

        $this->db = new WC_Ezdefi_Db();

        $this->init_hooks();
    }

    /**
     * Process admin options
     *
     * @return bool
     */
    public function process_admin_options() {
	    parent::process_admin_options();
	    $this->api->update_callback_url();
    }

	/**
	 * Set general property of class
	 */
    protected function set_general_property()
    {
	    $this->id = 'ezdefi';
	    $this->method_title = __( 'Pay with Cryptocurrencies', 'woocommerce-gateway-ezdefi' );
	    $this->method_description = __( 'Using BTC, ETH or any kinds of cryptocurrency handled by ezDeFi', 'woocommerce-gateway-ezdefi' );
	    $this->has_fields = true;
    }

	/**
	 * Set settings value
	 */
    protected function set_settings_value()
    {
	    $this->enabled = $this->get_option( 'enabled' );
	    $this->title = $this->get_option( 'title' );
	    $this->description = $this->get_option( 'description' );
	    $this->api_url = $this->get_option( 'api_url' );
	    $this->api_key = $this->get_option( 'api_key' );
    }

	/**
	 * Init hooks
	 */
    public function init_hooks()
    {
        global $woocommerce;

        if( is_object( $woocommerce ) && version_compare( $woocommerce->version, '3.7.0', '>=' ) ) {
	        add_action( 'woocommerce_before_thankyou', array(
		        $this, 'qrcode_section'
	        ) );
        } else {
	        add_filter( 'do_shortcode_tag', array(
                $this, 'prepend_woocommerce_checkout_shortcode'
            ), 10, 4 );
        }

	    add_action( 'wp_enqueue_scripts', array(
            $this, 'payment_scripts'
        ) );

        add_action( 'admin_enqueue_scripts', array(
            $this, 'admin_scripts'
        ) );

	    add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array(
            $this, 'process_admin_options'
        ) );
    }

	/**
	 * Register needed scripts for admin
	 */
    public function admin_scripts()
    {
        if ( 'woocommerce_page_wc-settings' !== get_current_screen()->id ) {
            return;
        }

        wp_register_script( 'wc_ezdefi_validate', plugins_url( 'assets/js/jquery.validate.min.js', WC_EZDEFI_MAIN_FILE ), array( 'jquery' ), WC_EZDEFI_VERSION, true );
        wp_register_style( 'wc_ezdefi_admin', plugins_url( 'assets/css/ezdefi-admin.css', WC_EZDEFI_MAIN_FILE ) );
        wp_register_script( 'wc_ezdefi_admin', plugins_url( 'assets/js/ezdefi-admin.js', WC_EZDEFI_MAIN_FILE ), array( 'jquery' ), WC_EZDEFI_VERSION, true );
    }

	/**
	 * Init plugin setting fields
	 */
    public function init_form_fields()
    {
        $this->form_fields = require dirname( __FILE__ ) . '/admin/ezdefi-settings.php';
    }

	/**
	 * Add needed scripts for admin
	 */
    public function generate_settings_html( $form_fields = array(), $echo = true )
    {
        wp_enqueue_script( 'wc_ezdefi_validate', plugins_url( 'assets/js/jquery.validate.min.js', WC_EZDEFI_MAIN_FILE ), array( 'jquery' ), WC_EZDEFI_VERSION, true );
        wp_enqueue_style( 'wc_ezdefi_admin', plugins_url( 'assets/css/ezdefi-admin.css', WC_EZDEFI_MAIN_FILE ) );
        wp_enqueue_script( 'wc_ezdefi_admin', plugins_url( 'assets/js/ezdefi-admin.js', WC_EZDEFI_MAIN_FILE ), array( 'jquery' ), WC_EZDEFI_VERSION, true );
        wp_localize_script( 'wc_ezdefi_admin', 'wc_ezdefi_data',
            array(
                'ajax_url' => admin_url( 'admin-ajax.php' )
            )
        );

        return parent::generate_settings_html($form_fields, $echo);
    }

    /**
     * Validate function for title field
     *
     * @param $key
     * @param $value
     *
     * @return string
     */
    public function validate_title_field( $key, $value )
    {
        if( empty( $value ) ) {
            $value = 'Pay with cryptocurrencies';
        }

        return $this->validate_text_field( $key, $value );
    }

    /**
     * Validate function for description field
     *
     * @param $key
     * @param $value
     *
     * @return string
     */
    public function validate_description_field( $key, $value )
    {
        if( empty( $value ) ) {
            $value = 'Using BTC, ETH or any kinds of cryptocurrency. Handled by ezDeFi';
        }

        return $this->validate_text_field( $key, $value );
    }

	/**
	 * Add needed scripts for payment process
	 */
    public function payment_scripts()
    {
	    if ( 'no' === $this->enabled ) {
		    return;
	    }

	    wp_register_style( 'wc_ezdefi_checkout', plugins_url( 'assets/css/ezdefi-checkout.css', WC_EZDEFI_MAIN_FILE ), array(), WC_EZDEFI_VERSION );
	    wp_enqueue_style( 'wc_ezdefi_checkout' );
	    wp_register_script( 'wc_ezdefi_checkout', plugins_url( 'assets/js/ezdefi-checkout.js', WC_EZDEFI_MAIN_FILE ), array( 'jquery' ), WC_EZDEFI_VERSION );
	    wp_enqueue_script( 'wc_ezdefi_checkout' );

	    wp_register_style( 'wc_ezdefi_qrcode', plugins_url( 'assets/css/ezdefi-qrcode.css', WC_EZDEFI_MAIN_FILE ), array(), WC_EZDEFI_VERSION );
	    wp_register_script( 'wc_ezdefi_qrcode', plugins_url( 'assets/js/ezdefi-qrcode.js', WC_EZDEFI_MAIN_FILE ), array( 'jquery', 'jquery-ui-tabs', 'clipboard' ), WC_EZDEFI_VERSION );
	    wp_localize_script( 'wc_ezdefi_qrcode', 'wc_ezdefi_data',
		    array(
			    'ajax_url' => admin_url( 'admin-ajax.php' ),
                'checkout_url' => wc_get_checkout_url(),
                'order_status' => $this->db->get_order_status()
		    )
	    );
    }

    /**
     * Icon for payment method on checkout page
     *
     * @return mixed|string|void
     */
    public function get_icon() {
        $coins = $this->get_website_coins();

        if( is_null( $coins ) ) {
            return '';
        }

        $icon_html = '<div id="wc-ezdefi-icon">';

	    foreach( $coins as $i => $c ) {
	        if($i < 3) {
		        $icon_html .= '<img src="' . $c['token']['logo'] . '" />';
	        }
        }

        $icon_html .= '</div>';

	    return apply_filters( 'woocommerce_gateway_icon', $icon_html, $this->id );
    }

	/**
	 * Add payment field on checkout page
	 */
	public function payment_fields() {
        $description = $this->get_description();

        ob_start(); ?>
        <div id="wc-wzdefi-checkout">
            <?php
                $cart = WC()->cart;
                $total = $cart->get_totals()['total'];
                $currency = get_woocommerce_currency();

                echo wpautop( wp_kses_post( $description ) );
                echo $this->currency_select_html( $total, $currency );
            ?>
            <input type="hidden" name="wc_ezdefi_coin" id="wc-ezdefi-coin" value="">
        </div>
	    <?php echo ob_get_clean();
    }

	/**
     * Validate field before process payment
     *
	 * @return bool
	 */
	public function validate_fields() {
		if( ! isset( $_POST['wc_ezdefi_coin'] ) || empty( $_POST['wc_ezdefi_coin'] ) ) {
		    wc_add_notice( '<strong>' . __( 'Please select currency', 'woocommerce-gateway-ezdefi' ) . '</strong>', 'error' );
		    return false;
        }

		return true;
    }

	/**
     * Handle creating payment
     *
	 * @param int $order_id
	 *
	 * @return array
	 */
    public function process_payment( $order_id ) {
        $order = wc_get_order( $order_id );

	    $order->update_status('on-hold', __( 'Awaiting ezdefi payment', 'woocommerce-gateway-ezdefi' ) );

	    $coin_id = sanitize_text_field( $_POST['wc_ezdefi_coin'] );

	    $website_coins = $this->get_website_coins();

	    $coin_data = null;

	    foreach ( $website_coins as $key => $coin ) {
            if ( $coin['_id'] == $coin_id ) {
                $coin_data = $website_coins[$key];
            }
        }

	    if( is_null( $coin_data ) ) {
		    wc_add_notice( 'Fail. Please try again or contact shop owner', 'error' );

            $order->update_status( 'failed' );

            return array(
                'result'   => 'fail',
                'redirect' => '',
            );
        }

	    $order->add_meta_data( 'ezdefi_coin', $coin_id );
	    $order->save_meta_data();

	    return array(
		    'result' => 'success',
		    'redirect' => $this->get_return_url( $order )
	    );
    }

	/**
     * Preprend QRcode section to woocommerce_checkout_shortcode (older version)
     *
	 * @param $output
	 * @param $tag
	 *
	 * @return string
	 */
    public function prepend_woocommerce_checkout_shortcode( $output, $tag )
    {
        global $wp;

	    if ( $tag != 'woocommerce_checkout' ) {
		    return $output;
	    }

	    $order_id = $wp->query_vars['order-received'];

	    if( ! $order_id ) {
	        return $output;
        }

	    $prepend = $this->qrcode_section( $order_id );

	    $output = $prepend . $output;

	    return $output;
    }

	/**
     * Add QRcode section to thankyou page
     *
	 * @param $order_id
	 */
    public function qrcode_section( $order_id )
    {
        $order = wc_get_order( $order_id );

	    if( ( $order->get_payment_method() != $this->id ) || ( $order->get_status() === $this->db->get_order_status() ) || ( $order->get_status() === 'failed' ) ) {
		    return;
	    }

	    $coin_id = $order->get_meta( 'ezdefi_coin' );

	    $website_config = $this->api->get_website_config();

	    if ( is_null( $website_config ) ) {
	        return;
        }

        $website_coins = $website_config['coins'];

        $selected_currency = null;

        foreach ( $website_coins as $key => $coin ) {
            if ( $coin['_id'] == $coin_id ) {
                $selected_currency = $website_coins[$key];
            }
        }

	    if( is_null( $selected_currency ) ) {
	        return;
        }

	    $payment_data = array(
		    'uoid' => $order_id,
		    'total' => $order->get_total(),
		    'ezdefi_payment' => ( $order->get_meta( 'ezdefi_payment' ) ) ? $order->get_meta( 'ezdefi_payment' ) : ''
	    );

	    wp_enqueue_style( 'wc_ezdefi_qrcode' );
	    wp_enqueue_script( 'wc_ezdefi_qrcode' );

        ob_start();?>
            <div id="wc_ezdefi_qrcode">
                <script type="application/json" id="payment-data"><?php echo json_encode( $payment_data ); ?></script>
                <?php echo $this->currency_select_html( $order->get_total(), $order->get_currency(), $selected_currency ); ?>
                <div class="wc-ezdefi-loader"></div>
                <div class="ezdefi-payment-tabs" style="display: none">
                    <ul>
                        <?php
                            if( $website_config['website']['payAnyWallet'] == true ) {
                                echo '<li>';
                                echo '<a href="#amount_id" id="tab-amount_id"><span class="large-screen">' . __( 'Pay with any crypto wallet', 'woocommerce-gateway-ezdefi' ) . '</span><span class="small-screen">' . __( 'Any crypto wallet', 'woocommerce-gateway-ezdefi' ) . '</span></a>';
                                echo '</li>';
                            }

                            if( $website_config['website']['payEzdefiWallet'] == true ) {
                                echo '<li>';
                                echo '<a href="#ezdefi_wallet" id="tab-ezdefi_wallet" style="background-image: url('.plugins_url( 'assets/images/ezdefi-icon.png', WC_EZDEFI_MAIN_FILE ).')"><span class="large-screen"> ' . __( 'Pay with ezDeFi wallet', 'woocommerce-gateway-ezdefi' ) . '</span><span class="small-screen" style="background-image: url('.plugins_url( 'assets/images/ezdefi-icon.png', WC_EZDEFI_MAIN_FILE ).')"> ' . __( 'ezDeFi wallet', 'woocommerce-gateway-ezdefi' ) . '</span></a>';
                                echo '</li>';
                            }
                        ?>
                    </ul>
                    <?php
                        if( $website_config['website']['payAnyWallet'] == true ) {
                            echo '<div id="amount_id" class="ezdefi-payment-panel"></div>';
                        }

                        if( $website_config['website']['payEzdefiWallet'] == true ) {
                            echo '<div id="ezdefi_wallet" class="ezdefi-payment-panel"></div>';
                        }
                    ?>
                </div>
            </div>
        <?php echo ob_get_clean();
    }

    /**
     * Generate currency select HTML
     *
     * @param $total
     * @param $currency
     * @param  array  $selected_currency
     *
     * @return false|string
     */
    public function currency_select_html( $total, $currency, $selected_currency = array() )
    {
        $coins = $this->get_website_coins( true );
        $to = implode(',', array_map( function ( $coin ) {
            return $coin['token']['symbol'];
        }, $coins ) );
        $exchanges = $this->api->get_token_exchanges(
            $total,
            $currency,
            $to
        );
        ob_start(); ?>
        <div class="currency-select">
		    <?php foreach( $coins as $c ) : ?>
                <div class="currency-item__wrap">
                    <div class="currency-item <?php echo ( ! empty( $selected_currency['_id'] ) && $c['_id'] === $selected_currency['_id'] ) ? 'selected' : ''; ?>" data-id="<?php echo $c['_id']; ?>" data-symbol="<?php echo $c['token']['symbol'] ;?>">
                        <div class="item__logo">
                            <img src="<?php echo $c['token']['logo']; ?>" alt="">
                            <?php if( ! empty( $c['token']['desc'] ) ) : ?>
                                <div class="item__desc">
                                    <?php echo $c['token']['desc']; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="item__text">
                            <div class="item__price">
							    <?php
							    $discount = ( intval( $c['discount']) > 0 ) ? $c['discount'] : 0;
							    $index = array_search( $c['token']['symbol'], array_column( $exchanges, 'token' ) );
							    $amount = $exchanges[$index]['amount'];
							    $amount = $amount - ( $amount * ( $discount / 100 ) );
							    echo number_format( $amount, 8 );
							    ?>
                            </div>
                            <div class="item__info">
                                <div class="item__symbol">
								    <?php echo $c['token']['symbol']; ?>
                                </div>
                                <div class="item__discount">
                                    - <?php echo $discount; ?>%
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
		    <?php endforeach; ?>
        </div>
        <?php return ob_get_clean();
    }

    /**
     * Get website coins
     *
     * @param  bool  $is_coin_select
     *
     * @return mixed|null
     */
    protected function get_website_coins( $is_checkout_page = false )
    {
        if( $is_checkout_page && get_transient( 'ezdefi_website_coins' ) ) {
            return get_transient( 'ezdefi_website_coins' );
        }

        $website_config = $this->api->get_website_config();

        if( is_null( $website_config ) ) {
            return null;
        }

        set_transient( 'ezdefi_website_coins', $website_config['coins'] );

        return $website_config['coins'];
    }
}