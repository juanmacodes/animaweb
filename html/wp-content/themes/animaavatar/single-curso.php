<?php
/**
 * Template Name: Anima Cyberpunk Course Layout (Secure + Progress)
 */

get_header();

$course_id = get_the_ID();
$user_id   = get_current_user_id();

// 1. DATOS DEL CURSO
$subtitle   = get_post_meta($course_id, '_anima_course_subtitle', true);
$level      = get_post_meta($course_id, '_anima_course_level', true);
$duration   = get_post_meta($course_id, '_anima_course_duration', true);
$product_id = get_post_meta($course_id, '_anima_product_id', true);
$bg_image   = get_the_post_thumbnail_url($course_id, 'full');

$syllabus_json = get_post_meta($course_id, '_anima_syllabus_json', true);
$syllabus = json_decode($syllabus_json, true);
$syllabus = is_array($syllabus) ? $syllabus : [];

$downloads_json = get_post_meta($course_id, '_anima_course_downloads', true);
$downloads = json_decode($downloads_json, true);
$downloads = is_array($downloads) ? $downloads : [];

// 2. LÓGICA DE ACCESO
$has_bought_course = false;
$is_purchasable = true;
$product = null;

if ($product_id && function_exists('wc_get_product')) {
    $product = wc_get_product($product_id);
    if ($product) {
        $is_purchasable = $product->is_purchasable() && $product->is_in_stock();
        if ($user_id > 0 && function_exists('wc_customer_bought_product')) {
            $has_bought_course = wc_customer_bought_product(wp_get_current_user()->user_email, $user_id, $product_id);
        }
    }
}

// 3. CÁLCULO DE PROGRESO
$total_lessons = 0;
$user_progress = [];
if ($has_bought_course) {
    $user_progress = get_user_meta($user_id, '_anima_progress_' . $course_id, true);
    if (!is_array($user_progress)) $user_progress = [];
    
    // Contar total lecciones
    foreach($syllabus as $mod) {
        if(isset($mod['lessons']) && is_array($mod['lessons'])) {
            $total_lessons += count($mod['lessons']);
        }
    }
    $completed_count = count($user_progress);
    $percent = ($total_lessons > 0) ? round(($completed_count / $total_lessons) * 100) : 0;
} else {
    $percent = 0;
}

// 4. CRÉDITOS
$course_credit_cost = $product_id ? (int) get_post_meta($product_id, 'anima_course_credit_cost', true) : 0;
$agent_stats = function_exists('anima_get_agent_stats') ? anima_get_agent_stats() : ['credits' => 0];
$agent_credits = $agent_stats['credits'];

// 5. CTA BUTTON
$cta_html = '';
if ( ! is_user_logged_in() ) {
    $cta_html = sprintf('<a href="%s" class="action-btn purchase-btn" style="background:#BC13FE!important;color:#fff!important;box-shadow:0 0 20px #BC13FE;">INICIAR SESIÓN</a>', esc_url(home_url('/login/')));
} elseif ( $has_bought_course ) {
    $cta_html = '<a href="#syllabus" class="action-btn access-btn" style="background:#00FF94!important;color:#000!important;box-shadow:0 0 20px #00FF94;">CONTINUAR ENTRENAMIENTO</a>';
} elseif ( $course_credit_cost > 0 ) {
    if ($agent_credits >= $course_credit_cost) {
        $cta_html = sprintf(
            '<form class="redeem-form" method="post" action="%s">
                <input type="hidden" name="action" value="anima_redeem_course" />
                <input type="hidden" name="course_id" value="%d" />
                <input type="hidden" name="cost" value="%d" />
                %s
                <button type="submit" class="action-btn purchase-btn">CANJEAR (%s CR)</button>
            </form>',
            esc_url( admin_url( 'admin-post.php' ) ), esc_attr($course_id), esc_attr($course_credit_cost),
            wp_nonce_field( 'anima_redeem_action', 'redeem_nonce', true, false ), number_format($course_credit_cost)
        );
    } else {
        $cta_html = sprintf('<a href="%s" class="action-btn purchase-btn">RECARGAR CRÉDITOS</a>', esc_url(home_url('/mi-cuenta/?view=recharge')));
    }
} elseif ( $is_purchasable ) {
    $cta_html = sprintf('<a href="%s" class="action-btn purchase-btn">ADQUIRIR ACCESO</a>', esc_url($product->add_to_cart_url()));
}
?>

<style>
    /* Estilos Base */
    .course-hero { position: relative; height: 50vh; min-height: 400px; display: flex; align-items: flex-end; padding-bottom: 60px; background-size: cover; background-position: center; }
    .course-hero::before { content: ''; position: absolute; inset: 0; background: linear-gradient(0deg, #050505 0%, rgba(5,5,5,0.6) 100%); }
    .hero-content { position: relative; z-index: 2; width: 100%; max-width: 1400px; margin: 0 auto; padding: 0 20px; }
    .hero-title { font-size: 3.5rem; line-height: 1.1; color: #fff; text-transform: uppercase; text-shadow: 0 0 20px rgba(0,0,0,0.8); margin: 10px 0; }
    
    .course-layout { max-width: 1400px; margin: 0 auto; padding: 60px 20px; display: grid; grid-template-columns: minmax(0, 2fr) minmax(300px, 1fr); gap: 50px; }
    
    /* Barra de Progreso */
    .progress-container { background: #111; border: 1px solid #333; padding: 15px; border-radius: 8px; margin-bottom: 30px; }
    .progress-label { display: flex; justify-content: space-between; color: #00F0FF; font-weight: 700; margin-bottom: 5px; font-family: 'Rajdhani'; text-transform: uppercase; }
    .progress-track { width: 100%; height: 8px; background: #222; border-radius: 4px; overflow: hidden; }
    .progress-fill { height: 100%; background: #00FF94; width: 0%; transition: width 0.5s ease; box-shadow: 0 0 10px #00FF94; }

    /* Temario */
    .module-item { margin-bottom: 15px; border: 1px solid #333; background: rgba(20,20,25,0.5); border-radius: 6px; overflow: hidden; }
    .module-title { padding: 15px; cursor: pointer; background: #151518; color: #fff; font-weight: 700; display: flex; align-items: center; gap: 10px; transition: 0.3s; }
    .module-title:hover { background: #1f1f25; color: #00F0FF; }
    .module-lessons { display: none; list-style: none; margin: 0; padding: 0; }
    .module-lessons.open { display: block; }
    .lesson-item { padding: 12px 20px; border-top: 1px solid #222; display: flex; align-items: center; justify-content: space-between; transition: 0.2s; }
    .lesson-item:hover { background: rgba(255,255,255,0.02); }
    
    .lesson-left { display: flex; align-items: center; gap: 15px; }
    .lesson-check { 
        appearance: none; width: 20px; height: 20px; border: 2px solid #555; border-radius: 4px; cursor: pointer; position: relative; 
        background: #000; transition: 0.2s;
    }
    .lesson-check:checked { background: #00FF94; border-color: #00FF94; }
    .lesson-check:checked::after { content: '✔'; position: absolute; top: -2px; left: 3px; color: #000; font-size: 14px; font-weight: bold; }

    .lesson-link { color: #ccc; text-decoration: none; transition: 0.2s; }
    .lesson-link:hover { color: #fff; }
    .lesson-link.locked { color: #555; cursor: not-allowed; }
    
    /* Sidebar */
    .course-sidebar { background: #0F0F12; border: 1px solid #2A2A35; padding: 30px; border-radius: 12px; position: sticky; top: 100px; }
    .action-btn { display: block; width: 100%; padding: 15px; text-align: center; font-weight: 700; text-transform: uppercase; text-decoration: none; border-radius: 6px; margin-bottom: 20px; }
    
    @media (max-width: 900px) { .course-layout { grid-template-columns: 1fr; } }
</style>

<div id="primary" class="content-area anima-dark-theme">
    <main id="main" class="site-main">

    <?php while ( have_posts() ) : the_post(); ?>
        <div class="course-hero" style="background-image: url('<?php echo esc_url($bg_image); ?>');">
            <div class="hero-content">
                <span style="background:#00F0FF; color:#000; padding:2px 8px; font-weight:bold; font-size:0.8em;">UNREAL ENGINE 5</span>
                <h1 class="hero-title"><?php the_title(); ?></h1>
                <p style="color:#ccc; font-size:1.2rem;"><?php echo esc_html($subtitle); ?></p>
            </div>
        </div>
        
        <div class="course-layout">
            
            <div class="course-main-content">
                
                <?php if ($has_bought_course): ?>
                    <div class="progress-container">
                        <div class="progress-label">
                            <span>Sincronización Neural</span>
                            <span id="progress-text"><?php echo $percent; ?>%</span>
                        </div>
                        <div class="progress-track">
                            <div class="progress-fill" style="width: <?php echo $percent; ?>%;"></div>
                        </div>
                    </div>
                <?php endif; ?>

                <section class="course-description">
                    <h2 style="color:#00F0FF; border-bottom:1px solid #333; padding-bottom:10px; margin-bottom:20px;">INFORMACIÓN DE LA MISIÓN</h2>
                    <div class="entry-content" style="color:#bbb; line-height:1.6;"><?php the_content(); ?></div>
                </section>

                <?php if (!empty($syllabus)): ?>
                <section class="course-syllabus" id="syllabus" style="margin-top:40px;">
                    <h2 style="color:#00F0FF; border-bottom:1px solid #333; padding-bottom:10px; margin-bottom:20px;">PLAN DE ESTUDIOS</h2>

                    <?php foreach ($syllabus as $mod_idx => $module) : ?>
                        <div class="module-item">
                            <div class="module-title" onclick="this.nextElementSibling.classList.toggle('open')">
                                <span class="dashicons dashicons-arrow-down-alt2"></span>
                                <?php echo esc_html($module['title'] ?? ($module['name'] ?? 'Módulo')); ?>
                            </div>
                            
                            <ul class="module-lessons <?php echo $mod_idx===0?'open':''; ?>">
                                <?php 
                                $lessons = (isset($module['lessons']) && is_array($module['lessons'])) ? $module['lessons'] : [];
                                foreach ($lessons as $less_idx => $lesson) : 
                                    $lesson_name = isset($lesson['title']) ? $lesson['title'] : 'Lección';
                                    $vid_file = isset($lesson['video']) ? $lesson['video'] : '';
                                    
                                    // ID Único para tracking: mod_0_less_1
                                    $lesson_uid = 'mod_' . $mod_idx . '_less_' . $less_idx;
                                    $is_checked = in_array($lesson_uid, $user_progress);

                                    // URL Segura (Proxy)
                                    $vid_url = '';
                                    if ($vid_file) {
                                        $vid_url = home_url( '/?anima_video=true&course_id=' . $course_id . '&file=' . urlencode($vid_file) );
                                    }
                                ?>
                                    <li class="lesson-item">
                                        <div class="lesson-left">
                                            <?php if($has_bought_course): ?>
                                                <input type="checkbox" class="lesson-check" data-lid="<?php echo $lesson_uid; ?>" <?php checked($is_checked); ?>>
                                            <?php else: ?>
                                                <span class="dashicons dashicons-lock" style="color:#444;"></span>
                                            <?php endif; ?>

                                            <?php if ($has_bought_course && $vid_url): ?>
                                                <a href="<?php echo esc_url($vid_url); ?>" target="_blank" class="lesson-link">
                                                    <span class="dashicons dashicons-media-play" style="font-size:0.8em; margin-right:5px;"></span>
                                                    <?php echo esc_html($lesson_name); ?>
                                                </a>
                                            <?php else: ?>
                                                <span class="lesson-link locked"><?php echo esc_html($lesson_name); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <span style="font-size:0.8em; color:#555;">VÍDEO</span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endforeach; ?>
                </section>
                <?php endif; ?>

            </div>
            
            <aside class="course-sidebar">
                <div style="text-align:center; margin-bottom:20px; padding-bottom:20px; border-bottom:1px solid #2A2A35;">
                    <?php if ($course_credit_cost > 0): ?>
                        <span style="font-size:2.5rem; color:#00FF94; font-weight:700; font-family:'Rajdhani';"><?php echo number_format($course_credit_cost,0,',','.'); ?> Créditos</span>
                        <p style="color:#666; margin:0;">Costo de acceso</p>
                    <?php else: ?>
                        <?php echo $product ? $product->get_price_html() : ''; ?>
                    <?php endif; ?>
                </div>

                <?php echo $cta_html; ?>

                <ul style="list-style:none; padding:0; color:#888;">
                    <li style="padding:8px 0; border-bottom:1px solid #222; display:flex; justify-content:space-between;"><span>Duración:</span> <span style="color:#fff;"><?php echo esc_html($duration); ?></span></li>
                    <li style="padding:8px 0; border-bottom:1px solid #222; display:flex; justify-content:space-between;"><span>Nivel:</span> <span style="color:#fff;"><?php echo esc_html($level); ?></span></li>
                    <li style="padding:8px 0; border-bottom:1px solid #222; display:flex; justify-content:space-between;"><span>Acceso:</span> <span style="color:#fff;">Vitalicio</span></li>
                </ul>

                <?php if(!empty($downloads) && $has_bought_course): ?>
                    <div style="margin-top:30px;">
                        <h4 style="color:#fff;">ARCHIVOS DE MISIÓN</h4>
                        <?php foreach($downloads as $dl): 
                            // Aquí podrías hacer otro proxy para descargas seguras también
                            $dl_url = content_url( '/anima-protected/courses/' . $course_id . '/downloads/' . ($dl['file'] ?? '') );
                        ?>
                            <a href="<?php echo esc_url($dl_url); ?>" download class="action-btn" style="background:transparent; border:1px solid #00F0FF; color:#00F0FF; margin-bottom:10px; font-size:0.9em;">
                                <span class="dashicons dashicons-download"></span> <?php echo esc_html($dl['name']); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </aside>

        </div>
    <?php endwhile; ?>
    </main>
</div>

<?php if($has_bought_course): ?>
<script>
document.querySelectorAll('.lesson-check').forEach(chk => {
    chk.addEventListener('change', async function() {
        const lid = this.dataset.lid;
        const fd = new FormData();
        fd.append('action', 'anima_toggle_lesson');
        fd.append('course_id', '<?php echo $course_id; ?>');
        fd.append('lesson_id', lid);
        fd.append('total_lessons', '<?php echo $total_lessons; ?>');

        try {
            const res = await fetch('<?php echo admin_url('admin-ajax.php'); ?>', {method:'POST', body:fd});
            const data = await res.json();
            if(data.success) {
                // Actualizar barra
                document.querySelector('.progress-fill').style.width = data.data.percent + '%';
                document.getElementById('progress-text').innerText = data.data.percent + '%';
            }
        } catch(e) { console.error(e); }
    });
});
</script>
<?php endif; ?>

<?php get_footer(); ?>