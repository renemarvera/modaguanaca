<?php
if (!defined('ABSPATH')) {
    exit;
}
class Eh_Paypal_Express_Hooks
{
    protected $eh_paypal_express_options;
    function __construct() {
        $this->eh_paypal_express_options=get_option('woocommerce_eh_paypal_express_settings');
         
        if(isset($this->eh_paypal_express_options['express_button_on_pages'])){
            $this->express_button_on_pages = $this->eh_paypal_express_options['express_button_on_pages'] ? $this->eh_paypal_express_options['express_button_on_pages'] : array();
        }

        if(isset($this->eh_paypal_express_options['credit_button_on_pages'])){
            $this->credit_button_on_pages = $this->eh_paypal_express_options['credit_button_on_pages'] ? $this->eh_paypal_express_options['credit_button_on_pages'] : array();
        }

        if(isset($this->eh_paypal_express_options['smart_button_on_pages'])){
            $this->smart_button_on_pages = $this->eh_paypal_express_options['smart_button_on_pages'] ? $this->eh_paypal_express_options['smart_button_on_pages'] : array();
        }
                
         if (isset($this->eh_paypal_express_options['smart_button_enabled']) && $this->eh_paypal_express_options['smart_button_enabled'] == 'yes') {
             add_action('woocommerce_review_order_after_payment', array($this, 'eh_express_checkout_hook')); //add express button in checkout page

        }
        else{
            add_action('woocommerce_before_checkout_form', array($this, 'eh_express_checkout_hook')); //add express button in checkout page
        }

       add_action('woocommerce_proceed_to_checkout',array($this,'eh_express_checkout_hook'),20);
        add_action('wp', array($this, 'unset_express'));
        add_action('woocommerce_cart_emptied', array($this,'unset_expres_cart_empty'));
    }
    public function unset_express()
    {
        if( ( isset($_REQUEST['cancel_express_checkout']) && ($_REQUEST['cancel_express_checkout'] === 'cancel') ) )
        {
            if(isset(WC()->session->eh_pe_billing)){
                unset(WC()->session->eh_pe_billing);
            }
            if(isset(WC()->session->pay_for_order['pay_for_order'])){
                unset(WC()->session->pay_for_order);
            }
            if(isset(WC()->session->eh_pe_checkout))
            {
                unset(WC()->session->eh_pe_checkout);
                wc_clear_notices();
                wc_add_notice(__('You have cancelled PayPal Express Checkout. Please try to process your order again.', 'express-checkout-paypal-payment-gateway-for-woocommerce'), 'notice');
            }
        }
    }
    public function unset_expres_cart_empty()
    {
        if(isset(WC()->session->eh_pe_billing)){
            unset(WC()->session->eh_pe_billing);
        }
        if(isset(WC()->session->pay_for_order['pay_for_order'])){
            unset(WC()->session->pay_for_order);
        }
        if(isset(WC()->session->eh_pe_checkout))
        {
            unset(WC()->session->eh_pe_checkout);
        }
    }
    public function express_run()
    {
        if(isset($this->eh_paypal_express_options['enabled']) && ($this->eh_paypal_express_options['enabled']==="yes") )
        {            
            $this->check_express();
        }        
    }
    protected function check_express()
    {
        if (isset(WC()->session->eh_pe_checkout)) {
            return;
        }

        if(is_cart())
        {
            $show_button_in_cart_page = apply_filters('wt_show_paypal_express_button_in_cart_page', TRUE);
            if($show_button_in_cart_page){
                $this->checkout_button_include();
                $this->eh_payment_scripts();
            }
        }
        if(is_checkout())
        {
            $show_button_in_checkout_page = apply_filters('wt_show_paypal_express_button_in_checkout_page', TRUE);
            if($show_button_in_checkout_page){
                $this->checkout_button_include();
                $this->eh_payment_scripts();
            }
        }
            
    }
    public function eh_express_checkout_hook() 
    {
        if ( apply_filters( 'eh_hide_paypal_express_button_in_cart', false ) ) {
			return;
		}
        require_once (EH_PAYPAL_MAIN_PATH . "includes/functions.php");
        eh_paypal_express_hook_init();
    }
    public function checkout_button_include()
    {
        $page= get_page_link();
       
        //Smart button
        if(isset($this->eh_paypal_express_options['smart_button_enabled']) && $this->eh_paypal_express_options['smart_button_enabled'] == 'yes'){        
            $desc="";
            $style = "";
            print '<span class="eh_spinner" style="display:none"><img style="width:50px;"  src="' . admin_url() .'images/loading.gif" /></span>';
            if(isset($this->eh_paypal_express_options['smart_button_description']) && $this->eh_paypal_express_options['smart_button_description'] !=='')
            {
                $desc = '<div class="eh_paypal_express_description" ><small>-- '.$this->eh_paypal_express_options['smart_button_description'].' --</small></div>';
            }
            $ex_button_output = '<center>';    
            if($this->eh_express_button_enabled()){
                if (isset($this->eh_paypal_express_options['smart_button_size'])) {
                   if ($this->eh_paypal_express_options['smart_button_size'] == 'small') {
                      $style = 'style="width:35%"';
                   }
                   elseif ($this->eh_paypal_express_options['smart_button_size'] == 'medium') {
                       $style = 'style="width:45%"';
                   }
                   elseif ($this->eh_paypal_express_options['smart_button_size'] == 'large') {
                       $style = 'style="width:55%"';
                   }                      
                }
                $style = apply_filters('eh_paypal_smart_button_style', $style);
                $ex_button_output .= $desc.'<div ' . $style . ' class="single_add_to_cart_button eh_paypal_express_link" id="paypal-checkout-button-render"></div>'; 

                $ex_button_output .= "</center>";
                echo $ex_button_output;         
            } 
        }
       //Express checkout
        else{        
            $express_button   = apply_filters("eh_paypal_express_checkout_button", EH_PAYPAL_MAIN_URL ."assets/img/checkout-".$this->eh_paypal_express_options['button_size'].".svg");
            $express_ccbutton = apply_filters("eh_paypal_express_checkout_ccbutton", EH_PAYPAL_MAIN_URL ."assets/img/paypalcredit-".$this->eh_paypal_express_options['button_size'].".svg");

            $desc = ""; $add_desc = 0;    
            if($this->eh_paypal_express_options['express_description'] !=='')
            {
                $desc = '<div class="eh_paypal_express_description" ><small>-- '.$this->eh_paypal_express_options['express_description'].' --</small></div>';
            }
            $ex_button_output = '<center>';    

            if($this->eh_express_button_enabled())
            {
                $ex_button_output .= $desc.'<a href="' . esc_url(add_query_arg('p', $page, $this->make_express_url('express_start'))) . '" class="single_add_to_cart_button eh_paypal_express_link"><img src="'.$express_button.'" style="width:auto;height:auto;" class=" single_add_to_cart_button eh_paypal_express_image" alt="' . __('Check out with PayPal', 'express-checkout-paypal-payment-gateway-for-woocommerce') . '" /></a>';
            }else{
                $add_desc = 1;
            }

            if($this->eh_express_credit_button_enabled())
            {
                if($add_desc == 1){
                    $ex_button_output .= $desc;
                }
                $ex_button_output .= '<a href="' . esc_url(add_query_arg('p', $page, $this->make_express_url('credit_start'))) . '" class="single_add_to_cart_button eh_paypal_express_link"><img src="'.$express_ccbutton.'" style="width:auto;height:auto;" class=" single_add_to_cart_button eh_paypal_express_image" alt="' . __('Check out with PayPal Credit', 'express-checkout-paypal-payment-gateway-for-woocommerce') . '" /></a>';
            }
                
            $ex_button_output .= "</center>";
            echo $ex_button_output;
        }        
    }

    public function eh_express_button_enabled(){

        if(isset($this->eh_paypal_express_options['smart_button_enabled']) && $this->eh_paypal_express_options['smart_button_enabled'] == 'yes' && $this->eh_paypal_express_options['enabled'] == 'yes'){
            if(is_cart())
            {
                if( (isset($this->smart_button_on_pages) && in_array('cart', $this->smart_button_on_pages))){
                   return true;
                }
            }
            if(is_checkout())
            { 
                if( (isset($this->smart_button_on_pages) && in_array('checkout', $this->smart_button_on_pages))){ 
                    return true;
                }
            }

            return false;
        }
        else{
            if(is_cart())
            {
                if( (isset($this->express_button_on_pages) && in_array('cart', $this->express_button_on_pages)) || (isset($this->eh_paypal_express_options['express_enabled']) && ($this->eh_paypal_express_options['express_enabled'] === 'yes' ) && isset($this->eh_paypal_express_options['express_on_cart_page']) && $this->eh_paypal_express_options['express_on_cart_page'] === 'yes')){
                   return true;
                }
            }
            if(is_checkout())
            {
                if( (isset($this->express_button_on_pages) && in_array('checkout', $this->express_button_on_pages))  || (isset($this->eh_paypal_express_options['express_enabled']) && ($this->eh_paypal_express_options['express_enabled'] === 'yes' ) && isset($this->eh_paypal_express_options['express_on_checkout_page']) && $this->eh_paypal_express_options['express_on_checkout_page'] === 'yes')){
                    return true;
                }
            }
            return false;
       }
    }
    public function eh_express_credit_button_enabled(){
        
        if(is_cart())
        {
            if( (isset($this->credit_button_on_pages) && in_array('cart', $this->credit_button_on_pages)) || (isset($this->eh_paypal_express_options['express_enabled']) && ($this->eh_paypal_express_options['express_enabled'] === 'yes' ) && isset($this->eh_paypal_express_options['credit_checkout']) && ($this->eh_paypal_express_options['credit_checkout']==='yes') && isset($this->eh_paypal_express_options['express_on_cart_page']) && $this->eh_paypal_express_options['express_on_cart_page'] === 'yes')){
               return true;
            }
        }
        if(is_checkout())
        {
            if( (isset($this->credit_button_on_pages) && in_array('checkout', $this->credit_button_on_pages)) || (isset($this->eh_paypal_express_options['express_enabled']) && ($this->eh_paypal_express_options['express_enabled'] === 'yes' ) && isset($this->eh_paypal_express_options['credit_checkout']) && ($this->eh_paypal_express_options['credit_checkout']==='yes') && isset($this->eh_paypal_express_options['express_on_checkout_page']) && $this->eh_paypal_express_options['express_on_checkout_page'] === 'yes')){
                return true;
            }
        }
        return false;
        
    }
    public function make_express_url($action) {
         
        return add_query_arg('c', $action, WC()->api_request_url('Eh_PayPal_Express_Payment'));
    }
    public function eh_payment_scripts()
    {
        if ( is_cart() || is_checkout() ) {
            $page= get_permalink();
            $pagename = (is_cart()) ? 'cart' : 'checkout';
            $wpc_plugin_active = is_plugin_active('wpc-ajax-add-to-cart/wpc-ajax-add-to-cart.php') ? 'yes' : 'no' ;
            wp_register_style( 'eh-express-style', EH_PAYPAL_MAIN_URL . 'assets/css/eh-express-style.css',array(),EH_PAYPAL_VERSION );
            wp_enqueue_style( 'eh-express-style' );
            wp_register_script( 'eh-express-js', EH_PAYPAL_MAIN_URL . 'assets/js/eh-express-script.js',array(),EH_PAYPAL_VERSION );
            wp_enqueue_script( 'eh-express-js' );
            wp_localize_script( 'eh-express-js', 'eh_express_checkout_params', array( 'page_name' => $pagename, 'wpc_plugin' => $wpc_plugin_active ) );

            if (isset($this->eh_paypal_express_options['smart_button_enabled']) && $this->eh_paypal_express_options['smart_button_enabled'] == 'yes') { 
                if ($this->eh_paypal_express_options['smart_button_environment'] == 'live') {
                    $client_id = (isset($this->eh_paypal_express_options['live_client_id']) ? $this->eh_paypal_express_options['live_client_id'] : '');
                }
                else{
                     $client_id = (isset($this->eh_paypal_express_options['sandbox_client_id']) ? $this->eh_paypal_express_options['sandbox_client_id'] : '');
                }
                 $smart_payment_url = esc_url(add_query_arg(array('p' => $page, 'type' => 'ajax'), $this->make_express_url('create_order')));
                $c = 'create_order';               
                $intent  = 'capture';
                $locale = ($this->eh_paypal_express_options['smart_button_paypal_locale']==='yes') ? $this->get_locale(get_locale()) : false;
                $commit = ($this->eh_paypal_express_options['smart_button_skip_review'] === 'yes' ? 'true' : 'false' );

                $paypal_script_url = esc_url(add_query_arg(array('client-id' => $client_id, 'intent' => $intent, 'currency' => get_woocommerce_currency(), 'locale' => $locale, 'commit' => $commit, 'components' => 'buttons', 'debug' => 'false'), 'https://www.paypal.com/sdk/js'));

                if (isset($this->eh_paypal_express_options['disable_funding_source']) && !empty($this->eh_paypal_express_options['disable_funding_source'])) {
                    $disabled_funding = implode(',', $this->eh_paypal_express_options['disable_funding_source']);
                   if (!empty($disabled_funding)) {
                       $paypal_script_url .= "&disable-funding=" . $disabled_funding;
                   }
                }
                $button_params = array(
                    'c'    => 'express_start',
                    'p'    => $page,
                    'express_button'    => true,
                    'return_url'    => add_query_arg(array('p' => $page, 'intent' => $intent), $this->make_express_url('order_details')),
                    'cancel_url'    => add_query_arg('p', $page, $this->make_express_url('cancel_order')),
                    'express_url'    => $smart_payment_url,
                    'environment'  => $this->eh_paypal_express_options['smart_button_environment'],
                    'size'  => $this->eh_paypal_express_options['smart_button_size'],
                    'layout'  => $this->eh_paypal_express_options['button_layout'],
                    'color'  => $this->eh_paypal_express_options['button_color'],
                    'shape'  => $this->eh_paypal_express_options['button_shape'],
                    'label'  => $this->eh_paypal_express_options['button_label'],
                    'tagline'  => $this->eh_paypal_express_options['button_tagline'],
                    'locale'       => $locale ,
                    'page_name'       => $pagename ,
                );

                wp_register_script( 'paypal-checkout-incontext-js', $paypal_script_url, array() , null);
                wp_register_script('eh-smart-button-js', EH_PAYPAL_MAIN_URL . 'assets/js/eh-button-render.js',array('paypal-checkout-incontext-js'),EH_PAYPAL_VERSION);
                wp_enqueue_script('eh-smart-button-js');
                wp_localize_script( 'eh-smart-button-js', 'eh_button_params', $button_params);
            }
        }
    }

        public function get_locale($locale)
    {
        $safe_locales = array(
        'da_DK',
        'de_DE',
        'en_AU',
        'en_GB',
        'en_US',
        'es_ES',
        'fr_CA',
        'fr_FR',
        'he_IL',
        'id_ID',
        'it_IT',
        'ja_JP',
        'nl_NL',
        'pl_PL',
        'pt_BR',
        'pt_PT',
        'ru_RU',
        'sv_SE',
        'th_TH',
        'tr_TR',
        'zh_CN',
        'zh_HK',
        'zh_TW',
        );
        if (!in_array($locale, $safe_locales)) {
            $locale = 'en_US';
        }
        return $locale;
    }
}