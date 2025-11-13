# WordPress Email 2FA Utility

A lightweight, procedural, and plugin-free utility to add an email-based Two-Factor Authentication (2FA) layer to the standard WordPress login form.

This is not a plugin. It is designed to be included directly within a WordPress theme (e.g., a parent or child theme) to provide enhanced security without relying on third-party plugins.

## Features

* **Plugin-Free:** No plugin management needed. Integrates directly into your theme.
* **Secure:** Adds an extra verification step. After a correct username/password entry, the user must enter a 6-digit code sent to their email.
* **Lightweight:** Minimal, procedural code that hooks into the WordPress authentication flow.
* **Clean UI:** Seamlessly replaces the username/password fields with the 2FA code field during verification.
* **Secure Storage:** Stores 2FA codes as secure, one-way hashes in the user meta.
* **Time-Sensitive:** Codes automatically expire after 5 minutes.

## How It Works

1.  A user enters their correct username and password.
2.  The utility intercepts this successful login *before* the session cookie is set.
3.  It generates a 6-digit code, hashes it, and stores it in the user's meta table with a 5-minute expiry.
4.  It sends the 6-digit code to the user's registered email address.
5.  The login form reloads, hiding the username/password fields and showing a "Verification Code" field.
6.  The user enters the code from their email.
7.  The utility verifies the code against the stored hash and checks the expiry time.
8.  If correct, all temporary data is cleared, and the user is logged in.
9.  If incorrect or expired, an error is shown, and the user must restart the login process.

## 🛠️ Installation (How to Use)

1.  **Download:** Download the `email-2fa-utility` directory.
2.  **Copy to Theme:** Place the entire `email-2fa-utility` folder into your active theme's directory (e.g., `/wp-content/themes/your-theme/email-2fa-utility`).
3.  **Include in Theme:** Open your theme's `functions.php` file and add the following line at the end:

    ```php
    // Load the Email 2FA Utility
    require_once get_template_directory() . '/email-2fa-utility/hussainas-email-2fa.php';
    ```

    *Note: If you are using a child theme, you may want to use `get_stylesheet_directory()` instead of `get_template_directory()`.*

4.  **Done:** The 2FA system is now active. Test it by trying to log in.

## 📁 File Structure
