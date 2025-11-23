<?php
/**
 * Template Name: Modern Login
 *
 * @package Anima_Avatar_Agency
 */

if (is_user_logged_in()) {
    wp_redirect(home_url('/perfil/'));
    exit;
}

get_header();
?>

<div class="anima-login-wrapper">
    <?php
    /**
     * Template Name: Modern Login
     *
     * @package Anima_Avatar_Agency
     */

    if (is_user_logged_in()) {
        wp_redirect(home_url('/perfil/'));
        exit;
    }

    get_header();
    ?>

    <div class="anima-login-wrapper">
        <div class="anima-login-container glass-panel">
            <div class="login-header">
                <h1 class="glitch-text" data-text="ACCESS_GRANTED">SYSTEM_LOGIN</h1>
                <p class="login-subtitle">Enter the Metaverse</p>
            </div>

            <div class="auth-forms-container">
                <!-- Login Form -->
                <div id="anima-login-box" class="auth-box active">
                    <div class="login-form-box">
                        <?php
                        $args = array(
                            'echo' => true,
                            'redirect' => home_url('/perfil/'),
                            'form_id' => 'anima-login-form',
                            'label_username' => __('Username / Email'),
                            'label_password' => __('Password'),
                            'label_remember' => __('Remember Me'),
                            'label_log_in' => __('INITIALIZE LINK'),
                            'id_username' => 'user_login',
                            'id_password' => 'user_pass',
                            'id_remember' => 'rememberme',
                            'id_submit' => 'wp-submit',
                            'remember' => true,
                            'value_username' => '',
                            'value_remember' => false
                        );
                        wp_login_form($args);
                        ?>
                    </div>
                    <div class="login-footer">
                        <a href="#" id="toggle-register" class="register-link">Create New Identity</a>
                        <span class="separator">|</span>
                        <a href="<?php echo wp_lostpassword_url(); ?>" class="lost-pass-link">Reset Credentials</a>
                    </div>
                </div>

                <!-- Registration Form -->
                <div id="anima-register-box" class="auth-box" style="display: none;">
                    <form method="post" id="anima-register-form" action="">
                        <?php
                        $reg_errors = get_transient('anima_register_errors');
                        if ($reg_errors) {
                            echo '<div class="anima-errors">';
                            foreach ($reg_errors as $error) {
                                echo '<p>' . esc_html($error) . '</p>';
                            }
                            echo '</div>';
                            delete_transient('anima_register_errors');
                        }
                        ?>
                        <p class="login-username">
                            <label for="anima_username">Username</label>
                            <input type="text" name="anima_username" id="anima_username" class="input" value=""
                                size="20" required />
                        </p>
                        <p class="login-email">
                            <label for="anima_email">Email</label>
                            <input type="email" name="anima_email" id="anima_email" class="input" value="" size="20"
                                required />
                        </p>
                        <p class="login-password">
                            <label for="anima_password">Password</label>
                            <input type="password" name="anima_password" id="anima_password" class="input" value=""
                                size="20" required />
                        </p>
                        <?php wp_nonce_field('anima_register_action', 'anima_register_nonce'); ?>
                        <p class="login-submit">
                            <input type="submit" name="wp-submit" id="wp-submit-register" class="button button-primary"
                                value="ESTABLISH UPLINK" />
                        </p>
                    </form>
                    <div class="login-footer">
                        <a href="#" id="toggle-login" class="register-link">Return to Login</a>
                    </div>
                </div>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const loginBox = document.getElementById('anima-login-box');
                    const registerBox = document.getElementById('anima-register-box');
                    const toggleRegister = document.getElementById('toggle-register');
                    const toggleLogin = document.getElementById('toggle-login');
                    const title = document.querySelector('.glitch-text');
                    const subtitle = document.querySelector('.login-subtitle');

                    toggleRegister.addEventListener('click', function (e) {
                        e.preventDefault();
                        loginBox.style.display = 'none';
                        registerBox.style.display = 'block';
                        title.setAttribute('data-text', 'NEW_IDENTITY');
                        title.textContent = 'SYSTEM_REGISTER';
                        subtitle.textContent = 'Join the Metaverse';
                    });

                    toggleLogin.addEventListener('click', function (e) {
                        e.preventDefault();
                        registerBox.style.display = 'none';
                        loginBox.style.display = 'block';
                        title.setAttribute('data-text', 'ACCESS_GRANTED');
                        title.textContent = 'SYSTEM_LOGIN';
                        subtitle.textContent = 'Enter the Metaverse';
                    });
                });
            </script>
        </div>

        <!-- Background Particles or Effect -->
        <div class="login-bg-effect"></div>
    </div>

    <?php get_footer(); ?>