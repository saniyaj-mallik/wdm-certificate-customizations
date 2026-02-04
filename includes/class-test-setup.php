<?php
/**
 * E2E Test Data Setup Class
 *
 * Provides functionality to set up test data for E2E tests.
 * Access via: /wp-admin/admin.php?page=wdm-test-setup
 *
 * @package WDM_Certificate_Customizations
 */

if (!defined('ABSPATH')) {
    exit;
}

class WDM_Test_Setup {

    /**
     * Initialize the test setup
     */
    public static function init() {
        add_action('admin_menu', array(__CLASS__, 'add_admin_menu'));
        add_action('admin_init', array(__CLASS__, 'handle_setup'));
    }

    /**
     * Add admin menu page
     */
    public static function add_admin_menu() {
        add_submenu_page(
            'tools.php',
            'E2E Test Setup',
            'E2E Test Setup',
            'manage_options',
            'wdm-test-setup',
            array(__CLASS__, 'render_page')
        );
    }

    /**
     * Handle setup action
     */
    public static function handle_setup() {
        if (!isset($_GET['page']) || $_GET['page'] !== 'wdm-test-setup') {
            return;
        }

        if (!isset($_GET['action']) || $_GET['action'] !== 'setup') {
            return;
        }

        if (!wp_verify_nonce($_GET['_wpnonce'], 'wdm_test_setup')) {
            wp_die('Security check failed');
        }

        $result = self::setup_test_data();

        // Store result for display
        set_transient('wdm_test_setup_result', $result, 60);

        // Redirect back to page
        wp_redirect(admin_url('tools.php?page=wdm-test-setup&completed=1'));
        exit;
    }

    /**
     * Render admin page
     */
    public static function render_page() {
        $result = get_transient('wdm_test_setup_result');
        delete_transient('wdm_test_setup_result');
        ?>
        <div class="wrap">
            <h1>E2E Test Data Setup</h1>

            <?php if ($result): ?>
                <div class="notice notice-success">
                    <p><strong>Test data setup complete!</strong></p>
                </div>

                <h2>Test Data IDs (copy to .env)</h2>
                <textarea rows="20" cols="80" readonly style="font-family: monospace;"><?php echo esc_textarea($result['env_content']); ?></textarea>

                <h2>Setup Log</h2>
                <pre style="background: #f1f1f1; padding: 15px; overflow: auto; max-height: 400px;"><?php echo esc_html($result['log']); ?></pre>
            <?php else: ?>
                <p>This tool will create all necessary test data for running E2E tests:</p>
                <ul>
                    <li>Test users (student, student2)</li>
                    <li>Certificate templates</li>
                    <li>Test courses with certificates</li>
                    <li>Test quiz</li>
                    <li>Certificate records for test student</li>
                    <li>Plugin configuration</li>
                </ul>

                <p><strong>Warning:</strong> This will create test data in your database. Only run this on a development/testing environment.</p>

                <p>
                    <a href="<?php echo wp_nonce_url(admin_url('tools.php?page=wdm-test-setup&action=setup'), 'wdm_test_setup'); ?>" class="button button-primary button-large">
                        Run Test Data Setup
                    </a>
                </p>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Set up all test data
     *
     * @return array Result with env_content and log
     */
    public static function setup_test_data() {
        $log = "";
        $log .= "=== WDM Certificate Customizations E2E Test Setup ===\n\n";

        // 1. Ensure plugin is active
        $log .= "1. Checking plugin activation...\n";
        $plugin_file = 'wdm-certificate-customizations/wdm-certificate-customizations.php';
        if (!is_plugin_active($plugin_file)) {
            activate_plugin($plugin_file);
            $log .= "   Plugin activated.\n";
        } else {
            $log .= "   Plugin already active.\n";
        }

        // 2. Create test users
        $log .= "\n2. Creating test users...\n";

        // Student 1
        $student1_username = 'saniyaj';
        $student1 = get_user_by('login', $student1_username);
        if (!$student1) {
            $student1_id = wp_create_user($student1_username, 'saniyaj', 'saniyaj@test.local');
            if (!is_wp_error($student1_id)) {
                $student1 = get_user_by('ID', $student1_id);
                $student1->set_role('subscriber');
                $log .= "   Created student1: $student1_username (ID: $student1_id)\n";
            } else {
                $log .= "   ERROR creating student1: " . $student1_id->get_error_message() . "\n";
                $student1_id = 0;
            }
        } else {
            $student1_id = $student1->ID;
            $log .= "   Student1 exists: $student1_username (ID: $student1_id)\n";
        }

        // Student 2
        $student2_username = 'test_student_2';
        $student2 = get_user_by('login', $student2_username);
        if (!$student2) {
            $student2_id = wp_create_user($student2_username, 'test_student_password', 'student2@test.local');
            if (!is_wp_error($student2_id)) {
                $student2 = get_user_by('ID', $student2_id);
                $student2->set_role('subscriber');
                $log .= "   Created student2: $student2_username (ID: $student2_id)\n";
            } else {
                $log .= "   ERROR creating student2\n";
                $student2_id = 0;
            }
        } else {
            $student2_id = $student2->ID;
            $log .= "   Student2 exists: $student2_username (ID: $student2_id)\n";
        }

        // 3. Create certificate templates
        $log .= "\n3. Creating certificate templates...\n";

        // Standard certificate
        $existing_cert = get_posts(array(
            'post_type' => 'sfwd-certificates',
            'title' => 'E2E Test Certificate - Standard',
            'numberposts' => 1,
        ));

        if (!empty($existing_cert)) {
            $standard_cert_id = $existing_cert[0]->ID;
            $log .= "   Standard certificate exists (ID: $standard_cert_id)\n";
        } else {
            $standard_cert_id = wp_insert_post(array(
                'post_title'   => 'E2E Test Certificate - Standard',
                'post_content' => '<div style="text-align:center;padding:50px;border:2px solid #333;">
                    <h1>Certificate of Completion</h1>
                    <p>This certifies that</p>
                    <h2>[usermeta field="display_name"]</h2>
                    <p>has successfully completed</p>
                    <h2>[courseinfo show="course_title"]</h2>
                    <p>Certificate ID: [wdm_certificate_id]</p>
                    [wdm_certificate_qr_code size="100"]
                </div>',
                'post_status'  => 'publish',
                'post_type'    => 'sfwd-certificates',
            ));
            $log .= "   Created standard certificate (ID: $standard_cert_id)\n";
        }

        // Pocket certificate
        $existing_pocket = get_posts(array(
            'post_type' => 'sfwd-certificates',
            'title' => 'E2E Test Certificate - Pocket',
            'numberposts' => 1,
        ));

        if (!empty($existing_pocket)) {
            $pocket_cert_id = $existing_pocket[0]->ID;
            $log .= "   Pocket certificate exists (ID: $pocket_cert_id)\n";
        } else {
            $pocket_cert_id = wp_insert_post(array(
                'post_title'   => 'E2E Test Certificate - Pocket',
                'post_content' => '<div style="text-align:center;padding:20px;border:1px solid #333;max-width:300px;">
                    <h3>Pocket Certificate</h3>
                    <p>[usermeta field="display_name"]</p>
                    <p>[courseinfo show="course_title"]</p>
                    <p>ID: [wdm_certificate_id]</p>
                    [wdm_certificate_qr_code size="80"]
                </div>',
                'post_status'  => 'publish',
                'post_type'    => 'sfwd-certificates',
            ));
            $log .= "   Created pocket certificate (ID: $pocket_cert_id)\n";
        }

        // 4. Create test courses
        $log .= "\n4. Creating test courses...\n";

        // Course 1 - single certificate
        $existing_course1 = get_posts(array(
            'post_type' => 'sfwd-courses',
            'title' => 'E2E Test Course - Single Certificate',
            'numberposts' => 1,
        ));

        if (!empty($existing_course1)) {
            $course1_id = $existing_course1[0]->ID;
            $log .= "   Course 1 exists (ID: $course1_id)\n";
        } else {
            $course1_id = wp_insert_post(array(
                'post_title'   => 'E2E Test Course - Single Certificate',
                'post_content' => 'Test course for E2E testing with single certificate.',
                'post_status'  => 'publish',
                'post_type'    => 'sfwd-courses',
            ));
            $log .= "   Created course 1 (ID: $course1_id)\n";
        }

        // Set certificate for course 1
        if ($course1_id && $standard_cert_id) {
            $course_settings = get_post_meta($course1_id, '_sfwd-courses', true);
            if (!is_array($course_settings)) {
                $course_settings = array();
            }
            $course_settings['sfwd-courses_certificate'] = $standard_cert_id;
            update_post_meta($course1_id, '_sfwd-courses', $course_settings);
            $log .= "   Assigned certificate to course 1\n";
        }

        // Course 2 - dual certificates
        $existing_course2 = get_posts(array(
            'post_type' => 'sfwd-courses',
            'title' => 'E2E Test Course - Dual Certificates',
            'numberposts' => 1,
        ));

        if (!empty($existing_course2)) {
            $course2_id = $existing_course2[0]->ID;
            $log .= "   Course 2 exists (ID: $course2_id)\n";
        } else {
            $course2_id = wp_insert_post(array(
                'post_title'   => 'E2E Test Course - Dual Certificates',
                'post_content' => 'Test course for E2E testing with dual certificates.',
                'post_status'  => 'publish',
                'post_type'    => 'sfwd-courses',
            ));
            $log .= "   Created course 2 (ID: $course2_id)\n";
        }

        // Set certificates for course 2
        if ($course2_id && $standard_cert_id) {
            $course_settings = get_post_meta($course2_id, '_sfwd-courses', true);
            if (!is_array($course_settings)) {
                $course_settings = array();
            }
            $course_settings['sfwd-courses_certificate'] = $standard_cert_id;
            update_post_meta($course2_id, '_sfwd-courses', $course_settings);
            update_post_meta($course2_id, '_wdm_pocket_certificate', $pocket_cert_id);
            $log .= "   Assigned standard and pocket certificates to course 2\n";
        }

        // 5. Create test quiz
        $log .= "\n5. Creating test quiz...\n";

        $existing_quiz = get_posts(array(
            'post_type' => 'sfwd-quiz',
            'title' => 'E2E Test Quiz',
            'numberposts' => 1,
        ));

        if (!empty($existing_quiz)) {
            $quiz_id = $existing_quiz[0]->ID;
            $log .= "   Quiz exists (ID: $quiz_id)\n";
        } else {
            $quiz_id = wp_insert_post(array(
                'post_title'   => 'E2E Test Quiz',
                'post_content' => 'Test quiz for E2E testing.',
                'post_status'  => 'publish',
                'post_type'    => 'sfwd-quiz',
            ));

            if ($quiz_id && $standard_cert_id) {
                $quiz_settings = array('sfwd-quiz_certificate' => $standard_cert_id);
                update_post_meta($quiz_id, '_sfwd-quiz', $quiz_settings);
            }
            $log .= "   Created quiz (ID: $quiz_id)\n";
        }

        // 6. Configure plugin settings
        $log .= "\n6. Configuring plugin settings...\n";

        // Create verification page if not exists
        $options = get_option('wdm_certificate_options', array());
        $verification_page_id = isset($options['verification_page_id']) ? $options['verification_page_id'] : 0;

        if (!$verification_page_id || !get_post($verification_page_id)) {
            $existing_page = get_posts(array(
                'post_type' => 'page',
                'title' => 'Certificate Verification',
                'numberposts' => 1,
            ));

            if (!empty($existing_page)) {
                $verification_page_id = $existing_page[0]->ID;
            } else {
                $verification_page_id = wp_insert_post(array(
                    'post_title'   => 'Certificate Verification',
                    'post_content' => '[wdm_certificate_verify]',
                    'post_status'  => 'publish',
                    'post_type'    => 'page',
                    'post_name'    => 'certificate-verification',
                ));
            }
            $log .= "   Verification page (ID: $verification_page_id)\n";
        } else {
            $log .= "   Verification page exists (ID: $verification_page_id)\n";
        }

        // Update options
        $options['verification_page_id'] = $verification_page_id;
        $options['enable_pocket_cert'] = 1;
        $options['qr_code_size'] = 150;
        update_option('wdm_certificate_options', $options);
        $log .= "   Plugin settings configured.\n";

        // 7. Create certificate records
        $log .= "\n7. Creating certificate records...\n";

        // Generate CSUIDs
        $cid = strtoupper(dechex($standard_cert_id));
        $sid1 = strtoupper(dechex($course1_id));
        $sid2 = strtoupper(dechex($course2_id));
        $uid = strtoupper(dechex($student1_id + 1000));

        $csuid1 = "$cid-$sid1-$uid";
        $csuid2 = "$cid-$sid2-$uid";

        // Create records
        $cert_records = array(
            $csuid1 => array(
                'csuid' => $csuid1,
                'standard_cert' => $standard_cert_id,
                'source_type' => 'course',
                'source_id' => $course1_id,
                'user_id' => $student1_id,
                'completion_date' => current_time('mysql'),
                'is_retroactive' => false,
            ),
            $csuid2 => array(
                'csuid' => $csuid2,
                'standard_cert' => $standard_cert_id,
                'pocket_cert' => $pocket_cert_id,
                'source_type' => 'course',
                'source_id' => $course2_id,
                'user_id' => $student1_id,
                'completion_date' => current_time('mysql'),
                'is_retroactive' => false,
            ),
        );

        update_user_meta($student1_id, 'wdm_certificate_records', $cert_records);
        $log .= "   Created certificate record 1 (CSUID: $csuid1)\n";
        $log .= "   Created certificate record 2 (CSUID: $csuid2)\n";

        // Get verification URL
        $verification_url = get_permalink($verification_page_id);
        $verification_path = str_replace(home_url(), '', $verification_url);

        // 8. Build .env content
        $log .= "\n=== SETUP COMPLETE ===\n";

        $env_content = "# WordPress Site Configuration
WP_BASE_URL=" . home_url() . "

# Admin Credentials
WP_ADMIN_USER=admin
WP_ADMIN_PASS=admin

# Test Student Credentials
WP_TEST_STUDENT_USER=saniyaj
WP_TEST_STUDENT_PASS=saniyaj
WP_TEST_STUDENT_ID=$student1_id

# Test Student 2 (for non-owner tests)
WP_TEST_STUDENT2_USER=test_student_2
WP_TEST_STUDENT2_PASS=test_student_password
WP_TEST_STUDENT2_ID=$student2_id

# Test Data IDs
WP_TEST_COURSE_ID=$course1_id
WP_TEST_COURSE_WITH_DUAL_CERT_ID=$course2_id
WP_TEST_CERTIFICATE_ID=$standard_cert_id
WP_TEST_POCKET_CERTIFICATE_ID=$pocket_cert_id
WP_TEST_QUIZ_ID=$quiz_id
WP_VERIFICATION_PAGE_ID=$verification_page_id

# Verification Page URL
WP_VERIFICATION_URL=$verification_path

# Valid Test Certificate IDs (CSUID format)
WP_VALID_CERT_ID=$csuid1
WP_VALID_CERT_ID_WITH_POCKET=$csuid2

# Database Configuration
DB_HOST=localhost
DB_NAME=local
DB_USER=root
DB_PASS=root
DB_PREFIX=wp_
";

        return array(
            'env_content' => $env_content,
            'log' => $log,
        );
    }
}

// Initialize
WDM_Test_Setup::init();
