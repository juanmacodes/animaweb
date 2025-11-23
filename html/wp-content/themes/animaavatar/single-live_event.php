<?php
/**
 * Template Name: Single Live Event
 *
 * @package Anima_Avatar_Agency
 */

get_header();

$event_id = get_the_ID();
$user_id = get_current_user_id();
$has_access = Anima_Live_Events::user_has_access($user_id, $event_id);
$stream_url = get_post_meta($event_id, '_anima_event_stream_url', true);
$product_id = get_post_meta($event_id, '_anima_event_product_id', true);
$event_date = get_post_meta($event_id, '_anima_event_date', true);

?>

<div class="anima-live-event-wrapper">
    <div class="anima-container">

        <header class="event-header">
            <h1 class="glitch-text" data-text="<?php the_title(); ?>"><?php the_title(); ?></h1>
            <div class="event-meta">
                <span class="event-date"><span class="dashicons dashicons-calendar-alt"></span>
                    <?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($event_date)); ?></span>
            </div>
        </header>

        <div class="event-content cyberpunk-border">
            <?php if ($has_access): ?>
                <!-- Stream Area -->
                <div class="stream-container">
                    <?php if ($stream_url): ?>
                        <iframe src="<?php echo esc_url($stream_url); ?>" frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen></iframe>
                    <?php else: ?>
                        <div class="stream-placeholder">
                            <h3>Stream Starting Soon...</h3>
                            <p>Please stand by.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="event-chat-placeholder">
                    <!-- Future Chat Integration -->
                    <p>Live Chat (Coming Soon)</p>
                </div>

            <?php else: ?>
                <!-- Access Denied / Buy Ticket -->
                <div class="access-denied">
                    <div class="lock-icon"><span class="dashicons dashicons-lock"></span></div>
                    <h2>Access Restricted</h2>
                    <p>This is a premium live event. You need a ticket to access the stream.</p>

                    <?php if ($product_id):
                        $product = wc_get_product($product_id);
                        if ($product):
                            ?>
                            <div class="ticket-info">
                                <h3><?php echo $product->get_name(); ?></h3>
                                <div class="ticket-price"><?php echo $product->get_price_html(); ?></div>
                                <a href="<?php echo esc_url($product->add_to_cart_url()); ?>" class="anima-btn btn-primary">
                                    Get Ticket Now
                                </a>
                            </div>
                        <?php endif; endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="event-description">
            <?php the_content(); ?>
        </div>

    </div>
</div>

<style>
    .anima-live-event-wrapper {
        padding: 50px 0;
        background: var(--bg-dark);
        min-height: 80vh;
    }

    .event-header {
        text-align: center;
        margin-bottom: 30px;
    }

    .event-header h1 {
        font-size: 3rem;
        color: #fff;
        margin-bottom: 10px;
    }

    .event-meta {
        color: var(--cyan);
        font-size: 1.2rem;
    }

    .event-content {
        background: rgba(0, 0, 0, 0.8);
        padding: 20px;
        border-radius: 10px;
        margin-bottom: 30px;
        border: 1px solid var(--purple);
        box-shadow: 0 0 20px rgba(138, 43, 226, 0.2);
    }

    .stream-container {
        position: relative;
        padding-bottom: 56.25%;
        /* 16:9 */
        height: 0;
        background: #000;
        margin-bottom: 20px;
    }

    .stream-container iframe {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
    }

    .stream-placeholder {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        text-align: center;
        color: #fff;
    }

    .access-denied {
        text-align: center;
        padding: 50px;
        color: #fff;
    }

    .lock-icon span {
        font-size: 60px;
        color: var(--purple);
    }

    .ticket-info {
        margin-top: 30px;
        padding: 20px;
        background: rgba(255, 255, 255, 0.05);
        border-radius: 10px;
        display: inline-block;
    }

    .ticket-price {
        font-size: 1.5rem;
        color: var(--cyan);
        margin: 10px 0;
        font-weight: bold;
    }

    .event-description {
        color: #ddd;
        line-height: 1.6;
        max-width: 800px;
        margin: 0 auto;
    }
</style>

<?php get_footer(); ?>