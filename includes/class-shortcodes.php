<?php
/**
 * Shortcodes class for all plugin shortcodes
 *
 * @package WDM_Certificate_Customizations
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Shortcodes class
 */
class WDM_Cert_Shortcodes {

    /**
     * Single instance
     *
     * @var WDM_Cert_Shortcodes
     */
    private static $instance = null;

    /**
     * Get single instance
     *
     * @return WDM_Cert_Shortcodes
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
        $this->register_shortcodes();
    }

    /**
     * Register all shortcodes
     */
    private function register_shortcodes() {
        add_shortcode( 'wdm_certificate_verify', array( $this, 'shortcode_verify' ) );
        add_shortcode( 'wdm_certificate_qr_code', array( $this, 'shortcode_qr_code' ) );
        add_shortcode( 'wdm_certificate_id', array( $this, 'shortcode_certificate_id' ) );
        add_shortcode( 'wdm_certificate_verification_url', array( $this, 'shortcode_verification_url' ) );
    }

    /**
     * Certificate verification shortcode
     *
     * @param array $atts Shortcode attributes
     * @return string HTML output
     */
    public function shortcode_verify( $atts ) {
        $atts = shortcode_atts( array(
            'show_form' => 'yes',
        ), $atts, 'wdm_certificate_verify' );

        $verification = WDM_Cert_Verification::get_instance();
        return $verification->get_verification_page_content();
    }

    /**
     * QR code shortcode for certificates
     *
     * @param array $atts Shortcode attributes
     * @return string HTML output
     */
    public function shortcode_qr_code( $atts ) {
        // Get default size from options
        $options = get_option( 'wdm_certificate_options', array() );
        $default_size = isset( $options['qr_code_size'] ) ? absint( $options['qr_code_size'] ) : 150;

        $atts = shortcode_atts( array(
            'size'  => $default_size,
            'align' => 'center',
            'class' => 'wdm-cert-qr-code',
        ), $atts, 'wdm_certificate_qr_code' );

        return WDM_Cert_QR_Code::get_contextual_qr_code( $atts );
    }

    /**
     * Certificate ID shortcode
     *
     * @param array $atts Shortcode attributes
     * @return string Certificate ID or empty string
     */
    public function shortcode_certificate_id( $atts ) {
        $atts = shortcode_atts( array(
            'prefix' => '',
            'suffix' => '',
            'class'  => 'wdm-cert-id',
        ), $atts, 'wdm_certificate_id' );

        // Get current user
        $user_id = get_current_user_id();
        if ( ! $user_id ) {
            return '';
        }

        // Get certificate context
        $cert_id   = 0;
        $source_id = 0;

        // Check for LearnDash certificate context
        if ( isset( $_GET['course_id'] ) ) {
            $source_id = absint( $_GET['course_id'] );
        } elseif ( isset( $_GET['quiz'] ) ) {
            $source_id = absint( $_GET['quiz'] );
        } elseif ( isset( $_GET['group_id'] ) ) {
            $source_id = absint( $_GET['group_id'] );
        }

        // Get certificate ID from the current post if on certificate template
        global $post;
        if ( $post && $post->post_type === 'sfwd-certificates' ) {
            $cert_id = $post->ID;
        }

        // If we have source_id but no cert_id, get it from source settings
        if ( $source_id && ! $cert_id ) {
            $source_post = get_post( $source_id );
            if ( $source_post ) {
                $source_type = WDM_Cert_Helper::get_source_type( $source_post->post_type );
                if ( $source_type ) {
                    $cert_id = WDM_Cert_Helper::get_assigned_certificate( $source_id, $source_type );
                }
            }
        }

        if ( ! $cert_id || ! $source_id ) {
            return '<!-- WDM Certificate: No certificate context found -->';
        }

        // Always use standard certificate ID for CSUID (both standard and pocket share the same ID)
        $cert_id_for_csuid = WDM_Cert_Helper::get_standard_cert_for_csuid( $cert_id, $source_id );

        // Generate CSUID
        $csuid = WDM_Cert_Helper::encode_csuid( $cert_id_for_csuid, $source_id, $user_id );

        if ( empty( $csuid ) ) {
            return '';
        }

        // Format output
        $output = $atts['prefix'] . $csuid . $atts['suffix'];

        if ( ! empty( $atts['class'] ) ) {
            $output = sprintf(
                '<span class="%s">%s</span>',
                esc_attr( $atts['class'] ),
                esc_html( $output )
            );
        }

        return $output;
    }

    /**
     * Verification URL shortcode
     *
     * @param array $atts Shortcode attributes
     * @return string Verification URL
     */
    public function shortcode_verification_url( $atts ) {
        $atts = shortcode_atts( array(
            'link_text' => '',
            'class'     => '',
            'target'    => '_blank',
        ), $atts, 'wdm_certificate_verification_url' );

        // Get current user
        $user_id = get_current_user_id();
        if ( ! $user_id ) {
            return '';
        }

        // Get certificate context
        $cert_id   = 0;
        $source_id = 0;

        // Check for LearnDash certificate context
        if ( isset( $_GET['course_id'] ) ) {
            $source_id = absint( $_GET['course_id'] );
        } elseif ( isset( $_GET['quiz'] ) ) {
            $source_id = absint( $_GET['quiz'] );
        } elseif ( isset( $_GET['group_id'] ) ) {
            $source_id = absint( $_GET['group_id'] );
        }

        // Get certificate ID from the current post if on certificate template
        global $post;
        if ( $post && $post->post_type === 'sfwd-certificates' ) {
            $cert_id = $post->ID;
        }

        // If we have source_id but no cert_id, get it from source settings
        if ( $source_id && ! $cert_id ) {
            $source_post = get_post( $source_id );
            if ( $source_post ) {
                $source_type = WDM_Cert_Helper::get_source_type( $source_post->post_type );
                if ( $source_type ) {
                    $cert_id = WDM_Cert_Helper::get_assigned_certificate( $source_id, $source_type );
                }
            }
        }

        if ( ! $cert_id || ! $source_id ) {
            return '';
        }

        // Always use standard certificate ID for CSUID (both standard and pocket share the same ID)
        $cert_id_for_csuid = WDM_Cert_Helper::get_standard_cert_for_csuid( $cert_id, $source_id );

        // Generate CSUID
        $csuid = WDM_Cert_Helper::encode_csuid( $cert_id_for_csuid, $source_id, $user_id );

        if ( empty( $csuid ) ) {
            return '';
        }

        // Get verification URL
        $url = WDM_Cert_Helper::get_verification_url( $csuid );

        // Return as link or plain URL
        if ( ! empty( $atts['link_text'] ) ) {
            return sprintf(
                '<a href="%s" class="%s" target="%s">%s</a>',
                esc_url( $url ),
                esc_attr( $atts['class'] ),
                esc_attr( $atts['target'] ),
                esc_html( $atts['link_text'] )
            );
        }

        return esc_url( $url );
    }
}
