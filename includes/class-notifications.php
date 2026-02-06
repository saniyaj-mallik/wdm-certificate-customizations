<?php
/**
 * Notifications class for sending email on certificate generation
 *
 * @package WDM_Certificate_Customizations
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Notifications class
 */
class WDM_Cert_Notifications {

    /**
     * Single instance
     *
     * @var WDM_Cert_Notifications
     */
    private static $instance = null;

    /**
     * Get single instance
     *
     * @return WDM_Cert_Notifications
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
        add_action( 'wdm_certificate_record_generated', array( $this, 'send_certificate_email' ), 10, 2 );
    }

    /**
     * Get plugin email settings
     *
     * @return array Email settings with defaults.
     */
    private function get_email_settings() {
        $options  = get_option( 'wdm_certificate_options', array() );
        $defaults = array(
            'enable_email_notifications' => false,
            'email_send_to_admin'        => false,
            'email_send_to_group_leader' => false,
            'email_cc'                   => '',
            'email_user_subject'         => '',
            'email_admin_subject'        => '',
            'email_body'                 => '',
        );

        return wp_parse_args( $options, $defaults );
    }

    /**
     * Replace variable placeholders in a string
     *
     * @param string  $text      Text with placeholders.
     * @param WP_User $user      User object.
     * @param string  $course_name Course/source title.
     * @param string  $group_names Comma-separated group names.
     * @return string Text with placeholders replaced.
     */
    private function replace_variables( $text, $user, $course_name, $group_names = '' ) {
        $replacements = array(
            '%User%'            => $user->display_name,
            '%User First Name%' => $user->first_name,
            '%User Last Name%'  => $user->last_name,
            '%User Email%'      => $user->user_email,
            '%Course Name%'     => $course_name,
            '%Group Name%'      => $group_names,
        );

        /**
         * Filter the email variable replacements
         *
         * @param array   $replacements Key-value pairs of placeholders and values.
         * @param WP_User $user         User object.
         * @param string  $course_name  Source title.
         */
        $replacements = apply_filters( 'wdm_cert_email_variables', $replacements, $user, $course_name );

        return str_ireplace( array_keys( $replacements ), array_values( $replacements ), $text );
    }

    /**
     * Get group names for a user
     *
     * @param int $user_id User ID.
     * @return string Comma-separated group names.
     */
    private function get_user_group_names( $user_id ) {
        if ( ! function_exists( 'learndash_get_users_group_ids' ) ) {
            return '';
        }

        $group_ids = learndash_get_users_group_ids( $user_id, true );
        if ( empty( $group_ids ) ) {
            return '';
        }

        $names = array();
        foreach ( $group_ids as $group_id ) {
            $names[] = get_the_title( $group_id );
        }

        return implode( ', ', $names );
    }

    /**
     * Get group leaders for a user in a specific course context
     *
     * @param int $user_id   User ID.
     * @param int $source_id Course/source ID.
     * @return array Array of group leader email addresses.
     */
    private function get_group_leader_emails( $user_id, $source_id ) {
        $leader_emails = array();

        if ( ! function_exists( 'learndash_get_course_groups' ) ||
             ! function_exists( 'learndash_get_users_group_ids' ) ||
             ! function_exists( 'learndash_get_groups_administrators' ) ) {
            return $leader_emails;
        }

        $course_groups = learndash_get_course_groups( $source_id, true );
        $user_groups   = learndash_get_users_group_ids( $user_id, true );

        if ( empty( $course_groups ) || empty( $user_groups ) ) {
            return $leader_emails;
        }

        $common_groups = array_intersect( $course_groups, $user_groups );

        foreach ( $common_groups as $group_id ) {
            $leaders = learndash_get_groups_administrators( $group_id, true );
            if ( empty( $leaders ) ) {
                continue;
            }

            foreach ( $leaders as $leader ) {
                if ( function_exists( 'learndash_is_group_leader_of_user' ) &&
                     learndash_is_group_leader_of_user( $leader->ID, $user_id ) ) {
                    $leader_emails[] = $leader->user_email;
                }
            }
        }

        return array_unique( $leader_emails );
    }

    /**
     * Send certificate notification email to the user and configured recipients
     *
     * @param array  $record Certificate record data.
     * @param string $csuid  Certificate Secure Unique ID.
     */
    public function send_certificate_email( $record, $csuid ) {
        // Skip retroactive records to avoid mass emails
        if ( ! empty( $record['is_retroactive'] ) ) {
            return;
        }

        if ( empty( $record['user_id'] ) || empty( $record['source_id'] ) || empty( $record['source_type'] ) ) {
            return;
        }

        // Check if notifications are enabled
        $settings = $this->get_email_settings();
        if ( empty( $settings['enable_email_notifications'] ) ) {
            return;
        }

        $user = get_user_by( 'ID', $record['user_id'] );
        if ( ! $user ) {
            return;
        }

        // Get source post (course/quiz/group)
        $source_post = get_post( $record['source_id'] );
        if ( ! $source_post ) {
            return;
        }

        $source_type_label = ucfirst( $record['source_type'] );
        $source_title      = $source_post->post_title;
        $verification_url  = WDM_Cert_Helper::get_verification_url( $csuid );
        $site_name         = get_bloginfo( 'name' );
        $group_names       = $this->get_user_group_names( $user->ID );

        // Get configured subjects and body, with defaults
        $user_subject  = ! empty( $settings['email_user_subject'] ) ? $settings['email_user_subject'] : __( 'You earned a certificate', 'wdm-certificate-customizations' );
        $admin_subject = ! empty( $settings['email_admin_subject'] ) ? $settings['email_admin_subject'] : __( '%User% has earned a course certificate', 'wdm-certificate-customizations' );
        $email_body    = ! empty( $settings['email_body'] ) ? $settings['email_body'] : __( '%User% has earned a course certificate for completing %Course Name%.', 'wdm-certificate-customizations' );

        // Replace variables in user subject (replace %User% with "You" for user email)
        $user_subject_resolved = str_ireplace( '%User%', 'You', $user_subject );
        $user_subject_resolved = $this->replace_variables( $user_subject_resolved, $user, $source_title, $group_names );

        // Replace variables in admin subject and body
        $admin_subject_resolved = $this->replace_variables( $admin_subject, $user, $source_title, $group_names );
        $email_body_resolved    = $this->replace_variables( $email_body, $user, $source_title, $group_names );

        /**
         * Filter the certificate notification email subject (user)
         *
         * @param string $subject Email subject.
         * @param array  $record  Certificate record.
         * @param string $csuid   Certificate ID.
         */
        $user_subject_resolved = apply_filters( 'wdm_cert_notification_subject', $user_subject_resolved, $record, $csuid );

        // Build HTML email body with the resolved body text
        $message = $this->build_email_body( $user->display_name, $source_type_label, $source_title, $csuid, $verification_url, $site_name, $email_body_resolved );

        /**
         * Filter the certificate notification email body
         *
         * @param string $message Email body.
         * @param array  $record  Certificate record.
         * @param string $csuid   Certificate ID.
         */
        $message = apply_filters( 'wdm_cert_notification_message', $message, $record, $csuid );

        $headers = array( 'Content-Type: text/html; charset=UTF-8' );

        /**
         * Filter the certificate notification email headers
         *
         * @param array  $headers Email headers.
         * @param array  $record  Certificate record.
         * @param string $csuid   Certificate ID.
         */
        $headers = apply_filters( 'wdm_cert_notification_headers', $headers, $record, $csuid );

        // Send to user
        wp_mail( $user->user_email, $user_subject_resolved, $message, $headers );

        /**
         * Action fired after certificate notification email is sent to user
         *
         * @param array  $record Certificate record.
         * @param string $csuid  Certificate ID.
         * @param string $email  Recipient email.
         */
        do_action( 'wdm_cert_notification_sent', $record, $csuid, $user->user_email );

        // Build admin message (same HTML template but with admin subject)
        $admin_message = $this->build_email_body( $user->display_name, $source_type_label, $source_title, $csuid, $verification_url, $site_name, $email_body_resolved );

        /** This filter is documented above */
        $admin_message = apply_filters( 'wdm_cert_notification_message', $admin_message, $record, $csuid );

        // Send to site admin
        if ( ! empty( $settings['email_send_to_admin'] ) ) {
            wp_mail( get_bloginfo( 'admin_email' ), $admin_subject_resolved, $admin_message, $headers );
        }

        // Send to group leaders
        if ( ! empty( $settings['email_send_to_group_leader'] ) ) {
            $leader_emails = $this->get_group_leader_emails( $user->ID, $record['source_id'] );
            if ( ! empty( $leader_emails ) ) {
                // For group leaders, replace %Group Name% per-group if possible
                foreach ( $leader_emails as $leader_email ) {
                    wp_mail( $leader_email, $admin_subject_resolved, $admin_message, $headers );
                }
            }
        }

        // Send to CC addresses
        if ( ! empty( $settings['email_cc'] ) ) {
            $cc_emails = array_map( 'trim', explode( ',', $settings['email_cc'] ) );
            $cc_emails = array_filter( $cc_emails, 'is_email' );
            if ( ! empty( $cc_emails ) ) {
                wp_mail( $cc_emails, $admin_subject_resolved, $admin_message, $headers );
            }
        }
    }

    /**
     * Build the email body HTML
     *
     * @param string $user_name        User display name.
     * @param string $source_type_label Source type label (Course, Quiz, Group).
     * @param string $source_title     Source title.
     * @param string $csuid            Certificate ID.
     * @param string $verification_url Verification page URL.
     * @param string $site_name        Site name.
     * @param string $custom_body      Custom body text from settings (already with variables replaced).
     * @return string Email body HTML.
     */
    private function build_email_body( $user_name, $source_type_label, $source_title, $csuid, $verification_url, $site_name, $custom_body = '' ) {
        ob_start();
        ?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background-color: #f5f5f5;">
<table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f5f5f5; padding: 20px 0;">
<tr>
<td align="center">
<table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; max-width: 600px;">
    <!-- Header -->
    <tr>
        <td style="background-color: #2563eb; padding: 30px 40px; text-align: center;">
            <h1 style="margin: 0; color: #ffffff; font-size: 24px; font-weight: 600;">
                <?php echo esc_html__( 'Certificate Awarded', 'wdm-certificate-customizations' ); ?>
            </h1>
        </td>
    </tr>
    <!-- Body -->
    <tr>
        <td style="padding: 40px;">
            <p style="margin: 0 0 20px; font-size: 16px; color: #333333; line-height: 1.5;">
                <?php
                /* translators: %s: user display name */
                printf( esc_html__( 'Hi %s,', 'wdm-certificate-customizations' ), esc_html( $user_name ) );
                ?>
            </p>
            <p style="margin: 0 0 20px; font-size: 16px; color: #333333; line-height: 1.5;">
                <?php
                if ( ! empty( $custom_body ) ) {
                    echo esc_html( $custom_body );
                } else {
                    /* translators: 1: source type (Course, Quiz, Group), 2: source title */
                    printf(
                        esc_html__( 'Congratulations! You have successfully completed the %1$s "%2$s" and your certificate has been generated.', 'wdm-certificate-customizations' ),
                        esc_html( strtolower( $source_type_label ) ),
                        '<strong>' . esc_html( $source_title ) . '</strong>'
                    );
                }
                ?>
            </p>
            <!-- Certificate ID Box -->
            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 25px 0;">
            <tr>
                <td style="background-color: #f0f7ff; border: 1px solid #d0e3ff; border-radius: 6px; padding: 20px; text-align: center;">
                    <p style="margin: 0 0 5px; font-size: 13px; color: #666666; text-transform: uppercase; letter-spacing: 1px;">
                        <?php echo esc_html__( 'Certificate ID', 'wdm-certificate-customizations' ); ?>
                    </p>
                    <p style="margin: 0; font-size: 22px; color: #2563eb; font-weight: 700; font-family: monospace;">
                        <?php echo esc_html( $csuid ); ?>
                    </p>
                </td>
            </tr>
            </table>
            <p style="margin: 0 0 25px; font-size: 16px; color: #333333; line-height: 1.5;">
                <?php echo esc_html__( 'You can view and download your certificate using the link below:', 'wdm-certificate-customizations' ); ?>
            </p>
            <!-- CTA Button -->
            <table width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td align="center">
                    <a href="<?php echo esc_url( $verification_url ); ?>" style="display: inline-block; background-color: #2563eb; color: #ffffff; text-decoration: none; padding: 14px 30px; border-radius: 6px; font-size: 16px; font-weight: 600;">
                        <?php echo esc_html__( 'View Certificate', 'wdm-certificate-customizations' ); ?>
                    </a>
                </td>
            </tr>
            </table>
        </td>
    </tr>
    <!-- Footer -->
    <tr>
        <td style="background-color: #f9fafb; padding: 20px 40px; border-top: 1px solid #e5e7eb;">
            <p style="margin: 0; font-size: 13px; color: #888888; text-align: center;">
                <?php
                /* translators: %s: site name */
                printf( esc_html__( 'This email was sent by %s', 'wdm-certificate-customizations' ), esc_html( $site_name ) );
                ?>
            </p>
        </td>
    </tr>
</table>
</td>
</tr>
</table>
</body>
</html>
        <?php
        return ob_get_clean();
    }
}
