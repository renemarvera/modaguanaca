<?php
if (!defined('ABSPATH')) {
    exit;
}
class Eh_PE_Process_Request
{
    public function process_request($params,$uri)
    {
        $response_processer=new Eh_PE_Process_Response();
        $response   = $response_processer->process_response(wp_safe_remote_request($uri, $params));
        return $response;
    }
    
}
