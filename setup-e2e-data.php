<?php
/**
 * E2E Test Data Setup Endpoint
 *
 * Access via: /wp-content/plugins/wdm-certificate-customizations/setup-e2e-data.php?key=e2e_setup_key_2024
 *
 * SECURITY: Remove this file after setup or in production!
 */

// Simple security key - change this or remove file after use
$secret_key = 'e2e_setup_key_2024';

if (!isset($_GET['key']) || $_GET['key'] !== $secret_key) {
    header('HTTP/1.1 403 Forbidden');
    die('Access denied. Provide correct key parameter.');
}

// Load WordPress
require_once dirname(__FILE__) . '/../../../wp-load.php';

// Set content type
header('Content-Type: text/plain');

echo "=== WDM Certificate Customizations E2E Test Setup ===\n\n";

// Set admin user
wp_set_current_user(1);

// 1. Ensure plugin is active
echo "1. Checking plugin activation...\n";
$plugin_file = 'wdm-certificate-customizations/wdm-certificate-customizations.php';
if (!function_exists('is_plugin_active')) {
    require_once ABSPATH . 'wp-admin/includes/plugin.php';
}
if (!is_plugin_active($plugin_file)) {
    $result = activate_plugin($plugin_file);
    if (is_wp_error($result)) {
        echo "   ERROR: " . $result->get_error_message() . "\n";
    } else {
        echo "   Plugin activated.\n";
    }
} else {
    echo "   Plugin already active.\n";
}

// 2. Create test users
echo "\n2. Creating test users...\n";

// Student 1
$student1_username = 'saniyaj';
$student1 = get_user_by('login', $student1_username);
if (!$student1) {
    $student1_id = wp_create_user($student1_username, 'saniyaj', 'saniyaj@test.local');
    if (!is_wp_error($student1_id)) {
        $u = new WP_User($student1_id);
        $u->set_role('subscriber');
        echo "   Created student1 (ID: $student1_id)\n";
    } else {
        echo "   ERROR: " . $student1_id->get_error_message() . "\n";
        $student1_id = 0;
    }
} else {
    $student1_id = $student1->ID;
    echo "   Student1 exists (ID: $student1_id)\n";
}

// Student 2
$student2_username = 'test_student_2';
$student2 = get_user_by('login', $student2_username);
if (!$student2) {
    $student2_id = wp_create_user($student2_username, 'test_student_password', 'student2@test.local');
    if (!is_wp_error($student2_id)) {
        $u = new WP_User($student2_id);
        $u->set_role('subscriber');
        echo "   Created student2 (ID: $student2_id)\n";
    } else {
        echo "   ERROR: " . $student2_id->get_error_message() . "\n";
        $student2_id = 0;
    }
} else {
    $student2_id = $student2->ID;
    echo "   Student2 exists (ID: $student2_id)\n";
}

// 3. Create certificate templates
echo "\n3. Creating certificate templates...\n";

// Check if post type exists
if (!post_type_exists('sfwd-certificates')) {
    // Register temporarily for creation
    register_post_type('sfwd-certificates', array(
        'public' => true,
        'label' => 'Certificates',
    ));
    echo "   Note: LearnDash not active, registered certificate post type temporarily.\n";
}

// Standard certificate
$existing_cert = get_posts(array(
    'post_type' => 'sfwd-certificates',
    'title' => 'E2E Test Certificate - Standard',
    'numberposts' => 1,
    'post_status' => 'any',
));

if (!empty($existing_cert)) {
    $standard_cert_id = $existing_cert[0]->ID;
    echo "   Standard certificate exists (ID: $standard_cert_id)\n";
} else {
    $standard_cert_id = wp_insert_post(array(
        'post_title'   => 'E2E Test Certificate - Standard',
        'post_content' => '<div style="text-align:center;padding:50px;border:2px solid #333;background:#fff;">
<h1 style="color:#333;">Certificate of Completion</h1>
<p>This certifies that</p>
<h2>[usermeta field="display_name"]</h2>
<p>has successfully completed</p>
<h2>[courseinfo show="course_title"]</h2>
<p>Date: [courseinfo show="completed_on"]</p>
<p style="margin-top:30px;"><strong>Certificate ID:</strong> [wdm_certificate_id]</p>
[wdm_certificate_qr_code size="100"]
</div>',
        'post_status'  => 'publish',
        'post_type'    => 'sfwd-certificates',
    ));
    echo "   Created standard certificate (ID: $standard_cert_id)\n";
}

// Pocket certificate
$existing_pocket = get_posts(array(
    'post_type' => 'sfwd-certificates',
    'title' => 'E2E Test Certificate - Pocket',
    'numberposts' => 1,
    'post_status' => 'any',
));

if (!empty($existing_pocket)) {
    $pocket_cert_id = $existing_pocket[0]->ID;
    echo "   Pocket certificate exists (ID: $pocket_cert_id)\n";
} else {
    $pocket_cert_id = wp_insert_post(array(
        'post_title'   => 'E2E Test Certificate - Pocket',
        'post_content' => '<div style="text-align:center;padding:20px;border:1px solid #333;max-width:300px;background:#fff;">
<h3>Pocket Certificate</h3>
<p>[usermeta field="display_name"]</p>
<p>[courseinfo show="course_title"]</p>
<p><small>ID: [wdm_certificate_id]</small></p>
[wdm_certificate_qr_code size="80"]
</div>',
        'post_status'  => 'publish',
        'post_type'    => 'sfwd-certificates',
    ));
    echo "   Created pocket certificate (ID: $pocket_cert_id)\n";
}

// 4. Create test courses
echo "\n4. Creating test courses...\n";

// Check if post type exists
if (!post_type_exists('sfwd-courses')) {
    register_post_type('sfwd-courses', array(
        'public' => true,
        'label' => 'Courses',
    ));
    echo "   Note: LearnDash not active, registered course post type temporarily.\n";
}

// Course 1
$existing_course1 = get_posts(array(
    'post_type' => 'sfwd-courses',
    'title' => 'E2E Test Course - Single Certificate',
    'numberposts' => 1,
    'post_status' => 'any',
));

if (!empty($existing_course1)) {
    $course1_id = $existing_course1[0]->ID;
    echo "   Course 1 exists (ID: $course1_id)\n";
} else {
    $course1_id = wp_insert_post(array(
        'post_title'   => 'E2E Test Course - Single Certificate',
        'post_content' => 'This is a test course for E2E testing with a single certificate.',
        'post_status'  => 'publish',
        'post_type'    => 'sfwd-courses',
    ));
    echo "   Created course 1 (ID: $course1_id)\n";
}

// Set certificate for course 1
if ($course1_id && $standard_cert_id) {
    update_post_meta($course1_id, '_sfwd-courses', array(
        'sfwd-courses_certificate' => $standard_cert_id,
    ));
    echo "   Assigned certificate to course 1\n";
}

// Course 2
$existing_course2 = get_posts(array(
    'post_type' => 'sfwd-courses',
    'title' => 'E2E Test Course - Dual Certificates',
    'numberposts' => 1,
    'post_status' => 'any',
));

if (!empty($existing_course2)) {
    $course2_id = $existing_course2[0]->ID;
    echo "   Course 2 exists (ID: $course2_id)\n";
} else {
    $course2_id = wp_insert_post(array(
        'post_title'   => 'E2E Test Course - Dual Certificates',
        'post_content' => 'This is a test course for E2E testing with both standard and pocket certificates.',
        'post_status'  => 'publish',
        'post_type'    => 'sfwd-courses',
    ));
    echo "   Created course 2 (ID: $course2_id)\n";
}

// Set certificates for course 2
if ($course2_id) {
    update_post_meta($course2_id, '_sfwd-courses', array(
        'sfwd-courses_certificate' => $standard_cert_id,
    ));
    update_post_meta($course2_id, '_wdm_pocket_certificate', $pocket_cert_id);
    echo "   Assigned standard and pocket certificates to course 2\n";
}

// 5. Create test quiz
echo "\n5. Creating test quiz...\n";

if (!post_type_exists('sfwd-quiz')) {
    register_post_type('sfwd-quiz', array(
        'public' => true,
        'label' => 'Quizzes',
    ));
}

$existing_quiz = get_posts(array(
    'post_type' => 'sfwd-quiz',
    'title' => 'E2E Test Quiz',
    'numberposts' => 1,
    'post_status' => 'any',
));

if (!empty($existing_quiz)) {
    $quiz_id = $existing_quiz[0]->ID;
    echo "   Quiz exists (ID: $quiz_id)\n";
} else {
    $quiz_id = wp_insert_post(array(
        'post_title'   => 'E2E Test Quiz',
        'post_content' => 'This is a test quiz for E2E testing.',
        'post_status'  => 'publish',
        'post_type'    => 'sfwd-quiz',
    ));

    if ($quiz_id && $standard_cert_id) {
        update_post_meta($quiz_id, '_sfwd-quiz', array(
            'sfwd-quiz_certificate' => $standard_cert_id,
        ));
    }
    echo "   Created quiz (ID: $quiz_id)\n";
}

// 6. Configure plugin settings
echo "\n6. Configuring plugin settings...\n";

$options = get_option('wdm_certificate_options', array());
$verification_page_id = isset($options['verification_page_id']) ? intval($options['verification_page_id']) : 0;

if (!$verification_page_id || !get_post($verification_page_id)) {
    $existing_page = get_posts(array(
        'post_type' => 'page',
        'title' => 'Certificate Verification',
        'numberposts' => 1,
        'post_status' => 'any',
    ));

    if (!empty($existing_page)) {
        $verification_page_id = $existing_page[0]->ID;
        // Make sure it's published
        wp_update_post(array('ID' => $verification_page_id, 'post_status' => 'publish'));
        echo "   Verification page exists (ID: $verification_page_id)\n";
    } else {
        $verification_page_id = wp_insert_post(array(
            'post_title'   => 'Certificate Verification',
            'post_content' => '[wdm_certificate_verify]',
            'post_status'  => 'publish',
            'post_type'    => 'page',
            'post_name'    => 'certificate-verification',
        ));
        echo "   Created verification page (ID: $verification_page_id)\n";
    }
}

// Update options
$options['verification_page_id'] = $verification_page_id;
$options['enable_pocket_cert'] = 1;
$options['qr_code_size'] = 150;
update_option('wdm_certificate_options', $options);
echo "   Plugin settings saved.\n";

// Flush rewrite rules
flush_rewrite_rules();
echo "   Rewrite rules flushed.\n";

// 7. Simulate LearnDash course completion for test user
echo "\n7. Simulating LearnDash course completion...\n";

$completion_time = time();

// Add course_completed_{course_id} user meta - this is what LearnDash checks
update_user_meta($student1_id, 'course_completed_' . $course1_id, $completion_time);
echo "   Added course completion meta for course 1\n";

update_user_meta($student1_id, 'course_completed_' . $course2_id, $completion_time);
echo "   Added course completion meta for course 2\n";

// Add _sfwd-course_progress user meta (legacy format used by LearnDash)
$course_progress = get_user_meta($student1_id, '_sfwd-course_progress', true);
if (!is_array($course_progress)) {
    $course_progress = array();
}

// Course 1 progress - mark as completed (empty lessons/topics means no steps required)
$course_progress[$course1_id] = array(
    'lessons' => array(),
    'topics'  => array(),
    'total'   => 0,
    'completed' => 0,
);
echo "   Added course progress for course 1\n";

// Course 2 progress - mark as completed
$course_progress[$course2_id] = array(
    'lessons' => array(),
    'topics'  => array(),
    'total'   => 0,
    'completed' => 0,
);
echo "   Added course progress for course 2\n";

update_user_meta($student1_id, '_sfwd-course_progress', $course_progress);
echo "   Saved course progress meta\n";

// Also add user to course access list for proper enrollment
$course1_access = get_post_meta($course1_id, 'course_access_list', true);
if (empty($course1_access)) {
    $course1_access = '';
}
if (strpos($course1_access, (string)$student1_id) === false) {
    $course1_access = trim($course1_access . ',' . $student1_id, ',');
    update_post_meta($course1_id, 'course_access_list', $course1_access);
    echo "   Added student to course 1 access list\n";
}

$course2_access = get_post_meta($course2_id, 'course_access_list', true);
if (empty($course2_access)) {
    $course2_access = '';
}
if (strpos($course2_access, (string)$student1_id) === false) {
    $course2_access = trim($course2_access . ',' . $student1_id, ',');
    update_post_meta($course2_id, 'course_access_list', $course2_access);
    echo "   Added student to course 2 access list\n";
}

// 8. Create certificate records
echo "\n8. Creating certificate records...\n";

// Generate CSUIDs using the plugin's actual encoding algorithm
require_once dirname(__FILE__) . '/includes/class-helper.php';
$csuid1 = WDM_Cert_Helper::encode_csuid($standard_cert_id, $course1_id, $student1_id);
$csuid2 = WDM_Cert_Helper::encode_csuid($standard_cert_id, $course2_id, $student1_id);

// Create certificate records for student 1 using the correct meta key format
// Meta key format: _wdm_certificate_{source_type}_{source_id}

// Record for course 1 (single certificate)
$record1 = array(
    'certificate_id'  => $csuid1,
    'standard_cert'   => $standard_cert_id,
    'pocket_cert'     => 0,
    'source_type'     => 'course',
    'source_id'       => $course1_id,
    'user_id'         => $student1_id,
    'completion_date' => time(),
    'generated_date'  => time(),
    'is_retroactive'  => false,
);
update_user_meta($student1_id, '_wdm_certificate_course_' . $course1_id, $record1);
echo "   Created certificate record 1: $csuid1\n";

// Record for course 2 (dual certificate with pocket)
$record2 = array(
    'certificate_id'  => $csuid2,
    'standard_cert'   => $standard_cert_id,
    'pocket_cert'     => $pocket_cert_id,
    'source_type'     => 'course',
    'source_id'       => $course2_id,
    'user_id'         => $student1_id,
    'completion_date' => time(),
    'generated_date'  => time(),
    'is_retroactive'  => false,
);
update_user_meta($student1_id, '_wdm_certificate_course_' . $course2_id, $record2);
echo "   Created certificate record 2: $csuid2\n";

// Get verification URL
$verification_url = get_permalink($verification_page_id);
$verification_path = str_replace(home_url(), '', $verification_url);

echo "\n";
echo "========================================\n";
echo "=== SETUP COMPLETE ===\n";
echo "========================================\n\n";

echo "Copy this to your .env file:\n";
echo "----------------------------------------\n";

echo "# WordPress Site Configuration
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

# Valid Test Certificate IDs
WP_VALID_CERT_ID=$csuid1
WP_VALID_CERT_ID_WITH_POCKET=$csuid2

# Database Configuration
DB_HOST=localhost
DB_NAME=local
DB_USER=root
DB_PASS=root
DB_PREFIX=wp_
";

echo "\n----------------------------------------\n";
echo "Setup complete! Remove this file after use.\n";
