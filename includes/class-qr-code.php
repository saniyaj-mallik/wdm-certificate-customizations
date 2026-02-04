<?php
/**
 * QR Code generation class
 *
 * @package WDM_Certificate_Customizations
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * QR Code class
 */
class WDM_Cert_QR_Code {

    /**
     * Generate QR code image URL using external API
     *
     * @param string $data Data to encode in QR code
     * @param int $size Size in pixels (default: 150)
     * @return string QR code image URL
     */
    public static function generate_url( $data, $size = 150 ) {
        if ( empty( $data ) ) {
            return '';
        }

        $size = absint( $size );
        if ( $size < 50 ) {
            $size = 50;
        }
        if ( $size > 500 ) {
            $size = 500;
        }

        // Use QuickChart.io API (free, no API key required, reliable)
        return sprintf(
            'https://quickchart.io/qr?text=%s&size=%d&margin=1',
            rawurlencode( $data ),
            $size
        );
    }

    /**
     * Generate QR code HTML img tag
     *
     * @param string $url URL to encode in QR code
     * @param array $atts Attributes (size, align, alt, class)
     * @return string HTML img tag
     */
    public static function generate_html( $url, $atts = array() ) {
        if ( empty( $url ) ) {
            return '';
        }

        $defaults = array(
            'size'  => 150,
            'align' => '',
            'alt'   => __( 'Certificate QR Code', 'wdm-certificate-customizations' ),
            'class' => 'wdm-cert-qr-code',
        );
        $atts = wp_parse_args( $atts, $defaults );

        $qr_url = self::generate_url( $url, $atts['size'] );

        if ( empty( $qr_url ) ) {
            return '';
        }

        $img = sprintf(
            '<img src="%s" width="%d" height="%d" alt="%s" class="%s" style="max-width: 100%%; height: auto;" />',
            esc_url( $qr_url ),
            absint( $atts['size'] ),
            absint( $atts['size'] ),
            esc_attr( $atts['alt'] ),
            esc_attr( $atts['class'] )
        );

        // Wrap in alignment div if specified
        if ( ! empty( $atts['align'] ) && in_array( $atts['align'], array( 'left', 'center', 'right' ), true ) ) {
            return sprintf(
                '<div style="margin: 0; line-height: 0; text-align: %s;">%s</div>',
                esc_attr( $atts['align'] ),
                $img
            );
        }

        return sprintf( '<span style="margin: 0; line-height: 0; display: inline-block;">%s</span>', $img );
    }

    /**
     * Generate QR code for a certificate
     *
     * @param string $csuid Certificate ID
     * @param array $atts Attributes
     * @return string HTML
     */
    public static function generate_for_certificate( $csuid, $atts = array() ) {
        if ( empty( $csuid ) ) {
            return '';
        }

        $verification_url = WDM_Cert_Helper::get_verification_url( $csuid );

        if ( empty( $verification_url ) ) {
            return '';
        }

        return self::generate_html( $verification_url, $atts );
    }

    /**
     * Get QR code for current certificate context
     *
     * This is used within certificate templates to dynamically generate
     * QR codes based on the current user viewing their certificate.
     *
     * @param array $atts Shortcode attributes
     * @return string HTML
     */
    public static function get_contextual_qr_code( $atts = array() ) {
        // Get current user
        $user_id = get_current_user_id();
        if ( ! $user_id ) {
            return '';
        }

        // Try to get certificate context from query vars or global
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

        // If still no context, try to get from user meta for recent completion
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

        // Get options for default size
        $options = get_option( 'wdm_certificate_options', array() );
        $default_size = isset( $options['qr_code_size'] ) ? absint( $options['qr_code_size'] ) : 150;

        $atts = wp_parse_args( $atts, array(
            'size' => $default_size,
        ) );

        return self::generate_for_certificate( $csuid, $atts );
    }
}
