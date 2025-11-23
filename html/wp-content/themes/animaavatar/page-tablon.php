<?php
/**
 * Template Name: Nexus - Centro de Operaciones (Masonry Stable V4)
 * Template Post Type: page
 */

get_header();

// --- LÓGICA DE PUBLICACIÓN (BACKEND) ---
if ('POST' === $_SERVER['REQUEST_METHOD'] && isset($_POST['nexus_post_nonce']) && wp_verify_nonce($_POST['nexus_post_nonce'], 'create_nexus_post')) {
    if (is_user_logged_in()) {
        $new_post_id = wp_insert_post([
            'post_title' => sanitize_text_field($_POST['post_title']),
            'post_content' => wp_kses_post($_POST['post_content']),
            'post_status' => 'publish',
            'post_type' => 'nexus_post',
            'post_author' => get_current_user_id(),
        ]);
        if (!is_wp_error($new_post_id)) {
            update_post_meta($new_post_id, '_anima_nexus_message_type', sanitize_text_field($_POST['post_type']));
            if ($_POST['post_type'] === 'evento') {
                update_post_meta($new_post_id, '_anima_nexus_event_date', sanitize_text_field($_POST['event_date']));
                update_post_meta($new_post_id, '_anima_nexus_event_link', esc_url_raw($_POST['event_link']));
            }
            echo "<script>window.location.href = window.location.href;</script>";
        }
    }
}

// --- CONSULTA INICIAL (Para SEO y primera carga) ---
$filter = isset($_GET['f']) ? sanitize_text_field($_GET['f']) : 'all';
$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

$args = [
    'post_type' => 'nexus_post',
    'posts_per_page' => 12,
    'paged' => $paged,
    'orderby' => 'date',
    'order' => 'DESC',
    'post_status' => 'publish'
];

if ($filter !== 'all') {
    $args['meta_key'] = '_anima_nexus_message_type';
    $args['meta_value'] = $filter;
}

$feed_query = new WP_Query($args);
$current_user_avatar = get_avatar(get_current_user_id(), 50);

// === LÓGICA MASONRY PHP (DIVIDIR EN 3 COLUMNAS) ===
$columns = [[], [], []];
$posts = $feed_query->get_posts();
foreach ($posts as $index => $post_obj) {
    $columns[$index % 3][] = $post_obj;
}
?>

<div id="primary" class="content-area nexus-v3-container">
    <main id="main" class="site-main">

        <div class="nexus-hero-section">
            <div class="scanlines"></div>
            <div class="hero-inner">
                <h1 class="nexus-main-title" data-text="NEXUS_FEED_V3">NEXUS_FEED_V3</h1>
                <p class="nexus-tagline">CANAL DE COMUNICACIÓN ENCRIPTADO // AGENCIA ANIMA</p>
                <div class="nexus-nav-tabs" id="nexus-tabs">
                    <a href="#" data-filter="all" class="nav-tab <?php echo $filter === 'all' ? 'active' : ''; ?>">GLOBAL</a>
                    <a href="#" data-filter="general" class="nav-tab <?php echo $filter === 'general' ? 'active' : ''; ?>"
                        style="--glow: #888;">💬 CHAT</a>
                    <a href="#" data-filter="duda" class="nav-tab <?php echo $filter === 'duda' ? 'active' : ''; ?>"
                        style="--glow: #FF5733;">❓ S.O.S.</a>
                    <a href="#" data-filter="idea" class="nav-tab <?php echo $filter === 'idea' ? 'active' : ''; ?>"
                        style="--glow: #00F0FF;">💡 INTEL</a>
                    <a href="#" data-filter="evento" class="nav-tab <?php echo $filter === 'evento' ? 'active' : ''; ?>"
                        style="--glow: #BC13FE;">📅 EVENTOS</a>
                </div>
            </div>
        </div>

        <div class="nexus-layout-grid">

            <aside class="nexus-sidebar">
                <?php if (is_user_logged_in()): ?>
                    <div class="cyber-panel publish-panel">
                        <div class="panel-header">
                            <div class="led-status blink"></div>
                            <h3>NUEVA TRANSMISIÓN</h3>
                        </div>
                        <form action="" method="post" id="nexusForm">
                            <div class="publisher-profile">
                                <?php echo $current_user_avatar; ?>
                                <div class="publisher-info"><span class="agent-label">AGENTE ACTIVO</span><span
                                        class="agent-name">@<?php echo wp_get_current_user()->user_login; ?></span></div>
                            </div>
                            <div class="input-group">
                                <label>Frecuencia:</label>
                                <select name="post_type" id="postTypeSelector" class="cyber-select">
                                    <option value="general">💬 General</option>
                                    <option value="duda">❓ Duda Táctica</option>
                                    <option value="idea">💡 Compartir Intel</option>
                                    <option value="evento">📅 Evento de Red</option>
                                </select>
                            </div>
                            <input type="text" name="post_title" class="cyber-input" placeholder="Asunto..." required>
                            <textarea name="post_content" class="cyber-textarea" rows="4" placeholder="Mensaje..."
                                required></textarea>
                            <div id="eventFields" style="display:none;" class="event-fields-group">
                                <input type="datetime-local" name="event_date" class="cyber-input">
                                <input type="url" name="event_link" class="cyber-input" placeholder="Enlace Evento">
                            </div>
                            <?php wp_nonce_field('create_nexus_post', 'nexus_post_nonce'); ?>
                            <button type="submit" class="cyber-button-glitch">TRANSMITIR DATOS</button>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="cyber-panel access-denied">
                        <h3>ACCESO DENEGADO</h3>
                        <a href="<?php echo home_url('/login/'); ?>" class="cyber-button-glitch">INICIAR SESIÓN</a>
                    </div>
                <?php endif; ?>
            </aside>

            <div class="nexus-feed-stream" id="nexus-feed-stream">
                <?php if ($feed_query->have_posts()): ?>

                    <div class="nexus-masonry-fixed">
                        <?php foreach ($columns as $col_posts): ?>
                            <div class="masonry-col">
                                <?php foreach ($col_posts as $post):
                                    setup_postdata($post);
                                    $pid = get_the_ID();
                                    $author_id = get_the_author_meta('ID');
                                    $type = get_post_meta($pid, '_anima_nexus_message_type', true);
                                    $likes = function_exists('anima_get_likes_count') ? anima_get_likes_count($pid) : 0;
                                    $liked = function_exists('anima_user_has_liked') ? anima_user_has_liked($pid, get_current_user_id()) : false;
                                    $comments_num = get_comments_number();
                                    ?>
                                    <article class="holo-card type-<?php echo esc_attr($type); ?>" id="post-<?php echo $pid; ?>">
                                        <div class="card-header">
                                            <div class="author-box">
                                                <?php echo get_avatar($author_id, 45); ?>
                                                <div class="author-meta">
                                                    <span class="author-nick"><?php the_author(); ?></span>
                                                    <span
                                                        class="post-time"><?php echo human_time_diff(get_the_time('U'), current_time('timestamp')) . ' atrás'; ?></span>
                                                </div>
                                            </div>
                                            <span
                                                class="post-badge badge-<?php echo esc_attr($type); ?>"><?php echo strtoupper($type); ?></span>
                                        </div>

                                        <div class="card-content">
                                            <a href="<?php the_permalink(); ?>" class="content-link">
                                                <h3 class="post-title"><?php the_title(); ?></h3>
                                            </a>
                                            <div class="post-excerpt"><?php the_content(); ?></div>
                                            <?php if ($type === 'evento'):
                                                $edate = get_post_meta($pid, '_anima_nexus_event_date', true);
                                                ?>
                                                <div class="event-data-display">📅
                                                    <?php echo date_i18n('d M, H:i', strtotime($edate)); ?></div>
                                            <?php endif; ?>
                                        </div>

                                        <div class="card-footer">
                                            <div class="interactions">
                                                <button class="interact-btn like-btn <?php echo $liked ? 'liked' : ''; ?>"
                                                    data-id="<?php echo $pid; ?>">
                                                    <span class="icon">♥</span> <span class="count"><?php echo $likes; ?></span>
                                                </button>
                                                <button class="interact-btn comment-toggle-btn" data-id="<?php echo $pid; ?>">
                                                    <span class="icon">💬</span> <?php echo $comments_num; ?> Respuestas
                                                </button>
                                            </div>
                                        </div>

                                        <div class="comments-drawer" id="comments-<?php echo $pid; ?>" style="display:none;">
                                            <div class="comments-list-container"></div>
                                            <div class="comment-input-wrapper">
                                                <textarea class="mini-textarea" placeholder="Respuesta..."></textarea>
                                                <button class="mini-send-btn" data-id="<?php echo $pid; ?>">></button>
                                            </div>
                                        </div>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach;
                        wp_reset_postdata(); ?>
                    </div>

                    <div class="pagination-wrapper">
                        <?php echo paginate_links(['total' => $feed_query->max_num_pages, 'current' => $paged]); ?>
                    </div>

                <?php else: ?>
                    <div class="cyber-panel empty-state">
                        <h2>SIN SEÑAL</h2>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </main>
</div>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Rajdhani:wght@400;600;700&display=swap');

    :root {
        --neon-blue: #00F0FF;
        --neon-pink: #BC13FE;
        --neon-green: #00FF94;
        --neon-orange: #FF5733;
        --bg-dark: #050505;
        --panel-bg: #0F0F12;
        --border-color: #2A2A35;
    }

    .nexus-v3-container {
        background-color: var(--bg-dark);
        min-height: 100vh;
        color: #e0e0e0;
        font-family: 'Rajdhani', sans-serif;
    }

    /* === GRID FIXED MASONRY (LA SOLUCIÓN) === */
    .nexus-masonry-fixed {
        display: flex;
        /* Usamos Flexbox, NO grid */
        gap: 25px;
        /* Espacio horizontal entre columnas */
        align-items: flex-start;
        /* Alineación superior */
    }

    .masonry-col {
        flex: 1;
        /* Las 3 columnas ocupan lo mismo */
        display: flex;
        flex-direction: column;
        /* Los posts van uno debajo de otro */
        gap: 25px;
        /* Espacio vertical entre posts */
        min-width: 0;
        /* Evita desbordamientos */
    }

    /* Responsive: 1 Columna en móvil */
    @media(max-width: 900px) {
        .nexus-masonry-fixed {
            flex-direction: column;
        }
    }

    /* Resto de estilos... */
    .holo-card {
        background: rgba(20, 20, 25, 0.6);
        border: 1px solid #333;
        border-radius: 8px;
        padding: 20px;
        transition: 0.3s;
        backdrop-filter: blur(10px);
        border-top: 2px solid transparent;
        position: relative;
        z-index: 1;
    }

    .holo-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        border-color: #555;
        z-index: 10;
    }

    .nexus-hero-section {
        padding: 60px 20px;
        text-align: center;
        background: radial-gradient(circle at 50% 0%, #1a1a2e 0%, #050505 80%);
        border-bottom: 1px solid var(--border-color);
    }

    .nexus-main-title {
        font-size: 4rem;
        margin: 0;
        color: #fff;
        letter-spacing: 4px;
        text-shadow: 0 0 20px rgba(0, 240, 255, 0.6);
        font-weight: 700;
    }

    .nexus-nav-tabs {
        display: flex;
        justify-content: center;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 20px;
    }

    .nav-tab {
        padding: 10px 25px;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid var(--border-color);
        color: #888;
        text-decoration: none;
        font-weight: 700;
        text-transform: uppercase;
        transition: all 0.3s;
        border-radius: 4px;
    }

    .nav-tab:hover,
    .nav-tab.active {
        color: #fff;
        border-color: var(--glow, var(--neon-blue));
        box-shadow: 0 0 15px var(--glow, rgba(0, 240, 255, 0.3));
        background: rgba(0, 0, 0, 0.3);
    }

    .nexus-layout-grid {
        max-width: 1400px;
        margin: 0 auto;
        padding: 40px 20px;
        display: grid;
        grid-template-columns: 350px 1fr;
        gap: 30px;
    }

    @media(max-width: 900px) {
        .nexus-layout-grid {
            grid-template-columns: 1fr;
        }
    }

    .cyber-panel {
        background: var(--panel-bg);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 25px;
        margin-bottom: 20px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
    }

    .publish-panel {
        border-left: 4px solid var(--neon-pink);
    }

    .panel-header {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 20px;
        color: var(--neon-pink);
        font-weight: 700;
    }

    .led-status {
        width: 8px;
        height: 8px;
        background: var(--neon-green);
        border-radius: 50%;
        box-shadow: 0 0 5px var(--neon-green);
        animation: blink 1.5s infinite;
    }

    @keyframes blink {
        50% {
            opacity: 0.4;
        }
    }

    .publisher-profile {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 20px;
    }

    .publisher-profile img {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        border: 2px solid var(--neon-pink);
    }

    .cyber-input,
    .cyber-select,
    .cyber-textarea {
        width: 100%;
        background: #08080a;
        border: 1px solid #333;
        color: #fff;
        padding: 12px;
        margin-bottom: 12px;
        font-family: inherit;
        border-radius: 4px;
        box-sizing: border-box;
    }

    .cyber-input:focus,
    .cyber-textarea:focus {
        border-color: var(--neon-pink);
        outline: none;
        box-shadow: 0 0 10px rgba(188, 19, 254, 0.2);
    }

    .cyber-button-glitch {
        width: 100%;
        padding: 15px;
        background: var(--neon-pink);
        color: #fff;
        font-weight: 800;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        text-transform: uppercase;
        letter-spacing: 2px;
        transition: 0.3s;
    }

    .cyber-button-glitch:hover {
        background: #fff;
        color: #000;
        box-shadow: 0 0 20px var(--neon-pink);
    }

    .type-general {
        border-top-color: #888;
    }

    .type-duda {
        border-top-color: var(--neon-orange);
    }

    .type-idea {
        border-top-color: var(--neon-blue);
    }

    .type-evento {
        border-top-color: var(--neon-green);
    }

    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 15px;
    }

    .author-box {
        display: flex;
        gap: 12px;
        align-items: center;
    }

    .author-box img {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        border: 1px solid #444;
    }

    .author-nick {
        font-weight: 700;
        color: #fff;
        font-size: 1rem;
    }

    .post-time {
        font-size: 0.8rem;
        color: #777;
    }

    .post-badge {
        font-size: 0.7rem;
        padding: 4px 8px;
        background: #222;
        border-radius: 4px;
        color: #ccc;
    }

    .post-title {
        margin: 0 0 10px 0;
        font-size: 1.3rem;
        color: #fff;
    }

    .content-link {
        text-decoration: none;
    }

    .post-excerpt {
        color: #bbb;
        font-size: 0.95rem;
        line-height: 1.6;
        margin-bottom: 15px;
    }

    .event-data-display {
        background: rgba(0, 255, 148, 0.1);
        color: var(--neon-green);
        padding: 10px;
        font-weight: 700;
        font-size: 0.9rem;
        border-left: 3px solid var(--neon-green);
        margin-top: 10px;
    }

    .card-footer {
        padding-top: 15px;
        border-top: 1px solid rgba(255, 255, 255, 0.05);
        margin-top: auto;
        position: relative;
        z-index: 50;
    }

    .interactions {
        display: flex;
        gap: 15px;
    }

    .interact-btn {
        background: none;
        border: none;
        color: #888;
        cursor: pointer;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        gap: 6px;
        transition: 0.2s;
        position: relative;
        z-index: 51;
    }

    .interact-btn:hover {
        color: #fff;
    }

    .like-btn.liked {
        color: var(--neon-pink);
        text-shadow: 0 0 10px var(--neon-pink);
    }

    .comments-drawer {
        background: #0a0a0c;
        margin-top: 15px;
        padding: 15px;
        border-radius: 4px;
        border: 1px solid #222;
        position: relative;
        z-index: 50;
    }

    .cyber-comment {
        margin-bottom: 10px;
        padding-bottom: 10px;
        border-bottom: 1px solid #222;
    }

    .comment-author {
        color: var(--neon-blue);
        font-size: 0.85rem;
        margin-bottom: 2px;
        display: block;
    }

    .comment-text {
        color: #ccc;
        font-size: 0.9rem;
    }

    .comment-input-wrapper {
        display: flex;
        gap: 10px;
        margin-top: 15px;
    }

    .mini-textarea {
        flex: 1;
        background: #111;
        border: 1px solid #333;
        color: #fff;
        padding: 8px;
        border-radius: 4px;
        height: 40px;
        font-family: inherit;
    }

    .mini-send-btn {
        background: var(--neon-pink);
        color: #fff;
        border: none;
        width: 40px;
        border-radius: 4px;
        cursor: pointer;
        font-weight: 700;
    }

    .pagination-wrapper {
        text-align: center;
        margin-top: 40px;
    }

    .page-numbers {
        padding: 10px 15px;
        border: 1px solid #333;
        color: #888;
        text-decoration: none;
        margin: 0 5px;
    }

    .page-numbers.current {
        border-color: var(--neon-blue);
        color: #fff;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ajaxUrl = "<?php echo admin_url('admin-ajax.php'); ?>";
        let currentFilter = '<?php echo $filter; ?>';

        function loadTablonFeed(filter, paged = 1) {
            const container = document.getElementById('nexus-feed-stream');
            container.style.opacity = '0.5';

            const fd = new FormData();
            fd.append('action', 'anima_nexus_tablon_filter');
            fd.append('filter', filter);
            fd.append('paged', paged);

            fetch(ajaxUrl, { method: 'POST', body: fd })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        container.innerHTML = data.data.html;
                        if (data.data.pagination) {
                            // Asegurar que la paginación esté dentro del contenedor si no lo está
                            if (!container.querySelector('.pagination-wrapper')) {
                                const pagDiv = document.createElement('div');
                                pagDiv.className = 'pagination-wrapper';
                                pagDiv.innerHTML = data.data.pagination;
                                container.appendChild(pagDiv);
                            } else {
                                container.querySelector('.pagination-wrapper').innerHTML = data.data.pagination;
                            }
                        } else {
                            // Si no hay paginación, eliminar el wrapper si existe
                            const pagWrapper = container.querySelector('.pagination-wrapper');
                            if (pagWrapper) pagWrapper.remove();
                        }
                        container.style.opacity = '1';
                        currentFilter = filter;

                        // Actualizar clases activas en tabs
                        document.querySelectorAll('.nav-tab').forEach(tab => {
                            tab.classList.toggle('active', tab.dataset.filter === filter);
                        });
                    }
                })
                .catch(err => {
                    console.error(err);
                    container.style.opacity = '1';
                });
        }

        // CLICK EN TABS DE FILTRO
        document.getElementById('nexus-tabs').addEventListener('click', function (e) {
            if (e.target.classList.contains('nav-tab')) {
                e.preventDefault();
                const filter = e.target.dataset.filter;
                loadTablonFeed(filter, 1);
            }
        });

        // CLICK EN PAGINACIÓN (Delegación)
        document.getElementById('nexus-feed-stream').addEventListener('click', function (e) {
            if (e.target.closest('.page-numbers')) {
                e.preventDefault();
                const link = e.target.closest('.page-numbers').href;
                if (link) {
                    let paged = 1;
                    const match = link.match(/paged=(\d+)/) || link.match(/\/page\/(\d+)/);
                    if (match) paged = match[1];
                    loadTablonFeed(currentFilter, paged);
                }
            }
        });

        // DELEGACIÓN DE EVENTOS EXISTENTE (Likes, Comentarios)
        document.body.addEventListener('click', async function (e) {
            // LIKE
            const likeBtn = e.target.closest('.like-btn');
            if (likeBtn) {
                e.preventDefault();
                if (likeBtn.classList.contains('loading')) return;
                likeBtn.classList.add('loading');
                const fd = new FormData();
                fd.append('action', 'anima_nexus_like');
                fd.append('post_id', likeBtn.dataset.id);
                try {
                    const res = await fetch(ajaxUrl, { method: 'POST', body: fd });
                    const data = await res.json();
                    if (data.success) {
                        likeBtn.querySelector('.count').innerText = data.data.count;
                        if (data.data.action === 'added') likeBtn.classList.add('liked'); else likeBtn.classList.remove('liked');
                    }
                } catch (err) { }
                likeBtn.classList.remove('loading');
            }

            // TOGGLE COMMENTS
            const toggleBtn = e.target.closest('.comment-toggle-btn');
            if (toggleBtn) {
                e.preventDefault();
                const pid = toggleBtn.dataset.id;
                const drawer = document.getElementById(`comments-${pid}`);
                const list = drawer.querySelector('.comments-list-container');

                if (drawer.style.display === 'none' || drawer.style.display === '') {
                    drawer.style.display = 'block';
                    if (list.innerHTML.trim() === '') {
                        list.innerHTML = '<div style="color:#666; padding:10px;">Cargando...</div>';
                        try {
                            const res = await fetch(`${ajaxUrl}?action=anima_load_comments&post_id=${pid}`);
                            const data = await res.json();
                            if (data.success) list.innerHTML = data.data.html;
                        } catch (err) { list.innerHTML = 'Error.'; }
                    }
                } else {
                    drawer.style.display = 'none';
                }
            }

            // SEND COMMENT
            const sendBtn = e.target.closest('.mini-send-btn');
            if (sendBtn) {
                e.preventDefault();
                const pid = sendBtn.dataset.id;
                const drawer = document.getElementById(`comments-${pid}`);
                const input = drawer.querySelector('textarea');
                const list = drawer.querySelector('.comments-list-container');
                const txt = input.value.trim();
                if (!txt) return;

                sendBtn.disabled = true;
                const fd = new FormData();
                fd.append('action', 'anima_post_comment');
                fd.append('post_id', pid);
                fd.append('content', txt);
                try {
                    const res = await fetch(ajaxUrl, { method: 'POST', body: fd });
                    const data = await res.json();
                    if (data.success) {
                        input.value = '';
                        const resLoad = await fetch(`${ajaxUrl}?action=anima_load_comments&post_id=${pid}`);
                        const dataLoad = await resLoad.json();
                        if (dataLoad.success) list.innerHTML = dataLoad.data.html;
                        const countSpan = document.querySelector(`#post-${pid} .comment-toggle-btn .count`);
                        if (countSpan) countSpan.innerText = parseInt(countSpan.innerText) + 1;
                    }
                } catch (err) { }
                sendBtn.disabled = false;
            }
        });

        const typeSelector = document.getElementById('postTypeSelector');
        if (typeSelector) {
            typeSelector.addEventListener('change', function (e) {
                const evDiv = document.getElementById('eventFields');
                if (evDiv) evDiv.style.display = (e.target.value === 'evento') ? 'block' : 'none';
            });
        }
    });
</script>

<?php get_footer(); ?>