<?php

namespace WiwiWebsolutionsBookingSystem\App\WordPress;
use function WiwiWebsolutionsBookingSystem\App\BookingSystem\WWBS_CallBookingSystem;
use function WiwiWebsolutionsBookingSystem\App\General\WWBS_CallAPI;


//======================================================================
// Function to get all the properties from WordPress
//======================================================================
function WWBS_get_properties_wp() {
    $properties_wp_array = [];

    //-----------------------------------------------------
    // Check if the option for a post type is set
    //-----------------------------------------------------
    if(get_option('_wiwi_websolutions_bookingsystem_wp_post_type')) {
        $post_types = get_option('_wiwi_websolutions_bookingsystem_wp_post_type');

        if (str_contains($post_types, ',')) { 
            $post_types = explode(',', $post_types);
        }

        $args = array(
            'post_type' => $post_types,
            'posts_per_page' => -1,
            'order' => 'ASC',
            'orderby' => 'title',
            'post_status' => 'publish'
        );
        
        $properties_query = new \WP_Query($args);
        
        if($properties_query->have_posts()){
            foreach($properties_query->posts as $item) {
                $properties_wp_array[$item->ID] = $item->post_title;
            }
        }
        wp_reset_postdata();
    }
    
    return $properties_wp_array;
}