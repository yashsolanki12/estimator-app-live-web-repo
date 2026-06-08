<?php

namespace App\Services;

use Exception;

class Api
{
    public static function callCURL($url, $headers = [], $form_params = [], $method = "POST")
    {
        if ($method == 'GET' || $method == 'DELETE') {
            $ch = curl_init();

            $options = array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 180,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => $method,
                CURLOPT_HTTPHEADER => $headers,
            ); // cURL options
            curl_setopt_array($ch, $options);

            $response = curl_exec($ch);
            $errors = curl_error($ch);
            $response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error_number = curl_errno($ch);
            
            curl_close($ch);
        } else {
            $ch = curl_init();

            $options = array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 180,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => $method,
                CURLOPT_POSTFIELDS => $form_params,
                CURLOPT_HTTPHEADER => $headers,
            ); // cURL options
            curl_setopt_array($ch, $options);

            $response = curl_exec($ch);
            $errors = curl_error($ch);
            $response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error_number = curl_errno($ch);

            curl_close($ch);
        }
        return ['response' => $response, 'errors' => $errors, 'response_code' => $response_code, 'error_number' => $error_number];
    }
}
