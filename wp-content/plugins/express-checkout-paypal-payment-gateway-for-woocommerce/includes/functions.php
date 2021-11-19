<?php

if (!defined('ABSPATH')) {
    exit;
}

function eh_paypal_express_hook_init() {
    $hook = eh_paypal_express_run()->hook_include;
    $hook->express_run();
}
