<?php
/**
 * Certificate verification search form template
 *
 * @package WDM_Certificate_Customizations
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$cert_id = isset( $cert_id ) ? $cert_id : '';
?>

<div class="wdm-cert-search-container">
    <div class="wdm-cert-search-header">
        <h2 class="wdm-cert-search-title"><?php esc_html_e( 'Verify Certificate', 'wdm-certificate-customizations' ); ?></h2>
        <p class="wdm-cert-search-description">
            <?php esc_html_e( 'Enter the Certificate ID to verify its authenticity.', 'wdm-certificate-customizations' ); ?>
        </p>
    </div>

    <form id="wdm-cert-search-form" class="wdm-cert-search-form" method="get">
        <div class="wdm-cert-search-field">
            <label for="wdm-cert-id-input" class="screen-reader-text">
                <?php esc_html_e( 'Certificate ID', 'wdm-certificate-customizations' ); ?>
            </label>
            <input
                type="text"
                id="wdm-cert-id-input"
                name="cert_id"
                class="wdm-cert-id-input"
                placeholder="<?php esc_attr_e( 'Enter Certificate ID (e.g., ABC123-DEF456-GHI789)', 'wdm-certificate-customizations' ); ?>"
                value="<?php echo esc_attr( $cert_id ); ?>"
                required
                autocomplete="off"
            />
        </div>
        <div class="wdm-cert-search-button">
            <button type="submit" class="wdm-cert-verify-btn">
                <span class="wdm-cert-btn-text"><?php esc_html_e( 'Verify Certificate', 'wdm-certificate-customizations' ); ?></span>
                <span class="wdm-cert-btn-loading" style="display: none;">
                    <span class="wdm-cert-spinner"></span>
                    <?php esc_html_e( 'Verifying...', 'wdm-certificate-customizations' ); ?>
                </span>
            </button>
        </div>
    </form>
</div>
