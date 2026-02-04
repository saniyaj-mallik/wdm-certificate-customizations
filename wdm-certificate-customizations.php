<?php
/**
 * Plugin Name: WDM Certificate Customizations
 * Plugin URI: https://wisdmlabs.com
 * Description: Adds dual certificate support (Standard + Pocket Size) with built-in QR code verification system for LearnDash. Requires LearnDash Certificate Builder.
 * Version: 1.0.0
 * Author: WisdmLabs
 * Author URI: https://wisdmlabs.com
 * Text Domain: wdm-certificate-customizations
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Plugin constants
define( 'WDM_CERT_VERSION', '1.0.0' );
define( 'WDM_CERT_PLUGIN_FILE', __FILE__ );
define( 'WDM_CERT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WDM_CERT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WDM_CERT_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Main plugin class
 */
final class WDM_Certificate_Customizations {

    /**
     * Single instance
     *
     * @var WDM_Certificate_Customizations
     */
    private static $instance = null;

    /**
     * Plugin components
     */
    public $admin;
    public $handler;
    public $verification;
    public $shortcodes;

    /**
     * Get single instance
     *
     * @return WDM_Certificate_Customizations
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->init_hooks();
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Check dependencies on plugins_loaded
        add_action( 'plugins_loaded', array( $this, 'check_dependencies' ), 5 );

        // Load plugin after dependencies are verified
        add_action( 'plugins_loaded', array( $this, 'init_plugin' ), 15 );

        // Load textdomain
        add_action( 'init', array( $this, 'load_textdomain' ) );

        // Activation hook
        register_activation_hook( __FILE__, array( $this, 'activate' ) );

        // Deactivation hook
        register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );
    }

    /**
     * Check if required plugins are active
     *
     * @return bool
     */
    public function check_dependencies() {
        $missing = array();

        // Check for LearnDash
        if ( ! class_exists( 'SFWD_LMS' ) ) {
            $missing[] = 'LearnDash LMS';
        }

        // Check for LearnDash Certificate Builder
        if ( ! defined( 'LEARNDASH_CERTIFICATE_BUILDER_VERSION' ) ) {
            $missing[] = 'LearnDash Certificate Builder';
        }

        if ( ! empty( $missing ) ) {
            add_action( 'admin_notices', function() use ( $missing ) {
                $message = sprintf(
                    /* translators: %s: list of missing plugins */
                    __( 'WDM Certificate Customizations requires the following plugins to be active: %s', 'wdm-certificate-customizations' ),
                    '<strong>' . implode( ', ', $missing ) . '</strong>'
                );
                echo '<div class="notice notice-error"><p>' . wp_kses_post( $message ) . '</p></div>';
            });
            return false;
        }

        return true;
    }

    /**
     * Initialize plugin components
     */
    public function init_plugin() {
        // Don't load if dependencies are missing
        if ( ! class_exists( 'SFWD_LMS' ) || ! defined( 'LEARNDASH_CERTIFICATE_BUILDER_VERSION' ) ) {
            return;
        }

        // Load classes
        $this->load_classes();

        // Initialize components
        $this->admin        = WDM_Cert_Admin::get_instance();
        $this->handler      = WDM_Cert_Handler::get_instance();
        $this->verification = WDM_Cert_Verification::get_instance();
        $this->shortcodes   = WDM_Cert_Shortcodes::get_instance();

        // Hook to allow pocket certificates access
        add_action( 'learndash_certificate_disallowed', array( $this, 'allow_pocket_certificate' ), 5 );

        // Enqueue assets
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
    }

    /**
     * Load required classes
     */
    private function load_classes() {
        require_once WDM_CERT_PLUGIN_DIR . 'includes/class-helper.php';
        require_once WDM_CERT_PLUGIN_DIR . 'includes/class-qr-code.php';
        require_once WDM_CERT_PLUGIN_DIR . 'includes/class-admin.php';
        require_once WDM_CERT_PLUGIN_DIR . 'includes/class-certificate-handler.php';
        require_once WDM_CERT_PLUGIN_DIR . 'includes/class-verification.php';
        require_once WDM_CERT_PLUGIN_DIR . 'includes/class-shortcodes.php';
        require_once WDM_CERT_PLUGIN_DIR . 'includes/class-upgrade.php';

        // Load test setup class (only in admin for development/testing)
        if ( is_admin() && file_exists( WDM_CERT_PLUGIN_DIR . 'includes/class-test-setup.php' ) ) {
            require_once WDM_CERT_PLUGIN_DIR . 'includes/class-test-setup.php';
        }
    }

    /**
     * Load plugin textdomain
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'wdm-certificate-customizations',
            false,
            dirname( WDM_CERT_PLUGIN_BASENAME ) . '/languages'
        );
    }

    /**
     * Enqueue frontend assets
     */
    public function enqueue_frontend_assets() {
        // Only load on verification page
        $options = get_option( 'wdm_certificate_options', array() );
        $verification_page_id = isset( $options['verification_page_id'] ) ? absint( $options['verification_page_id'] ) : 0;

        if ( is_page( $verification_page_id ) || has_shortcode( get_post()->post_content ?? '', 'wdm_certificate_verify' ) ) {
            wp_enqueue_style(
                'wdm-cert-frontend',
                WDM_CERT_PLUGIN_URL . 'assets/css/frontend.css',
                array(),
                WDM_CERT_VERSION
            );

            wp_enqueue_script(
                'wdm-cert-frontend',
                WDM_CERT_PLUGIN_URL . 'assets/js/frontend.js',
                array( 'jquery' ),
                WDM_CERT_VERSION,
                true
            );

            wp_localize_script( 'wdm-cert-frontend', 'wdmCertVars', array(
                'ajaxUrl'         => admin_url( 'admin-ajax.php' ),
                'nonce'           => wp_create_nonce( 'wdm_cert_verify' ),
                'verificationUrl' => WDM_Cert_Helper::get_verification_url(),
                'strings'         => array(
                    'loading'   => __( 'Verifying...', 'wdm-certificate-customizations' ),
                    'error'     => __( 'An error occurred. Please try again.', 'wdm-certificate-customizations' ),
                ),
            ) );
        }
    }

    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets( $hook ) {
        $screen = get_current_screen();

        // Load on plugin settings page and course edit pages
        if (
            ( isset( $_GET['page'] ) && $_GET['page'] === 'wdm-certificate-settings' ) ||
            ( $screen && in_array( $screen->post_type, array( 'sfwd-courses', 'sfwd-quiz', 'groups' ), true ) )
        ) {
            wp_enqueue_style(
                'wdm-cert-admin',
                WDM_CERT_PLUGIN_URL . 'assets/css/admin.css',
                array(),
                WDM_CERT_VERSION
            );

            wp_enqueue_script(
                'wdm-cert-admin',
                WDM_CERT_PLUGIN_URL . 'assets/js/admin.js',
                array( 'jquery' ),
                WDM_CERT_VERSION,
                true
            );

            wp_localize_script( 'wdm-cert-admin', 'wdmCertAdmin', array(
                'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                'nonce'   => wp_create_nonce( 'wdm_cert_admin' ),
                'strings' => array(
                    'generating'  => __( 'Generating Certificate IDs...', 'wdm-certificate-customizations' ),
                    'complete'    => __( 'Complete!', 'wdm-certificate-customizations' ),
                    'error'       => __( 'An error occurred.', 'wdm-certificate-customizations' ),
                ),
            ) );
        }
    }

    /**
     * Plugin activation
     */
    public function activate() {
        // Create verification page if it doesn't exist
        $this->create_verification_page();

        // Set default options
        $this->set_default_options();

        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Allow access to pocket certificates
     *
     * This hooks into LearnDash's certificate access denied action
     * and checks if the requested certificate is a pocket certificate
     * that should be allowed.
     */
    public function allow_pocket_certificate() {
        // Only run on certificate pages
        if ( ! is_singular( 'sfwd-certificates' ) ) {
            return;
        }

        // Check for required parameters
        if ( ! isset( $_GET['cert-nonce'] ) || empty( $_GET['cert-nonce'] ) ) {
            return;
        }

        $certificate_post = get_post( get_the_ID() );
        if ( ! $certificate_post ) {
            return;
        }

        // Determine source type and ID
        $source_id   = 0;
        $source_type = '';

        if ( isset( $_GET['course_id'] ) && ! empty( $_GET['course_id'] ) ) {
            $source_id   = absint( $_GET['course_id'] );
            $source_type = 'course';
        } elseif ( isset( $_GET['quiz'] ) && ! empty( $_GET['quiz'] ) ) {
            $source_id   = absint( $_GET['quiz'] );
            $source_type = 'quiz';
        } elseif ( isset( $_GET['group_id'] ) && ! empty( $_GET['group_id'] ) ) {
            $source_id   = absint( $_GET['group_id'] );
            $source_type = 'group';
        }

        if ( ! $source_id || ! $source_type ) {
            return;
        }

        // Get the viewing user ID and certificate user ID
        $view_user_id = get_current_user_id();

        if ( ( learndash_is_admin_user() || learndash_is_group_leader_user() ) && isset( $_GET['user'] ) && ! empty( $_GET['user'] ) ) {
            $cert_user_id = absint( $_GET['user'] );
        } else {
            $cert_user_id = get_current_user_id();
        }

        if ( ! $cert_user_id ) {
            return;
        }

        // Verify nonce
        if ( ! wp_verify_nonce( sanitize_text_field( $_GET['cert-nonce'] ), $source_id . $cert_user_id . $view_user_id ) ) {
            return;
        }

        // Check if this certificate is the pocket certificate for this source
        $pocket_cert_id = absint( get_post_meta( $source_id, '_wdm_pocket_certificate', true ) );

        if ( $pocket_cert_id !== absint( $certificate_post->ID ) ) {
            // Not a pocket certificate for this source
            return;
        }

        // Verify user completion
        $has_completed = false;

        switch ( $source_type ) {
            case 'course':
                $course_status = learndash_course_status( $source_id, $cert_user_id, true );
                $has_completed = ( 'completed' === $course_status );
                break;

            case 'quiz':
                $quizinfo = get_user_meta( $cert_user_id, '_sfwd-quizzes', true );
                if ( ! empty( $quizinfo ) ) {
                    foreach ( $quizinfo as $quiz_i ) {
                        if ( intval( $quiz_i['quiz'] ) === $source_id ) {
                            $certificate_threshold = learndash_get_setting( $source_id, 'threshold' );
                            if ( ( isset( $quiz_i['percentage'] ) && $quiz_i['percentage'] >= $certificate_threshold * 100 ) ||
                                 ( isset( $quiz_i['count'] ) && $quiz_i['score'] / $quiz_i['count'] >= $certificate_threshold ) ) {
                                $has_completed = true;
                            }
                            break;
                        }
                    }
                }
                break;

            case 'group':
                if ( function_exists( 'learndash_get_user_group_status' ) ) {
                    $group_status  = learndash_get_user_group_status( $source_id, $cert_user_id, true );
                    $has_completed = ( 'completed' === $group_status );
                }
                break;
        }

        if ( ! $has_completed ) {
            return;
        }

        // All checks passed - render the pocket certificate
        if ( ( learndash_is_admin_user() || learndash_is_group_leader_user() ) && ( intval( $cert_user_id ) !== intval( $view_user_id ) ) ) {
            wp_set_current_user( $cert_user_id );
        }

        // Use LearnDash's certificate rendering
        if ( has_action( 'learndash_tcpdf_init' ) ) {
            do_action(
                'learndash_tcpdf_init',
                array(
                    'cert_id' => $certificate_post->ID,
                    'user_id' => $cert_user_id,
                    'post_id' => $source_id,
                )
            );
        } else {
            require_once LEARNDASH_LMS_PLUGIN_DIR . 'includes/ld-convert-post-pdf.php';
            learndash_certificate_post_shortcode(
                array(
                    'cert_id' => $certificate_post->ID,
                    'user_id' => $cert_user_id,
                    'post_id' => $source_id,
                )
            );
        }
        die();
    }

    /**
     * Create verification page
     */
    private function create_verification_page() {
        $options = get_option( 'wdm_certificate_options', array() );

        // Check if page already exists
        if ( ! empty( $options['verification_page_id'] ) ) {
            $page = get_post( $options['verification_page_id'] );
            if ( $page && $page->post_status !== 'trash' ) {
                return;
            }
        }

        // Create the page
        $page_id = wp_insert_post( array(
            'post_title'   => __( 'Certificate Verification', 'wdm-certificate-customizations' ),
            'post_content' => '[wdm_certificate_verify]',
            'post_status'  => 'publish',
            'post_type'    => 'page',
            'post_author'  => get_current_user_id() ?: 1,
        ) );

        if ( $page_id && ! is_wp_error( $page_id ) ) {
            $options['verification_page_id'] = $page_id;
            update_option( 'wdm_certificate_options', $options );
        }
    }

    /**
     * Set default options
     */
    private function set_default_options() {
        $defaults = array(
            'verification_page_id'      => 0,
            'qr_code_size'              => 150,
            'enable_pocket_certificate' => true,
            'certificate_id_prefix'     => '',
            'custom_css'                => '',
        );

        $options = get_option( 'wdm_certificate_options', array() );
        $options = wp_parse_args( $options, $defaults );
        update_option( 'wdm_certificate_options', $options );
    }

    /**
     * Get plugin option
     *
     * @param string $key Option key
     * @param mixed $default Default value
     * @return mixed
     */
    public function get_option( $key, $default = null ) {
        $options = get_option( 'wdm_certificate_options', array() );
        return isset( $options[ $key ] ) ? $options[ $key ] : $default;
    }
}

/**
 * Initialize plugin
 *
 * @return WDM_Certificate_Customizations
 */
function wdm_certificate_customizations() {
    return WDM_Certificate_Customizations::get_instance();
}

// Start the plugin
wdm_certificate_customizations();
