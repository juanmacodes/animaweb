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
                <a href="#minigames" class="nav-tab">Minijuegos</a>
                <a href="#mobile" class="nav-tab">Móvil y Visuales</a>
                <a href="#unreal-editor" class="nav-tab">Unreal Editor</a>
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

            <div id="minigames" class="anima-tab-content">
                <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                    <input type="hidden" name="action" value="anima_save_pro_settings">
                    <input type="hidden" name="settings_group" value="minigames">
                    <?php wp_nonce_field('anima_pro_settings_verify'); ?>

                    <div class="anima-card">
                        <h3>Whack-a-Hater</h3>
                        <p>
                            <label>Tiempo de Juego (segundos):</label><br>
                            <input type="number" name="whack_time"
                                value="<?php echo esc_attr(get_option('anima_whack_time', 30)); ?>" class="small-text">
                        </p>
                    </div>

                    <div class="anima-card">
                        <h3>Stream Runner</h3>
                        <p>
                            <label>Velocidad Inicial:</label><br>
                            <input type="number" name="runner_speed"
                                value="<?php echo esc_attr(get_option('anima_runner_speed', 5)); ?>" step="0.1"
                                class="small-text">
                        </p>
                    </div>

                    <div class="anima-card">
                        <h3>Emoji Rain</h3>
                        <p>
                            <label>Velocidad de Caída:</label><br>
                            <input type="number" name="rain_speed"
                                value="<?php echo esc_attr(get_option('anima_rain_speed', 3)); ?>" step="0.1"
                                class="small-text">
                        </p>
                    </div>

                    <button type="submit" class="button button-primary button-large">Guardar Configuración de Juegos</button>
                </form>
            </div>

            <div id="mobile" class="anima-tab-content">
                <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                    <input type="hidden" name="action" value="anima_save_pro_settings">
                    <input type="hidden" name="settings_group" value="mobile">
                    <?php wp_nonce_field('anima_pro_settings_verify'); ?>

                    <div class="anima-card">
                        <h3>Home V2 (3D Hero)</h3>
                        <p>
                            <label>Título del Hero:</label><br>
                            <input type="text" name="home_title"
                                value="<?php echo esc_attr(get_option('anima_home_title', 'ANIMA V2.0')); ?>"
                                class="regular-text">
                        </p>
                        <p>
                            <label>Subtítulo:</label><br>
                            <input type="text" name="home_subtitle"
                                value="<?php echo esc_attr(get_option('anima_home_subtitle', 'REALITY IS OBSOLETE')); ?>"
                                class="regular-text">
                        </p>
                        <p>
                            <label>URL del Modelo 3D (.glb):</label><br>
                            <input type="url" name="home_model_url"
                                value="<?php echo esc_attr(get_option('anima_home_model_url', 'https://models.readyplayer.me/64d61e9e17d0505b63025255.glb')); ?>"
                                class="large-text">
                        </p>
                    </div>

                    <div class="anima-card">
                        <h3>Configuración Visual Global</h3>
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
                        <p>
                            <label>Color de Acento (App):</label><br>
                            <input type="color" name="app_accent_color"
                                value="<?php echo esc_attr(get_option('anima_app_accent_color', '#00F0FF')); ?>">
                        </p>
                    </div>

                    <div class="anima-card">
                        <h3>Custom CSS (Global)</h3>
                        <textarea name="custom_css" rows="10"
                            style="width:100%; background:#111; color:#00FF94; font-family:monospace;"><?php echo esc_textarea(get_option('anima_custom_css')); ?></textarea>
                    </div>

                    <button type="submit" class="button button-primary button-large">Guardar Visuales</button>
                </form>
            </div>
            <div id="unreal-editor" class="anima-tab-content">
                <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                    <input type="hidden" name="action" value="anima_save_pro_settings">
                    <input type="hidden" name="settings_group" value="unreal">
                    <?php wp_nonce_field('anima_pro_settings_verify'); ?>

                    <div class="anima-card">
                        <h3>Core Assets</h3>
                        <p>
                            <label>3D Model URL (.glb / .gltf):</label><br>
                            <input type="url" name="unreal_model_url"
                                value="<?php echo esc_attr(get_option('anima_unreal_model_url', 'https://models.readyplayer.me/64d61e9e17d0505b63025255.glb')); ?>"
                                class="large-text" placeholder="https://...">
                        </p>
                        <p>
                            <label>Poster Image (Loading):</label><br>
                            <input type="url" name="unreal_poster_url"
                                value="<?php echo esc_attr(get_option('anima_unreal_poster_url', '')); ?>" class="large-text"
                                placeholder="https://...">
                        </p>
                    </div>

                    <div class="anima-card">
                        <h3>Environment & Lighting</h3>
                        <p>
                            <label>Skybox Image (HDR/Equirectangular):</label><br>
                            <input type="url" name="unreal_skybox_url"
                                value="<?php echo esc_attr(get_option('anima_unreal_skybox_url', '')); ?>" class="large-text"
                                placeholder="Leave empty for default lighting">
                        </p>
                        <div style="display:flex; gap:20px;">
                            <p>
                                <label>Exposure:</label><br>
                                <input type="number" name="unreal_exposure" step="0.1"
                                    value="<?php echo esc_attr(get_option('anima_unreal_exposure', '1.0')); ?>"
                                    class="small-text">
                            </p>
                            <p>
                                <label>Shadow Intensity:</label><br>
                                <input type="number" name="unreal_shadow_intensity" step="0.1"
                                    value="<?php echo esc_attr(get_option('anima_unreal_shadow_intensity', '1.0')); ?>"
                                    class="small-text">
                            </p>
                        </div>
                    </div>

                    <div class="anima-card">
                        <h3>Camera Control</h3>
                        <p>
                            <label>
                                <input type="checkbox" name="unreal_auto_rotate" value="1" <?php checked(get_option('anima_unreal_auto_rotate', 1), 1); ?>>
                                Enable Auto-Rotate
                            </label>
                        </p>
                        <div style="display:flex; gap:20px;">
                            <p>
                                <label>Field of View (FOV):</label><br>
                                <input type="text" name="unreal_fov"
                                    value="<?php echo esc_attr(get_option('anima_unreal_fov', '30deg')); ?>" class="small-text">
                            </p>
                            <p>
                                <label>Camera Orbit (Start):</label><br>
                                <input type="text" name="unreal_orbit"
                                    value="<?php echo esc_attr(get_option('anima_unreal_orbit', '0deg 90deg 2.5m')); ?>"
                                    class="regular-text">
                            </p>
                        </div>
                    </div>

                    <div class="anima-card">
                        <h3>Visuals</h3>
                        <p>
                            <label>Background Color (Hex):</label><br>
                            <input type="color" name="unreal_bg_color"
                                value="<?php echo esc_attr(get_option('anima_unreal_bg_color', '#000000')); ?>">
                        </p>
                        <p>
                            <label>Interaction Prompt:</label><br>
                            <select name="unreal_prompt">
                                <option value="auto" <?php selected(get_option('anima_unreal_prompt', 'auto'), 'auto'); ?>>Auto
                                </option>
                                <option value="none" <?php selected(get_option('anima_unreal_prompt', 'auto'), 'none'); ?>>None
                                </option>
                            </select>
                        </p>
                    </div>

                    <button type="submit" class="button button-primary button-large">Save Unreal Config</button>
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

        $group = isset($_POST['settings_group']) ? $_POST['settings_group'] : 'mobile';

        if ($group === 'minigames') {
            update_option('anima_whack_time', intval($_POST['whack_time']));
            update_option('anima_runner_speed', floatval($_POST['runner_speed']));
            update_option('anima_rain_speed', floatval($_POST['rain_speed']));
            $redirect_tab = 'minigames';
        } elseif ($group === 'unreal') {
            // Unreal Editor Settings
            update_option('anima_unreal_model_url', esc_url_raw($_POST['unreal_model_url']));
            update_option('anima_unreal_poster_url', esc_url_raw($_POST['unreal_poster_url']));
            update_option('anima_unreal_skybox_url', esc_url_raw($_POST['unreal_skybox_url']));
            update_option('anima_unreal_exposure', sanitize_text_field($_POST['unreal_exposure']));
            update_option('anima_unreal_shadow_intensity', sanitize_text_field($_POST['unreal_shadow_intensity']));
            update_option('anima_unreal_auto_rotate', isset($_POST['unreal_auto_rotate']) ? 1 : 0);
            update_option('anima_unreal_fov', sanitize_text_field($_POST['unreal_fov']));
            update_option('anima_unreal_orbit', sanitize_text_field($_POST['unreal_orbit']));
            update_option('anima_unreal_bg_color', sanitize_hex_color($_POST['unreal_bg_color']));
            update_option('anima_unreal_prompt', sanitize_text_field($_POST['unreal_prompt']));

            $redirect_tab = 'unreal-editor';
        } else {
            // Mobile & Visuals
            update_option('anima_enable_orb', isset($_POST['enable_orb']) ? 1 : 0);
            update_option('anima_cyberpunk_mode', isset($_POST['cyberpunk_mode']) ? 1 : 0);
            update_option('anima_custom_css', sanitize_textarea_field($_POST['custom_css']));

            update_option('anima_home_title', sanitize_text_field($_POST['home_title']));
            update_option('anima_home_subtitle', sanitize_text_field($_POST['home_subtitle']));
            update_option('anima_home_model_url', esc_url_raw($_POST['home_model_url']));
            update_option('anima_app_accent_color', sanitize_hex_color($_POST['app_accent_color']));

            $redirect_tab = 'mobile';
        }

        wp_redirect(admin_url('admin.php?page=anima-pro-panel&tab=' . $redirect_tab . '&status=saved'));
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
