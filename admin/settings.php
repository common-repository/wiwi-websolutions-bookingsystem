<?php
use Carbon_Fields\Container;
use Carbon_Fields\Field;
use function WiwiWebsolutionsBookingSystem\App\General\WWBS_CallAPI;
use function WiwiWebsolutionsBookingSystem\App\BookingSystem\WWBS_CallBookingSystem;
use function WiwiWebsolutionsBookingSystem\App\WordPress\WWBS_get_properties_wp;
use function WiwiWebsolutionsBookingSystem\App\BookingSystem\WWBS_get_properties_bookingsystem;


//======================================================================
// Register plugin settings page and fields
//======================================================================
add_action('carbon_fields_register_fields', function() { 
    return wiwi_websolutions_bookingsystem_attach_theme_options(); 
});
add_action('carbon_fields_theme_options_container_saved', 'wiwi_websolutions_bookingsystem_on_save');



//======================================================================
// Hook the Carbon Fields save function and insert the data into the postmeta table
//======================================================================
function wiwi_websolutions_bookingsystem_on_save($data) {
    $houses = WWBS_get_properties_wp();
    $data = [];
    
    foreach($houses as $key => $value) {
        $property_id = get_option('_wiwi_websolutions_bookingsystem_property_' . $key);

        if ($property_id != '0') {
            update_post_meta( $key, '_wiwi_websolutions_bookingsystem_property_id', $property_id );

            $data[$property_id] = $key;
        }
    }

    $data = [
        'data' => $data,
    ];

    $data = json_encode($data);

    $response = WWBS_CallBookingSystem('POST', '/v1/properties/connect', $data);

    return;
}



//======================================================================
// Function to create the page with fields via Carbon Fields
//======================================================================
function wiwi_websolutions_bookingsystem_attach_theme_options() {

    $fields = [];
    if (isset($_GET['page']) && $_GET['page'] == 'crb_carbon_fields_container_bookingsystem.php') {
        //-----------------------------------------------------
        // Create an array for the fields
        //-----------------------------------------------------
        $fields = [
            Field::make( 'text', 'wiwi_websolutions_bookingsystem_api_key', 'API Key' ),
            Field::make( 'text', 'wiwi_websolutions_bookingsystem_api_base_url', 'API Base URL' ),
            Field::make( 'text', 'wiwi_websolutions_bookingsystem_wp_post_type', 'WordPress Post Type' )
                ->set_help_text( 'post, page, or custom post type' ),
            Field::make( 'textarea', 'wiwi_websolutions_bookingsystem_booking_success_message', 'Booking Succes Message' )
                ->set_help_text( 'Text that will be displayed when a booking is succesful' ),
            Field::make( 'textarea', 'wiwi_websolutions_bookingsystem_booking_fail_message', 'Booking Failed Message' )
                ->set_help_text( 'Text that will be displayed when a booking fails' ),
            Field::make( 'text', 'wiwi_websolutions_bookingsystem_booking_succes_page_id', 'Booking Succes Result Page' )
                ->set_help_text( 'ID for the page when a booking is successful' ),
            Field::make( 'text', 'wiwi_websolutions_bookingsystem_booking_terms_agreement_text', 'Booking Terms Agreement Text' ),
            Field::make( 'textarea', 'wiwi_websolutions_bookingsystem_booking_intro_text', 'Booking Form Intro Text' ),
            Field::make( 'text', 'wiwi_websolutions_bookingsystem_booking_option_text', 'Booking Option Checkmark Text' ),
            Field::make( 'textarea', 'wiwi_websolutions_bookingsystem_booking_option_description', 'Booking Option Checkmark Description' ),            
        ];

        //-----------------------------------------------------
        // Dynamically add a field to the array for each property returned by WordPress
        // Add a default empty item to the select, will be saved in the database as 0
        //-----------------------------------------------------
        $array_properties_bookingsystem = WWBS_get_properties_bookingsystem();
        array_unshift($array_properties_bookingsystem, "---");
        foreach(WWBS_get_properties_wp() as $key => $value):
            array_push($fields, Field::make( 'select',  'wiwi_websolutions_bookingsystem_property_' . $key, $value )->set_options($array_properties_bookingsystem));
        endforeach;
    }


    //-----------------------------------------------------
    // Generate the created fields by Carbon Fields
    //-----------------------------------------------------
    $wiwi_websolutions_bookingsystem_settings_page = Container::make( 'theme_options', __( 'Bookingsystem' ) )
        ->set_icon( 'dashicons-database-view' )
        ->add_fields($fields);

}
