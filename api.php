<?php

class api
{
    function make_request($token, $method_name, $method = 'get', $params = NULL)
    {
        $request_url = "https://api.telegram.org/bot{$token}/{$method_name}";
        $handle = curl_init($request_url);
  			curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
  			curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
  			curl_setopt($handle, CURLOPT_TIMEOUT, 60);
  			curl_setopt($handle, CURLOPT_POST, true);
  			curl_setopt($handle, CURLOPT_POSTFIELDS, json_encode($params));
  			curl_setopt($handle, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
  			
  			return $handle;
    }
}
