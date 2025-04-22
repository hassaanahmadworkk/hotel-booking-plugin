<?php
/**
 * Registers Custom Post Types and Taxonomies
 *
 * @package G9X_Hotel_Booking
 * @since   1.1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * G9X_Post_Types Class.
 */
class G9X_Post_Types {

	/**
	 * Hook in methods.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_post_types' ), 5 );
		add_action( 'init', array( __CLASS__, 'register_taxonomies' ), 5 );
	}

	/**
	 * Register core post types.
	 */
	public static function register_post_types() {
		if ( ! is_blog_installed() || post_type_exists( 'g9x_room' ) ) {
			return;
		}

		/**
		 * Post Type: Rooms.
		 */
		$room_labels = array(
			'name'                  => _x( 'Rooms', 'Post Type General Name', 'g9x-hotel-booking' ),
			'singular_name'         => _x( 'Room', 'Post Type Singular Name', 'g9x-hotel-booking' ),
			'menu_name'             => __( 'Hotel Rooms', 'g9x-hotel-booking' ),
			'name_admin_bar'        => __( 'Room', 'g9x-hotel-booking' ),
			'archives'              => __( 'Room Archives', 'g9x-hotel-booking' ),
			'attributes'            => __( 'Room Attributes', 'g9x-hotel-booking' ),
			'parent_item_colon'     => __( 'Parent Room:', 'g9x-hotel-booking' ),
			'all_items'             => __( 'All Rooms', 'g9x-hotel-booking' ),
			'add_new_item'          => __( 'Add New Room', 'g9x-hotel-booking' ),
			'add_new'               => __( 'Add New', 'g9x-hotel-booking' ),
			'new_item'              => __( 'New Room', 'g9x-hotel-booking' ),
			'edit_item'             => __( 'Edit Room', 'g9x-hotel-booking' ),
			'update_item'           => __( 'Update Room', 'g9x-hotel-booking' ),
			'view_item'             => __( 'View Room', 'g9x-hotel-booking' ),
			'view_items'            => __( 'View Rooms', 'g9x-hotel-booking' ),
			'search_items'          => __( 'Search Room', 'g9x-hotel-booking' ),
			'not_found'             => __( 'Not found', 'g9x-hotel-booking' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'g9x-hotel-booking' ),
			'featured_image'        => __( 'Featured Image', 'g9x-hotel-booking' ),
			'set_featured_image'    => __( 'Set featured image', 'g9x-hotel-booking' ),
			'remove_featured_image' => __( 'Remove featured image', 'g9x-hotel-booking' ),
			'use_featured_image'    => __( 'Use as featured image', 'g9x-hotel-booking' ),
			'insert_into_item'      => __( 'Insert into room', 'g9x-hotel-booking' ),
			'uploaded_to_this_item' => __( 'Uploaded to this room', 'g9x-hotel-booking' ),
			'items_list'            => __( 'Rooms list', 'g9x-hotel-booking' ),
			'items_list_navigation' => __( 'Rooms list navigation', 'g9x-hotel-booking' ),
			'filter_items_list'     => __( 'Filter rooms list', 'g9x-hotel-booking' ),
		);
		$room_args = array(
			'label'                 => __( 'Room', 'g9x-hotel-booking' ),
			'description'           => __( 'Hotel Rooms', 'g9x-hotel-booking' ),
			'labels'                => $room_labels,
			'supports'              => array( 'title', 'editor', 'thumbnail', 'custom-fields', 'revisions' ),
			'taxonomies'            => array( 'g9x_room_type', 'g9x_amenity', 'g9x_location' ), // Associate taxonomies
			'hierarchical'          => false,
			'public'                => true,
			'show_ui'               => true,
			'show_in_menu'          => true,
			'menu_position'         => 20, // Position below Pages
			'menu_icon'             => 'dashicons-admin-home',
			'show_in_admin_bar'     => true,
			'show_in_nav_menus'     => true,
			'can_export'            => true,
			'has_archive'           => 'rooms', // Enable archive page at /rooms/
			'exclude_from_search'   => false,
			'publicly_queryable'    => true,
			'rewrite'               => array( 'slug' => 'room', 'with_front' => false ), // URL structure /room/room-name/
			'capability_type'       => 'post', // Use standard post capabilities
            'show_in_rest'          => true, // Enable Gutenberg/REST API support
		);
		register_post_type( 'g9x_room', $room_args );

		/**
		 * Post Type: Bookings.
		 */
		$booking_labels = array(
			'name'                  => _x( 'Bookings', 'Post Type General Name', 'g9x-hotel-booking' ),
			'singular_name'         => _x( 'Booking', 'Post Type Singular Name', 'g9x-hotel-booking' ),
			'menu_name'             => __( 'Bookings', 'g9x-hotel-booking' ),
			'name_admin_bar'        => __( 'Booking', 'g9x-hotel-booking' ),
			'archives'              => __( 'Booking Archives', 'g9x-hotel-booking' ),
			'attributes'            => __( 'Booking Attributes', 'g9x-hotel-booking' ),
			'parent_item_colon'     => __( 'Parent Booking:', 'g9x-hotel-booking' ),
			'all_items'             => __( 'All Bookings', 'g9x-hotel-booking' ),
			'add_new_item'          => __( 'Add New Booking', 'g9x-hotel-booking' ),
			'add_new'               => __( 'Add New', 'g9x-hotel-booking' ),
			'new_item'              => __( 'New Booking', 'g9x-hotel-booking' ),
			'edit_item'             => __( 'Edit Booking', 'g9x-hotel-booking' ),
			'update_item'           => __( 'Update Booking', 'g9x-hotel-booking' ),
			'view_item'             => __( 'View Booking', 'g9x-hotel-booking' ),
			'view_items'            => __( 'View Bookings', 'g9x-hotel-booking' ),
			'search_items'          => __( 'Search Booking', 'g9x-hotel-booking' ),
			'not_found'             => __( 'Not found', 'g9x-hotel-booking' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'g9x-hotel-booking' ),
			'items_list'            => __( 'Bookings list', 'g9x-hotel-booking' ),
			'items_list_navigation' => __( 'Bookings list navigation', 'g9x-hotel-booking' ),
			'filter_items_list'     => __( 'Filter bookings list', 'g9x-hotel-booking' ),
		);
		$booking_args = array(
			'label'                 => __( 'Booking', 'g9x-hotel-booking' ),
			'description'           => __( 'Customer Bookings', 'g9x-hotel-booking' ),
			'labels'                => $booking_labels,
			'supports'              => array( 'title', 'custom-fields', 'comments' ), // Added comments support
			'hierarchical'          => false,
			'public'                => false, // Not publicly visible on front-end archive/single pages
			'show_ui'               => true, // Show in admin UI
			'show_in_menu'          => true, // Show under main Hotel Rooms menu or as separate item
			// 'show_in_menu'       => 'edit.php?post_type=g9x_room', // Example: Submenu of Rooms
			'menu_position'         => 21,
			'menu_icon'             => 'dashicons-calendar-alt',
			'show_in_admin_bar'     => false,
			'show_in_nav_menus'     => false,
			'can_export'            => true,
			'has_archive'           => false, // No public archive page
			'exclude_from_search'   => true, // Exclude from front-end searches
			'publicly_queryable'    => false, // Cannot be queried publicly
			'rewrite'               => false, // No rewrite rules needed
			'capability_type'       => 'post', // Consider custom capabilities later ('booking'?)
            'show_in_rest'          => true, // Allow management via REST API if needed
		);
		register_post_type( 'g9x_booking', $booking_args );

        /**
		 * Post Type: Customers (DEPRECATED - Use WP Users).
         * Keeping registration temporarily to avoid fatal errors if referenced,
         * but functionality should migrate to WP Users.
		 */
		$customer_labels = array(
			'name'                  => _x( 'Customers (Deprecated)', 'Post Type General Name', 'g9x-hotel-booking' ),
			'singular_name'         => _x( 'Customer (Deprecated)', 'Post Type Singular Name', 'g9x-hotel-booking' ),
            'menu_name'             => __( 'Customers (Deprecated)', 'g9x-hotel-booking' ),
		);
		$customer_args = array(
			'label'                 => __( 'Customer (Deprecated)', 'g9x-hotel-booking' ),
			'labels'                => $customer_labels,
			'supports'              => array( 'title' ), // Minimal support
			'hierarchical'          => false,
			'public'                => false,
			'show_ui'               => true, // Still show in admin for reference during transition
            'show_in_menu'          => true, // Keep visible for now
            'menu_icon'             => 'dashicons-admin-users',
			'publicly_queryable'    => false,
			'exclude_from_search'   => true,
			'has_archive'           => false,
            'rewrite'               => false,
            'show_in_rest'          => false, // Do not expose via REST
            'capability_type'       => 'post',
		);
		register_post_type( 'g9x_customer', $customer_args ); // Register with minimal args

	}

	/**
	 * Register core taxonomies.
	 */
	public static function register_taxonomies() {
		if ( ! is_blog_installed() ) {
			return;
		}

		/**
		 * Taxonomy: Room Types.
		 */
		$type_labels = array(
			'name'                       => _x( 'Room Types', 'Taxonomy General Name', 'g9x-hotel-booking' ),
			'singular_name'              => _x( 'Room Type', 'Taxonomy Singular Name', 'g9x-hotel-booking' ),
			'menu_name'                  => __( 'Room Types', 'g9x-hotel-booking' ),
			'all_items'                  => __( 'All Room Types', 'g9x-hotel-booking' ),
			'parent_item'                => __( 'Parent Room Type', 'g9x-hotel-booking' ),
			'parent_item_colon'          => __( 'Parent Room Type:', 'g9x-hotel-booking' ),
			'new_item_name'              => __( 'New Room Type Name', 'g9x-hotel-booking' ),
			'add_new_item'               => __( 'Add New Room Type', 'g9x-hotel-booking' ),
			'edit_item'                  => __( 'Edit Room Type', 'g9x-hotel-booking' ),
			'update_item'                => __( 'Update Room Type', 'g9x-hotel-booking' ),
			'view_item'                  => __( 'View Room Type', 'g9x-hotel-booking' ),
			'separate_items_with_commas' => __( 'Separate types with commas', 'g9x-hotel-booking' ),
			'add_or_remove_items'        => __( 'Add or remove types', 'g9x-hotel-booking' ),
			'choose_from_most_used'      => __( 'Choose from the most used', 'g9x-hotel-booking' ),
			'popular_items'              => __( 'Popular Room Types', 'g9x-hotel-booking' ),
			'search_items'               => __( 'Search Room Types', 'g9x-hotel-booking' ),
			'not_found'                  => __( 'Not Found', 'g9x-hotel-booking' ),
			'no_terms'                   => __( 'No types', 'g9x-hotel-booking' ),
			'items_list'                 => __( 'Room Types list', 'g9x-hotel-booking' ),
			'items_list_navigation'      => __( 'Room Types list navigation', 'g9x-hotel-booking' ),
		);
		$type_args = array(
			'labels'                     => $type_labels,
			'hierarchical'               => true, // Category style
			'public'                     => true,
			'show_ui'                    => true,
			'show_admin_column'          => true,
			'show_in_nav_menus'          => true,
			'show_tagcloud'              => false, // Usually false for hierarchical
            'rewrite'                    => array( 'slug' => 'room-type', 'with_front' => false ),
            'show_in_rest'               => true, // Enable Gutenberg/REST API support
		);
		register_taxonomy( 'g9x_room_type', array( 'g9x_room' ), $type_args );

		/**
		 * Taxonomy: Amenities.
		 */
		$amenity_labels = array(
			'name'                       => _x( 'Amenities', 'Taxonomy General Name', 'g9x-hotel-booking' ),
			'singular_name'              => _x( 'Amenity', 'Taxonomy Singular Name', 'g9x-hotel-booking' ),
			'menu_name'                  => __( 'Amenities', 'g9x-hotel-booking' ),
			'all_items'                  => __( 'All Amenities', 'g9x-hotel-booking' ),
			'parent_item'                => null, // Non-hierarchical
			'parent_item_colon'          => null, // Non-hierarchical
			'new_item_name'              => __( 'New Amenity Name', 'g9x-hotel-booking' ),
			'add_new_item'               => __( 'Add New Amenity', 'g9x-hotel-booking' ),
			'edit_item'                  => __( 'Edit Amenity', 'g9x-hotel-booking' ),
			'update_item'                => __( 'Update Amenity', 'g9x-hotel-booking' ),
			'view_item'                  => __( 'View Amenity', 'g9x-hotel-booking' ),
			'separate_items_with_commas' => __( 'Separate amenities with commas', 'g9x-hotel-booking' ),
			'add_or_remove_items'        => __( 'Add or remove amenities', 'g9x-hotel-booking' ),
			'choose_from_most_used'      => __( 'Choose from the most used', 'g9x-hotel-booking' ),
			'popular_items'              => __( 'Popular Amenities', 'g9x-hotel-booking' ),
			'search_items'               => __( 'Search Amenities', 'g9x-hotel-booking' ),
			'not_found'                  => __( 'Not Found', 'g9x-hotel-booking' ),
			'no_terms'                   => __( 'No amenities', 'g9x-hotel-booking' ),
			'items_list'                 => __( 'Amenities list', 'g9x-hotel-booking' ),
			'items_list_navigation'      => __( 'Amenities list navigation', 'g9x-hotel-booking' ),
		);
		$amenity_args = array(
			'labels'                     => $amenity_labels,
			'hierarchical'               => false, // Tag style
			'public'                     => true,
			'show_ui'                    => true,
			'show_admin_column'          => true,
			'show_in_nav_menus'          => true,
			'show_tagcloud'              => true, // Often true for non-hierarchical
            'rewrite'                    => array( 'slug' => 'amenity', 'with_front' => false ),
            'show_in_rest'               => true, // Enable Gutenberg/REST API support
		);
		register_taxonomy( 'g9x_room_amenity', array( 'g9x_room' ), $amenity_args );

        /**
		 * Taxonomy: Locations.
		 */
		$location_labels = array(
			'name'                       => _x( 'Locations', 'Taxonomy General Name', 'g9x-hotel-booking' ),
			'singular_name'              => _x( 'Location', 'Taxonomy Singular Name', 'g9x-hotel-booking' ),
			'menu_name'                  => __( 'Locations', 'g9x-hotel-booking' ),
			'all_items'                  => __( 'All Locations', 'g9x-hotel-booking' ),
			'parent_item'                => __( 'Parent Location', 'g9x-hotel-booking' ),
			'parent_item_colon'          => __( 'Parent Location:', 'g9x-hotel-booking' ),
			'new_item_name'              => __( 'New Location Name', 'g9x-hotel-booking' ),
			'add_new_item'               => __( 'Add New Location', 'g9x-hotel-booking' ),
			'edit_item'                  => __( 'Edit Location', 'g9x-hotel-booking' ),
			'update_item'                => __( 'Update Location', 'g9x-hotel-booking' ),
			'view_item'                  => __( 'View Location', 'g9x-hotel-booking' ),
			'separate_items_with_commas' => __( 'Separate locations with commas', 'g9x-hotel-booking' ),
			'add_or_remove_items'        => __( 'Add or remove locations', 'g9x-hotel-booking' ),
			'choose_from_most_used'      => __( 'Choose from the most used', 'g9x-hotel-booking' ),
			'popular_items'              => __( 'Popular Locations', 'g9x-hotel-booking' ),
			'search_items'               => __( 'Search Locations', 'g9x-hotel-booking' ),
			'not_found'                  => __( 'Not Found', 'g9x-hotel-booking' ),
			'no_terms'                   => __( 'No locations', 'g9x-hotel-booking' ),
			'items_list'                 => __( 'Locations list', 'g9x-hotel-booking' ),
			'items_list_navigation'      => __( 'Locations list navigation', 'g9x-hotel-booking' ),
		);
		$location_args = array(
			'labels'                     => $location_labels,
			'hierarchical'               => true, // Category style
			'public'                     => true,
			'show_ui'                    => true,
			'show_admin_column'          => true,
			'show_in_nav_menus'          => true,
			'show_tagcloud'              => false,
            'rewrite'                    => array( 'slug' => 'room-location', 'with_front' => false ),
            'show_in_rest'               => true, // Enable Gutenberg/REST API support
		);
		register_taxonomy( 'g9x_location', array( 'g9x_room' ), $location_args );

	}
}

G9X_Post_Types::init();