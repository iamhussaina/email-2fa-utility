<?php
/**
 * Hussainas Email 2FA Authentication Flow
 *
 * Handles the core logic of intercepting the login, sending the code,
 * and verifying the code.
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
 * Main authentication handler.
 * Intercepts the 'authenticate' hook to implement the 2FA logic.
 *
 * @since 1.0.0
 * @param WP_User|WP_Error|null $user     User object or error.
 * @param string                $username The username from the form.
 * @param string                $password The password from the form.
 * @return WP_User|WP_Error The user object on success, or WP_Error on failure/2FA.
 */
function hussainas_handle_authentication( $user, $username, $password ) {
	// Bypass if username/password are empty or already an error (e.g., from another plugin).
	if ( empty( $username ) || empty( $password ) || is_wp_error( $user ) ) {
		return $user;
	}

	// Get the user object by their login name.
	$user_obj = get_user_by( 'login', $username );

	// If user doesn't exist, $user will be a WP_Error from WordPress core. Let it pass.
	if ( ! $user_obj ) {
		return $user;
	}

	// ---
	// STEP 2: Verify the 2FA code if it was submitted.
	// ---
	if ( ! empty( $_POST['hussainas_2fa_code'] ) ) {
		return hussainas_verify_2fa_code( $user_obj, $_POST['hussainas_2fa_code'] );
	}

	// ---
	// STEP 1: Check password and initiate 2FA if correct.
	// ---
	// We check the password manually.
	$is_correct_password = wp_check_password( $password, $user_obj->user_pass, $user_obj->ID );

	if ( $is_correct_password ) {
		// Password is correct. Stop normal login and initiate 2FA.
		return hussainas_initiate_2fa_process( $user_obj );
	}

	// If password was incorrect, $user is already a WP_Error. Return it.
	return $user;
}

/**
 * Initiates the 2FA process for a user.
 * Generates, saves, and emails the code. Returns a WP_Error to stop login.
 *
 * @since 1.0.0
 * @param WP_User $user The user object.
 * @return WP_Error An error object to force the 2FA prompt.
 */
function hussainas_initiate_2fa_process( $user ) {
	$code   = hussainas_generate_otp();
	$expiry = time() + HUSSAINAS_2FA_EXPIRY_TIME;

	// Store the code and expiry time in user meta.
	// We hash the code in the database for security.
	update_user_meta( $user->ID, '_hussainas_2fa_code', wp_hash( $code ) );
	update_user_meta( $user->ID, '_hussainas_2fa_expiry', $expiry );
	update_user_meta( $user->ID, '_hussainas_2fa_pending', true ); // Flag for UI.

	// Send the email.
	hussainas_send_2fa_email( $user, $code );

	// Return a custom WP_Error. This stops the login and displays the message.
	$error = new WP_Error();
	$error->add(
		'hussainas_2fa_required',
		__( 'A verification code has been sent to your email. Please enter it below.', 'hussainas' )
	);
	return $error;
}

/**
 * Verifies the 2FA code submitted by the user.
 *
 * @since 1.0.0
 * @param WP_User $user The user object.
 * @param string  $code The 6-digit OTP from the form.
 * @return WP_User|WP_Error The user object on success, WP_Error on failure.
 */
function hussainas_verify_2fa_code( $user, $code ) {
	// Sanitize the input code.
	$code = sanitize_text_field( $code );

	// Verify the nonce.
	if ( ! isset( $_POST['_hussainas_2fa_nonce'] ) || ! wp_verify_nonce( $_POST['_hussainas_2fa_nonce'], 'hussainas_2fa_verify_action' ) ) {
		return new WP_Error( 'hussainas_nonce_fail', __( 'Security check failed. Please log in again.', 'hussainas' ) );
	}

	// Retrieve saved meta.
	$saved_hash = get_user_meta( $user->ID, '_hussainas_2fa_code', true );
	$expiry     = get_user_meta( $user->ID, '_hussainas_2fa_expiry', true );

	// Check for expiry.
	if ( time() > $expiry ) {
		hussainas_clear_2fa_meta( $user->ID ); // Clean up expired meta.
		return new WP_Error( 'hussainas_expired', __( 'The verification code has expired. Please log in again to receive a new one.', 'hussainas' ) );
	}

	// Check the code. We use wp_hash() for consistency, which is a salted hash.
	// We must re-hash the input code to compare.
	// NOTE: A simple comparison is also fine, but hashing is best practice.
	// For a simple compare: `if ( $saved_code === $code ) { ... }`
	// For a hashed compare:
	if ( wp_check_password( $code, $saved_hash ) ) {
		// Success! Clear the meta.
		hussainas_clear_2fa_meta( $user->ID );

		// Return the user object. WordPress will now log them in.
		return $user;
	} else {
		// Invalid code.
		return new WP_Error( 'hussainas_invalid_code', __( 'Invalid verification code. Please try again.', 'hussainas' ) );
	}
}
