<?php 
/**
 * Template Name: Acceso al Metaverso
 * Description: Pantalla de login/registro con fondo a pantalla completa y card centrada.
 */

if ( ! defined('ABSPATH') ) exit;
get_header();

$bg = get_the_post_thumbnail_url( get_the_ID(), 'full' );
?>
<style>
  .anima-auth-viewport{
    min-height: calc(100vh - var(--admin-bar,0px));
    position: relative; display:flex; align-items:center; justify-content:center;
    padding: clamp(24px, 5vw, 48px);
    background: #090d15; overflow:hidden;
  }
  .anima-auth-viewport::before{
    content:""; position:absolute; inset:0;
    background:
      radial-gradient(1200px 600px at 10% -10%, rgba(100,180,255,.10), transparent 60%),
      radial-gradient(900px 600px at 110% 110%, rgba(124,58,237,.12), transparent 60%),
      rgba(5,8,14,.8);
    pointer-events:none;
  }
  .anima-auth-bg{
    position:absolute; inset:0; background:center/cover no-repeat;
    filter:saturate(110%) contrast(105%); transform:scale(1.03)
  }
  .anima-auth-overlay{
    position:absolute; inset:0;
    background:radial-gradient(800px 500px at 70% 20%, rgba(0,180,255,.12), transparent 70%);
    backdrop-filter:blur(4px)
  }

  .anima-auth-card{
    position:relative; z-index:2; width:min(100%, 1040px);
    border-radius:24px; padding:clamp(20px, 3vw, 28px);
    background: rgba(13,18,30,.45);
    border:1px solid rgba(255,255,255,.08);
    box-shadow:0 10px 40px rgba(0,0,0,.45), inset 0 0 0 1px rgba(255,255,255,.04);
    backdrop-filter: blur(10px);
  }
  .anima-auth-head{ display:flex; align-items:baseline; justify-content:space-between; gap:1rem; margin-bottom:12px }
  .anima-auth-title{ margin:0; font-size:clamp(22px, 2.2vw, 28px); color:#e9f2ff; letter-spacing:.3px }
  .anima-auth-sub{ margin:0; font-size:.95rem; color:#9db2d9 }

  .anima-auth-tabs{
    margin-top:10px; display:flex; gap:10px;
    background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.08);
    border-radius:14px; padding:6px;
    /* --- centrado --- */
    max-width:560px; margin-left:auto; margin-right:auto;
  }
  .anima-auth-tab{
    appearance:none; background:transparent; border:0; color:#bcd4ff;
    padding:10px 16px; border-radius:10px; cursor:pointer; font-weight:700; letter-spacing:.2px
  }
  .anima-auth-tab.is-active{ background:linear-gradient(90deg,#6f65ff,#24d1ff); color:#06111b }

  /* ---- GRID centrado (un panel visible a la vez) ---- */
  .anima-auth-grid{
    display:grid;
    grid-template-columns: minmax(300px, 560px);
    justify-content:center; gap:20px; margin-top:16px;
  }
  .anima-auth-panel{ width:100%; }

  .anima-auth-card .anima-auth{
    background:rgba(255,255,255,.03); border:1px solid rgba(255,255,255,.08);
    border-radius:16px; padding:18px;
    max-width:560px; margin-inline:auto;
  }
  .anima-auth-card .anima-auth .button,
  .anima-auth-card .anima-auth input[type=submit]{
    background:linear-gradient(90deg,#6f65ff,#24d1ff)!important; color:#06111b!important; font-weight:800
  }
  .anima-auth-card .anima-auth input[type=text],
  .anima-auth-card .anima-auth input[type=email],
  .anima-auth-card .anima-auth input[type=password]{ background:#0f1522!important }

  .woocommerce-message, .woocommerce-error, .woocommerce-info{
    background:rgba(255,255,255,.05); border-color:rgba(255,255,255,.15); color:#eaf0ff
  }
</style>

<main id="content" class="site-main" role="main">
  <section class="anima-auth-viewport">
    <?php if ( $bg ) : ?>
      <div class="anima-auth-bg" style="background-image:url('<?php echo esc_url($bg); ?>')"></div>
    <?php endif; ?>
    <div class="anima-auth-overlay"></div>

    <div class="anima-auth-card">
      <header class="anima-auth-head">
        <h1 class="anima-auth-title"><?php echo esc_html( get_the_title() ?: 'Accede a tu metaverso' ); ?></h1>
        <p class="anima-auth-sub"><?php echo esc_html( get_bloginfo('name') ); ?></p>
      </header>

      <div class="anima-auth-tabs" data-tabs>
        <button class="anima-auth-tab is-active" data-tab="login"><?php esc_html_e('Entrar','animaavatar'); ?></button>
        <button class="anima-auth-tab" data-tab="register"><?php esc_html_e('Crear cuenta','animaavatar'); ?></button>
      </div>

      <div class="anima-auth-grid">
        <div class="anima-auth-panel" data-panel="login">
          <?php echo do_shortcode('[anima_login_form]'); ?>
          
        </div>

        <div class="anima-auth-panel" data-panel="register" style="display:none">
          <?php echo do_shortcode('[anima_register_form]'); ?>
        </div>
      </div>
    </div>
  </section>
</main>

<script>
(function(){
  const wrap = document.querySelector('[data-tabs]');
  if(!wrap) return;
  const tabs = wrap.querySelectorAll('.anima-auth-tab');
  const panels = document.querySelectorAll('.anima-auth-panel');
  function show(which){
    tabs.forEach(t => t.classList.toggle('is-active', t.dataset.tab===which));
    panels.forEach(p => p.style.display = (p.dataset.panel===which) ? '' : 'none');
  }
  tabs.forEach(t => t.addEventListener('click', () => show(t.dataset.tab)));
  const h = (location.hash||'').replace('#','');
  if(h==='register' || h==='login'){ show(h); }
})();
</script>

<?php get_footer(); ?>
