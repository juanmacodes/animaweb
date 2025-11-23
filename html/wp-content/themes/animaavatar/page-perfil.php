<?php
/**
 * Template Name: Dashboard 2.0 - Neural Interface
 */
defined( 'ABSPATH' ) || exit;

if ( ! is_user_logged_in() ) {
    wp_safe_redirect( home_url( '/login/' ) );
    exit;
}

$current_user = wp_get_current_user();
$uid = $current_user->ID;

// --- 1. RECUPERACIÓN DE DATOS ---
// Estadísticas del Agente (XP, Nivel, Créditos)
$stats = function_exists('anima_get_agent_stats') ? anima_get_agent_stats($uid) : ['level'=>1, 'xp'=>0, 'credits'=>0]; 

// Cursos Comprados (Entrenamiento)
$cursos = function_exists('anima_get_user_courses') ? anima_get_user_courses($uid) : [];

// Avatar: Lógica de prioridad (Custom > Gravatar)
$avatar_url = get_avatar_url($uid, ['size' => 200]);
$custom_avatar_id = get_user_meta($uid, 'profile_picture', true);
if($custom_avatar_id) {
    $img = wp_get_attachment_image_src($custom_avatar_id, 'medium');
    if($img) $avatar_url = $img[0];
}

// Vista actual (por defecto 'dashboard')
$view = isset($_GET['view']) ? sanitize_text_field($_GET['view']) : 'dashboard';

get_header();
?>


<link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/assets/css/dashboard-v2.css?v=2.3">

<div class="anima-dashboard-v2">
    
    <nav class="dashboard-nav">
        <a href="?view=dashboard" class="nav-item <?php echo $view==='dashboard'?'active':''; ?>">
            <span class="icon dashicons dashicons-dashboard"></span> Dashboard
        </a>
        <a href="?view=ai-lab" class="nav-item <?php echo $view==='ai-lab'?'active':''; ?>">
            <span class="icon dashicons dashicons-superhero"></span> AI Lab
        </a>
        
        <a href="?view=achievements" class="nav-item <?php echo $view==='achievements'?'active':''; ?>">
            <span class="icon dashicons dashicons-awards"></span> Logros
        </a>

        <a href="?view=connections" class="nav-item <?php echo $view==='connections'?'active':''; ?>">
            <span class="icon dashicons dashicons-networking"></span> Enlaces
        </a>
        <a href="?view=recharge" class="nav-item <?php echo $view==='recharge'?'active':''; ?>">
            <span class="icon dashicons dashicons-cart"></span> Recargar
        </a>
        <a href="?view=settings" class="nav-item <?php echo $view==='settings'?'active':''; ?>">
            <span class="icon dashicons dashicons-admin-settings"></span> Ajustes
        </a>
        <a href="<?php echo wp_logout_url(home_url()); ?>" class="nav-item" style="margin-top: auto; color: var(--neon-red);">
            <span class="icon dashicons dashicons-migrate"></span> Desconectar
        </a>
    </nav>

    <div class="dashboard-content">

        <div class="agent-id-card">
            <div class="agent-avatar-wrapper">
                <div class="level-ring"></div>
                <img src="<?php echo esc_url($avatar_url); ?>" class="agent-avatar">
                <label for="file_trigger" class="anima-avatar-edit-btn" style="cursor:pointer;" title="Cambiar Foto">📷</label>
                
                <form id="upload_form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" enctype="multipart/form-data" style="display:none;">
                    <input type="hidden" name="action" value="upload_profile_picture">
                    <input type="hidden" name="profile_picture_nonce" value="<?php echo wp_create_nonce( 'upload_profile_picture_action' ); ?>">
                    <input type="file" id="file_trigger" name="profile_picture" accept="image/*" onchange="document.getElementById('upload_form').submit()">
                </form>
            </div>
            <div class="agent-details">
                <h1><?php echo esc_html($current_user->display_name); ?></h1>
                <span class="agent-role">AGENTE NIVEL <?php echo $stats['level']; ?></span>
                <span class="agent-email"><?php echo esc_html($current_user->user_email); ?></span>
            </div>
        </div>

        <?php 
        // --------------------------------------------------------
        // VISTA: DASHBOARD (RESUMEN)
        // --------------------------------------------------------
        if($view === 'dashboard'): ?>
            
            <div class="stats-grid">
                <div class="stat-card" style="--color: var(--neon-cyan);">
                    <div class="stat-icon-box"><span class="dashicons dashicons-money-alt"></span></div>
                    <div class="stat-info">
                        <h3>Créditos Disponibles</h3>
                        <div class="stat-value hud-credits"><?php echo number_format($stats['credits']); ?></div>
                    </div>
                </div>
                <div class="stat-card" style="--color: var(--neon-green);">
                    <div class="stat-icon-box"><span class="dashicons dashicons-chart-area"></span></div>
                    <div class="stat-info">
                        <h3>Sincronización (XP)</h3>
                        <div class="stat-value"><?php echo number_format($stats['xp']); ?></div>
                    </div>
                </div>
                <a href="<?php echo get_author_posts_url($uid); ?>" class="stat-card" style="--color: var(--neon-purple); text-decoration: none;">
                    <div class="stat-icon-box"><span class="dashicons dashicons-admin-site"></span></div>
                    <div class="stat-info">
                        <h3>Perfil Público</h3>
                        <div class="stat-value" style="font-size: 1.2rem;">VISUALIZAR ↗</div>
                    </div>
                </a>
            </div>

            <div class="view-container" style="margin-top: 30px;">
                <h2 class="view-title">Centro de Mando</h2>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px;">
                    
                    <div style="padding:25px; border:1px dashed var(--neon-green); border-radius:12px; background: rgba(0,255,148,0.05);">
                        <h3 style="color:#fff; margin-bottom:15px; display:flex; align-items:center; gap:10px;">
                            <span class="dashicons dashicons-id"></span> Identidad Digital
                        </h3>
                        <p style="color:var(--neon-green); font-family:monospace; font-size:0.95rem; line-height:1.6;">
                            <?php echo nl2br(esc_html(get_user_meta($uid, 'anima_ai_bio', true) ?: 'Sin datos biográficos. Ve al AI Lab para generar una identidad.')); ?>
                        </p>
                        <a href="?view=ai-lab" class="cyber-button-glitch" style="display:inline-block; margin-top:15px; padding:10px; font-size:0.8em; width:auto;">IR AL AI LAB</a>
                    </div>

                    <div style="padding:25px; border:1px solid var(--neon-cyan); border-radius:12px; background: rgba(0,240,255,0.05);">
                        <h3 style="color:#fff; margin-bottom:15px; display:flex; align-items:center; gap:10px;">
                            <span class="dashicons dashicons-welcome-learn-more"></span> Entrenamiento Activo
                        </h3>
                        
                        <?php if(!empty($cursos)): ?>
                            <div style="display:flex; flex-direction:column; gap:15px;">
                                <?php foreach($cursos as $c): ?>
                                    <a href="<?php echo esc_url($c['url']); ?>" style="display:flex; align-items:center; gap:15px; text-decoration:none; background:rgba(0,0,0,0.3); padding:10px; border-radius:8px; border:1px solid #333; transition:0.3s;" onmouseover="this.style.borderColor='var(--neon-cyan)'" onmouseout="this.style.borderColor='#333'">
                                        <img src="<?php echo esc_url($c['thumb']); ?>" style="width:60px; height:40px; object-fit:cover; border-radius:4px;">
                                        <div>
                                            <h4 style="margin:0; color:#fff; font-size:1rem;"><?php echo esc_html($c['title']); ?></h4>
                                            <span style="font-size:0.8em; color:var(--neon-cyan);">CONTINUAR ></span>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p style="color:#888;">No hay módulos de entrenamiento activos.</p>
                            <a href="<?php echo home_url('/academy/'); ?>" class="cyber-button-glitch" style="display:inline-block; margin-top:15px; padding:10px; font-size:0.8em; width:auto; background:var(--neon-cyan); color:#000;">EXPLORAR ACADEMY</a>
                        <?php endif; ?>
                    </div>

                </div>
            </div>

            <?php 
// Verificar si hay un aviso (o informe forense)
$reward_notice = get_transient( 'anima_daily_reward_notice_' . $uid );

if ( $reward_notice ) {
    // Borrar el aviso
    delete_transient( 'anima_daily_reward_notice_' . $uid );
    
    // Si es el mensaje antiguo "yes", mostramos el texto normal. Si no, mostramos el informe.
    $notice_content = ($reward_notice === 'yes') ? '<strong>¡Bonus Diario Recibido!</strong> +10 Créditos.' : $reward_notice;
    
    // Usamos un estilo rojo/código para que destaque el diagnóstico
    ?>
    <div style="background: #222; border: 2px solid red; color: #fff; padding: 20px; border-radius: 8px; margin-bottom: 30px; font-family: monospace; white-space: pre-wrap; text-align: left;">
        <?php echo $notice_content; ?>
    </div>
    <?php
}
?>


        <?php 
        // --------------------------------------------------------
        // VISTA: AI LAB (GENERADOR IA)
        // --------------------------------------------------------
        elseif($view === 'ai-lab'): ?>
            
            <div class="view-container">
                <h2 class="view-title">Laboratorio de Identidad <span style="font-size:0.6em; color:var(--neon-purple);">v2.0</span></h2>
                
                <div class="ai-tabs">
                    <button class="ai-tab active" onclick="switchAiTab('bio')">📝 Historia</button>
                    <button class="ai-tab" onclick="switchAiTab('img')">🎨 Imagen</button>
                </div>

                <div id="ai-panel-bio" class="anima-ai-layout">
                    <div class="anima-ai-form-box">
                        <form id="ai-bio-form">
                            <div class="field-group"><label>Nombre</label><input type="text" id="ai_name" required></div>
                            <div class="field-group"><label>Arquetipo</label>
                                <select id="ai_style">
                                    <option value="Cyberpunk">Cyberpunk</option>
                                    <option value="Fantasía Oscura">Fantasía Oscura</option>
                                    <option value="Sci-Fi Militar">Sci-Fi Militar</option>
                                </select>
                            </div>
                            <div class="field-group"><label>Rasgos</label><input type="text" id="ai_traits" required></div>
                            <button type="submit" class="cyber-button-glitch" id="ai_btn">Generar Bio (2 Créditos)</button>
                        </form>
                    </div>
                    
                    <div class="anima-ai-result-box">
                        <div class="ai-terminal-header">
                            <span class="term-title">TERMINAL_OUTPUT.exe</span><span style="font-size:10px; color:#00F0FF;">● ONLINE</span>
                        </div>
                        <div class="ai-content" id="ai_output">
                            <div class="ai-placeholder" id="bio_placeholder"><span class="dashicons dashicons-editor-code"></span><p>Esperando datos...</p></div>
                        </div>
                        <div class="ai-actions">
                            <button class="cyber-button-glitch" id="save_bio_btn" style="display:none; font-size:0.8em; padding:10px;">GUARDAR EN PERFIL</button>
                        </div>
                    </div>
                </div>

                <div id="ai-panel-img" class="anima-ai-layout" style="display:none;">
                    <div class="anima-ai-form-box">
                        <form id="ai-img-form">
                            <div class="field-group"><label>Estilo</label>
                                <select id="img_style">
                                    <option value="Cyberpunk Neon">Cyberpunk Neon</option>
                                    <option value="Realistic">Realista</option>
                                    <option value="Anime">Anime Futurista</option>
                                </select>
                            </div>
                            <div class="field-group"><label>Descripción</label><textarea id="img_desc" rows="4" required></textarea></div>
                            <button type="submit" class="cyber-button-glitch" id="img_btn">Generar Imagen (10 Créditos)</button>
                        </form>
                    </div>
                    <div class="anima-ai-result-box">
                        <div class="ai-placeholder" id="img_placeholder"><span class="dashicons dashicons-format-image"></span><p>La imagen aparecerá aquí...</p></div>
                        <div id="img_loading" style="display:none;"><div class="cyber-spinner"></div><p class="blink">RENDERIZANDO...</p></div>
                        <img id="generated_img" src="" style="display:none; width:100%; border-radius:4px;">
                        
                        <div class="ai-actions" id="img_actions" style="display:none; gap:10px; justify-content: flex-end;">
                            <a id="download_avatar_btn" href="#" download="avatar-anima.png" target="_blank" class="cyber-button-glitch" style="font-size:0.8em; padding:10px; text-decoration:none; background:transparent; border:1px solid var(--neon-cyan); color:var(--neon-cyan);">
                                <span class="dashicons dashicons-download"></span> DESCARGAR
                            </a>
                            <button class="cyber-button-glitch" id="save_avatar_btn" style="font-size:0.8em;">
                                <span class="dashicons dashicons-admin-users"></span> USAR COMO AVATAR
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <script>
            function switchAiTab(tab) {
                document.getElementById('ai-panel-bio').style.display = (tab==='bio')?'grid':'none';
                document.getElementById('ai-panel-img').style.display = (tab==='img')?'grid':'none';
                document.querySelectorAll('.ai-tab').forEach(t => t.classList.remove('active'));
                event.target.classList.add('active');
            }
            
            function updateVisualCredits(cost) {
                const creditCounters = document.querySelectorAll('.hud-credits');
                creditCounters.forEach(el => {
                    let current = parseInt(el.innerText.replace(/[^0-9]/g, '')); 
                    if (!isNaN(current)) {
                        el.innerText = Math.max(0, current - cost).toLocaleString(); 
                        el.style.color = '#ff5733';
                        setTimeout(() => el.style.color = '', 1000);
                    }
                });
            }
            
            // 1. GENERAR BIO
            document.getElementById('ai-bio-form').addEventListener('submit', async function(e){
                e.preventDefault();
                const btn = document.getElementById('ai_btn'); 
                const out = document.getElementById('ai_output'); 
                const ph = document.getElementById('bio_placeholder');
                const save = document.getElementById('save_bio_btn');
                
                btn.disabled = true; btn.innerText = 'Procesando (-2 Créditos)...';
                
                const fd = new FormData(); 
                fd.append('action', 'anima_generate_bio'); 
                fd.append('name', document.getElementById('ai_name').value); 
                fd.append('style', document.getElementById('ai_style').value); 
                fd.append('traits', document.getElementById('ai_traits').value);
                
                try {
                    const res = await fetch('<?php echo admin_url('admin-ajax.php'); ?>', {method:'POST', body:fd});
                    const data = await res.json();
                    if(data.success) {
                        updateVisualCredits(data.data.cost);
                        if(ph) ph.style.display = 'none';
                        let i = 0; const txt = data.data.bio; out.innerText = '';
                        function type() { if(i<txt.length){out.innerText+=txt.charAt(i); i++; setTimeout(type, 10);} else { save.style.display='inline-block'; } }
                        type();
                    } else { alert(data.data.message || 'Error'); }
                } catch(e) { console.error(e); alert('Error de red.'); }
                btn.disabled = false; btn.innerText = 'Generar Bio (2 Créditos)';
            });

            // 2. GUARDAR BIO
            document.getElementById('save_bio_btn').addEventListener('click', async function(){
                const fd = new FormData(); 
                fd.append('action', 'anima_save_ai_bio'); 
                fd.append('bio_content', document.getElementById('ai_output').innerText);
                await fetch('<?php echo admin_url('admin-ajax.php'); ?>', {method:'POST', body:fd});
                window.location.href = '?view=dashboard';
            });

            // 3. GENERAR IMAGEN
            document.getElementById('ai-img-form').addEventListener('submit', async function(e){
                e.preventDefault();
                const btn = document.getElementById('img_btn'); 
                const ph = document.getElementById('img_placeholder'); 
                const load = document.getElementById('img_loading'); 
                const img = document.getElementById('generated_img'); 
                const acts = document.getElementById('img_actions');
                
                btn.disabled = true; btn.innerText = 'Renderizando (-10 Créditos)...';
                ph.style.display = 'none'; load.style.display = 'flex'; img.style.display = 'none'; acts.style.display = 'none';
                
                const fd = new FormData(); 
                fd.append('action', 'anima_generate_avatar_img'); 
                fd.append('style', document.getElementById('img_style').value); 
                fd.append('desc', document.getElementById('img_desc').value);
                
                try {
                    const res = await fetch('<?php echo admin_url('admin-ajax.php'); ?>', {method:'POST', body:fd});
                    const data = await res.json();
                    if(data.success) {
                        updateVisualCredits(data.data.cost);
                        img.src = data.data.url; 
                        img.style.display = 'block'; 
                        acts.style.display = 'flex'; 
                        
                        // Configurar botones
                        document.getElementById('save_avatar_btn').dataset.url = data.data.url;
                        document.getElementById('download_avatar_btn').href = data.data.url;
                    } else { alert(data.data.message); ph.style.display = 'flex'; }
                } catch(e) { alert('Error'); ph.style.display = 'flex'; }
                load.style.display = 'none'; btn.disabled = false; btn.innerText = 'Generar Imagen (10 Créditos)';
            });
            
            // 4. GUARDAR AVATAR
            document.getElementById('save_avatar_btn').addEventListener('click', async function(){
                const url = this.dataset.url; if(!url) return;
                this.disabled = true; this.innerText = 'Guardando...';
                const fd = new FormData(); 
                fd.append('action', 'anima_save_generated_avatar'); 
                fd.append('image_url', url);
                try {
                    const res = await fetch('<?php echo admin_url('admin-ajax.php'); ?>', {method:'POST', body:fd});
                    const data = await res.json();
                    if(data.success) { alert('¡Avatar actualizado!'); window.location.reload(); } 
                    else { alert('Error: ' + data.data); }
                } catch(e) { alert('Error de red'); }
                this.disabled = false; this.innerText = 'Usar como Avatar';
            });
            </script>

        <?php 
        // --------------------------------------------------------
        // VISTA: RECARGA DE CRÉDITOS
        // --------------------------------------------------------
        elseif($view === 'recharge'): ?>
            
            <div class="view-container">
                <h2 class="view-title">Recarga de Suministros</h2>
                <?php $packages = function_exists('anima_get_credit_packages') ? anima_get_credit_packages() : []; ?>
                <div class="stats-grid">
                    <?php foreach($packages as $pid => $data): ?>
                        <div class="stat-card" style="--color: var(--neon-green); flex-direction:column; text-align:center;">
                            <h3 style="font-size:1.2rem; color:#fff;"><?php echo $data['title']; ?></h3>
                            <div class="stat-value" style="color:var(--neon-cyan);"><?php echo number_format($data['credits']); ?> ⚡</div>
                            
                            <?php $price_html = function_exists('anima_get_product_price_html') ? anima_get_product_price_html($pid) : ''; ?>
                            <div style="margin:10px 0; font-weight:bold; color:var(--neon-green);"><?php echo $price_html; ?></div>

                            <a href="<?php echo esc_url( home_url( '/finalizar-compra/?add-to-cart=' . $pid ) ); ?>" class="cyber-button-glitch" style="background:var(--neon-green); color:#000; padding:10px; text-decoration:none; border-radius:4px; font-weight:bold; display:block;">ADQUIRIR</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>


            <?php 
        // --------------------------------------------------------
        // VISTA: LOGROS (GAMIFICATION 2.0)
        // --------------------------------------------------------
        elseif($view === 'achievements'): ?>
            
            <div class="view-container">
                <h2 class="view-title">Matriz de Progreso</h2>
                
                <div class="ai-tabs">
                    <button class="ai-tab active" onclick="switchTab('badges')">🏅 Insignias</button>
                    <button class="ai-tab" onclick="switchTab('earn')">🚀 Guía de Ascenso</button>
                </div>

                <?php 
                $all_badges = function_exists('anima_get_system_badges') ? anima_get_system_badges() : [];
                $my_badges = (array) get_user_meta($uid, 'anima_user_badges', true);
                $mission = function_exists('anima_get_weekly_mission') ? anima_get_weekly_mission() : null;
                ?>

                <div id="panel-badges" class="tab-panel" style="display:block;">
                    <div class="achievements-grid" style="display:grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap:20px;">
                        <?php foreach($all_badges as $id => $badge): 
                            $unlocked = in_array($id, $my_badges);
                        ?>
                            <div class="achievement-card" style="
                                background: rgba(20,20,25,0.6); border: 1px solid <?php echo $unlocked ? 'var(--neon-green)' : '#333'; ?>;
                                padding: 25px; border-radius: 12px; text-align: center; position: relative; overflow: hidden;
                                opacity: <?php echo $unlocked ? '1' : '0.5'; ?>; box-shadow: <?php echo $unlocked ? '0 0 15px rgba(0,255,148,0.1)' : 'none'; ?>;
                            ">
                                <div style="font-size: 3rem; margin-bottom: 15px; filter: <?php echo $unlocked ? 'none' : 'grayscale(100%)'; ?>; transition:0.3s;">
                                    <?php echo $badge['icon']; ?>
                                </div>
                                <h3 style="color: #fff; font-size: 1.1rem; margin: 5px 0; font-family:'Rajdhani',sans-serif;"><?php echo $badge['title']; ?></h3>
                                <p style="color: #888; font-size: 0.85rem; line-height:1.4;"><?php echo $badge['desc']; ?></p>
                                
                                <?php if($unlocked): ?>
                                    <div style="margin-top:10px; color:var(--neon-cyan); font-size:0.8em; font-weight:bold; border-top:1px dashed #444; padding-top:5px;">
                                        RECOMPENSA: +<?php echo $badge['xp']; ?> XP
                                    </div>
                                    <span style="position:absolute; top:10px; right:10px; color:var(--neon-green); font-size:1.2em;">✔</span>
                                <?php else: ?>
                                    <span style="position:absolute; top:10px; right:10px; color:#444; font-size:1.2em;">🔒</span>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div id="panel-earn" class="tab-panel" style="display:none;">
                    <div class="earn-grid" style="display:grid; gap:20px;">
                        
                        <?php if ($mission): 
                             $m_progress = get_user_meta($uid, 'anima_weekly_progress_' . $mission['id'], true);
                             $m_count = $m_progress['count'] ?? 0;
                             $m_percent = ($m_count / $mission['target']) * 100;
                        ?>
                        <div class="cyber-panel" style="border-left:4px solid var(--neon-orange); display:flex; align-items:center; gap:20px; flex-wrap:wrap; background:rgba(20,20,25,0.8); padding:20px; border-radius:8px;">
                            <div style="font-size:2.5rem;">🎯</div>
                            <div style="flex-grow:1;">
                                <h3 style="color:var(--neon-orange); margin:0;">RETO SEMANAL: <?php echo esc_html($mission['title']); ?></h3>
                                <p style="color:#ccc; margin:5px 0;"><?php echo esc_html($mission['desc']); ?></p>
                                <div style="width:100%; height:6px; background:#222; border-radius:3px; margin-top:10px; overflow:hidden;">
                                    <div style="width:<?php echo $m_percent; ?>%; height:100%; background:var(--neon-orange);"></div>
                                </div>
                            </div>
                            <div style="text-align:right; min-width:120px;">
                                <span style="display:block; color:#fff; font-weight:bold; font-size:1.2rem;"><?php echo $m_count; ?> / <?php echo $mission['target']; ?></span>
                                <span style="color:var(--neon-cyan); font-size:0.9em;">+<?php echo $mission['reward_credits']; ?> Créditos</span>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="earn-list" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap:20px;">
                            
                            <div class="cyber-panel" style="display:flex; flex-direction:column; justify-content:space-between; background:rgba(20,20,25,0.8); padding:20px; border-radius:8px;">
                                <div>
                                    <div style="display:flex; justify-content:space-between; margin-bottom:10px;">
                                        <strong style="color:#fff; font-size:1.1rem;">🎓 Entrenamiento</strong>
                                        <span style="color:var(--neon-green);">+100 XP / Curso</span>
                                    </div>
                                    <p style="color:#888; font-size:0.9em;">Completa cursos en la Academia para subir de nivel rápidamente.</p>
                                </div>
                                <a href="<?php echo home_url('/academy/'); ?>" class="cyber-button-glitch" style="margin-top:15px; text-align:center; font-size:0.8em; text-decoration:none; display:block;">IR A ACADEMY</a>
                            </div>

                            <div class="cyber-panel" style="display:flex; flex-direction:column; justify-content:space-between; background:rgba(20,20,25,0.8); padding:20px; border-radius:8px;">
                                <div>
                                    <div style="display:flex; justify-content:space-between; margin-bottom:10px;">
                                        <strong style="color:#fff; font-size:1.1rem;">💎 Suministros</strong>
                                        <span style="color:var(--neon-cyan);">+ Créditos</span>
                                    </div>
                                    <p style="color:#888; font-size:0.9em;">Adquiere paquetes para usar herramientas avanzadas de IA.</p>
                                </div>
                                <a href="?view=recharge" class="cyber-button-glitch" style="margin-top:15px; text-align:center; font-size:0.8em; text-decoration:none; display:block;">RECARGAR</a>
                            </div>

                            <div class="cyber-panel" style="display:flex; flex-direction:column; justify-content:space-between; background:rgba(20,20,25,0.8); padding:20px; border-radius:8px;">
                                <div>
                                    <div style="display:flex; justify-content:space-between; margin-bottom:10px;">
                                        <strong style="color:#fff; font-size:1.1rem;">🔗 Conexión Neuronal</strong>
                                        <span style="color:var(--neon-purple);">Insignia Especial</span>
                                    </div>
                                    <p style="color:#888; font-size:0.9em;">Conecta con otros agentes en el Nexus. Tu primer amigo desbloquea una insignia.</p>
                                </div>
                                <a href="<?php echo home_url('/comunidad-2/'); ?>" class="cyber-button-glitch" style="margin-top:15px; text-align:center; font-size:0.8em; text-decoration:none; display:block;">BUSCAR AGENTES</a>
                            </div>

                        </div>
                    </div>
                </div>

            </div>

            <script>
            function switchTab(tabName) {
                document.querySelectorAll('.tab-panel').forEach(el => el.style.display = 'none');
                document.querySelectorAll('.ai-tab').forEach(el => el.classList.remove('active'));
                document.getElementById('panel-' + tabName).style.display = 'block';
                event.target.classList.add('active');
            }
            </script>




        <?php 
        // --------------------------------------------------------
        // VISTA: CONEXIONES
        // --------------------------------------------------------
        elseif($view === 'connections'): ?>
            
            <div class="view-container">
                <h2 class="view-title">Enlaces Neuronales</h2>
                <?php echo do_shortcode('[anima_connections_v2]'); ?>
            </div>
            
        <?php 
        // --------------------------------------------------------
        // VISTA: AJUSTES
        // --------------------------------------------------------
        elseif($view === 'settings'): ?>
        
            <div class="view-container">
                <h2 class="view-title">Ajustes del Sistema</h2>
                <p style="color:#888;">Próximamente: Configuración de redes sociales y preferencias.</p>
            </div>

        <?php endif; ?>

    </div>
</div>

<?php get_footer(); ?>