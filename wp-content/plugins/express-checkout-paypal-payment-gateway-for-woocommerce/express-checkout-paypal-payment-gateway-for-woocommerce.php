<?php
/*
 * Plugin Name: PayPal Express Checkout Payment Gateway for WooCommerce ( Basic )
 * Plugin URI: https://wordpress.org/plugins/express-checkout-paypal-payment-gateway-for-woocommerce/
 * Description: Accepts payments via PayPal, Credit/Debit cards, Paypal Credit, or Local Payment Methods based on country/device using PayPal Express/Smart button checkout.
 * Author: WebToffee
 * Author URI: https://www.webtoffee.com/product/paypal-express-checkout-gateway-for-woocommerce/
 * Version: 1.6.3
 * * WC requires at least: 3.0
 * WC tested up to: 5.7.1
 * Text Domain: express-checkout-paypal-payment-gateway-for-woocommerce
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 */
if (!defined('ABSPATH')) {
    exit;
}
if (!defined('EH_PAYPAL_MAIN_PATH')) {
    define('EH_PAYPAL_MAIN_PATH', plugin_dir_path(__FILE__));
}
if (!defined('EH_PAYPAL_MAIN_URL')) {
    define('EH_PAYPAL_MAIN_URL', plugin_dir_url(__FILE__));
}
if (!defined('EH_PAYPAL_VERSION')) {
    define('EH_PAYPAL_VERSION', '1.6.3');
}

require_once(ABSPATH . "wp-admin/includes/plugin.php");


if(is_plugin_active('eh-paypal-express-checkout/eh-paypal-express-checkout.php')) 
{
    deactivate_plugins(plugin_basename(__FILE__));
    wp_die(__("Oops! PREMIUM Version of this Plugin Installed. Please uninstall the PREMIUM Version before activating BASIC", "express-checkout-paypal-payment-gateway-for-woocommerce"), "", array('back_link' => 1));
    
    return;
}
 else
 {
    
    add_action('plugins_loaded', 'eh_paypal_check', 99);

    function eh_paypal_check(){ 

        if( class_exists( 'WooCommerce' )){

            register_activation_hook(__FILE__, 'eh_paypal_express_init_log');
            include(EH_PAYPAL_MAIN_PATH . "includes/log.php");
            
            add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'eh_paypal_express_plugin_action_links');
            function eh_paypal_express_plugin_action_links($links)
            {
                $setting_link = admin_url('admin.php?page=wc-settings&tab=checkout&section=eh_paypal_express');
                $plugin_links = array(
                    '<a href="' . $setting_link . '">' . __('Settings', 'express-checkout-paypal-payment-gateway-for-woocommerce') . '</a>',
                    '<a href="https://www.webtoffee.com/product/paypal-express-checkout-gateway-for-woocommerce/?utm_source=free_plugin_sidebar&utm_medium=Paypal_basic&utm_campaign=Paypal&utm_content='.EH_PAYPAL_VERSION.'" target="_blank" style="color:#3db634;">' . __( 'Premium Upgrade', 'express-checkout-paypal-payment-gateway-for-woocommerce') . '</a>',
                    '<a href="https://wordpress.org/support/plugin/express-checkout-paypal-payment-gateway-for-woocommerce/" target="_blank">' . __('Support', 'express-checkout-paypal-payment-gateway-for-woocommerce') . '</a>',
                    '<a href="https://wordpress.org/support/plugin/express-checkout-paypal-payment-gateway-for-woocommerce/reviews/" target="_blank">' . __('Review', 'express-checkout-paypal-payment-gateway-for-woocommerce') . '</a>',
                );
                
                if (array_key_exists('deactivate', $links)) {
                    $links['deactivate'] = str_replace('<a', '<a class="ehpypl-deactivate-link"', $links['deactivate']);
                }
                
                return array_merge($plugin_links, $links);
            }
            

        } else {
           add_action('admin_notices', 'eh_paypal_express_wc_admin_notices', 99);
           deactivate_plugins(plugin_basename(__FILE__));
        }  
    }
    function eh_paypal_express_wc_admin_notices()
    {
        is_admin() && add_filter('gettext', function($translated_text, $untranslated_text, $domain)
        {
            $old = array(
                "Plugin <strong>deactivated</strong>.",
                "Selected plugins <strong>deactivated</strong>.",
                "Plugin deactivated.",
                "Selected plugins deactivated.",
                "Plugin <strong>activated</strong>.",
                "Selected plugins <strong>activated</strong>.",
                "Plugin activated.",
                "Selected plugins activated."
            );
            $new = "<span style='color:red'>PayPal Express Payment for WooCommerce (BASIC) (WebToffee)-</span> Plugin Needs WooCommerce to Work.";
            if (in_array($untranslated_text, $old, true)) {
                $translated_text = $new;
            }
            return $translated_text;
        }, 99, 3);
    }
    function eh_paypal_express_init_log()
    {
        
        if(WC()->version >= '2.7.0')
        {
            $log      = wc_get_logger();
            $init_msg = Eh_PayPal_Log::init_log();
            $context = array( 'source' => 'eh_paypal_express_log' );
            $log->log("debug", $init_msg,$context);
        }
        else
        {
            $log      = new WC_Logger();
            $init_msg = Eh_PayPal_Log::init_log();
            $log->add("eh_paypal_express_log", $init_msg);
        }
    }

    function eh_paypal_express_run()
    {
        static $eh_paypal_plugin;
        if(!isset($eh_paypal_plugin))
        {
            require_once (EH_PAYPAL_MAIN_PATH . "includes/class-eh-paypal-init-handler.php");
            $eh_paypal_plugin=new Eh_Paypal_Express_Handlers();
        }
        return $eh_paypal_plugin;
    }
    eh_paypal_express_run()->express_run();

    /*
    *   When Skip Review option disabled, Prevent WC order creation and divert to our order creation process for prevent creating twise order 
    *
    */

    add_action('woocommerce_checkout_process', 'get_order_processed');
    function get_order_processed(){
    
        if(isset(WC()->session->eh_pe_checkout['order_id']) && isset(WC()->session->eh_pe_set['skip_review_disabled']) && (WC()->session->eh_pe_set['skip_review_disabled'] === 'true') ){
            $order_id = WC()->session->eh_pe_checkout['order_id'];
            $order = wc_get_order($order_id);
            
            $eh_paypal_express = new Eh_PayPal_Express_Payment();
            $eh_paypal_express->process_payment($order_id);
            
            unset(WC()->session->eh_pe_set);
    
        }
    }
}
add_action('admin_footer',  'deactivate_scripts');
add_action('wp_ajax_ehpypl_submit_uninstall_reason',  "send_uninstall_reason");

function deactivate_scripts() {

    global $pagenow;
    if ('plugins.php' != $pagenow) {
        return;
    }
    $reasons = get_uninstall_reasons();
    ?>
    <div class="ehpypl-modal" id="ehpypl-ehpypl-modal">
        <div class="ehpypl-modal-wrap">
            <div class="ehpypl-modal-header">
                <h3><?php _e('If you have a moment, please let us know why you are deactivating:', 'express-checkout-paypal-payment-gateway-for-woocommerce'); ?></h3>
            </div>
            <div class="ehpypl-modal-body">
                <ul class="reasons">
                    <?php foreach ($reasons as $reason) { ?>
                        <li data-type="<?php echo esc_attr($reason['type']); ?>" data-placeholder="<?php echo esc_attr($reason['placeholder']); ?>">
                            <label><input type="radio" name="selected-reason" value="<?php echo $reason['id']; ?>"> <?php echo $reason['text']; ?></label>
                        </li>
                    <?php } ?>
                </ul>
                <div class="wt-uninstall-feedback-privacy-policy">
                    <?php _e("We do not collect any personal data when you submit this form. It's your feedback that we value.", "payment-gateway-stripe-and-woocommerce-integration"); ?>
                    <a href="https://www.webtoffee.com/privacy-policy/" target="_blank"><?php _e('Privacy Policy', 'express-checkout-paypal-payment-gateway-for-woocommerce'); ?></a>
                </div>
            </div>
            <div class="ehpypl-modal-footer">
                    <a class="button-primary" href="https://wordpress.org/support/plugin/express-checkout-paypal-payment-gateway-for-woocommerce/" target="_blank">
                    <span class="dashicons dashicons-external" style="margin-top:3px;"></span>
                    <?php _e('Go to support', 'express-checkout-paypal-payment-gateway-for-woocommerce'); ?></a>
                <button class="button-primary ehpypl-model-submit"><?php _e('Submit & Deactivate', 'express-checkout-paypal-payment-gateway-for-woocommerce'); ?></button>
                <button class="button-secondary ehpypl-model-cancel"><?php _e('Cancel', 'express-checkout-paypal-payment-gateway-for-woocommerce'); ?></button>
                <a href="#" class="dont-bother-me"><?php _e('I rather wouldn\'t say', 'express-checkout-paypal-payment-gateway-for-woocommerce'); ?></a>

            </div>
        </div>
    </div>

    <style type="text/css">
        .ehpypl-modal {
            position: fixed;
            z-index: 99999;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            background: rgba(0,0,0,0.5);
            display: none;
        }
        .ehpypl-modal.modal-active {display: block;}
        .ehpypl-modal-wrap {
            width: 50%;
            position: relative;
            margin: 10% auto;
            background: #fff;
        }
        .ehpypl-modal-header {
            border-bottom: 1px solid #eee;
            padding: 8px 20px;
        }
        .ehpypl-modal-header h3 {
            line-height: 150%;
            margin: 0;
        }
        .ehpypl-modal-body {padding: 5px 20px 20px 20px;}
        .ehpypl-modal-body .input-text,.ehpypl-modal-body textarea {width:75%;}
        .ehpypl-modal-body .reason-input {
            margin-top: 5px;
            margin-left: 20px;
        }
        .ehpypl-modal-footer {
            border-top: 1px solid #eee;
            padding: 12px 20px;
            text-align: left;
        }
        .reviewlink{
            padding:10px 0px 0px 35px !important;
            font-size: 15px;
        }
        .review-and-deactivate{
            padding:5px;
        }
        .wt-uninstall-feedback-privacy-policy {
            text-align: left;
            font-size: 12px;
            color: #aaa;
            line-height: 14px;
            margin-top: 20px;
            font-style: italic;
        }

        .wt-uninstall-feedback-privacy-policy a {
            font-size: 11px;
            color: #4b9cc3;
            text-decoration-color: #99c3d7;
        }
    </style>
    <script type="text/javascript">
        (function ($) {
            $(function () {
                var modal = $('#ehpypl-ehpypl-modal');
                var deactivateLink = '';


                $('#the-list').on('click', 'a.ehpypl-deactivate-link', function (e) {
                    e.preventDefault();
                    modal.addClass('modal-active');
                    deactivateLink = $(this).attr('href');
                    modal.find('a.dont-bother-me').attr('href', deactivateLink).css('float', 'right');
                });

                $('#ehpypl-ehpypl-modal').on('click', 'a.review-and-deactivate', function (e) {
                    e.preventDefault();
                    window.open("https://wordpress.org/plugins/express-checkout-paypal-payment-gateway-for-woocommerce/reviews/#new-post");
                    window.location.href = deactivateLink;
                });
                modal.on('click', 'button.ehpypl-model-cancel', function (e) {
                    e.preventDefault();
                    modal.removeClass('modal-active');
                });
                modal.on('click', 'input[type="radio"]', function () {
                    var parent = $(this).parents('li:first');
                    modal.find('.reason-input').remove();
                    var inputType = parent.data('type'),
                            inputPlaceholder = parent.data('placeholder');
                    if ('reviewhtml' === inputType) {
                        var reasonInputHtml = '<div class="reviewlink"><a href="#" target="_blank" class="review-and-deactivate"><?php _e('Deactivate and leave a review', 'express-checkout-paypal-payment-gateway-for-woocommerce'); ?> <span class="xa-ehpypl-rating-link"> &#9733;&#9733;&#9733;&#9733;&#9733; </span></a></div>';
                    } else {
                        var reasonInputHtml = '<div class="reason-input">' + (('text' === inputType) ? '<input type="text" class="input-text" size="40" />' : '<textarea rows="5" cols="45"></textarea>') + '</div>';
                    }
                    if (inputType !== '') {
                        parent.append($(reasonInputHtml));
                        parent.find('input, textarea').attr('placeholder', inputPlaceholder).focus();
                    }
                });

                modal.on('click', 'button.ehpypl-model-submit', function (e) {
                    e.preventDefault();
                    var button = $(this);
                    if (button.hasClass('disabled')) {
                        return;
                    }
                    var $radio = $('input[type="radio"]:checked', modal);
                    var $selected_reason = $radio.parents('li:first'),
                            $input = $selected_reason.find('textarea, input[type="text"]');

                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'ehpypl_submit_uninstall_reason',
                            reason_id: (0 === $radio.length) ? 'none' : $radio.val(),
                            reason_info: (0 !== $input.length) ? $input.val().trim() : ''
                        },
                        beforeSend: function () {
                            button.addClass('disabled');
                            button.text('Processing...');
                        },
                        complete: function () {
                            window.location.href = deactivateLink;
                        }
                    });
                });
            });
        }(jQuery));
    </script>
    <?php
}

function get_uninstall_reasons() {

    $reasons = array(
        array(
            'id' => 'used-it',
            'text' => __('Used it successfully. Don\'t need anymore.', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
            'type' => 'reviewhtml',
            'placeholder' => __('Have used it successfully and aint in need of it anymore', 'express-checkout-paypal-payment-gateway-for-woocommerce')
        ),
        array(
            'id' => 'could-not-understand',
            'text' => __('I couldn\'t understand how to make it work', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
            'type' => 'textarea',
            'placeholder' => __('Would you like us to assist you?', 'express-checkout-paypal-payment-gateway-for-woocommerce')
        ),
        array(
            'id' => 'found-better-plugin',
            'text' => __('I found a better plugin', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
            'type' => 'text',
            'placeholder' => __('Which plugin?', 'express-checkout-paypal-payment-gateway-for-woocommerce')
        ),
        array(
            'id' => 'not-have-that-feature',
            'text' => __('The plugin is great, but I need specific feature that you don\'t support', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
            'type' => 'textarea',
            'placeholder' => __('Could you tell us more about that feature?', 'express-checkout-paypal-payment-gateway-for-woocommerce')
        ),
        array(
            'id' => 'is-not-working',
            'text' => __('The plugin is not working', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
            'type' => 'textarea',
            'placeholder' => __('Could you tell us a bit more whats not working?', 'express-checkout-paypal-payment-gateway-for-woocommerce')
        ),
        array(
            'id' => 'looking-for-other',
            'text' => __('It\'s not what I was looking for', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
            'type' => 'textarea',
            'placeholder' => __('Could you tell us a bit more?', 'express-checkout-paypal-payment-gateway-for-woocommerce')
        ),
        array(
            'id' => 'did-not-work-as-expected',
            'text' => __('The plugin didn\'t work as expected', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
            'type' => 'textarea',
            'placeholder' => __('What did you expect?', 'express-checkout-paypal-payment-gateway-for-woocommerce')
        ),
        array(
            'id' => 'other',
            'text' => __('Other', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
            'type' => 'textarea',
            'placeholder' => __('Could you tell us a bit more?', 'express-checkout-paypal-payment-gateway-for-woocommerce')
        ),
    );

    return $reasons;
}

function send_uninstall_reason() {

    global $wpdb;

    if (!isset($_POST['reason_id'])) {
        wp_send_json_error();
    }

    $data = array(
        'reason_id' => sanitize_text_field($_POST['reason_id']),
        'plugin' => "ehpypl",
        'auth' => 'ehpypl_uninstall_1234#',
        'date' => gmdate("M d, Y h:i:s A"),
        'url' => '',
        'user_email' => '',
        'reason_info' => isset($_REQUEST['reason_info']) ? trim(stripslashes($_REQUEST['reason_info'])) : '',
        'software' => $_SERVER['SERVER_SOFTWARE'],
        'php_version' => phpversion(),
        'mysql_version' => $wpdb->db_version(),
        'wp_version' => get_bloginfo('version'),
        'wc_version' => (!defined('WC_VERSION')) ? '' : WC_VERSION,
        'locale' => get_locale(),
        'multisite' => is_multisite() ? 'Yes' : 'No',
        'ehpypl_version' => EH_PAYPAL_VERSION
    );
    // Write an action/hook here in webtoffe to recieve the data
    $resp = wp_remote_post('https://feedback.webtoffee.com/wp-json/ehpypl/v1/uninstall', array(
        'method' => 'POST',
        'timeout' => 45,
        'redirection' => 5,
        'httpversion' => '1.0',
        'blocking' => false,
        'body' => $data,
        'cookies' => array()
            )
    );

    wp_send_json_success();
}

add_action('init',  'load_ehpypl_plugin_textdomain');
/**
 * Handle localization
 */
function load_ehpypl_plugin_textdomain() {
    load_plugin_textdomain('express-checkout-paypal-payment-gateway-for-woocommerce', false, dirname(plugin_basename(__FILE__)) . '/lang/');
    add_thickbox();
}


/*
 *  Displays update information for a plugin. 
 */
function eh_express_checkout_paypal_payment_gateway_for_woocommerce_update_message( $data, $response )
{
    if(isset( $data['upgrade_notice']))
    {
        add_action( 'admin_print_footer_scripts','eh_express_checkout_paypal_payment_gateway_for_woocommerce_plugin_screen_update_js'); //fix for more than one alert text showing while updating the plugin
            $msg=str_replace(array('<p>','</p>'),array('<div>','</div>'),$data['upgrade_notice']);
            $msg=str_replace(array('<p>','</p>'),array('<div>','</div>'),$data['upgrade_notice']);
            echo '<style type="text/css">
            #express-checkout-paypal-payment-gateway-for-woocommerce-update .update-message p:last-child{ display:none;}     
            #express-checkout-paypal-payment-gateway-for-woocommerce-update ul{ list-style:disc; margin-left:30px;}
            .wt-update-message{ padding-left:30px;}
            </style>
            <div class="update-message wt-update-message">'. wpautop($msg).'</div>';
    }
}
add_action( 'in_plugin_update_message-express-checkout-paypal-payment-gateway-for-woocommerce/express-checkout-paypal-payment-gateway-for-woocommerce.php', 'eh_express_checkout_paypal_payment_gateway_for_woocommerce_update_message', 10, 2 );

if(!function_exists('eh_express_checkout_paypal_payment_gateway_for_woocommerce_plugin_screen_update_js'))
{
    function eh_express_checkout_paypal_payment_gateway_for_woocommerce_plugin_screen_update_js()
    {
        ?>
            <script>
                ( function( $ ){
                    var update_dv=$( '#express-checkout-paypal-payment-gateway-for-woocommerce-update');
                    update_dv.find('.wt-update-message').next('p').remove();
                    update_dv.find('a.update-link:eq(0)').click(function(){
                        $('.wt-update-message').remove();
                    });
                })( jQuery );
            </script>
        <?php
    }
}