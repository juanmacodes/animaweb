<?php
/**
 * Plantilla individual para Tablón (anima_feed)
 */
get_header();
?>
<section class="section container" style="padding-top:40px; max-width:800px;">
    <?php while ( have_posts() ) : the_post(); 
        $type = get_post_meta(get_the_ID(), '_anima_feed_type', true);
    ?>
        <div style="margin-bottom:20px;">
            <a href="<?php echo home_url('/tablon/'); ?>" class="anima-btn ghost">← Volver al Tablón</a>
        </div>

        <article <?php post_class('anima-card feed-single'); ?> style="padding:30px;">
            <header class="feed-single-header" style="margin-bottom:20px; border-bottom:1px solid var(--line); padding-bottom:20px;">
                <div class="flex-between">
                    <div class="feed-author" style="display:flex; gap:12px; align-items:center;">
                        <?php echo get_avatar(get_the_author_meta('ID'), 50); ?>
                        <div>
                            <h1 style="font-size:24px; margin:0;"><?php the_title(); ?></h1>
                            <p class="anima-muted" style="margin:0; font-size:14px;">
                                Por <?php the_author(); ?> • <?php the_date(); ?>
                            </p>
                        </div>
                    </div>
                    <span class="feed-badge type-<?php echo esc_attr($type); ?>" style="font-weight:800;">
                        <?php echo anima_get_feed_type_icon($type); ?>
                    </span>
                </div>
            </header>

            <div class="entry-content">
                <?php the_content(); ?>
                
                <?php 
                // Si es evento, mostramos detalles
                if($type === 'evento'): 
                    $ev_date = get_post_meta(get_the_ID(), '_anima_event_date', true);
                    $ev_link = get_post_meta(get_the_ID(), '_anima_event_link', true);
                ?>
                    <div class="feed-event-box" style="margin-top:30px; background:rgba(36, 209, 255, 0.1); padding:20px; border-radius:12px; border:1px solid var(--cyan);">
                        <h3>📅 Detalles del Evento</h3>
                        <p><strong>Fecha:</strong> <?php echo date_i18n('l j F, Y - H:i', strtotime($ev_date)); ?></p>
                        <?php if($ev_link): ?>
                            <a href="<?php echo esc_url($ev_link); ?>" target="_blank" class="anima-btn">Unirse al Live</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </article>

        <div class="anima-comments-section" style="margin-top:40px;">
            <?php 
            if ( comments_open() || get_comments_number() ) {
                comments_template();
            }
            ?>
        </div>

    <?php endwhile; ?>
</section>
<?php get_footer(); ?>