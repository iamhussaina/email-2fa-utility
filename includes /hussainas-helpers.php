<?php
/**
 * Hussainas Email 2FA Helper Functions
 *
 * Contains utility/helper functions for the 2FA module.
 *
 * @package     HussainasEmail2FA
 * @subpackage  Includes
 * @since       1.0.0
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Generates a cryptographically secure 6-digit One-Time Passcode (OTP).
 *
 * @since 1.0.0
 * @return string The 6-digit OTP.
 */
function hussainas_generate_otp() {
	// Use wp_rand() for WordPress-a-safe random number generation.
	return (string) wp_rand( 100000, 999999 );
}

/**
 * Sends the 2FA email with the passcode to the user.
 *
 * @since 1.0.0
 * @param WP_User $user The user object.
 * @param string  $code The 6-digit OTP to send.
 * @return bool True on success, false on failure.
 */
function hussainas_send_2fa_email( $user, $code ) {
	if ( ! is_a( $user, 'WP_User' ) ) {
		return false;
	}

	$site_name = get_bloginfo( 'name' );
	$subject   = sprintf(
		/* translators: %s: Site Name */
		__( '[%s] Your Verification Code', 'hussainas' ),
		$site_name
	);

	$message = sprintf(
		__( 'Your one-time verification code for %s is:', 'hussainas' ),
		$site_name
	) . "\r\n\r\n";
	$message .= "<strong>" . $code . "</strong>\r\n\r\n";
	$message .= sprintf(
		/* translators: %d: Number of minutes */
		__( 'This code will expire in %d minutes.', 'hussainas' ),
		(int) ( HUSSAINAS_2FA_EXPIRY_TIME / 60 )
	) . "\r\n";
	$message .= __( 'If you did not request this code, you can safely ignore this email.', 'hussainas' ) . "\r\n";

	// Set content type to HTML for the <strong> tag.
	$headers = array( 'Content-Type: text/html; charset=UTF-8' );

	return wp_mail( $user->user_email, $subject, $message, $headers );
}

/**
 * Clears all 2FA user meta keys for a user.
 * Used after successful login or code expiry.
 *
 * @since 1.0.0
 * @param int $user_id The user's ID.
 */
function hussainas_clear_2fa_meta( $user_id ) {
	delete_user_meta( $user_id, '_hussainas_2fa_code' );
	delete_user_meta( $user_id, '_hussainas_2fa_expiry' );
	delete_user_meta( $user_id, '_hussainas_2fa_pending' );
}

/**
 * Safely gets the user object from the login form POST data.
 *
 * @since 1.0.0
 * @return WP_User|false The user object if found, otherwise false.
 */
function hussainas_get_user_from_post() {
	if ( isset( $_POST['log'] ) ) {
		$user = get_user_by( 'login', sanitize_user( $_POST['log'] ) );
		if ( $user ) {
			return $user;
		}
	}
	return false;
}
