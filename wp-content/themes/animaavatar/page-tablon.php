<?php
/**
 * Template Name: Tablón de Comunidad
 */
get_header();

// Procesar paginación
$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;

$args = [
    'post_type' => 'anima_feed',
    'post_status' => 'publish',
    'posts_per_page' => 15,
    'paged' => $paged,
    'orderby' => 'date',
    'order' => 'DESC'
];
$feed_query = new WP_Query($args);
?>

<section class="section container" style="padding-top:40px; padding-bottom:80px;">
    
    <div class="anima-feed-layout">
        
        <aside class="anima-feed-sidebar">
            <div class="anima-card sticky-card">
                <h3 class="anima-card-title">Transmisión al Nexus</h3>
                
                <?php if(is_user_logged_in()): ?>
                    <form action="<?php echo admin_url('admin-post.php'); ?>" method="POST" class="anima-feed-form">
                        <input type="hidden" name="action" value="anima_save_feed_post">
                        <?php wp_nonce_field('anima_feed_nonce'); ?>
                        
                        <div class="field-group">
                            <label>Tipo de mensaje</label>
                            <select name="feed_type" id="feed_type_selector">
                                <option value="general">💬 Mensaje General</option>
                                <option value="duda">❓ Tengo una Duda</option>
                                <option value="idea">💡 Tengo una Idea</option>
                                <option value="evento">📅 Crear Evento / Live</option>
                            </select>
                        </div>

                        <div class="field-group">
                            <input type="text" name="feed_title" placeholder="Título (breve)" required>
                        </div>

                        <div class="field-group">
                            <textarea name="feed_content" rows="4" placeholder="Escribe aquí tu mensaje..." required></textarea>
                        </div>

                        <div id="event_fields" style="display:none; border-top:1px solid var(--line); padding-top:15px; margin-top:15px;">
                            <div class="field-group">
                                <label>Fecha y Hora del Live</label>
                                <input type="datetime-local" name="event_date">
                            </div>
                            <div class="field-group">
                                <label>Enlace (TikTok/Twitch/YouTube)</label>
                                <input type="url" name="event_link" placeholder="https://tiktok.com/...">
                            </div>
                        </div>

                        <button type="submit" class="anima-btn full-width">Publicar</button>
                    </form>
                <?php else: ?>
                    <p class="anima-muted">Debes acceder para publicar.</p>
                    <a href="<?php echo home_url('/accede-al-metaverso/'); ?>" class="anima-btn full-width">Acceder</a>
                <?php endif; ?>
            </div>
        </aside>

        <div class="anima-feed-content">
            <header class="feed-header">
                <h1>Centro de Operaciones</h1>
                <p class="anima-muted">Novedades, eventos en vivo y debates de la agencia.</p>
            </header>

            <?php if($feed_query->have_posts()): ?>
                <div class="feed-list">
                <?php while($feed_query->have_posts()): $feed_query->the_post(); 
                    $pid = get_the_ID();
                    $author_id = get_the_author_meta('ID');
                    $type = get_post_meta($pid, '_anima_feed_type', true);
                    $is_event = ($type === 'evento');
                    
                    // Datos evento
                    $ev_date = get_post_meta($pid, '_anima_event_date', true);
                    $ev_link = get_post_meta($pid, '_anima_event_link', true);
                    $ev_time = $ev_date ? strtotime($ev_date) : 0;
                ?>
                    <article class="feed-card <?php echo $is_event ? 'is-event' : ''; ?>">
                        <div class="feed-card-head">
                            <div class="feed-author">
                                <?php echo get_avatar($author_id, 40); ?>
                                <div>
                                    <strong><?php the_author(); ?></strong>
                                    <span class="feed-meta"><?php echo human_time_diff(get_the_time('U'), current_time('timestamp')) . ' atrás'; ?></span>
                                </div>
                            </div>
                            <span class="feed-badge type-<?php echo esc_attr($type); ?>">
                                <?php echo anima_get_feed_type_icon($type); ?>
                            </span>
                        </div>

                        <div class="feed-card-body">
                            <h3 class="feed-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                            <div class="feed-excerpt">
                                <?php the_excerpt(); ?>
                            </div>

                            <?php if($is_event && $ev_time): ?>
                                <div class="feed-event-box">
                                    <div class="ev-date">
                                        <span class="day"><?php echo date_i18n('d', $ev_time); ?></span>
                                        <span class="month"><?php echo date_i18n('M', $ev_time); ?></span>
                                    </div>
                                    <div class="ev-info">
                                        <strong>LIVE STREAMING</strong>
                                        <span><?php echo date_i18n('l, H:i', $ev_time); ?></span>
                                        <?php if($ev_link): ?>
                                            <a href="<?php echo esc_url($ev_link); ?>" target="_blank" class="ev-join">Unirse ahora →</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="feed-card-foot">
                            <a href="<?php the_permalink(); ?>" class="feed-comments-btn">
                                💬 <?php comments_number('Responder', '1 Respuesta', '% Respuestas'); ?>
                            </a>
                        </div>
                    </article>
                <?php endwhile; ?>
                </div>

                <div class="anima-pagination">
                    <?php echo paginate_links([
                        'total' => $feed_query->max_num_pages,
                        'prev_text' => '←',
                        'next_text' => '→'
                    ]); ?>
                </div>

            <?php else: ?>
                <p class="anima-muted">Aún no hay transmisiones en el tablón.</p>
            <?php endif; wp_reset_postdata(); ?>
        </div>
    </div>
</section>

<script>
// Mostrar campos de evento si se selecciona
document.getElementById('feed_type_selector').addEventListener('change', function(e){
    const box = document.getElementById('event_fields');
    box.style.display = (e.target.value === 'evento') ? 'block' : 'none';
});
</script>

<?php get_footer(); ?>