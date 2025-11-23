<?php
/**
 * Template Name: Anima Academy Archive
 * Description: Super Learning Center layout for courses.
 */

get_header();
?>

<div class="academy-wrapper">
    <!-- Academy Hero -->
    <section class="academy-hero relative">
        <div class="container relative z-10">
            <span class="badge pill-gradient">Anima Academy</span>
            <h1 class="hero__title">Centro de Entrenamiento Neural</h1>
            <p class="hero__subtitle">Domina las artes del metaverso. Gana XP, desbloquea logros y construye tu legado.
            </p>
        </div>
        <div class="hero-bg-overlay"></div>
    </section>

    <div class="container flex flex-wrap gap-30" style="margin-top: -50px; position: relative; z-index: 20;">

        <!-- Main Content: Course Grid -->
        <main class="academy-main flex-2">

            <div class="academy-filters flex flex-between flex-center mb-30">
                <h2 class="section__title m-0">Protocolos Disponibles</h2>
                <div class="filter-controls">
                    <!-- Future: Add filters here -->
                </div>
            </div>

            <?php if (have_posts()): ?>
                <div class="course-grid">
                    <?php while (have_posts()):
                        the_post();
                        $course_id = get_the_ID();
                        $user_id = get_current_user_id();
                        $progress = function_exists('learndash_course_progress') ? learndash_course_progress(array('user_id' => $user_id, 'course_id' => $course_id, 'array' => true)) : array('percentage' => 0);
                        $percentage = $progress['percentage'] ?? 0;
                        ?>
                        <article id="post-<?php the_ID(); ?>" <?php post_class('course-card'); ?>>
                            <div class="course-card__thumb relative">
                                <a href="<?php the_permalink(); ?>">
                                    <?php if (has_post_thumbnail()) {
                                        the_post_thumbnail('medium_large');
                                    } else {
                                        echo '<div class="placeholder-thumb"></div>';
                                    } ?>
                                </a>
                                <div class="course-xp-badge">
                                    <span class="dashicons dashicons-awards"></span> +500 XP
                                </div>
                            </div>
                            <div class="course-card__content">
                                <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                                <div class="course-meta flex flex-between">
                                    <span class="author">By <?php the_author(); ?></span>
                                    <span class="lessons-count">8 Módulos</span>
                                </div>

                                <?php if (is_user_logged_in()): ?>
                                    <div class="course-progress-bar mt-15">
                                        <div class="progress-fill" style="width: <?php echo esc_attr($percentage); ?>%;"></div>
                                    </div>
                                    <span class="progress-text"><?php echo esc_html($percentage); ?>% Completado</span>
                                <?php endif; ?>

                                <a class="button button-small mt-15 full-width text-center" href="<?php the_permalink(); ?>">
                                    <?php echo ($percentage > 0) ? 'Continuar Protocolo' : 'Iniciar Protocolo'; ?>
                                </a>
                            </div>
                        </article>
                    <?php endwhile; ?>
                </div>

                <nav class="pagination mt-50">
                    <?php the_posts_pagination(); ?>
                </nav>

            <?php else: ?>
                <p>No hay protocolos activos en este momento.</p>
            <?php endif; ?>

        </main>

        <!-- Sidebar: AI Mentor -->
        <aside class="academy-sidebar flex-1">
            <div class="sticky-sidebar">
                <div class="widget-card ai-mentor-widget">
                    <h3><span class="dashicons dashicons-superhero"></span> AI Mentor</h3>
                    <p class="small-text mb-15">¿Dudas sobre tu entrenamiento? Consulta al Oráculo de la Academia.</p>
                    <?php echo do_shortcode('[anima_ai id="academy_mentor"]'); ?>
                </div>

                <div class="widget-card mt-30">
                    <h3>Top Estudiantes</h3>
                    <ul class="leaderboard-list">
                        <li>
                            <span class="rank">#1</span>
                            <span class="user">NeonSamurai</span>
                            <span class="xp">12,500 XP</span>
                        </li>
                        <li>
                            <span class="rank">#2</span>
                            <span class="user">CyberVixen</span>
                            <span class="xp">10,200 XP</span>
                        </li>
                        <li>
                            <span class="rank">#3</span>
                            <span class="user">GlitchMaster</span>
                            <span class="xp">9,800 XP</span>
                        </li>
                    </ul>
                </div>
            </div>
        </aside>

    </div>
</div>

<style>
    /* Academy Specific Styles - Should be moved to CSS file later */
    .academy-hero {
        background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.9)), url('<?php echo get_template_directory_uri(); ?>/assets/images/academy-bg.jpg');
        background-size: cover;
        background-position: center;
        padding: 100px 0 150px;
        text-align: center;
        border-bottom: 1px solid var(--purple);
    }

    .academy-wrapper {
        background-color: #050505;
        min-height: 100vh;
    }

    .course-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 25px;
    }

    .course-card {
        background: #111;
        border: 1px solid #333;
        border-radius: 12px;
        overflow: hidden;
        transition: transform 0.3s, box-shadow 0.3s;
    }

    .course-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0 20px rgba(188, 19, 254, 0.3);
        border-color: var(--purple);
    }

    .course-card__thumb {
        height: 180px;
        background: #222;
        overflow: hidden;
    }

    .course-card__thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s;
    }

    .course-card:hover .course-card__thumb img {
        transform: scale(1.1);
    }

    .course-xp-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        background: rgba(0, 0, 0, 0.8);
        color: #00FF94;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.8rem;
        font-weight: bold;
        border: 1px solid #00FF94;
    }

    .course-card__content {
        padding: 20px;
    }

    .course-card__content h3 {
        margin: 0 0 10px;
        font-size: 1.2rem;
    }

    .course-card__content h3 a {
        color: #fff;
        text-decoration: none;
    }

    .course-meta {
        color: #888;
        font-size: 0.9rem;
    }

    .course-progress-bar {
        height: 6px;
        background: #333;
        border-radius: 3px;
        overflow: hidden;
    }

    .progress-fill {
        height: 100%;
        background: linear-gradient(90deg, var(--cyan), var(--purple));
    }

    .progress-text {
        font-size: 0.8rem;
        color: #ccc;
        display: block;
        text-align: right;
        margin-top: 5px;
    }

    .widget-card {
        background: #111;
        border: 1px solid #333;
        padding: 20px;
        border-radius: 8px;
    }

    .leaderboard-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .leaderboard-list li {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid #222;
    }

    .leaderboard-list li:last-child {
        border-bottom: none;
    }

    .leaderboard-list .rank {
        color: var(--cyan);
        font-weight: bold;
        width: 30px;
    }

    .leaderboard-list .user {
        color: #fff;
        flex: 1;
    }

    .leaderboard-list .xp {
        color: var(--purple);
        font-weight: bold;
    }

    @media (max-width: 768px) {
        .container.flex {
            flex-direction: column;
        }

        .academy-sidebar {
            order: -1;
            /* Sidebar on top on mobile? Or bottom? Let's keep it bottom for now, or maybe top for AI */
            margin-bottom: 30px;
        }
    }
</style>

<?php get_footer(); ?>