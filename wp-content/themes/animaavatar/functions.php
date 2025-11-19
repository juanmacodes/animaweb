<?php
/**
 * Funciones del tema Anima Avatar
 * Versión Master 1.1.2 (Final)
 */
define( 'ANIMAAVATAR_VERSION', '1.1.2' );

// Registrar hooks base del tema
action_hook_setup();

/** ===============================
 * 1. SETUP & ASSETS
 * =============================== */
function action_hook_setup() {
    add_action( 'after_setup_theme', 'animaavatar_setup' );
    add_action( 'after_setup_theme', 'animaavatar_content_width', 0 );
    add_action( 'wp_enqueue_scripts', 'animaavatar_enqueue_assets' );
    add_action( 'wp_head', 'animaavatar_preload_fonts', 1 );
    add_action( 'wp_head', 'animaavatar_print_critical_css', 5 );
    add_filter( 'nav_menu_link_attributes', 'animaavatar_aria_current', 10, 3 );
    add_filter( 'nav_menu_css_class', 'animaavatar_add_mega_menu_class', 10, 4 );
    add_filter( 'walker_nav_menu_start_el', 'animaavatar_render_mega_menu', 10, 4 );
}

function animaavatar_setup() {
    load_theme_textdomain( 'animaavatar', get_template_directory() . '/languages' );
    add_theme_support( 'automatic-feed-links' );
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'custom-logo', [ 'height' => 120, 'width' => 320, 'flex-height' => true, 'flex-width' => true ] );
    add_theme_support( 'html5', [ 'search-form','comment-form','comment-list','gallery','caption','style','script' ] );
    add_theme_support( 'woocommerce' );
    add_image_size( 'an_card_16x10', 1200, 750, true );
    add_image_size( 'an_square',    1000, 1000, true );
    register_nav_menus( [ 'main-menu' => __( 'Menú principal', 'animaavatar' ) ] );
}

function animaavatar_content_width() {
    $GLOBALS['content_width'] = apply_filters( 'animaavatar_content_width', 800 );
}

function animaavatar_enqueue_assets() {
    $theme = wp_get_theme();

    wp_enqueue_style( 'animaavatar-fonts',
        'https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Rajdhani:wght@500;700&display=swap',
        [], null
    );

    wp_enqueue_style( 'animaavatar-utilities',
        get_template_directory_uri() . '/assets/css/utilities.css',
        [], ANIMAAVATAR_VERSION
    );

    wp_enqueue_style( 'animaavatar-style',
        get_stylesheet_uri(),
        [ 'animaavatar-utilities' ],
        $theme->get( 'Version' )
    );

    // Script principal (Menú móvil, etc.)
    wp_enqueue_script( 'animaavatar-main',
        get_template_directory_uri() . '/assets/js/main.js',
        [], ANIMAAVATAR_VERSION, true
    );
    wp_script_add_data( 'animaavatar-main', 'defer', true );
}

function animaavatar_preload_fonts() { /* ... */ }

function animaavatar_print_critical_css() { /* ... */ }

/** ===============================
 * 2. MEGA MENÚ (Lógica mínima, CSS maneja el resto)
 * =============================== */
function animaavatar_aria_current( $atts, $item, $args ) {
    if ( isset($args->theme_location) && 'main-menu' === $args->theme_location && in_array( 'current-menu-item', $item->classes, true ) ) {
        $atts['aria-current'] = 'page';
    }
    return $atts;
}

function animaavatar_add_mega_menu_class( $classes, $item, $args, $depth ) {
    if ( isset($args->theme_location) && 'main-menu' === $args->theme_location && 0 === $depth ) {
        if ( 'servicios' === sanitize_title( $item->title ) ) $classes[] = 'menu-item--mega';
    }
    return $classes;
}

function animaavatar_render_mega_menu( $item_output, $item, $depth, $args ) {
    return $item_output;
}

/** ===============================
 * 3. REDIRECCIONES Y ACCESO
 * =============================== */
function anima_profile_url() {
    if ( function_exists('wc_get_page_permalink') ) {
        return wc_get_page_permalink('myaccount');
    }
    return home_url('/mi-cuenta/');
}

// Redirigir siempre a mi-cuenta tras login/registro
add_filter('login_redirect', 'anima_login_redirect_fix', 10, 3);
add_filter('woocommerce_login_redirect', 'anima_login_redirect_fix', 10, 2);
add_filter('woocommerce_registration_redirect', 'anima_login_redirect_fix', 10);

function anima_login_redirect_fix($redirect_to, $user = null) {
    if ( $user && is_wp_error($user) ) return $redirect_to;
    return anima_profile_url();
}

// ⚠️ Proteger la página de LOGIN (slug: 'login') si ya estás dentro
add_action('template_redirect', function(){
    if ( is_user_logged_in() && is_page('login') ) {
        wp_safe_redirect( anima_profile_url() );
        exit;
    }
});

// ⚠️ Proteger la página de PERFIL (mi-cuenta) si NO estás logueado
add_action('template_redirect', function () {
    if ( is_user_logged_in() ) return;

    if ( function_exists('is_account_page') && is_account_page() ) {
        wp_safe_redirect( home_url('/login/') );
        exit;
    }
});

/** ===============================
 * 4. WOOCOMMERCE FIXES
 * =============================== */
add_filter('body_class', function($classes){
    if ( function_exists('is_cart') && ( is_cart() || is_checkout() || is_account_page() ) ) {
        $classes[] = 'anima-no-overlay';
    }
    return $classes;
});

// Autocompletar pedidos virtuales
add_action( 'woocommerce_thankyou', 'anima_auto_complete_virtual_orders' );
add_action( 'woocommerce_payment_complete', 'anima_auto_complete_virtual_orders' );

function anima_auto_complete_virtual_orders( $order_id ) {
    if ( ! $order_id ) return;
    $order = wc_get_order( $order_id );
    if ( ! $order || ! in_array( $order->get_status(), ['processing', 'pending'], true ) ) return;

    $all_virtual = true;
    foreach ( $order->get_items() as $item ) {
        $product = $item->get_product();
        if ( ! $product->is_virtual() || ! $product->is_downloadable() ) {
            $all_virtual = false;
            break;
        }
    }
    if ( $all_virtual ) {
        $order->update_status( 'completed', __( 'Autocompletado por Anima.', 'animaavatar' ) );
    }
}

/** ===============================
 * 5. LÓGICA DE CURSOS Y PRODUCTOS
 * =============================== */
if ( ! function_exists( 'anima_get_course_by_product' ) ) {
    function anima_get_course_by_product( $product_id ) {
        $product_id = (int) $product_id;
        if ( ! $product_id ) return null;
        $posts = get_posts( [
            'post_type' => 'curso',
            'posts_per_page' => 1,
            'meta_query' => [ ['key' => '_anima_product_id', 'value' => $product_id] ],
        ] );
        return $posts ? $posts[0] : null;
    }
}

if ( ! function_exists( 'anima_get_user_courses' ) ) {
    function anima_get_user_courses( $user_id ) {
        if ( ! function_exists( 'wc_get_orders' ) ) return [];
        $user_id = (int) $user_id;
        if ( ! $user_id ) return [];

        $orders = wc_get_orders( [
            'customer_id' => $user_id,
            'status' => ['completed', 'processing', 'on-hold'],
            'limit' => -1, 'orderby' => 'date', 'order' => 'DESC',
        ] );

        $seen = [];
        $courses = [];

        foreach ( $orders as $order ) {
            foreach ( $order->get_items() as $item ) {
                $pid = $item->get_product_id();
                $product = wc_get_product( $pid );
                if ( ! $product ) continue;
                $base_id = $product->is_type( 'variation' ) ? $product->get_parent_id() : $pid;
                if ( in_array( $base_id, $seen, true ) ) continue;
                $seen[] = $base_id;

                $course_post = anima_get_course_by_product( $base_id );
                if ( ! $course_post ) continue;

                $courses[] = [
                    'course_id' => $course_post->ID,
                    'title'     => get_the_title( $course_post ),
                    'url'       => get_permalink( $course_post ),
                    'thumb'     => get_the_post_thumbnail_url( $course_post, 'medium' ) ?: wc_placeholder_img_src(),
                ];
            }
        }
        return $courses;
    }
}

/** ===============================
 * 6. PEDIDOS RECIENTES
 * =============================== */
if ( class_exists( 'WooCommerce' ) ) {
    if ( ! function_exists( 'anima_get_recent_orders' ) ) {
        function anima_get_recent_orders( $user_id, $count = 3 ) {
            $customer_orders = wc_get_orders( [
                'limit'        => $count,
                'customer_id'  => $user_id,
                'orderby'      => 'date',
                'order'        => 'DESC',
                'status'       => ['completed', 'processing'], // Solo completados/procesando
            ] );

            $orders_data = [];
            if ( ! empty( $customer_orders ) ) {
                foreach ( $customer_orders as $order ) {
                    $orders_data[] = [
                        'id'          => $order->get_id(),
                        'date'        => wc_format_datetime( $order->get_date_created() ),
                        'status'      => wc_get_order_status_name( $order->get_status() ),
                        'status_slug' => $order->get_status(),
                        'total'       => $order->get_formatted_order_total(),
                        'view_url'    => $order->get_view_order_url(),
                    ];
                }
            }
            return $orders_data;
        }
    }
}

// Borrar Pedidos (Draft/Pending/Failed)
add_action( 'template_redirect', 'anima_handle_delete_order' );
if ( ! function_exists( 'anima_handle_delete_order' ) ) {
    function anima_handle_delete_order() {
        if ( ! isset($_GET['action'], $_GET['order_id'], $_GET['nonce']) || $_GET['action'] !== 'delete_order' ) return;
        $order_id = absint( $_GET['order_id'] );
        $user_id  = get_current_user_id();
        if ( ! wp_verify_nonce($_GET['nonce'], 'delete_order_' . $order_id) ) return;

        $order = wc_get_order( $order_id );
        if ( ! $order ) {
            wp_safe_redirect( wc_get_page_permalink( 'myaccount' ) ); exit;
        }

        if ( (int) $order->get_customer_id() === $user_id && in_array( $order->get_status(), ['pending','draft','failed'], true ) ) {
            $order->delete( true );
            wp_safe_redirect( add_query_arg( 'msg', 'deleted', wc_get_page_permalink( 'myaccount' ) ) ); exit;
        } else {
            wp_safe_redirect( add_query_arg( 'msg', 'delete_fail', wc_get_page_permalink( 'myaccount' ) ) ); exit;
        }
    }
}

/** ===============================
 * 7. AVATAR PERSONALIZADO
 * =============================== */
add_action('init', 'anima_handle_avatar_upload');
function anima_handle_avatar_upload() {
    if ( ! isset($_FILES['anima_avatar_file']) || ! isset($_POST['anima_avatar_nonce']) ) return;
    if ( ! wp_verify_nonce($_POST['anima_avatar_nonce'], 'anima_avatar_upload') ) return;
    if ( ! is_user_logged_in() ) return;

    require_once( ABSPATH . 'wp-admin/includes/image.php' );
    require_once( ABSPATH . 'wp-admin/includes/file.php' );
    require_once( ABSPATH . 'wp-admin/includes/media.php' );

    $attach_id = media_handle_upload( 'anima_avatar_file', 0 );
    if ( ! is_wp_error( $attach_id ) ) {
        update_user_meta( get_current_user_id(), 'anima_custom_avatar', $attach_id );
    }
    wp_safe_redirect( remove_query_arg( 'avatar_updated' ) );
    exit;
}

add_filter( 'get_avatar', 'anima_get_custom_avatar', 10, 6 );
function anima_get_custom_avatar( $avatar, $id_or_email, $size, $default, $alt, $args ) {
    $user_id = 0;
    if ( is_numeric( $id_or_email ) ) $user_id = (int) $id_or_email;
    elseif ( is_string( $id_or_email ) && ( $user = get_user_by( 'email', $id_or_email ) ) ) $user_id = $user->ID;
    elseif ( is_object( $id_or_email ) && ! empty( $id_or_email->user_id ) ) $user_id = (int) $id_or_email->user_id;

    if ( $user_id ) {
        $custom_id = get_user_meta( $user_id, 'anima_custom_avatar', true );
        if ( $custom_id ) {
            $img = wp_get_attachment_image_src( $custom_id, 'thumbnail' );
            if ( $img ) {
                $class = isset( $args['class'] ) ? $args['class'] : '';
                return '<img alt="' . esc_attr( $alt ) . '" src="' . esc_url( $img[0] ) . '" class="avatar photo ' . esc_attr( $class ) . '" height="' . esc_attr( $size ) . '" width="' . esc_attr( $size ) . '" loading="lazy" />';
            }
        }
    }
    return $avatar;
}

add_action('init', function() {
    $role = get_role('subscriber');
    if($role && !$role->has_cap('upload_files')) $role->add_cap('upload_files');
    $role_c = get_role('customer');
    if($role_c && !$role_c->has_cap('upload_files')) $role_c->add_cap('upload_files');
});

/** ===============================
 * 8. VISOR 3D (Carga Directa)
 * =============================== */
add_action('wp_head', function(){
    if ( is_page_template('page-showcase.php') || is_page_template('page-assets.php') ) {
        ?>
        <script type="module" src="https://ajax.googleapis.com/ajax/libs/model-viewer/3.3.0/model-viewer.min.js"></script>
        <style>model-viewer { width: 100%; height: 100%; min-height: 400px; display: block; }</style>
        <?php
    }
});

/** ===============================
 * 9. CPT ASSETS & GESTIÓN
 * =============================== */
add_action('init', 'anima_register_asset_cpt');
function anima_register_asset_cpt() {
    register_post_type('anima_asset', [
        'labels' => ['name' => 'Assets', 'singular_name' => 'Asset'],
        'public' => true,
        'show_ui' => true,
        'menu_icon' => 'dashicons-lightbulb',
        'supports' => ['title', 'editor', 'thumbnail', 'custom-fields', 'excerpt'],
        'has_archive' => true,
        'show_in_rest' => true,
    ]);
}

add_action('add_meta_boxes', 'anima_add_asset_product_meta_box');
function anima_add_asset_product_meta_box() {
    add_meta_box('anima_asset_meta', 'Datos del Asset', 'anima_asset_meta_cb', 'anima_asset', 'normal', 'high');
}

function anima_asset_meta_cb($post) {
    wp_nonce_field('anima_save_asset_meta', 'anima_asset_nonce');
    $prod_id = get_post_meta($post->ID, '_anima_asset_product_id', true);
    $dl_url  = get_post_meta($post->ID, '_anima_asset_download_url', true);
    $glb_url = get_post_meta($post->ID, '_anima_asset_glb_url', true);

    echo '<p><label>ID Producto (Opcional):</label><input type="number" name="anima_asset_pid" value="'.esc_attr($prod_id).'" style="width:100%" placeholder="Vacío = Gratis"></p>';
    echo '<p><label>URL Descarga (.zip):</label><input type="url" name="anima_asset_dl" value="'.esc_url($dl_url).'" style="width:100%"></p>';
    echo '<p><label>URL 3D (.glb):</label><input type="url" name="anima_asset_glb" value="'.esc_url($glb_url).'" style="width:100%"></p>';
}

add_action('save_post_anima_asset', 'anima_save_asset_meta_data');
function anima_save_asset_meta_data($post_id) {
    if (!isset($_POST['anima_asset_nonce']) || !wp_verify_nonce($_POST['anima_asset_nonce'], 'anima_save_asset_meta')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    
    if(isset($_POST['anima_asset_pid'])) update_post_meta($post_id, '_anima_asset_product_id', absint($_POST['anima_asset_pid']));
    if(isset($_POST['anima_asset_dl'])) update_post_meta($post_id, '_anima_asset_download_url', esc_url_raw($_POST['anima_asset_dl']));
    if(isset($_POST['anima_asset_glb'])) update_post_meta($post_id, '_anima_asset_glb_url', esc_url_raw($_POST['anima_asset_glb']));
}

// Helper acceso
if ( ! function_exists('anima_user_can_access_asset') ) {
    function anima_user_can_access_asset($asset_id, $user_id = 0) {
        if (!$user_id) $user_id = get_current_user_id();
        if (!$user_id) return false;

        $pid = (int) get_post_meta($asset_id, '_anima_asset_product_id', true);
        if (!$pid) return true;

        $orders = wc_get_orders(['customer_id' => $user_id, 'status' => ['completed', 'processing'], 'limit' => -1]);
        foreach ($orders as $order) {
            foreach ($order->get_items() as $item) {
                if ($item->get_product_id() === $pid) return true;
            }
        }
        return false;
    }
}

// Helper Dashboard
if ( ! function_exists('anima_get_user_owned_assets') ) {
    function anima_get_user_owned_assets($user_id) {
        if(!$user_id) return [];
        $out = [];
        $posts = get_posts(['post_type'=>'anima_asset', 'posts_per_page'=>-1]);
        foreach ($posts as $p) {
            if (anima_user_can_access_asset($p->ID, $user_id)) {
                $out[] = [
                    'id' => $p->ID,
                    'title' => $p->post_title,
                    'thumb' => get_the_post_thumbnail_url($p->ID, 'thumbnail') ?: wc_placeholder_img_src(),
                    'link' => home_url('/assets/')
                ];
            }
        }
        return $out;
    }
}

/** ===============================
 * 10. API REST
 * =============================== */
add_action('rest_api_init', function () {
    register_rest_route('anima/v1', '/user-assets/(?P<user_id>\d+)', [
        'methods' => 'GET',
        'callback' => 'anima_rest_get_user_assets',
        'permission_callback' => function ($req) { return is_user_logged_in(); },
    ]);
});

function anima_rest_get_user_assets($request) {
    $uid = (int)$request['user_id'];
    $data = [];
    $posts = get_posts(['post_type'=>'anima_asset', 'posts_per_page'=>-1]);

    foreach ($posts as $p) {
        if (anima_user_can_access_asset($p->ID, $uid)) {
            $data[] = [
                'id' => $p->ID,
                'title' => $p->post_title,
                'dl' => get_post_meta($p->ID, '_anima_asset_download_url', true),
                'glb' => get_post_meta($p->ID, '_anima_asset_glb_url', true),
                'thumb' => get_the_post_thumbnail_url($p->ID, 'medium')
            ];
        }
    }
    return new WP_REST_Response($data, 200);
}