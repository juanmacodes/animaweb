<?php
if (!defined('ABSPATH'))
    exit;

class Anima_P2P_Market
{

    public function __construct()
    {
        add_action('init', array($this, 'register_cpt'));
        add_action('init', array($this, 'register_taxonomy'));
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action('save_post', array($this, 'save_meta_data'));

        // AJAX for buying items
        add_action('wp_ajax_anima_buy_item', array($this, 'handle_buy_item'));

        // Shortcode
        add_shortcode('anima_rewards_shop', array($this, 'render_shop_shortcode'));
    }

    public function register_cpt()
    {
        $labels = array(
            'name' => 'Market Items',
            'singular_name' => 'Market Item',
            'menu_name' => 'Marketplace',
            'add_new' => 'Add Item',
            'add_new_item' => 'Add New Market Item',
            'edit_item' => 'Edit Item',
        );
        $args = array(
            'labels' => $labels,
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'menu_icon' => 'dashicons-cart',
            'supports' => array('title', 'editor', 'thumbnail', 'author'),
            'has_archive' => true,
            'rewrite' => array('slug' => 'market'),
        );
        register_post_type('market_item', $args);
    }

    public function register_taxonomy()
    {
        register_taxonomy('market_category', 'market_item', array(
            'labels' => array('name' => 'Categories'),
            'hierarchical' => true,
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => array('slug' => 'market-category'),
        ));
    }

    public function add_meta_boxes()
    {
        add_meta_box(
            'anima_market_details',
            'Item Details',
            array($this, 'render_meta_box'),
            'market_item',
            'normal',
            'high'
        );
    }

    public function render_meta_box($post)
    {
        $price = get_post_meta($post->ID, '_anima_market_price', true);
        $file_url = get_post_meta($post->ID, '_anima_market_file_url', true);
        wp_nonce_field('anima_market_save', 'anima_market_nonce');
        ?>
        <p>
            <label for="anima_market_price">Price (Credits):</label>
            <input type="number" id="anima_market_price" name="anima_market_price" value="<?php echo esc_attr($price); ?>"
                class="widefat">
        </p>
        <p>
            <label for="anima_market_file_url">File URL (Download):</label>
            <input type="url" id="anima_market_file_url" name="anima_market_file_url" value="<?php echo esc_attr($file_url); ?>"
                class="widefat">
        </p>
        <?php
    }

    public function save_meta_data($post_id)
    {
        if (!isset($_POST['anima_market_nonce']) || !wp_verify_nonce($_POST['anima_market_nonce'], 'anima_market_save'))
            return;
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
            return;
        if (!current_user_can('edit_post', $post_id))
            return;

        if (isset($_POST['anima_market_price'])) {
            update_post_meta($post_id, '_anima_market_price', intval($_POST['anima_market_price']));
        }
        if (isset($_POST['anima_market_file_url'])) {
            update_post_meta($post_id, '_anima_market_file_url', esc_url_raw($_POST['anima_market_file_url']));
        }
    }

    public function handle_buy_item()
    {
        check_ajax_referer('anima_minigame_nonce', 'nonce'); // Reusing nonce for simplicity, or create new

        if (!is_user_logged_in()) {
            wp_send_json_error('Not logged in');
        }

        $user_id = get_current_user_id();
        $item_id = isset($_POST['item_id']) ? intval($_POST['item_id']) : 0;

        if (!$item_id)
            wp_send_json_error('Invalid Item');

        $price = (int) get_post_meta($item_id, '_anima_market_price', true);
        $user_credits = (int) get_user_meta($user_id, 'anima_credits', true);

        if ($user_credits < $price) {
            wp_send_json_error('Insufficient Credits');
        }

        // Deduct credits
        update_user_meta($user_id, 'anima_credits', $user_credits - $price);

        // Grant access (Logic: Add to user meta 'purchased_items')
        $purchased = get_user_meta($user_id, 'anima_purchased_items', true);
        if (!is_array($purchased))
            $purchased = array();
        $purchased[] = $item_id;
        update_user_meta($user_id, 'anima_purchased_items', $purchased);

        // Credit Seller (Optional - Platform Fee?)
        $seller_id = get_post_field('post_author', $item_id);
        if ($seller_id != $user_id) {
            $seller_credits = (int) get_user_meta($seller_id, 'anima_credits', true);
            update_user_meta($seller_id, 'anima_credits', $seller_credits + $price);
        }

        wp_send_json_success(array('new_credits' => $user_credits - $price, 'message' => 'Item Purchased!'));
    }

    public function render_shop_shortcode($atts)
    {
        $user_id = get_current_user_id();
        $credits = (int) get_user_meta($user_id, 'anima_credits', true);

        $args = array(
            'post_type' => 'market_item',
            'posts_per_page' => 12,
            'post_status' => 'publish'
        );
        $query = new WP_Query($args);

        ob_start();
        ?>
        <div class="anima-market-wrapper">
            <div class="market-header">
                <h3>Rewards Shop</h3>
                <div class="user-balance">Balance: <span class="text-cyan"><?php echo $credits; ?> Credits</span></div>
            </div>
            <div class="market-grid">
                <?php if ($query->have_posts()):
                    while ($query->have_posts()):
                        $query->the_post();
                        $price = get_post_meta(get_the_ID(), '_anima_market_price', true);
                        $thumb = get_the_post_thumbnail_url(get_the_ID(), 'medium');
                        ?>
                        <div class="market-card cyberpunk-border">
                            <div class="card-img" style="background-image: url('<?php echo $thumb; ?>');"></div>
                            <div class="card-content">
                                <h4><?php the_title(); ?></h4>
                                <div class="price-tag"><?php echo $price; ?> Credits</div>
                                <button class="anima-btn btn-sm btn-buy"
                                    onclick="buyItem(<?php echo get_the_ID(); ?>, <?php echo $price; ?>)">Buy Now</button>
                            </div>
                        </div>
                    <?php endwhile; else: ?>
                    <p>No items available.</p>
                <?php endif;
                wp_reset_postdata(); ?>
            </div>
        </div>
        <script>
            function buyItem(id, price) {
                if (!confirm('Buy this item for ' + price + ' credits?')) return;

                const data = new FormData();
                data.append('action', 'anima_buy_item');
                data.append('nonce', '<?php echo wp_create_nonce('anima_minigame_nonce'); ?>'); // Ideally localize this
                data.append('item_id', id);

                fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                    method: 'POST',
                    body: data
                })
                    .then(res => res.json())
                    .then(res => {
                        if (res.success) {
                            alert(res.data.message);
                            location.reload();
                        } else {
                            alert('Error: ' + res.data);
                        }
                    });
            }
        </script>
        <style>
            .anima-market-wrapper {
                padding: 20px;
                background: rgba(0, 0, 0, 0.5);
                border-radius: 10px;
            }

            .market-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 20px;
                color: #fff;
            }

            .market-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
                gap: 20px;
            }

            .market-card {
                background: #111;
                border: 1px solid #333;
                border-radius: 8px;
                overflow: hidden;
                transition: transform 0.2s;
            }

            .market-card:hover {
                transform: translateY(-5px);
                border-color: var(--cyan);
            }

            .card-img {
                height: 150px;
                background-size: cover;
                background-position: center;
            }

            .card-content {
                padding: 15px;
                text-align: center;
            }

            .card-content h4 {
                color: #fff;
                margin: 0 0 10px;
                font-size: 1rem;
            }

            .price-tag {
                color: var(--purple);
                font-weight: bold;
                margin-bottom: 10px;
            }

            .btn-buy {
                width: 100%;
            }
        </style>
        <?php
        return ob_get_clean();
    }
}

new Anima_P2P_Market();
