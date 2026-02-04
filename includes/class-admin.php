<?php
/**
 * Admin class for settings page and course meta
 *
 * @package WDM_Certificate_Customizations
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Admin class
 */
class WDM_Cert_Admin {

    /**
     * Single instance
     *
     * @var WDM_Cert_Admin
     */
    private static $instance = null;

    /**
     * Get single instance
     *
     * @return WDM_Cert_Admin
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
        // Admin menu
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ), 100 );

        // Register settings
        add_action( 'admin_init', array( $this, 'register_settings' ) );

        // Course settings - add pocket certificate field
        add_filter( 'learndash_settings_fields', array( $this, 'add_course_certificate_field' ), 10, 2 );

        // Save course meta via LearnDash settings save mechanism
        add_filter( 'learndash_metabox_save_fields_learndash-course-display-content-settings', array( $this, 'save_pocket_certificate_learndash' ), 30, 3 );
        add_filter( 'learndash_metabox_save_fields_learndash-quiz-display-content-settings', array( $this, 'save_pocket_certificate_learndash' ), 30, 3 );

        // Save course meta (fallback for classic editor)
        add_action( 'save_post_sfwd-courses', array( $this, 'save_course_meta' ), 10, 2 );
        add_action( 'save_post_sfwd-quiz', array( $this, 'save_quiz_meta' ), 10, 2 );

        // Add metabox for classic editor
        add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );

        // AJAX handler for retroactive generation
        add_action( 'wp_ajax_wdm_cert_generate_retroactive', array( $this, 'ajax_generate_retroactive' ) );

        // Admin notices
        add_action( 'admin_notices', array( $this, 'admin_notices' ) );

        // Plugin action links
        add_filter( 'plugin_action_links_' . WDM_CERT_PLUGIN_BASENAME, array( $this, 'plugin_action_links' ) );
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_submenu_page(
            'learndash-lms',
            __( 'Certificate Customizations', 'wdm-certificate-customizations' ),
            __( 'Certificate Customizations', 'wdm-certificate-customizations' ),
            'manage_options',
            'wdm-certificate-settings',
            array( $this, 'render_settings_page' )
        );
    }

    /**
     * Register settings
     */
    public function register_settings() {
        register_setting(
            'wdm_certificate_options_group',
            'wdm_certificate_options',
            array( $this, 'sanitize_options' )
        );

        // General settings section
        add_settings_section(
            'wdm_cert_general_section',
            __( 'General Settings', 'wdm-certificate-customizations' ),
            array( $this, 'render_general_section' ),
            'wdm-certificate-settings'
        );

        // Verification page field
        add_settings_field(
            'verification_page_id',
            __( 'Verification Page', 'wdm-certificate-customizations' ),
            array( $this, 'render_verification_page_field' ),
            'wdm-certificate-settings',
            'wdm_cert_general_section'
        );

        // Enable pocket certificate
        add_settings_field(
            'enable_pocket_certificate',
            __( 'Pocket Size Certificates', 'wdm-certificate-customizations' ),
            array( $this, 'render_enable_pocket_field' ),
            'wdm-certificate-settings',
            'wdm_cert_general_section'
        );

        // QR Code section
        add_settings_section(
            'wdm_cert_qr_section',
            __( 'QR Code Settings', 'wdm-certificate-customizations' ),
            array( $this, 'render_qr_section' ),
            'wdm-certificate-settings'
        );

        // QR code size
        add_settings_field(
            'qr_code_size',
            __( 'QR Code Size', 'wdm-certificate-customizations' ),
            array( $this, 'render_qr_size_field' ),
            'wdm-certificate-settings',
            'wdm_cert_qr_section'
        );

        // Retroactive section
        add_settings_section(
            'wdm_cert_retroactive_section',
            __( 'Retroactive Generation', 'wdm-certificate-customizations' ),
            array( $this, 'render_retroactive_section' ),
            'wdm-certificate-settings'
        );
    }

    /**
     * Sanitize options
     *
     * @param array $input Input values
     * @return array Sanitized values
     */
    public function sanitize_options( $input ) {
        $output = array();

        $output['verification_page_id']      = isset( $input['verification_page_id'] ) ? absint( $input['verification_page_id'] ) : 0;
        $output['enable_pocket_certificate'] = isset( $input['enable_pocket_certificate'] ) ? (bool) $input['enable_pocket_certificate'] : false;
        $output['qr_code_size']              = isset( $input['qr_code_size'] ) ? absint( $input['qr_code_size'] ) : 150;
        $output['certificate_id_prefix']     = isset( $input['certificate_id_prefix'] ) ? sanitize_text_field( $input['certificate_id_prefix'] ) : '';
        $output['custom_css']                = isset( $input['custom_css'] ) ? wp_strip_all_tags( $input['custom_css'] ) : '';

        // Validate QR code size
        if ( $output['qr_code_size'] < 50 ) {
            $output['qr_code_size'] = 50;
        }
        if ( $output['qr_code_size'] > 500 ) {
            $output['qr_code_size'] = 500;
        }

        return $output;
    }

    /**
     * Render settings page
     */
    public function render_settings_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        ?>
        <div class="wrap wdm-cert-settings-wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

            <form action="options.php" method="post">
                <?php
                settings_fields( 'wdm_certificate_options_group' );
                do_settings_sections( 'wdm-certificate-settings' );
                submit_button( __( 'Save Settings', 'wdm-certificate-customizations' ) );
                ?>
            </form>

            <hr />

            <h2><?php esc_html_e( 'Shortcodes Reference', 'wdm-certificate-customizations' ); ?></h2>
            <table class="widefat" style="max-width: 800px;">
                <thead>
                    <tr>
                        <th><?php esc_html_e( 'Shortcode', 'wdm-certificate-customizations' ); ?></th>
                        <th><?php esc_html_e( 'Description', 'wdm-certificate-customizations' ); ?></th>
                        <th><?php esc_html_e( 'Usage', 'wdm-certificate-customizations' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>[wdm_certificate_verify]</code></td>
                        <td><?php esc_html_e( 'Display certificate verification form and results', 'wdm-certificate-customizations' ); ?></td>
                        <td><?php esc_html_e( 'Add to verification page', 'wdm-certificate-customizations' ); ?></td>
                    </tr>
                    <tr>
                        <td><code>[wdm_certificate_qr_code]</code></td>
                        <td><?php esc_html_e( 'Display QR code on certificates', 'wdm-certificate-customizations' ); ?></td>
                        <td><code>[wdm_certificate_qr_code size="150" align="center"]</code></td>
                    </tr>
                    <tr>
                        <td><code>[wdm_certificate_id]</code></td>
                        <td><?php esc_html_e( 'Display Certificate ID on certificates', 'wdm-certificate-customizations' ); ?></td>
                        <td><code>[wdm_certificate_id]</code></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <?php
    }

    /**
     * Render general section
     */
    public function render_general_section() {
        echo '<p>' . esc_html__( 'Configure general certificate verification settings.', 'wdm-certificate-customizations' ) . '</p>';
    }

    /**
     * Render QR section
     */
    public function render_qr_section() {
        echo '<p>' . esc_html__( 'Configure QR code appearance on certificates.', 'wdm-certificate-customizations' ) . '</p>';
    }

    /**
     * Render retroactive section
     */
    public function render_retroactive_section() {
        ?>
        <p><?php esc_html_e( 'Generate Certificate IDs for historical course completions that don\'t have one yet.', 'wdm-certificate-customizations' ); ?></p>
        <p>
            <button type="button" class="button button-secondary" id="wdm-cert-generate-retroactive">
                <?php esc_html_e( 'Generate Certificate IDs for Historical Completions', 'wdm-certificate-customizations' ); ?>
            </button>
            <span id="wdm-cert-retroactive-status" style="margin-left: 10px;"></span>
        </p>
        <p class="description">
            <?php esc_html_e( 'This will scan all course completions and create Certificate IDs for any that are missing. This process may take a while depending on the number of completions.', 'wdm-certificate-customizations' ); ?>
        </p>
        <?php
    }

    /**
     * Render verification page field
     */
    public function render_verification_page_field() {
        $options = get_option( 'wdm_certificate_options', array() );
        $page_id = isset( $options['verification_page_id'] ) ? absint( $options['verification_page_id'] ) : 0;

        $pages = get_pages( array(
            'post_status' => 'publish',
            'sort_order'  => 'ASC',
            'sort_column' => 'post_title',
        ) );
        ?>
        <select name="wdm_certificate_options[verification_page_id]" id="wdm_cert_verification_page">
            <option value=""><?php esc_html_e( '-- Select Page --', 'wdm-certificate-customizations' ); ?></option>
            <?php foreach ( $pages as $page ) : ?>
                <option value="<?php echo esc_attr( $page->ID ); ?>" <?php selected( $page_id, $page->ID ); ?>>
                    <?php echo esc_html( $page->post_title ); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <p class="description">
            <?php esc_html_e( 'Select the page that contains the [wdm_certificate_verify] shortcode.', 'wdm-certificate-customizations' ); ?>
            <?php if ( $page_id ) : ?>
                <a href="<?php echo esc_url( get_permalink( $page_id ) ); ?>" target="_blank"><?php esc_html_e( 'View Page', 'wdm-certificate-customizations' ); ?></a>
            <?php endif; ?>
        </p>
        <?php
    }

    /**
     * Render enable pocket certificate field
     */
    public function render_enable_pocket_field() {
        $options = get_option( 'wdm_certificate_options', array() );
        $enabled = isset( $options['enable_pocket_certificate'] ) ? (bool) $options['enable_pocket_certificate'] : true;
        ?>
        <label>
            <input type="checkbox" name="wdm_certificate_options[enable_pocket_certificate]" value="1" <?php checked( $enabled ); ?> />
            <?php esc_html_e( 'Enable Pocket Size Certificates', 'wdm-certificate-customizations' ); ?>
        </label>
        <p class="description">
            <?php esc_html_e( 'Allow courses to have a second (pocket size) certificate in addition to the standard certificate.', 'wdm-certificate-customizations' ); ?>
        </p>
        <?php
    }

    /**
     * Render QR code size field
     */
    public function render_qr_size_field() {
        $options = get_option( 'wdm_certificate_options', array() );
        $size = isset( $options['qr_code_size'] ) ? absint( $options['qr_code_size'] ) : 150;
        ?>
        <input type="number" name="wdm_certificate_options[qr_code_size]" value="<?php echo esc_attr( $size ); ?>" min="50" max="500" step="10" style="width: 80px;" />
        <span><?php esc_html_e( 'pixels', 'wdm-certificate-customizations' ); ?></span>
        <p class="description">
            <?php esc_html_e( 'Default size for QR codes on certificates (50-500 pixels).', 'wdm-certificate-customizations' ); ?>
        </p>
        <?php
    }

    /**
     * Add pocket certificate field to course settings
     *
     * @param array $fields Settings fields
     * @param string $metabox_key Metabox key
     * @return array Modified fields
     */
    public function add_course_certificate_field( $fields, $metabox_key ) {
        // Check if pocket certificates are enabled
        $options = get_option( 'wdm_certificate_options', array() );
        $enabled = isset( $options['enable_pocket_certificate'] ) ? (bool) $options['enable_pocket_certificate'] : true;

        if ( ! $enabled ) {
            return $fields;
        }

        // Only add to course certificate settings
        if ( $metabox_key !== 'learndash-course-display-content-settings' ) {
            return $fields;
        }

        // Get certificate options
        $certificate_options = $this->get_certificate_options();

        // Get current post ID
        $post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;
        if ( ! $post_id ) {
            global $post;
            $post_id = $post ? $post->ID : 0;
        }

        $current_value = $post_id ? get_post_meta( $post_id, '_wdm_pocket_certificate', true ) : '';

        // Add pocket certificate field after the main certificate field
        $new_fields = array();
        foreach ( $fields as $key => $field ) {
            $new_fields[ $key ] = $field;

            // Add our field after the certificate field
            if ( $key === 'certificate' ) {
                $new_fields['wdm_pocket_certificate'] = array(
                    'name'      => 'wdm_pocket_certificate',
                    'type'      => 'select',
                    'label'     => __( 'Pocket Size Certificate', 'wdm-certificate-customizations' ),
                    'help_text' => __( 'Optional compact certificate. Shares the same Certificate ID and QR code as the standard certificate.', 'wdm-certificate-customizations' ),
                    'value'     => $current_value,
                    'options'   => $certificate_options,
                    'default'   => '',
                );
            }
        }

        return $new_fields;
    }

    /**
     * Add meta boxes for classic editor
     */
    public function add_meta_boxes() {
        $options = get_option( 'wdm_certificate_options', array() );
        $enabled = isset( $options['enable_pocket_certificate'] ) ? (bool) $options['enable_pocket_certificate'] : true;

        if ( ! $enabled ) {
            return;
        }

        // Add to courses
        add_meta_box(
            'wdm_pocket_certificate_metabox',
            __( 'Pocket Size Certificate', 'wdm-certificate-customizations' ),
            array( $this, 'render_pocket_certificate_metabox' ),
            'sfwd-courses',
            'side',
            'default'
        );

        // Add to quizzes
        add_meta_box(
            'wdm_pocket_certificate_metabox',
            __( 'Pocket Size Certificate', 'wdm-certificate-customizations' ),
            array( $this, 'render_pocket_certificate_metabox' ),
            'sfwd-quiz',
            'side',
            'default'
        );
    }

    /**
     * Render pocket certificate metabox
     *
     * @param WP_Post $post Current post
     */
    public function render_pocket_certificate_metabox( $post ) {
        $current_value = get_post_meta( $post->ID, '_wdm_pocket_certificate', true );
        $certificate_options = $this->get_certificate_options();

        wp_nonce_field( 'wdm_pocket_certificate_nonce', 'wdm_pocket_certificate_nonce' );
        ?>
        <p>
            <select name="wdm_pocket_certificate" id="wdm_pocket_certificate" style="width: 100%;">
                <?php foreach ( $certificate_options as $value => $label ) : ?>
                    <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $current_value, $value ); ?>>
                        <?php echo esc_html( $label ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>
        <p class="description">
            <?php esc_html_e( 'Optional compact certificate that shares the same verification ID.', 'wdm-certificate-customizations' ); ?>
        </p>
        <?php
    }

    /**
     * Get certificate options for dropdown
     *
     * @return array Certificate options
     */
    private function get_certificate_options() {
        $options = array(
            '' => __( '-- None --', 'wdm-certificate-customizations' ),
        );

        $certificates = get_posts( array(
            'post_type'      => 'sfwd-certificates',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => 'title',
            'order'          => 'ASC',
        ) );

        foreach ( $certificates as $cert ) {
            $options[ $cert->ID ] = $cert->post_title;
        }

        return $options;
    }

    /**
     * Save pocket certificate via LearnDash metabox save mechanism
     *
     * @param array $settings_field_updates Settings being saved
     * @param string $settings_metabox_key Metabox key
     * @param string $settings_screen_id Screen ID (post type)
     * @return array Modified settings
     */
    public function save_pocket_certificate_learndash( $settings_field_updates, $settings_metabox_key, $settings_screen_id ) {
        global $post;

        if ( ! $post || ! isset( $post->ID ) ) {
            return $settings_field_updates;
        }

        $post_id = $post->ID;

        // Get the pocket certificate value directly from $_POST
        // LearnDash filters out custom fields before this hook, so we must read from $_POST
        $pocket_cert = '';

        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified by LearnDash
        if ( isset( $_POST[ $settings_metabox_key ]['wdm_pocket_certificate'] ) ) {
            // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
            $pocket_cert = sanitize_text_field( wp_unslash( $_POST[ $settings_metabox_key ]['wdm_pocket_certificate'] ) );
        }

        // Save or delete meta
        if ( ! empty( $pocket_cert ) ) {
            update_post_meta( $post_id, '_wdm_pocket_certificate', absint( $pocket_cert ) );
        } else {
            delete_post_meta( $post_id, '_wdm_pocket_certificate' );
        }

        return $settings_field_updates;
    }

    /**
     * Save course meta
     *
     * @param int $post_id Post ID
     * @param WP_Post $post Post object
     */
    public function save_course_meta( $post_id, $post ) {
        $this->save_pocket_certificate_meta( $post_id );
    }

    /**
     * Save quiz meta
     *
     * @param int $post_id Post ID
     * @param WP_Post $post Post object
     */
    public function save_quiz_meta( $post_id, $post ) {
        $this->save_pocket_certificate_meta( $post_id );
    }

    /**
     * Save pocket certificate meta
     *
     * @param int $post_id Post ID
     */
    private function save_pocket_certificate_meta( $post_id ) {
        // Check nonce
        if ( ! isset( $_POST['wdm_pocket_certificate_nonce'] ) ||
             ! wp_verify_nonce( $_POST['wdm_pocket_certificate_nonce'], 'wdm_pocket_certificate_nonce' ) ) {
            // Also check for LearnDash settings save
            if ( ! isset( $_POST['wdm_pocket_certificate'] ) && ! isset( $_POST['learndash-course-display-content-settings']['wdm_pocket_certificate'] ) ) {
                return;
            }
        }

        // Check permissions
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        // Check for autosave
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        // Get value from either source
        $pocket_cert = '';
        if ( isset( $_POST['wdm_pocket_certificate'] ) ) {
            $pocket_cert = sanitize_text_field( $_POST['wdm_pocket_certificate'] );
        } elseif ( isset( $_POST['learndash-course-display-content-settings']['wdm_pocket_certificate'] ) ) {
            $pocket_cert = sanitize_text_field( $_POST['learndash-course-display-content-settings']['wdm_pocket_certificate'] );
        }

        // Save or delete meta
        if ( ! empty( $pocket_cert ) ) {
            update_post_meta( $post_id, '_wdm_pocket_certificate', absint( $pocket_cert ) );
        } else {
            delete_post_meta( $post_id, '_wdm_pocket_certificate' );
        }
    }

    /**
     * AJAX handler for retroactive certificate ID generation
     */
    public function ajax_generate_retroactive() {
        // Verify nonce
        check_ajax_referer( 'wdm_cert_admin', 'nonce' );

        // Check permissions
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'wdm-certificate-customizations' ) ) );
        }

        // Get upgrade handler
        $upgrade = WDM_Cert_Upgrade::get_instance();
        $result = $upgrade->generate_retroactive_certificate_ids();

        wp_send_json_success( array(
            'message' => sprintf(
                /* translators: %d: number of certificates generated */
                __( 'Generated Certificate IDs for %d completions.', 'wdm-certificate-customizations' ),
                $result['count']
            ),
            'count' => $result['count'],
        ) );
    }

    /**
     * Admin notices
     */
    public function admin_notices() {
        $options = get_option( 'wdm_certificate_options', array() );
        $page_id = isset( $options['verification_page_id'] ) ? absint( $options['verification_page_id'] ) : 0;

        // Check if verification page is set
        if ( ! $page_id ) {
            $screen = get_current_screen();
            if ( $screen && $screen->id !== 'learndash-lms_page_wdm-certificate-settings' ) {
                ?>
                <div class="notice notice-warning">
                    <p>
                        <?php
                        printf(
                            /* translators: %s: settings page link */
                            esc_html__( 'WDM Certificate Customizations: Please configure the %s to enable certificate verification.', 'wdm-certificate-customizations' ),
                            '<a href="' . esc_url( admin_url( 'admin.php?page=wdm-certificate-settings' ) ) . '">' . esc_html__( 'verification page', 'wdm-certificate-customizations' ) . '</a>'
                        );
                        ?>
                    </p>
                </div>
                <?php
            }
        }
    }

    /**
     * Plugin action links
     *
     * @param array $links Existing links
     * @return array Modified links
     */
    public function plugin_action_links( $links ) {
        $settings_link = '<a href="' . esc_url( admin_url( 'admin.php?page=wdm-certificate-settings' ) ) . '">' . esc_html__( 'Settings', 'wdm-certificate-customizations' ) . '</a>';
        array_unshift( $links, $settings_link );
        return $links;
    }
}
