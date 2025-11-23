<?php
namespace Anima\Engine\Elementor;

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Widget_Base;
use WP_Query;

/**
 * Grid de cursos para Elementor con estilo Anima (Versión Self-Contained).
 */
class Widget_Cursos_Grid extends Widget_Base {
    
    public function get_name(): string {
        return 'anima-cursos-grid';
    }

    public function get_title(): string {
        return \__('Cursos — Grid', 'anima-engine');
    }

    public function get_icon(): string {
        return 'eicon-gallery-grid';
    }

    public function get_categories(): array {
        return ['anima'];
    }

    public function get_keywords(): array {
        return ['curso', 'grid', 'card', 'academy', 'anima'];
    }

    // --- Helper: Listar cursos ---
    protected function get_all_courses_list() {
        $courses = \get_posts([
            'post_type'      => 'curso',
            'posts_per_page' => 100,
            'post_status'    => 'publish',
            'fields'         => 'ids',
        ]);
        
        $options = [];
        if ( ! empty( $courses ) && ! \is_wp_error( $courses ) ) {
            foreach ( $courses as $id ) {
                $options[ $id ] = \get_the_title( $id ) . ' (ID: ' . $id . ')';
            }
        }
        return $options;
    }

    protected function register_controls(): void {
        $this->register_query_controls();
        $this->register_status_controls();
        $this->register_card_controls();
        $this->register_style_controls();
    }

    protected function register_status_controls(): void {
        $this->start_controls_section(
            'section_status',
            [ 'label' => \__('⚡ Estados y Etiquetas', 'anima-engine') ]
        );
        $this->add_control(
            'marked_coming_soon_ids',
            [
                'label'       => \__('Selector de "Próximamente"', 'anima-engine'),
                'type'        => Controls_Manager::SELECT2,
                'label_block' => true,
                'multiple'    => true,
                'options'     => $this->get_all_courses_list(),
            ]
        );
        $this->end_controls_section();
    }

    protected function register_query_controls(): void {
        $this->start_controls_section(
            'section_query',
            [ 'label' => \__('Consulta', 'anima-engine') ]
        );
        $this->add_control('posts_per_page', ['label' => \__('Entradas por página', 'anima-engine'), 'type' => Controls_Manager::NUMBER, 'default' => 8]);
        $this->add_control('orderby', ['label' => \__('Ordenar por', 'anima-engine'), 'type' => Controls_Manager::SELECT, 'default' => 'date', 'options' => ['date' => 'Fecha', 'title' => 'Título']]);
        $this->add_control('order', ['label' => \__('Dirección', 'anima-engine'), 'type' => Controls_Manager::SELECT, 'default' => 'DESC', 'options' => ['ASC' => 'Asc', 'DESC' => 'Desc']]);
        
        // Filtros de taxonomía
        $this->add_control('nivel_terms', ['label' => \__('Nivel', 'anima-engine'), 'type' => Controls_Manager::SELECT2, 'options' => $this->get_taxonomy_options('nivel'), 'multiple' => true]);
        $this->add_control('tecnologia_terms', ['label' => \__('Tecnología', 'anima-engine'), 'type' => Controls_Manager::SELECT2, 'options' => $this->get_taxonomy_options('tecnologia'), 'multiple' => true]);
        
        $this->add_control('enable_pagination', ['label' => \__('Paginación', 'anima-engine'), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes']);
        $this->end_controls_section();
    }

    protected function register_card_controls(): void {
        $this->start_controls_section(
            'section_card',
            [ 'label' => \__('Contenido de la tarjeta', 'anima-engine') ]
        );
        $this->add_control('show_image', ['label' => \__('Mostrar imagen', 'anima-engine'), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes']);
        $this->add_control('show_excerpt', ['label' => \__('Mostrar resumen', 'anima-engine'), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes']);
        $this->add_control('show_meta', ['label' => \__('Mostrar Info (Precio/Horas)', 'anima-engine'), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes']);
        $this->add_control('show_badges', ['label' => \__('Mostrar Etiquetas (Nivel/Tec)', 'anima-engine'), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes']);
        $this->add_control('button_text', ['label' => \__('Texto botón hover', 'anima-engine'), 'type' => Controls_Manager::TEXT, 'default' => \__('Ver curso', 'anima-engine')]);
        $this->end_controls_section();
    }

    protected function register_style_controls(): void {
        $this->start_controls_section(
            'section_layout_style',
            [ 'label' => \__('Diseño', 'anima-engine'), 'tab' => Controls_Manager::TAB_STYLE ]
        );
        $this->add_responsive_control(
            'columns',
            [
                'label' => \__('Columnas', 'anima-engine'),
                'type' => Controls_Manager::SLIDER,
                'range' => ['px' => ['min' => 1, 'max' => 6]],
                'default' => ['size' => 4],
                'selectors' => ['{{WRAPPER}} .an-grid' => 'grid-template-columns: repeat({{SIZE}}, minmax(0, 1fr));'],
            ]
        );
        // Estilos básicos para asegurar visibilidad si falta CSS externo
        $this->add_control(
            'card_bg',
            [
                'label' => 'Color Fondo Tarjeta',
                'type' => Controls_Manager::COLOR,
                'selectors' => ['{{WRAPPER}} .an-card' => 'background-color: {{VALUE}};'],
            ]
        );
        $this->end_controls_section();
    }

    protected function render(): void {
        $settings = $this->get_settings_for_display();
        $marked_ids = ! empty( $settings['marked_coming_soon_ids'] ) ? $settings['marked_coming_soon_ids'] : [];

        $paged = ( \get_query_var( 'paged' ) ) ? \get_query_var( 'paged' ) : 1;
        if( isset($_GET['anima_page']) ) $paged = \absint($_GET['anima_page']);

        $query_args = [
            'post_type'      => 'curso',
            'post_status'    => 'publish',
            'posts_per_page' => \absint( $settings['posts_per_page'] ?? 8 ),
            'orderby'        => \sanitize_text_field( $settings['orderby'] ?? 'date' ),
            'order'          => \sanitize_text_field( $settings['order'] ?? 'DESC' ),
            'paged'          => ( 'yes' === ( $settings['enable_pagination'] ?? '' ) ) ? $paged : 1,
        ];

        // Filtros de taxonomía (si se usan)
        $tax_query = [];
        if ( ! empty( $settings['nivel_terms'] ) ) {
            $tax_query[] = [ 'taxonomy' => 'nivel', 'field' => 'slug', 'terms' => $settings['nivel_terms'] ];
        }
        if ( ! empty( $settings['tecnologia_terms'] ) ) {
            $tax_query[] = [ 'taxonomy' => 'tecnologia', 'field' => 'slug', 'terms' => $settings['tecnologia_terms'] ];
        }
        if ( ! empty( $tax_query ) ) $query_args['tax_query'] = $tax_query;

        $query = new WP_Query( $query_args );

        if ( ! $query->have_posts() ) {
            echo '<div class="an-grid anima-grid-empty">' . \esc_html__('No hay cursos disponibles.', 'anima-engine') . '</div>';
            return;
        }

        echo '<div class="an-grid an-grid--courses">';

        while ( $query->have_posts() ) {
            $query->the_post();
            $post_id = \get_the_ID();
            $permalink = \get_permalink(); // URL DEL CURSO

            // --- 1. EXTRACCIÓN DE DATOS MANUAL (Directo de DB) ---
            
            // Meta: Duración y Precio (Claves basadas en cpt-curso.php)
            $hours = \get_post_meta( $post_id, 'anima_duration_hours', true );
            $price = \get_post_meta( $post_id, 'anima_price', true );

            // Taxonomías: Nivel y Tecnología
            $terms_nivel = \get_the_terms( $post_id, 'nivel' );
            $terms_tech  = \get_the_terms( $post_id, 'tecnologia' );
            
            // Resumen
            $excerpt = \get_the_excerpt();
            $excerpt = \wp_trim_words( $excerpt, 15, '...' );

            // Check "Próximamente"
            $is_coming_soon = is_array($marked_ids) && in_array( $post_id, $marked_ids );

            echo '<article class="an-card">';

            // --- IMAGEN ---
            if ( 'yes' === ( $settings['show_image'] ?? 'yes' ) ) {
                $thumbnail = get_the_post_thumbnail(
                    $post_id,
                    'anima_course_card', // Asegúrate de que este tamaño exista, si no usa 'medium_large'
                    [ 'class' => 'an-card__image', 'loading' => 'lazy' ]
                );

                if ( $thumbnail ) {
                    echo '<div class="an-card__media">' . $thumbnail;
                    
                    if ( $is_coming_soon ) {
                        // CASO A: Es "Próximamente" -> SOLO etiqueta, SIN enlace ni botón.
                        // Añadí estilos inline para asegurar que se vea bien de inmediato.
                        echo '<span class="an-card__label-coming-soon" style="position:absolute; top:10px; right:10px; background:#BC13FE; color:#fff; padding:5px 10px; font-size:0.8em; font-weight:bold; text-transform:uppercase; border-radius:4px; z-index:5;">' . esc_html__('PRÓXIMAMENTE', 'anima-engine') . '</span>';
                    } else {
                        // CASO B: Disponible -> Botón funcional con enlace.
                        echo '<a class="an-card__overlay" href="' . esc_url( $permalink ) . '" aria-label="' . esc_attr( sprintf( __( 'Ver curso: %s', 'anima-engine' ), get_the_title( $post_id ) ) ) . '">';
                        echo '<span class="an-card__overlay-button">' . esc_html( $settings['button_text'] ?: __('Ver curso', 'anima-engine') ) . '</span>';
                        echo '</a>';
                    }
                    
                    echo '</div>';
                }
            }

            // --- CUERPO ---
            echo '<div class="an-card__body">';
            
            // Título (con enlace por si acaso)
            echo '<h3 class="an-card__title"><a href="' . \esc_url( $permalink ) . '">' . \get_the_title() . '</a></h3>';

            // Resumen
            if ( 'yes' === $settings['show_excerpt'] && $excerpt ) {
                echo '<p class="an-card__excerpt">' . \esc_html( $excerpt ) . '</p>';
            }

            // --- METADATOS (CHIPS) ---
            if ( 'yes' === $settings['show_meta'] ) {
                echo '<div class="an-card__chips">';
                // Mostrar Precio si existe
                if ( $price ) {
                    echo '<span class="an-chip an-chip--price">' . \esc_html( $price ) . '€</span>';
                }
                // Mostrar Horas si existe
                if ( $hours ) {
                    echo '<span class="an-chip an-chip--hours">' . \esc_html( $hours ) . 'h</span>';
                }
                echo '</div>';
            }

            // --- ETIQUETAS (TAXONOMÍAS) ---
            if ( 'yes' === $settings['show_badges'] ) {
                echo '<div class="an-card__badges">';
                
                // Badges de Nivel
                if ( ! empty( $terms_nivel ) && ! \is_wp_error( $terms_nivel ) ) {
                    foreach ( $terms_nivel as $term ) {
                        echo '<span class="an-badge an-badge--level">' . \esc_html( $term->name ) . '</span>';
                    }
                }
                // Badges de Tecnología
                if ( ! empty( $terms_tech ) && ! \is_wp_error( $terms_tech ) ) {
                    foreach ( $terms_tech as $term ) {
                        echo '<span class="an-badge an-badge--tech">' . \esc_html( $term->name ) . '</span>';
                    }
                }
                echo '</div>';
            }

            echo '</div>'; // Fin body
            echo '</article>';
        }

        echo '</div>'; // Fin grid

        // Paginación sencilla
        if ( 'yes' === ( $settings['enable_pagination'] ?? '' ) && $query->max_num_pages > 1 ) {
             echo '<div class="an-pagination">';
             echo \paginate_links([
                'total' => $query->max_num_pages,
                'current' => $paged,
                'format' => '?anima_page=%#%'
             ]);
             echo '</div>';
        }

        \wp_reset_postdata();
    }

    private function get_taxonomy_options( string $taxonomy ): array {
        $terms = \get_terms( [ 'taxonomy' => $taxonomy, 'hide_empty' => false ] );
        $options = [];
        if ( ! \is_wp_error( $terms ) && ! empty( $terms ) ) {
            foreach ( $terms as $term ) {
                $options[ $term->slug ] = $term->name;
            }
        }
        return $options;
    }
}