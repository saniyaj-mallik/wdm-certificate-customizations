/**
 * WDM Certificate Customizations - Admin JavaScript
 *
 * @package WDM_Certificate_Customizations
 */

(function($) {
    'use strict';

    /**
     * Admin Handler
     */
    var WDMCertAdmin = {
        /**
         * Initialize
         */
        init: function() {
            this.bindEvents();
        },

        /**
         * Bind events
         */
        bindEvents: function() {
            var self = this;

            // Retroactive generation button
            $('#wdm-cert-generate-retroactive').on('click', function(e) {
                e.preventDefault();
                self.handleRetroactiveGeneration($(this));
            });
        },

        /**
         * Handle retroactive generation
         *
         * @param {jQuery} $button Button element
         */
        handleRetroactiveGeneration: function($button) {
            var self = this;
            var $status = $('#wdm-cert-retroactive-status');

            // Confirm action
            if (!confirm('This will generate Certificate IDs for all historical course completions. This may take a while. Continue?')) {
                return;
            }

            // Show loading state
            $button.prop('disabled', true);
            $status.removeClass('success error').addClass('loading').text(wdmCertAdmin.strings.generating || 'Generating Certificate IDs...');

            // Make AJAX request
            $.ajax({
                url: wdmCertAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'wdm_cert_generate_retroactive',
                    nonce: wdmCertAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $status.removeClass('loading').addClass('success').text(response.data.message);
                    } else {
                        $status.removeClass('loading').addClass('error').text(response.data.message || wdmCertAdmin.strings.error);
                    }
                },
                error: function() {
                    $status.removeClass('loading').addClass('error').text(wdmCertAdmin.strings.error || 'An error occurred.');
                },
                complete: function() {
                    $button.prop('disabled', false);
                }
            });
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        WDMCertAdmin.init();
    });

})(jQuery);
