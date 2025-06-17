<?php

if (!function_exists('api_response')) {
    function api_response($data = null, string $message = null, int $status = 200, bool $success = true)
    {
        $response = service('response');
        $output = [
            'success' => $success,
            'status' => $status,
            'message' => $message,
            'data' => $data
        ];
        return $response->setStatusCode($status)->setJSON($output);
    }
}

if (!function_exists('api_error')) {
    function api_error(string $message = null, int $status = 400, $errors = null, bool $success = false)
    {
        $response = service('response');
        $output = [
            'success' => $success,
            'status' => $status,
            'message' => $message,
            'errors' => $errors
        ];
        return $response->setStatusCode($status)->setJSON($output);
    }
}


/**
 * Custom Helper untuk Debugging Query Database
 */

if(! function_exists("tesx")) {

    /**  Error Tracing */
    function tesx()
    {
        $env = (ENVIRONMENT == 'production') ? 'none' : 'block; background-color: #c7c5b2;';
        $args = func_get_args();
        if(is_array($args) && count($args)){ foreach($args as $x){
            $echo = "<div style='display:$env'><pre style='padding: 1rem;'>";
            if(is_array($x) || is_object($x)){
                $echo .= print_r($x, true);
            }else{
                $echo .= var_export($x, true);
            }
            $echo .= "</pre><hr /></div>";
            echo $echo;
        }}
        die();
    }
}

