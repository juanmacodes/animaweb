<?php
/**
 * Registro del tipo de contenido Curso.
 * Versión Limpia: Solo registra el CPT. Los datos se manejan en admin-course-fields.php.
 *
 * @package Anima\Engine
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'anima_engine_register_curso_post_type' ) ) {
    /**
     * Registra el CPT Curso.
     */
    function anima_engine_register_curso_post_type(): void {
        if ( post_type_exists( 'curso' ) ) {
            return;
        }

        $labels = [
            'name'               => _x( 'Cursos', 'Post Type General Name', 'anima-engine' ),
            'singular_name'      => _x( 'Curso', 'Post Type Singular Name', 'anima-engine' ),
            'menu_name'          => __( 'Cursos', 'anima-engine' ),
            'name_admin_bar'     => __( 'Curso', 'anima-engine' ),
            'add_new'            => __( 'Añadir nuevo', 'anima-engine' ),
            'add_new_item'       => __( 'Añadir nuevo curso', 'anima-engine' ),
            'edit_item'          => __( 'Editar curso', 'anima-engine' ),
            'new_item'           => __( 'Nuevo curso', 'anima-engine' ),
            'view_item'          => __( 'Ver curso', 'anima-engine' ),
            'search_items'       => __( 'Buscar cursos', 'anima-engine' ),
            'not_found'          => __( 'No se encontraron cursos.', 'anima-engine' ),
            'not_found_in_trash' => __( 'No hay cursos en la papelera.', 'anima-engine' ),
            'all_items'          => __( 'Todos los cursos', 'anima-engine' ),
        ];

        register_post_type(
            'curso',
            [
                'label'               => __( 'Curso', 'anima-engine' ),
                'labels'              => $labels,
                'public'              => true,
                'show_ui'             => true,
                'show_in_menu'        => true,
                'menu_position'       => 21,
                'menu_icon'           => 'dashicons-welcome-learn-more',
                'has_archive'         => 'cursos',
                'rewrite'             => [ 'slug' => 'curso', 'with_front' => false ],
                'supports'            => [ 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ], // 'custom-fields' habilitado por si acaso
                'show_in_rest'        => true, // Necesario para el editor de bloques
                'rest_base'           => 'curso',
                'publicly_queryable'  => true,
                'map_meta_cap'        => true,
            ]
        );
        
        // Registramos las taxonomías aquí mismo para tenerlo todo junto y ordenado
        $taxonomies = [
            'nivel'      => 'Nivel',
            'modalidad'  => 'Modalidad',
            'tecnologia' => 'Tecnología'
        ];

        foreach ($taxonomies as $slug => $label) {
            register_taxonomy($slug, ['curso'], [
                'label' => $label,
                'rewrite' => ['slug' => $slug],
                'hierarchical' => true,
                'show_in_rest' => true,
            ]);
        }
    }
}

add_action( 'init', 'anima_engine_register_curso_post_type' );
?>