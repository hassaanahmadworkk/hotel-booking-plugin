<?php
/**
 * Database Setup File
 *
 * NOTE: As of v1.1.0, this plugin relies on Custom Post Types and Post Meta
 * for storing Room, Booking, and Customer data, aligning with WordPress best practices.
 * The custom table creation logic below is deprecated and should not be used
 * as it leads to data redundancy and potential synchronization issues.
 * It is kept here temporarily for reference during the upgrade process but will be removed.
 * The uninstall hook in the main plugin file handles dropping these tables if they exist.
 */

/**
 * Create Database Tables - DEPRECATED
 *
 * This function is no longer called on activation.
 */
/* // Commenting out the entire function to prevent accidental use.
function g9x_hotel_booking_create_tables() {
    global $wpdb;

    $charset_collate = $wpdb->get_charset_collate();

    $rooms_table_name = $wpdb->prefix . 'g9x_rooms';
    $bookings_table_name = $wpdb->prefix . 'g9x_bookings';
    $customers_table_name = $wpdb->prefix . 'g9x_customers';

    // --- Room Table (Deprecated - Use g9x_room CPT) ---
    $sql_rooms = "CREATE TABLE $rooms_table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        title varchar(255) NOT NULL,
        description text NOT NULL,
        room_type varchar(255) NOT NULL,
        capacity_adults int(11) NOT NULL,
        capacity_children int(11) NOT NULL,
        amenities text NOT NULL,
        price_per_night decimal(10,2) NOT NULL,
        availability_calendar text NOT NULL, // Inefficient - consider dedicated availability storage
        featured_image varchar(255) NOT NULL,
        room_gallery text NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    // --- Bookings Table (Deprecated - Use g9x_booking CPT) ---
    $sql_bookings = "CREATE TABLE $bookings_table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        booking_id varchar(255) NOT NULL, // Should be unique
        customer_id mediumint(9) NOT NULL, // Link to wp_users instead?
        room_id mediumint(9) NOT NULL, // Link to post ID of g9x_room CPT
        check_in_date date NOT NULL,
        check_out_date date NOT NULL,
        number_of_guests_adults int(11) NOT NULL,
        number_of_guests_children int(11) NOT NULL,
        total_price decimal(10,2) NOT NULL,
        payment_status varchar(255) NOT NULL,
        booking_status varchar(255) NOT NULL,
        special_requests text NULL,
        coupon_code varchar(255) NULL,
        services_packages text NULL,
        PRIMARY KEY  (id)
        // Add indexes for performance: KEY `room_id` (`room_id`), KEY `customer_id` (`customer_id`), KEY `check_in_date` (`check_in_date`)
    ) $charset_collate;";

    // --- Customers Table (Deprecated - Use WP Users) ---
    $sql_customers = "CREATE TABLE $customers_table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        first_name varchar(255) NOT NULL,
        last_name varchar(255) NOT NULL,
        email_address varchar(255) NOT NULL, // Should be unique
        phone_number varchar(255) NOT NULL,
        address text NOT NULL,
        password varchar(255) NOT NULL, // SECURITY RISK - Use WP Users system
        registration_date datetime NOT NULL,
        PRIMARY KEY  (id)
        // Add indexes for performance: UNIQUE KEY `email_address` (`email_address`)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql_rooms );
    dbDelta( $sql_bookings );
    dbDelta( $sql_customers );
}
*/ // End of commented out function