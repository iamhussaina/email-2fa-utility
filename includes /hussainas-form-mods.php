<?php
/**
 * Hussainas Email 2FA Form Modifications
 *
 * Functions to modify the wp-login.php form, adding the 2FA field
 * and hiding default fields when 2FA is required.
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
 * Renders the 2FA input field on the login form if needed.
 *
 * @since 1.0.0
 */
function hussainas_render_2fa_field() {
	$user = hussainas_get_user_from_post();

	// Show the 2FA field only if the user is pending 2FA.
	if ( $user && get_user_meta( $user->ID, '_hussainas_2fa_pending', true ) ) {
		// We must pass the log and pwd back, but hidden.
		// This is required for the 'authenticate' hook to run again.
		$username = isset( $_POST['log'] ) ? esc_attr( $_POST['log'] ) : '';
		$password = isset( $_POST['pwd'] ) ? esc_attr( $_POST['pwd'] ) : '';
		
		// 2FA Code Input Field.
		echo '<p>
			<label for="hussainas_2fa_code">' . esc_html__( 'Verification Code', 'hussainas' ) . '</label>
			<input type="text" name="hussainas_2fa_code" id="hussainas_2fa_code" class="input" value="" size="20" autofocus="autofocus" autocomplete="off" />
		</p>';

		// Hidden fields to re-submit user/pass.
		echo '<input type="hidden" name="log" value="' . $username . '" />';
		echo '<input type="hidden" name="pwd" value="' . $password . '" />';

		// Add a nonce for security.
		wp_nonce_field( 'hussainas_2fa_verify_action', '_hussainas_2fa_nonce' );
	}
}

/**
 * Adds CSS to the login head to hide default fields during 2FA.
 *
 * @since 1.0.0
 */
function hussainas_hide_login_fields_css() {
	$user = hussainas_get_user_from_post();

	// If 2FA is pending, hide the username, password, and "remember me" fields.
	if ( $user && get_user_meta( $user->ID, '_hussainas_2fa_pending', true ) ) {
		echo '
		<style type="text/css">
			#loginform > p:nth-child(1), /* Username field */
			#loginform > p:nth-child(2)  /* Password field */
			{
				display: none !important;
			}
			.forgetmenot {
				display: none !important;
			}
		</style>
		';
	}
}
