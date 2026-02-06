<?php
/**
 * Certificate verification result template
 *
 * @package WDM_Certificate_Customizations
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Ensure we have certificate data
if ( ! isset( $certificate ) || ! is_array( $certificate ) ) {
    return;
}

$has_pocket = ! empty( $certificate['pocket_certificate'] );
$active_view = isset( $certificate['active_view'] ) ? $certificate['active_view'] : 'standard';
$is_owner = isset( $certificate['is_owner'] ) ? $certificate['is_owner'] : false;
?>

<div class="wdm-cert-verified-container">
    <!-- Success Header -->
    <div class="wdm-cert-verified-header">
        <div class="wdm-cert-verified-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
        </div>
        <h3 class="wdm-cert-verified-title"><?php esc_html_e( 'Certificate Verified', 'wdm-certificate-customizations' ); ?></h3>
        <p class="wdm-cert-verified-subtitle"><?php esc_html_e( 'This certificate is valid and authentic.', 'wdm-certificate-customizations' ); ?></p>
    </div>

    <?php if ( $has_pocket ) : ?>
    <!-- Certificate Type Tabs -->
    <div class="wdm-cert-tabs">
        <button type="button" class="wdm-cert-tab <?php echo $active_view === 'standard' ? 'active' : ''; ?>" data-view="standard">
            <span class="wdm-cert-tab-radio"></span>
            <?php esc_html_e( 'Standard Certificate', 'wdm-certificate-customizations' ); ?>
        </button>
        <button type="button" class="wdm-cert-tab <?php echo $active_view === 'pocket' ? 'active' : ''; ?>" data-view="pocket">
            <span class="wdm-cert-tab-radio"></span>
            <?php esc_html_e( 'Wallet Card', 'wdm-certificate-customizations' ); ?>
        </button>
    </div>
    <?php endif; ?>

    <!-- Certificate Preview -->
    <div class="wdm-cert-preview-container">
        <!-- Standard Certificate -->
        <div class="wdm-cert-preview wdm-cert-preview-standard" style="<?php echo $active_view === 'standard' ? '' : 'display: none;'; ?>">
            <div class="wdm-cert-preview-frame">
                <iframe
                    id="wdm-cert-iframe-standard"
                    src="<?php echo esc_url( $certificate['standard_certificate']['pdf_url'] ); ?>"
                    class="wdm-cert-iframe"
                    title="<?php echo esc_attr( $certificate['standard_certificate']['title'] ); ?>"
                ></iframe>
            </div>
        </div>

        <?php if ( $has_pocket ) : ?>
        <!-- Wallet Card Certificate -->
        <div class="wdm-cert-preview wdm-cert-preview-pocket" style="<?php echo $active_view === 'pocket' ? '' : 'display: none;'; ?>">
            <div class="wdm-cert-preview-frame">
                <iframe
                    id="wdm-cert-iframe-pocket"
                    src="<?php echo esc_url( $certificate['pocket_certificate']['pdf_url'] ); ?>"
                    class="wdm-cert-iframe"
                    title="<?php echo esc_attr( $certificate['pocket_certificate']['title'] ); ?>"
                ></iframe>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Certificate Details -->
    <div class="wdm-cert-details">
        <h4 class="wdm-cert-details-title"><?php esc_html_e( 'Verification Details', 'wdm-certificate-customizations' ); ?></h4>

        <div class="wdm-cert-details-grid">
            <div class="wdm-cert-detail-item">
                <span class="wdm-cert-detail-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                        <polyline points="10 9 9 9 8 9"></polyline>
                    </svg>
                </span>
                <span class="wdm-cert-detail-label"><?php esc_html_e( 'Certificate ID', 'wdm-certificate-customizations' ); ?></span>
                <span class="wdm-cert-detail-value wdm-cert-csuid"><?php echo esc_html( $certificate['csuid'] ); ?></span>
            </div>

            <div class="wdm-cert-detail-item">
                <span class="wdm-cert-detail-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                </span>
                <span class="wdm-cert-detail-label"><?php esc_html_e( 'Recipient', 'wdm-certificate-customizations' ); ?></span>
                <span class="wdm-cert-detail-value"><?php echo esc_html( $certificate['recipient']['name'] ); ?></span>
            </div>

            <div class="wdm-cert-detail-item">
                <span class="wdm-cert-detail-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                    </svg>
                </span>
                <span class="wdm-cert-detail-label"><?php echo esc_html( ucfirst( $certificate['source']['type'] ) ); ?></span>
                <span class="wdm-cert-detail-value">
                    <a href="<?php echo esc_url( $certificate['source']['url'] ); ?>" target="_blank">
                        <?php echo esc_html( $certificate['source']['title'] ); ?>
                    </a>
                </span>
            </div>

            <div class="wdm-cert-detail-item">
                <span class="wdm-cert-detail-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                </span>
                <span class="wdm-cert-detail-label"><?php esc_html_e( 'Completed', 'wdm-certificate-customizations' ); ?></span>
                <span class="wdm-cert-detail-value"><?php echo esc_html( $certificate['completion_date_formatted'] ); ?></span>
            </div>

            <div class="wdm-cert-detail-item">
                <span class="wdm-cert-detail-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                </span>
                <span class="wdm-cert-detail-label"><?php esc_html_e( 'Status', 'wdm-certificate-customizations' ); ?></span>
                <span class="wdm-cert-detail-value wdm-cert-status-valid"><?php esc_html_e( 'Valid', 'wdm-certificate-customizations' ); ?></span>
            </div>
        </div>
    </div>

    <!-- Download Buttons (visible to certificate owner) -->
    <?php if ( $is_owner ) : ?>
    <div class="wdm-cert-actions">
        <a href="<?php echo esc_url( $certificate['standard_certificate']['pdf_url'] ); ?>" class="wdm-cert-download-btn wdm-cert-download-standard" target="_blank" download>
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                <polyline points="7 10 12 15 17 10"></polyline>
                <line x1="12" y1="15" x2="12" y2="3"></line>
            </svg>
            <?php esc_html_e( 'Download Standard PDF', 'wdm-certificate-customizations' ); ?>
        </a>

        <?php if ( $has_pocket ) : ?>
        <a href="<?php echo esc_url( $certificate['pocket_certificate']['pdf_url'] ); ?>" class="wdm-cert-download-btn wdm-cert-download-pocket" target="_blank" download>
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                <polyline points="7 10 12 15 17 10"></polyline>
                <line x1="12" y1="15" x2="12" y2="3"></line>
            </svg>
            <?php esc_html_e( 'Download Wallet Card PDF', 'wdm-certificate-customizations' ); ?>
        </a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>
