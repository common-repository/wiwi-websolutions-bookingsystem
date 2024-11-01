# README #
This README would normally document whatever steps are necessary to get your application up and running.


### What is this repository for? ###
* Quick summary
* Version
* [Learn Markdown](https://bitbucket.org/tutorials/markdowndemo)


### How do I get set up? ###
* Summary of set up
* Configuration
* Dependencies
* Database configuration
* How to run tests
* Deployment instructions
* Compiling SCSS to CSS:
`sass --watch app.scss "../css/wiwi-websolutions-bookingsystem.css"`


### Usage in themes ###
* How to check if the plugin exists and is active:
```
//======================================================================
// Detect plugin. For frontend only
//======================================================================
include_once ABSPATH . 'wp-admin/includes/plugin.php';



//======================================================================
// Check for plugin using plugin name
//======================================================================
$wiwi_websolutions_bookingsystem_plugin = is_plugin_active( 'wiwi-websolutions-bookingsystem/wiwi-websolutions-bookingsystem.php' ) ?? "";
if ($wiwi_websolutions_bookingsystem_plugin) {
    $bookingsystem_id = get_post_meta( get_the_ID(), '_wiwi_websolutions_bookingsystem_property_id', true ) ?? "";
}

//-----------------------------------------------------
// Check if plugin is active and variable is not empty
//-----------------------------------------------------
?>
<?php if($wiwi_websolutions_bookingsystem_plugin && $bookingsystem_id): ?>
    <?= $bookingsystem_id ?>
<?php endif; ?>
}
```
* Get metadata created by the plugin:
```
<?php if(get_post_meta( get_the_ID(), '_wiwi_websolutions_bookingsystem_max_persons', true )): ?>
    <?= get_post_meta( get_the_ID(), '_wiwi_websolutions_bookingsystem_max_persons', true ) ?>
<?php endif; ?>
```

### Data created by the plugin ###
* `_options` table:
    * `_wiwi_websolutions_bookingsystem_api_key`
    * `_wiwi_websolutions_bookingsystem_api_base_url`
    * `_wiwi_websolutions_bookingsystem_wp_post_type`
    * `_wiwi_websolutions_bookingsystem_property_{{wp_post_id}}`
    * `_wiwi_websolutions_bookingsystem_booking_succes_page_id`
    * `_wiwi_websolutions_bookingsystem_booking_success_message`
    * `_wiwi_websolutions_bookingsystem_booking_fail_message`
    * `_wiwi_websolutions_bookingsystem_booking_terms_agreement_text`
    * `_wiwi_websolutions_bookingsystem_booking_intro_text`
    * `_wiwi_websolutions_bookingsystem_booking_option_text`
    * `_wiwi_websolutions_bookingsystem_booking_option_description`
* `_postmeta`
    * `_wiwi_websolutions_bookingsystem_property_id`
    * `_wiwi_websolutions_bookingsystem_max_persons`
    * `_wiwi_websolutions_bookingsystem_last_minute`
    * `_wiwi_websolutions_bookingsystem_lowest_price`
    * `_wiwi_websolutions_bookingsystem_date_departure`
    * `_wiwi_websolutions_bookingsystem_date_arrival`


### Endpoints/API created by the plugin ###
* `/wp-json/wiwi-websolutions-bookingsystem/v2/settings/`
* `/wp-json/wiwi-websolutions-bookingsystem/v2/properties/`
* `/wp-json/wiwi-websolutions-bookingsystem/v2/properties/{{bookingsystem_property_id}}`
* `/wp-json/wiwi-websolutions-bookingsystem/v2/properties/{{bookingsystem_property_id}}/update`


### Contribution guidelines ###
* Writing tests
* Code review
* Other guidelines


### Who do I talk to? ###
* Repo owner or admin
* Other community or team contact