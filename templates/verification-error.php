<?php
/**
 * Certificate verification error template
 *
 * @package WDM_Certificate_Customizations
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Get error message
$error_message = isset( $result['message'] ) ? $result['message'] : __( 'Certificate could not be verified.', 'wdm-certificate-customizations' );
$error_code = isset( $result['error'] ) ? $result['error'] : 'unknown';
?>

<div class="wdm-cert-error-container">
    <div class="wdm-cert-error-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="15" y1="9" x2="9" y2="15"></line>
            <line x1="9" y1="9" x2="15" y2="15"></line>
        </svg>
    </div>

    <h3 class="wdm-cert-error-title"><?php esc_html_e( 'Certificate Not Found', 'wdm-certificate-customizations' ); ?></h3>

    <p class="wdm-cert-error-message"><?php echo esc_html( $error_message ); ?></p>

    <div class="wdm-cert-error-reasons">
        <p class="wdm-cert-error-reasons-title"><?php esc_html_e( 'Possible reasons:', 'wdm-certificate-customizations' ); ?></p>
        <ul>
            <li><?php esc_html_e( 'The Certificate ID was entered incorrectly', 'wdm-certificate-customizations' ); ?></li>
            <li><?php esc_html_e( 'The certificate does not exist', 'wdm-certificate-customizations' ); ?></li>
            <li><?php esc_html_e( 'The certificate has been revoked', 'wdm-certificate-customizations' ); ?></li>
            <li><?php esc_html_e( 'The course/quiz associated with this certificate has been removed', 'wdm-certificate-customizations' ); ?></li>
        </ul>
    </div>

    <div class="wdm-cert-error-actions">
        <a href="<?php echo esc_url( WDM_Cert_Helper::get_verification_url() ); ?>" class="wdm-cert-try-again-btn">
            <?php esc_html_e( 'Try Another Certificate', 'wdm-certificate-customizations' ); ?>
        </a>
    </div>
</div>
