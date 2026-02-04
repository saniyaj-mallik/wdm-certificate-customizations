<?php
/**
 * Verification class for certificate verification system
 *
 * @package WDM_Certificate_Customizations
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Verification class
 */
class WDM_Cert_Verification {

    /**
     * Single instance
     *
     * @var WDM_Cert_Verification
     */
    private static $instance = null;

    /**
     * Get single instance
     *
     * @return WDM_Cert_Verification
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
        // Add rewrite rules for pretty URLs
        add_action( 'init', array( $this, 'add_rewrite_rules' ) );

        // Handle verification query var
        add_filter( 'query_vars', array( $this, 'add_query_vars' ) );

        // AJAX verification handler
        add_action( 'wp_ajax_wdm_cert_verify', array( $this, 'ajax_verify_certificate' ) );
        add_action( 'wp_ajax_nopriv_wdm_cert_verify', array( $this, 'ajax_verify_certificate' ) );
    }

    /**
     * Add rewrite rules for certificate verification
     */
    public function add_rewrite_rules() {
        $options = get_option( 'wdm_certificate_options', array() );
        $page_id = isset( $options['verification_page_id'] ) ? absint( $options['verification_page_id'] ) : 0;

        if ( ! $page_id ) {
            return;
        }

        $page = get_post( $page_id );
        if ( ! $page ) {
            return;
        }

        $page_slug = $page->post_name;

        // Add rewrite rule for /verification-page/CERT-ID/
        add_rewrite_rule(
            '^' . preg_quote( $page_slug ) . '/([A-Fa-f0-9_-]+)/?$',
            'index.php?pagename=' . $page_slug . '&cert_id=$matches[1]',
            'top'
        );
    }

    /**
     * Add query vars
     *
     * @param array $vars Query vars
     * @return array Modified query vars
     */
    public function add_query_vars( $vars ) {
        $vars[] = 'cert_id';
        $vars[] = 'view';
        return $vars;
    }

    /**
     * Verify a certificate by CSUID
     *
     * @param string $csuid Certificate ID
     * @return array Verification result
     */
    public function verify_certificate( $csuid ) {
        $csuid = strtoupper( trim( $csuid ) );

        // Validate format
        if ( ! WDM_Cert_Helper::is_csuid_valid( $csuid ) ) {
            return array(
                'valid'   => false,
                'error'   => 'invalid_format',
                'message' => __( 'Invalid Certificate ID format.', 'wdm-certificate-customizations' ),
            );
        }

        // Decode CSUID
        $decoded = WDM_Cert_Helper::decode_csuid( $csuid );

        if ( ! $decoded['cert_id'] || ! $decoded['source_id'] || ! $decoded['user_id'] ) {
            return array(
                'valid'   => false,
                'error'   => 'decode_failed',
                'message' => __( 'Could not decode Certificate ID.', 'wdm-certificate-customizations' ),
            );
        }

        // Get source post
        $source_post = get_post( $decoded['source_id'] );
        if ( ! $source_post ) {
            return array(
                'valid'   => false,
                'error'   => 'source_not_found',
                'message' => __( 'Certificate source not found.', 'wdm-certificate-customizations' ),
            );
        }

        // Determine source type
        $source_type = WDM_Cert_Helper::get_source_type( $source_post->post_type );
        if ( ! $source_type ) {
            return array(
                'valid'   => false,
                'error'   => 'invalid_source_type',
                'message' => __( 'Invalid certificate source type.', 'wdm-certificate-customizations' ),
            );
        }

        // Get user
        $user = get_user_by( 'ID', $decoded['user_id'] );
        if ( ! $user ) {
            return array(
                'valid'   => false,
                'error'   => 'user_not_found',
                'message' => __( 'Certificate recipient not found.', 'wdm-certificate-customizations' ),
            );
        }

        // Get certificate template
        $cert_post = get_post( $decoded['cert_id'] );
        if ( ! $cert_post || $cert_post->post_type !== 'sfwd-certificates' ) {
            return array(
                'valid'   => false,
                'error'   => 'certificate_not_found',
                'message' => __( 'Certificate template not found.', 'wdm-certificate-customizations' ),
            );
        }

        // Verify certificate is assigned to this source
        $assigned_cert = WDM_Cert_Helper::get_assigned_certificate( $decoded['source_id'], $source_type );
        if ( $assigned_cert != $decoded['cert_id'] ) {
            return array(
                'valid'   => false,
                'error'   => 'certificate_mismatch',
                'message' => __( 'Certificate is not assigned to this source.', 'wdm-certificate-customizations' ),
            );
        }

        // Verify user completed the source
        $has_completed = $this->verify_completion( $decoded['source_id'], $decoded['user_id'], $source_type );
        if ( ! $has_completed ) {
            return array(
                'valid'   => false,
                'error'   => 'not_completed',
                'message' => __( 'User has not completed this course/quiz.', 'wdm-certificate-customizations' ),
            );
        }

        // Get certificate record (or generate if missing)
        $handler = WDM_Cert_Handler::get_instance();
        $record = $handler->get_certificate_record( $decoded['source_id'], $decoded['user_id'], $source_type );

        if ( ! $record ) {
            // Generate record on-the-fly
            $handler->generate_certificate_record( $decoded['cert_id'], $decoded['source_id'], $decoded['user_id'], $source_type );
            $record = $handler->get_certificate_record( $decoded['source_id'], $decoded['user_id'], $source_type );
        }

        // Get pocket certificate
        $pocket_cert_id = WDM_Cert_Helper::get_pocket_certificate( $decoded['source_id'], $source_type );
        $pocket_cert = $pocket_cert_id ? get_post( $pocket_cert_id ) : null;

        // Get completion date
        $completion_date = $record ? $record['completion_date'] : WDM_Cert_Helper::get_completion_date( $decoded['source_id'], $decoded['user_id'], $source_type );

        // Build certificate data
        $certificate_data = array(
            'csuid'            => $csuid,
            'recipient'        => array(
                'id'           => $user->ID,
                'name'         => $user->display_name,
                'email'        => $user->user_email,
                'avatar_url'   => get_avatar_url( $user->ID ),
            ),
            'source'           => array(
                'id'           => $source_post->ID,
                'type'         => $source_type,
                'title'        => $source_post->post_title,
                'url'          => get_permalink( $source_post->ID ),
            ),
            'standard_certificate' => array(
                'id'           => $cert_post->ID,
                'title'        => $cert_post->post_title,
                'pdf_url'      => WDM_Cert_Helper::get_pdf_url( $cert_post->ID, $source_post->ID, $user->ID, $source_type ),
            ),
            'pocket_certificate' => $pocket_cert ? array(
                'id'           => $pocket_cert->ID,
                'title'        => $pocket_cert->post_title,
                'pdf_url'      => WDM_Cert_Helper::get_pdf_url( $pocket_cert->ID, $source_post->ID, $user->ID, $source_type ),
            ) : null,
            'completion_date'  => $completion_date,
            'completion_date_formatted' => date_i18n( get_option( 'date_format' ), $completion_date ),
            'is_owner'         => is_user_logged_in() && get_current_user_id() === $user->ID,
        );

        // Get course if source is quiz
        if ( $source_type === 'quiz' ) {
            $course_id = learndash_get_course_id( $source_post->ID );
            if ( $course_id ) {
                $course = get_post( $course_id );
                if ( $course ) {
                    $certificate_data['course'] = array(
                        'id'    => $course->ID,
                        'title' => $course->post_title,
                        'url'   => get_permalink( $course->ID ),
                    );
                }
            }
        }

        return array(
            'valid'       => true,
            'certificate' => $certificate_data,
        );
    }

    /**
     * Verify user completed the source
     *
     * @param int $source_id Source ID
     * @param int $user_id User ID
     * @param string $source_type Source type
     * @return bool
     */
    private function verify_completion( $source_id, $user_id, $source_type ) {
        switch ( $source_type ) {
            case 'course':
                return learndash_course_completed( $user_id, $source_id );

            case 'quiz':
                $quiz_attempts = learndash_get_user_quiz_attempt( $user_id, array( 'quiz' => $source_id ) );
                if ( empty( $quiz_attempts ) ) {
                    return false;
                }
                // Check if any attempt passed
                foreach ( $quiz_attempts as $attempt ) {
                    if ( isset( $attempt['pass'] ) && $attempt['pass'] ) {
                        return true;
                    }
                }
                // If no pass field, assume completed
                return true;

            case 'group':
                if ( function_exists( 'learndash_get_user_group_completed_timestamp' ) ) {
                    $timestamp = learndash_get_user_group_completed_timestamp( $source_id, $user_id );
                    return ! empty( $timestamp );
                }
                return false;

            default:
                return false;
        }
    }

    /**
     * AJAX handler for certificate verification
     */
    public function ajax_verify_certificate() {
        // Verify nonce
        check_ajax_referer( 'wdm_cert_verify', 'nonce' );

        // Get certificate ID
        $cert_id = isset( $_POST['cert_id'] ) ? sanitize_text_field( $_POST['cert_id'] ) : '';

        if ( empty( $cert_id ) ) {
            wp_send_json_error( array(
                'message' => __( 'Please enter a Certificate ID.', 'wdm-certificate-customizations' ),
            ) );
        }

        // Verify certificate
        $result = $this->verify_certificate( $cert_id );

        if ( $result['valid'] ) {
            // Render verification result HTML
            ob_start();
            $this->render_verification_result( $result['certificate'] );
            $html = ob_get_clean();

            wp_send_json_success( array(
                'html'        => $html,
                'certificate' => $result['certificate'],
            ) );
        } else {
            wp_send_json_error( array(
                'message' => $result['message'],
                'error'   => $result['error'],
            ) );
        }
    }

    /**
     * Render verification result HTML
     *
     * @param array $certificate Certificate data
     */
    public function render_verification_result( $certificate ) {
        // Load template
        $template_path = WDM_CERT_PLUGIN_DIR . 'templates/verification-result.php';

        if ( file_exists( $template_path ) ) {
            include $template_path;
        }
    }

    /**
     * Get verification page content
     *
     * @return string HTML content
     */
    public function get_verification_page_content() {
        ob_start();

        // Check for certificate ID in URL
        $cert_id = get_query_var( 'cert_id' );
        if ( ! $cert_id && isset( $_GET['cert_id'] ) ) {
            $cert_id = sanitize_text_field( $_GET['cert_id'] );
        }

        // Get view preference
        $view = get_query_var( 'view' );
        if ( ! $view && isset( $_GET['view'] ) ) {
            $view = sanitize_text_field( $_GET['view'] );
        }

        // Load search form template
        include WDM_CERT_PLUGIN_DIR . 'templates/search-form.php';

        // If certificate ID provided, show result
        if ( $cert_id ) {
            $result = $this->verify_certificate( $cert_id );

            echo '<div id="wdm-cert-result" class="wdm-cert-result">';

            if ( $result['valid'] ) {
                $certificate = $result['certificate'];
                $certificate['active_view'] = $view === 'pocket' ? 'pocket' : 'standard';
                include WDM_CERT_PLUGIN_DIR . 'templates/verification-result.php';
            } else {
                include WDM_CERT_PLUGIN_DIR . 'templates/verification-error.php';
            }

            echo '</div>';
        } else {
            echo '<div id="wdm-cert-result" class="wdm-cert-result"></div>';
        }

        return ob_get_clean();
    }
}
