<?php
if (!defined('ABSPATH')) {
    exit;
}
class Eh_Paypal_Express_Handlers {
    public function express_run()
    {
        add_action('plugins_loaded',array($this, 'check_dependencies'), 99);
        add_filter('woocommerce_payment_gateways',array($this, 'add_payment_gateway'));
    }
    public function add_payment_gateway($methods)
    {
        $methods[] = 'Eh_PayPal_Express_Payment';
        return $methods;
    }
    public function check_dependencies()
    {
        if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
            add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'eh_paypal_express_plugin_action_links');
            $this->paypal_express_init();
        } 
    }
    public function paypal_express_init()
    {
        if (!class_exists('Eh_PayPal_Express_Payment')) {
            require_once (EH_PAYPAL_MAIN_PATH . "includes/class-paypal-express-api.php");
        }
        $this->run_dependencies_hook();
    }
    public function run_dependencies_hook()
    {
        require_once (EH_PAYPAL_MAIN_PATH . "includes/class-paypal-express-hook.php");
        $this->hook_include=new Eh_Paypal_Express_Hooks();
        require_once (EH_PAYPAL_MAIN_PATH . "includes/class-paypal-express-request-builder.php");
        require_once (EH_PAYPAL_MAIN_PATH . "includes/class-paypal-rest-request-builder.php");
        require_once (EH_PAYPAL_MAIN_PATH . "includes/class-paypal-express-request-sender.php");
        require_once (EH_PAYPAL_MAIN_PATH . "includes/class-paypal-express-response-processer.php");
        require_once (EH_PAYPAL_MAIN_PATH . "includes/class-paypal-express-review-request.php");
    }
}
