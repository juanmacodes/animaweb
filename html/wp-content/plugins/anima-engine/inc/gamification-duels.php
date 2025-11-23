<?php
if (!defined('ABSPATH'))
    exit;

class Anima_Gamification_Duels
{

    public function __construct()
    {
        add_action('init', [$this, 'register_cpt']);
        add_shortcode('anima_style_duels', [$this, 'render_duels']);
        add_action('wp_ajax_anima_vote_duel', [$this, 'handle_vote']);
        add_action('wp_ajax_nopriv_anima_vote_duel', [$this, 'handle_vote']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function register_cpt()
    {
        register_post_type('style_duel', [
            'labels' => [
                'name' => 'Duelos de Estilo',
                'singular_name' => 'Duelo',
                'add_new' => 'Crear Duelo',
                'add_new_item' => 'Nuevo Duelo de Estilo'
            ],
            'public' => true,
            'has_archive' => true,
            'supports' => ['title', 'editor', 'thumbnail', 'custom-fields'],
            'menu_icon' => 'dashicons-swords',
            'rewrite' => ['slug' => 'duels'],
            'show_in_rest' => true
        ]);
    }

    public function enqueue_assets()
    {
        wp_register_script('anima-gamification', ANIMA_ENGINE_URL . 'assets/js/gamification.js', ['jquery'], '1.0.0', true);
        wp_localize_script('anima-gamification', 'anima_game_vars', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('anima_game_nonce')
        ]);
    }

    public function render_duels($atts)
    {
        wp_enqueue_script('anima-gamification');

        $args = [
            'post_type' => 'style_duel',
            'posts_per_page' => 1,
            'meta_query' => [
                [
                    'key' => 'duel_status',
                    'value' => 'active',
                    'compare' => '='
                ]
            ]
        ];

        // For demo purposes, just get the latest one if no active meta is set
        if (empty(get_posts($args))) {
            $args = ['post_type' => 'style_duel', 'posts_per_page' => 1];
        }

        $query = new WP_Query($args);

        ob_start();
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $duel_id = get_the_ID();
                $contender_1_name = get_post_meta($duel_id, 'contender_1_name', true) ?: 'Cyber Samurai';
                $contender_1_img = get_post_meta($duel_id, 'contender_1_img', true) ?: 'https://placehold.co/300x400/111/00F0FF?text=Samurai';
                $contender_1_votes = (int) get_post_meta($duel_id, 'contender_1_votes', true);

                $contender_2_name = get_post_meta($duel_id, 'contender_2_name', true) ?: 'Neon Ninja';
                $contender_2_img = get_post_meta($duel_id, 'contender_2_img', true) ?: 'https://placehold.co/300x400/111/BC13FE?text=Ninja';
                $contender_2_votes = (int) get_post_meta($duel_id, 'contender_2_votes', true);

                $total_votes = $contender_1_votes + $contender_2_votes;
                $p1_percent = $total_votes > 0 ? round(($contender_1_votes / $total_votes) * 100) : 50;
                $p2_percent = $total_votes > 0 ? round(($contender_2_votes / $total_votes) * 100) : 50;

                ?>
                <div class="anima-duel-arena" id="duel-<?php echo $duel_id; ?>">
                    <h2 class="duel-title text-center glitch-text" data-text="<?php the_title(); ?>"><?php the_title(); ?></h2>

                    <div class="duel-contenders flex flex-center gap-30 mt-30">
                        <!-- Contender 1 -->
                        <div class="contender-card" data-id="1">
                            <div class="contender-img-wrap">
                                <img src="<?php echo esc_url($contender_1_img); ?>" alt="<?php echo esc_attr($contender_1_name); ?>">
                                <div class="vote-overlay">
                                    <button class="vote-btn" data-duel="<?php echo $duel_id; ?>" data-contender="1">VOTE</button>
                                </div>
                            </div>
                            <h3><?php echo esc_html($contender_1_name); ?></h3>
                            <div class="vote-bar-wrap">
                                <div class="vote-bar" style="width: <?php echo $p1_percent; ?>%; background: var(--cyan);"></div>
                                <span class="vote-count"><?php echo $contender_1_votes; ?></span>
                            </div>
                        </div>

                        <div class="vs-badge">VS</div>

                        <!-- Contender 2 -->
                        <div class="contender-card" data-id="2">
                            <div class="contender-img-wrap">
                                <img src="<?php echo esc_url($contender_2_img); ?>" alt="<?php echo esc_attr($contender_2_name); ?>">
                                <div class="vote-overlay">
                                    <button class="vote-btn" data-duel="<?php echo $duel_id; ?>" data-contender="2">VOTE</button>
                                </div>
                            </div>
                            <h3><?php echo esc_html($contender_2_name); ?></h3>
                            <div class="vote-bar-wrap">
                                <div class="vote-bar" style="width: <?php echo $p2_percent; ?>%; background: var(--purple);"></div>
                                <span class="vote-count"><?php echo $contender_2_votes; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                <style>
                    .anima-duel-arena {
                        background: #0a0a0a;
                        padding: 40px;
                        border: 1px solid #333;
                        border-radius: 12px;
                        position: relative;
                        overflow: hidden;
                    }

                    .duel-contenders {
                        position: relative;
                        z-index: 2;
                    }

                    .contender-card {
                        width: 45%;
                        text-align: center;
                        position: relative;
                        transition: transform 0.3s;
                    }

                    .contender-card:hover {
                        transform: scale(1.02);
                    }

                    .contender-img-wrap {
                        position: relative;
                        border-radius: 8px;
                        overflow: hidden;
                        border: 2px solid #333;
                        height: 300px;
                    }

                    .contender-img-wrap img {
                        width: 100%;
                        height: 100%;
                        object-fit: cover;
                    }

                    .vote-overlay {
                        position: absolute;
                        top: 0;
                        left: 0;
                        width: 100%;
                        height: 100%;
                        background: rgba(0, 0, 0, 0.6);
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        opacity: 0;
                        transition: opacity 0.3s;
                    }

                    .contender-card:hover .vote-overlay {
                        opacity: 1;
                    }

                    .vote-btn {
                        background: #fff;
                        color: #000;
                        border: none;
                        padding: 10px 30px;
                        font-weight: bold;
                        cursor: pointer;
                        transform: skew(-10deg);
                    }

                    .vote-btn:hover {
                        background: var(--cyan);
                    }

                    .vs-badge {
                        font-size: 3rem;
                        font-weight: 900;
                        color: #fff;
                        text-shadow: 0 0 20px #fff;
                        font-style: italic;
                    }

                    .vote-bar-wrap {
                        background: #222;
                        height: 10px;
                        border-radius: 5px;
                        margin-top: 10px;
                        position: relative;
                        overflow: hidden;
                    }

                    .vote-bar {
                        height: 100%;
                        transition: width 0.5s ease;
                    }

                    .vote-count {
                        position: absolute;
                        right: 5px;
                        top: -20px;
                        font-size: 0.8rem;
                        color: #888;
                    }
                </style>
                <?php
            }
            wp_reset_postdata();
        } else {
            echo '<p class="text-center">No hay duelos activos esta semana.</p>';
        }
        return ob_get_clean();
    }

    public function handle_vote()
    {
        check_ajax_referer('anima_game_nonce', 'nonce');

        $duel_id = intval($_POST['duel_id']);
        $contender = intval($_POST['contender']); // 1 or 2

        // Simple cookie check to prevent spam (in production use User ID or IP)
        if (isset($_COOKIE['anima_voted_' . $duel_id])) {
            wp_send_json_error('Ya has votado en este duelo.');
        }

        $meta_key = 'contender_' . $contender . '_votes';
        $current_votes = (int) get_post_meta($duel_id, $meta_key, true);
        update_post_meta($duel_id, $meta_key, $current_votes + 1);

        // Set cookie
        setcookie('anima_voted_' . $duel_id, '1', time() + 86400, COOKIEPATH, COOKIE_DOMAIN);

        // Return new stats
        $v1 = (int) get_post_meta($duel_id, 'contender_1_votes', true);
        $v2 = (int) get_post_meta($duel_id, 'contender_2_votes', true);

        wp_send_json_success([
            'v1' => $v1,
            'v2' => $v2,
            'total' => $v1 + $v2
        ]);
    }
}

new Anima_Gamification_Duels();
