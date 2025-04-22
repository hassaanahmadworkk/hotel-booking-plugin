<?php
/**
 * Loads the class for handling Custom Metaboxes and Fields.
 *
 * @package G9X_Hotel_Booking
 * @since   1.1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Include the class file that handles the metabox registrations and saving.
require_once G9X_HOTEL_BOOKING_PLUGIN_PATH . 'includes/class-g9x-metaboxes.php';

// Note: The G9X_Metaboxes::init() call inside the class file hooks everything correctly.
// The old procedural functions (g9x_hotel_booking_add_rooms_metaboxes, etc.)
// are no longer needed and have been removed/replaced by the class structure.