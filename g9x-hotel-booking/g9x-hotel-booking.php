<?php
/**
 * Plugin Name:       G9X Hotel Booking Pro
 * Plugin URI:        https://example.com/g9x-hotel-booking-pro
 * Description:       A professional and feature-rich hotel booking system for WordPress.
 * Version:           1.1.0
 * Author:            G9X Enhanced
 * Author URI:        https://example.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       g9x-hotel-booking
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Define Constants
 */
define( 'G9X_HOTEL_BOOKING_VERSION', '1.1.0' );
define( 'G9X_HOTEL_BOOKING_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'G9X_HOTEL_BOOKING_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'G9X_HOTEL_BOOKING_PLUGIN_FILE', __FILE__ ); // Added for reference

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
final class G9X_Hotel_Booking {

    /**
     * The single instance of the class.
     * @var G9X_Hotel_Booking
     * @since 1.1.0
     */
    private static $instance = null;

    /**
     * Plugin version.
     * @var string
     * @since 1.1.0
     */
    public $version = G9X_HOTEL_BOOKING_VERSION;

    /**
     * Main G9X_Hotel_Booking Instance.
     * Ensures only one instance of G9X_Hotel_Booking is loaded or can be loaded.
     *
     * @since 1.1.0
     * @static
     * @return G9X_Hotel_Booking - Main instance.
     */
    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
            self::$instance->setup_constants();
            self::$instance->includes();
            self::$instance->hooks();
        }
        return self::$instance;
    }

    /**
     * Cloning is forbidden.
     * @since 1.1.0
     */
    public function __clone() {
        _doing_it_wrong( __FUNCTION__, __( 'Cloning is forbidden.', 'g9x-hotel-booking' ), $this->version );
    }

    /**
     * Unserializing instances of this class is forbidden.
     * @since 1.1.0
     */
    public function __wakeup() {
        _doing_it_wrong( __FUNCTION__, __( 'Unserializing instances of this class is forbidden.', 'g9x-hotel-booking' ), $this->version );
    }

    /**
     * Constructor.
     * @since 1.1.0
     * @access private
     */
    private function __construct() {
        // Constructor logic can go here if needed in the future
    }

    /**
     * Setup plugin constants.
     * @since 1.1.0
     * @access private
     */
    private function setup_constants() {
        // Define constants if not already defined (redundant here but good practice)
        if ( ! defined( 'G9X_HOTEL_BOOKING_PLUGIN_PATH' ) ) {
            define( 'G9X_HOTEL_BOOKING_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
        }
        if ( ! defined( 'G9X_HOTEL_BOOKING_PLUGIN_URL' ) ) {
            define( 'G9X_HOTEL_BOOKING_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
        }
        if ( ! defined( 'G9X_HOTEL_BOOKING_VERSION' ) ) {
            define( 'G9X_HOTEL_BOOKING_VERSION', $this->version );
        }
    }

    /**
     * Include required core files used in admin and on the frontend.
     * @since 1.1.0
     * @access private
     */
    private function includes() {
        require_once G9X_HOTEL_BOOKING_PLUGIN_PATH . 'includes/post-types.php';
        require_once G9X_HOTEL_BOOKING_PLUGIN_PATH . 'includes/custom-fields.php';
        require_once G9X_HOTEL_BOOKING_PLUGIN_PATH . 'includes/database.php'; // Reviewed - Deprecated table creation
        require_once G9X_HOTEL_BOOKING_PLUGIN_PATH . 'includes/shortcodes.php';
        // require_once G9X_HOTEL_BOOKING_PLUGIN_PATH . 'includes/hooks.php'; // Reviewed - Deprecated/Moved
        // Add includes for new classes/files as we create them (e.g., Admin, Frontend, Settings)
    }

    /**
     * Hook into actions and filters.
     * @since 1.1.0
     * @access private
     */
    private function hooks() {
        // Activation, Deactivation, Uninstall Hooks
        register_activation_hook( G9X_HOTEL_BOOKING_PLUGIN_FILE, array( $this, 'activate' ) );
        register_deactivation_hook( G9X_HOTEL_BOOKING_PLUGIN_FILE, array( $this, 'deactivate' ) );
        register_uninstall_hook( G9X_HOTEL_BOOKING_PLUGIN_FILE, array( __CLASS__, 'uninstall' ) ); // Static method for uninstall

        // Load Text Domain
        add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );

        // Initialize components (Post Types, Shortcodes etc. might have their own init hooks)
        // The existing add_action('init', ...) calls in included files will still work,
        // but ideally, they would be managed within classes loaded by this main class.
    }

    /**
     * Load Localisation files.
     * Note: the actual .mo files must be placed in the /languages/ folder.
     * @since 1.1.0
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'g9x-hotel-booking',
            false,
            dirname( plugin_basename( G9X_HOTEL_BOOKING_PLUGIN_FILE ) ) . '/languages/'
        );
    }

    /**
     * Plugin activation callback.
     * @since 1.1.0
     */
    public function activate() {
        // Ensure the Post Types class is loaded (it should be by the includes method)
        if ( class_exists( 'G9X_Post_Types' ) ) {
            // Register CPTs and Taxonomies directly on activation
            G9X_Post_Types::register_post_types();
            G9X_Post_Types::register_taxonomies();
        }
        flush_rewrite_rules();

        // Set a transient flag for admin notices, etc.
        set_transient( 'g9x_hotel_booking_activated', true, 30 );
    }

    /**
     * Plugin deactivation callback.
     * @since 1.1.0
     */
    public function deactivate() {
        flush_rewrite_rules();
    }

    /**
     * Plugin uninstall callback.
     * Static method to ensure it runs even if the main class isn't instantiated.
     * @since 1.1.0
     */
    public static function uninstall() {
        global $wpdb;

        // Define table names
        $rooms_table_name = $wpdb->prefix . 'g9x_rooms';
        $bookings_table_name = $wpdb->prefix . 'g9x_bookings';
        $customers_table_name = $wpdb->prefix . 'g9x_customers';

        // Drop custom tables if they exist (for cleanup from older versions)
        $wpdb->query( "DROP TABLE IF EXISTS {$rooms_table_name}" );
        $wpdb->query( "DROP TABLE IF EXISTS {$bookings_table_name}" );
        $wpdb->query( "DROP TABLE IF EXISTS {$customers_table_name}" );

        // Optional: Remove plugin options
        // delete_option('g9x_hotel_booking_settings'); // Example if settings exist

        // Optional: Remove CPT posts (Use with caution - users might want to keep data)
        /*
        $cpts_to_remove = array('g9x_room', 'g9x_booking', 'g9x_customer');
        foreach ($cpts_to_remove as $cpt) {
            $items = get_posts(array('post_type' => $cpt, 'numberposts' => -1, 'post_status' => 'any'));
            foreach ($items as $item) {
                wp_delete_post($item->ID, true); // true = force delete, bypass trash
            }
        }
        */

        // Flush rewrite rules one last time
        flush_rewrite_rules();
    }
}

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * initiating the plugin class is the only step needed.
 *
 * @since    1.1.0
 */
function g9x_hotel_booking_run() {
    return G9X_Hotel_Booking::instance();
}
g9x_hotel_booking_run();