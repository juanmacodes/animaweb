<?php
/**
 * Anima Pro Admin Panel
 * 
 * Panel de control centralizado para gestionar usuarios, créditos y diseño.
 */

if (!defined('ABSPATH'))
    exit;

class Anima_Pro_Panel
{

    public function __construct()
    {
        add_action('admin_menu', [$this, 'register_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        add_action('admin_post_anima_save_pro_settings', [$this, 'save_settings']);
        add_action('wp_ajax_anima_quick_update_credits', [$this, 'ajax_update_credits']);
    }

    public function register_menu()
    {
        add_menu_page(
            'Anima Pro',
            'Anima Pro',
            'manage_options',
            'anima-pro-panel',
            [$this, 'render_panel'],
            'dashicons-superhero',
            2
        );
    }

    public function enqueue_admin_assets($hook)
    {
        if ('toplevel_page_anima-pro-panel' !== $hook)
            return;

        wp_enqueue_style('anima-pro-admin', ANIMA_ENGINE_URL . 'assets/css/admin-pro.css', [], '1.0.0');
        // Inline CSS for simplicity if file doesn't exist yet
        wp_add_inline_style('anima-pro-admin', "
            .anima-pro-wrap { max-width: 1200px; margin: 20px auto; background: #1a1a1a; color: #e0e0e0; padding: 20px; border-radius: 8px; border: 1px solid #333; }
            .anima-pro-header { display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #333; padding-bottom: 20px; margin-bottom: 20px; }
            .anima-pro-header h1 { color: #00F0FF; font-family: 'Rajdhani', sans-serif; text-transform: uppercase; margin: 0; }
            .nav-tab-wrapper { border-bottom: 1px solid #333 !important; }
            .nav-tab { background: #222 !important; color: #888 !important; border: 1px solid #333 !important; border-bottom: none !important; margin-right: 5px !important; }
            .nav-tab-active { background: #1a1a1a !important; color: #00F0FF !important; border-bottom: 1px solid #1a1a1a !important; }
            .anima-tab-content { display: none; padding: 20px 0; }
            .anima-tab-content.active { display: block; }
            .anima-card { background: #222; padding: 20px; border-radius: 6px; border: 1px solid #333; margin-bottom: 20px; }
            .anima-card h3 { color: #BC13FE; margin-top: 0; }
            table.wp-list-table { background: #222; color: #ccc; border: none; }
            table.wp-list-table th { color: #00F0FF; }
            table.wp-list-table td { border-top: 1px solid #333; }
            .button-primary { background: #00F0FF !important; border-color: #00F0FF !important; color: #000 !important; text-shadow: none !important; font-weight: bold !important; }
            .button-primary:hover { background: #fff !important; }
        ");

        wp_enqueue_script('anima-pro-js', ANIMA_ENGINE_URL . 'assets/js/admin-pro.js', ['jquery'], '1.0.0', true);
        // Inline JS for tab switching
        wp_add_inline_script('anima-pro-js', "
            jQuery(document).ready(function($) {
                $('.nav-tab').click(function(e) {
                    e.preventDefault();
                    $('.nav-tab').removeClass('nav-tab-active');
                    $(this).addClass('nav-tab-active');
                    $('.anima-tab-content').removeClass('active');
                    $($(this).attr('href')).addClass('active');
                });
                
                // Quick Update Credits
                $('.update-credits-btn').click(function() {
                    var userId = $(this).data('user');
                    var credits = prompt('Nuevos créditos para usuario ID ' + userId + ':', $(this).data('current'));
                    if(credits !== null) {
                        $.post(ajaxurl, {
                            action: 'anima_quick_update_credits',
                            user_id: userId,
                            credits: credits
                        }, function(res) {
                            if(res.success) location.reload();
                            else alert('Error');
                        });
                    }
                });
            });
        ");
    }

    public function render_panel()
    {
        $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'overview';
        ?>
        <div class="wrap anima-pro-wrap">
            <div class="anima-pro-header">
                <h1>Anima Pro Control Center</h1>
                <span class="version">v1.0</span>
            </div>

            <h2 class="nav-tab-wrapper">
                <a href="#overview" class="nav-tab nav-tab-active">Visión General</a>
                <a href="#users" class="nav-tab">Usuarios y Créditos</a>
                <a href="#ai-assistants" class="nav-tab">Asistentes IA</a>
                <a href="#design" class="nav-tab">Diseño y Funciones</a>
            </h2>

            <div id="overview" class="anima-tab-content active">
                <div class="anima-card">
                    <h3>Estado del Sistema</h3>
                    <p><strong>PHP Version:</strong> <?php echo phpversion(); ?></p>
                    <p><strong>WordPress:</strong> <?php echo get_bloginfo('version'); ?></p>
                    <p><strong>Anima Engine:</strong> <?php echo Anima_Engine_Core::VERSION; ?></p>
                </div>
            </div>

            <div id="users" class="anima-tab-content">
                <div class="anima-card">
                    <h3>Gestión Rápida de Usuarios</h3>
                    <?php
                    $users = get_users(['number' => 10, 'orderby' => 'registered', 'order' => 'DESC']);
                    ?>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Usuario</th>
                                <th>Email</th>
                                <th>Créditos</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user):
                                $credits = (int) get_user_meta($user->ID, 'anima_credits', true);
                                ?>
                                <tr>
                                    <td>#<?php echo $user->ID; ?></td>
                                    <td><?php echo $user->display_name; ?></td>
                                    <td><?php echo $user->user_email; ?></td>
                                    <td><strong style="color:#00FF94;"><?php echo $credits; ?></strong></td>
                                    <td>
                                        <button class="button update-credits-btn" data-user="<?php echo $user->ID; ?>"
                                            data-current="<?php echo $credits; ?>">Editar Saldo</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <p class="description">Mostrando los últimos 10 usuarios registrados.</p>
                </div>
            </div>

            <div id="ai-assistants" class="anima-tab-content">
                <div class="anima-card">
                    <h3>Crear Nuevo Asistente</h3>
                    <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                        <input type="hidden" name="action" value="anima_save_ai_assistant">
                        <?php wp_nonce_field('anima_ai_assistant_verify'); ?>

                        <p>
                            <label>Nombre del Asistente:</label><br>
                            <input type="text" name="ai_name" class="regular-text" placeholder="Ej: Yoda Tutor" required>
                        </p>
                        <p>
                            <label>ID Único (Slug):</label><br>
                            <input type="text" name="ai_slug" class="regular-text" placeholder="Ej: yoda_tutor" required>
                        <p class="description">Usa este ID en el shortcode: <code>[anima_ai id="yoda_tutor"]</code></p>
                        </p>
                        <p>
                            <label>System Prompt (Personalidad):</label><br>
                            <textarea name="ai_prompt" rows="5" class="large-text" placeholder="Eres un maestro sabio..."
                                required></textarea>
                        </p>
                        <p>
                            <label>URL del Avatar (Imagen):</label><br>
                            <input type="url" name="ai_avatar" class="regular-text" placeholder="https://...">
                        </p>
                        <button type="submit" class="button button-primary">Guardar Asistente</button>
                    </form>
                </div>

                <div class="anima-card">
                    <h3>Asistentes Existentes</h3>
                    <?php
                    $assistants = get_option('anima_ai_assistants', []);
                    if (!empty($assistants)): ?>
                        <table class="wp-list-table widefat fixed striped">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>ID (Slug)</th>
                                    <th>Shortcode</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($assistants as $slug => $data): ?>
                                    <tr>
                                        <td><?php echo esc_html($data['name']); ?></td>
                                        <td><?php echo esc_html($slug); ?></td>
                                        <td><code>[anima_ai id="<?php echo esc_attr($slug); ?>"]</code></td>
                                        <td>
                                            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>"
                                                style="display:inline;">
                                                <input type="hidden" name="action" value="anima_delete_ai_assistant">
                                                <input type="hidden" name="ai_slug" value="<?php echo esc_attr($slug); ?>">
                                                <?php wp_nonce_field('anima_delete_ai_verify'); ?>
                                                <button type="submit" class="button button-link-delete"
                                                    onclick="return confirm('¿Borrar?');">Borrar</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>No hay asistentes creados aún.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div id="design" class="anima-tab-content">
                <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                    <input type="hidden" name="action" value="anima_save_pro_settings">
                    <?php wp_nonce_field('anima_pro_settings_verify'); ?>

                    <div class="anima-card">
                        <h3>Configuración Visual</h3>
                        <p>
                            <label>
                                <input type="checkbox" name="enable_orb" value="1" <?php checked(get_option('anima_enable_orb', 1), 1); ?>>
                                Activar Orbe Flotante (Chatbot)
                            </label>
                        </p>
                        <p>
                            <label>
                                <input type="checkbox" name="cyberpunk_mode" value="1" <?php checked(get_option('anima_cyberpunk_mode', 1), 1); ?>>
                                Forzar Modo Cyberpunk (Dark)
                            </label>
                        </p>
                    </div>

                    <div class="anima-card">
                        <h3>Custom CSS (Global)</h3>
                        <textarea name="custom_css" rows="10"
                            style="width:100%; background:#111; color:#00FF94; font-family:monospace;"><?php echo esc_textarea(get_option('anima_custom_css')); ?></textarea>
                    </div>

                    <button type="submit" class="button button-primary button-large">Guardar Cambios</button>
                </form>
            </div>
            <?php
    }

    public function save_ai_assistant()
    {
        if (!current_user_can('manage_options'))
            wp_die('No autorizado');
        check_admin_referer('anima_ai_assistant_verify');

        $name = sanitize_text_field($_POST['ai_name']);
        $slug = sanitize_title($_POST['ai_slug']);
        $prompt = sanitize_textarea_field($_POST['ai_prompt']);
        $avatar = esc_url_raw($_POST['ai_avatar']);

        $assistants = get_option('anima_ai_assistants', []);
        $assistants[$slug] = [
            'name' => $name,
            'prompt' => $prompt,
            'avatar' => $avatar
        ];

        update_option('anima_ai_assistants', $assistants);
        wp_redirect(admin_url('admin.php?page=anima-pro-panel&tab=ai-assistants&status=created'));
        exit;
    }

    public function delete_ai_assistant()
    {
        if (!current_user_can('manage_options'))
            wp_die('No autorizado');
        check_admin_referer('anima_delete_ai_verify');

        $slug = sanitize_title($_POST['ai_slug']);
        $assistants = get_option('anima_ai_assistants', []);

        if (isset($assistants[$slug])) {
            unset($assistants[$slug]);
            update_option('anima_ai_assistants', $assistants);
        }

        wp_redirect(admin_url('admin.php?page=anima-pro-panel&tab=ai-assistants&status=deleted'));
        exit;
    }

    public function save_settings()
    {
        if (!current_user_can('manage_options'))
            wp_die('No autorizado');
        check_admin_referer('anima_pro_settings_verify');

        update_option('anima_enable_orb', isset($_POST['enable_orb']) ? 1 : 0);
        update_option('anima_cyberpunk_mode', isset($_POST['cyberpunk_mode']) ? 1 : 0);
        update_option('anima_custom_css', sanitize_textarea_field($_POST['custom_css']));

        wp_redirect(admin_url('admin.php?page=anima-pro-panel&tab=design&status=saved'));
        exit;
    }

    public function ajax_update_credits()
    {
        if (!current_user_can('edit_users'))
            wp_send_json_error();

        $user_id = intval($_POST['user_id']);
        $credits = intval($_POST['credits']);

        update_user_meta($user_id, 'anima_credits', $credits);
        wp_send_json_success();
    }
}

new Anima_Pro_Panel();
