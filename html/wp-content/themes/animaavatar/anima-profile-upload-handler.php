<?php
/**
 * Anima Avatar Agency: Profile Picture Upload Handler
 * Procesador dedicado de la foto de perfil (sin tocar functions.php)
 */

// Carga el entorno principal de WordPress (ASUME que el archivo está en la carpeta del tema)
// Ajusta la ruta '../../../wp-load.php' si es necesario.
require_once( '../../../wp-load.php' );

// Cargar dependencias de medios necesarias para el procesamiento de imágenes
require_once( ABSPATH . 'wp-admin/includes/image.php' );
require_once( ABSPATH . 'wp-admin/includes/file.php' );
require_once( ABSPATH . 'wp-admin/includes/media.php' );

// 1. Verificación de seguridad y login
if ( ! is_user_logged_in() ) {
    wp_die( 'Acceso denegado: Debes iniciar sesión.', '', array( 'response' => 403 ) );
}

if ( ! isset( $_POST['profile_picture_nonce'] ) || 
     ! wp_verify_nonce( $_POST['profile_picture_nonce'], 'upload_profile_picture_action' ) ||
     empty( $_FILES['profile_picture']['name'] ) ) {
    wp_redirect( wp_get_referer() ); // Redirigir si falla la seguridad o falta el archivo
    exit;
}

// --- Procesar Subida ---
$user_id = get_current_user_id();
$file = $_FILES['profile_picture'];
$upload_overrides = array( 'test_form' => false );

$movefile = wp_handle_upload( $file, $upload_overrides );

if ( $movefile && ! isset( $movefile['error'] ) ) {
    // 1. Insertar en la Biblioteca de Medios
    $attach_id = wp_insert_attachment(
        array(
            'guid'           => $movefile['url'],
            'post_mime_type' => $movefile['type'],
            'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $movefile['file'] ) ),
            'post_content'   => '',
            'post_status'    => 'inherit'
        ),
        $movefile['file']
    );

    // 2. Generar miniaturas y metadatos
    if ( ! is_wp_error( $attach_id ) ) {
        $attach_data = wp_generate_attachment_metadata( $attach_id, $movefile['file'] );
        wp_update_attachment_metadata( $attach_id, $attach_data );
        
        // 3. Guardar el ID de la imagen en los metadatos del usuario
        update_user_meta( $user_id, 'profile_picture', $attach_id );
    }

    wp_redirect( add_query_arg( 'upload_status', 'success', wp_get_referer() ) );
    exit;

} else {
    wp_redirect( add_query_arg( 'upload_status', 'error_upload', wp_get_referer() ) );
    exit;
}