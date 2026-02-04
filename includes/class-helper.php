<?php
/**
 * Helper class for Certificate ID (CSUID) encoding/decoding
 *
 * @package WDM_Certificate_Customizations
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Helper class
 */
class WDM_Cert_Helper {

    /**
     * Encode data to Certificate ID (CSUID)
     *
     * Uses simple hex encoding: cert_id-source_id-user_id
     * Each ID is converted to uppercase hexadecimal.
     *
     * @param int $cert_id Certificate template ID
     * @param int $source_id Course/Quiz/Group ID
     * @param int $user_id User ID
     * @return string Certificate ID
     */
    public static function encode_csuid( $cert_id, $source_id, $user_id ) {
        if ( ! $cert_id || ! $source_id || ! $user_id ) {
            return '';
        }

        $cert_id   = absint( $cert_id );
        $source_id = absint( $source_id );
        $user_id   = absint( $user_id );

        // Simple hex encoding of each ID
        $cid = strtoupper( dechex( $cert_id ) );
        $sid = strtoupper( dechex( $source_id ) );
        $uid = strtoupper( dechex( $user_id ) );

        return sprintf( '%s-%s-%s', $cid, $sid, $uid );
    }

    /**
     * Decode Certificate ID to data
     *
     * @param string $csuid Certificate ID
     * @return array Data with cert_id, source_id, user_id
     */
    public static function decode_csuid( $csuid ) {
        if ( ! self::is_csuid_valid( $csuid ) ) {
            return array(
                'cert_id'   => 0,
                'source_id' => 0,
                'user_id'   => 0,
            );
        }

        $csuid = strtoupper( trim( $csuid ) );
        $parts = explode( '-', $csuid );

        if ( count( $parts ) !== 3 ) {
            return array(
                'cert_id'   => 0,
                'source_id' => 0,
                'user_id'   => 0,
            );
        }

        list( $cid, $sid, $uid ) = $parts;

        // Simple hex decoding
        $cert_id   = hexdec( $cid );
        $source_id = hexdec( $sid );
        $user_id   = hexdec( $uid );

        return compact( 'cert_id', 'source_id', 'user_id' );
    }

    /**
     * Validate Certificate ID format
     *
     * @param string $csuid Certificate ID
     * @return bool
     */
    public static function is_csuid_valid( $csuid ) {
        if ( empty( $csuid ) || ! is_string( $csuid ) ) {
            return false;
        }

        $csuid = strtoupper( trim( $csuid ) );

        // Check new format with underscores
        if ( preg_match( '/^[A-F0-9]+(?:_[A-F0-9]+)?-[A-F0-9]+(?:_[A-F0-9]+)?-[A-F0-9]+(?:_[A-F0-9]+)?$/', $csuid ) ) {
            return true;
        }

        // Check old format without underscores
        if ( preg_match( '/^[A-F0-9]+-[A-F0-9]+-[A-F0-9]+$/', $csuid ) ) {
            return true;
        }

        return false;
    }

    /**
     * Get certificate record by CSUID
     *
     * @param string $csuid Certificate ID
     * @return array|false Certificate record or false if not found
     */
    public static function get_certificate_by_csuid( $csuid ) {
        $decoded = self::decode_csuid( $csuid );

        if ( ! $decoded['cert_id'] || ! $decoded['source_id'] || ! $decoded['user_id'] ) {
            return false;
        }

        // Determine source type
        $source_post = get_post( $decoded['source_id'] );
        if ( ! $source_post ) {
            return false;
        }

        $source_type = self::get_source_type( $source_post->post_type );
        if ( ! $source_type ) {
            return false;
        }

        // Get certificate record from user meta
        $meta_key = '_wdm_certificate_' . $source_type . '_' . $decoded['source_id'];
        $record = get_user_meta( $decoded['user_id'], $meta_key, true );

        if ( empty( $record ) ) {
            // Try to generate record on-the-fly for retroactive support
            $record = self::generate_certificate_record( $decoded['cert_id'], $decoded['source_id'], $decoded['user_id'], $source_type );
        }

        if ( ! $record || ! isset( $record['certificate_id'] ) ) {
            return false;
        }

        // Verify the CSUID matches
        if ( strtoupper( $record['certificate_id'] ) !== strtoupper( $csuid ) ) {
            return false;
        }

        return $record;
    }

    /**
     * Get source type from post type
     *
     * @param string $post_type Post type
     * @return string|false Source type or false
     */
    public static function get_source_type( $post_type ) {
        $type_map = array(
            'sfwd-courses' => 'course',
            'sfwd-quiz'    => 'quiz',
            'groups'       => 'group',
        );

        return isset( $type_map[ $post_type ] ) ? $type_map[ $post_type ] : false;
    }

    /**
     * Get post type from source type
     *
     * @param string $source_type Source type
     * @return string|false Post type or false
     */
    public static function get_post_type( $source_type ) {
        $type_map = array(
            'course' => 'sfwd-courses',
            'quiz'   => 'sfwd-quiz',
            'group'  => 'groups',
        );

        return isset( $type_map[ $source_type ] ) ? $type_map[ $source_type ] : false;
    }

    /**
     * Generate certificate record on-the-fly
     *
     * @param int $cert_id Certificate ID
     * @param int $source_id Source ID
     * @param int $user_id User ID
     * @param string $source_type Source type
     * @return array|false
     */
    private static function generate_certificate_record( $cert_id, $source_id, $user_id, $source_type ) {
        // Verify the user actually completed the course/quiz/group
        $has_completed = false;

        switch ( $source_type ) {
            case 'course':
                $has_completed = learndash_course_completed( $user_id, $source_id );
                break;
            case 'quiz':
                // Check quiz completion
                $quiz_attempts = learndash_get_user_quiz_attempt( $user_id, array( 'quiz' => $source_id ) );
                $has_completed = ! empty( $quiz_attempts );
                break;
            case 'group':
                // Check group completion
                if ( function_exists( 'learndash_get_user_group_completed_timestamp' ) ) {
                    $timestamp = learndash_get_user_group_completed_timestamp( $source_id, $user_id );
                    $has_completed = ! empty( $timestamp );
                }
                break;
        }

        if ( ! $has_completed ) {
            return false;
        }

        // Verify certificate is assigned
        $assigned_cert = self::get_assigned_certificate( $source_id, $source_type );
        if ( $assigned_cert != $cert_id ) {
            return false;
        }

        return array(
            'certificate_id'  => self::encode_csuid( $cert_id, $source_id, $user_id ),
            'standard_cert'   => $cert_id,
            'pocket_cert'     => self::get_pocket_certificate( $source_id, $source_type ),
            'source_type'     => $source_type,
            'source_id'       => $source_id,
            'user_id'         => $user_id,
            'completion_date' => self::get_completion_date( $source_id, $user_id, $source_type ),
            'generated_date'  => time(),
            'is_retroactive'  => true,
        );
    }

    /**
     * Get assigned certificate for a source
     *
     * @param int $source_id Source ID
     * @param string $source_type Source type
     * @return int Certificate ID or 0
     */
    public static function get_assigned_certificate( $source_id, $source_type ) {
        switch ( $source_type ) {
            case 'course':
                return absint( learndash_get_setting( $source_id, 'certificate' ) );
            case 'quiz':
                return absint( learndash_get_setting( $source_id, 'certificate' ) );
            case 'group':
                return absint( learndash_get_setting( $source_id, 'certificate' ) );
            default:
                return 0;
        }
    }

    /**
     * Get pocket certificate for a source
     *
     * @param int $source_id Source ID
     * @param string $source_type Source type
     * @return int Pocket certificate ID or 0
     */
    public static function get_pocket_certificate( $source_id, $source_type ) {
        return absint( get_post_meta( $source_id, '_wdm_pocket_certificate', true ) );
    }

    /**
     * Get the standard certificate ID to use for CSUID encoding
     *
     * Both standard and pocket certificates should use the same CSUID,
     * which is based on the standard certificate ID. This function
     * returns the standard certificate ID regardless of whether the
     * current certificate is standard or pocket.
     *
     * @param int $current_cert_id Current certificate template ID
     * @param int $source_id Source ID (course/quiz/group)
     * @return int Standard certificate ID to use for CSUID
     */
    public static function get_standard_cert_for_csuid( $current_cert_id, $source_id ) {
        if ( ! $current_cert_id || ! $source_id ) {
            return $current_cert_id;
        }

        $source_post = get_post( $source_id );
        if ( ! $source_post ) {
            return $current_cert_id;
        }

        $source_type = self::get_source_type( $source_post->post_type );
        if ( ! $source_type ) {
            return $current_cert_id;
        }

        // Get the standard certificate assigned to this source
        $standard_cert_id = self::get_assigned_certificate( $source_id, $source_type );

        // Check if current certificate is the pocket certificate
        $pocket_cert_id = self::get_pocket_certificate( $source_id, $source_type );

        if ( absint( $current_cert_id ) === absint( $pocket_cert_id ) && $standard_cert_id ) {
            // Current certificate is the pocket certificate, use standard cert ID for CSUID
            return $standard_cert_id;
        }

        // Current certificate is the standard certificate or no match found
        return $current_cert_id;
    }

    /**
     * Get completion date for a source
     *
     * @param int $source_id Source ID
     * @param int $user_id User ID
     * @param string $source_type Source type
     * @return int Timestamp
     */
    public static function get_completion_date( $source_id, $user_id, $source_type ) {
        switch ( $source_type ) {
            case 'course':
                $activity = learndash_get_user_activity( array(
                    'user_id'       => $user_id,
                    'post_id'       => $source_id,
                    'activity_type' => 'course',
                ) );
                return ! empty( $activity->activity_completed ) ? $activity->activity_completed : time();

            case 'quiz':
                $quiz_attempts = learndash_get_user_quiz_attempt( $user_id, array( 'quiz' => $source_id ) );
                if ( ! empty( $quiz_attempts ) ) {
                    $last_attempt = end( $quiz_attempts );
                    return isset( $last_attempt['time'] ) ? $last_attempt['time'] : time();
                }
                return time();

            case 'group':
                if ( function_exists( 'learndash_get_user_group_completed_timestamp' ) ) {
                    $timestamp = learndash_get_user_group_completed_timestamp( $source_id, $user_id );
                    return $timestamp ? $timestamp : time();
                }
                return time();

            default:
                return time();
        }
    }

    /**
     * Get verification page URL
     *
     * @param string $csuid Optional Certificate ID to append
     * @return string URL
     */
    public static function get_verification_url( $csuid = '' ) {
        $options = get_option( 'wdm_certificate_options', array() );
        $page_id = isset( $options['verification_page_id'] ) ? absint( $options['verification_page_id'] ) : 0;

        if ( ! $page_id ) {
            return home_url();
        }

        $url = get_permalink( $page_id );

        if ( $csuid ) {
            if ( get_option( 'permalink_structure' ) ) {
                $url = trailingslashit( $url ) . $csuid . '/';
            } else {
                $url = add_query_arg( 'cert_id', $csuid, $url );
            }
        }

        return $url;
    }

    /**
     * Get certificate PDF URL
     *
     * @param int    $cert_id Certificate template ID
     * @param int    $source_id Source ID
     * @param int    $user_id User ID
     * @param string $source_type Source type (course, quiz, group)
     * @return string PDF URL
     */
    public static function get_pdf_url( $cert_id, $source_id, $user_id, $source_type = 'course' ) {
        if ( ! $cert_id || ! $source_id || ! $user_id ) {
            return '';
        }

        $cert_post = get_post( $cert_id );
        if ( ! $cert_post || $cert_post->post_type !== 'sfwd-certificates' ) {
            return '';
        }

        // Get the viewing user ID (current user or the cert owner if not logged in)
        $view_user_id = get_current_user_id();
        if ( ! $view_user_id ) {
            $view_user_id = $user_id;
        }

        // Generate the nonce required by LearnDash
        // Format: course_id . cert_user_id . view_user_id
        $nonce = wp_create_nonce( $source_id . $user_id . $view_user_id );

        // Build LearnDash certificate URL
        $url = get_permalink( $cert_id );

        // Add appropriate parameters based on source type
        $args = array(
            'cert-nonce' => $nonce,
            'user'       => $user_id,
        );

        switch ( $source_type ) {
            case 'quiz':
                $args['quiz'] = $source_id;
                break;
            case 'group':
                $args['group_id'] = $source_id;
                break;
            case 'course':
            default:
                $args['course_id'] = $source_id;
                break;
        }

        $url = add_query_arg( $args, $url );

        return $url;
    }
}
