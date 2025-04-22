<?php
/**
 * Handles Custom Metaboxes and Fields
 *
 * @package G9X_Hotel_Booking
 * @since   1.1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * G9X_Metaboxes Class.
 */
class G9X_Metaboxes {

	/**
	 * Hook in methods.
	 */
	public static function init() {
		add_action( 'add_meta_boxes', array( __CLASS__, 'add_metaboxes' ) );
		add_action( 'save_post_g9x_room', array( __CLASS__, 'save_room_details' ) );
		add_action( 'save_post_g9x_booking', array( __CLASS__, 'save_booking_details' ) );
		// The save_post_g9x_customer action is intentionally removed as the CPT is deprecated.
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ) );
	}

	/**
	 * Add metaboxes to relevant post types.
     *
     * @param string $post_type The current post type.
	 */
	public static function add_metaboxes( $post_type ) {
        $post_types = array( 'g9x_room', 'g9x_booking' ); // Post types to add metaboxes to

        if ( in_array( $post_type, $post_types ) ) {
            // Room Metabox
            add_meta_box(
                'g9x_room_details',
                __( 'Room Details', 'g9x-hotel-booking' ),
                array( __CLASS__, 'render_room_details_metabox' ),
                'g9x_room',
                'normal',
                'high'
            );

            // Booking Metabox
            add_meta_box(
                'g9x_booking_details',
                __( 'Booking Details', 'g9x-hotel-booking' ),
                array( __CLASS__, 'render_booking_details_metabox' ),
                'g9x_booking',
                'normal',
                'high'
            );
        }

        // Customer Metabox (Deprecated - No longer added)
        /*
        add_meta_box(
            'g9x_customer_details',
            __( 'Customer Details (Deprecated)', 'g9x-hotel-booking' ),
            array( __CLASS__, 'render_customer_details_metabox' ), // Callback kept for reference if needed
            'g9x_customer',
            'normal',
            'high'
        );
        */
	}

    /**
	 * Render the Room Details metabox.
	 *
	 * @param WP_Post $post The post object.
	 */
	public static function render_room_details_metabox( $post ) {
        // Add nonce for security
        wp_nonce_field( 'g9x_save_room_details', 'g9x_room_details_nonce' );

        // Get existing values
        $capacity_adults = get_post_meta( $post->ID, 'g9x_capacity_adults', true );
        $capacity_children = get_post_meta( $post->ID, 'g9x_capacity_children', true );
        $price_per_night = get_post_meta( $post->ID, 'g9x_price_per_night', true );
        $room_gallery = get_post_meta( $post->ID, 'g9x_room_gallery', true );
        $room_size = get_post_meta( $post->ID, 'g9x_room_size', true );
        $max_occupancy = get_post_meta( $post->ID, 'g9x_max_occupancy', true );
        $bed_type = get_post_meta( $post->ID, 'g9x_bed_type', true );
        $availability_status = get_post_meta( $post->ID, 'g9x_availability_status', true );
        $unavailable_dates = get_post_meta( $post->ID, 'g9x_unavailable_dates', true );
        $address = get_post_meta( $post->ID, 'g9x_address', true );

        ?>
        <div class="g9x-hotel-booking-metabox">
            <style>
                .g9x-hotel-booking-metabox p { margin-bottom: 15px; }
                .g9x-hotel-booking-metabox label { display: block; margin-bottom: 5px; font-weight: bold; }
                .g9x-hotel-booking-metabox input[type="text"],
                .g9x-hotel-booking-metabox input[type="number"],
                .g9x-hotel-booking-metabox select,
                .g9x-hotel-booking-metabox textarea { width: 100%; max-width: 400px; padding: 8px; }
                .g9x-hotel-booking-metabox small { display: block; color: #666; font-style: italic; margin-top: 3px;}
                .g9x_room_gallery_preview { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 10px; }
                .g9x_room_gallery_image { position: relative; border: 1px solid #ddd; padding: 5px; }
                .g9x_room_gallery_image img { display: block; max-width: 100px; height: auto; }
                .g9x_remove_image { position: absolute; top: -5px; right: -5px; background: red; color: white; border-radius: 50%; width: 20px; height: 20px; text-align: center; line-height: 18px; cursor: pointer; font-weight: bold; }
            </style>
            <h3><?php esc_html_e( 'Basic Information', 'g9x-hotel-booking' ); ?></h3>
            <p>
                <label for="g9x_capacity_adults"><?php esc_html_e( 'Capacity (Adults):', 'g9x-hotel-booking' ); ?></label>
                <input type="number" id="g9x_capacity_adults" name="g9x_capacity_adults" value="<?php echo esc_attr( $capacity_adults ); ?>" min="1" />
            </p>
            <p>
                <label for="g9x_capacity_children"><?php esc_html_e( 'Capacity (Children):', 'g9x-hotel-booking' ); ?></label>
                <input type="number" id="g9x_capacity_children" name="g9x_capacity_children" value="<?php echo esc_attr( $capacity_children ); ?>" min="0" />
            </p>
            <p>
                <label for="g9x_max_occupancy"><?php esc_html_e( 'Maximum Occupancy:', 'g9x-hotel-booking' ); ?></label>
                <input type="number" id="g9x_max_occupancy" name="g9x_max_occupancy" value="<?php echo esc_attr( $max_occupancy ); ?>" min="1" />
            </p>
            <p>
                <label for="g9x_room_size"><?php esc_html_e( 'Room Size (e.g., 25 sq m):', 'g9x-hotel-booking' ); ?></label>
                <input type="text" id="g9x_room_size" name="g9x_room_size" value="<?php echo esc_attr( $room_size ); ?>" />
            </p>
            <p>
                <label for="g9x_bed_type"><?php esc_html_e( 'Bed Type:', 'g9x-hotel-booking' ); ?></label>
                <select id="g9x_bed_type" name="g9x_bed_type">
                    <option value="single" <?php selected( $bed_type, 'single' ); ?>><?php esc_html_e( 'Single', 'g9x-hotel-booking' ); ?></option>
                    <option value="twin" <?php selected( $bed_type, 'twin' ); ?>><?php esc_html_e( 'Twin', 'g9x-hotel-booking' ); ?></option>
                    <option value="double" <?php selected( $bed_type, 'double' ); ?>><?php esc_html_e( 'Double', 'g9x-hotel-booking' ); ?></option>
                    <option value="queen" <?php selected( $bed_type, 'queen' ); ?>><?php esc_html_e( 'Queen', 'g9x-hotel-booking' ); ?></option>
                    <option value="king" <?php selected( $bed_type, 'king' ); ?>><?php esc_html_e( 'King', 'g9x-hotel-booking' ); ?></option>
                    <option value="other" <?php selected( $bed_type, 'other' ); ?>><?php esc_html_e( 'Other', 'g9x-hotel-booking' ); ?></option>
                </select>
            </p>
            <p>
                <label for="g9x_address"><?php esc_html_e( 'Address:', 'g9x-hotel-booking' ); ?></label>
                <input type="text" id="g9x_address" name="g9x_address" value="<?php echo esc_attr( $address ); ?>" />
                <small><?php esc_html_e( 'Enter the specific street address of the room/property.', 'g9x-hotel-booking' ); ?></small>
            </p>

            <h3><?php esc_html_e( 'Pricing', 'g9x-hotel-booking' ); ?></h3>
            <p>
                <label for="g9x_price_per_night"><?php esc_html_e( 'Price per Night:', 'g9x-hotel-booking' ); ?></label>
                <input type="number" step="0.01" min="0" id="g9x_price_per_night" name="g9x_price_per_night" value="<?php echo esc_attr( $price_per_night ); ?>" />
                <small><?php esc_html_e( 'Enter the price. Currency symbol should be handled separately (e.g., via plugin settings or a filter).', 'g9x-hotel-booking' ); ?></small>
            </p>

            <h3><?php esc_html_e( 'Availability', 'g9x-hotel-booking' ); ?></h3>
            <p>
                <label for="g9x_availability_status"><?php esc_html_e( 'Availability Status:', 'g9x-hotel-booking' ); ?></label>
                <select id="g9x_availability_status" name="g9x_availability_status">
                    <option value="available" <?php selected( $availability_status, 'available' ); ?>><?php esc_html_e( 'Available', 'g9x-hotel-booking' ); ?></option>
                    <option value="booked" <?php selected( $availability_status, 'booked' ); ?>><?php esc_html_e( 'Fully Booked', 'g9x-hotel-booking' ); ?></option>
                    <option value="maintenance" <?php selected( $availability_status, 'maintenance' ); ?>><?php esc_html_e( 'Under Maintenance', 'g9x-hotel-booking' ); ?></option>
                </select>
                 <small><?php esc_html_e( 'Overall status. Specific date availability below.', 'g9x-hotel-booking' ); ?></small>
            </p>
            <p>
                <label for="g9x_unavailable_dates"><?php esc_html_e( 'Unavailable Dates (YYYY-MM-DD):', 'g9x-hotel-booking' ); ?></label>
                <textarea id="g9x_unavailable_dates" name="g9x_unavailable_dates" rows="4"><?php echo esc_textarea( $unavailable_dates ); ?></textarea>
                <small><?php esc_html_e( 'Enter dates when this room is unavailable, one per line or comma-separated. Example: 2025-12-25. NOTE: This is a basic implementation. A full calendar-based availability system is recommended for a professional plugin.', 'g9x-hotel-booking' ); ?></small>
            </p>

            <h3><?php esc_html_e( 'Room Gallery', 'g9x-hotel-booking' ); ?></h3>
            <p>
                <input type="hidden" id="g9x_room_gallery" name="g9x_room_gallery" value="<?php echo esc_attr( $room_gallery ); ?>" />
                <button type="button" class="button" id="g9x_room_gallery_button"><?php esc_html_e( 'Manage Gallery Images', 'g9x-hotel-booking' ); ?></button>
                <div class="g9x_room_gallery_preview">
                    <?php
                    $gallery_ids = ! empty( $room_gallery ) ? explode( ',', $room_gallery ) : array();
                    foreach ( $gallery_ids as $image_id ) {
                        $image_id = intval( $image_id );
                        if ( $image_id > 0 ) {
                            $image_url = wp_get_attachment_thumb_url( $image_id );
                            if ( $image_url ) {
                                echo '<div class="g9x_room_gallery_image" data-image-id="' . esc_attr( $image_id ) . '">';
                                echo '<img src="' . esc_url( $image_url ) . '" alt="' . esc_attr__( 'Room Gallery Image', 'g9x-hotel-booking' ) . '" />';
                                echo '<span class="g9x_remove_image" title="' . esc_attr__( 'Remove image', 'g9x-hotel-booking' ) . '">×</span>';
                                echo '</div>';
                            }
                        }
                    }
                    ?>
                </div>
                 <small><?php esc_html_e( 'Click button to add/remove images using the WordPress Media Library.', 'g9x-hotel-booking' ); ?></small>
            </p>
        </div>
        <?php
    }

    /**
	 * Render the Booking Details metabox.
	 *
	 * @param WP_Post $post The post object.
	 */
	public static function render_booking_details_metabox( $post ) {
        // Add nonce for security
        wp_nonce_field( 'g9x_save_booking_details', 'g9x_booking_details_nonce' );

        // Get existing values
        $booking_id_display = get_post_meta( $post->ID, 'g9x_booking_id', true ); // Use a different name for display meta key if needed
        $customer_user_id = get_post_meta( $post->ID, 'g9x_customer_user_id', true ); // Changed from g9x_customer_id
        $room_id = get_post_meta( $post->ID, 'g9x_room_id', true );
        $check_in_date = get_post_meta( $post->ID, 'g9x_check_in_date', true );
        $check_out_date = get_post_meta( $post->ID, 'g9x_check_out_date', true );
        $number_of_guests_adults = get_post_meta( $post->ID, 'g9x_number_of_guests_adults', true );
        $number_of_guests_children = get_post_meta( $post->ID, 'g9x_number_of_guests_children', true );
        $total_price = get_post_meta( $post->ID, 'g9x_total_price', true );
        $payment_status = get_post_meta( $post->ID, 'g9x_payment_status', true );
        $booking_status = get_post_meta( $post->ID, 'g9x_booking_status', true );
        $special_requests = get_post_meta( $post->ID, 'g9x_special_requests', true );
        $coupon_code = get_post_meta( $post->ID, 'g9x_coupon_code', true );
        $services_packages = get_post_meta( $post->ID, 'g9x_services_packages', true ); // Consider making this a repeatable field or taxonomy

        // Get Rooms for dropdown
        $rooms = get_posts(array(
            'post_type' => 'g9x_room',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
            'post_status' => 'publish' // Only show published rooms
        ));

        // Get Customer Info
        $customer_info = '';
        if ( $customer_user_id && $user = get_userdata( $customer_user_id ) ) {
             $customer_info = sprintf(
                '<a href="%s" target="_blank">%s (%s)</a>',
                esc_url( get_edit_user_link( $customer_user_id ) ),
                esc_html( $user->display_name ),
                esc_html( $user->user_email )
            );
        } elseif ( $customer_user_id ) {
            $customer_info = __( 'User not found', 'g9x-hotel-booking' ) . ' (ID: ' . esc_html( $customer_user_id ) . ')';
        } else {
             $customer_info = __( 'No customer assigned', 'g9x-hotel-booking' );
        }

        ?>
        <div class="g9x-hotel-booking-metabox">
             <style> /* Scoped styles for this metabox */
                .g9x-hotel-booking-metabox p { margin-bottom: 15px; }
                .g9x-hotel-booking-metabox label { display: block; margin-bottom: 5px; font-weight: bold; }
                .g9x-hotel-booking-metabox input[type="text"],
                .g9x-hotel-booking-metabox input[type="number"],
                .g9x-hotel-booking-metabox input[type="date"],
                .g9x-hotel-booking-metabox select,
                .g9x-hotel-booking-metabox textarea { width: 100%; max-width: 400px; padding: 8px; }
                 .g9x-hotel-booking-metabox .readonly-field { background-color: #f0f0f0; cursor: not-allowed; }
                 .g9x-hotel-booking-metabox small { display: block; color: #666; font-style: italic; margin-top: 3px;}
            </style>
            <p>
                <label><?php esc_html_e( 'Booking ID:', 'g9x-hotel-booking' ); ?></label>
                <input type="text" value="<?php echo esc_attr( $booking_id_display ); ?>" readonly class="readonly-field" />
                 <small><?php esc_html_e( 'Unique identifier for this booking.', 'g9x-hotel-booking' ); ?></small>
            </p>
            <p>
                <label for="g9x_customer_user_id"><?php esc_html_e( 'Customer (WP User):', 'g9x-hotel-booking' ); ?></label>
                <div><?php echo wp_kses_post( $customer_info ); ?></div>
                <input type="number" id="g9x_customer_user_id" name="g9x_customer_user_id" value="<?php echo esc_attr( $customer_user_id ); ?>" placeholder="<?php esc_attr_e( 'Enter WP User ID', 'g9x-hotel-booking' ); ?>" />
                <small><?php esc_html_e( 'Assign a registered WordPress user to this booking. Future enhancement: User search field.', 'g9x-hotel-booking' ); ?></small>
            </p>
            <p>
                <label for="g9x_room_id"><?php esc_html_e( 'Room:', 'g9x-hotel-booking' ); ?></label>
                <select id="g9x_room_id" name="g9x_room_id">
                    <option value=""><?php esc_html_e( '-- Select Room --', 'g9x-hotel-booking' ); ?></option>
                    <?php foreach ( $rooms as $room_post ) : ?>
                        <option value="<?php echo esc_attr( $room_post->ID ); ?>" <?php selected( $room_id, $room_post->ID ); ?>>
                            <?php echo esc_html( $room_post->post_title ); ?> (ID: <?php echo esc_html( $room_post->ID ); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </p>
            <p>
                <label for="g9x_check_in_date"><?php esc_html_e( 'Check-in Date:', 'g9x-hotel-booking' ); ?></label>
                <input type="date" id="g9x_check_in_date" name="g9x_check_in_date" value="<?php echo esc_attr( $check_in_date ); ?>" />
            </p>
            <p>
                <label for="g9x_check_out_date"><?php esc_html_e( 'Check-out Date:', 'g9x-hotel-booking' ); ?></label>
                <input type="date" id="g9x_check_out_date" name="g9x_check_out_date" value="<?php echo esc_attr( $check_out_date ); ?>" />
            </p>
            <p>
                <label for="g9x_number_of_guests_adults"><?php esc_html_e( 'Number of Guests (Adults):', 'g9x-hotel-booking' ); ?></label>
                <input type="number" min="0" id="g9x_number_of_guests_adults" name="g9x_number_of_guests_adults" value="<?php echo esc_attr( $number_of_guests_adults ); ?>" />
            </p>
            <p>
                <label for="g9x_number_of_guests_children"><?php esc_html_e( 'Number of Guests (Children):', 'g9x-hotel-booking' ); ?></label>
                <input type="number" min="0" id="g9x_number_of_guests_children" name="g9x_number_of_guests_children" value="<?php echo esc_attr( $number_of_guests_children ); ?>" />
            </p>
            <p>
                <label for="g9x_total_price"><?php esc_html_e( 'Total Price:', 'g9x-hotel-booking' ); ?></label>
                <input type="number" step="0.01" min="0" id="g9x_total_price" name="g9x_total_price" value="<?php echo esc_attr( $total_price ); ?>" />
                 <small><?php esc_html_e( 'Calculated or manually entered total price.', 'g9x-hotel-booking' ); ?></small>
            </p>
            <p>
                <label for="g9x_payment_status"><?php esc_html_e( 'Payment Status:', 'g9x-hotel-booking' ); ?></label>
                <select id="g9x_payment_status" name="g9x_payment_status">
                    <option value="pending" <?php selected( $payment_status, 'pending' ); ?>><?php esc_html_e( 'Pending', 'g9x-hotel-booking' ); ?></option>
                    <option value="processing" <?php selected( $payment_status, 'processing' ); ?>><?php esc_html_e( 'Processing', 'g9x-hotel-booking' ); ?></option>
                    <option value="completed" <?php selected( $payment_status, 'completed' ); ?>><?php esc_html_e( 'Completed', 'g9x-hotel-booking' ); ?></option>
                    <option value="failed" <?php selected( $payment_status, 'failed' ); ?>><?php esc_html_e( 'Failed', 'g9x-hotel-booking' ); ?></option>
                    <option value="refunded" <?php selected( $payment_status, 'refunded' ); ?>><?php esc_html_e( 'Refunded', 'g9x-hotel-booking' ); ?></option>
                    <option value="cancelled" <?php selected( $payment_status, 'cancelled' ); ?>><?php esc_html_e( 'Cancelled', 'g9x-hotel-booking' ); ?></option>
                    <option value="on-hold" <?php selected( $payment_status, 'on-hold' ); ?>><?php esc_html_e( 'On Hold', 'g9x-hotel-booking' ); ?></option>
                </select>
            </p>
            <p>
                <label for="g9x_booking_status"><?php esc_html_e( 'Booking Status:', 'g9x-hotel-booking' ); ?></label>
                <select id="g9x_booking_status" name="g9x_booking_status">
                    <option value="pending" <?php selected( $booking_status, 'pending' ); ?>><?php esc_html_e( 'Pending Confirmation', 'g9x-hotel-booking' ); ?></option>
                    <option value="confirmed" <?php selected( $booking_status, 'confirmed' ); ?>><?php esc_html_e( 'Confirmed', 'g9x-hotel-booking' ); ?></option>
                    <option value="cancelled" <?php selected( $booking_status, 'cancelled' ); ?>><?php esc_html_e( 'Cancelled', 'g9x-hotel-booking' ); ?></option>
                    <option value="checked-in" <?php selected( $booking_status, 'checked-in' ); ?>><?php esc_html_e( 'Checked In', 'g9x-hotel-booking' ); ?></option>
                    <option value="checked-out" <?php selected( $booking_status, 'checked-out' ); ?>><?php esc_html_e( 'Checked Out', 'g9x-hotel-booking' ); ?></option>
                    <option value="no-show" <?php selected( $booking_status, 'no-show' ); ?>><?php esc_html_e( 'No Show', 'g9x-hotel-booking' ); ?></option>
                </select>
            </p>
             <p>
                <label for="g9x_special_requests"><?php esc_html_e( 'Special Requests:', 'g9x-hotel-booking' ); ?></label>
                <textarea id="g9x_special_requests" name="g9x_special_requests" rows="3"><?php echo esc_textarea( $special_requests ); ?></textarea>
            </p>
            <p>
                <label for="g9x_coupon_code"><?php esc_html_e( 'Coupon Code Used:', 'g9x-hotel-booking' ); ?></label>
                <input type="text" id="g9x_coupon_code" name="g9x_coupon_code" value="<?php echo esc_attr( $coupon_code ); ?>" />
            </p>
            <p>
                <label for="g9x_services_packages"><?php esc_html_e( 'Additional Services/Packages:', 'g9x-hotel-booking' ); ?></label>
                <textarea id="g9x_services_packages" name="g9x_services_packages" rows="3"><?php echo esc_textarea( $services_packages ); ?></textarea>
                <small><?php esc_html_e( 'Record any add-ons associated with this booking.', 'g9x-hotel-booking' ); ?></small>
            </p>
        </div>
        <?php
    }

    /**
     * Render the Customer Details metabox (DEPRECATED).
     * Kept for reference, but should not be actively used or saved.
     *
     * @param WP_Post $post The post object.
     */
    public static function render_customer_details_metabox( $post ) {
        ?>
        <div class="g9x-hotel-booking-metabox notice notice-warning inline">
            <p><strong><?php esc_html_e( 'Deprecated:', 'g9x-hotel-booking' ); ?></strong> <?php esc_html_e( 'Customer data should be managed using standard WordPress Users.', 'g9x-hotel-booking' ); ?></p>
        </div>
        <?php
        /* // Original fields commented out
        wp_nonce_field( 'g9x_save_customer_details', 'g9x_customer_details_nonce' );
        $first_name = get_post_meta( $post->ID, 'g9x_first_name', true );
        $last_name = get_post_meta( $post->ID, 'g9x_last_name', true );
        $email_address = get_post_meta( $post->ID, 'g9x_email_address', true );
        $phone_number = get_post_meta( $post->ID, 'g9x_phone_number', true );
        $address = get_post_meta( $post->ID, 'g9x_address', true );
        // $password = get_post_meta( $post->ID, 'g9x_password', true ); // SECURITY RISK - REMOVED
        $registration_date = get_post_meta( $post->ID, 'g9x_registration_date', true );
        ?>
        <p>...</p> // Fields omitted
        <?php
        */
    }

    /**
     * Save handler for Room Details metabox.
     *
     * @param int $post_id The ID of the post being saved.
     */
    public static function save_room_details( $post_id ) {
        // Check security (Nonce, Autosave, Permissions)
        if ( ! self::can_save( $post_id, 'g9x_room_details_nonce', 'g9x_save_room_details' ) ) {
            return;
        }

        // Define expected fields and their sanitization types
        $fields = array(
            'g9x_capacity_adults'   => 'int',
            'g9x_capacity_children' => 'int',
            'g9x_max_occupancy'     => 'int',
            'g9x_room_size'         => 'text',
            'g9x_bed_type'          => 'key', // sanitize_key or specific validation
            'g9x_address'           => 'text',
            'g9x_price_per_night'   => 'float',
            'g9x_availability_status' => 'key',
            'g9x_unavailable_dates' => 'textarea', // Needs better sanitization/validation based on format
            'g9x_room_gallery'      => 'text', // Comma-separated IDs, needs validation
        );

        // Loop through fields, sanitize, and update meta
        foreach ( $fields as $key => $type ) {
            if ( isset( $_POST[ $key ] ) ) {
                $value = self::sanitize_field( $_POST[ $key ], $type );
                update_post_meta( $post_id, $key, $value );
            } else {
                 // Optionally delete meta if field is not submitted (e.g., for checkboxes)
                 // delete_post_meta( $post_id, $key );
            }
        }
    }

    /**
     * Save handler for Booking Details metabox.
     *
     * @param int $post_id The ID of the post being saved.
     */
    public static function save_booking_details( $post_id ) {
        // Check security
        if ( ! self::can_save( $post_id, 'g9x_booking_details_nonce', 'g9x_save_booking_details' ) ) {
            return;
        }

        // Define expected fields and their sanitization types
        $fields = array(
            // 'g9x_booking_id' is likely generated, not saved from form here
            'g9x_customer_user_id'      => 'int', // Changed from g9x_customer_id
            'g9x_room_id'               => 'int',
            'g9x_check_in_date'         => 'date', // Needs validation YYYY-MM-DD
            'g9x_check_out_date'        => 'date', // Needs validation YYYY-MM-DD
            'g9x_number_of_guests_adults' => 'int',
            'g9x_number_of_guests_children' => 'int',
            'g9x_total_price'           => 'float',
            'g9x_payment_status'        => 'key',
            'g9x_booking_status'        => 'key',
            'g9x_special_requests'      => 'textarea',
            'g9x_coupon_code'           => 'text',
            'g9x_services_packages'     => 'textarea',
        );

        // Loop through fields, sanitize, and update meta
        foreach ( $fields as $key => $type ) {
            if ( isset( $_POST[ $key ] ) ) {
                $value = self::sanitize_field( $_POST[ $key ], $type );
                update_post_meta( $post_id, $key, $value );
            }
        }

        // Generate/Update Booking ID if needed (example)
        $booking_id_display = get_post_meta( $post_id, 'g9x_booking_id', true );
        if ( empty( $booking_id_display ) ) {
            update_post_meta( $post_id, 'g9x_booking_id', 'BOOK-' . $post_id );
        }
    }

    /**
     * Helper function to check if meta box data can be saved.
     *
     * @param int    $post_id Post ID.
     * @param string $nonce_name Nonce name.
     * @param string $nonce_action Nonce action.
     * @return bool True if safe to save, false otherwise.
     */
    private static function can_save( $post_id, $nonce_name, $nonce_action ) {
        // Check if nonce is set and valid.
        if ( ! isset( $_POST[ $nonce_name ] ) || ! wp_verify_nonce( $_POST[ $nonce_name ], $nonce_action ) ) {
            return false;
        }

        // Check if this is an autosave.
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return false;
        }

        // Check the user's permissions.
        // Determine the post type from $post_id if needed, or assume 'edit_post' capability.
        $post_type = get_post_type( $post_id );
        $capability = 'edit_post';
        if ( $post_type_object = get_post_type_object( $post_type ) ) {
            $capability = $post_type_object->cap->edit_post;
        }
        if ( ! current_user_can( $capability, $post_id ) ) {
            return false;
        }

        return true;
    }

    /**
     * Helper function to sanitize field values based on type.
     *
     * @param mixed  $value The raw value.
     * @param string $type  The type of sanitization needed ('text', 'textarea', 'int', 'float', 'email', 'key', 'date', 'url').
     * @return mixed Sanitized value.
     */
    private static function sanitize_field( $value, $type ) {
        switch ( $type ) {
            case 'int':
                return intval( $value );
            case 'float':
                return floatval( $value );
            case 'email':
                return sanitize_email( $value );
            case 'key':
                return sanitize_key( $value );
            case 'textarea':
                return sanitize_textarea_field( $value );
            case 'url':
                return esc_url_raw( $value );
            case 'date':
                // Basic validation for YYYY-MM-DD format
                if ( preg_match( "/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $value ) ) {
                    return sanitize_text_field( $value );
                }
                return ''; // Return empty if format is invalid
            case 'text':
            default:
                return sanitize_text_field( $value );
        }
    }

    /**
	 * Enqueue admin scripts and styles.
     * Only load on relevant pages.
	 *
	 * @param string $hook_suffix The current admin page hook.
	 */
	public static function enqueue_scripts( $hook_suffix ) {
        // Only load on specific post type edit screens
        if ( in_array( $hook_suffix, array( 'post.php', 'post-new.php' ) ) ) {
            $screen = get_current_screen();
            if ( is_object( $screen ) && 'g9x_room' === $screen->post_type ) {
                // Enqueue WP Media Uploader scripts
                wp_enqueue_media();
                // Enqueue custom script for gallery management
                wp_enqueue_script(
                   'g9x-admin-room-metabox', // New handle
                   G9X_HOTEL_BOOKING_PLUGIN_URL . 'assets/js/admin-room-metabox.js', // New path
                   array( 'jquery', 'jquery-ui-sortable' ), // Add sortable dependency
                   G9X_HOTEL_BOOKING_VERSION, // Use defined version
                   true
                );
                // Enqueue admin styles
                wp_enqueue_style(
                    'g9x-hotel-booking-admin-styles',
                    G9X_HOTEL_BOOKING_PLUGIN_URL . 'assets/css/admin-style.css', // Use admin-specific CSS
                    array(),
                    G9X_HOTEL_BOOKING_VERSION // Use defined version
                );
            }
        }
	}
}

G9X_Metaboxes::init();