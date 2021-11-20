<?php
/**
 * Plugin Name: ezDeFi - Bitcoin, Ethereum and Cryptocurrencies Payment Gateway for WooCommerce
 * Plugin URI: https://ezdefi.io/
 * Description: Accept Bitcoin, Ethereum and Cryptocurrencies on your Woocommerce store with ezDeFi
 * Version: 2.0.0
 * Author: ezDeFi
 * Author URI: https://ezdefi.io/
 * License: GPLv2 or later
 * Text Domain: woocommerce-gateway-ezdefi
 * Domain Path: /languages
 */

defined( 'ABSPATH' ) or exit;

class WC_Ezdefi {

	/**
	 * @var Single instance of WC_Ezdefi class
	 */
	protected static $instance;

	protected $version = '2.0.0';

	/**
	 * Constructs the class
	 */
	protected function __construct()
	{
		$this->includes();

		$this->define_constants();

		add_filter( 'woocommerce_payment_gateways', array( $this, 'add_gateways' ) );

        add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array(
            $this, 'plugin_action_links'
        ) );

        add_action( 'admin_init', array(
            $this, 'update_database_notice'
        ) );

        add_action( 'admin_post_woocommerce_gateway_ezdefi_update_database', array(
            $this, 'update_database'
        ) );

        add_filter( 'cron_schedules', array(
            $this, 'add_cron_schedule'
        ) );

        add_action( 'woocommerce_gateway_ezdefi_weekly_event', array(
            $this, 'clear_database'
        ) );
	}

    /**
     * Notice update database after update plugin
     */
    public function update_database_notice()
    {
        $current_version = get_option( 'woocommerce_gateway_ezdefi_version' );

        if( ! empty( $current_version ) && version_compare( $current_version, $this->version, '>=' ) ) {
            return;
        }

        add_action( 'admin_notices', function() {
            ob_start(); ?>
            <div class="error is-dismissible">
                <p>
                    <strong>
                        <?php echo __( 'Woocommerce Gateway Ezdefi has been updated. You have to update your database to newest version.' ); ?>
                    </strong>
                </p>
                <p>
                    <?php echo __( 'The update process may take a little while, so please be patient.', 'woocommerce-gateway-ezdefi' ); ?>
                </p>
                <form action="<?php echo admin_url( 'admin-post.php' ); ?>" method="POST">
                    <?php wp_nonce_field( 'woocommerce_gateway_ezdefi_update_database', 'woocommerce_gateway_ezdefi_update_database_nonce' ); ?>
                    <input type="hidden" name="action" value="woocommerce_gateway_ezdefi_update_database">
                    <p><input class="button button-primary" type="submit" value="Update database"></p>
                </form>
            </div>
            <?php echo ob_get_clean();
        });
    }

    /**
     * Update database
     */
    public function update_database()
    {
        global $wpdb;

        if(
            ! isset( $_POST['woocommerce_gateway_ezdefi_update_database_nonce'] ) ||
            ! wp_verify_nonce( $_POST['woocommerce_gateway_ezdefi_update_database_nonce'], 'woocommerce_gateway_ezdefi_update_database' )
        ) {
            wp_safe_redirect( admin_url() );
        }

        $amount_table_name = $wpdb->prefix . 'woocommerce_ezdefi_amount';

        $wpdb->query( "DROP TABLE IF EXISTS $amount_table_name" );
        $wpdb->query( "DROP PROCEDURE IF EXISTS `wc_ezdefi_generate_amount_id`" );
        $wpdb->query( "DROP EVENT IF EXISTS `wc_ezdefi_clear_amount_table`" );
        $wpdb->query( "DROP EVENT IF EXISTS `wc_ezdefi_clear_exception_table`" );

        $exception_table_name = $wpdb->prefix . 'woocommerce_ezdefi_exception';

        if( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $exception_table_name ) ) === $exception_table_name ) {
            $wpdb->query(
                "ALTER TABLE $exception_table_name ADD confirmed TinyInt(1) DEFAULT 0, ADD is_show TinyInt(1) DEFAULT 1, ALTER explorer_url SET DEFAULT NULL;"
            );
        }

        update_option( 'woocommerce_gateway_ezdefi_version', $this->version );

        if (! wp_next_scheduled ( 'woocommerce_gateway_ezdefi_weekly_event' ) ) {
            wp_schedule_event( time(), 'weekly', 'woocommerce_gateway_ezdefi_weekly_event' );
        }

        wp_safe_redirect( admin_url() );
    }

    /**
     * Add weekly cron schedule
     *
     * @param $schedules
     *
     * @return mixed
     */
    public function add_cron_schedule( $schedules )
    {
        $schedules['weekly'] = array(
            'interval' => 604800,
            'display' => __( 'Once Weekly' )
        );

        return $schedules;
    }

    /**
     * Create database weekly
     */
    public function clear_database()
    {
        global $wpdb;

        $exception_table_name = $wpdb->prefix . 'woocommerce_ezdefi_exception';

        $wpdb->query( "DELETE FROM $exception_table_name;" );
    }

	/**
	 * Run when activate plugin
	 */
	public static function activate()
	{
		global $wpdb;

		$sql = array();

		$charset_collate = $wpdb->get_charset_collate();

		$exception_table_name = $wpdb->prefix . 'woocommerce_ezdefi_exception';

		$sql[] = "CREATE TABLE $exception_table_name (
			id int(11) NOT NULL AUTO_INCREMENT,
			amount_id decimal(60,30) NOT NULL,
			currency varchar(10) NOT NULL,
			order_id int(11),
			status varchar(20),
			payment_method varchar(100),
			explorer_url varchar(200) DEFAULT NULL,
			confirmed tinyint(1) DEFAULT 0 NOT NULL,
			is_show tinyint(1) DEFAULT 1 NOT NULL,
			PRIMARY KEY (id)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		dbDelta( $sql );

        if (! wp_next_scheduled ( 'woocommerce_gateway_ezdefi_weekly_event' ) ) {
            wp_schedule_event( time(), 'weekly', 'woocommerce_gateway_ezdefi_weekly_event' );
        }

        update_option( 'woocommerce_gateway_ezdefi_version', '2.0.0' );
	}

    /**
     * Run when deactivate plugin
     */
	public static function deactivate()
    {
        wp_clear_scheduled_hook( 'woocommerce_gateway_ezdefi_weekly_event' );
    }

	/**
	 * Includes required files
	 */
	public function includes()
	{
	    require_once dirname( __FILE__ ) . '/functions.php';
		require_once dirname( __FILE__ ) . '/includes/class-wc-ezdefi-db.php';
		require_once dirname( __FILE__ ) . '/includes/class-wc-ezdefi-api.php';
		require_once dirname( __FILE__ ) . '/includes/class-wc-ezdefi-ajax.php';
		require_once dirname( __FILE__ ) . '/includes/class-wc-ezdefi-callback.php';
        require_once dirname( __FILE__ ) . '/includes/admin/class-wc-ezdefi-admin-notices.php';
		require_once dirname( __FILE__ ) . '/includes/admin/class-wc-ezdefi-exception-page.php';
		require_once dirname( __FILE__ ) . '/includes/class-wc-gateway-ezdefi.php';
	}

	/**
	 * Add Woocommerce payment gateway
	 *
	 * @param $methods
	 *
	 * @return array
	 */
	public function add_gateways( $methods )
	{
		$methods[] = 'WC_Gateway_Ezdefi';

		return $methods;
	}

	/**
	 * Define constants
	 */
	public function define_constants()
	{
		define( 'WC_EZDEFI_VERSION', '1.0.0' );
		define( 'WC_EZDEFI_MAIN_FILE', __FILE__ );
		define( 'WC_EZDEFI_PLUGIN_URL', untrailingslashit( plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) ) );
		define( 'WC_EZDEFI_PLUGIN_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
	}

    /**
     * Add action link
     *
     * @param $links
     *
     * @return array
     */
    public function plugin_action_links( $links )
    {
        $plugin_links = array(
            '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=ezdefi' ) . '">' . __( 'Settings', 'woocommerce-gateway-ezdefi' ) . '</a>'
        );

        return array_merge( $plugin_links, $links );
    }

	/**
	 * Get the main WC_Ezdefi instance
	 *
	 * @return WC_Ezdefi
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}

add_action( 'plugins_loaded', 'woocommerce_gateway_ezdefi_init' );

function woocommerce_gateway_ezdefi_init() {
	load_plugin_textdomain( 'woocommerce-gateway-ezdefi', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );

    WC_Ezdefi::get_instance();
}

register_activation_hook( __FILE__, array( 'WC_Ezdefi', 'activate' ) );

register_deactivation_hook( __FILE__, array( 'WC_Ezdefi', 'deactivate' ) );