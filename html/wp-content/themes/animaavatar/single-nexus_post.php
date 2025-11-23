<?php
/**
 * Template Name: Single Nexus Post
 * Template Post Type: nexus_post
 * Description: Plantilla para mostrar un post individual del Nexus con sus comentarios.
 */

get_header(); ?>

<div id="primary" class="content-area anima-dark-theme">
    <main id="main" class="site-main">
        <div class="anima-container nexus-single-layout">
            
            <a href="<?php echo get_permalink(get_page_by_path('nexus')); ?>" class="back-to-nexus-btn">
                ← Volver al Centro de Operaciones
            </a>

            <?php
            while ( have_posts() ) : the_post();
                $pid = get_the_ID();
                $author_id = get_the_author_meta('ID');
                $type = get_post_meta( $pid, '_anima_nexus_message_type', true );
                $likes = function_exists('anima_get_likes_count') ? anima_get_likes_count($pid) : 0;
                $liked = (is_user_logged_in() && function_exists('anima_user_has_liked')) ? anima_user_has_liked($pid, get_current_user_id()) : false;
                
                $is_admin = user_can($author_id, 'manage_options') ? 'is-admin-post' : '';
            ?>
                <article class="feed-item single-post feed-type-<?php echo esc_attr($type); ?> <?php echo $is_admin; ?>">
                    <div class="feed-header">
                        <div class="feed-author-info">
                            <?php echo get_avatar($author_id, 64); // Avatar más grande ?>
                            <div>
                                <span class="feed-author-name"><?php the_author(); ?></span>
                                <span class="feed-timestamp"><?php echo human_time_diff( get_the_time('U'), current_time('timestamp') ) . ' atrás'; ?></span>
                            </div>
                        </div>
                        <?php if($is_admin): ?><span class="admin-badge">SISTEMA</span><?php endif; ?>
                    </div>

                    <div class="feed-body">
                        <h1 class="feed-title-single"><?php the_title(); ?></h1>
                        <div class="feed-content-single">
                            <?php the_content(); ?>
                        </div>
                        
                        <?php if($type === 'evento'): 
                            $edate = get_post_meta($pid, '_anima_nexus_event_date', true);
                            $elink = get_post_meta($pid, '_anima_nexus_event_link', true);
                        ?>
                            <div class="feed-event-card">
                                <div class="ev-icon">📅</div>
                                <div class="ev-data">
                                    <strong><?php echo date_i18n('d F, H:i', strtotime($edate)); ?></strong>
                                    <?php if($elink): ?><a href="<?php echo esc_url($elink); ?>" target="_blank" class="ev-link">Unirse a la señal</a><?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="feed-actions single-actions">
                        <button class="action-btn like-btn <?php echo $liked?'liked':''; ?>" data-id="<?php echo $pid; ?>">
                            <span class="icon">♥</span> <span class="count"><?php echo $likes; ?></span> Me gusta
                        </button>
                    </div>
                </article>

                <div class="nexus-comments-section nexus-card anima-pad">
                    <h2><?php comments_number('Sin Respuestas', '1 Respuesta', '% Respuestas'); ?></h2>
                    <?php 
                    // Asegúrate de que los comentarios estén abiertos
                    if ( comments_open() || get_comments_number() ) :
                        comments_template();
                    endif;
                    ?>
                </div>

            <?php endwhile; // End of the loop. ?>

        </div>
    </main>
</div>

<script>
// AJAX Likes para el post individual (si no se carga desde el footer global)
document.addEventListener('DOMContentLoaded', function() {
    const singleLikeBtn = document.querySelector('.single-actions .like-btn');
    if (singleLikeBtn) {
        singleLikeBtn.addEventListener('click', async function(){
            if(this.classList.contains('loading')) return;
            this.classList.add('loading');
            
            const pid = this.dataset.id;
            const fd = new FormData();
            fd.append('action', 'anima_nexus_like');
            fd.append('post_id', pid);
            
            try {
                const res = await fetch('<?php echo admin_url('admin-ajax.php'); ?>', {method:'POST', body:fd});
                const data = await res.json();
                if(data.success){
                    this.querySelector('.count').innerText = data.data.count;
                    if(data.data.action === 'added') this.classList.add('liked');
                    else this.classList.remove('liked');
                }
            } catch(e){
                console.error("Error al procesar like:", e);
            }
            this.classList.remove('loading');
        });
    }
});
</script>

<?php get_footer(); ?>