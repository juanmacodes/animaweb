<?php
/**
 * Template Name: Perfil de Usuario (Anima)
 */
defined( 'ABSPATH' ) || exit;

if ( ! is_user_logged_in() ) {
    wp_safe_redirect( home_url( '/accede-al-metaverso/' ) );
    exit;
}

$current_user = wp_get_current_user();
$uid = $current_user->ID;

// --- DATOS GENERALES ---
$cursos       = function_exists('anima_get_user_courses') ? anima_get_user_courses($uid) : [];
$level_info   = function_exists('anima_get_user_level_info') ? anima_get_user_level_info($uid) : ['level'=>1];
$user_badges  = (array) get_user_meta($uid, 'anima_user_badges', true);
$unread_count = function_exists('anima_count_unread_messages') ? anima_count_unread_messages($uid) : 0;

// Pedidos recientes (Filtrados por functions.php)
$pedidos = function_exists( 'anima_get_recent_orders' ) ? anima_get_recent_orders( $uid, 3 ) : [];

// Assets comprados (Nueva función)
$user_owned_assets = function_exists('anima_get_user_owned_assets') ? anima_get_user_owned_assets($uid) : [];

// Enlaces Woo
$orders_url    = wc_get_endpoint_url( 'orders', '', wc_get_page_permalink( 'myaccount' ) );
$downloads_url = wc_get_endpoint_url( 'downloads', '', wc_get_page_permalink( 'myaccount' ) );

// Controlador de Vistas
$view = isset($_GET['view']) ? $_GET['view'] : 'dashboard';

get_header();
?>

<section class="anima-account-wrap">
    <div class="anima-account-card">
        
        <header class="anima-account-header">
            <div class="anima-user-avatar-wrap">
                <div class="anima-user-avatar"><?php echo get_avatar( $uid, 96 ); ?></div>
                <form id="anima-avatar-form" method="post" enctype="multipart/form-data" style="display:none;">
                    <?php wp_nonce_field('anima_avatar_upload', 'anima_avatar_nonce'); ?>
                    <input type="file" name="anima_avatar_file" id="anima_avatar_file" accept="image/*" onchange="document.getElementById('anima-avatar-form').submit();">
                </form>
                <label for="anima_avatar_file" class="anima-avatar-edit-btn" title="Cambiar foto">📷</label>
            </div>
            
            <div class="anima-user-info-main">
                <h1 class="anima-user-name">
                    <?php echo esc_html( $current_user->display_name ); ?>
                    <span class="anima-level-badge">Lvl <?php echo $level_info['level']; ?></span>
                </h1>
                <p class="anima-user-mail"><?php echo esc_html( $current_user->user_email ); ?></p>
                
                <?php if($user_badges): ?>
                <div class="anima-header-badges">
                    <?php foreach(array_slice($user_badges, 0, 5) as $bid): 
                        $b_img = get_the_post_thumbnail_url($bid, 'thumbnail');
                        $b_col = get_post_meta($bid,'anima_badge_color',true) ?: '#6f65ff'; ?>
                        <div class="mini-badge" style="border-color:<?php echo $b_col; ?>"><?php if($b_img): ?><img src="<?php echo $b_img; ?>"><?php endif; ?></div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </header>

        <?php if($view === 'dashboard'): ?>
        <div class="anima-account-grid">
            
            <div class="anima-card anima-col-span-2">
                <div class="anima-quick-links-grid">
                    <a class="anima-link-card" href="?view=inbox">
                        <span class="icon">📩</span> Buzón 
                        <?php if($unread_count > 0): ?><span class="anima-notif-dot"><?php echo $unread_count; ?></span><?php endif; ?>
                    </a>
                    <a class="anima-link-card" href="?view=connections">
                        <span class="icon">👥</span> Conexiones
                    </a>
                    <a class="anima-link-card" href="?view=ai-lab">
                        <span class="icon">🧬</span> AI Lab
                    </a>
                    <a class="anima-link-card" href="<?php echo home_url('/nexus/'); ?>">
                        <span class="icon">🌍</span> Nexus
                    </a>
                    <a class="anima-link-card" href="?view=settings">
                        <span class="icon">⚙️</span> Configuración
                    </a>
                </div>
            </div>

            <div class="anima-card anima-col-span-2">
                <div class="anima-card-title">Mis Assets <span style="opacity:0.5; font-weight:400;">(Para Anima Live)</span></div>
                <div class="anima-card-body">
                    <?php if(!empty($user_owned_assets)): ?>
                        <div class="anima-assets-mini-grid">
                            <?php foreach($user_owned_assets as $asset): ?>
                                <article class="anima-asset-mini-card">
                                    <a class="anima-asset-thumb" href="<?php echo esc_url(home_url('/assets/')); ?>">
                                        <img src="<?php echo esc_url($asset['thumb']); ?>" alt="<?php echo esc_attr($asset['title']); ?>">
                                    </a>
                                    <h4 class="anima-asset-title-mini"><?php echo esc_html($asset['title']); ?></h4>
                                </article>
                            <?php endforeach; ?>
                        </div>
                        <div style="text-align:right; margin-top: 15px;">
                            <a href="<?php echo home_url('/assets/'); ?>" class="anima-btn ghost anima-btn-small">Ver Arsenal Completo →</a>
                        </div>
                    <?php else: ?>
                        <p class="anima-muted">No tienes assets desbloqueados. Explora el <a href="<?php echo home_url('/assets/'); ?>">Arsenal Digital</a>.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="anima-card anima-col-span-2 anima-bio-card">
                <div class="anima-card-title">Biografía de mi Avatar <span class="anima-badge-ia">IA</span></div>
                <div class="anima-card-body">
                    <?php 
                        $user_bio = get_user_meta($uid, 'anima_ai_bio', true);
                        if ($user_bio): 
                    ?>
                        <p class="anima-bio-content"><?php echo nl2br(esc_html($user_bio)); ?></p>
                    <?php else: ?>
                        <p class="anima-muted">Tu avatar aún no tiene una historia. Ve al <a href="?view=ai-lab">AI Lab</a> para generar una.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="anima-card anima-col-span-2">
                <div class="anima-card-title">Entrenamiento</div>
                <div class="anima-card-body">
                    <?php if(!empty($cursos)): ?>
                        <div class="anima-courses-grid">
                            <?php foreach($cursos as $c): ?>
                                <article class="anima-course-card">
                                    <a class="anima-course-thumb" href="<?php echo esc_url($c['url']); ?>"><img src="<?php echo esc_url($c['thumb']); ?>"></a>
                                    <h3 class="anima-course-title"><a href="<?php echo esc_url($c['url']); ?>"><?php echo esc_html($c['title']); ?></a></h3>
                                    <a class="anima-btn" href="<?php echo esc_url($c['url']); ?>">Ir al curso</a>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="anima-muted">No tienes cursos activos.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="anima-card anima-col-span-2 anima-collapsible-card">
                <div class="anima-card-title anima-card-toggle" data-target="#recent-orders-content">
                    Pedidos Recientes
                    <span class="toggle-icon">▼</span>
                </div>
                <div class="anima-card-body" id="recent-orders-content">
                    <?php if ( ! empty( $pedidos ) ) : ?>
                        <div class="anima-activity">
                            <?php foreach ( $pedidos as $p ) : ?>
                                <a class="anima-activity-row" href="<?php echo esc_url( $p['view_url'] ); ?>">
                                    <div class="anima-activity-left">
                                        <span class="anima-dot"></span>
                                        <div class="text-wrap">
                                            <div class="anima-activity-title">Pedido #<?php echo esc_html( $p['id'] ); ?></div>
                                            <div class="anima-activity-meta"><?php echo esc_html( $p['date'] ); ?> — <?php echo esc_html( $p['status'] ); ?></div>
                                        </div>
                                    </div>
                                    <div class="anima-activity-right">
                                        <?php echo wp_kses_post( $p['total'] ); ?>
                                        <?php if ( in_array($p['status_slug'], ['pending','draft','failed']) ) : ?>
                                            <a href="<?php echo esc_url( add_query_arg( ['action' => 'delete_order', 'order_id' => $p['id'], 'nonce' => wp_create_nonce('delete_order_' . $p['id'])] ) ); ?>"
                                                class="anima-delete-btn" onclick="return confirm('¿Eliminar este pedido?');">🗑️</a>
                                        <?php endif; ?>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php else : ?>
                        <p class="anima-muted">No hay actividad reciente.</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                document.querySelectorAll('.anima-card-toggle').forEach(function(toggle) {
                    toggle.addEventListener('click', function() {
                        const targetId = this.dataset.target;
                        const targetContent = document.querySelector(targetId);
                        const parentCard = this.closest('.anima-collapsible-card');
                        
                        if (targetContent.style.maxHeight) {
                            targetContent.style.maxHeight = null;
                            parentCard.classList.remove('is-open');
                        } else {
                            targetContent.style.maxHeight = targetContent.scrollHeight + "px";
                            parentCard.classList.add('is-open');
                        }
                    });
                    const targetContent = document.querySelector(toggle.dataset.target);
                    if (targetContent) targetContent.style.maxHeight = null;
                });
            });
            </script>
        </div>

        <?php elseif($view === 'ai-lab'): ?>
        <div class="anima-pad">
            <div class="flex-between" style="margin-bottom:20px">
                <h2>Laboratorio de Identidad (IA)</h2>
                <a href="?view=dashboard" class="anima-btn ghost">← Volver</a>
            </div>

            <div class="anima-ai-layout">
                <div class="anima-ai-form-box">
                    <p class="anima-muted">Define los parámetros y deja que la IA construya tu historia.</p>
                    
                    <form id="ai-bio-form">
                        <div class="field-group">
                            <label>Nombre del Avatar</label>
                            <input type="text" id="ai_name" placeholder="Ej: Zed, Nova, Kael..." required>
                        </div>
                        <div class="field-group">
                            <label>Estilo / Arquetipo</label>
                            <select id="ai_style">
                                <option value="Cyberpunk">Cyberpunk / Hacker</option>
                                <option value="Fantasía Oscura">Fantasía Oscura</option>
                                <option value="Sci-Fi Militar">Sci-Fi Militar</option>
                                <option value="Androide">Androide / IA</option>
                                <option value="Nómada Digital">Nómada Digital</option>
                            </select>
                        </div>
                        <div class="field-group">
                            <label>Rasgos Clave (separados por comas)</label>
                            <input type="text" id="ai_traits" placeholder="Ej: sigiloso, rebelde, experto en códigos..." required>
                        </div>
                        <button type="submit" class="anima-btn full-width" id="ai_btn">
                            <span class="btn-txt">Generar Biografía</span>
                            <span class="btn-loader" style="display:none">Procesando...</span>
                        </button>
                    </form>
                </div>

                <div class="anima-ai-result-box" id="ai_result_box" style="display:none;">
                    <div class="ai-terminal-header">
                        <span class="dot red"></span><span class="dot yellow"></span><span class="dot green"></span>
                        <span class="term-title">ANIMA_GEN_V1.exe</span>
                    </div>
                    <div class="ai-content" id="ai_output"></div>
                    
                    <div class="ai-actions">
                        <button class="anima-btn-small ghost" onclick="navigator.clipboard.writeText(document.getElementById('ai_output').innerText); alert('Copiado!')">Copiar</button>
                        <button class="anima-btn-small" id="save_bio_btn" style="display:none;">Guardar Biografía</button>
                    </div>
                </div>
            </div>

            <script>
            // Script Generar
            document.getElementById('ai-bio-form').addEventListener('submit', async function(e){
                e.preventDefault();
                const btn = document.getElementById('ai_btn');
                const box = document.getElementById('ai_result_box');
                const out = document.getElementById('ai_output');
                const saveBtn = document.getElementById('save_bio_btn');
                
                btn.disabled = true;
                btn.querySelector('.btn-txt').style.display = 'none';
                btn.querySelector('.btn-loader').style.display = 'inline-block';
                box.style.display = 'none'; out.innerHTML = '';
                saveBtn.style.display = 'none';

                const fd = new FormData();
                fd.append('action', 'anima_generate_bio');
                fd.append('nonce', '<?php echo wp_create_nonce("anima_ai_nonce"); ?>');
                fd.append('name', document.getElementById('ai_name').value);
                fd.append('style', document.getElementById('ai_style').value);
                fd.append('traits', document.getElementById('ai_traits').value);

                try {
                    const res = await fetch('<?php echo admin_url('admin-ajax.php'); ?>', {method:'POST', body:fd});
                    const data = await res.json();
                    if(data.success) {
                        box.style.display = 'block';
                        let i = 0; const txt = data.data.bio; const speed = 20; 
                        function typeWriter() { 
                            if (i < txt.length) { out.innerHTML += txt.charAt(i); i++; setTimeout(typeWriter, speed); }
                            else { saveBtn.style.display = 'inline-block'; }
                        }
                        typeWriter();
                    } else { alert('Error: ' + data.data); }
                } catch(e) { alert('Error de conexión'); }
                btn.disabled = false;
                btn.querySelector('.btn-txt').style.display = 'inline-block';
                btn.querySelector('.btn-loader').style.display = 'none';
            });

            // Script Guardar
            document.getElementById('save_bio_btn').addEventListener('click', async function(){
                const bioContent = document.getElementById('ai_output').innerText;
                const saveBtn = this;
                saveBtn.disabled = true; saveBtn.textContent = 'Guardando...';
                
                const fd = new FormData();
                fd.append('action', 'anima_save_ai_bio');
                fd.append('nonce', '<?php echo wp_create_nonce("anima_ai_nonce"); ?>');
                fd.append('bio_content', bioContent);

                try {
                    const res = await fetch('<?php echo admin_url('admin-ajax.php'); ?>', {method:'POST', body:fd});
                    const data = await res.json();
                    if(data.success) {
                        alert('¡Biografía guardada en tu perfil!');
                        window.location.href = '?view=dashboard';
                    } else { alert('Error: ' + data.data); }
                } catch(e) { alert('Error al guardar.'); }
                saveBtn.disabled = false; saveBtn.textContent = 'Guardar Biografía';
            });
            </script>
        </div>

        <?php elseif($view === 'connections'): 
            $friends = (array) get_user_meta($uid, 'anima_friends', true);
        ?>
        <div class="anima-pad">
            <div class="flex-between" style="margin-bottom:20px">
                <h2>Mis Conexiones</h2>
                <a href="?view=dashboard" class="anima-btn ghost">← Volver</a>
            </div>
            <?php if($friends): ?>
                <div class="anima-avatars-grid">
                <?php foreach($friends as $fid): 
                    $fuser = get_userdata($fid); if(!$fuser) continue;
                    $flevel = function_exists('anima_get_user_level_info') ? anima_get_user_level_info($fid) : ['level'=>1];
                ?>
                    <article class="anima-avatar-card">
                        <div class="av-header">
                            <div class="av-photo"><?php echo get_avatar($fid, 80); ?></div>
                            <div class="av-level-badge">Lvl <?php echo $flevel['level']; ?></div>
                        </div>
                        <h3 class="av-name"><?php echo esc_html($fuser->display_name); ?></h3>
                        <div class="av-actions">
                            <a href="?view=compose&to=<?php echo $fid; ?>" class="anima-btn-small active">Enviar Mensaje</a>
                        </div>
                    </article>
                <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="anima-muted">Aún no has conectado con nadie. <a href="<?php echo home_url('/comunidad/'); ?>">Explora la comunidad</a>.</p>
            <?php endif; ?>
        </div>

        <?php elseif($view === 'inbox'): 
            $msgs = get_posts([
                'post_type'=>'anima_message',
                'meta_key'=>'_anima_to_user',
                'meta_value'=>$uid,
                'posts_per_page'=>20
            ]);
        ?>
        <div class="anima-pad">
            <div class="flex-between" style="margin-bottom:20px">
                <h2>Buzón de Entrada</h2>
                <a href="?view=dashboard" class="anima-btn ghost">← Volver</a>
            </div>
            <?php if(isset($_GET['sent'])): ?><div class="anima-notice anima-notice--success">Mensaje enviado correctamente.</div><?php endif; ?>
            <?php if($msgs): ?>
                <div class="anima-msg-list">
                <?php foreach($msgs as $msg): 
                    $sender = get_userdata($msg->post_author);
                    $is_read = get_post_meta($msg->ID, '_anima_read_status', true) === 'read';
                ?>
                    <a href="?view=read&id=<?php echo $msg->ID; ?>" class="anima-msg-row <?php echo $is_read ? 'read' : 'unread'; ?>">
                        <div class="msg-avatar"><?php echo get_avatar($msg->post_author, 40); ?></div>
                        <div class="msg-content">
                            <strong class="msg-subject"><?php echo esc_html($msg->post_title); ?></strong>
                            <span class="msg-meta">De: <?php echo $sender ? esc_html($sender->display_name) : 'Desconocido'; ?> • <?php echo get_the_date('d M', $msg->ID); ?></span>
                        </div>
                        <?php if(!$is_read): ?><span class="msg-dot"></span><?php endif; ?>
                    </a>
                <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="anima-muted">No tienes mensajes nuevos.</p>
            <?php endif; ?>
        </div>

        <?php elseif($view === 'read'): 
            $mid = (int) $_GET['id'];
            $msg = get_post($mid);
            $recipient = (int) get_post_meta($mid, '_anima_to_user', true);
            if($msg && $recipient === $uid):
                update_post_meta($mid, '_anima_read_status', 'read');
                $sender = get_userdata($msg->post_author);
        ?>
        <div class="anima-pad">
            <div class="flex-between" style="margin-bottom:20px"><h2>Lectura</h2><a href="?view=inbox" class="anima-btn ghost">← Volver al buzón</a></div>
            <div class="anima-msg-reader">
                <div class="reader-head">
                    <h3><?php echo esc_html($msg->post_title); ?></h3>
                    <p class="anima-muted">De: <strong><?php echo $sender ? esc_html($sender->display_name) : 'Usuario'; ?></strong> el <?php echo get_the_date('d F, Y - H:i', $mid); ?></p>
                </div>
                <div class="reader-body"><?php echo wp_kses_post(wpautop($msg->post_content)); ?></div>
                <div class="reader-actions"><a href="?view=compose&to=<?php echo $msg->post_author; ?>&subject=Re: <?php echo urlencode($msg->post_title); ?>" class="anima-btn">Responder</a></div>
            </div>
        </div>
        <?php endif; ?>

        <?php elseif($view === 'compose'): 
            $to = isset($_GET['to']) ? (int)$_GET['to'] : 0;
            $subject = isset($_GET['subject']) ? sanitize_text_field($_GET['subject']) : '';
            $target_user = get_userdata($to);
        ?>
        <div class="anima-pad">
            <div class="flex-between" style="margin-bottom:20px"><h2>Enviar Mensaje</h2><a href="?view=dashboard" class="anima-btn ghost">Cancelar</a></div>
            <?php if($target_user): ?>
            <form action="<?php echo admin_url('admin-post.php'); ?>" method="POST" class="anima-settings-form">
                <input type="hidden" name="action" value="anima_send_msg">
                <input type="hidden" name="to_user" value="<?php echo $to; ?>">
                <?php wp_nonce_field('anima_send_msg_nonce'); ?>
                <div class="field" style="margin-bottom:15px"><label>Para:</label><input type="text" value="<?php echo esc_attr($target_user->display_name); ?>" disabled></div>
                <div class="field" style="margin-bottom:15px"><label>Asunto:</label><input type="text" name="subject" value="<?php echo esc_attr($subject); ?>" required placeholder="Asunto del mensaje..."></div>
                <div class="field" style="margin-bottom:15px"><label>Mensaje:</label><textarea name="message" rows="6" required style="width:100%;background:rgba(0,0,0,.2);border:1px solid var(--line);color:var(--ink);padding:12px;border-radius:12px"></textarea></div>
                <div style="text-align:right"><button type="submit" class="anima-btn">Enviar 🚀</button></div>
            </form>
            <?php else: ?><p>Usuario no encontrado.</p><?php endif; ?>
        </div>
        
        <?php elseif($view === 'settings'): ?>
        <div class="anima-pad">
            <div class="flex-between" style="margin-bottom:20px"><h2>Editar Perfil Social</h2><a href="?view=dashboard" class="anima-btn ghost">← Volver</a></div>
            <form action="<?php echo admin_url('admin-post.php'); ?>" method="POST" class="anima-settings-form">
                <input type="hidden" name="action" value="anima_save_profile">
                <?php wp_nonce_field('anima_save_profile_nonce'); ?>
                <div class="anima-form-grid">
                    <div class="field"><label>Instagram</label><input type="url" name="instagram" value="<?php echo esc_attr(get_user_meta($uid,'anima_social_instagram',true)); ?>"></div>
                    <div class="field"><label>TikTok</label><input type="url" name="tiktok" value="<?php echo esc_attr(get_user_meta($uid,'anima_social_tiktok',true)); ?>"></div>
                    <div class="field"><label>Twitter</label><input type="url" name="twitter" value="<?php echo esc_attr(get_user_meta($uid,'anima_social_twitter',true)); ?>"></div>
                    <div class="field"><label>LinkedIn</label><input type="url" name="linkedin" value="<?php echo esc_attr(get_user_meta($uid,'anima_social_linkedin',true)); ?>"></div>
                    <div class="field"><label>ArtStation (Portfolio)</label><input type="url" name="artstation" value="<?php echo esc_attr(get_user_meta($uid,'anima_social_artstation',true)); ?>"></div>
                    <div class="field"><label>YouTube</label><input type="url" name="youtube" value="<?php echo esc_attr(get_user_meta($uid,'anima_social_youtube',true)); ?>"></div>
                </div>
                <div style="margin-top:20px; text-align:right;"><button type="submit" class="anima-btn">Guardar</button></div>
            </form>
        </div>
        <?php endif; ?>

    </div>
</section>
<?php get_footer(); ?>