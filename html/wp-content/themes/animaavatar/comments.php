<?php
/**
 * The template for displaying Comments.
 *
 * The area of the page that contains both current comments
 * and the comment form.
 *
 * @package WordPress
 * @subpackage YourThemeName
 */

/*
 * If the current post is protected by a password and
 * the visitor has not yet entered the password we will
 * return early.
 */
if ( post_password_required() )
    return;
?>

<div id="comments" class="comments-area">

    <?php // You can start editing here -- including this comment! ?>

    <?php if ( have_comments() ) : ?>
        <h3 class="comments-title">
            <?php
                printf( _nx( 'Una respuesta a &ldquo;%2$s&rdquo;', '%1$s respuestas a &ldquo;%2$s&rdquo;', get_comments_number(), 'comments title', 'your-theme-text-domain' ),
                    number_format_i18n( get_comments_number() ), '<span>' . get_the_title() . '</span>' );
            ?>
        </h3>

        <ol class="comment-list">
            <?php
                wp_list_comments( array(
                    'style'      => 'ol',
                    'short_ping' => true,
                    'avatar_size'=> 60,
                    'callback'   => 'anima_nexus_comment_callback', // Usaremos una función personalizada para el estilo
                ) );
            ?>
        </ol><?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : // Are there comments to navigate through? ?>
        <nav id="comment-nav-below" class="navigation comment-navigation" role="navigation">
            <h1 class="screen-reader-text"><?php _e( 'Comment navigation', 'your-theme-text-domain' ); ?></h1>
            <div class="nav-previous"><?php previous_comments_link( __( '&larr; Older Comments', 'your-theme-text-domain' ) ); ?></div>
            <div class="nav-next"><?php next_comments_link( __( 'Newer Comments &rarr;', 'your-theme-text-domain' ) ); ?></div>
        </nav><?php endif; // check for comment navigation ?>

    <?php endif; // have_comments() ?>

    <?php
        // If comments are closed and there are comments, let's leave a little note, shall we?
        if ( ! comments_open() && '0' != get_comments_number() && post_type_supports( get_post_type(), 'comments' ) ) :
    ?>
        <p class="no-comments"><?php _e( 'Comments are closed.', 'your-theme-text-domain' ); ?></p>
    <?php endif; ?>

    <?php comment_form(); ?>

</div>
