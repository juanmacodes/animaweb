<?php
/**
 * Footer Cyberpunk para Anima Avatar
 * Reemplaza al footer básico.
 */
?>

    </div><footer id="colophon" class="site-footer">
        
        <div class="footer-neon-line"></div>

        <div class="footer-container container">
            
            <div class="footer-col footer-brand">
                <h2 class="footer-logo"><?php bloginfo( 'name' ); ?></h2>
                <p class="footer-desc">
                    <?php esc_html_e( 'Creado para experiencias inmersivas y avatares digitales.', 'animaavatar' ); ?>
                    Diseñamos identidades que brillan en cualquier realidad.
                </p>
                <div class="footer-socials">
                    <a href="#" aria-label="Instagram"><span class="dashicons dashicons-instagram"></span></a>
                    <a href="#" aria-label="Twitter"><span class="dashicons dashicons-twitter"></span></a>
                    <a href="#" aria-label="Discord"><span class="dashicons dashicons-groups"></span></a>
                </div>
            </div>

            <div class="footer-col footer-links">
                <h3>EXPLORA</h3>
                <nav class="footer-nav">
                    <?php
                    // Muestra el menú 'footer-menu' si existe, sino una lista por defecto
                    if ( has_nav_menu( 'footer-menu' ) ) {
                        wp_nav_menu( array( 
                            'theme_location' => 'footer-menu', 
                            'container' => false, 
                            'depth' => 1,
                            'fallback_cb' => false
                        ) );
                    } else {
                        // Enlaces por defecto si no has creado el menú aún
                        echo '<ul>
                                <li><a href="'.home_url('/cursos').'">Anima Academy</a></li>
                                <li><a href="'.home_url('/nexus').'">Nexus (Comunidad)</a></li>
                                <li><a href="'.home_url('/showroom').'">Showcase 3D</a></li>
                                <li><a href="'.home_url('/mi-cuenta').'">Área Personal</a></li>
                              </ul>';
                    }
                    ?>
                </nav>
            </div>

            <div class="footer-col footer-legal">
                <h3>LEGAL</h3>
                <ul>
                    <li><a href="#">Política de Privacidad</a></li>
                    <li><a href="#">Términos de Uso</a></li>
                    <li><a href="#">Cookies</a></li>
                </ul>
                <p class="copyright">
                    &copy; <?php echo date( 'Y' ); ?> <?php bloginfo( 'name' ); ?>.<br>
                    <span style="font-size:0.8em; color:#666;">System Online</span>
                </p>
            </div>

        </div>
    </footer>

</div><?php wp_footer(); ?>

</body>
</html>