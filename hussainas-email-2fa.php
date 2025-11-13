<?php
/**
 * Hussainas Email 2FA Utility Loader
 *
 * This file loads all necessary components for the email-based Two-Factor
 * Authentication utility and registers the required WordPress hooks.
 *
 * @package     HussainasEmail2FA
 * @version     1.0.0
 * @author      Hussain Ahmed Shrabon
 * @link        https://github.com/iamhussaina
 * @textdomain  hussainas
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Define utility constants.
define( 'HUSSAINAS_2FA_VERSION', '1.0.0' );
define( 'HUSSAINAS_2FA_PATH', __DIR__ ); // The full path to this directory.
define( 'HUSSAINAS_2FA_INCLUDES', HUSSAINAS_2FA_PATH . '/includes/' );
define( 'HUSSAINAS_2FA_EXPIRY_TIME', 5 * MINUTE_IN_SECONDS ); // 5 Minutes.

// Include required files.
require_once HUSSAINAS_2FA_INCLUDES . 'hussainas-helpers.php';
require_once HUSSAINAS_2FA_INCLUDES . 'hussainas-auth-flow.php';
require_once HUSSAINAS_2FA_INCLUDES . 'hussainas-form-mods.php';

/**
 * Registers all WordPress hooks required for the 2FA utility.
 * This function is called to initialize the entire process.
 *
 * @since 1.0.0
 */
function hussainas_load_2fa_utility_hooks() {
	// Intercept the authentication process. Priority 30 to run after default checks.
	add_filter( 'authenticate', 'hussainas_handle_authentication', 30, 3 );

	// Add the 2FA code field to the login form when needed.
	add_action( 'login_form', 'hussainas_render_2fa_field' );

	// Add custom CSS to the login head to hide fields.
	add_action( 'login_head', 'hussainas_hide_login_fields_css' );
}

// Initialize the utility.
hussainas_load_2fa_utility_hooks();
