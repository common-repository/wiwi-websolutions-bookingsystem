<?php

namespace WiwiWebsolutionsBookingSystem\App\BookingSystem;

use function WiwiWebsolutionsBookingSystem\App\General\WWBS_CallAPI;

//======================================================================
// Function to get all the properties from the bookingsystem
//======================================================================
function WWBS_get_properties_bookingsystem()
{
    $properties_bookingsystem_array = [];

    $properties_api = WWBS_CallBookingSystem('GET', '/v1/properties', ['limit' => 10000]);

    if (!empty($properties_api['data'])) {
        foreach ($properties_api['data'] as $item) {
            $properties_bookingsystem_array[$item['id']] = $item['name'];
        }
    }

    return $properties_bookingsystem_array;
}



//======================================================================
// Function to call the bookingsystem API, includes bearer token automatically
//======================================================================
function WWBS_CallBookingSystem($method, $url, $data = false, $headers = [])
{
    $headers['Authorization'] = 'Bearer ' . WIWI_WEBSOLUTIONS_BOOKINGSYSTEM_API_KEY;

    $url = WIWI_WEBSOLUTIONS_BOOKINGSYSTEM_API_BASE_URL . $url;

    return WWBS_CallAPI($method, $url, $data, $headers);
}
