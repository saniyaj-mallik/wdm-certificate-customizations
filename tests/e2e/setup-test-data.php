<?php
/**
 * E2E Test Data Setup Script
 *
 * Run this script to create all necessary test data for E2E tests.
 * Execute via: php setup-test-data.php
 *
 * This script must be run from the WordPress context.
 */

// Load WordPress
$wp_load_path = 'C:/Users/saniy/Local Sites/dave-small/app/public/wp-load.php';
if (!file_exists($wp_load_path)) {
    die("Error: Cannot find wp-load.php at: $wp_load_path\n");
}
require_once $wp_load_path;

// Ensure we're running as admin
if (!current_user_can('manage_options') && php_sapi_name() !== 'cli') {
    wp_set_current_user(1); // Set to admin user
}

echo "=== WDM Certificate Customizations E2E Test Setup ===\n\n";

// 1. Activate the plugin if not active
echo "1. Checking plugin activation...\n";
$plugin_file = 'wdm-certificate-customizations/wdm-certificate-customizations.php';
if (!is_plugin_active($plugin_file)) {
    $result = activate_plugin($plugin_file);
    if (is_wp_error($result)) {
        echo "   ERROR: Could not activate plugin: " . $result->get_error_message() . "\n";
    } else {
        echo "   Plugin activated successfully.\n";
    }
} else {
    echo "   Plugin already active.\n";
}

// 2. Create test student user if not exists
echo "\n2. Creating test users...\n";

// Student 1
$student1_username = 'saniyaj';
$student1 = get_user_by('login', $student1_username);
if (!$student1) {
    $student1_id = wp_create_user($student1_username, 'saniyaj', 'saniyaj@test.local');
    if (is_wp_error($student1_id)) {
        echo "   ERROR creating student1: " . $student1_id->get_error_message() . "\n";
        $student1_id = 0;
    } else {
        $student1 = get_user_by('ID', $student1_id);
        $student1->set_role('subscriber');
        echo "   Created student1: $student1_username (ID: $student1_id)\n";
    }
} else {
    $student1_id = $student1->ID;
    echo "   Student1 already exists: $student1_username (ID: $student1_id)\n";
}

// Student 2
$student2_username = 'test_student_2';
$student2 = get_user_by('login', $student2_username);
if (!$student2) {
    $student2_id = wp_create_user($student2_username, 'test_student_password', 'student2@test.local');
    if (is_wp_error($student2_id)) {
        echo "   ERROR creating student2: " . $student2_id->get_error_message() . "\n";
        $student2_id = 0;
    } else {
        $student2 = get_user_by('ID', $student2_id);
        $student2->set_role('subscriber');
        echo "   Created student2: $student2_username (ID: $student2_id)\n";
    }
} else {
    $student2_id = $student2->ID;
    echo "   Student2 already exists: $student2_username (ID: $student2_id)\n";
}

// 3. Create certificate templates
echo "\n3. Creating certificate templates...\n";

// Check if LearnDash is active
if (!defined('LEARNDASH_VERSION')) {
    echo "   WARNING: LearnDash is not active. Creating mock certificates.\n";

    // Create a simple certificate post
    $cert_args = array(
        'post_title'   => 'Test Certificate - Standard',
        'post_content' => '<div style="text-align:center;padding:50px;border:2px solid #333;">
            <h1>Certificate of Completion</h1>
            <p>This certifies that</p>
            <h2>[usermeta field="display_name"]</h2>
            <p>has successfully completed</p>
            <h2>[courseinfo show="course_title"]</h2>
            <p>Certificate ID: [wdm_certificate_id]</p>
            <p>[wdm_certificate_qr_code size="100"]</p>
        </div>',
        'post_status'  => 'publish',
        'post_type'    => 'sfwd-certificates',
    );

    // Check if certificate already exists
    $existing_cert = get_posts(array(
        'post_type' => 'sfwd-certificates',
        'title' => 'Test Certificate - Standard',
        'numberposts' => 1,
    ));

    if (!empty($existing_cert)) {
        $standard_cert_id = $existing_cert[0]->ID;
        echo "   Standard certificate already exists (ID: $standard_cert_id)\n";
    } else {
        $standard_cert_id = wp_insert_post($cert_args);
        if (is_wp_error($standard_cert_id)) {
            echo "   ERROR creating standard certificate\n";
            $standard_cert_id = 0;
        } else {
            echo "   Created standard certificate (ID: $standard_cert_id)\n";
        }
    }

    // Create pocket certificate
    $pocket_cert_args = array(
        'post_title'   => 'Test Certificate - Pocket',
        'post_content' => '<div style="text-align:center;padding:20px;border:1px solid #333;max-width:300px;">
            <h3>Pocket Certificate</h3>
            <p>[usermeta field="display_name"]</p>
            <p>[courseinfo show="course_title"]</p>
            <p>ID: [wdm_certificate_id]</p>
            <p>[wdm_certificate_qr_code size="80"]</p>
        </div>',
        'post_status'  => 'publish',
        'post_type'    => 'sfwd-certificates',
    );

    $existing_pocket = get_posts(array(
        'post_type' => 'sfwd-certificates',
        'title' => 'Test Certificate - Pocket',
        'numberposts' => 1,
    ));

    if (!empty($existing_pocket)) {
        $pocket_cert_id = $existing_pocket[0]->ID;
        echo "   Pocket certificate already exists (ID: $pocket_cert_id)\n";
    } else {
        $pocket_cert_id = wp_insert_post($pocket_cert_args);
        if (is_wp_error($pocket_cert_id)) {
            echo "   ERROR creating pocket certificate\n";
            $pocket_cert_id = 0;
        } else {
            echo "   Created pocket certificate (ID: $pocket_cert_id)\n";
        }
    }
} else {
    echo "   LearnDash is active. Using existing certificates or creating new ones.\n";

    // Get existing certificates
    $certs = get_posts(array(
        'post_type' => 'sfwd-certificates',
        'numberposts' => 2,
        'post_status' => 'publish',
    ));

    if (count($certs) >= 1) {
        $standard_cert_id = $certs[0]->ID;
        echo "   Using existing certificate as standard (ID: $standard_cert_id)\n";
    }

    if (count($certs) >= 2) {
        $pocket_cert_id = $certs[1]->ID;
        echo "   Using existing certificate as pocket (ID: $pocket_cert_id)\n";
    } else {
        $pocket_cert_id = $standard_cert_id;
        echo "   Using same certificate for pocket (ID: $pocket_cert_id)\n";
    }
}

// 4. Create test courses
echo "\n4. Creating test courses...\n";

// Course with single certificate
$course1_args = array(
    'post_title'   => 'E2E Test Course - Single Certificate',
    'post_content' => 'This is a test course for E2E testing with a single certificate.',
    'post_status'  => 'publish',
    'post_type'    => 'sfwd-courses',
);

$existing_course1 = get_posts(array(
    'post_type' => 'sfwd-courses',
    'title' => 'E2E Test Course - Single Certificate',
    'numberposts' => 1,
));

if (!empty($existing_course1)) {
    $course1_id = $existing_course1[0]->ID;
    echo "   Course 1 already exists (ID: $course1_id)\n";
} else {
    $course1_id = wp_insert_post($course1_args);
    if (is_wp_error($course1_id)) {
        echo "   ERROR creating course 1\n";
        $course1_id = 0;
    } else {
        echo "   Created course 1 (ID: $course1_id)\n";
    }
}

// Set certificate for course 1
if ($course1_id && $standard_cert_id) {
    update_post_meta($course1_id, '_sfwd-courses', array(
        'sfwd-courses_certificate' => $standard_cert_id,
    ));
    echo "   Assigned standard certificate to course 1\n";
}

// Course with dual certificates
$course2_args = array(
    'post_title'   => 'E2E Test Course - Dual Certificates',
    'post_content' => 'This is a test course for E2E testing with both standard and pocket certificates.',
    'post_status'  => 'publish',
    'post_type'    => 'sfwd-courses',
);

$existing_course2 = get_posts(array(
    'post_type' => 'sfwd-courses',
    'title' => 'E2E Test Course - Dual Certificates',
    'numberposts' => 1,
));

if (!empty($existing_course2)) {
    $course2_id = $existing_course2[0]->ID;
    echo "   Course 2 already exists (ID: $course2_id)\n";
} else {
    $course2_id = wp_insert_post($course2_args);
    if (is_wp_error($course2_id)) {
        echo "   ERROR creating course 2\n";
        $course2_id = 0;
    } else {
        echo "   Created course 2 (ID: $course2_id)\n";
    }
}

// Set certificates for course 2
if ($course2_id && $standard_cert_id) {
    update_post_meta($course2_id, '_sfwd-courses', array(
        'sfwd-courses_certificate' => $standard_cert_id,
    ));
    update_post_meta($course2_id, '_wdm_pocket_certificate', $pocket_cert_id);
    echo "   Assigned standard and pocket certificates to course 2\n";
}

// 5. Create test quiz
echo "\n5. Creating test quiz...\n";

$quiz_args = array(
    'post_title'   => 'E2E Test Quiz',
    'post_content' => 'This is a test quiz for E2E testing.',
    'post_status'  => 'publish',
    'post_type'    => 'sfwd-quiz',
);

$existing_quiz = get_posts(array(
    'post_type' => 'sfwd-quiz',
    'title' => 'E2E Test Quiz',
    'numberposts' => 1,
));

if (!empty($existing_quiz)) {
    $quiz_id = $existing_quiz[0]->ID;
    echo "   Quiz already exists (ID: $quiz_id)\n";
} else {
    $quiz_id = wp_insert_post($quiz_args);
    if (is_wp_error($quiz_id)) {
        echo "   ERROR creating quiz\n";
        $quiz_id = 0;
    } else {
        // Assign certificate to quiz
        if ($standard_cert_id) {
            update_post_meta($quiz_id, '_sfwd-quiz', array(
                'sfwd-quiz_certificate' => $standard_cert_id,
            ));
        }
        echo "   Created quiz (ID: $quiz_id)\n";
    }
}

// 6. Configure plugin settings
echo "\n6. Configuring plugin settings...\n";

// Get verification page
$options = get_option('wdm_certificate_options', array());
$verification_page_id = isset($options['verification_page_id']) ? $options['verification_page_id'] : 0;

if (!$verification_page_id) {
    // Create verification page
    $page_args = array(
        'post_title'   => 'Certificate Verification',
        'post_content' => '[wdm_certificate_verify]',
        'post_status'  => 'publish',
        'post_type'    => 'page',
    );

    $existing_page = get_posts(array(
        'post_type' => 'page',
        'title' => 'Certificate Verification',
        'numberposts' => 1,
    ));

    if (!empty($existing_page)) {
        $verification_page_id = $existing_page[0]->ID;
        echo "   Verification page already exists (ID: $verification_page_id)\n";
    } else {
        $verification_page_id = wp_insert_post($page_args);
        echo "   Created verification page (ID: $verification_page_id)\n";
    }
}

// Update plugin options
$options['verification_page_id'] = $verification_page_id;
$options['enable_pocket_cert'] = 1;
$options['qr_code_size'] = 150;
update_option('wdm_certificate_options', $options);
echo "   Plugin settings configured.\n";

// 7. Create certificate records for student
echo "\n7. Creating certificate records for test student...\n";

if (class_exists('WDM_Certificate_Helper') && $student1_id && $course1_id) {
    // Generate CSUID for course 1
    $csuid1 = WDM_Certificate_Helper::encode_csuid($standard_cert_id, $course1_id, $student1_id);

    // Create certificate record
    $cert_records = get_user_meta($student1_id, 'wdm_certificate_records', true);
    if (!is_array($cert_records)) {
        $cert_records = array();
    }

    // Add record for course 1
    $cert_records[$csuid1] = array(
        'csuid' => $csuid1,
        'standard_cert' => $standard_cert_id,
        'source_type' => 'course',
        'source_id' => $course1_id,
        'user_id' => $student1_id,
        'completion_date' => current_time('mysql'),
        'is_retroactive' => false,
    );

    // Generate CSUID for course 2 (with pocket cert)
    if ($course2_id) {
        $csuid2 = WDM_Certificate_Helper::encode_csuid($standard_cert_id, $course2_id, $student1_id);
        $cert_records[$csuid2] = array(
            'csuid' => $csuid2,
            'standard_cert' => $standard_cert_id,
            'pocket_cert' => $pocket_cert_id,
            'source_type' => 'course',
            'source_id' => $course2_id,
            'user_id' => $student1_id,
            'completion_date' => current_time('mysql'),
            'is_retroactive' => false,
        );
    }

    update_user_meta($student1_id, 'wdm_certificate_records', $cert_records);

    echo "   Created certificate record for course 1 (CSUID: $csuid1)\n";
    if (isset($csuid2)) {
        echo "   Created certificate record for course 2 (CSUID: $csuid2)\n";
    }

    $valid_csuid = $csuid1;
    $valid_csuid_with_pocket = isset($csuid2) ? $csuid2 : '';
} else {
    // Manual CSUID generation if class not available
    echo "   WARNING: WDM_Certificate_Helper not available. Creating manual CSUIDs.\n";

    // Simple hex encoding
    $cid = strtoupper(dechex($standard_cert_id));
    $sid = strtoupper(dechex($course1_id));
    $uid = strtoupper(dechex($student1_id + 1000));
    $valid_csuid = "$cid-$sid-$uid";

    if ($course2_id) {
        $sid2 = strtoupper(dechex($course2_id));
        $valid_csuid_with_pocket = "$cid-$sid2-$uid";
    } else {
        $valid_csuid_with_pocket = '';
    }

    // Store records manually
    $cert_records = array(
        $valid_csuid => array(
            'csuid' => $valid_csuid,
            'standard_cert' => $standard_cert_id,
            'source_type' => 'course',
            'source_id' => $course1_id,
            'user_id' => $student1_id,
            'completion_date' => current_time('mysql'),
            'is_retroactive' => false,
        ),
    );

    if ($valid_csuid_with_pocket) {
        $cert_records[$valid_csuid_with_pocket] = array(
            'csuid' => $valid_csuid_with_pocket,
            'standard_cert' => $standard_cert_id,
            'pocket_cert' => $pocket_cert_id,
            'source_type' => 'course',
            'source_id' => $course2_id,
            'user_id' => $student1_id,
            'completion_date' => current_time('mysql'),
            'is_retroactive' => false,
        );
    }

    update_user_meta($student1_id, 'wdm_certificate_records', $cert_records);

    echo "   Created certificate record (CSUID: $valid_csuid)\n";
    if ($valid_csuid_with_pocket) {
        echo "   Created certificate record with pocket (CSUID: $valid_csuid_with_pocket)\n";
    }
}

// 8. Get verification page URL
$verification_url = get_permalink($verification_page_id);
$verification_path = str_replace(home_url(), '', $verification_url);

// 9. Output summary and .env values
echo "\n========================================\n";
echo "=== TEST DATA SETUP COMPLETE ===\n";
echo "========================================\n\n";

echo "Update your .env file with these values:\n\n";
echo "WP_TEST_STUDENT_ID=$student1_id\n";
echo "WP_TEST_STUDENT2_ID=$student2_id\n";
echo "WP_TEST_COURSE_ID=$course1_id\n";
echo "WP_TEST_COURSE_WITH_DUAL_CERT_ID=$course2_id\n";
echo "WP_TEST_CERTIFICATE_ID=$standard_cert_id\n";
echo "WP_TEST_POCKET_CERTIFICATE_ID=$pocket_cert_id\n";
echo "WP_TEST_QUIZ_ID=$quiz_id\n";
echo "WP_VERIFICATION_PAGE_ID=$verification_page_id\n";
echo "WP_VERIFICATION_URL=$verification_path\n";
echo "WP_VALID_CERT_ID=$valid_csuid\n";
if ($valid_csuid_with_pocket) {
    echo "WP_VALID_CERT_ID_WITH_POCKET=$valid_csuid_with_pocket\n";
}

echo "\n=== Setup Script Complete ===\n";
