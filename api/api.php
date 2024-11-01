<?php

use function WiwiWebsolutionsBookingSystem\App\BookingSystem\WWBS_CallBookingSystem;


//======================================================================
// Function to get plugin settings
//======================================================================
function WWBS_get_plugin_settings($data)
{
    $plugin_settings = [];

    $plugin_settings['booking_succes_page_id'] = get_option('_wiwi_websolutions_bookingsystem_booking_succes_page_id');

    return $plugin_settings;
}



//======================================================================
// Function to get all properties
//======================================================================
function WWBS_get_all_properties($data)
{
    $properties_api = WWBS_CallBookingSystem('GET', '/v1/properties', false);

    return $properties_api;
}



//======================================================================
// Function to get a propert by id
//======================================================================
function WWBS_get_property_by_id($request)
{
    $id = $request['id'];
    $start_date = $request->get_param('start_date') ?? date("Y-m-d");
    $end_date = $request->get_param('end_date') ?? (new \DateTime())->add(new \DateInterval('P30D'))->format('Y-m-d');
    $query = [
        'start_date' => $start_date,
        'end_date' => $end_date,
        'limit' => '10000'
    ];
    $properties_api = WWBS_CallBookingSystem('GET', '/v1/properties/' . $id, $query);

    unset($properties_api['data']['owner']);
    unset($properties_api['data']['contact']);

    return $properties_api;
}


//======================================================================
// Create booking in bookingsystem
//======================================================================
function WWBS_create_booking($request)
{
    $id = $request['id'];
    $data = $request->get_body();
    $data = json_decode(json_encode($data), true);

    $response = WWBS_CallBookingSystem('POST', '/v1/properties/' . $id . '/bookings/create', $data);
    
    if ($response['status'] != 201) {

        $to = get_bloginfo('admin_email');
        $subject = 'Booking failed from booking form';
        $body = '----- Request: <br><br>' . json_encode($data, JSON_PRETTY_PRINT) . '<br><br><br><br>' . '----- Response: <br><br>' . json_encode($response, JSON_PRETTY_PRINT);
        $headers = array('Content-Type: text/html; charset=UTF-8');

        wp_mail($to, $subject, $body, $headers);


        return new \WP_REST_Response($response, $response->status);
    }
    return $response;
}



//======================================================================
// Function to update the metadata of a property
//======================================================================
function WWBS_update_property_by_id($request)
{
    $property_id = $request['id'];
    $wp_property_ids = [];

    //-----------------------------------------------------
    // Get property from the bookingsystem by id
    //-----------------------------------------------------
    $start_date = date('Y-m-d');
    $end_date = date('Y-m-d', strtotime('+2 years'));
    $query = [
        'start_date' => $start_date,
        'end_date' => $end_date,
        'limit' => '10000'
    ];
    $property = WWBS_CallBookingSystem('GET', '/v1/properties/' . $property_id, $query);

    //-----------------------------------------------------
    // Check if the option for a post type is set and the bookingsystem property id is not empty
    //-----------------------------------------------------
    if (get_option('_wiwi_websolutions_bookingsystem_wp_post_type') && $property) {
        $post_types = get_option('_wiwi_websolutions_bookingsystem_wp_post_type');

        if (str_contains($post_types, ',')) { 
            $post_types = explode(',', $post_types);
        }

        //-----------------------------------------------------
        // Query to get the WordPress id by bookingsystem id
        //-----------------------------------------------------
        $args = array(
            'post_type' => $post_types,
            'posts_per_page' => -1,
            'suppress_filters' => true,
            'meta_query' => array(
                array(
                    'key' => '_wiwi_websolutions_bookingsystem_property_id',
                    'value' => $property['data']['id']
                )
            ),
            'post_status' => 'publish'
        );

        $properties_query = new \WP_Query($args);

        if ($properties_query->have_posts()) {
            foreach ($properties_query->posts as $item) {
                array_push($wp_property_ids, $item->ID);
            }
        }
        wp_reset_postdata();
    }
    
    //-----------------------------------------------------
    // Check if property id is returned and update the data from the api
    //-----------------------------------------------------
    foreach ($wp_property_ids as $property_wp_id) {
        if ($property_wp_id !== 0) {

            if(array_key_exists('availability', $property['data']) && isset($property['data']['availability']) ) {
                $dates_arrival = [];
                $dates_departure = [];
    
                foreach($property['data']['availability'] as $key => $item) {
                    $date = intval(date('Ymd', strtotime($key)));
        
                    if($item['arrival'] === true) {
                        array_push($dates_arrival, $date);
                    }
        
                    if($item['departure'] === true) {
                        array_push($dates_departure, $date);
                    }
                }
            }
    
            update_post_meta($property_wp_id, '_wiwi_websolutions_bookingsystem_date_arrival', $dates_arrival);
            update_post_meta($property_wp_id, '_wiwi_websolutions_bookingsystem_date_departure', $dates_departure);
    
            update_post_meta($property_wp_id, '_wiwi_websolutions_bookingsystem_max_persons', $property['data']['max_persons']);
            update_post_meta($property_wp_id, '_wiwi_websolutions_bookingsystem_last_minute', $property['data']['has_last_minute'] ? 'true' : 'false');
            update_post_meta($property_wp_id, '_wiwi_websolutions_bookingsystem_lowest_price', $property['data']['lowest_price']);
    
    
            //-----------------------------------------------------
            // Clear post cache, because Search & Filter Pro doesn't have an option for this
            //-----------------------------------------------------
            wp_update_post( ['ID' => $property_wp_id] );
    
            //-----------------------------------------------------
            // Check if WP-Rocket is active, if so clear cache for the post
            //-----------------------------------------------------
            if (is_plugin_active('wp-rocket/wp-rocket.php')) {
                rocket_clean_post($property_wp_id);
            }
    
            //-----------------------------------------------------
            // Check if Search & Filter Pro is active, if so clear cache for the post
            //-----------------------------------------------------
            if (is_plugin_active('search-filter-pro/search-filter-pro.php')) {
                do_action('search_filter_update_post_cache', $property_wp_id);
            }
        }
    }

    return "Updated";
}



//======================================================================
// Expand the WordPress API with the custom endpoints below
//======================================================================
add_action('rest_api_init', function () {
    register_rest_route('wiwi-websolutions-bookingsystem/v2', '/settings/', array(
        'methods' => 'GET',
        'callback' => 'WWBS_get_plugin_settings',
    ));

    register_rest_route('wiwi-websolutions-bookingsystem/v2', '/properties/', array(
        'methods' => 'GET',
        'callback' => 'WWBS_get_all_properties',
    ));

    register_rest_route('wiwi-websolutions-bookingsystem/v2', '/properties/(?P<id>[0-9a-zA-Z\-]+)', array(
        'methods' => 'GET',
        'callback' => 'WWBS_get_property_by_id',
    ));

    register_rest_route('wiwi-websolutions-bookingsystem/v2', '/properties/(?P<id>[0-9a-zA-Z\-]+)/update', array(
        'methods' => 'GET',
        'callback' => 'WWBS_update_property_by_id',
    ));

    register_rest_route('wiwi-websolutions-bookingsystem/v2', '/properties/(?P<id>[0-9a-zA-Z\-]+)/bookings/create', array(
        'methods' => 'POST',
        'callback' => 'WWBS_create_booking',
    ));
});
