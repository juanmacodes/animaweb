<?php
if (!defined('ABSPATH'))
    exit;

class Anima_Live_Events
{

    public function __construct()
    {
        add_action('init', array($this, 'register_cpt'));
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action('save_post', array($this, 'save_meta_data'));
    }

    public function register_cpt()
    {
        $labels = array(
            'name' => 'Live Events',
            'singular_name' => 'Live Event',
            'menu_name' => 'Live Events',
            'add_new' => 'Add Event',
            'add_new_item' => 'Add New Live Event',
            'edit_item' => 'Edit Event',
            'view_item' => 'View Event',
        );
        $args = array(
            'labels' => $labels,
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'menu_icon' => 'dashicons-video-alt3',
            'supports' => array('title', 'editor', 'thumbnail'),
            'has_archive' => true,
            'rewrite' => array('slug' => 'live-event'),
        );
        register_post_type('live_event', $args);
    }

    public function add_meta_boxes()
    {
        add_meta_box(
            'anima_event_details',
            'Event Details',
            array($this, 'render_meta_box'),
            'live_event',
            'normal',
            'high'
        );
    }

    public function render_meta_box($post)
    {
        $date = get_post_meta($post->ID, '_anima_event_date', true);
        $stream_url = get_post_meta($post->ID, '_anima_event_stream_url', true);
        $product_id = get_post_meta($post->ID, '_anima_event_product_id', true);

        wp_nonce_field('anima_event_save', 'anima_event_nonce');
        ?>
        <p>
            <label for="anima_event_date">Event Date & Time:</label>
            <input type="datetime-local" id="anima_event_date" name="anima_event_date" value="<?php echo esc_attr($date); ?>"
                class="widefat">
        </p>
        <p>
            <label for="anima_event_stream_url">Stream Embed URL (Zoom/YouTube/RTMP):</label>
            <input type="url" id="anima_event_stream_url" name="anima_event_stream_url"
                value="<?php echo esc_attr($stream_url); ?>" class="widefat" placeholder="https://www.youtube.com/embed/...">
        </p>
        <p>
            <label for="anima_event_product_id">Required Product ID (Ticket):</label>
            <input type="number" id="anima_event_product_id" name="anima_event_product_id"
                value="<?php echo esc_attr($product_id); ?>" class="widefat" placeholder="Enter WooCommerce Product ID">
            <span class="description">User must purchase this product to access the stream. Leave empty for free events.</span>
        </p>
        <?php
    }

    public function save_meta_data($post_id)
    {
        if (!isset($_POST['anima_event_nonce']) || !wp_verify_nonce($_POST['anima_event_nonce'], 'anima_event_save'))
            return;
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
            return;
        if (!current_user_can('edit_post', $post_id))
            return;

        if (isset($_POST['anima_event_date'])) {
            update_post_meta($post_id, '_anima_event_date', sanitize_text_field($_POST['anima_event_date']));
        }
        if (isset($_POST['anima_event_stream_url'])) {
            update_post_meta($post_id, '_anima_event_stream_url', esc_url_raw($_POST['anima_event_stream_url']));
        }
        if (isset($_POST['anima_event_product_id'])) {
            update_post_meta($post_id, '_anima_event_product_id', intval($_POST['anima_event_product_id']));
        }
    }

    public static function user_has_access($user_id, $event_id)
    {
        $product_id = get_post_meta($event_id, '_anima_event_product_id', true);

        // Free event
        if (empty($product_id))
            return true;

        // Admin always has access
        if (user_can($user_id, 'manage_options'))
            return true;

        // Check if user bought the product
        if (wc_customer_bought_product($user_id, $user_id, $product_id)) {
            return true;
        }

        return false;
    }
}

new Anima_Live_Events();
