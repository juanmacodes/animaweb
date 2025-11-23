<?php
/**
 * Cabecera del tema Anima Avatar
 */

// --- LÓGICA DEL BOTÓN DE CABECERA ---
// 1. URL del Perfil (/mi-cuenta/)
$profile_url = function_exists('wc_get_page_permalink')
    ? wc_get_page_permalink('myaccount')
    : home_url('/mi-cuenta/');

// 2. URL de Login (/login/)
$login_url = home_url('/login/');

// 3. Decidir destino y texto
if (is_user_logged_in()) {
    $cta_url = $profile_url;
    $cta_text = 'Mi Área Personal';
} else {
    $cta_url = $login_url;
    $cta_text = 'Accede al metaverso';
        </script>
    <?php endif; ?>
</head>

<body <?php body_class(); ?>>
    <?php wp_body_open(); ?>

    <header class="site-header">
        <div class="site-header__container container">

            <div class="site-header__branding">
                <?php
                if (has_custom_logo()) {
                    the_custom_logo();
                } else {
                    echo '<a href="' . esc_url(home_url('/')) . '" rel="home" class="site-title">' . esc_html(get_bloginfo('name')) . '</a>';
                }
                ?>
            </div>

            <nav class="main-navigation" aria-label="<?php esc_attr_e('Menú principal', 'animaavatar'); ?>">
                <?php
                wp_nav_menu(array(
                    'theme_location' => 'main-menu',
                    'container' => false,
                    'menu_class' => 'main-menu__list',
                    'echo' => true,
                    'fallback_cb' => 'wp_page_menu',
                    'depth' => 2, // Permitir submenús (dropdowns)
                ));
                ?>
            </nav>

            <div class="site-header__actions">

                <a href="<?php echo esc_url($cta_url); ?>" class="anima-btn header-btn">
                    <?php echo esc_html($cta_text); ?>
                </a>

                <?php get_template_part('header-mobile'); ?>

            </div>
        </div>



    </header>

    <div id="content" class="site-content">