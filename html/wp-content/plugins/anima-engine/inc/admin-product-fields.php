<?php
// Archivo para añadir campos custom al producto de WooCommerce.

defined( 'ABSPATH' ) || exit;

add_action( 'woocommerce_product_options_general_product_data', 'anima_add_course_credit_field' );

function anima_add_course_credit_field() {
    global $woocommerce_errors;
    
    echo '<div class="options_group">';
    
    // Campo de entrada de Créditos Neuronales
    woocommerce_wp_text_input(
        array(
            'id'          => 'anima_course_credit_cost',
            'value'       => get_post_meta( get_the_ID(), 'anima_course_credit_cost', true ),
            'label'       => __( 'Costo en Créditos Neuronales', 'anima-engine' ),
            'placeholder' => __( 'Ej: 1500', 'anima-engine' ),
            'desc_tip'    => true,
            'description' => __( 'Créditos necesarios para canjear este curso.', 'anima-engine' ),
            'data_type'   => 'price', // Asegura el formato numérico
        )
    );
    
    echo '</div>';
}

add_action( 'woocommerce_process_product_meta', 'anima_save_course_credit_field' );

function anima_save_course_credit_field( $post_id ) {
    $cost = isset( $_POST['anima_course_credit_cost'] ) ? sanitize_text_field( $_POST['anima_course_credit_cost'] ) : '';
    update_post_meta( $post_id, 'anima_course_credit_cost', $cost );
}