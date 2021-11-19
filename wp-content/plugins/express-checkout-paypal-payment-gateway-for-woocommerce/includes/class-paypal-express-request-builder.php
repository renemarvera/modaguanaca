<?php
if (!defined('ABSPATH')) {
    exit;
}
class Eh_PE_Request_Built
{
    protected $params=array();
    const version="115";
    public $supported_decimal_currencies = array('HUF', 'JPY', 'TWD');
    public $store_currency;
    public $http_version;
    function __construct($username, $password, $signature)
    {
        $this->store_currency = get_woocommerce_currency();
        $this->make_params(array(
            'USER'      => $username,
            'PWD'       => $password,
            'SIGNATURE' => $signature,
            'VERSION'   => self::version
        ));
        $this->http_version = '1.1';
    }
    public function make_request_params(array $args)
    {
        $this->make_params
                (
                    array
                    (
                        'METHOD'                => $args['method'],
                        'RETURNURL'             => $args['return_url'],
                        'CANCELURL'             => $args['cancel_url'],
                        'ADDROVERRIDE'          => $args['address_override'],
                        'BRANDNAME'             => $args['business_name'],
                        'HDRIMG'                => $args['banner'],
                        'LOGOIMG'               => $args['logo'],
                        'SOLUTIONTYPE'          => 'Sole',
                        // 'CUSTOMERSERVICENUMBER' => $args['customerservicenumber'],
                        'LOCALECODE'            => $args['localecode'],
                    )
                );
        if($args['credit'])
        {
            $this->make_params
                (
                    array
                    (
                        'USERSELECTEDFUNDINGSOURCE' => 'BML',
                        'LANDINGPAGE'               => 'Billing',
                    )
                );
        }
        else
        {
            $this->make_params
                (
                    array
                    (
                        'LANDINGPAGE' => $args['landing_page']
                    )
                );
        }
        $i=0;
        if (!defined('WOOCOMMERCE_CART')) {
            define('WOOCOMMERCE_CART', true);
        }

        //if order processed from pay for order page set line items from order details as cart doesn't contains product details
        if($args['pay_for_order']){
            
            $order_id = $args['order_id'];
            $order    = wc_get_order($order_id);
            $this->order_item_params($order);

            $this->add_payment_params
                    (                
                        array
                        (
                            'SHIPTONAME'        =>  $order->get_shipping_first_name().' '.$order->get_shipping_last_name(),
                            'SHIPTOSTREET'      =>  $order->get_shipping_address_1(),
                            'SHIPTOSTREET2'     =>  $order->get_shipping_address_2(),
                            'SHIPTOCITY'        =>  $order->get_shipping_city(),
                            'SHIPTOSTATE'       =>  $order->get_shipping_state(),
                            'SHIPTOZIP'         =>  $order->get_shipping_postcode(),
                            'SHIPTOCOUNTRYCODE' =>  $order->get_shipping_country(),
                            'SHIPTOPHONENUM'    =>  $order->get_billing_phone(),
                            'EMAIL'             =>  $order->get_billing_email(),
                            'PAYMENTREQUESTID'  =>  $args['order_id'],
                            
                        )
                    );

        }else{

            WC()->cart->calculate_totals();

            //when checkout using express button some fee details are not saved in order
            $order = wc_get_order($args['order_id']);
            if(!empty(WC()->cart->get_fees()) && (count($order->get_fees()) != count(WC()->cart->get_fees()))){
                
                foreach( $order->get_items( 'fee' ) as $item_id => $fee_obj ){
                        
                    $fee_item_data = $fee_obj->get_data();
                    $fee_data_id = $fee_item_data['id'];

                    $order->remove_item($fee_data_id);
                }

                //adding fee line item to order
                foreach ( WC()->cart->get_fees() as $fee_key => $fee ) {
                    $item                 = new WC_Order_Item_Fee();
                    $item->legacy_fee     = $fee; 
                    $item->legacy_fee_key = $fee_key; 
                    $item->set_props(
                        array(
                            'name'      => $fee->name,
                            'tax_class' => $fee->taxable ? $fee->tax_class : 0,
                            'amount'    => $fee->amount,
                            'total'     => $fee->total,
                            'total_tax' => $fee->tax,
                            'taxes'     => array(
                                'total' => $fee->tax_data,
                            ),
                        )
                    );
        
                    // Add item to order and save.
                    $order->add_item( $item );
                    $order->save();
                    $order->calculate_totals();
                }
            }

            $cart_item=wc()->cart->get_cart();

            $line_item_total_amount = 0;

            $wt_skip_line_items = $this->wt_skip_line_items(); // if tax enabled and when product has inclusive tax  
            foreach ($cart_item as $item) 
            {
                $cart_product       = $item['data'];
                $line_item_title    = $cart_product->get_title();
                $desc_temp          = array();
                foreach ($item['variation'] as $key => $value) 
                {
                    $desc_temp[]    = wc_attribute_label(str_replace('attribute_','',$key)).' : '.$value;
                }
                $line_item_desc     = implode(', ', $desc_temp);
                $line_item_url      = $cart_product->get_permalink();
                
                if( $wt_skip_line_items ){   // if tax enabled and when product has inclusive tax  
                    
                    $this->add_line_items
                        (
                            array
                            (
                                'NAME'      => $line_item_title.' x '.$item['quantity'],
                                'DESC'      => $line_item_desc,
                                'AMT'       => $this->make_paypal_amount($item['line_subtotal']),
                                'ITEMURL'   => $line_item_url
                            ),
                            $i++
                        );

                        $line_item_total_amount  = $line_item_total_amount + $this->make_paypal_amount($item['line_subtotal']);
                    
                }else{
                    
                    $line_item_quan     = $item['quantity'];
                    $line_item_total    = $item['line_subtotal']/$line_item_quan;
                    $this->add_line_items
                            (
                                array
                                (
                                    'NAME'      => $line_item_title,
                                    'DESC'      => $line_item_desc,
                                    'AMT'       => $this->make_paypal_amount($line_item_total),
                                    'QTY'       => $line_item_quan,
                                    'ITEMURL'   => $line_item_url
                                ),
                                $i++
                            );

                            $total_amount = ($line_item_quan * $this->make_paypal_amount($line_item_total));

                        $line_item_total_amount  = $line_item_total_amount + $total_amount;
                    
                }
                
                
            }
            if (WC()->cart->get_cart_discount_total() > 0) 
            {
                $discount_amount = $this->make_paypal_amount(WC()->cart->get_cart_discount_total());
                $this->add_line_items
                        (
                            array
                            (
                                'NAME'  => 'Discount',
                                'DESC'  => implode(', ', wc()->cart->get_applied_coupons()),
                                'QTY'   => 1,
                                'AMT'   => - $discount_amount,
                            ),
                            $i++
                        );

                    $line_item_total_amount  = $line_item_total_amount - $discount_amount;
            }

            //add fee to cart line items
            foreach ( WC()->cart->get_fees() as $fee_key => $fee_values ) {

                $this->add_line_items
                (
                    array
                    (
                        'NAME'  => $fee_values->name,
                        'DESC'  => '',
                        'QTY'   => 1,
                        'AMT'   => $this->make_paypal_amount( $fee_values->total),
                    ),
                    $i++
                );

                $line_item_total_amount  = $line_item_total_amount + $this->make_paypal_amount( $fee_values->total);
                
            }

            //add line items amount and compares it with cart total amount to check for any total mismatch
            $item_amount = $this->make_paypal_amount(WC()->cart->cart_contents_total + WC()->cart->fee_total);

            if($line_item_total_amount != $item_amount){
                $diff = $this->make_paypal_amount( $item_amount - $line_item_total_amount);
                if ( abs( $diff ) > 0.000001 && 0.0 !== (float) $diff ) {
                    //add extra line item if there is a total mismatch
                    $this->add_line_items
                            (
                                array
                                (
                                    'NAME'  => 'Extra line item',
                                    'DESC'  => '',
                                    'QTY'   => 1,
                                    'AMT'   => $diff,
                                ),
                                $i++
                            );
                }
            }

            //handle mismatch due to rounded tax calculation
            $ship_discount_amount = 0;
            $cart_total = $this->make_paypal_amount(WC()->cart->total);
            $cart_tax = $this->make_paypal_amount(WC()->cart->tax_total + WC()->cart->shipping_tax_total);
            $cart_items_total = $item_amount + $this->make_paypal_amount(WC()->cart->shipping_total) + $cart_tax;
            if($cart_total != $cart_items_total){
                if($cart_items_total < $cart_total){
                    $cart_tax += $cart_total - $cart_items_total;
                }else{
                    $ship_discount_amount += $this->make_paypal_amount($cart_total - $cart_items_total);
                }
            }

            $this->add_payment_params
                    (
                        array
                        (
                            'AMT'                   => $cart_total,
                            'CURRENCYCODE'          => $this->store_currency,
                            'ITEMAMT'               => $item_amount,
                            'SHIPPINGAMT'           => $this->make_paypal_amount(WC()->cart->shipping_total),
                            'TAXAMT'                => $cart_tax,
                            'SHIPDISCAMT'           => $ship_discount_amount,
                            'PAYMENTACTION'         => 'Sale'
                        )
                    );
            $this->make_param('MAXAMT',$this->make_paypal_amount(WC()->cart->total + ceil(WC()->cart->total * 0.75)));
            $eh_paypal_express_options = get_option('woocommerce_eh_paypal_express_settings');
            $need_shipping = $eh_paypal_express_options['send_shipping'];
            if(($need_shipping === 'yes') && (isset(WC()->session->post_data['ship_to_different_address'])) && ( WC()->session->post_data['ship_to_different_address'] == 1)){ 
                
                $this->add_payment_params
                    (                
                        array
                        (
                            'SHIPTONAME'        =>  (empty(WC()->session->post_data['shipping_first_name']) ? '' :WC()->session->post_data['shipping_first_name']) .' '.(empty(WC()->session->post_data['shipping_last_name']) ? '': WC()->session->post_data['shipping_last_name'] ),
                            'SHIPTOSTREET'      =>  empty(WC()->session->post_data['shipping_address_1'])   ? '' : wc_clean(WC()->session->post_data['shipping_address_1']) ,
                            'SHIPTOSTREET2'     =>  empty(WC()->session->post_data['shipping_address_2'])   ? '' : wc_clean(WC()->session->post_data['shipping_address_2']),
                            'SHIPTOCITY'        =>  empty(WC()->session->post_data['shipping_city'])        ? '' : wc_clean(WC()->session->post_data['shipping_city']),
                            'SHIPTOSTATE'       =>  empty(WC()->session->post_data['shipping_state'])       ? '' : wc_clean(WC()->session->post_data['shipping_state']),
                            'SHIPTOZIP'         =>  empty(WC()->session->post_data['shipping_postcode'])    ? '' : wc_clean(WC()->session->post_data['shipping_postcode']),
                            'SHIPTOCOUNTRYCODE' =>  empty(WC()->session->post_data['shipping_country'])     ? '' : wc_clean(WC()->session->post_data['shipping_country']),
                            'SHIPTOPHONENUM'    =>  empty(WC()->session->post_data['billing_phone'])        ? '' : wc_clean(WC()->session->post_data['billing_phone']),
                            'NOTETEXT'          =>  empty(WC()->session->post_data['order_comments'])       ? '' : wc_clean(WC()->session->post_data['order_comments']),
                            'EMAIL'             =>  empty(WC()->session->post_data['billing_email'])        ? '' : wc_clean(WC()->session->post_data['billing_email']),
                            'PAYMENTREQUESTID'  =>  $args['order_id'],
                            
                        )
                    );
            }
            else{ 

                $this->add_payment_params
                    (                
                        array
                        (

                            'SHIPTONAME'        =>  (empty(WC()->session->post_data['billing_first_name']) ? ((( WC()->version < '2.7.0' ) ? WC()->session->post_data['billing_first_name'] : WC()->customer->get_billing_first_name()).' '.(( WC()->version < '2.7.0' ) ? WC()->session->post_data['billing_last_name'] : WC()->customer->get_billing_last_name())) : WC()->session->post_data['billing_first_name']) .' '.(empty(WC()->session->post_data['billing_last_name']) ? '': WC()->session->post_data['billing_last_name'] ),
                            'SHIPTOSTREET'      =>  empty(WC()->session->post_data['billing_address_1'])   ? (( WC()->version < '2.7.0' ) ? WC()->customer->get_address() : WC()->customer->get_billing_address())           : wc_clean(WC()->session->post_data['billing_address_1']) ,
                            'SHIPTOSTREET2'     =>  empty(WC()->session->post_data['billing_address_2'])   ? (( WC()->version < '2.7.0' ) ? WC()->customer->get_address_2() : WC()->customer->get_billing_address_2())       : wc_clean(WC()->session->post_data['billing_address_2']),
                            'SHIPTOCITY'        =>  empty(WC()->session->post_data['billing_city'])        ? (( WC()->version < '2.7.0' ) ? WC()->customer->get_city() : WC()->customer->get_billing_city())                 : wc_clean(WC()->session->post_data['billing_city']),
                            'SHIPTOSTATE'       =>  empty(WC()->session->post_data['billing_state'])       ? (( WC()->version < '2.7.0' ) ? WC()->customer->get_state() : WC()->customer->get_billing_state())               : wc_clean(WC()->session->post_data['billing_state']),
                            'SHIPTOZIP'         =>  empty(WC()->session->post_data['billing_postcode'])    ? (( WC()->version < '2.7.0' ) ? WC()->customer->get_postcode() : WC()->customer->get_billing_postcode())         : wc_clean(WC()->session->post_data['billing_postcode']),
                            'SHIPTOCOUNTRYCODE' =>  empty(WC()->session->post_data['billing_country'])     ? (( WC()->version < '2.7.0' ) ? WC()->customer->get_country() : WC()->customer->get_billing_country())           : wc_clean(WC()->session->post_data['billing_country']),
                            'SHIPTOPHONENUM'    =>  empty(WC()->session->post_data['billing_phone'])       ? (( WC()->version < '2.7.0' ) ? WC()->session->post_data['billing_phone'] : WC()->customer->get_billing_phone()) : wc_clean(WC()->session->post_data['billing_phone']),
                            'NOTETEXT'          =>  empty(WC()->session->post_data['order_comments'])      ? '' : wc_clean(WC()->session->post_data['order_comments']),
                            'EMAIL'             =>  empty(WC()->session->post_data['billing_email'])       ? (( WC()->version < '2.7.0' ) ? WC()->session->post_data['billing_email'] : WC()->customer->get_billing_email()) : wc_clean(WC()->session->post_data['billing_email']),
                            'PAYMENTREQUESTID'  => $args['order_id'],

                        )
                    );
            }
        }

        // $this->add_payment_params
        // (
        //     array
        //     (
        //         'NOTIFYURL'         => $args['notify_url'],
        //     )
        // );

        $this->params = apply_filters('wt_paypal_request_params', $this->params);
        Eh_PayPal_Log::log_update($this->params,'Setting Express Checkout');
        return $this->get_params();
    }
    public function get_checkout_details(array $args)
    {
        $this->make_params($args);
        $this->params = apply_filters('wt_paypal_request_params', $this->params);
        Eh_PayPal_Log::log_update($this->params,'Getting Express Checkout Details');
        return $this->get_params();
    }
    public function finish_request_params(array $args,$order)
    {
        $this->make_params
                (
                    array
                    (
                        'METHOD'        => $args['method'],
                        'TOKEN'         => $args['token'],
                        'PAYERID'       => $args['payer_id'],
                        'BUTTONSOURCE'  => $args['button']
                    )
                );
       
        $this->order_item_params($order);
        $order_id = (WC()->version < '2.7.0')?$order->id:$order->get_id();

        $this->add_payment_params
                (
                    array
                    (
                        'INVNUM'            => (!empty($args['invoice_prefix']))?$args['invoice_prefix'].$order_id:$order_id,
                        'PAYMENTREQUESTID'  => $order_id,
                        // 'NOTIFYURL'         => $args['notify_url'],
                    )
                );
        $this->params = apply_filters('wt_paypal_request_params', $this->params);
        Eh_PayPal_Log::log_update($this->params,'Processing Express Checkout');
        return $this->get_params();
    }

    public function order_item_params($order){
        
        $order_item=$order->get_items( array( 'line_item', 'fee' ) );
        $i=0;

        //gets fee total amount
        $total_fee = 0;
		$fees  = $order->get_fees();
		foreach ( $fees as $fee ) {
			$total_fee = $total_fee + $fee->get_amount();
        }
        
        $line_item_total_amount = 0;

        $currency=(WC()->version < '2.7.0')?$order->get_order_currency():$order->get_currency();
        $order_id = (WC()->version < '2.7.0')?$order->id:$order->get_id();
        $wt_skip_line_items = $this->wt_skip_line_items(); // if tax enabled and when product has inclusive tax  
        foreach ($order_item as $item)
        {

            //add fee details to order line items
            if ( 'fee' === $item['type'] ) {

                $this->add_line_items
                    (
                        array
                        (
                            'NAME'      => $item['name'],
                            'DESC'      => 'Fee',
                            'AMT'       => $this->make_paypal_amount($item['line_total']),                            
                            'QTY'       => 1,
                        ),
                        $i++
                    );
                    $line_item_total_amount = $line_item_total_amount + $this->make_paypal_amount($item['line_total']);

			} else {

                $line_item_title    = $item['name'];
                $desc_temp          = array();
                foreach ($item as $key => $value) 
                {
                    if(strstr($key, 'pa_'))
                    {
                        $desc_temp[] = wc_attribute_label($key).' : '.$value;
                    }
                }
                $line_item_desc     = implode(', ', $desc_temp);
                $line_item_quan     = $item['qty'];
                $line_item_total    = $item['line_subtotal']/$line_item_quan;
                
                if($wt_skip_line_items){
                    $this->add_line_items
                        (
                            array
                            (
                                'NAME'      => $line_item_title.' x '.$item['quantity'],
                                'DESC'      => $line_item_desc,
                                'AMT'       => $this->make_paypal_amount($item['line_subtotal'],$currency),
                            ),
                            $i++
                        );

                        $line_item_total_amount = $line_item_total_amount + $this->make_paypal_amount($item['line_subtotal'],$currency);
                    
                }else{
                    $this->add_line_items
                        (
                            array
                            (
                                'NAME'      => $line_item_title,
                                'DESC'      => $line_item_desc,
                                'AMT'       => $this->make_paypal_amount($line_item_total,$currency),
                                'QTY'       => $line_item_quan,
                            ),
                            $i++
                        );
                        $total_amount = ($line_item_quan * $this->make_paypal_amount($line_item_total,$currency));
                        $line_item_total_amount = $line_item_total_amount + $total_amount;
                }
            }
            
        }
        if ($order->get_total_discount() > 0) 
        {
            $this->add_line_items
                    (
                        array
                        (
                            'NAME'  => 'Discount',
                            'DESC'  => implode(', ', $order->get_used_coupons()),
                            'QTY'   => 1,
                            'AMT'   => - $this->make_paypal_amount($order->get_total_discount()),
                        ),
                        $i++
                    );
                    $line_item_total_amount = $line_item_total_amount - $this->make_paypal_amount($order->get_total_discount());
        }

        //add line items amount and compares it with order total amount to check for any total mismatch
        $order_item_total = $this->make_paypal_amount($order->get_subtotal()-$order->get_total_discount() + $total_fee,$currency);

        if($line_item_total_amount != $order_item_total){
            $diff = $this->make_paypal_amount( $order_item_total - $line_item_total_amount);
            if ( abs( $diff ) > 0.000001 && 0.0 !== (float) $diff ) {
                //add extra line item if there is a total mismatch
                $this->add_line_items
                        (
                            array
                            (
                                'NAME'  => 'Extra line item',
                                'DESC'  => '',
                                'QTY'   => 1,
                                'AMT'   => $diff,
                            ),
                            $i++
                        );
            }
        }

        //handle mismatch due to rounded tax calculation
        $ship_discount_amount = 0;
        $order_total = $this->make_paypal_amount($order->get_total(),$currency);
        $order_tax = $this->make_paypal_amount($order->get_total_tax(),$currency);
        $order_items_total = $order_item_total + $this->make_paypal_amount($order->get_total_shipping(),$currency) + $order_tax;
        if($order_total != $order_items_total){
            if($order_items_total < $order_total){
                $order_tax += $order_total - $order_items_total;
            }else{
                $ship_discount_amount += $this->make_paypal_amount($order_total - $order_items_total);
            }
        }

        $this->add_payment_params
                (
                    array
                    (
                        'AMT'               => $order_total,
                        'CURRENCYCODE'      => $currency,
                        'ITEMAMT'           => $order_item_total,                                                
                        'SHIPPINGAMT'       => $this->make_paypal_amount($order->get_total_shipping(),$currency),
                        'TAXAMT'            => $order_tax,
                        'SHIPDISCAMT'       => $ship_discount_amount,
                        'PAYMENTACTION'     => 'Sale',
                    )
                );
    }

    public function make_capture_params($args)
    {
        $this->make_params
                (
                    array
                    (
                        'METHOD'            => $args['method'],
                        'AUTHORIZATIONID'   => $args['auth_id'],
                        'AMT'               => $this->make_paypal_amount($args['amount'],$args['currency']),
                        'CURRENCYCODE'      => $args['currency'],
                        'COMPLETETYPE'      => $args['type']
                    )
                );
        Eh_PayPal_Log::log_update($this->params,'Capture Express Checkout');
        return $this->get_params();
    }
    public function make_refund_params($args)
    {
        $this->make_params
                (
                    array
                    (
                        'METHOD'            => $args['method'],
                        'TRANSACTIONID'     => $args['auth_id'],
                        'AMT'               => $this->make_paypal_amount($args['amount'],$args['currency']),
                        'CURRENCYCODE'      => $args['currency'],
                        'REFUNDTYPE'        => $args['type']
                    )
                );
        Eh_PayPal_Log::log_update($this->params,'Refund Express Checkout');
        return $this->get_params();
    }
    public function query_params()
    {
        foreach ($this->params as $key => $value) 
        {
            if ('' === $value || is_null($value)) {
                unset($this->params[$key]);
            }
            if (false !== strpos($key, 'AMT')) 
            {
                /*  Commented for PECPGFW-154 Unable to checkout with product price 7999
                if (isset($this->params['PAYMENTREQUEST_0_CURRENCYCODE']) && 'USD' == $this->params['PAYMENTREQUEST_0_CURRENCYCODE'] && $value > 10000)
                {
                    wc_add_notice(sprintf('%1$s amount of $%2$s must be less than $10,000.00', 'PayPal Amount', $value), 'error');
                    wp_redirect(wc_get_cart_url());
                    exit;
                } */
                $this->params[$key] = number_format($value, 2, '.', '');
            }
        }        
        return $this->params;
    }
    public function get_params() {
        $args = array(
            'method' => 'POST',
            'timeout' => 60,
            'redirection' => 0,
            'httpversion' => $this->http_version,
            'sslverify' => FALSE,
            'blocking' => true,
            'user-agent' => 'EH_PAYPAL_EXPRESS_CHECKOUT',
            'headers' => array(),
            'body' => http_build_query($this->query_params()),
            'cookies' => array(),
        );
        return $args;
    }
    public function make_paypal_amount($amount,$currency='')
    {
        $currency=  empty($currency)?$this->store_currency:$currency;
        if (in_array($currency, $this->supported_decimal_currencies))
        {
            return round((float) $amount, 0);
        }
        else
        {
            return round((float) $amount, 2);
        }
    }
    public function add_line_items($items,$count)
    {
        foreach ($items as $line_key => $line_value) {
            $this->make_param("L_PAYMENTREQUEST_0_{$line_key}{$count}", $line_value);
        }
    }
    public function add_payment_params($items)
    {
        foreach ($items as $item_key => $item_value) {
            $this->make_param("PAYMENTREQUEST_0_{$item_key}", $item_value);
        }
    }
    public function make_param($key,$value) {
        $this->params[$key] = $value;
    }
    public function make_params(array $args) {
        foreach ($args as $key => $value) {
            $this->params[$key] = $value;
        }
    }
    public function wt_skip_line_items() {
        return ( 'yes' === get_option('woocommerce_calc_taxes') && 'yes' === get_option('woocommerce_prices_include_tax') );         
    }
}
