<?php
/**
 * Template Name: Anima Academy Single Course
 */

get_header();
?>

<div class="academy-single-wrapper">
    <?php while (have_posts()):
        the_post();
        $user_id = get_current_user_id();
        $course_id = get_the_ID();
        $progress = function_exists('learndash_course_progress') ? learndash_course_progress(array('user_id' => $user_id, 'course_id' => $course_id, 'array' => true)) : array('percentage' => 0);
        $percentage = $progress['percentage'] ?? 0;
        ?>

        <!-- Course Header -->
        <header class="course-header relative">
            <div class="container relative z-10">
                <div class="breadcrumbs mb-20">
                    <a href="<?php echo home_url('/courses'); ?>">Academy</a> <span class="sep">/</span> <span
                        class="current"><?php the_title(); ?></span>
                </div>
                <h1 class="course-title"><?php the_title(); ?></h1>
                <div class="course-meta flex gap-20 mt-10">
                    <span class="meta-item"><span class="dashicons dashicons-clock"></span> 2h 30m</span>
                    <span class="meta-item"><span class="dashicons dashicons-awards"></span> 500 XP</span>
                    <span class="meta-item"><span class="dashicons dashicons-admin-users"></span> 1.2k Estudiantes</span>
                </div>
            </div>
            <div class="header-bg-overlay"></div>
        </header>

        <div class="container flex flex-wrap gap-30 mt-30 pb-50">

            <!-- Main Content -->
            <main class="course-content flex-2">

                <!-- Video / Featured Image -->
                <div class="course-media mb-30">
                    <?php if (has_post_thumbnail()) {
                        the_post_thumbnail('full');
                    } ?>
                </div>

                <!-- Description -->
                <div class="course-description card mb-30">
                    <h2>Descripción del Protocolo</h2>
                    <?php the_content(); ?>
                </div>

                <!-- Course Content (LearnDash Shortcode usually handles this, but we wrap it) -->
                <div class="course-modules card mb-30">
                    <h2>Módulos de Entrenamiento</h2>
                    <!-- This would typically be [course_content] or similar -->
                    <div class="modules-list">
                        <?php
                        // Fallback if LearnDash content isn't rendering automatically
                        // In a real LD theme, we might use specific functions here.
                        // For now, we assume the_content() outputs the course steps or we leave a placeholder.
                        ?>
                        <p class="muted">El contenido del curso se carga dinámicamente aquí.</p>
                    </div>
                </div>

                <!-- Practical Challenge -->
                <div class="practical-challenge card border-purple">
                    <div class="flex flex-between flex-center mb-20">
                        <h2 class="m-0 text-purple">Reto Práctico: <?php the_title(); ?></h2>
                        <span class="badge pill-purple">Recompensa: 100 Créditos</span>
                    </div>
                    <p>Aplica lo aprendido. Sube tu resultado para recibir feedback de los mentores.</p>
                    <div class="upload-zone dashed-border p-30 text-center mt-20">
                        <span class="dashicons dashicons-cloud-upload" style="font-size: 40px; color: #888;"></span>
                        <p class="mt-10">Arrastra tu archivo o haz clic para subir (.zip, .fbx, .png)</p>
                        <button class="button button-small mt-10">Seleccionar Archivo</button>
                    </div>
                </div>

            </main>

            <!-- Sidebar -->
            <aside class="course-sidebar flex-1">
                <div class="sticky-sidebar">

                    <!-- Progress Widget -->
                    <div class="widget-card mb-20">
                        <h3>Tu Progreso</h3>
                        <div class="course-progress-bar big mt-10 mb-10">
                            <div class="progress-fill" style="width: <?php echo esc_attr($percentage); ?>%;"></div>
                        </div>
                        <div class="flex flex-between small-text">
                            <span><?php echo esc_html($percentage); ?>% Completado</span>
                            <span><?php echo ($percentage < 100) ? 'En curso' : 'Completado'; ?></span>
                        </div>
                        <a href="#" class="button full-width mt-20">Continuar Lección</a>
                    </div>

                    <!-- AI Tutor Widget -->
                    <div class="widget-card ai-tutor-widget">
                        <h3><span class="dashicons dashicons-welcome-learn-more"></span> AI Tutor</h3>
                        <p class="small-text mb-15">Estoy aquí para ayudarte con este curso. ¡Pregúntame lo que sea!</p>
                        <?php echo do_shortcode('[anima_ai id="course_tutor"]'); ?>
                    </div>

                    <!-- Instructor -->
                    <div class="widget-card mt-20">
                        <h3>Instructor</h3>
                        <div class="instructor-profile flex gap-15 flex-center">
                            <?php echo get_avatar(get_the_author_meta('ID'), 50); ?>
                            <div>
                                <h4 class="m-0"><?php the_author(); ?></h4>
                                <span class="small-text muted">Senior Avatar Artist</span>
                            </div>
                        </div>
                    </div>

                </div>
            </aside>

        </div>

    <?php endwhile; ?>
</div>

<style>
    .course-header {
        background: #111;
        padding: 60px 0;
        border-bottom: 1px solid #333;
    }

    .course-title {
        font-size: 2.5rem;
        margin: 0;
        text-shadow: 0 0 10px rgba(0, 240, 255, 0.3);
    }

    .breadcrumbs {
        color: #888;
        font-size: 0.9rem;
    }

    .breadcrumbs a {
        color: #ccc;
        text-decoration: none;
    }

    .breadcrumbs .current {
        color: var(--cyan);
    }

    .meta-item {
        display: flex;
        align-items: center;
        gap: 5px;
        color: #ccc;
        font-size: 0.9rem;
    }

    .course-media img {
        width: 100%;
        border-radius: 8px;
        border: 1px solid #333;
    }

    .course-progress-bar.big {
        height: 10px;
        background: #222;
        border-radius: 5px;
    }

    .upload-zone {
        border: 2px dashed #444;
        border-radius: 8px;
        transition: all 0.3s;
        background: rgba(255, 255, 255, 0.02);
    }

    .upload-zone:hover {
        border-color: var(--purple);
        background: rgba(188, 19, 254, 0.05);
    }

    .instructor-profile img {
        border-radius: 50%;
        border: 2px solid var(--cyan);
    }
</style>

<?php get_footer(); ?>