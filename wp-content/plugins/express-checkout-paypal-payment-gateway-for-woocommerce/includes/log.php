<?php

if (!defined('ABSPATH')) {
    exit;
}

class Eh_PayPal_Log {

    public static function init_log() {
        $content = "<------------------- WebToffee PayPal Express Payment Log File ( " . EH_PAYPAL_VERSION . " ) ------------------->\n";
        return $content;
    }

    public static function remove_data($data) {
        if (isset($data['USER'])) {
             unset($data['USER']);
        }
        if (isset($data['PWD'])) {
             unset($data['PWD']);
        }        
        if (isset($data['SIGNATURE'])) {
            unset($data['SIGNATURE']);
        }        
        if (isset($data['VERSION'])) {
             unset($data['VERSION']);
        } 
       if (isset($data['client_id'])) {
             unset($data['client_id']);
        }       
         if (isset($data['client_secret'])) {
             unset($data['client_secret']);
        }       
        if (isset($data['access_token'])) {
            unset($data['access_token']);
        }
        return $data;
    }

    public static function log_update($mg, $title, $type = null) {
        $check = get_option('woocommerce_eh_paypal_express_settings');
        if ($type == 'json') { 
            $resp= Eh_PayPal_Log::remove_data(json_decode($mg, true));
            $msg = json_encode($resp, JSON_PRETTY_PRINT);
        }
        else{
            $msg= Eh_PayPal_Log::remove_data($mg);
        }
        if ('yes' === $check['paypal_logging']) {
            if (WC()->version >= '2.7.0') {
                $log = wc_get_logger();
                $head = "<------------------- WebToffee PayPal Express Payment ( " . $title . " ) ------------------->\n";
                if ($type == 'json') { 
                    $log_text=$head . print_r($msg, true);
                }
                else{
                     $log_text=$head.print_r((object)$msg,true);
                }
                $context = array('source' => 'eh_paypal_express_log');
                $log->log("debug", $log_text, $context);
            } else {
                $log = new WC_Logger();
                $head = "<------------------- WebToffee PayPal Express Payment ( " . $title . " ) ------------------->\n";
                if ($type == 'json') {
                    $log_text=$head . print_r($msg, true);
                }
                else{
                    $log_text=$head.print_r((object)$msg,true);
                }                $log->add("eh_paypal_express_log", $log_text);
            }
        }
    }

}
