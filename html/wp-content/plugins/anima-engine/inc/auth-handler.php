<?php
if (!defined('ABSPATH'))
    exit;

class Anima_Auth_Handler
{

    public function __construct()
    {
        add_action('init', array($this, 'handle_registration'));
    }

    public function handle_registration()
    {
        if (isset($_POST['anima_register_nonce']) && wp_verify_nonce($_POST['anima_register_nonce'], 'anima_register_action')) {

            $username = sanitize_user($_POST['anima_username']);
            $email = sanitize_email($_POST['anima_email']);
            $password = $_POST['anima_password'];

            $errors = new WP_Error();

            if (empty($username) || empty($email) || empty($password)) {
                $errors->add('field', 'Please fill in all fields.');
            }

            if (username_exists($username)) {
                $errors->add('username', 'Username already exists.');
            }

            if (email_exists($email)) {
                $errors->add('email', 'Email already exists.');
            }

            if ($errors->get_error_code()) {
                // Store errors in transient or session to display on form
                set_transient('anima_register_errors', $errors->get_error_messages(), 60);
                return;
            }

            // Create User
            $user_id = wp_create_user($username, $password, $email);

            if (!is_wp_error($user_id)) {
                // Auto Login
                wp_set_current_user($user_id);
                wp_set_auth_cookie($user_id);

                // Award Welcome Bonus (50 Credits)
                if (class_exists('Anima_Karma_System')) {
                    Anima_Karma_System::get_instance()->add_karma($user_id, 50, 'Welcome Bonus');
                }

                // Redirect to Dashboard
                wp_redirect(home_url('/perfil/'));
                exit;
            }
        }
    }
}

new Anima_Auth_Handler();
