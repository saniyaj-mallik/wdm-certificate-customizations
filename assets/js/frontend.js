/**
 * WDM Certificate Customizations - Frontend JavaScript
 *
 * @package WDM_Certificate_Customizations
 */

(function($) {
    'use strict';

    /**
     * Certificate Verification Handler
     */
    var WDMCertVerification = {
        /**
         * Initialize
         */
        init: function() {
            this.bindEvents();
            this.initTabSwitcher();
        },

        /**
         * Bind events
         */
        bindEvents: function() {
            var self = this;

            // Form submission
            $(document).on('submit', '#wdm-cert-search-form', function(e) {
                e.preventDefault();
                self.handleFormSubmit($(this));
            });

            // Tab switching
            $(document).on('click', '.wdm-cert-tab', function() {
                self.switchTab($(this));
            });
        },

        /**
         * Handle form submission
         *
         * @param {jQuery} $form Form element
         */
        handleFormSubmit: function($form) {
            var self = this;
            var $input = $form.find('.wdm-cert-id-input');
            var $btn = $form.find('.wdm-cert-verify-btn');
            var $btnText = $btn.find('.wdm-cert-btn-text');
            var $btnLoading = $btn.find('.wdm-cert-btn-loading');
            var $result = $('#wdm-cert-result');
            var certId = $input.val().trim().toUpperCase();

            if (!certId) {
                this.showMessage($result, wdmCertVars.strings.error || 'Please enter a Certificate ID.', 'error');
                return;
            }

            // Show loading state
            $btn.prop('disabled', true);
            $btnText.hide();
            $btnLoading.show();

            // Clear previous results
            $result.empty();

            // Make AJAX request
            $.ajax({
                url: wdmCertVars.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'wdm_cert_verify',
                    nonce: wdmCertVars.nonce,
                    cert_id: certId
                },
                success: function(response) {
                    if (response.success) {
                        $result.html(response.data.html);
                        self.initTabSwitcher();

                        // Update URL without reload
                        if (window.history && window.history.pushState) {
                            var url = window.location.href.split('?')[0];
                            url = url.replace(/\/[A-F0-9_-]+\/?$/i, '/');
                            url = url.replace(/\/$/, '') + '/' + certId + '/';
                            window.history.pushState({ certId: certId }, '', url);
                        }
                    } else {
                        self.showMessage($result, response.data.message || wdmCertVars.strings.error, 'error');
                    }
                },
                error: function() {
                    self.showMessage($result, wdmCertVars.strings.error || 'An error occurred.', 'error');
                },
                complete: function() {
                    // Reset button state
                    $btn.prop('disabled', false);
                    $btnText.show();
                    $btnLoading.hide();
                }
            });
        },

        /**
         * Show message in result container
         *
         * @param {jQuery} $container Container element
         * @param {string} message Message to show
         * @param {string} type Message type (error, success)
         */
        showMessage: function($container, message, type) {
            var verificationUrl = wdmCertVars.verificationUrl || window.location.href.split('?')[0];
            var html = '<div class="wdm-cert-error-container">' +
                '<div class="wdm-cert-error-icon">' +
                '<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' +
                '<circle cx="12" cy="12" r="10"></circle>' +
                '<line x1="15" y1="9" x2="9" y2="15"></line>' +
                '<line x1="9" y1="9" x2="15" y2="15"></line>' +
                '</svg>' +
                '</div>' +
                '<h3 class="wdm-cert-error-title">Certificate Not Found</h3>' +
                '<p class="wdm-cert-error-message">' + this.escapeHtml(message) + '</p>' +
                '<div class="wdm-cert-error-reasons">' +
                '<p class="wdm-cert-error-reasons-title">Possible reasons:</p>' +
                '<ul>' +
                '<li>The Certificate ID was entered incorrectly</li>' +
                '<li>The certificate does not exist</li>' +
                '<li>The certificate has been revoked</li>' +
                '<li>The course/quiz associated with this certificate has been removed</li>' +
                '</ul>' +
                '</div>' +
                '<div class="wdm-cert-error-actions">' +
                '<a href="' + this.escapeHtml(verificationUrl) + '" class="wdm-cert-try-again-btn">Try Another Certificate</a>' +
                '</div>' +
                '</div>';

            $container.html(html);
        },

        /**
         * Initialize tab switcher
         */
        initTabSwitcher: function() {
            var $tabs = $('.wdm-cert-tabs');
            if ($tabs.length === 0) {
                return;
            }

            // Set initial state based on URL parameter
            var urlParams = new URLSearchParams(window.location.search);
            var view = urlParams.get('view');
            if (view === 'pocket') {
                this.switchTab($('.wdm-cert-tab[data-view="pocket"]'));
            }
        },

        /**
         * Switch certificate tab
         *
         * @param {jQuery} $tab Tab element
         */
        switchTab: function($tab) {
            var view = $tab.data('view');
            var $container = $tab.closest('.wdm-cert-verified-container');

            // Update active tab
            $container.find('.wdm-cert-tab').removeClass('active');
            $tab.addClass('active');

            // Show/hide previews
            $container.find('.wdm-cert-preview').hide();
            $container.find('.wdm-cert-preview-' + view).show();

            // Update URL parameter
            if (window.history && window.history.replaceState) {
                var url = new URL(window.location.href);
                url.searchParams.set('view', view);
                window.history.replaceState({}, '', url.toString());
            }
        },

        /**
         * Escape HTML entities
         *
         * @param {string} text Text to escape
         * @return {string} Escaped text
         */
        escapeHtml: function(text) {
            var div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        WDMCertVerification.init();
    });

})(jQuery);
