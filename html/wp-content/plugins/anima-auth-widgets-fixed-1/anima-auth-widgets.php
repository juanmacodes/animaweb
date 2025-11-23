<?php
/**
 * Plugin Name: Anima Auth Widgets
 * Description: Widgets de Elementor y shortcodes para Login y Registro (compatibles con WooCommerce).
 * Version: 1.0.1
 * Author: Anima Avatar Agency
 * Text Domain: anima-auth-widgets
 */

if ( ! defined('ABSPATH') ) exit;

/**
 * Assets
 */
add_action('wp_enqueue_scripts', function(){
    $base = plugin_dir_url(__FILE__) . 'assets/';
    wp_register_style('anima-auth-widgets', $base . 'auth.css', [], '1.0.1');
    wp_enqueue_style('anima-auth-widgets');
});

/**
 * Helpers
 */
function anima_auth_redirect_url(){
    $redirect = isset($_REQUEST['redirect_to']) ? esc_url_raw($_REQUEST['redirect_to']) : '';
    if ($redirect){
        $home = home_url();
        if (stripos($redirect, $home) === 0) return $redirect;
    }
    if (function_exists('wc_get_page_permalink')){
        return wc_get_page_permalink('myaccount');
    }
    return home_url('/');
}

// Clave segura para errores sin depender de session_id()
function anima_err_key(){
    $u = is_user_logged_in() ? 'u'.get_current_user_id() : 'g';
    $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
    $ua = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
    return 'anima_reg_err_' . md5($u.'|'.$ip.'|'.$ua);
}

/**
 * Procesado del registro (no-AJAX, PRG)
 */
function anima_handle_registration_post(){
    if ( 'POST' !== $_SERVER['REQUEST_METHOD'] ) return;
    if ( empty($_POST['anima_register_action']) ) return;
    if ( ! isset($_POST['_wpnonce']) || ! wp_verify_nonce($_POST['_wpnonce'], 'anima_register') ){
        wp_die(__('Nonce inválido, recarga la página e inténtalo de nuevo.', 'anima-auth-widgets'));
    }

    $username = sanitize_user( isset($_POST['anima_user_login']) ? $_POST['anima_user_login'] : '' );
    $email    = sanitize_email( isset($_POST['anima_user_email']) ? $_POST['anima_user_email'] : '' );
    $pass     = isset($_POST['anima_user_pass'])  ? $_POST['anima_user_pass']  : '';
    $pass2    = isset($_POST['anima_user_pass2']) ? $_POST['anima_user_pass2'] : '';

    $errors = new WP_Error();

    if ( empty($username) ) $errors->add('username', __('El nombre de usuario es obligatorio.', 'anima-auth-widgets'));
    if ( empty($email) || ! is_email($email) ) $errors->add('email', __('El email no es válido.', 'anima-auth-widgets'));
    if ( username_exists($username) ) $errors->add('username_exists', __('Ese usuario ya existe.', 'anima-auth-widgets'));
    if ( email_exists($email) ) $errors->add('email_exists', __('Ese email ya está registrado.', 'anima-auth-widgets'));
    if ( strlen($pass) < 6 ) $errors->add('pass_short', __('La contraseña debe tener al menos 6 caracteres.', 'anima-auth-widgets'));
    if ( $pass !== $pass2 ) $errors->add('pass_mismatch', __('Las contraseñas no coinciden.', 'anima-auth-widgets'));

    if ( ! empty($errors->errors) ){
        set_transient(anima_err_key(), $errors->errors, 120);
        wp_safe_redirect( wp_get_referer() ? wp_get_referer() : home_url('/') );
        exit;
    }

    $user_id = wp_create_user( $username, $pass, $email );
    if ( is_wp_error($user_id) ){
        set_transient(anima_err_key(), $user_id->errors, 120);
        wp_safe_redirect( wp_get_referer() ? wp_get_referer() : home_url('/') );
        exit;
    }

    if ( function_exists('wc') ){
        $user = get_user_by('id', $user_id);
        if ( $user ) $user->set_role('customer');
    }

    wp_set_current_user($user_id);
    wp_set_auth_cookie($user_id, true);
    do_action('wp_login', $username, get_user_by('id', $user_id));

    wp_safe_redirect( anima_auth_redirect_url() );
    exit;
}
add_action('init', 'anima_handle_registration_post');

/**
 * Shortcodes
 */
function anima_login_form_sc($atts=array()){
    $args = array(
        'echo'           => false,
        'redirect'       => anima_auth_redirect_url(),
        'remember'       => true,
        'label_username' => __('Usuario o email', 'anima-auth-widgets'),
        'label_password' => __('Contraseña', 'anima-auth-widgets'),
        'label_remember' => __('Recordarme', 'anima-auth-widgets'),
        'label_log_in'   => __('Entrar', 'anima-auth-widgets'),
    );
    $html = wp_login_form($args);
    $lost = wp_lostpassword_url();
    $html .= '<p class="anima-auth-links"><a href="'.esc_url($lost).'">'.esc_html__('¿Has olvidado tu contraseña?', 'anima-auth-widgets').'</a></p>';
    return '<div class="anima-auth anima-login">'.$html.'</div>';
}
add_shortcode('anima_login_form', 'anima_login_form_sc');

function anima_register_form_sc($atts=array()){
    $err = get_transient(anima_err_key());
    if ($err){
        delete_transient(anima_err_key());
    }
    ob_start(); ?>
    <div class="anima-auth anima-register">
        <?php if (!empty($err)): ?>
            <div class="anima-auth-errors">
                <ul>
                    <?php foreach ($err as $msgs): foreach ($msgs as $msg): ?>
                        <li><?php echo esc_html($msg); ?></li>
                    <?php endforeach; endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        <form method="post" class="anima-register-form">
            <?php wp_nonce_field('anima_register'); ?>
            <input type="hidden" name="anima_register_action" value="1"/>
            <div class="field">
                <label><?php _e('Nombre de usuario','anima-auth-widgets'); ?></label>
                <input type="text" name="anima_user_login" required>
            </div>
            <div class="field">
                <label><?php _e('Email','anima-auth-widgets'); ?></label>
                <input type="email" name="anima_user_email" required>
            </div>
            <div class="field">
                <label><?php _e('Contraseña','anima-auth-widgets'); ?></label>
                <input type="password" name="anima_user_pass" minlength="6" required>
            </div>
            <div class="field">
                <label><?php _e('Repite la contraseña','anima-auth-widgets'); ?></label>
                <input type="password" name="anima_user_pass2" minlength="6" required>
            </div>
            <div class="actions">
                <button type="submit" class="button"><?php _e('Crear cuenta','anima-auth-widgets'); ?></button>
            </div>
        </form>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('anima_register_form', 'anima_register_form_sc');

/**
 * Elementor widgets
 */
add_action('elementor/elements/categories_registered', function($elements_manager){
    if ( method_exists($elements_manager, 'add_category') ) {
        $elements_manager->add_category('anima', array(
            'title' => __('Anima', 'anima-auth-widgets'),
            'icon'  => 'fa fa-user'
        ));
    }
});

add_action('elementor/widgets/register', function($widgets_manager){
    if ( ! did_action('elementor/loaded') ) return;
    require_once __DIR__ . '/widgets/class-login-widget.php';
    require_once __DIR__ . '/widgets/class-register-widget.php';
    require_once __DIR__ . '/widgets/class-access-combined-widget.php';
    if ( method_exists($widgets_manager, 'register') ) {
        $widgets_manager->register( new \Anima_Login_Widget() );
        $widgets_manager->register( new \Anima_Register_Widget() );
        $widgets_manager->register( new \Anima_Access_Combined_Widget() );
    }
});
