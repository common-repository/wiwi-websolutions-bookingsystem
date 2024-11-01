<?php

namespace WiwiWebsolutionsBookingSystem\App\General;

//======================================================================
// Function to call the Bookingsystem API
//======================================================================
/*
* Method: POST, PUT, GET etc
* Data: array("param" => "value") ==> index.php?param=value
*/

function WWBS_CallAPI($method, $url, $data = [], $headers = [])
{
    $response = null;

    $headers['Content-Type'] = 'application/json';
    $headers['Accept'] = 'application/json';
    
    $args = (array) [
        'body'        => $data,
        'headers'     => $headers,
        'timeout'     => 45,
        'redirection' => 5,
        'httpversion' => '1.0',
        'blocking'    => true,
        'cookies'     => [],
        'sslverify'   => false,
        'data_format' => 'body',
    ];

    switch($method) {
        case "GET":
            $url = sprintf("%s?%s", $url, http_build_query($data));
            $response = wp_remote_get($url, $args);
            break;

        case "POST":
            $response = wp_remote_post($url, $args);
            break;

        case "PUT":
            $args['method'] = 'PUT';
            $response = wp_remote_request($url, $args);
            break;
        
        default:
            break;
    }

    $body = wp_remote_retrieve_body($response);
    $body = json_decode($body, true);

    return $body;
}