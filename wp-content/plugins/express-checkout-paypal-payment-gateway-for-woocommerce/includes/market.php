<style>
    .box14{
        width: 30%;
        margin-top:2px;
        min-height: 310px;
        margin-right: 10px;
        position:absolute;
        z-index:1;
        background: -webkit-gradient(linear, 0% 20%, 0% 92%, from(#fff), to(#f3f3f3), color-stop(.1,#fff));
    }
    
    .eh-button-go-pro {
        box-shadow: none;
        border: 0;
        text-shadow: none;
        padding: 10px 15px;
        height: auto;
        font-size: 16px;
        border-radius: 4px;
        font-weight: 600;
        background: #00cb95;
        margin-top: 20px;
        text-decoration: none;
    }

    .eh-button {
        margin-bottom: 20px;
        color: #fff;
    }
    .eh-button:hover, .eh-button:visited {
        color: #fff;
    }
    .eh_gopro_block{ background: #fff; padding: 15px;}
    .eh_gopro_block h3{ text-align: center; }
    .eh_premium_features{ padding-left: 20px; }
    .eh_premium_features li{ padding-left:15px; padding-right: 15px; }
    .eh_premium_features li::before {
        font-family: dashicons;
        text-decoration: inherit;
        font-weight: 400;
        font-style: normal;
        vertical-align: top;
        text-align: center;
        content: "\f147";
        margin-right: 10px;
        margin-left: -25px;
        font-size: 16px;
        color: #3085bb;
    }
    .eh-button-documentation{
        border: 0;
        background: #d8d8dc;
        box-shadow: none;
        padding: 10px 15px;
        font-size: 15px;
        height: auto;
        margin-left: 10px;
        margin-right: 10px;
        margin-top: 10px;
        border-radius: 3px;
        text-decoration: none;
    }
    .table-box-main {
        box-shadow: 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.24);
        transition: all 0.3s cubic-bezier(.25,.8,.25,1);
    }

    .table-box-main:hover {
        box-shadow: 0 14px 28px rgba(0,0,0,0.25), 0 10px 10px rgba(0,0,0,0.22);
    }
</style>

<div class="box14 table-box-main">
<div class="eh_gopro_block">
    <p style="text-align: center;">
            <ul style="font-weight: bold; color:#666; list-style: none; background:#f8f8f8; padding:20px; margin:0px 15px; font-size: 15px; line-height: 26px;">
                <li style=""><?php echo __('30 Day Money Back Guarantee','express-checkout-paypal-payment-gateway-for-woocommerce'); ?></li>
                <li style=""><?php echo __('Fast and Superior Support','express-checkout-paypal-payment-gateway-for-woocommerce'); ?></li>
                <li style="padding-top:5px;">
                   <p style="text-align: left;">
                    <a href="https://www.webtoffee.com/product/paypal-express-checkout-gateway-for-woocommerce/?utm_source=free_plugin_sidebar&utm_medium=Paypal_basic&utm_campaign=Paypal&utm_content=<?php echo EH_PAYPAL_VERSION;?>" target="_blank" class="eh-button eh-button-go-pro"><?php echo __('Upgrade to Premium','express-checkout-paypal-payment-gateway-for-woocommerce'); ?></a>
                </p>
                </li>
            </ul>

            <ul class="eh_premium_features">
            <li><?php _e('Adds PayPal Smart Button Checkout option on individual Product Page.', 'express-checkout-paypal-payment-gateway-for-woocommerce');?></li>
            <li><?php _e('Accepts payment using multiple Alternative Payment Method (APM) based on country or device.', 'express-checkout-paypal-payment-gateway-for-woocommerce');?></li>
            <li><?php _e('Adds Express PayPal Checkout Option on Product Page and Mini-cart for Faster Checkout.', 'express-checkout-paypal-payment-gateway-for-woocommerce');?></li>
                <li><?php _e('Capture the authorized payment later.', 'express-checkout-paypal-payment-gateway-for-woocommerce');?></li>
                <li><?php _e('Partial and Full Refund the order amount directly from Order Admin Page.', 'express-checkout-paypal-payment-gateway-for-woocommerce');?></li>
                <li><?php _e('Lots of Customization Options like Button Style, Position, Etc.', 'express-checkout-paypal-payment-gateway-for-woocommerce');?></li>
                <li><?php _e('Option to enable In-Context checkout, to keep customers inside your store while checkout process.', 'express-checkout-paypal-payment-gateway-for-woocommerce');?></li> 
                <li><?php _e('Supports WooCommerce Subscriptions for Express buttons.', 'express-checkout-paypal-payment-gateway-for-woocommerce');?></li>   
                <li><?php _e('Payment gateway that allow users to pay with their credit card without leaving the site(Guest Checkout).', 'express-checkout-paypal-payment-gateway-for-woocommerce');?></li>   
                <li><?php _e('Option to set up a specific PayPal locale.', 'express-checkout-paypal-payment-gateway-for-woocommerce');?></li>   
                <li><?php _e('Shortcode support for Paypal Express button.', 'express-checkout-paypal-payment-gateway-for-woocommerce');?></li>     
                <li><?php _e('Timely compatibility updates and bug fixes.', 'express-checkout-paypal-payment-gateway-for-woocommerce');?></li>
                <li><?php _e('Premium support!', 'express-checkout-paypal-payment-gateway-for-woocommerce');?></li>     
                
        </ul>
        <br/>
    </p>
    <p style="text-align: center;">
        <a href="https://www.webtoffee.com/category/documentation/paypal-express-checkout-payment-gateway-for-woocommerce/" target="_blank" class="eh-button eh-button-documentation" style=" color: #555 !important;"><?php echo __('Documentation','express-checkout-paypal-payment-gateway-for-woocommerce'); ?></a>
    </p>
</div>

<div class="eh_gopro_block" style="margin-top: 45px;">
    <h3 style="text-align: center;"><?php echo __('Like this plugin?','express-checkout-paypal-payment-gateway-for-woocommerce'); ?></h3>
    <p><?php echo __('If you find this plugin useful please show your support and rate it','express-checkout-paypal-payment-gateway-for-woocommerce'); ?> <a href="https://wordpress.org/support/plugin/express-checkout-paypal-payment-gateway-for-woocommerce/reviews/" target="_blank" style="color: #ffc600; text-decoration: none;">★★★★★</a><?php echo __(' on','express-checkout-paypal-payment-gateway-for-woocommerce'); ?> <a href="https://wordpress.org/support/plugin/express-checkout-paypal-payment-gateway-for-woocommerce/" target="_blank">WordPress.org</a> -<?php echo __('  much appreciated!','express-checkout-paypal-payment-gateway-for-woocommerce'); ?> :)</p>

</div>
</div>

<?php
if ( is_rtl() ) {
   ?>
    <style type="text/css"> .box14 { left:0px;float:left; }</style>
    <?php
}else{
    ?>
    <style type="text/css"> .box14 { right:0px;float:right; }</style>
    <?php
}

