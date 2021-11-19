<?php
if (!defined('ABSPATH')) {
    exit;
}
/**
	*	PayPal helper class for REST API requests.
	*	
*/

class Eh_Rest_Request_Built {
	
	private $token = null;
	private $client_id = null;
	private $client_secret = null;
    protected $params=array();
    public $supported_decimal_currencies = array('HUF', 'JPY', 'TWD');
    public $store_currency;
    public $http_version;	
	/**
		* 	Class constructor.
		*	
	*/
	public function __construct($client_id, $client_secret) {
        $this->make_params(array(
            'client_id' => $client_id,
            'client_secret' => $client_secret,
        ));
        $this->http_version = '1.1';
        $this->currency = get_woocommerce_currency();
	}

    public function make_params($args) {
    	if (is_array($args) && !empty($args)) {
	        foreach ($args as $key => $value) {
           		 $this->params[$key] = $value;
			}
    	}

    }

    public function make_request_params(array $args) {
        $this->currency_code = get_woocommerce_currency();


        $this->make_params (
                    array
                    (
                        'intent' => 'CAPTURE',
                        'application_context'   => array('brand_name' => $args['brand_name'],
							'locale' => ((strpos($args['locale'], '_') !== false) ? str_replace('_', '-', $args['locale']) : $args['locale']) ,
							 'landing_page' => $args['landing_page'],
							'shipping_preference' => $args['shipping_preference'],
							'user_action' => $args['user_action'],
							'return_url' => $args['return_url'],
    						'cancel_url' => $args['cancel_url'],
							//'payment_method' => array('payee_preferred' => $args['payee_preferred']) 
						), 
                       'purchase_units'   => array(0 => array('invoice_id' => (!empty($args['invoice_prefix']) ? $args['invoice_prefix'] . $args['order_id'] : $args['order_id'])))

                    )
        );


        $i=0;
        if (!defined('WOOCOMMERCE_CART')) {
            define('WOOCOMMERCE_CART', true);
        }

        //if order processed from pay for order page set line items from order details as cart doesn't contains product details
        if($args['pay_for_order']){
            
            $order_id = $args['order_id'];
            $order    = wc_get_order($order_id);
            $this->order_item_params($order);

            $this->add_shipping_details ( 
                array(
                    'name' => array('full_name' => $order->get_shipping_first_name().' '.$order->get_shipping_last_name()),
                    'address' => array(
                        'address_line_1' => $order->get_shipping_address_1(),
                        'address_line_2' => $order->get_shipping_address_2(),
                        'admin_area_2' => $order->get_shipping_city(),
                        'admin_area_1' => $order->get_shipping_state(),
                        'postal_code' => $order->get_shipping_postcode(),
                        'country_code' => $order->get_shipping_country(),
                        //'SHIPTOPHONENUM' => $order->get_billing_phone(),
                        //'EMAIL' => $order->get_billing_email(),
                       // 'PAYMENTREQUESTID' =>  $args['order_id'],
                    )
                )
            );

        }else{
            WC()->cart->calculate_totals();
            
            //sets recurring cart items to session
            if(isset(WC()->cart->recurring_carts)){
                if(count(WC()->cart->recurring_carts) > 0){

                    WC()->session->eh_recurring_carts = WC()->cart->recurring_carts;
                }
            }
            $discount_amount = 0;

            //fix for compatibility issue with store credit coupon created with  WC Smart Coupon plugin 
            $order = wc_get_order($args['order_id']);
            if(isset( WC()->cart->smart_coupon_credit_used )){

                foreach( $order->get_items( 'coupon' ) as $item_id => $coupon_item_obj ){
                        
                    $coupon_item_data = $coupon_item_obj->get_data();
                
                    $coupon_data_id = $coupon_item_data['id'];

                    $order->remove_item($coupon_data_id);
                
                }
                foreach ( WC()->cart->get_coupons() as $code => $coupon ) {

                    $item = new WC_Order_Item_Coupon();
                    $item->set_props(
                        array(
                            'code'         => $code,
                            'discount'     => WC()->cart->get_coupon_discount_amount( $code ),
                            'discount_tax' => WC()->cart->get_coupon_discount_tax_amount( $code ),
                        )
                    );
                    // Avoid storing used_by - it's not needed and can get large.
                    $coupon_data = $coupon->get_data();
                    unset( $coupon_data['used_by'] );
                    $item->add_meta_data( 'coupon_data', $coupon_data );

                    // // Add item to order and save.
                    $order->add_item( $item );

                    $order->set_total( WC()->cart->get_total( 'edit' ) );
                    $order->save();

                }
                
                $total = $this->make_paypal_amount(WC()->cart->total);
                $rounded_total = ($this->make_paypal_amount(WC()->cart->cart_contents_total + WC()->cart->fee_total)) + ($this->make_paypal_amount(WC()->cart->shipping_total)) + (wc_round_tax_total(WC()->cart->tax_total + WC()->cart->shipping_tax_total));

                if ( $total != $rounded_total ) {
                    
                    $discount_amount += $total - $rounded_total;
                }
            }

            //when checkout using express button some fee details are not saved in order
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
                    

                     $this->add_line_items (array(

                        'name'      => $line_item_title.' x '.$item['quantity'],
                        'description' => $line_item_desc,
                        'quantity' => $item['quantity'],
                        'unit_amount' => array(
                            'currency_code' => $this->currency_code,
                            'value' => $this->make_paypal_amount(($item['line_subtotal'] / $item['quantity']), $this->currency_code)
                        ),
                                //'ITEMURL'   => $line_item_url
                    ), $i++ );      
                    

                    $line_item_total_amount  = $line_item_total_amount + $this->make_paypal_amount($item['line_subtotal']);
                    
                }else{
                    
                    $line_item_quan     = $item['quantity'];
                    $line_item_total    = $item['line_subtotal']/$line_item_quan;
                    $this->add_line_items( array(

                        'name' => $line_item_title,
                        'description' => $line_item_desc,
                        'unit_amount' => array(
                            'currency_code' => $this->currency_code,
                            'value' => $this->make_paypal_amount($line_item_total, $this->currency_code)
                        ),
                        'quantity' => $line_item_quan,

                    ), $i++);
                    $total_amount = ($line_item_quan * $this->make_paypal_amount($line_item_total, $this->currency_code));
                    $line_item_total_amount  = $line_item_total_amount + $total_amount;
                    
                }
            }     

           if (WC()->cart->get_cart_discount_total() > 0) 
            {
                $cart_discount_amount = $this->make_paypal_amount(WC()->cart->get_cart_discount_total(), $this->currency_code);

                $line_item_total_amount  = $line_item_total_amount - $cart_discount_amount;
            }

            //add fee to cart line items
           foreach ( WC()->cart->get_fees() as $fee_key => $fee_values ) {

                $line_item_total_amount  = $line_item_total_amount + $this->make_paypal_amount( $fee_values->total, $this->currency_code);
                
            }

            //add line items amount and compares it with cart total amount to check for any total mismatch.cart_contents_total is line item total - doscount 
            $item_amount = $this->make_paypal_amount(WC()->cart->cart_contents_total + WC()->cart->fee_total, $this->currency_code);

            if($line_item_total_amount != $item_amount){
                $diff = $this->make_paypal_amount( $item_amount - $line_item_total_amount, $this->currency_code);
                if ( abs( $diff ) > 0.000001 && 0.0 !== (float) $diff ) {
                    //add extra line item if there is a total mismatch
                    $this->add_line_items(array(
                            'name'  => 'Extra line item',
                            'description'  => '',
                            'quantity'   => 1,
                            'unit_amount'   => array(
                                'currency_code' => $this->currency_code,
                                'value' => $diff
                            ),

                        ),  $i++ );
                }
            }

            //handle mismatch due to rounded tax calculation
            $ship_discount_amount = 0; 
            $cart_total = $this->make_paypal_amount(WC()->cart->total, $this->currency_code);
            $cart_tax = $this->make_paypal_amount(WC()->cart->tax_total + WC()->cart->shipping_tax_total, $this->currency_code);
            $cart_items_total = $item_amount + $this->make_paypal_amount(WC()->cart->shipping_total, $this->currency_code) + $cart_tax;
            if($cart_total != $cart_items_total){
                if($cart_items_total < $cart_total){
                    $cart_tax += $cart_total - $cart_items_total;
                }else{
                    $ship_discount_amount += $this->make_paypal_amount($cart_total - $cart_items_total, $this->currency_code);
                }
            }
           
            $this->add_amount_breakdown(array( 
                'currency_code' => $this->currency_code,
                'value' => $cart_total,
                'breakdown' => array(
                    'item_total' => array(
                        'currency_code' => $this->currency_code,
                        //'value' => $cart_total
                        'value' => $this->make_paypal_amount(WC()->cart->subtotal_ex_tax, $this->currency_code)
                    ),
                    'shipping' => array(
                        'currency_code' => $this->currency_code,
                        'value' => $this->make_paypal_amount(WC()->cart->shipping_total, $this->currency_code)
                    ),
                    'tax_total' => array(
                       'currency_code' => $this->currency_code,
                        'value' => $this->make_paypal_amount($cart_tax, $this->currency_code)
                    ),
                    'shipping_discount' => array(
                        'currency_code' => $this->currency_code,
                        'value' => $this->make_paypal_amount($discount_amount + $ship_discount_amount, $this->currency_code)
                    ),                   
                    'discount' => array(
                        'currency_code' => $this->currency_code,
                        'value' => $this->make_paypal_amount(WC()->cart->discount_cart, $this->currency_code)
                    ),

                )
            ));

            $eh_paypal_express_options = get_option('woocommerce_eh_paypal_express_settings');
            $need_shipping = $eh_paypal_express_options['smart_button_send_shipping'];
            if(($need_shipping === 'yes') && (isset(WC()->session->post_data['ship_to_different_address'])) && ( WC()->session->post_data['ship_to_different_address'] == 1)){ 
                
                $this->add_shipping_details (                
                    array
                    (
                        'name'  =>  array('full_name' => (empty(WC()->session->post_data['shipping_first_name']) ? '' :WC()->session->post_data['shipping_first_name']) .' '.(empty(WC()->session->post_data['shipping_last_name']) ? '': WC()->session->post_data['shipping_last_name'] )),
                        'address' => array(
                            'address_line_1'      =>  empty(WC()->session->post_data['shipping_address_1'])   ? '' : wc_clean(WC()->session->post_data['shipping_address_1']) ,
                            'address_line_2'     =>  empty(WC()->session->post_data['shipping_address_2'])   ? '' : wc_clean(WC()->session->post_data['shipping_address_2']),
                            'admin_area_2'        =>  empty(WC()->session->post_data['shipping_city'])        ? '' : wc_clean(WC()->session->post_data['shipping_city']),
                            'admin_area_1'       =>  empty(WC()->session->post_data['shipping_state'])       ? '' : wc_clean(WC()->session->post_data['shipping_state']),
                            'postal_code'         =>  empty(WC()->session->post_data['shipping_postcode'])    ? '' : wc_clean(WC()->session->post_data['shipping_postcode']),
                            'country_code' =>  empty(WC()->session->post_data['shipping_country'])     ? '' : wc_clean(WC()->session->post_data['shipping_country']),
                            //'SHIPTOPHONENUM'    =>  empty(WC()->session->post_data['billing_phone'])        ? '' : wc_clean(WC()->session->post_data['billing_phone']),
                            //'NOTETEXT'          =>  empty(WC()->session->post_data['order_comments'])       ? '' : wc_clean(WC()->session->post_data['order_comments']),
                            //'PAYMENTREQUESTID'  =>  $args['order_id'],
                        )
                        
                    )
                );
            }
            else{ 

                $this->add_shipping_details (                
                        array(

                            'name'        =>  array('full_name' => (empty(WC()->session->post_data['billing_first_name']) ? ((( WC()->version < '2.7.0' ) ? WC()->session->post_data['billing_first_name'] : WC()->customer->get_billing_first_name()).' '.(( WC()->version < '2.7.0' ) ? WC()->session->post_data['billing_last_name'] : WC()->customer->get_billing_last_name())) : WC()->session->post_data['billing_first_name']) .' '.(empty(WC()->session->post_data['billing_last_name']) ? '': WC()->session->post_data['billing_last_name'] )),
                            'address' => array(
                                'address_line_1'      =>  empty(WC()->session->post_data['billing_address_1'])   ? (( WC()->version < '2.7.0' ) ? WC()->customer->get_address() : WC()->customer->get_billing_address())           : wc_clean(WC()->session->post_data['billing_address_1']) ,
                                'address_line_2'     =>  empty(WC()->session->post_data['billing_address_2'])   ? (( WC()->version < '2.7.0' ) ? WC()->customer->get_address_2() : WC()->customer->get_billing_address_2())       : wc_clean(WC()->session->post_data['billing_address_2']),
                                'admin_area_2'        =>  empty(WC()->session->post_data['billing_city'])        ? (( WC()->version < '2.7.0' ) ? WC()->customer->get_city() : WC()->customer->get_billing_city())                 : wc_clean(WC()->session->post_data['billing_city']),
                                'admin_area_1'       =>  empty(WC()->session->post_data['billing_state'])       ? (( WC()->version < '2.7.0' ) ? WC()->customer->get_state() : WC()->customer->get_billing_state())               : wc_clean(WC()->session->post_data['billing_state']),
                                'postal_code'         =>  empty(WC()->session->post_data['billing_postcode'])    ? (( WC()->version < '2.7.0' ) ? WC()->customer->get_postcode() : WC()->customer->get_billing_postcode())         : wc_clean(WC()->session->post_data['billing_postcode']),
                                 'country_code' =>  empty(WC()->session->post_data['billing_country'])     ? (( WC()->version < '2.7.0' ) ? WC()->customer->get_country() : WC()->customer->get_billing_country())           : wc_clean(WC()->session->post_data['billing_country']),
                                //'SHIPTOPHONENUM'    =>  empty(WC()->session->post_data['billing_phone'])       ? (( WC()->version < '2.7.0' ) ? WC()->session->post_data['billing_phone'] : WC()->customer->get_billing_phone()) : wc_clean(WC()->session->post_data['billing_phone']),
                                //'NOTETEXT'          =>  empty(WC()->session->post_data['order_comments'])      ? '' : wc_clean(WC()->session->post_data['order_comments']),
                                //'PAYMENTREQUESTID'  => $args['order_id']
                            )

                        )
                    );
            }
        }

        //to avoid adding 14 digit precision while json encoding
        ini_set("precision", 14); ini_set("serialize_precision", -1); 
        
        $headers = array('Authorization' => 'Bearer ' . $args['access_token'], 'Content-Type' => 'application/json');
        $this->params = apply_filters('wt_rest_request_params', $this->params);

        $api_name = "Create Order API";

        Eh_PayPal_Log::log_update(json_encode($this->params, JSON_PRETTY_PRINT), $api_name, 'json');
        $body = json_encode($this->query_params());
        return $this->get_params('POST', $headers, $body);
    }

    public function get_order_details($reqst){
        Eh_PayPal_Log::log_update(json_encode($reqst, JSON_PRETTY_PRINT),'Get Order Details API', 'json');
         $headers = array('Authorization' => 'Bearer ' . $reqst['access_token'], 'Content-Type' => 'application/json');
        return $this->get_params('GET', $headers);
    }

    public function update_order($reqst, $order){ 

        $this->order_item_params($order);
         $headers = array('Authorization' => 'Bearer ' . $reqst['access_token'], 'Content-Type' => 'application/json');
         $this->get_address_details(); 
          $body = $this->query_params();  
         $req_params = $this->alter_query_params($body);

         //to avoid adding 14 digit precision while json encoding
        ini_set("precision", 14); ini_set("serialize_precision", -1);
          Eh_PayPal_Log::log_update(json_encode($req_params, JSON_PRETTY_PRINT),'Update Order API', 'json');
        return $this->get_params('PATCH', $headers, json_encode($req_params));
    } 

    public function capture_order($reqst){
        Eh_PayPal_Log::log_update(json_encode($reqst, JSON_PRETTY_PRINT),'Captue Order API', 'json');
         $headers = array('Authorization' => 'Bearer ' . $reqst['access_token'], 'Content-Type' => 'application/json');
        return $this->get_params('POST', $headers);
    }
    
    public function authorize_order($reqst){
        Eh_PayPal_Log::log_update(json_encode($reqst, JSON_PRETTY_PRINT),'Authorize Order API', 'json');
         $headers = array('Authorization' => 'Bearer ' . $reqst['access_token'], 'Content-Type' => 'application/json');
        return $this->get_params('POST', $headers);
    }
    
    public function make_refund_params($args){
         $headers = array('Authorization' => 'Bearer ' . $args['access_token'], 'Content-Type' => 'application/json');
         if (isset($args['amount'])) {
             $this->make_params
            (
                array
                (
                    'invoice_number'            => $args['invoice_number'],
                    'note_to_payer'            => $args['note_to_payer'],
                    'amount'     => array(
                        'value'               => $this->make_paypal_amount($args['amount'],$args['currency']),
                        'currency_code'      => $args['currency'],
                   )
                )
            );          
        }
        else{
              $this->make_params(array
                (
                    'invoice_number'            => $args['invoice_number'],
                    'note_to_payer'            => $args['note_to_payer'],

                ));
        }

        if (isset($this->params['note_to_payer']) && empty($this->params['note_to_payer'])) {
            unset($this->params['note_to_payer']);
        }
        Eh_PayPal_Log::log_update(json_encode($this->params, JSON_PRETTY_PRINT),'Refund Order API', 'json');

        return $this->get_params('POST', $headers, json_encode($this->params));
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
        
        $currency = (WC()->version < '2.7.0')?$order->get_order_currency():$order->get_currency();
        
        $order_id = (WC()->version < '2.7.0')?$order->id:$order->get_id();
        
        $wt_skip_line_items = $this->wt_skip_line_items(); // if tax enabled and when product has inclusive tax  
        
        
        foreach ($order_item as $item)
        {
            //add fee details to order line items
            if ( 'fee' === $item['type'] ) {

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
                
                if( $wt_skip_line_items ){    

                    $this->add_line_items(                     
                        array(
                            'name'      => $line_item_title.' x '.$item['quantity'],
                            'description'      => $line_item_desc,
                            'quantity'      => $item['quantity'],
                            'unit_amount' => array(
                                'currency_code' => $currency,
                                'value'       => $this->make_paypal_amount(($item['line_subtotal']/$item['quantity']),$currency),
                            )
                        ),$i++
                    );
                   
                    $line_item_total_amount = $line_item_total_amount + $this->make_paypal_amount($item['line_subtotal'],$currency);

                }else{
                    $line_item_quan     = $item['quantity'];
                    $line_item_total    = $item['line_subtotal']/$line_item_quan;
                    $this->add_line_items
                            (
                                array
                                (
                                    'name'      => $line_item_title,
                                    'description'      => $line_item_desc,
                                    'unit_amount' => array(
                                        'currency_code' => $currency,                                    
                                        'value'       => $this->make_paypal_amount($line_item_total,$currency)
                                    ),                            
                                    'quantity'       => $line_item_quan,
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

            $line_item_total_amount = $line_item_total_amount - $this->make_paypal_amount($order->get_total_discount());
        }

        //add line items amount and compares it with order total amount to check for any total mismatch
        $order_item_total = $this->make_paypal_amount($order->get_subtotal()-$order->get_total_discount() + $total_fee,$currency);

        if($line_item_total_amount != $order_item_total){
            $diff = $this->make_paypal_amount( $order_item_total - $line_item_total_amount, $currenc);
            if ( abs( $diff ) > 0.000001 && 0.0 !== (float) $diff ) {
                //add extra line item if there is a total mismatch
                $this->add_line_items(
                    array
                    (
                        'name'  => 'Extra line item',
                        'description'  => '',
                        'quantity'   => 1,
                        'unit_amount' => array(
                            'currency_code' => $currency,
                            'value'   => $diff
                        ),
                    ),
                    $i++
                );
            }
        }

        //handle mismatch due to rounded tax calculation
        $ship_discount_amount = 0;
        $order_tax = $this->make_paypal_amount($order->get_total_tax(),$currency);
         $order_total = $this->make_paypal_amount($order->get_total(),$currency) + $order_tax;
        $order_items_total = $order_item_total + $this->make_paypal_amount($order->get_total_shipping(),$currency) + $order_tax;
        if($order_total != $order_items_total){
            if($order_items_total < $order_total){
                $order_tax += $order_total - $order_items_total;
            }else{
                $ship_discount_amount += $this->make_paypal_amount($order_total - $order_items_total);
            }
        }
            $this->add_amount_breakdown(array( 
                'currency_code' => $currency,
                'value' => $this->make_paypal_amount($order_total,$currency),
                'breakdown' => array(
                    'item_total' => array(
                        'currency_code' => $currency,
                        'value' => $this->make_paypal_amount($order->get_subtotal()-$order->get_total_discount() ,$currency)
                    ),
                    'shipping' => array(
                        'currency_code' => $currency,
                        'value' => $this->make_paypal_amount($order->get_total_shipping(),$currency)
                    ),
                    'tax_total' => array(
                       'currency_code' => $currency,
                        'value' => $this->make_paypal_amount($order_tax,$currency)
                    ),
                    'shipping_discount' => array(
                        'currency_code' => $currency,
                        'value' => $ship_discount_amount
                    ),                   
                    'discount' => array(
                        'currency_code' => $currency,
                        'value' => $this->make_paypal_amount(WC()->cart->discount_cart,$currency)
                    ),

                )
            ));

    }

    public function get_address_details()
    {
       $eh_paypal_express_options = get_option('woocommerce_eh_paypal_express_settings');
        $need_shipping = $eh_paypal_express_options['smart_button_send_shipping'];
        if(($need_shipping === 'yes') && (isset(WC()->session->post_data['ship_to_different_address'])) && ( WC()->session->post_data['ship_to_different_address'] == 1)){ 
            
            $this->add_shipping_details (                
                    array
                    (
                        'name'  =>  array('full_name' => (empty(WC()->session->post_data['shipping_first_name']) ? '' :WC()->session->post_data['shipping_first_name']) .' '.(empty(WC()->session->post_data['shipping_last_name']) ? '': WC()->session->post_data['shipping_last_name'] )),
                        'address' => array(
                            'address_line_1'      =>  empty(WC()->session->post_data['shipping_address_1'])   ? '' : wc_clean(WC()->session->post_data['shipping_address_1']) ,
                            'address_line_2'     =>  empty(WC()->session->post_data['shipping_address_2'])   ? '' : wc_clean(WC()->session->post_data['shipping_address_2']),
                            'admin_area_2'        =>  empty(WC()->session->post_data['shipping_city'])        ? '' : wc_clean(WC()->session->post_data['shipping_city']),
                            'admin_area_1'       =>  empty(WC()->session->post_data['shipping_state'])       ? '' : wc_clean(WC()->session->post_data['shipping_state']),
                            'postal_code'         =>  empty(WC()->session->post_data['shipping_postcode'])    ? '' : wc_clean(WC()->session->post_data['shipping_postcode']),
                            'country_code' =>  empty(WC()->session->post_data['shipping_country'])     ? '' : wc_clean(WC()->session->post_data['shipping_country']),

                        )
                        
                    )
                );

        }
        else{ 

            $this->add_shipping_details (                
                    array
                    (

                        'name'        =>  array('full_name' => (empty(WC()->session->post_data['billing_first_name']) ? ((( WC()->version < '2.7.0' ) ? WC()->session->post_data['billing_first_name'] : WC()->customer->get_billing_first_name()).' '.(( WC()->version < '2.7.0' ) ? WC()->session->post_data['billing_last_name'] : WC()->customer->get_billing_last_name())) : WC()->session->post_data['billing_first_name']) .' '.(empty(WC()->session->post_data['billing_last_name']) ? '': WC()->session->post_data['billing_last_name'] )),
                        'address' => array(
                            'address_line_1'      =>  empty(WC()->session->post_data['billing_address_1'])   ? (( WC()->version < '2.7.0' ) ? WC()->customer->get_address() : WC()->customer->get_billing_address())           : wc_clean(WC()->session->post_data['billing_address_1']) ,
                            'address_line_2'     =>  empty(WC()->session->post_data['billing_address_2'])   ? (( WC()->version < '2.7.0' ) ? WC()->customer->get_address_2() : WC()->customer->get_billing_address_2())       : wc_clean(WC()->session->post_data['billing_address_2']),
                            'admin_area_2'        =>  empty(WC()->session->post_data['billing_city'])        ? (( WC()->version < '2.7.0' ) ? WC()->customer->get_city() : WC()->customer->get_billing_city())                 : wc_clean(WC()->session->post_data['billing_city']),
                            'admin_area_1'       =>  empty(WC()->session->post_data['billing_state'])       ? (( WC()->version < '2.7.0' ) ? WC()->customer->get_state() : WC()->customer->get_billing_state())               : wc_clean(WC()->session->post_data['billing_state']),
                            'postal_code'         =>  empty(WC()->session->post_data['billing_postcode'])    ? (( WC()->version < '2.7.0' ) ? WC()->customer->get_postcode() : WC()->customer->get_billing_postcode())         : wc_clean(WC()->session->post_data['billing_postcode']),
                             'country_code' =>  empty(WC()->session->post_data['billing_country'])     ? (( WC()->version < '2.7.0' ) ? WC()->customer->get_country() : WC()->customer->get_billing_country())           : wc_clean(WC()->session->post_data['billing_country']),
                            //'SHIPTOPHONENUM'    =>  empty(WC()->session->post_data['billing_phone'])       ? (( WC()->version < '2.7.0' ) ? WC()->session->post_data['billing_phone'] : WC()->customer->get_billing_phone()) : wc_clean(WC()->session->post_data['billing_phone']),
                            //'NOTETEXT'          =>  empty(WC()->session->post_data['order_comments'])      ? '' : wc_clean(WC()->session->post_data['order_comments']),
                            //'PAYMENTREQUESTID'  => $args['order_id']
                        )

                    )
                );
           /* $this->make_params //bugfix email address not getting pre-filled in the paypal page
            (
                array
                (
                    'EMAIL'             =>  empty(WC()->session->post_data['billing_email'])        ? '' : wc_clean(WC()->session->post_data['billing_email']),
                )
            );*/
        }
    }

    public function alter_query_params($rqst)
    {   
        $req_params = array();
        $index = 0;

       if (isset($rqst['purchase_units'][0]['amount'])) {
            $req_params[$index]['op'] = 'replace';
            $req_params[ $index]['path'] = "/purchase_units/@reference_id=='default'/amount";
            $req_params[ $index]['value'] = $rqst['purchase_units'][0]['amount'];
            $index++;
        }
        if (isset($rqst['purchase_units'][0]['shipping']['name'])) {
            $req_params[$index]['op'] = 'replace';
            $req_params[ $index]['path'] = "/purchase_units/@reference_id=='default'/shipping/name";
            $req_params[ $index]['value'] = $rqst['purchase_units'][0]['shipping']['name'];
            $index++;
        }      
        if (isset($rqst['purchase_units'][0]['shipping']['address'])) {
            $req_params[$index]['op'] = 'replace';
            $req_params[ $index]['path'] = "/purchase_units/@reference_id=='default'/shipping/address";
            $req_params[ $index]['value'] = $rqst['purchase_units'][0]['shipping']['address'];
            $index++;
        }

        return $req_params;
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

    public function wt_skip_line_items() {
        return ( 'yes' === get_option('woocommerce_calc_taxes') && 'yes' === get_option('woocommerce_prices_include_tax') );         
    }

    public function add_line_items($items,$count)
    {

        $this->params['purchase_units'][0]['items'][$count] = $items;

    }

    public function add_amount_breakdown($items)
    {

        $this->params['purchase_units'][0]['amount'] = $items;

    }

    public function add_shipping_details($items)
    {

        $this->params['purchase_units'][0]['shipping'] = $items;
    }

    public function query_params()
    {
        foreach ($this->params as $key => $value) 
        {
            if ('' === $value || is_null($value)) {
                unset($this->params[$key]);
            }

        }  

       if (isset($this->params['client_id'])) {
             unset($this->params['client_id']);
        }       
         if (isset($this->params['client_secret'])) {
             unset($this->params['client_secret']);
        }      
        return $this->params;
    }

    public function get_params($method, $header, $body = null) {
       
        $args = array(
            'method' => $method,
            'timeout' => 60,
            'redirection' => 0,
            'httpversion' => $this->http_version,
            'sslverify' => FALSE,
            'blocking' => true,
            'user-agent' => 'EH_PAYPAL_EXPRESS_CHECKOUT',
            'headers' => $header,
            'body' => $body,
            'cookies' => array(),
        );
        return $args;
    }

    public function get_token()   
    { 
         $encoded_value =  base64_encode($this->params['client_id'] . ':' . $this->params['client_secret'] );
        $header =  array('Authorization' => 'Basic '. $encoded_value);
        $body = http_build_query(array('grant_type' => 'client_credentials' ));
       return $this->get_params('POST', $header, $body );
    }
	
}