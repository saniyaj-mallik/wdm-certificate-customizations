<?php
/**
 * Certificate Handler class for certificate generation and storage
 *
 * @package WDM_Certificate_Customizations
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Certificate Handler class
 */
class WDM_Cert_Handler {

    /**
     * Single instance
     *
     * @var WDM_Cert_Handler
     */
    private static $instance = null;

    /**
     * Get single instance
     *
     * @return WDM_Cert_Handler
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
        // Course completion
        add_action( 'learndash_course_completed', array( $this, 'on_course_completed' ), 10, 1 );

        // Quiz completion
        add_action( 'learndash_quiz_completed', array( $this, 'on_quiz_completed' ), 10, 2 );

        // Group completion
        add_action( 'ld_added_group_access', array( $this, 'check_group_completion' ), 10, 2 );

        // Modify certificate link to point to verification page
        add_filter( 'learndash_course_certificate_link', array( $this, 'modify_certificate_link' ), 20, 3 );
        add_filter( 'learndash_quiz_certificate_link', array( $this, 'modify_quiz_certificate_link' ), 20, 2 );
    }

    /**
     * Handle course completion
     *
     * @param array $data Completion data
     */
    public function on_course_completed( $data ) {
        if ( empty( $data['user'] ) || empty( $data['course'] ) ) {
            return;
        }

        $user_id   = $data['user']->ID;
        $course_id = $data['course']->ID;

        // Get assigned certificate
        $cert_id = WDM_Cert_Helper::get_assigned_certificate( $course_id, 'course' );

        if ( ! $cert_id ) {
            return;
        }

        // Generate and store certificate record
        $this->generate_certificate_record( $cert_id, $course_id, $user_id, 'course' );
    }

    /**
     * Handle quiz completion
     *
     * @param array $quiz_data Quiz data
     * @param WP_User $user User object
     */
    public function on_quiz_completed( $quiz_data, $user ) {
        if ( empty( $quiz_data['quiz'] ) || empty( $user->ID ) ) {
            return;
        }

        $user_id = $user->ID;
        $quiz_id = $quiz_data['quiz'];

        // Get assigned certificate
        $cert_id = WDM_Cert_Helper::get_assigned_certificate( $quiz_id, 'quiz' );

        if ( ! $cert_id ) {
            return;
        }

        // Check if quiz passed (if applicable)
        if ( isset( $quiz_data['pass'] ) && ! $quiz_data['pass'] ) {
            return;
        }

        // Generate and store certificate record
        $this->generate_certificate_record( $cert_id, $quiz_id, $user_id, 'quiz' );
    }

    /**
     * Check group completion
     *
     * @param int $user_id User ID
     * @param int $group_id Group ID
     */
    public function check_group_completion( $user_id, $group_id ) {
        // Check if group is completed
        if ( ! function_exists( 'learndash_get_user_group_completed_timestamp' ) ) {
            return;
        }

        $timestamp = learndash_get_user_group_completed_timestamp( $group_id, $user_id );
        if ( ! $timestamp ) {
            return;
        }

        // Get assigned certificate
        $cert_id = WDM_Cert_Helper::get_assigned_certificate( $group_id, 'group' );

        if ( ! $cert_id ) {
            return;
        }

        // Generate and store certificate record
        $this->generate_certificate_record( $cert_id, $group_id, $user_id, 'group' );
    }

    /**
     * Generate and store certificate record
     *
     * @param int $cert_id Certificate template ID
     * @param int $source_id Source (course/quiz/group) ID
     * @param int $user_id User ID
     * @param string $source_type Source type (course, quiz, group)
     * @return bool Success
     */
    public function generate_certificate_record( $cert_id, $source_id, $user_id, $source_type ) {
        if ( ! $cert_id || ! $source_id || ! $user_id || ! $source_type ) {
            return false;
        }

        // Check if record already exists
        $meta_key = '_wdm_certificate_' . $source_type . '_' . $source_id;
        $existing = get_user_meta( $user_id, $meta_key, true );

        if ( ! empty( $existing ) && isset( $existing['certificate_id'] ) ) {
            // Record already exists
            return true;
        }

        // Generate Certificate ID
        $csuid = WDM_Cert_Helper::encode_csuid( $cert_id, $source_id, $user_id );

        if ( empty( $csuid ) ) {
            return false;
        }

        // Get pocket certificate if assigned
        $pocket_cert = WDM_Cert_Helper::get_pocket_certificate( $source_id, $source_type );

        // Get completion date
        $completion_date = WDM_Cert_Helper::get_completion_date( $source_id, $user_id, $source_type );

        // Build record
        $record = array(
            'certificate_id'  => $csuid,
            'standard_cert'   => absint( $cert_id ),
            'pocket_cert'     => absint( $pocket_cert ),
            'source_type'     => sanitize_key( $source_type ),
            'source_id'       => absint( $source_id ),
            'user_id'         => absint( $user_id ),
            'completion_date' => absint( $completion_date ),
            'generated_date'  => time(),
            'is_retroactive'  => false,
        );

        // Store record
        update_user_meta( $user_id, $meta_key, $record );

        /**
         * Action fired after certificate record is generated
         *
         * @param array $record Certificate record
         * @param string $csuid Certificate ID
         */
        do_action( 'wdm_certificate_record_generated', $record, $csuid );

        return true;
    }

    /**
     * Get certificate record for a user and source
     *
     * @param int $source_id Source ID
     * @param int $user_id User ID
     * @param string $source_type Source type
     * @return array|false Certificate record or false
     */
    public function get_certificate_record( $source_id, $user_id, $source_type ) {
        $meta_key = '_wdm_certificate_' . $source_type . '_' . $source_id;
        $record = get_user_meta( $user_id, $meta_key, true );

        if ( empty( $record ) || ! isset( $record['certificate_id'] ) ) {
            return false;
        }

        return $record;
    }

    /**
     * Modify course certificate link to point to verification page
     *
     * @param string $link Certificate link
     * @param int $course_id Course ID
     * @param int $user_id User ID
     * @return string Modified link
     */
    public function modify_certificate_link( $link, $course_id, $user_id ) {
        if ( empty( $link ) || ! $course_id || ! $user_id ) {
            return $link;
        }

        // Get certificate ID for this course
        $cert_id = WDM_Cert_Helper::get_assigned_certificate( $course_id, 'course' );
        if ( ! $cert_id ) {
            return $link;
        }

        // Get or generate certificate record
        $record = $this->get_certificate_record( $course_id, $user_id, 'course' );

        if ( ! $record ) {
            // Try to generate on-the-fly
            $this->generate_certificate_record( $cert_id, $course_id, $user_id, 'course' );
            $record = $this->get_certificate_record( $course_id, $user_id, 'course' );
        }

        if ( ! $record || ! isset( $record['certificate_id'] ) ) {
            return $link;
        }

        // Return verification page URL
        return WDM_Cert_Helper::get_verification_url( $record['certificate_id'] );
    }

    /**
     * Modify quiz certificate link to point to verification page
     *
     * @param string $link Certificate link
     * @param WP_User|int $user User object or ID
     * @return string Modified link
     */
    public function modify_quiz_certificate_link( $link, $user ) {
        if ( empty( $link ) ) {
            return $link;
        }

        // Get user ID
        $user_id = is_object( $user ) ? $user->ID : absint( $user );
        if ( ! $user_id ) {
            return $link;
        }

        // Try to extract quiz ID from the link
        $quiz_id = 0;
        if ( preg_match( '/quiz[_-]?id[=\/](\d+)/i', $link, $matches ) ) {
            $quiz_id = absint( $matches[1] );
        } else {
            // Try to get from post ID in link
            $url_parts = wp_parse_url( $link );
            if ( isset( $url_parts['query'] ) ) {
                parse_str( $url_parts['query'], $query_vars );
                if ( isset( $query_vars['quiz'] ) ) {
                    $quiz_id = absint( $query_vars['quiz'] );
                }
            }
        }

        if ( ! $quiz_id ) {
            return $link;
        }

        // Get certificate ID for this quiz
        $cert_id = WDM_Cert_Helper::get_assigned_certificate( $quiz_id, 'quiz' );
        if ( ! $cert_id ) {
            return $link;
        }

        // Get or generate certificate record
        $record = $this->get_certificate_record( $quiz_id, $user_id, 'quiz' );

        if ( ! $record ) {
            // Try to generate on-the-fly
            $this->generate_certificate_record( $cert_id, $quiz_id, $user_id, 'quiz' );
            $record = $this->get_certificate_record( $quiz_id, $user_id, 'quiz' );
        }

        if ( ! $record || ! isset( $record['certificate_id'] ) ) {
            return $link;
        }

        // Return verification page URL
        return WDM_Cert_Helper::get_verification_url( $record['certificate_id'] );
    }

    /**
     * Get all certificate records for a user
     *
     * @param int $user_id User ID
     * @return array Certificate records
     */
    public function get_user_certificates( $user_id ) {
        global $wpdb;

        $user_id = absint( $user_id );
        if ( ! $user_id ) {
            return array();
        }

        // Get all certificate meta keys
        $meta_keys = $wpdb->get_col( $wpdb->prepare(
            "SELECT DISTINCT meta_key FROM {$wpdb->usermeta}
             WHERE user_id = %d AND meta_key LIKE %s",
            $user_id,
            '_wdm_certificate_%'
        ) );

        $certificates = array();
        foreach ( $meta_keys as $meta_key ) {
            $record = get_user_meta( $user_id, $meta_key, true );
            if ( ! empty( $record ) && isset( $record['certificate_id'] ) ) {
                $certificates[] = $record;
            }
        }

        return $certificates;
    }

    /**
     * Delete certificate record
     *
     * @param int $source_id Source ID
     * @param int $user_id User ID
     * @param string $source_type Source type
     * @return bool Success
     */
    public function delete_certificate_record( $source_id, $user_id, $source_type ) {
        $meta_key = '_wdm_certificate_' . $source_type . '_' . $source_id;
        return delete_user_meta( $user_id, $meta_key );
    }
}
