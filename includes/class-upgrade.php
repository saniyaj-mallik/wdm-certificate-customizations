<?php
/**
 * Upgrade class for migration and retroactive generation
 *
 * @package WDM_Certificate_Customizations
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Upgrade class
 */
class WDM_Cert_Upgrade {

    /**
     * Single instance
     *
     * @var WDM_Cert_Upgrade
     */
    private static $instance = null;

    /**
     * Get single instance
     *
     * @return WDM_Cert_Upgrade
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
        // Constructor
    }

    /**
     * Generate certificate IDs for historical completions
     *
     * @return array Result with count and details
     */
    public function generate_retroactive_certificate_ids() {
        global $wpdb;

        $generated_count = 0;
        $errors = array();

        // Get all course completions
        $course_completions = $this->get_course_completions();

        foreach ( $course_completions as $completion ) {
            $result = $this->process_completion( $completion, 'course' );
            if ( $result ) {
                $generated_count++;
            }
        }

        // Get all quiz completions
        $quiz_completions = $this->get_quiz_completions();

        foreach ( $quiz_completions as $completion ) {
            $result = $this->process_completion( $completion, 'quiz' );
            if ( $result ) {
                $generated_count++;
            }
        }

        // Get all group completions
        $group_completions = $this->get_group_completions();

        foreach ( $group_completions as $completion ) {
            $result = $this->process_completion( $completion, 'group' );
            if ( $result ) {
                $generated_count++;
            }
        }

        return array(
            'count'  => $generated_count,
            'errors' => $errors,
        );
    }

    /**
     * Get all course completions
     *
     * @return array Course completions
     */
    private function get_course_completions() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'learndash_user_activity';

        // Check if table exists
        if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) !== $table_name ) {
            return array();
        }

        $results = $wpdb->get_results( $wpdb->prepare(
            "SELECT DISTINCT user_id, post_id, activity_completed
             FROM {$table_name}
             WHERE activity_type = %s
             AND activity_completed > 0
             ORDER BY activity_completed ASC",
            'course'
        ), ARRAY_A );

        return $results ? $results : array();
    }

    /**
     * Get all quiz completions
     *
     * @return array Quiz completions
     */
    private function get_quiz_completions() {
        global $wpdb;

        $results = $wpdb->get_results(
            "SELECT DISTINCT user_id, meta_key
             FROM {$wpdb->usermeta}
             WHERE meta_key LIKE '_sfwd-quizzes'",
            ARRAY_A
        );

        $completions = array();

        if ( $results ) {
            foreach ( $results as $row ) {
                $quiz_data = get_user_meta( $row['user_id'], '_sfwd-quizzes', true );
                if ( is_array( $quiz_data ) ) {
                    foreach ( $quiz_data as $quiz_attempt ) {
                        if ( isset( $quiz_attempt['quiz'] ) && isset( $quiz_attempt['time'] ) ) {
                            $completions[] = array(
                                'user_id'            => $row['user_id'],
                                'post_id'            => $quiz_attempt['quiz'],
                                'activity_completed' => $quiz_attempt['time'],
                            );
                        }
                    }
                }
            }
        }

        return $completions;
    }

    /**
     * Get all group completions
     *
     * @return array Group completions
     */
    private function get_group_completions() {
        global $wpdb;

        $results = $wpdb->get_results(
            "SELECT user_id, meta_key, meta_value
             FROM {$wpdb->usermeta}
             WHERE meta_key LIKE 'learndash_group_completed_%'",
            ARRAY_A
        );

        $completions = array();

        if ( $results ) {
            foreach ( $results as $row ) {
                // Extract group ID from meta key
                if ( preg_match( '/learndash_group_completed_(\d+)/', $row['meta_key'], $matches ) ) {
                    $completions[] = array(
                        'user_id'            => $row['user_id'],
                        'post_id'            => $matches[1],
                        'activity_completed' => $row['meta_value'],
                    );
                }
            }
        }

        return $completions;
    }

    /**
     * Process a single completion
     *
     * @param array $completion Completion data
     * @param string $source_type Source type
     * @return bool True if certificate record was created
     */
    private function process_completion( $completion, $source_type ) {
        $user_id   = absint( $completion['user_id'] );
        $source_id = absint( $completion['post_id'] );

        if ( ! $user_id || ! $source_id ) {
            return false;
        }

        // Check if record already exists
        $meta_key = '_wdm_certificate_' . $source_type . '_' . $source_id;
        $existing = get_user_meta( $user_id, $meta_key, true );

        if ( ! empty( $existing ) && isset( $existing['certificate_id'] ) ) {
            // Record already exists
            return false;
        }

        // Get assigned certificate
        $cert_id = WDM_Cert_Helper::get_assigned_certificate( $source_id, $source_type );

        if ( ! $cert_id ) {
            // No certificate assigned
            return false;
        }

        // Generate CSUID
        $csuid = WDM_Cert_Helper::encode_csuid( $cert_id, $source_id, $user_id );

        if ( empty( $csuid ) ) {
            return false;
        }

        // Get pocket certificate
        $pocket_cert = WDM_Cert_Helper::get_pocket_certificate( $source_id, $source_type );

        // Get completion date
        $completion_date = isset( $completion['activity_completed'] ) ? absint( $completion['activity_completed'] ) : time();

        // Build record
        $record = array(
            'certificate_id'  => $csuid,
            'standard_cert'   => absint( $cert_id ),
            'pocket_cert'     => absint( $pocket_cert ),
            'source_type'     => $source_type,
            'source_id'       => $source_id,
            'user_id'         => $user_id,
            'completion_date' => $completion_date,
            'generated_date'  => time(),
            'is_retroactive'  => true,
        );

        // Store record
        update_user_meta( $user_id, $meta_key, $record );

        return true;
    }

    /**
     * Migrate data from LD Certificate Verify and Share plugin
     *
     * @return array Migration result
     */
    public function migrate_from_ld_cvss() {
        // This method can be expanded to migrate existing CSUID data from LD CVSS
        // For now, we rely on retroactive generation which achieves the same result

        return $this->generate_retroactive_certificate_ids();
    }

    /**
     * Get statistics about certificate records
     *
     * @return array Statistics
     */
    public function get_statistics() {
        global $wpdb;

        $stats = array(
            'total_records'    => 0,
            'course_records'   => 0,
            'quiz_records'     => 0,
            'group_records'    => 0,
            'retroactive'      => 0,
            'with_pocket_cert' => 0,
        );

        // Count all certificate records
        $meta_keys = $wpdb->get_results(
            "SELECT meta_key, meta_value
             FROM {$wpdb->usermeta}
             WHERE meta_key LIKE '_wdm_certificate_%'",
            ARRAY_A
        );

        foreach ( $meta_keys as $row ) {
            $record = maybe_unserialize( $row['meta_value'] );
            if ( ! is_array( $record ) || ! isset( $record['certificate_id'] ) ) {
                continue;
            }

            $stats['total_records']++;

            // Count by type
            if ( strpos( $row['meta_key'], '_wdm_certificate_course_' ) === 0 ) {
                $stats['course_records']++;
            } elseif ( strpos( $row['meta_key'], '_wdm_certificate_quiz_' ) === 0 ) {
                $stats['quiz_records']++;
            } elseif ( strpos( $row['meta_key'], '_wdm_certificate_group_' ) === 0 ) {
                $stats['group_records']++;
            }

            // Count retroactive
            if ( ! empty( $record['is_retroactive'] ) ) {
                $stats['retroactive']++;
            }

            // Count with pocket certificate
            if ( ! empty( $record['pocket_cert'] ) ) {
                $stats['with_pocket_cert']++;
            }
        }

        return $stats;
    }
}
