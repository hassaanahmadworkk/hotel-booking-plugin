<?php
/**
 * Loads the class for registering Custom Post Types and Taxonomies.
 *
 * @package G9X_Hotel_Booking
 * @since   1.1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Include the class file that handles the registrations.
require_once G9X_HOTEL_BOOKING_PLUGIN_PATH . 'includes/class-g9x-post-types.php';

// Note: The G9X_Post_Types::init() call inside the class file hooks everything correctly.
// The old procedural function g9x_hotel_booking_register_post_types() is no longer needed
// and has been removed/replaced by the class structure.