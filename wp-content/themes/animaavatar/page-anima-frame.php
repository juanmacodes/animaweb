<?php
/**
 * Template Name: Anima – Contenido con marco (sin overlay)
 * Description: Centrado con tarjeta y sin overlays que intercepten clics. Ideal para Carrito, Checkout y Dashboard.
 *
 * @package AnimaAvatar
 */
get_header();
?>

<main id="primary" class="site-main anima-frame no-overlay">
  <div class="anima-frame__inner">
    <?php
      while ( have_posts() ) : the_post();
        the_content();
      endwhile;
    ?>
  </div>
</main>

<style>
  /* ======= Layout centrado ======= */
  .anima-frame{
    padding: clamp(18px, 3vw, 40px) 0;
  }
  .anima-frame__inner{
    width: min(100% - 40px, 1180px);
    margin-inline: auto;
    background: #121a27;
    border: 1px solid #1c2533;
    border-radius: 18px;
    box-shadow: 0 10px 40px rgba(0,0,0,.35);
    padding: clamp(18px, 3vw, 36px);
  }

  /* ======= Botones siempre clicables ======= */
  .anima-frame .woocommerce a.button,
  .anima-frame .woocommerce button.button,
  .anima-frame .wc-block-components-button,
  .anima-frame button[type="submit"]{
    position: relative; z-index: 3;
  }

  /* ======= Kill overlays dentro de esta plantilla ======= */
  .anima-frame *,
  .anima-frame *::before,
  .anima-frame *::after{ z-index: 1; }
  .anima-frame .entry-content,
  .anima-frame .woocommerce,
  .anima-frame .wc-block-cart,
  .anima-frame .wc-block-checkout{ position: relative; z-index: 2; }

  .anima-frame .e-con::before,
  .anima-frame .e-con::after,
  .anima-frame [class*="card"]::before,
  .anima-frame [class*="panel"]::before,
  .anima-frame [class*="panel"]::after,
  .anima-frame .site-main::before,
  .anima-frame .site-main::after{
    pointer-events:none !important;
  }

  /* banners/overlays conocidos */
  #coming-soon-footer-banner{ display:none !important; pointer-events:none !important; }

  /* ======= Ajustes Woo (bloques) ======= */
  .anima-frame .wc-block-cart__submit-container,
  .anima-frame .wc-block-components-sidebar{
    position: relative; z-index: 2;
  }
</style>

<?php get_footer(); ?>
