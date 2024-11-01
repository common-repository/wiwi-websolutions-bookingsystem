

<?php
$wiwi_websolutions_bookingsystem_plugin = is_plugin_active( 'wiwi-websolutions-bookingsystem/wiwi-websolutions-bookingsystem.php' );
if (!$wiwi_websolutions_bookingsystem_plugin) {
    return ;
}
$bookingsystem_id = get_post_meta( get_the_ID(), '_wiwi_websolutions_bookingsystem_property_id', true );
$on_booking_success_message = get_option( '_wiwi_websolutions_bookingsystem_booking_success_message' );
$on_booking_fail_message = get_option( '_wiwi_websolutions_bookingsystem_booking_fail_message' );
$success_page_id = get_option( '_wiwi_websolutions_bookingsystem_booking_succes_page_id' );
$booking_intro_text = get_option( '_wiwi_websolutions_bookingsystem_booking_intro_text' );
$booking_terms_agreement_text= get_option( '_wiwi_websolutions_bookingsystem_booking_terms_agreement_text' );
$booking_option_description = get_option( '_wiwi_websolutions_bookingsystem_booking_option_description' );
$booking_option_text = get_option( '_wiwi_websolutions_bookingsystem_booking_option_text' );

?>

<style>
    #calendar .event-start { 
        background: rgb(255,0,0) !important;
        background: -moz-linear-gradient(135deg, rgba(220,53,69,1) 0%, rgba(220,53,69,1) 50%, rgba(40,167,69,1) 50%, rgba(40,167,69,1) 100%) !important;
        background: -webkit-linear-gradient(135deg, rgba(220,53,69,1) 0%, rgba(220,53,69,1) 50%, rgba(40,167,69,1) 50%, rgba(40,167,69,1) 100%) !important;
        background: linear-gradient(135deg, rgba(220,53,69,1) 0%, rgba(220,53,69,1) 50%, rgba(40,167,69,1) 50%, rgba(40,167,69,1) 100%) !important;
        filter: progid:DXImageTransform.Microsoft.gradient(startColorstr="#ff0000",endColorstr="#00ff00",GradientType=1) !important;
    }
    #calendar .event-end { 
        background: rgb(255,0,0) !important;
        background: -moz-linear-gradient(315deg, rgba(220,53,69,1) 0%, rgba(220,53,69,1) 50%, rgba(40,167,69,1) 50%, rgba(40,167,69,1) 100%) !important;
        background: -webkit-linear-gradient(315deg, rgba(220,53,69,1) 0%, rgba(220,53,69,1) 50%, rgba(40,167,69,1) 50%, rgba(40,167,69,1) 100%) !important;
        background: linear-gradient(315deg, rgba(220,53,69,1) 0%, rgba(220,53,69,1) 50%, rgba(40,167,69,1) 50%, rgba(40,167,69,1) 100%) !important;
        filter: progid:DXImageTransform.Microsoft.gradient(startColorstr="#ff0000",endColorstr="#00ff00",GradientType=1) !important;
    }
    #calendar .event-start.in_option { 
        background: rgb(255,0,0) !important;
        background: -moz-linear-gradient(135deg, rgba(220,210,53,1) 0%, rgba(220,210,53,1) 50%, rgba(40,167,69,1) 50%, rgba(40,167,69,1) 100%) !important;
        background: -webkit-linear-gradient(135deg, rgba(220,210,53,1) 0%, rgba(220,210,53,1) 50%, rgba(40,167,69,1) 50%, rgba(40,167,69,1) 100%) !important;
        background: linear-gradient(135deg, rgba(220,210,53,1) 0%, rgba(220,210,53,1) 50%, rgba(40,167,69,1) 50%, rgba(40,167,69,1) 100%) !important;
        filter: progid:DXImageTransform.Microsoft.gradient(startColorstr="#ff0000",endColorstr="#00ff00",GradientType=1) !important;
    }
    #calendar .event-end.in_option { 
        background: rgb(255,0,0) !important;
        background: -moz-linear-gradient(315deg, rgba(220,210,53,1) 0%, rgba(220,210,53,1) 50%, rgba(40,167,69,1) 50%, rgba(40,167,69,1) 100%) !important;
        background: -webkit-linear-gradient(315deg, rgba(220,210,53,1) 0%, rgba(220,210,53,1) 50%, rgba(40,167,69,1) 50%, rgba(40,167,69,1) 100%) !important;
        background: linear-gradient(315deg, rgba(220,210,53,1) 0%, rgba(220,210,53,1) 50%, rgba(40,167,69,1) 50%, rgba(40,167,69,1) 100%) !important;
        filter: progid:DXImageTransform.Microsoft.gradient(startColorstr="#ff0000",endColorstr="#00ff00",GradientType=1) !important;
    }
    #calendar .fc-bg-event {
        opacity: 1!important;
        opacity: var(--fc-bg-event-opacity,1)!important;
    } 
    #calendar .fc-daygrid-day-number {
        color: #fff!important;
    }
</style>

<?php if ($wiwi_websolutions_bookingsystem_plugin && $bookingsystem_id): ?> 
    <input type="hidden" value="<?php echo esc_html($bookingsystem_id); ?>" id="property-id">
    <input type="hidden" value="<?php echo esc_html($on_booking_success_message); ?>" id="on-booking-success-message">
    <input type="hidden" value="<?php echo esc_html($on_booking_fail_message); ?>" id="on-booking-fail-message">
    <input type="hidden" value="<?php echo esc_html($success_page_id); ?>" id="success-page-id">

<div id="booking-wrapper" class="container px-0">
    <div id="booking-step0" class="row">
        <div class="col-12 col-md-9 col-lg-6">
            <div class="calendar-wrap position-relative">
                <div id="loadingCalendar" class="loaders z-index-1 position-absolute"
                    style="top: 50%;left: 50%;transform: translate(-50%, -50%);">
                    <div class="spinner-grow text-primary" role="status" style="width: 5rem; height: 5rem;">
                        <span class="sr-only"></span>
                    </div>
                </div>
                <div id="calendar"></div>
            </div>
        </div>
        <div class="col-12 col-md-3 col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5><?php _e('Reserve your accommodation - Quote', 'wiwi-websolutions-bookingsystem'); ?></h5>
                </div>
                <div class="card-body">
                    <?php if($booking_intro_text): ?>
                        <p class="pb-3"><?php echo $booking_intro_text; ?></p>
                    <?php endif; ?>
                    <form action="" id="booking_form" class="form">
                        <div class="row mb-2">
                            <div class="col-12 col-md-6">
                                <label
                                    for="arrival_date"><?php _e('Date of arrival', 'wiwi-websolutions-bookingsystem'); ?></label>
                            </div>
                            <div class="col-12 col-md-6">
                                <input type="text" class="form-control" id="arrival_date" name="arrival_date" disabled>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-12 col-md-6">
                                <label label
                                    for="departure_date"><?php _e('Date of departure', 'wiwi-websolutions-bookingsystem'); ?></label>
                            </div>
                            <div class="col-12 col-md-6">
                                <input type="text" class="form-control" id="departure_date" name="departure_date"
                                    disabled>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-12 col-md-6">
                                <label
                                    for="amount_of_tenants"><?php _e('Amount of persons', 'wiwi-websolutions-bookingsystem'); ?></label>
                            </div>
                            <div class="col-12 col-md-6">
                                <select name="amount_of_tenants" id="amount_of_tenants" class="form-control">
                                </select>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-12 col-md-6">
                                <label
                                    for="rental_price"><?php _e('Rental price', 'wiwi-websolutions-bookingsystem'); ?></label>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="input-group mb-3">
                                    <span class="input-group-text">&euro;</span>
                                    <input type="text" class="form-control" id="rental_price" name="rental_price"
                                        disabled>
                                </div>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-12 col-md-6">
                                <label
                                    for="reservation_costs"><?php _e('Reservation fee', 'wiwi-websolutions-bookingsystem'); ?></label>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="input-group mb-3">
                                    <span class="input-group-text">&euro;</span>
                                    <input type="text" class="form-control" id="reservation_costs"
                                        name="reservation_costs" disabled>
                                </div>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-12 col-md-6">
                                <label
                                    for="additional_options"><?php _e('Options', 'wiwi-websolutions-bookingsystem'); ?></label>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="input-group mb-3" id="additional_options_group">
                                </div>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-12 col-md-6">
                                <label
                                    for="total_price"><?php _e('Total price', 'wiwi-websolutions-bookingsystem'); ?></label>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="input-group mb-3">
                                    <span class="input-group-text">&euro;</span>
                                    <input type="text" class="form-control" id="total_price" name="total_price"
                                        disabled>
                                </div>
                            </div>
                        </div>
                        <button class="btn btn-primary d-none" type="submit"
                            data-connect="start-booking-btn"><?php _e('Start booking!', 'wiwi-websolutions-bookingsystem'); ?></button>
                        <button class="btn btn-primary" type="button" id="reset-btn"><?php _e('Reset', 'wiwi-websolutions-bookingsystem'); ?></button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="row py-5">
        <div class="col-12">
            <div id="booking-stepper" class="d-none">
                <ul class="nav nav-pills mb-3 w-100 justify-content-evenly" id="booking-tab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" disabled id="booking-personal-data-tab" data-bs-toggle="pill"
                            data-bs-target="#booking-personal-data" data-toggle="pill" data-target="#booking-personal-data" type="button" role="tab"
                            aria-controls="booking-personal-data" aria-selected="true"> 1.
                            <?php _e('Your personal data', 'wiwi-websolutions-bookingsystem'); ?> </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" disabled id="booking-check-personal-data-tab" data-bs-toggle="pill"
                            data-bs-target="#booking-check-personal-data" data-toggle="pill" data-target="#booking-check-personal-data" type="button" role="tab"
                            aria-controls="booking-check-personal-data" aria-selected="false"> 2.
                            <?php _e('Check your personal data', 'wiwi-websolutions-bookingsystem'); ?> </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" disabled id="booking-confirmation-tab" data-bs-toggle="pill"
                            data-bs-target="#booking-confirmation" data-toggle="pill" data-target="#booking-confirmation" type="button" role="tab"
                            aria-controls="booking-confirmation" aria-selected="false"> 3.
                            <?php _e('Confirm your booking', 'wiwi-websolutions-bookingsystem'); ?> </button>
                    </li>
                </ul>
                <div class="tab-content" id="booking-tabContent">
                    <div class="tab-pane fade show active" id="booking-personal-data" role="tabpanel"
                        aria-labelledby="booking-personal-data-tab">
                        <div class="card" id="main-tenant-data">
                            <div class="card-header">
                                <h5><?php _e('Your personal data', 'wiwi-websolutions-bookingsystem'); ?></h5>
                            </div>
                            <div class="card-body">
                                <form class="needs-validation" id="personal-data-form" action="" novalidate>
                                    <div class="row mb-3 align-items-center">
                                        <div class="col-12 col-md-4">
                                            <label
                                                for="gender"><?php _e('Salutation', 'wiwi-websolutions-bookingsystem'); ?>:</label>
                                        </div>
                                        <div class="col-12 col-md-8">
                                            <select name="gender" class="form-control">
                                                <option value="m" selected>
                                                    <?php _e('Mr', 'wiwi-websolutions-bookingsystem'); ?>.</option>
                                                <option value="f">
                                                    <?php _e('Mrs', 'wiwi-websolutions-bookingsystem'); ?>.</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mb-3 align-items-center">
                                        <div class="col-12 col-md-4">
                                            <label
                                                for="firstname"><?php _e('First name', 'wiwi-websolutions-bookingsystem'); ?>:</label>
                                        </div>
                                        <div class="col-12 col-md-8">
                                            <input type="text" name="firstname" class="form-control" required>
                                            <div class="valid-feedback">
                                                <?php _e('Looks good!', 'wiwi-websolutions-bookingsystem'); ?> </div>
                                            <div class="invalid-feedback">
                                                <?php _e('Please provide a', 'wiwi-websolutions-bookingsystem'); ?>
                                                <?php _e('First name', 'wiwi-websolutions-bookingsystem'); ?> </div>
                                        </div>
                                    </div>
                                    <div class="row mb-3 align-items-center">
                                        <div class="col-12 col-md-4">
                                            <label
                                                for="lastname"><?php _e('Last name', 'wiwi-websolutions-bookingsystem'); ?>:</label>
                                        </div>
                                        <div class="col-12 col-md-8">
                                            <input type="text" name="lastname" class="form-control" required>
                                            <div class="valid-feedback">
                                                <?php _e('Looks good!', 'wiwi-websolutions-bookingsystem'); ?> </div>
                                            <div class="invalid-feedback">
                                                <?php _e('Please provide a', 'wiwi-websolutions-bookingsystem'); ?>
                                                <?php _e('Last name', 'wiwi-websolutions-bookingsystem'); ?> </div>
                                        </div>
                                    </div>
                                    <div class="row mb-3 align-items-center">
                                        <div class="col-12 col-md-4">
                                            <label
                                                for="street_housenumber"><?php _e('Street + house number', 'wiwi-websolutions-bookingsystem'); ?>:</label>
                                        </div>
                                        <div class="col-12 col-md-8">
                                            <div class="row">
                                                <div class="col">
                                                    <input type="text" name="street" class="form-control" required>
                                                    <div class="valid-feedback">
                                                        <?php _e('Looks good!', 'wiwi-websolutions-bookingsystem'); ?>
                                                    </div>
                                                    <div class="invalid-feedback">
                                                        <?php _e('Please provide a', 'wiwi-websolutions-bookingsystem'); ?>
                                                        <?php _e('Street', 'wiwi-websolutions-bookingsystem'); ?> </div>
                                                </div>
                                                <div class="col">
                                                    <input type="text" name="housenumber" class="form-control" required>
                                                    <div class="valid-feedback">
                                                        <?php _e('Looks good!', 'wiwi-websolutions-bookingsystem'); ?>
                                                    </div>
                                                    <div class="invalid-feedback">
                                                        <?php _e('Please provide a', 'wiwi-websolutions-bookingsystem'); ?>
                                                        <?php _e('House number', 'wiwi-websolutions-bookingsystem'); ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-3 align-items-center">
                                        <div class="col-12 col-md-4">
                                            <label
                                                for="zipcode_city"><?php _e('Zipcode + City', 'wiwi-websolutions-bookingsystem'); ?>:</label>
                                        </div>
                                        <div class="col-12 col-md-8">
                                            <div class="row">
                                                <div class="col">
                                                    <input type="text" name="zipcode" class="form-control" required>
                                                    <div class="valid-feedback">
                                                        <?php _e('Looks good!', 'wiwi-websolutions-bookingsystem'); ?>
                                                    </div>
                                                    <div class="invalid-feedback">
                                                        <?php _e('Please provide a', 'wiwi-websolutions-bookingsystem'); ?>
                                                        <?php _e('Zipcode', 'wiwi-websolutions-bookingsystem'); ?>
                                                    </div>
                                                </div>
                                                <div class="col">
                                                    <input type="text" name="city" class="form-control" required>
                                                    <div class="valid-feedback">
                                                        <?php _e('Looks good!', 'wiwi-websolutions-bookingsystem'); ?>
                                                    </div>
                                                    <div class="invalid-feedback">
                                                        <?php _e('Please provide a', 'wiwi-websolutions-bookingsystem'); ?>
                                                        <?php _e('City', 'wiwi-websolutions-bookingsystem'); ?> </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-3 align-items-center">
                                        <div class="col-12 col-md-4">
                                            <label
                                                for="country"><?php _e('Country', 'wiwi-websolutions-bookingsystem'); ?>:</label>
                                        </div>
                                        <div class="col-12 col-md-8">
                                            <select name="country" class="form-control">
                                                <option value="NLD" selected>
                                                    <?php _e('Netherlands', 'wiwi-websolutions-bookingsystem'); ?>
                                                </option>
                                                <option value="BEL">
                                                    <?php _e('Belgium', 'wiwi-websolutions-bookingsystem'); ?></option>
                                                <option value="DEU">
                                                    <?php _e('Germany', 'wiwi-websolutions-bookingsystem'); ?></option>
                                                <option value="GBR">
                                                    <?php _e('United Kingdom', 'wiwi-websolutions-bookingsystem'); ?>
                                                </option>
                                                <option value="FRA">
                                                    <?php _e('France', 'wiwi-websolutions-bookingsystem'); ?></option>
                                                <option value="PRT">
                                                    <?php _e('Portugal', 'wiwi-websolutions-bookingsystem'); ?></option>
                                                <option value="ESP">
                                                    <?php _e('Spain', 'wiwi-websolutions-bookingsystem'); ?></option>
                                                <option value="CHE">
                                                    <?php _e('Switzerland', 'wiwi-websolutions-bookingsystem'); ?>
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mb-3 align-items-center">
                                        <div class="col-12 col-md-4">
                                            <label
                                                for="phonenumber"><?php _e('Phone number', 'wiwi-websolutions-bookingsystem'); ?>:</label>
                                        </div>
                                        <div class="col-12 col-md-8">
                                            <input type="text" name="phonenumber" class="form-control" required>
                                            <div class="valid-feedback">
                                                <?php _e('Looks good!', 'wiwi-websolutions-bookingsystem'); ?> </div>
                                            <div class="invalid-feedback">
                                                <?php _e('Please provide a', 'wiwi-websolutions-bookingsystem'); ?>
                                                <?php _e('Phone number', 'wiwi-websolutions-bookingsystem'); ?> </div>
                                        </div>
                                    </div>
                                    <div class="row mb-3 align-items-center">
                                        <div class="col-12 col-md-4">
                                            <label
                                                for="secondary-phonenumber"><?php _e('Phone number (secondary)', 'wiwi-websolutions-bookingsystem'); ?>:</label>
                                        </div>
                                        <div class="col-12 col-md-8">
                                            <input type="text" name="secondary-phonenumber" class="form-control"
                                                required>
                                            <div class="valid-feedback">
                                                <?php _e('Looks good!', 'wiwi-websolutions-bookingsystem'); ?> </div>
                                            <div class="invalid-feedback">
                                                <?php _e('Please provide a', 'wiwi-websolutions-bookingsystem'); ?>
                                                <?php _e('Phone number (secondary)', 'wiwi-websolutions-bookingsystem'); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-3 align-items-center">
                                        <div class="col-12 col-md-4">
                                            <label
                                                for="date_of_birth"><?php _e('Date of birth', 'wiwi-websolutions-bookingsystem'); ?>:</label>
                                        </div>
                                        <div class="col-12 col-md-8">
                                            <input type="date" name="date_of_birth" class="form-control" required>
                                            <div class="valid-feedback">
                                                <?php _e('Looks good!', 'wiwi-websolutions-bookingsystem'); ?> </div>
                                            <div class="invalid-feedback">
                                                <?php _e('Please provide a', 'wiwi-websolutions-bookingsystem'); ?>
                                                <?php _e('Date of birth', 'wiwi-websolutions-bookingsystem'); ?> </div>
                                        </div>
                                    </div>
                                    <div class="row mb-3 align-items-center">
                                        <div class="col-12 col-md-4">
                                            <label
                                                for="email"><?php _e('E-mail address', 'wiwi-websolutions-bookingsystem'); ?>:</label>
                                        </div>
                                        <div class="col-12 col-md-8">
                                            <input type="email" name="email" class="form-control" required>
                                            <div class="valid-feedback">
                                                <?php _e('Looks good!', 'wiwi-websolutions-bookingsystem'); ?> </div>
                                            <div class="invalid-feedback">
                                                <?php _e('Please provide a', 'wiwi-websolutions-bookingsystem'); ?>
                                                <?php _e('E-mail address', 'wiwi-websolutions-bookingsystem'); ?> </div>
                                        </div>
                                    </div>
                                    <div class="row mb-3 align-items-center">
                                        <div class="col-12 col-md-4">
                                            <label
                                                for="email-check"><?php _e('E-mail address (check)', 'wiwi-websolutions-bookingsystem'); ?>:</label>
                                        </div>
                                        <div class="col-12 col-md-8">
                                            <input type="email" name="email-check" class="form-control" required>
                                            <div class="valid-feedback">
                                                <?php _e('Looks good!', 'wiwi-websolutions-bookingsystem'); ?> </div>
                                            <div class="invalid-feedback">
                                                <?php _e('Please provide a', 'wiwi-websolutions-bookingsystem'); ?>
                                                <?php _e('E-mail address', 'wiwi-websolutions-bookingsystem'); ?> </div>
                                        </div>
                                    </div>
                                    <div class="row mb-3 align-items-center">
                                        <div class="col-12 col-md-4">
                                            <label
                                                for="comment"><?php _e('Comment', 'wiwi-websolutions-bookingsystem'); ?>:</label>
                                        </div>
                                        <div class="col-12 col-md-8">
                                            <div class="form-floating">
                                                <textarea class="form-control" name="comment"></textarea>
                                                <label
                                                    for="floatingTextarea"><?php _e('Comment', 'wiwi-websolutions-bookingsystem'); ?></label>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="card mt-5">
                            <div class="card-header">
                                <h5><?php _e('Your Co-tenants (including children)', 'wiwi-websolutions-bookingsystem'); ?>
                                </h5>
                            </div>
                            <div class="card-body">
                                <form action="" id="sub-tenants-data">
                                    <div class="row mb-3 align-items-center" data-copy="booking-subtenant-row">
                                        <div class="col-12 col-md-2">
                                            <label
                                                for="gender"><?php _e('Salutation', 'wiwi-websolutions-bookingsystem'); ?>:</label>
                                            <select name="gender" class="form-control">
                                                <option value="m">
                                                    <?php _e('Mr', 'wiwi-websolutions-bookingsystem'); ?>.</option>
                                                <option value="f">
                                                    <?php _e('Mrs', 'wiwi-websolutions-bookingsystem'); ?>.</option>
                                            </select>
                                        </div>
                                        <div class="col">
                                            <label
                                                for="firstname"><?php _e('First name', 'wiwi-websolutions-bookingsystem'); ?>:</label>
                                            <input type="text" name="firstname" class="form-control">
                                        </div>
                                        <div class="col">
                                            <label
                                                for="lastname"><?php _e('Last name', 'wiwi-websolutions-bookingsystem'); ?>:</label>
                                            <input type="text" name="lastname" class="form-control">
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <label
                                                for="date_of_birth"><?php _e('Date of birth', 'wiwi-websolutions-bookingsystem'); ?>:</label>
                                            <input type="date" name="date_of_birth" class="form-control">
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="card mt-5" id="newsletter-card">
                            <div class="card-header">
                                <h5><?php _e('Newsletter', 'wiwi-websolutions-bookingsystem'); ?></h5>
                            </div>
                            <div class="card-body">
                                <form action="" id="">
                                    <div class="row mb-3 align-items-center">
                                        <div class="col">
                                            <label for="newsletter">Vakantietips, aanbiedingen en ons laatste nieuws
                                                ontvangen? <br>Schrijf u in voor onze maandelijkse nieuwsbrief!</label>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="flexRadioDefault"
                                                    id="flexRadioDefault1" checked>
                                                <label class="form-check-label" for="newsletterTrue">
                                                    <?php _e('Yes', 'wiwi-websolutions-bookingsystem'); ?> </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="flexRadioDefault"
                                                    id="flexRadioDefault2">
                                                <label class="form-check-label" for="newsletterFalse">
                                                    <?php _e('No', 'wiwi-websolutions-bookingsystem'); ?> </label>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="card mt-5">
                            <div class="card-header">
                                <h5><?php _e('Check your personal data', 'wiwi-websolutions-bookingsystem'); ?></h5>
                            </div>
                            <div class="card-body">
                                <p><?php _e('(Reservation not final yet)', 'wiwi-websolutions-bookingsystem'); ?></p>
                                <button class="btn btn-primary" data-target="step2"
                                    data-validate="#personal-data-form"><?php _e('Proceed to step 2', 'wiwi-websolutions-bookingsystem'); ?></button>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="booking-check-personal-data" role="tabpanel"
                        aria-labelledby="booking-check-personal-data-tab">
                        <button class="btn btn-primary"
                            data-target="step1"><?php _e('Previous step', 'wiwi-websolutions-bookingsystem'); ?></button>
                        <div class="card mt-5">
                            <div class="card-header">
                                <h5><?php _e('Details of the main tenant', 'wiwi-websolutions-bookingsystem'); ?></h5>
                            </div>
                            <div class="card-body">
                                <div class="row mb-2 align-items-center">
                                    <div class="col-12 col-md-2">
                                        <p class="mb-0"><?php _e('Name', 'wiwi-websolutions-bookingsystem'); ?>:</p>
                                    </div>
                                    <div class="col-10">
                                        <p class="mb-0" data-connect="name-check">{{gender}} {{firstname}}
                                            {{lastname}}
                                        </p>
                                    </div>
                                </div>
                                <div class="row mb-2 align-items-center">
                                    <div class="col-12 col-md-2">
                                        <p class="mb-0"><?php _e('Address', 'wiwi-websolutions-bookingsystem'); ?>:</p>
                                    </div>
                                    <div class="col-10">
                                        <p class="mb-0" data-connect="address-check">{{street}} {{housenumber}}</p>
                                    </div>
                                </div>
                                <div class="row mb-2 align-items-center">
                                    <div class="col-12 col-md-2">
                                        <p class="mb-0">
                                            <?php _e('Zipcode + City', 'wiwi-websolutions-bookingsystem'); ?>:</p>
                                    </div>
                                    <div class="col-10">
                                        <p class="mb-0" data-connect="zipcode-city-check">{{zipcode}}</p>
                                    </div>
                                </div>
                                <div class="row mb-2 align-items-center">
                                    <div class="col-12 col-md-2">
                                        <p class="mb-0"><?php _e('Country', 'wiwi-websolutions-bookingsystem'); ?>:</p>
                                    </div>
                                    <div class="col-10">
                                        <p class="mb-0" data-connect="country-check">{{city}}</p>
                                    </div>
                                </div>
                                <div class="row mb-2 align-items-center">
                                    <div class="col-12 col-md-2">
                                        <p class="mb-0">
                                            <?php _e('Primary phone number', 'wiwi-websolutions-bookingsystem'); ?>:</p>
                                    </div>
                                    <div class="col-10">
                                        <p class="mb-0" data-connect="phone-check">{{phonenumber}}</p>
                                    </div>
                                </div>
                                <div class="row mb-2 align-items-center">
                                    <div class="col-12 col-md-2">
                                        <p class="mb-0">
                                            <?php _e('Secondary phone number', 'wiwi-websolutions-bookingsystem'); ?>:
                                        </p>
                                    </div>
                                    <div class="col-10">
                                        <p class="mb-0" data-connect="secondary-phone-check">{{secondary-phonenumber}}
                                        </p>
                                    </div>
                                </div>
                                <div class="row mb-2 align-items-center">
                                    <div class="col-12 col-md-2">
                                        <p class="mb-0">
                                            <?php _e('E-mail address', 'wiwi-websolutions-bookingsystem'); ?>:</p>
                                    </div>
                                    <div class="col-10">
                                        <p class="mb-0" data-connect="email-check">{{email}}</p>
                                    </div>
                                </div>
                                <div class="row mb-2 align-items-center">
                                    <div class="col-12 col-md-2">
                                        <p class="mb-0">
                                            <?php _e('Date of birth', 'wiwi-websolutions-bookingsystem'); ?>:</p>
                                    </div>
                                    <div class="col-10">
                                        <p class="mb-0" data-connect="date-of-birth-check">{{Geboortedatum}}</p>
                                    </div>
                                </div>
                                <div class="row mb-2 align-items-center">
                                    <div class="col-12 col-md-2">
                                        <p class="mb-0"><?php _e('Comment', 'wiwi-websolutions-bookingsystem'); ?>:</p>
                                    </div>
                                    <div class="col-10">
                                        <p class="mb-0" data-connect="comment-check">{{Opmerkingen}}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card mt-5">
                            <div class="card-header">
                                <h5><?php _e('Your Co-tenants (including children)', 'wiwi-websolutions-bookingsystem'); ?>
                                </h5>
                            </div>
                            <div class="card-body" data-connect="subtenants-check">
                                <div class="row mb-3 align-items-center">
                                    <div class="col-12">
                                        <p></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card mt-5">
                            <div class="card-header">
                                <h5><?php _e('Confirm your booking', 'wiwi-websolutions-bookingsystem'); ?></h5>
                            </div>
                            <div class="card-body">
                                <p><?php _e('(Reservation not final yet)', 'wiwi-websolutions-bookingsystem'); ?></p>
                                <button class="btn btn-primary"
                                    data-target="step1"><?php _e('Previous step', 'wiwi-websolutions-bookingsystem'); ?></button>
                                <button class="btn btn-primary"
                                    data-target="step3"><?php _e('Go to step 3', 'wiwi-websolutions-bookingsystem'); ?></button>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="booking-confirmation" role="tabpanel"
                        aria-labelledby="booking-confirmation-tab">
                        <div class="card my-5">
                            <div class="card-header">
                                <h5><?php _e('Overview of the booking', 'wiwi-websolutions-bookingsystem'); ?></h5>
                            </div>
                            <div class="card-body">
                                <div class="row mb-2 align-items-center">
                                    <div class="col-12 col-md-2">
                                        <p class="mb-0">
                                            <?php _e('Vacation home', 'wiwi-websolutions-bookingsystem'); ?>:</p>
                                    </div>
                                    <div class="col-10">
                                        <p class="mb-0"><?php echo get_the_title(); ?></p>
                                    </div>
                                </div>
                                <div class="row mb-2 align-items-center">
                                    <div class="col-12 col-md-2">
                                        <p class="mb-0">
                                            <?php _e('Date of arrival', 'wiwi-websolutions-bookingsystem'); ?>:</p>
                                    </div>
                                    <div class="col-10">
                                        <p class="mb-0" data-connect="arrival-check"></p>
                                    </div>
                                </div>
                                <div class="row mb-2 align-items-center">
                                    <div class="col-12 col-md-2">
                                        <p class="mb-0">
                                            <?php _e('Date of departure', 'wiwi-websolutions-bookingsystem'); ?>:</p>
                                    </div>
                                    <div class="col-10">
                                        <p class="mb-0" data-connect="departure-check"></p>
                                    </div>
                                </div>
                                <div class="row mb-2 align-items-center">
                                    <div class="col-12 col-md-2">
                                        <p class="mb-0">
                                            <?php _e('Amount of persons', 'wiwi-websolutions-bookingsystem'); ?>:</p>
                                    </div>
                                    <div class="col-10">
                                        <p class="mb-0" data-connect="amount-of-persons-check"></p>
                                    </div>
                                </div>
                                <div class="row mb-2 align-items-center">
                                    <div class="col-12 col-md-2">
                                        <p class="mb-0">
                                            <?php _e('Options', 'wiwi-websolutions-bookingsystem'); ?>:</p>
                                    </div>
                                    <div class="col-10">
                                        <p class="mb-0" data-connect="additional-options-check"></p>
                                    </div>
                                </div>
                                <div class="row mb-2 align-items-center">
                                    <div class="col-12 col-md-2">
                                        <p class="mb-0"><?php _e('Total amount', 'wiwi-websolutions-bookingsystem'); ?>:
                                        </p>
                                    </div>
                                    <div class="col-10">
                                        <p class="mb-0" data-connect="total-price-check"></p>
                                    </div>
                                </div>
                                <form action="" id="confirm-booking-form" class="needs-validation" novalidate>
                                    <?php if($booking_option_text): ?>
                                        <div class="row mb-2 mt-5 align-items-center">
                                            <div class="col">
                                                <div class="form-check ps-0">
                                                    <input class="form-check-input" name="is_option" type="checkbox" id="is_option_checkbox">
                                                    <label class="form-check-label" for="is_option_checkbox">
                                                        <?php echo $booking_option_text; ?>
                                                    </label>
                                                    <?php if($booking_option_description): ?>
                                                        <small>
                                                            <?php echo $booking_option_description; ?>
                                                        </small>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    <div class="row mb-2 mt-5 align-items-center">
                                        <div class="col">
                                            <div class="form-check ps-0">
                                                <input class="form-check-input" type="checkbox" id="terms" required>
                                                <label class="form-check-label" for="terms">
                                                    <?php if($booking_terms_agreement_text): ?>
                                                        <?php echo $booking_terms_agreement_text; ?>
                                                    <?php else: ?>
                                                        <?php _e('I agree to the terms and conditions', 'wiwi-websolutions-bookingsystem'); ?>
                                                    <?php endif; ?>
                                                </label>
                                                <div class="invalid-feedback">
                                                    <?php _e('You must agree before submitting', 'wiwi-websolutions-bookingsystem'); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-2 align-items-center">
                                        <div class="col">
                                            <button class="btn btn-primary"
                                                type="submit"><?php _e('Confirm my booking', 'wiwi-websolutions-bookingsystem'); ?></button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <button class="btn btn-primary"
                            data-target="step2"><?php _e('Previous step', 'wiwi-websolutions-bookingsystem'); ?></button>
                    </div>
                </div>
            </div>
        </div>
    </div> 
</div>
<?php endif; ?>
