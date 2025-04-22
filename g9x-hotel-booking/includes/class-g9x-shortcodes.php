<?php
/**
 * Handles Frontend Shortcodes
 *
 * @package G9X_Hotel_Booking
 * @since   1.1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * G9X_Shortcodes Class.
 */
class G9X_Shortcodes {

 /**
  * Hook in methods.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_shortcodes' ) );
        // add_action( 'init', array( __CLASS__, 'handle_booking_submission' ) ); // REMOVED - Replaced by AJAX hooks
        add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ) );

        // Add AJAX action hooks
        add_action( 'wp_ajax_g9x_process_booking', array( __CLASS__, 'ajax_process_booking' ) );
        add_action( 'wp_ajax_nopriv_g9x_process_booking', array( __CLASS__, 'ajax_process_booking' ) );
	}

    /**
     * Register shortcodes.
     */
    public static function register_shortcodes() {
        add_shortcode( 'g9x_search_rooms', array( __CLASS__, 'render_search_rooms_shortcode' ) );
        // Add other shortcodes here if needed
    }

	/**
	 * Search and Display Rooms Shortcode Callback
     *
     * @param array $atts Shortcode attributes.
     * @return string Shortcode output.
  */
 public static function render_search_rooms_shortcode( $atts ) {
        // Enqueue scripts and styles needed for this shortcode
        wp_enqueue_style('jquery-ui-css');
        wp_enqueue_style('select2-css');
        wp_enqueue_style('g9x-hotel-booking-style'); // Registered in enqueue_scripts
        wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_script('select2-js');
        wp_enqueue_script('g9x-hotel-booking-script'); // Registered in enqueue_scripts

        // Normalize attributes if needed (none defined currently)
        // $atts = shortcode_atts( array( 'example' => 'default' ), $atts, 'g9x_search_rooms' );

        ob_start();

        $search_performed = false;
        $search_results = array();
        $search_params = array(); // Store sanitized search params

        // Process search form submission (GET request)
        if ( isset( $_GET['g9x_search_submitted'] ) ) {
            $search_performed = true;

            // Sanitize and store search parameters
            $search_params['location'] = isset( $_GET['g9x_search_location'] ) ? sanitize_text_field( $_GET['g9x_search_location'] ) : '';
            $search_params['check_in'] = isset( $_GET['g9x_search_check_in'] ) ? sanitize_text_field( $_GET['g9x_search_check_in'] ) : '';
            $search_params['check_out'] = isset( $_GET['g9x_search_check_out'] ) ? sanitize_text_field( $_GET['g9x_search_check_out'] ) : '';
            $search_params['adults'] = isset( $_GET['g9x_search_adults'] ) ? max( 1, intval( $_GET['g9x_search_adults'] ) ) : 1; // Ensure at least 1 adult
            $search_params['children'] = isset( $_GET['g9x_search_children'] ) ? max( 0, intval( $_GET['g9x_search_children'] ) ) : 0;
            $search_params['total_guests'] = $search_params['adults'] + $search_params['children'];

            // --- Build the Room Query ---
            $args = array(
                'post_type'      => 'g9x_room',
                'posts_per_page' => -1, // TODO: Add pagination setting
                'post_status'    => 'publish',
                'meta_query'     => array(
                    'relation' => 'AND',
                    // Base availability status
                    array(
                        'key'     => 'g9x_availability_status',
                        'value'   => 'available',
                        'compare' => '=',
                    ),
                    // Capacity checks
                    array(
                        'key'     => 'g9x_capacity_adults',
                        'value'   => $search_params['adults'],
                        'compare' => '>=',
                        'type'    => 'NUMERIC',
                    ),
                    array(
                        'key'     => 'g9x_capacity_children',
                        'value'   => $search_params['children'],
                        'compare' => '>=',
                        'type'    => 'NUMERIC',
                    ),
                    array(
                        'key'     => 'g9x_max_occupancy',
                        'value'   => $search_params['total_guests'],
                        'compare' => '>=',
                        'type'    => 'NUMERIC',
                    ),
                ),
                'tax_query' => array(),
            );

            // Add location taxonomy filter
            if ( ! empty( $search_params['location'] ) ) {
                $args['tax_query'][] = array(
                    'taxonomy' => 'g9x_location',
                    'field'    => 'slug',
                    'terms'    => $search_params['location'],
                );
            }

            // Get potential rooms based on capacity, location, base status
            $potential_rooms = get_posts( $args );

            // --- Filter by Date Availability (Inefficient - Needs Overhaul) ---
            // TODO: Replace this loop with a proper availability query (custom table or optimized meta)
            if ( ! empty( $search_params['check_in'] ) && ! empty( $search_params['check_out'] ) ) {
                $check_in_dt = date_create( $search_params['check_in'] );
                $check_out_dt = date_create( $search_params['check_out'] );

                if ( $check_in_dt && $check_out_dt && $check_out_dt > $check_in_dt ) {
                    foreach ( $potential_rooms as $key => $room ) {
                        $unavailable_dates_str = get_post_meta( $room->ID, 'g9x_unavailable_dates', true );
                        // Normalize separators (comma or newline)
                        $unavailable_dates = preg_split( '/[\s,]+/', $unavailable_dates_str, -1, PREG_SPLIT_NO_EMPTY );
                        $unavailable_array = array_map( 'trim', $unavailable_dates );

                        $current_date = clone $check_in_dt;
                        $date_conflict = false;

                        while ( $current_date < $check_out_dt ) {
                            $current_date_string = $current_date->format( 'Y-m-d' );
                            if ( in_array( $current_date_string, $unavailable_array ) ) {
                                $date_conflict = true;
                                break;
                            }
                            // TODO: Check against a proper availability system here
                            $current_date->modify( '+1 day' );
                        }

                        if ( $date_conflict ) {
                            unset( $potential_rooms[ $key ] ); // Remove room if unavailable
                        }
                    }
                    $search_results = array_values( $potential_rooms ); // Re-index array
                } else {
                    // Invalid date range, maybe show an error?
                    $search_results = array(); // Or return all potential rooms?
                     echo '<div class="g9x-notice g9x-error">Invalid check-in/check-out date range.</div>';
                }
            } else {
                 // If no dates provided, return all rooms matching other criteria
                 $search_results = $potential_rooms;
            }
        } // End if search submitted

        // --- Display Search Form ---
        self::render_search_form( $search_params );

        // --- Display Search Results ---
        if ( $search_performed ) {
            self::render_search_results( $search_results, $search_params );
        }

        // --- Display Booking Form Popup (Hidden) ---
        self::render_booking_popup();

        return ob_get_clean();
	}

    /**
     * Renders the search form HTML.
     *
     * @param array $search_params Current search parameters.
     */
    private static function render_search_form( $search_params ) {
        $location_terms = get_terms( array(
            'taxonomy'   => 'g9x_location',
            'hide_empty' => false,
        ) );
        ?>
        <div class="g9x-hotel-booking-form g9x-search-form">
            <h2><?php esc_html_e( 'Find Your Perfect Room', 'g9x-hotel-booking' ); ?></h2>
            <form action="" method="get"> <?php // Changed action to empty string "" ?>
                <input type="hidden" name="g9x_search_submitted" value="1">

                <div class="search-row">
                    <div class="search-field">
                        <label for="g9x_search_location"><?php esc_html_e( 'Location:', 'g9x-hotel-booking' ); ?></label>
                        <select id="g9x_search_location" name="g9x_search_location">
                            <option value=""><?php esc_html_e( 'Any Location', 'g9x-hotel-booking' ); ?></option>
                            <?php foreach ( $location_terms as $term ) : ?>
                                <option value="<?php echo esc_attr( $term->slug ); ?>" <?php selected( $search_params['location'] ?? '', $term->slug ); ?>>
                                    <?php echo esc_html( $term->name ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="search-field">
                        <label for="g9x_search_check_in"><?php esc_html_e( 'Check-in Date:', 'g9x-hotel-booking' ); ?></label>
                        <input type="date" id="g9x_search_check_in" name="g9x_search_check_in"
                               value="<?php echo esc_attr( $search_params['check_in'] ?? '' ); ?>" required>
                    </div>

                    <div class="search-field">
                        <label for="g9x_search_check_out"><?php esc_html_e( 'Check-out Date:', 'g9x-hotel-booking' ); ?></label>
                        <input type="date" id="g9x_search_check_out" name="g9x_search_check_out"
                               value="<?php echo esc_attr( $search_params['check_out'] ?? '' ); ?>" required>
                    </div>
                </div>

                <div class="search-row">
                    <div class="search-field">
                        <label for="g9x_search_adults"><?php esc_html_e( 'Adults:', 'g9x-hotel-booking' ); ?></label>
                        <input type="number" id="g9x_search_adults" name="g9x_search_adults" min="1"
                               value="<?php echo esc_attr( $search_params['adults'] ?? '1' ); ?>" required>
                    </div>

                    <div class="search-field">
                        <label for="g9x_search_children"><?php esc_html_e( 'Children:', 'g9x-hotel-booking' ); ?></label>
                        <input type="number" id="g9x_search_children" name="g9x_search_children" min="0"
                               value="<?php echo esc_attr( $search_params['children'] ?? '0' ); ?>">
                    </div>

                    <div class="search-field search-button">
                        <button type="submit"><?php esc_html_e( 'Search Rooms', 'g9x-hotel-booking' ); ?></button>
                    </div>
                </div>
            </form>
        </div>
        <?php
    }

    /**
     * Renders the search results HTML.
     *
     * @param array $search_results Array of WP_Post objects for rooms.
     * @param array $search_params  The search parameters used.
     */
    private static function render_search_results( $search_results, $search_params ) {
        if ( empty( $search_results ) ) {
            // Use CSS classes instead of inline styles
            echo '<div class="g9x-notice g9x-no-results">' . esc_html__( 'No rooms found matching your criteria. Please try different search parameters.', 'g9x-hotel-booking' ) . '</div>';
            return;
        }
        ?>
        <div class="g9x-search-results">
            <h2><?php esc_html_e( 'Available Rooms', 'g9x-hotel-booking' ); ?></h2>

            <?php foreach ( $search_results as $room ) :
                // Extract room data (consider creating a helper function or Room object)
                $room_id = $room->ID;
                $price = get_post_meta( $room_id, 'g9x_price_per_night', true );
                $room_size = get_post_meta( $room_id, 'g9x_room_size', true );
                $bed_type = get_post_meta( $room_id, 'g9x_bed_type', true );
                $address = get_post_meta( $room_id, 'g9x_address', true );
                $featured_image = get_the_post_thumbnail_url( $room_id, 'medium' );
                $amenities = wp_get_post_terms( $room_id, 'g9x_room_amenity', array( 'fields' => 'names' ) );
                $room_types = wp_get_post_terms( $room_id, 'g9x_room_type', array( 'fields' => 'names' ) );
                $room_type = ! empty( $room_types ) ? $room_types[0] : '';
                $locations = wp_get_post_terms( $room_id, 'g9x_location', array( 'fields' => 'names' ) );
                $location_name = ! empty( $locations ) ? implode( ', ', $locations ) : __( 'N/A', 'g9x-hotel-booking' );

                // Calculate total price (ensure dates are valid)
                $total_price = 0;
                $nights = 0;
                if ( ! empty( $search_params['check_in'] ) && ! empty( $search_params['check_out'] ) ) {
                    $check_in_dt = date_create( $search_params['check_in'] );
                    $check_out_dt = date_create( $search_params['check_out'] );
                    if ( $check_in_dt && $check_out_dt && $check_out_dt > $check_in_dt ) {
                        $interval = $check_in_dt->diff( $check_out_dt );
                        $nights = $interval->days;
                        $total_price = floatval( $price ) * $nights;
                    }
                }
                ?>
                <div class="g9x-room-result">
                    <div class="g9x-room-image">
                        <?php if ( $featured_image ) : ?>
                            <img src="<?php echo esc_url( $featured_image ); ?>" alt="<?php echo esc_attr( $room->post_title ); ?>">
                        <?php else : ?>
                            <div class="g9x-no-image"><?php esc_html_e( 'No Image Available', 'g9x-hotel-booking' ); ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="g9x-room-details">
                        <h3><?php echo esc_html( $room->post_title ); ?></h3>

                        <?php if ( ! empty( $room_type ) ) : ?>
                        <div class="g9x-room-type">
                            <span><?php echo esc_html( $room_type ); ?></span>
                        </div>
                        <?php endif; ?>

                        <div class="g9x-room-specs"><span><strong><?php esc_html_e( 'Room Size:', 'g9x-hotel-booking' ); ?></strong> <?php echo esc_html( $room_size ); ?></span><span><strong><?php esc_html_e( 'Bed Type:', 'g9x-hotel-booking' ); ?></strong> <?php echo esc_html( ucfirst( $bed_type ) ); ?></span><span><strong><?php esc_html_e( 'Location:', 'g9x-hotel-booking' ); ?></strong> <?php echo esc_html( $location_name ); ?></span><?php if ( ! empty( $address ) ) : ?><span class="g9x-room-address"><strong><?php esc_html_e( 'Address:', 'g9x-hotel-booking' ); ?></strong> <?php echo esc_html( $address ); ?></span><?php endif; ?></div>

                        <?php if ( ! empty( $amenities ) ) : ?>
                        <div class="g9x-room-amenities">
                            <strong><?php esc_html_e( 'Amenities:', 'g9x-hotel-booking' ); ?></strong> <?php echo esc_html( implode( ', ', $amenities ) ); ?>
                        </div>
                        <?php endif; ?>

                        <div class="g9x-room-description">
                            <?php echo wp_kses_post( wp_trim_words( $room->post_content, 30 ) ); ?>
                        </div>
                    </div>

                    <div class="g9x-room-booking">
                        <div class="g9x-room-price">
                            <?php // TODO: Add currency formatting/settings ?>
                            <span class="price-amount">$<?php echo esc_html( number_format( (float) $price, 2 ) ); ?></span>
                            <span class="price-period"><?php esc_html_e( 'per night', 'g9x-hotel-booking' ); ?></span>
                        </div>

                        <?php if ( $nights > 0 ) : ?>
                        <div class="g9x-total-price">
                            <span><?php printf( esc_html__( 'Total for %d nights: $%s', 'g9x-hotel-booking' ), esc_html( $nights ), esc_html( number_format( $total_price, 2 ) ) ); ?></span>
                        </div>
                        <?php endif; ?>

                        <?php // Pass necessary data to JS for the popup ?>
                        <button type="button" class="g9x-book-now-button"
                                data-room-id="<?php echo esc_attr( $room_id ); ?>"
                                data-room-title="<?php echo esc_attr( $room->post_title ); ?>"
                                data-check-in="<?php echo esc_attr( $search_params['check_in'] ?? '' ); ?>"
                                data-check-out="<?php echo esc_attr( $search_params['check_out'] ?? '' ); ?>"
                                data-adults="<?php echo esc_attr( $search_params['adults'] ?? '' ); ?>"
                                data-children="<?php echo esc_attr( $search_params['children'] ?? '' ); ?>"
                                data-price="<?php echo esc_attr( $total_price ); ?>"
                                data-nights="<?php echo esc_attr( $nights ); ?>">
                            <?php esc_html_e( 'Book Now', 'g9x-hotel-booking' ); ?>
                        </button>
                    </div>
                </div>

            <?php endforeach; ?>
        </div>
        <?php
    }

    /**
     * Renders the booking popup HTML structure (initially hidden).
     */
    private static function render_booking_popup() {
        ?>
        <div id="g9x-booking-popup" class="g9x-popup" style="display: none;"> <?php // Hidden initially ?>
            <div class="g9x-popup-content">
                <span class="g9x-close-popup" title="<?php esc_attr_e( 'Close', 'g9x-hotel-booking' ); ?>">&times;</span>
                <h3><?php esc_html_e( 'Complete Your Booking', 'g9x-hotel-booking' ); ?></h3>

                <div id="g9x-booking-form-notice" class="g9x-notice" style="display: none;"></div> <?php // For AJAX messages ?>

                <form id="g9x-booking-form" method="post" action="<?php echo esc_url( admin_url('admin-ajax.php') ); // Point to AJAX handler ?>">
                    <?php // Security Nonce ?>
                    <?php wp_nonce_field( 'g9x_process_booking_nonce', 'g9x_booking_nonce' ); ?>
                    <?php // Action for AJAX handler ?>
                    <input type="hidden" name="action" value="g9x_process_booking">

                    <?php // Hidden fields populated by JS ?>
                    <input type="hidden" id="g9x_booking_room_id" name="g9x_booking_room_id">
                    <input type="hidden" id="g9x_booking_check_in" name="g9x_booking_check_in">
                    <input type="hidden" id="g9x_booking_check_out" name="g9x_booking_check_out">
                    <input type="hidden" id="g9x_number_of_guests_adults" name="g9x_number_of_guests_adults">
                    <input type="hidden" id="g9x_number_of_guests_children" name="g9x_number_of_guests_children">
                    <input type="hidden" id="g9x_total_price" name="g9x_total_price">

                    <div class="g9x-booking-summary">
                        <h4><?php esc_html_e( 'Booking Summary', 'g9x-hotel-booking' ); ?></h4>
                        <div id="g9x-booking-room-title"></div>
                        <div id="g9x-booking-dates"></div>
                        <div id="g9x-booking-guests"></div>
                        <div id="g9x-booking-price"></div>
                    </div>

                    <h4><?php esc_html_e( 'Your Details', 'g9x-hotel-booking' ); ?></h4>

                    <?php if ( ! is_user_logged_in() ) : ?>
                        <div class="g9x-form-field">
                            <label for="g9x_booking_name"><?php esc_html_e( 'Full Name:', 'g9x-hotel-booking' ); ?></label>
                            <input type="text" id="g9x_booking_name" name="g9x_booking_name" required>
                        </div>
                        <div class="g9x-form-field">
                            <label for="g9x_booking_email"><?php esc_html_e( 'Email:', 'g9x-hotel-booking' ); ?></label>
                            <input type="email" id="g9x_booking_email" name="g9x_booking_email" required>
                        </div>
                         <div class="g9x-form-field">
                            <label for="g9x_booking_phone"><?php esc_html_e( 'Phone Number:', 'g9x-hotel-booking' ); ?></label>
                            <input type="tel" id="g9x_booking_phone" name="g9x_booking_phone" required>
                        </div>
                        <?php // TODO: Add option for guest to register/login ?>
                    <?php else :
                        $current_user = wp_get_current_user();
                    ?>
                        <div class="g9x-form-field">
                            <p><?php printf( esc_html__( 'Booking as: %s (%s)', 'g9x-hotel-booking' ), esc_html( $current_user->display_name ), esc_html( $current_user->user_email ) ); ?></p>
                            <?php // Optionally allow overriding phone number? ?>
                             <label for="g9x_booking_phone"><?php esc_html_e( 'Confirm Phone Number:', 'g9x-hotel-booking' ); ?></label>
                             <input type="tel" id="g9x_booking_phone" name="g9x_booking_phone" value="<?php echo esc_attr( get_user_meta( $current_user->ID, 'billing_phone', true ) ); ?>" required>
                        </div>
                    <?php endif; ?>

                    <div class="g9x-form-field">
                        <label for="g9x_special_requests"><?php esc_html_e( 'Special Requests:', 'g9x-hotel-booking' ); ?></label>
                        <textarea id="g9x_special_requests" name="g9x_special_requests"></textarea>
                    </div>

                    <div class="g9x-form-field">
                        <label for="g9x_coupon_code"><?php esc_html_e( 'Coupon Code:', 'g9x-hotel-booking' ); ?></label>
                        <input type="text" id="g9x_coupon_code" name="g9x_coupon_code">
                    </div>

                    <div class="g9x-form-actions">
                        <button type="submit" class="g9x-confirm-booking"><?php esc_html_e( 'Confirm Booking', 'g9x-hotel-booking' ); ?></button>
                        <span class="g9x-spinner" style="display: none;"></span> <?php // AJAX Spinner ?>
                    </div>
                </form>
            </div>
        </div>
        <?php
    }

    /**
     * AJAX handler for processing the booking form submission.
     */
    public static function ajax_process_booking() {
        // 1. Verify Nonce
        check_ajax_referer( 'g9x_process_booking_nonce', 'g9x_booking_nonce' );

        // 2. Sanitize and retrieve form data
        $errors = array();
        $booking_data = array();

        // Sanitize and retrieve form data
        $booking_data['room_id'] = isset( $_POST['g9x_booking_room_id'] ) ? intval( $_POST['g9x_booking_room_id'] ) : 0;
        $booking_data['check_in'] = isset( $_POST['g9x_booking_check_in'] ) ? sanitize_text_field( $_POST['g9x_booking_check_in'] ) : '';
        $booking_data['check_out'] = isset( $_POST['g9x_booking_check_out'] ) ? sanitize_text_field( $_POST['g9x_booking_check_out'] ) : '';
        $booking_data['adults'] = isset( $_POST['g9x_number_of_guests_adults'] ) ? intval( $_POST['g9x_number_of_guests_adults'] ) : 0;
        $booking_data['children'] = isset( $_POST['g9x_number_of_guests_children'] ) ? intval( $_POST['g9x_number_of_guests_children'] ) : 0;
        $booking_data['total_price'] = isset( $_POST['g9x_total_price'] ) ? floatval( $_POST['g9x_total_price'] ) : 0.0;
        $booking_data['special_requests'] = isset( $_POST['g9x_special_requests'] ) ? sanitize_textarea_field( $_POST['g9x_special_requests'] ) : '';
        $booking_data['coupon_code'] = isset( $_POST['g9x_coupon_code'] ) ? sanitize_text_field( $_POST['g9x_coupon_code'] ) : '';
        $booking_data['phone'] = isset( $_POST['g9x_booking_phone'] ) ? sanitize_text_field( $_POST['g9x_booking_phone'] ) : '';

        // Guest/User details
        $user_id = 0;
        $guest_name = '';
        $guest_email = '';

        if ( is_user_logged_in() ) {
            $user_id = get_current_user_id();
            $user_data = get_userdata( $user_id );
            $guest_name = $user_data->display_name;
            $guest_email = $user_data->user_email;
            // Use provided phone, or fallback to user meta if needed
            if ( empty( $booking_data['phone'] ) ) {
                 $booking_data['phone'] = get_user_meta( $user_id, 'billing_phone', true );
            }
        } else {
            $guest_name = isset( $_POST['g9x_booking_name'] ) ? sanitize_text_field( $_POST['g9x_booking_name'] ) : '';
            $guest_email = isset( $_POST['g9x_booking_email'] ) ? sanitize_email( $_POST['g9x_booking_email'] ) : '';
            // TODO: Option to create WP user account for guest?
        }

        // --- Validation ---
        if ( empty( $booking_data['room_id'] ) ) $errors[] = __( 'Invalid room selected.', 'g9x-hotel-booking' );
        if ( empty( $booking_data['check_in'] ) ) $errors[] = __( 'Check-in date is required.', 'g9x-hotel-booking' );
        if ( empty( $booking_data['check_out'] ) ) $errors[] = __( 'Check-out date is required.', 'g9x-hotel-booking' );
        // Add date range validation (check-out > check-in)
        if ( ! empty( $booking_data['check_in'] ) && ! empty( $booking_data['check_out'] ) && strtotime( $booking_data['check_out'] ) <= strtotime( $booking_data['check_in'] ) ) {
             $errors[] = __( 'Check-out date must be after check-in date.', 'g9x-hotel-booking' );
        }
        if ( empty( $guest_name ) ) $errors[] = __( 'Full name is required.', 'g9x-hotel-booking' );
        if ( empty( $guest_email ) || ! is_email( $guest_email ) ) $errors[] = __( 'A valid email address is required.', 'g9x-hotel-booking' );
        if ( empty( $booking_data['phone'] ) ) $errors[] = __( 'Phone number is required.', 'g9x-hotel-booking' );
        // TODO: Add availability check here against the proper system before creating booking

        if ( ! empty( $errors ) ) {
            wp_send_json_error( array( 'message' => implode( '<br>', $errors ) ) );
        }

        // 3. Create Booking Post ---
        $booking_post_title = sprintf( 'Booking: Room %d for %s (%s - %s)',
            $booking_data['room_id'],
            $guest_name,
            $booking_data['check_in'],
            $booking_data['check_out']
        );

        $booking_post_data = array(
            'post_title'    => $booking_post_title,
            'post_content'  => $booking_data['special_requests'],
            'post_status'   => 'publish', // Or 'pending' for admin approval - make this a setting?
            'post_type'     => 'g9x_booking',
            'post_author'   => $user_id, // Assign to WP user if logged in, otherwise 0 or admin ID
        );

        $booking_post_id = wp_insert_post( $booking_post_data, true ); // Pass true to return WP_Error on failure

        if ( is_wp_error( $booking_post_id ) ) {
            // Handle booking creation error
            error_log( 'G9X Booking Error: Failed to insert booking post. ' . $booking_post_id->get_error_message() );
            wp_send_json_error( array( 'message' => __( 'Could not create booking. Please try again later.', 'g9x-hotel-booking' ) ) );
        }

        // 4. Add Booking Meta Data ---
        update_post_meta( $booking_post_id, 'g9x_booking_id', 'BOOK-' . $booking_post_id ); // Simple unique ID
        update_post_meta( $booking_post_id, 'g9x_room_id', $booking_data['room_id'] );
        update_post_meta( $booking_post_id, 'g9x_check_in_date', $booking_data['check_in'] );
        update_post_meta( $booking_post_id, 'g9x_check_out_date', $booking_data['check_out'] );
        update_post_meta( $booking_post_id, 'g9x_number_of_guests_adults', $booking_data['adults'] );
        update_post_meta( $booking_post_id, 'g9x_number_of_guests_children', $booking_data['children'] );
        update_post_meta( $booking_post_id, 'g9x_total_price', $booking_data['total_price'] );
        update_post_meta( $booking_post_id, 'g9x_payment_status', 'pending' ); // Default status - needs payment gateway integration
        update_post_meta( $booking_post_id, 'g9x_booking_status', 'confirmed' ); // Default status - or 'pending' if approval needed
        update_post_meta( $booking_post_id, 'g9x_special_requests', $booking_data['special_requests'] );
        update_post_meta( $booking_post_id, 'g9x_coupon_code', $booking_data['coupon_code'] );
        // update_post_meta( $booking_post_id, 'g9x_services_packages', '' ); // Add if applicable

        // Store guest details
        update_post_meta( $booking_post_id, 'g9x_guest_name', $guest_name );
        update_post_meta( $booking_post_id, 'g9x_guest_email', $guest_email );
        update_post_meta( $booking_post_id, 'g9x_guest_phone', $booking_data['phone'] );
        if ( $user_id > 0 ) {
            update_post_meta( $booking_post_id, 'g9x_customer_user_id', $user_id ); // Link to WP User ID
        }

        // --- Update Room Availability ---
        // TODO: Implement this crucial step. Needs to interact with the chosen availability system.
        // Example using the (inefficient) comma-separated meta field:
        /*
        $unavailable_str = get_post_meta( $booking_data['room_id'], 'g9x_unavailable_dates', true );
        $unavailable_arr = preg_split( '/[\s,]+/', $unavailable_str, -1, PREG_SPLIT_NO_EMPTY );
        $check_in_dt = date_create( $booking_data['check_in'] );
        $check_out_dt = date_create( $booking_data['check_out'] );
        $current_dt = clone $check_in_dt;
        while( $current_dt < $check_out_dt ) {
            $unavailable_arr[] = $current_dt->format('Y-m-d');
            $current_dt->modify('+1 day');
        }
        $new_unavailable_str = implode( ',', array_unique( $unavailable_arr ) );
        update_post_meta( $booking_data['room_id'], 'g9x_unavailable_dates', $new_unavailable_str );
        */

        // 6. Send Success Response ---
        // TODO: Add option for redirect URL from settings?
        $confirmation_page_url = get_permalink( get_page_by_path( 'booking-confirmation' ) ); // Optional confirmation page

        wp_send_json_success( array(
            'message' => __( 'Booking confirmed successfully!', 'g9x-hotel-booking' ),
            'booking_id' => $booking_post_id,
            'redirect_url' => $confirmation_page_url ?: null // Send redirect URL if confirmation page exists
        ) );

        // wp_send_json_* functions include wp_die()
    }

    /**
	 * Enqueue frontend scripts and styles conditionally.
	 */
	public static function enqueue_scripts() {
        // Only load if the shortcode will be rendered.
        // This check happens *before* shortcode rendering, so we use a flag set by the shortcode callback.
        // A more robust way is to check post content in 'wp_enqueue_scripts' or use wp_register_script/style
        // and wp_enqueue_script/style within the shortcode callback itself.
        // For now, we'll use the flag method which works if the check happens after the shortcode runs (unlikely).
        // Let's refine this: Register scripts/styles here, enqueue them in the shortcode render function.

        // Register scripts/styles (jquery-ui-datepicker is core, no need to register)
        wp_register_style( 'jquery-ui-css', '//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css', array(), '1.12.1' );
        wp_register_style( 'select2-css', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css', array(), '4.1.0-rc.0' );
        wp_register_script( 'select2-js', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', array( 'jquery' ), '4.1.0-rc.0', true );

        // Register plugin's main script and style
        wp_register_style(
            'g9x-hotel-booking-style', // Keep handle the same for consistency
            G9X_HOTEL_BOOKING_PLUGIN_URL . 'assets/css/frontend-style.css', // Use frontend-specific CSS
            array('jquery-ui-css', 'select2-css'), // Dependencies
            G9X_HOTEL_BOOKING_VERSION
        );
         wp_register_script(
            'g9x-hotel-booking-script',
            G9X_HOTEL_BOOKING_PLUGIN_URL . 'assets/js/g9x-hotel-booking.js',
            array( 'jquery', 'jquery-ui-datepicker', 'select2-js' ), // Dependencies
            G9X_HOTEL_BOOKING_VERSION,
            true // Load in footer
        );

        // Localize script for AJAX
        wp_localize_script( 'g9x-hotel-booking-script', 'g9x_booking_params', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'booking_nonce' => wp_create_nonce( 'g9x_process_booking_nonce' ), // Pass nonce to JS
            'processing_message' => esc_html__( 'Processing...', 'g9x-hotel-booking' ),
            'error_message' => esc_html__( 'An error occurred. Please try again.', 'g9x-hotel-booking' ),
            // Add other translatable strings needed in JS
        ) );


        // The check using has_shortcode is removed as enqueuing now happens in the render function.
 }
}

G9X_Shortcodes::init();