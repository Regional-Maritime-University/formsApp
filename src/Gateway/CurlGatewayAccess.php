<?php

namespace Src\Gateway;

class CurlGatewayAccess
{
    private $curl_array = array();

    public function __construct($url, $httpHeader, $payload)
    {
        $this->curl_array = array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => $httpHeader,
        );
    }

    public function initiateProcess()
    {
        $curl = curl_init();
        curl_setopt_array($curl, $this->curl_array);
        $response = curl_exec($curl);
        curl_close($curl);

        if ($response === false) {
            $error_code = curl_errno($curl);
            $error_message = curl_error($curl);
            return "cURL Error: $error_message (Error code: $error_code)";
        } else {
            return $response;
        }
    }
}
