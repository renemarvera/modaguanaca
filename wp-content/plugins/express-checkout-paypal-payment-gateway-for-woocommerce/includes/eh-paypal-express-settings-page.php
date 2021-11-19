<?php

if (!defined('ABSPATH')) {
    exit;
}
if (!defined('ABSPATH')) {
    exit;
}

$smart_button_default = 'no';
$express_button_default = 'yes';
$eh_paypal = get_option("woocommerce_eh_paypal_express_settings");
if (empty($eh_paypal)) {
   $smart_button_default = 'yes';
    $express_button_default = 'no';
}

if(isset($eh_paypal['express_enabled'])){

    if(isset($eh_paypal['express_on_cart_page']) && ($eh_paypal['express_on_cart_page'] === 'yes')){
        $cart = 'cart';
        if(isset($eh_paypal['credit_checkout']) && ($eh_paypal['credit_checkout'] === 'yes')){
            $cart_cc = 'cart';
        }else{
            $cart_cc = '';
        }
    }else{
        $cart = ''; $cart_cc = '';
    }
    if(isset($eh_paypal['express_on_checkout_page']) && ($eh_paypal['express_on_checkout_page'] === 'yes')){
        $checkout = 'checkout';
        if(isset($eh_paypal['credit_checkout']) && ($eh_paypal['credit_checkout'] === 'yes')){
            $checkout_cc = 'checkout';
        }else{
            $checkout_cc = '';
        }
    }else{
        $checkout = ''; $checkout_cc = '';
    }
}else{
    $cart = 'cart'; $checkout = 'checkout'; 
    $cart_cc = 'cart'; $checkout_cc = 'checkout'; 
}
$file_size=(file_exists(wc_get_log_file_path('eh_paypal_express_log'))?$this->file_size(filesize(wc_get_log_file_path('eh_paypal_express_log'))):'');

return array(
    'enabled' => array(
        'title' => __('PayPal Payment Gateway', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'label' => __('Enable', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'type' => 'checkbox',
        'description' => __('Enable to have PayPal payment method on the checkout page.', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'default' => 'no',
        'desc_tip' => true
    ),
    'express_checkout' => array(
        'title' => sprintf(__('Payment Button', 'express-checkout-paypal-payment-gateway-for-woocommerce')),
        'label' => sprintf(__('Express checkout', 'express-checkout-paypal-payment-gateway-for-woocommerce')),
        'type' => 'checkbox',
        'class' => 'eh_paypal_mode',
        'description' => 'Express Checkout are static buttons with limited customization options.', 
        'default' => $express_button_default
    ),   

   'smart_button_enabled' => array(
        'label' => sprintf(__('Smart Button', 'express-checkout-paypal-payment-gateway-for-woocommerce')),
       'type' => 'checkbox',
        'class' => 'eh_paypal_mode',
        'description' => '<div class="eh_paypal_mode_desc"><div>Smart Payment Buttons provides different ways to customize the PayPal checkout button. Accepts alternative payment methods such as PayPal Credit, Venmo, and local funding sources.</div><div class="smart_button_alert"> <span>&#9888;</span> Smart Payment buttons will override the existing customisations done for Express buttons.</div></div>', 
        'default' => $smart_button_default
    ), 

    'paypal_prerequesties' => array(
        'type' => 'title',
        'class' => 'express_toggle_display',
        'description' => sprintf("<div class='eh_wt_info_div express_toggle_display'><p><b>".__( 'Pre-requisites:','express-checkout-paypal-payment-gateway-for-woocommerce' )."</b></p><p> ".__( 'Requires a PayPal Business account linked with confirmed identity, email, and bank account.','express-checkout-paypal-payment-gateway-for-woocommerce' )."</p><p> ".__( 'To get the API credentials:','express-checkout-paypal-payment-gateway-for-woocommerce' )."</p><ul class='eh_wt_notice_bar_style'><li> ".__( 'Log into your PayPal business account.','express-checkout-paypal-payment-gateway-for-woocommerce' )." </li> <li>".__( 'Click Activity near the top of the page and select API Access.','express-checkout-paypal-payment-gateway-for-woocommerce' )."</li> <li>".__( 'Scroll to NVP/SOAP API Integration (Classic) and click Manage API Credentials.','express-checkout-paypal-payment-gateway-for-woocommerce' )."</li> <li>".__( 'Create keys if not done already. Else, copy the API Username, API Password, and Signature and paste it into the respective fields of the plugin.','express-checkout-paypal-payment-gateway-for-woocommerce' )."</li> </ul></div><p class='express_toggle_display'><a target='_blank' href='https://www.webtoffee.com/paypal-express-checkout-payment-gateway-woocommerce-user-guide/'>  ".__('Read documentation', 'express-checkout-paypal-payment-gateway-for-woocommerce')." </a></p>"),
    ),
    'credentials_title' => array(
        'title' => sprintf(__('PayPal Credentials', 'express-checkout-paypal-payment-gateway-for-woocommerce')),
        'type' => 'title',
        'class' => 'express_toggle_display',
        'description' => __('<span class="express_toggle_display">Select Live mode to accept payments and Sandbox mode to test payments.</span>', 'express-checkout-paypal-payment-gateway-for-woocommerce') 
    ),
    'environment' => array(
        'title' => __('Environment', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'type' => 'select',
        'class' => 'wc-enhanced-select express_toggle_display',
        'options' => array(
            'sandbox' => __('Sandbox mode', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
            'live' => __('Live mode', 'express-checkout-paypal-payment-gateway-for-woocommerce')
        ),
        'description' => sprintf(__('<div id="environment_alert_desc"></div>', 'express-checkout-paypal-payment-gateway-for-woocommerce')),
        'default' => 'sandbox',
        'desc_tip' => __('Choose Sandbox mode to test payment using test API keys. Switch to live mode to accept payments with PayPal using live API keys.', 'express-checkout-paypal-payment-gateway-for-woocommerce')
    ),
    'sandbox_username' => array(
        'title' => __('Sandbox API username', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'type' => 'text',
        'class' => 'express_toggle_display',
        'default' => ''
    ),
    'sandbox_password' => array(
        'title' => __('Sandbox API password', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'type' => 'password',
        'class' => 'express_toggle_display',
        'default' => ''
    ),
    'sandbox_signature' => array(
        'title' => __('Sandbox API signature', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'type' => 'password',
        'class' => 'express_toggle_display',
        'default' => ''
    ),
    'live_username' => array(
        'title' => __('Live API username', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'type' => 'text',
        'class' => 'express_toggle_display',
        'default' => ''
    ),
    'live_password' => array(
        'title' => __('Live API password', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'type' => 'password',
        'class' => 'express_toggle_display',
        'default' => ''
    ),
    'live_signature' => array(
        'title' => __('Live API signature', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'type' => 'password',
        'class' => 'express_toggle_display',
        'default' => ''
    ),
    'gateway_title' => array(
        'type' => 'title',
        'class'=> 'eh-css-class express_toggle_display'
    ),

    'title' => array(
        'title' => __('Title', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'type' => 'text',
        'class' => 'express_toggle_display',
        'description' => __('Input title for the payment gateway displayed at the checkout.', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'default' => __('PayPal', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'desc_tip' => true
    ),
    'description' => array(
        'title' => __('Description', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'type' => 'textarea',
        'class' => 'express_toggle_display',
        'css' => 'width:25em',
        'description' => __('Input description for the payment gateway displayed at the checkout.', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'default' => __('Secure payment via PayPal.', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'desc_tip' => true
    ),
   
    'express_title' => array(
        'title' => sprintf(__('PayPal Express Checkout Button', 'express-checkout-paypal-payment-gateway-for-woocommerce')),
        'type' => 'title',
        'description' => __('<span class="express_toggle_display">Add Express Checkout to your store for faster PayPal transactions.</span>', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'class'=> 'eh-css-class express_toggle_display'
    ),
    
    'express_button_on_pages' => array(
        'title' => __('Show Express button on  <a  class="thickbox" href="'.EH_PAYPAL_MAIN_URL . 'assets/img/express_button_preview.png?TB_iframe=true&width=100&height=100"> <small> [Preview] </small> </a>', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'type' => 'multiselect',
        'class' => 'chosen_select express_toggle_display',
        'css' => 'width: 350px;',
        'desc_tip' => __('Displays PayPal Express button on chosen pages.', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'options' => array(
            'cart' => 'Cart page',
            'checkout' => 'Checkout page',
        ),
        'default' => array(
            $cart,
            $checkout,
        )
    ),
    'credit_button_on_pages' => array(
        'title' => __('Show Express credit button on <a  class="thickbox" href="'.EH_PAYPAL_MAIN_URL . 'assets/img/credit_button_preview.png?TB_iframe=true&width=100&height=100"> <small> [Preview] </small> </a>', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'type' => 'multiselect',
        'class' => 'chosen_select express_toggle_display',
        'css' => 'width: 350px;',
        'desc_tip' => __('Displays a PayPal Credit button on selected pages. By using PayPal Credit, store owner will receive the payment upfront but customers can opt for financing and pay over time.', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'options' => array(
            'cart' => 'Cart page',
            'checkout' => 'Checkout page',
        ),
        'default' => array(
            $cart_cc,
            $checkout_cc,
        ),
    ),
    'express_description' => array(
        'title' => __('Description', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'type' => 'textarea',
        'css' => 'width:25em',
        'class' => 'express_toggle_display',
        'description' => __('Input description displayed above the PayPal Express button.', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'default' => __('Reduce the number of clicks with PayPal Express.', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'desc_tip' => true
    ),
    'button_size' => array(
        'title' => __('Button size', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'type' => 'select',
        'class' => 'wc-enhanced-select express_toggle_display',
        'description' => __('Choose the size of the button as either small, medium or large.', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'default' => 'medium',
        'desc_tip' => true,
        'options' => array(
            'small' => __('Small', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
            'medium' => __('Medium', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
            'large' => __('Large', 'express-checkout-paypal-payment-gateway-for-woocommerce')
        )
    ),
  
    'abilities_title' => array(
        'title' => sprintf(__('Branding', 'express-checkout-paypal-payment-gateway-for-woocommerce')),
        'type' => 'title',
        'description' => sprintf(__('<span class="express_toggle_display">Set your brand identity at the PayPal end by giving a brand name, logo, banner etc. It will be visible for customers on the PayPal site on choosing to pay via PayPal.</span>', 'express-checkout-paypal-payment-gateway-for-woocommerce')),
        'class'=> 'eh-css-class express_toggle_display',
    ),
    'business_name' => array(
        'title' => __('Brand name', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'type' => 'text',
        'class' => 'express_toggle_display',
        'description' => __('Input the name of your store that will appear on the PayPal end.', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'default' => __(get_bloginfo('name', 'display'), 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'desc_tip' => true
    ),
   
    'landing_page' => array(
        'title' => __('Landing page', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'type' => 'select',
        'class' => 'wc-enhanced-select express_toggle_display',
        'options' => array(
            'login' => __('Login', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
            'billing' => __('Billing', 'express-checkout-paypal-payment-gateway-for-woocommerce')
        ),
        'description' => sprintf(__('Customers will be redirected to the chosen page. By default, the billing page is taken.', 'express-checkout-paypal-payment-gateway-for-woocommerce')),
        'default' => 'billing',
        'desc_tip' => true
    ),
    'checkout_logo' => array(
        'title' => __('Logo (190 x 90)', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'type' => 'image',
        'class' => 'express_toggle_display',
        'description' => __('Upload a company logo that will appear on the PayPal end. Image requires an SSL host.', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'desc_tip' => true,
    ),
    'checkout_banner' => array(
        'title' => __('Header (750 x 90)', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'type' => 'image',
        'class' => 'express_toggle_display',
        'description' => __('Upload a header image that will appear on the PayPal end. Image requires an SSL host.', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'desc_tip' => true,
    ),
   
    'paypal_locale' => array(
        'title' => __('PayPal locale', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'type' => 'checkbox',
        'class' => 'express_toggle_display',
        'label' => __('Use store locale', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'default' => 'yes',
        'description' => __('Check to set the PayPal locale same as the store locale.', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'desc_tip' => true
    ),
    'advanced_option_title' => array(
        'title' => sprintf(__('Advanced Options', 'express-checkout-paypal-payment-gateway-for-woocommerce')),
        'type' => 'title',
        'class'=> 'eh-css-class express_toggle_display'
    ),
    'invoice_prefix' => array(
        'title' => __('Invoice prefix', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'type' => 'text',
        'class' => 'express_toggle_display',
        'description' => __('Enter an invoice prefix to identify transactions from your site.', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'default' => __('', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'desc_tip' => true,
        'placeholder' => 'EH_',
    ),
    'paypal_allow_override' => array(
        'title' => __('Address override', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'type' => 'checkbox',
        'class' => 'express_toggle_display',
        'label' => __('Enable to prevent checkout address being changed at the PayPal end.', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'default' => 'no',
        'description' => __('Enabling this will affect Express checkout and PayPal will strictly verify the address.', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'desc_tip' => false
    ),
    'send_shipping'   => array(
		'title'       => __( 'Shipping details', 'express-checkout-paypal-payment-gateway-for-woocommerce' ),
		'type'        => 'checkbox',
        'class' => 'express_toggle_display',
		'label'       => __( 'Enable to send shipping details to PayPal instead of billing.', 'express-checkout-paypal-payment-gateway-for-woocommerce' ),
        'default'     => 'no',
        'description' => __('PayPal allows us to send only one among shipping/billing addresses. We advise you to validate PayPal Seller protection to send shipping details to PayPal.', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'desc_tip'    => false,
	),
   
    'skip_review' => array(
        'title' => __('Skip review page', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'type' => 'checkbox',
        'class' => 'express_toggle_display',
        'label' => __('Enable', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'default' => 'yes',
        'description' => __('Enable to skip the review page (Customers returned from PayPal will be presented with a final review page which includes the order details) and move to the site directly.', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'desc_tip' => true
    ),
    'policy_notes' => array(
        'title' => __('Seller policy', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'type' => 'textarea',
        'class' => 'express_toggle_display',
        'css' => 'width:25em',
        'description' => __('Enter the seller protection policy or customized text which will be displayed in order review page.', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'default' => __('You are Protected by ' . get_bloginfo('name', 'display') . ' Policy', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'desc_tip' => true
    ),


    'smart_button_prerequesties' => array(
        'type' => 'title',
        'description' => sprintf("<div class='eh_wt_info_div smart_button_toggle_display' id='wt_info_div_smart'><p><b>".__( 'Pre-requisites:','express-checkout-paypal-payment-gateway-for-woocommerce' )."</b></p><p> ".__( 'Requires a PayPal Developer account and Business account linked with confirmed identity, email, and bank account.','express-checkout-paypal-payment-gateway-for-woocommerce' )."</p><p> ".__( 'To get the Client ID and Secret:','express-checkout-paypal-payment-gateway-for-woocommerce' )."</p><ul class='eh_wt_notice_bar_style'><li> ".__( 'Login to your PayPal business account.','express-checkout-paypal-payment-gateway-for-woocommerce' )." </li> <li>".__( 'Go to Activity at the top of the page and choose API Access.','express-checkout-paypal-payment-gateway-for-woocommerce' )."</li> <li>".__( 'Scroll down to the REST API Integration section and click Manage API apps and credentials. The REST API apps window opens up.','express-checkout-paypal-payment-gateway-for-woocommerce' )."</li> <li>".__( 'Click on the PayPal Developer experience link to create or manage apps.','express-checkout-paypal-payment-gateway-for-woocommerce' )."</li><li>".__( 'Next, Login to developer account or Signup a new developer account. Click on the  create a new application or default application for your business account. Then, copy the Client ID and Secret.','express-checkout-paypal-payment-gateway-for-woocommerce' )."</li><li>".__( 'Paste the Client ID and Secret in the respective fields of the plugin.','express-checkout-paypal-payment-gateway-for-woocommerce' )."</li> </ul></div><p class='smart_button_toggle_display'><a target='_blank' href='https://www.webtoffee.com/paypal-express-checkout-payment-gateway-woocommerce-user-guide/'>  ".__('Read documentation', 'express-checkout-paypal-payment-gateway-for-woocommerce')." </a></p>"),
    ),
    'smart_button_credentials_title' => array(
        'title' => sprintf(__('PayPal Credentials', 'express-checkout-paypal-payment-gateway-for-woocommerce')),
        'type' => 'title',
        'class' => 'smart_button_toggle_display',
        'description' => __('<span class="smart_button_toggle_display">Select Live mode to accept payments and Sandbox mode to test payments.</span>', 'express-checkout-paypal-payment-gateway-for-woocommerce') 
    ),
    'smart_button_environment' => array(
        'title' => __('Environment', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'type' => 'select',
        'css' => 'display:none',
        'class' => 'wc-enhanced-select smart_button_toggle_display',
        'options' => array(
            'sandbox' => __('Sandbox mode', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
            'live' => __('Live mode', 'express-checkout-paypal-payment-gateway-for-woocommerce')
        ),
        'description' => sprintf(__('<div id="environment_alert_desc "></div>', 'express-checkout-paypal-payment-gateway-for-woocommerce')),
        'default' => 'sandbox',
        'desc_tip' => __('Choose Sandbox mode to test payment using test API keys. Switch to live mode to accept payments with PayPal using live API keys.', 'express-checkout-paypal-payment-gateway-for-woocommerce')
    ),
    'sandbox_client_id' => array(
        'title' => __('Sandbox client ID', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'type' => 'text',
        'class' => 'smart_button_toggle_display',
        'default' => '',
    ),
    'sandbox_client_secret' => array(
        'title' => __('Sandbox client secret', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'type' => 'password',
        'class' => 'smart_button_toggle_display',
        'default' => '',
    ),
    'live_client_id' => array(
        'title' => __('Live client ID', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'type' => 'text',
        'class' => 'smart_button_toggle_display',
        'default' => '',
    ),
    'live_client_secret' => array(
        'title' => __('Live client secret', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'type' => 'password',
        'class' => 'smart_button_toggle_display',
        'default' => '',
    ),

    'smart_button_gateway_title' => array(
        'type' => 'title',
        'class'=> 'eh-css-class smart_button_toggle_display'
    ),

    'smart_button_title' => array(
        'title' => __('Title', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'type' => 'text',
        'class' => 'smart_button_toggle_display',
        'description' => __('Input title for the payment gateway displayed at the checkout.', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'default' => __('PayPal', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'desc_tip' => true,
    ),
    'smart_button_gateway_description' => array(
        'title' => __('Description', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'type' => 'textarea',
        'class' => 'smart_button_toggle_display',
        'css' => 'width:25em',
        'description' => __('Input description for the payment gateway displayed at the checkout.', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'default' => __('Secure payment via PayPal.', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'desc_tip' => true
    ),
    
    'smart_button_display_title' => array(
        'title' => sprintf(__('PayPal Smart Button', 'express-checkout-paypal-payment-gateway-for-woocommerce')),
        'type' => 'title',
        'class'=> 'eh-css-class smart_button_toggle_display',
        'description' => __('<span class="smart_button_toggle_display">PayPal Smart Payment Buttons gives merchants different ways to customize size, color, and shape of PayPal checkout button, as well as for other, multiple alternative payment methods such as PayPal Credit, Venmo, and local funding sources.</span>', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
    ),
   
    'smart_button_on_pages' => array(
        'title' => __('Show Smart button on', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'type' => 'multiselect',
        'class' => 'chosen_select smart_button_toggle_display',
        'css' => 'width: 350px;',
        'desc_tip' => __('Displays PayPal smart button on chosen pages.', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'options' => array(
            'cart' => 'Cart page',
            'checkout' => 'Checkout page'
        ),
        'default' => array(
            $cart,
            $checkout
        )
    ),
    'smart_button_description' => array(
        'title' => __('Description', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'type' => 'textarea',
        'css' => 'width:25em',
        'class' => 'smart_button_toggle_display',
        'description' => __('Input description displayed above the PayPal Smart button.', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'default' => __('Reduce the number of clicks with PayPal Express.', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'desc_tip' => true
    ),
    'smart_button_size' => array(
        'title' => __('Size', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'type' => 'select',
        'class' => 'wc-enhanced-select smart_button_toggle_display',
        'description' => __('Choose the size of the button as either small, medium, large or responsive.', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'default' => 'responsive',
        'desc_tip' => true,
        'options' => array(
            'small' => __('Small', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
            'medium' => __('Medium', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
            'large' => __('Large', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
            'responsive' => __('Responsive', 'express-checkout-paypal-payment-gateway-for-woocommerce')
        )
    ), 
   'button_label' => array(
        'title' => __('Button label', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'type' => 'select',
        'class' => 'wc-enhanced-select smart_button_toggle_display',
        'description' => __('Choose a pre-defined button label provided by PayPal.', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'default' => 'checkout',
        'desc_tip' => true,
        'options' => array(
            'checkout' => __('Checkout', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
            'pay' => __('Pay', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
            'buynow' => __('Buy Now', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
            'paypal' => __('PayPal', 'express-checkout-paypal-payment-gateway-for-woocommerce')
        )
    ),
   'button_tagline' => array(
        'title' => __('Tagline', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'type' => 'select',
        'class' => 'wc-enhanced-select smart_button_toggle_display',
        'description' => __('Choose whether to show or hide the button tagline.', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'default' => true,
        'desc_tip' => true,
        'options' => array(
            'show' => __('Show', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
            'hide' => __('Hide', 'express-checkout-paypal-payment-gateway-for-woocommerce')
        )
    ),
    'button_color' => array(
        'title' => __('Button color', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'type' => 'select',
        'class' => 'wc-enhanced-select smart_button_toggle_display',
        'description' => __('Choose the color of the smart checkout button.', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'default' => 'gold',
        'desc_tip' => true,
        'options' => array(
            'gold' => __('Gold', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
            'blue' => __('Blue', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
            'silver' => __('Silver', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
            'white' => __('White', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
            'black' => __('Black', 'express-checkout-paypal-payment-gateway-for-woocommerce')
        )
    ), 
 
    'button_shape' => array(
        'title' => __('Shape', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'type' => 'select',
        'class' => 'wc-enhanced-select smart_button_toggle_display',
        'description' => __('Choose the button shape as either Rect (squared) or Pill (rounded).', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'default' => 'rect',
        'desc_tip' => true,
        'options' => array(
            'rect' => __('Rect', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
            'pill' => __('Pill', 'express-checkout-paypal-payment-gateway-for-woocommerce')
        )
    ),
 
    'button_layout' => array(
        'title' => __('Layout', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'type' => 'select',
        'class' => 'wc-enhanced-select smart_button_toggle_display',
        'description' => __('Choose the button layout as either Vertical or Horizontal.', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'default' => 'rect',
        'desc_tip' => true,
        'options' => array(
            'vertical' => __('Vertical', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
            'horizontal' => __('Horizontal', 'express-checkout-paypal-payment-gateway-for-woocommerce')
        )
    ),

    'disable_funding_source' => array(
        'title' => __('Disable funding sources', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'type' => 'multiselect',
        'class' => 'wc-enhanced-select smart_button_toggle_display',
        'description' => __('Disable a funding source by choosing it from the available options. <br>Note: The eligible funding sources are shown by PayPal on the checkout based on buyer country or devices.', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'default' => '',
        'desc_tip' => "Selected funding sources are not displayed in the smart payment buttons.",
        'options' => array(
            'card' => __('Credit or debit cards', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
            'credit' => __('PayPal Credit', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
            'bancontact' => __('Bancontact', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
            'blik' => __('BLIK', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
            'eps' => __('eps', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
            'giropay' => __('giropay', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
            'ideal' => __('iDEAL', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
            'mercadopago' => __('Mercado Pago', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
            'mybank' => __('MyBank', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
            'p24' => __('Przelewy24', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
            'sepa' => __('SEPA-Lastschrift', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
            'sofort' => __('Sofort', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
            'venmo' => __('Venmo', 'express-checkout-paypal-payment-gateway-for-woocommerce')
        )
    ),

     'smart_button_abilities_title' => array(
        'title' => sprintf(__('Branding', 'express-checkout-paypal-payment-gateway-for-woocommerce')),
        'type' => 'title',
        'class'=> 'eh-css-class smart_button_toggle_display',
        'description' => sprintf(__('<span class="smart_button_toggle_display"> Set your brand identity at the PayPal end by giving a brand name. It will be visible for customers on the PayPal site on choosing to pay via PayPal.</span>', 'express-checkout-paypal-payment-gateway-for-woocommerce')),
    ),
    'smart_button_business_name' => array(
        'title' => __('Brand name', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'type' => 'text',
        'class' => 'smart_button_toggle_display',
        'description' => __('Input the name of your store that will appear on the PayPal end.', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'default' => __(get_bloginfo('name', 'display'), 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'desc_tip' => true,
    ),
    
    'smart_button_landing_page' => array(
        'title' => __('Landing page', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'type' => 'select',
        'class' => 'wc-enhanced-select smart_button_toggle_display',
        'options' => array(
            'login' => __('Login', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
            'billing' => __('Billing', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        ),
        'description' => sprintf(__('Customers will be redirected to the chosen page. By default, the billing page is taken.', 'express-checkout-paypal-payment-gateway-for-woocommerce')),
        'default' => 'billing',
        'desc_tip' => true,
    ),

    'smart_button_paypal_locale' => array(
        'title' => __('PayPal locale', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'type' => 'checkbox',
        'class' => 'class_locale smart_button_toggle_display',
        'label' => __('Use store locale', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'default' => 'yes',
        'description' => __('Choose to set the PayPal locale same as the store locale.', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'desc_tip' => true,
    ),
    'smart_button_advanced_option_title' => array(
        'title' => sprintf(__('Advanced Options', 'express-checkout-paypal-payment-gateway-for-woocommerce')),
        'type' => 'title',
        'class'=> 'eh-css-class smart_button_toggle_display'
    ),
    'smart_button_invoice_prefix' => array(
        'title' => __('Invoice prefix', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'type' => 'text',
        'class' => 'smart_button_toggle_display',
        'description' => __('Enter an invoice prefix to identify transactions from your site.', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'default' => __('', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'desc_tip' => true,
        'placeholder' => 'EH_',
    ),
    'smart_button_paypal_allow_override' => array(
        'title' => __('Address override', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'type' => 'checkbox',
        'class' => 'smart_button_toggle_display',
        'label' => __('Enable to prevent checkout address being changed at the PayPal end.', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'default' => 'no',
        'description' => __('Enabling this will affect Express checkout and PayPal will strictly verify the address.', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'desc_tip' => false,
    ),
    
    'smart_button_send_shipping'   => array(
        'title'       => __( 'Shipping details', 'express-checkout-paypal-payment-gateway-for-woocommerce' ),
        'type'        => 'checkbox',
        'class'        => 'smart_button_toggle_display',
        'label'       => __( 'Enable to send shipping details to PayPal instead of billing.', 'express-checkout-paypal-payment-gateway-for-woocommerce' ),
        'default'     => 'no',
        'description' => __('PayPal allows us to send only one among shipping/billing addresses. We advise you to validate PayPal Seller protection to send shipping details to PayPal.', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'desc_tip'    => false,
    ),
    'smart_button_skip_review' => array(
        'title' => __('Skip review page', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'type' => 'checkbox',
        'class' => 'smart_button_toggle_display',
        'label' => __('Enable', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'default' => 'yes',
        'description' => __('Enable to skip the review page(Customers returned from PayPal will be presented with a final review page which includes the order details) and move to the site directly.', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'desc_tip' => true,
    ),
    'smart_button_policy_notes' => array(
        'title' => __('Seller policy', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'type' => 'textarea',
        'class' => 'smart_button_toggle_display',
        'css' => 'width:25em',
        'description' => __('Enter the seller protection policy or customized text which will be displayed in order review page.', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'default' => __('You are Protected by ' . get_bloginfo('name', 'display') . ' Policy', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'desc_tip' => true,
    ),



    'log_title' => array(
        'title' => sprintf(__('Debug Logs', 'express-checkout-paypal-payment-gateway-for-woocommerce')),
        'type' => 'title',
        'class'=> 'eh-css-class',
        'description' => sprintf(__('Records PayPal payment transactions into WooCommerce status log. <a href="' . admin_url("admin.php?page=wc-status&tab=logs") . '" target="_blank"> View log </a>', 'express-checkout-paypal-payment-gateway-for-woocommerce'))
    ),
    'paypal_logging' => array(
        'title' => __(' Log', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'label' => __('Enable', 'express-checkout-paypal-payment-gateway-for-woocommerce'),
        'type' => 'checkbox',
        'description' => sprintf(__('<span style="color:green">Log File</span>: ' . strstr(wc_get_log_file_path('eh_paypal_express_log'), 'eh_paypal_express_log') . ' ( ' . $file_size . ' ) ', 'express-checkout-paypal-payment-gateway-for-woocommerce')),
        'default' => 'yes',
        'desc_tip' => __(' Enable to record PayPal payment transactions in a log file.', 'express-checkout-paypal-payment-gateway-for-woocommerce')
    ),
    
);

