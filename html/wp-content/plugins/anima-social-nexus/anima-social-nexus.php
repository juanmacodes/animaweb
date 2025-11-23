<?php
/**
 * Plugin Name: Anima Social Nexus
 * Description: Sistema de conexiones neuronales y transmisiones sociales pro-activas para la comunidad Cyberpunk.
 * Version: 1.0.0 alpha
 * Author: Anima Avatar Agency
 * Text Domain: anima-nexus
 */

// Evitar acceso directo si no es WordPress
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Definir constantes para rutas del plugin (útiles para más adelante)
define( 'ANIMA_NEXUS_PATH', plugin_dir_path( __FILE__ ) );
define( 'ANIMA_NEXUS_URL', plugin_dir_url( __FILE__ ) );
define( 'ANIMA_NEXUS_DB_TABLE', 'anima_connections' ); // Nombre base de nuestra tabla

/**
 * =================================================================
 * 1. ACTIVACIÓN E INSTALACIÓN DE LA BASE DE DATOS
 * =================================================================
 * Se ejecuta solo una vez al activar el plugin para crear la tabla.
 */
register_activation_hook( __FILE__, 'anima_nexus_install_db' );

function anima_nexus_install_db() {
	global $wpdb;

	// El nombre final de la tabla (incluye el prefijo de WP, ej: wp_anima_connections)
	$table_name = $wpdb->prefix . ANIMA_NEXUS_DB_TABLE;
	
	// Obtener el juego de caracteres de la base de datos actual
	$charset_collate = $wpdb->get_charset_collate();

	// Sentencia SQL para crear la estructura de la tabla
	// id: Identificador único de la conexión.
	// requester_id: ID del usuario que solicita el enlace.
	// recipient_id: ID del usuario que recibe la solicitud.
	// status: Estado del enlace ('pending', 'accepted', 'blocked').
	// created_at: Fecha y hora de la solicitud.
	$sql = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		requester_id bigint(20) UNSIGNED NOT NULL,
		recipient_id bigint(20) UNSIGNED NOT NULL,
		status varchar(20) NOT NULL DEFAULT 'pending',
		created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
		PRIMARY KEY  (id),
		KEY requester_id (requester_id),
		KEY recipient_id (recipient_id),
		KEY status (status)
	) $charset_collate;";

	// Necesitamos este archivo del núcleo de WP para usar dbDelta (creación segura de tablas)
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	
	// dbDelta examina la estructura actual de la BD y aplica los cambios necesarios
	dbDelta( $sql );

	// Opcional: Guardar versión de la DB por si necesitamos actualizarla en el futuro
	add_option( 'anima_nexus_db_version', '1.0' );
}

/**
 * =================================================================
 * 2. EL CEREBRO DEL NEXUS: FUNCIONES CRUD (Lógica Central)
 * =================================================================
 * Estas funciones manejan la comunicación directa con la base de datos.
 * NO las llames directamente en las plantillas, usaremos manejadores AJAX más tarde.
 */

/**
 * HELPER: Obtener el nombre completo de la tabla con prefijo
 */
function anima_nexus_get_table_name() {
	global $wpdb;
	return $wpdb->prefix . ANIMA_NEXUS_DB_TABLE;
}

/**
 * A) Comprobar el estado del enlace entre dos usuarios.
 * Devuelve: 'none', 'pending_sent' (enviada por mí), 'pending_received' (recibida por mí), 'accepted', 'blocked'.
 */
function anima_nexus_get_connection_status( $user1_id, $user2_id ) {
	global $wpdb;
	$table_name = anima_nexus_get_table_name();

	// Seguridad básica: no puedes conectar contigo mismo
	if ( $user1_id == $user2_id ) return 'self';

	// Buscamos si existe una relación en cualquier dirección (A->B o B->A)
	$sql = $wpdb->prepare(
		"SELECT * FROM $table_name WHERE (requester_id = %d AND recipient_id = %d) OR (requester_id = %d AND recipient_id = %d) LIMIT 1",
		$user1_id, $user2_id, $user2_id, $user1_id
	);

	$connection = $wpdb->get_row( $sql );

	if ( ! $connection ) {
		return 'none'; // No hay relación
	}

	if ( $connection->status === 'accepted' ) {
		return 'accepted'; // Enlace establecido
	} elseif ( $connection->status === 'blocked' ) {
		return 'blocked'; // Enlace bloqueado
	} elseif ( $connection->status === 'pending' ) {
		// Si está pendiente, debemos saber si yo la envié o la recibí
		if ( $connection->requester_id == $user1_id ) {
			return 'pending_sent';
		} else {
			return 'pending_received';
		}
	}

	return 'none'; // Fallback
}


/**
 * B) Iniciar un Enlace Neuronal (Enviar solicitud)
 */
function anima_nexus_send_request( $requester_id, $recipient_id ) {
	global $wpdb;
	$table_name = anima_nexus_get_table_name();

	// 1. Verificar si ya existe una relación para evitar duplicados
	$current_status = anima_nexus_get_connection_status( $requester_id, $recipient_id );
	if ( $current_status !== 'none' ) {
		return new WP_Error( 'nexus_exists', 'Ya existe un enlace o solicitud entre estos agentes.' );
	}

	// 2. Insertar la nueva solicitud 'pending'
	$result = $wpdb->insert(
		$table_name,
		array(
			'requester_id' => $requester_id,
			'recipient_id' => $recipient_id,
			'status'       => 'pending',
			'created_at'   => current_time( 'mysql' ) // Fecha actual
		),
		array( '%d', '%d', '%s', '%s' ) // Formatos de los datos (entero, entero, string, string)
	);

	if ( $result === false ) {
		return new WP_Error( 'nexus_db_error', 'Error en la matriz al intentar establecer el enlace.' );
	}

	return true; // Éxito
}


/**
 * C) Actualizar un Enlace (Aceptar o Bloquear)
 * Esta función busca la solicitud específica donde alguien me pidió a mí.
 */
function anima_nexus_update_request_status( $recipient_id_me, $requester_id_them, $new_status ) {
	global $wpdb;
	$table_name = anima_nexus_get_table_name();

	// Solo permitimos estados válidos
	if ( ! in_array( $new_status, array( 'accepted', 'blocked' ) ) ) {
		return false;
	}

	// Actualizamos la fila donde ELLO me pidieron a MÍ y estaba pendiente
	$result = $wpdb->update(
		$table_name,
		array( 'status' => $new_status ), // Qué cambiamos
		array( 
			'requester_id' => $requester_id_them,
			'recipient_id' => $recipient_id_me,
			'status'       => 'pending' // Seguridad: solo actualizar si estaba pendiente
		), 
		array( '%s' ), // Formato del nuevo valor
		array( '%d', '%d', '%s' ) // Formato de las condiciones WHERE
	);

	return ( $result !== false && $result > 0 ); // Devuelve true si se actualizó al menos una fila
}


/**
 * D) Eliminar un Enlace (Cancelar solicitud o eliminar amigo)
 */
function anima_nexus_delete_connection( $user1_id, $user2_id ) {
    global $wpdb;
    $table_name = anima_nexus_get_table_name();

    // Borra la relación bidireccionalmente
    $sql = $wpdb->prepare(
        "DELETE FROM $table_name WHERE (requester_id = %d AND recipient_id = %d) OR (requester_id = %d AND recipient_id = %d)",
        $user1_id, $user2_id, $user2_id, $user1_id
    );

    $result = $wpdb->query( $sql );
    return ( $result !== false );
}


/**
 * E) HELPER: Obtener solicitudes pendientes entrantes (para notificaciones)
 * Devuelve un array con los IDs de los usuarios que quieren conectar conmigo.
 */
function anima_nexus_get_incoming_requests_ids( $my_user_id ) {
	global $wpdb;
	$table_name = anima_nexus_get_table_name();

	// Selecciona quién (requester_id) me ha pedido (recipient_id = yo) y está 'pending'
	$sql = $wpdb->prepare(
		"SELECT requester_id FROM $table_name WHERE recipient_id = %d AND status = 'pending'",
		$my_user_id
	);

	// get_col devuelve un array simple de resultados [12, 45, 88]
	return $wpdb->get_col( $sql );
}

/**
 * F) HELPER: Obtener lista de IDs de mis conexiones aceptadas (Amigos)
 */
function anima_nexus_get_my_connections_ids( $my_user_id ) {
    global $wpdb;
    $table_name = anima_nexus_get_table_name();

    // Esta query es un poco más compleja porque puede que yo enviara la solicitud O que la recibiera.
    // Usamos UNION para combinar ambos casos donde el estado sea 'accepted'.
    $sql = $wpdb->prepare(
        "(SELECT recipient_id AS friend_id FROM $table_name WHERE requester_id = %d AND status = 'accepted')
        UNION
        (SELECT requester_id AS friend_id FROM $table_name WHERE recipient_id = %d AND status = 'accepted')",
        $my_user_id, $my_user_id
    );

    return $wpdb->get_col( $sql );
}

/**
 * =================================================================
 * 3. EL PUENTE CIBERNÉTICO: MANEJADORES AJAX (Seguridad y Comunicación)
 * =================================================================
 * Estos ganchos reciben las señales del frontend (JavaScript), verifican la seguridad
 * y ejecutan las funciones lógicas de arriba.
 */

/**
 * MANEJADOR A) Recibir solicitud de enviar enlace
 */
// El gancho 'wp_ajax_' indica que esta función responde a llamadas AJAX de usuarios logueados.
add_action( 'wp_ajax_anima_nexus_send_request', 'anima_nexus_ajax_send_request_handler' );

function anima_nexus_ajax_send_request_handler() {
	// 1. Seguridad: Verificar Nonce (Token de seguridad)
	// Esto asegura que la petición viene de nuestra web y no de un ataque externo.
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'anima_nexus_action_nonce' ) ) {
		wp_send_json_error( array( 'message' => 'Fallo de seguridad en el protocolo.' ) );
	}

	// 2. Seguridad: Verificar que el usuario está logueado
	if ( ! is_user_logged_in() ) {
		wp_send_json_error( array( 'message' => 'Acceso denegado: Agente no identificado.' ) );
	}

	// 3. Obtener datos
	$requester_id = get_current_user_id(); // Yo
	$recipient_id = isset( $_POST['recipient_id'] ) ? intval( $_POST['recipient_id'] ) : 0; // A quién

	if ( $recipient_id === 0 || $requester_id === $recipient_id ) {
		wp_send_json_error( array( 'message' => 'Destinatario inválido.' ) );
	}

	// 4. Llamar a la función lógica (Fase 2)
	$result = anima_nexus_send_request( $requester_id, $recipient_id );

	if ( is_wp_error( $result ) ) {
		// Si hubo un error lógico (ej. ya existía)
		wp_send_json_error( array( 'message' => $result->get_error_message() ) );
	} else {
		// Éxito: Devolvemos una respuesta JSON al navegador
		wp_send_json_success( array(
			'message'    => 'Enlace neuronal solicitado. Esperando sincronización.',
			'new_status' => 'pending_sent' // Para que JS actualice el botón
		) );
	}

	// Siempre terminar un manejador AJAX con wp_die() o wp_send_json_*
	wp_die();
}


/**
 * MANEJADOR B) Responder a una solicitud (Aceptar o Bloquear)
 */
add_action( 'wp_ajax_anima_nexus_respond_request', 'anima_nexus_ajax_respond_request_handler' );

function anima_nexus_ajax_respond_request_handler() {
	// 1. Seguridad: Verificar Nonce
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'anima_nexus_action_nonce' ) ) {
		wp_send_json_error( array( 'message' => 'Fallo de seguridad.' ) );
	}

	// 2. Seguridad: Verificar login
	if ( ! is_user_logged_in() ) {
		wp_send_json_error( array( 'message' => 'Acceso denegado.' ) );
	}

	// 3. Obtener datos
	$my_id = get_current_user_id(); // Yo soy el que responde (recipient)
	// El ID de quien me lo pidió
	$requester_id_them = isset( $_POST['requester_id'] ) ? intval( $_POST['requester_id'] ) : 0;
	// La acción que tomo ('accept' o 'block')
	$action = isset( $_POST['nexus_action'] ) ? sanitize_text_field( $_POST['nexus_action'] ) : '';

	if ( $requester_id_them === 0 || ! in_array( $action, array( 'accept', 'block' ) ) ) {
		wp_send_json_error( array( 'message' => 'Datos de respuesta inválidos.' ) );
	}

	// Mapear la acción del frontend al estado de la base de datos
	$new_status = ( $action === 'accept' ) ? 'accepted' : 'blocked';
	$message    = ( $action === 'accept' ) ? 'Enlace establecido. Sincronización completa.' : 'Señal bloqueada.';

	// 4. Llamar a la función lógica (Fase 2)
	$success = anima_nexus_update_request_status( $my_id, $requester_id_them, $new_status );

	if ( $success ) {
		wp_send_json_success( array( 
			'message' => $message,
			'action_taken' => $action 
		) );
	} else {
		wp_send_json_error( array( 'message' => 'No se pudo actualizar el estado del enlace.' ) );
	}

	wp_die();
}

/**
 * IMPORTANTE:
 * Para que esto funcione, necesitamos que WordPress pase ciertas variables a nuestro JavaScript 
 * (como la URL de admin-ajax.php y el nonce de seguridad).
 * Esto lo haremos en la siguiente fase cuando creemos el archivo JS.
 */

 /**
 * =================================================================
 * 4. CARGA DE ASSETS (JS/CSS) Y LOCALIZACIÓN
 * =================================================================
 * Encola los archivos JS y CSS del plugin y pasa variables necesarias de PHP a JS.
 */
add_action( 'wp_enqueue_scripts', 'anima_nexus_enqueue_scripts' );

function anima_nexus_enqueue_scripts() {
	// Solo cargar si el usuario está logueado (el sistema es para miembros)
	if ( ! is_user_logged_in() ) {
		return;
	}

	// 1. Registrar y encolar el CSS
	wp_enqueue_style(
		'anima-nexus-style', // Handle único
		plugins_url( 'anima-nexus.css', __FILE__ ), // URL del archivo
		array(), // Dependencias
		'1.0.0'  // Versión
	);

	// 2. Registrar el JS (pero no encolarlo todavía)
	wp_register_script(
		'anima-nexus-script',
		plugins_url( 'anima-nexus.js', __FILE__ ),
		array( 'jquery' ), // Depende de jQuery
		'1.0.0',
		true // Cargar en el footer
	);

	// 3. Localizar el script: Pasar datos de PHP a un objeto JS llamado 'anima_nexus_vars'
	// Esto es CRÍTICO para que AJAX funcione.
	wp_localize_script(
		'anima-nexus-script',
		'anima_nexus_vars',
		array(
			'ajax_url' => admin_url( 'admin-ajax.php' ), // La URL universal para AJAX en WP
			'nonce'    => wp_create_nonce( 'anima_nexus_action_nonce' ) // Token de seguridad generado
		)
	);

	// 4. Finalmente, encolar el JS ya localizado
	wp_enqueue_script( 'anima-nexus-script' );
}

// ================================================================
// NUEVO SHORTCODE: Mostrar Mis Conexiones Aceptadas
// Uso: [anima_my_connections_list]
// ================================================================
function anima_sn_my_connections_shortcode() {
    // 1. Seguridad: Solo para usuarios logueados
    if ( ! is_user_logged_in() ) {
        return '<p class="anima-alert">Debes iniciar sesión para ver tus conexiones.</p>';
    }

    global $wpdb;
    // Usamos la constante definida al principio del plugin para la tabla
    // Asegúrate de que tu plugin define ASN_TABLE_CONNECTIONS al principio.
    // Si no estás seguro, puedes usar la línea directa: $table_name = $wpdb->prefix . 'anima_connections';
    $table_name = defined('ASN_TABLE_CONNECTIONS') ? ASN_TABLE_CONNECTIONS : $wpdb->prefix . 'anima_connections';
    $current_uid = get_current_user_id();

    // 2. Consulta SQL: Buscar IDs de usuarios con status 'accepted' relacionados conmigo
    $query = $wpdb->prepare(
        "SELECT CASE 
            WHEN user_id_1 = %d THEN user_id_2 
            ELSE user_id_1 
         END AS friend_id
         FROM $table_name
         WHERE (user_id_1 = %d OR user_id_2 = %d) AND status = 'accepted'",
        $current_uid, $current_uid, $current_uid
    );

    // Obtenemos la lista de IDs
    $friend_ids = $wpdb->get_col( $query );

    // Iniciamos el buffer de salida (necesario para shortcodes)
    ob_start();

    if ( empty( $friend_ids ) ) {
        // ESTADO VACÍO
        ?>
        <div class="anima-empty-state" style="text-align:center; padding:60px 20px; border: 2px dashed rgba(255,255,255,0.1); border-radius: 16px; background: rgba(255,255,255,0.02);">
            <span style="font-size:4em; display:block; margin-bottom:20px; opacity:0.3; filter: grayscale(1);">🔗</span>
            <h3 style="color:white; font-size: 1.3em; margin-bottom: 10px;">Sin enlaces activos</h3>
            <p class="anima-muted" style="font-size: 1em;">Aún no tienes conexiones confirmadas en tu red neural.</p>
        </div>
        <?php
    } else {
        // ESTADO CON AMIGOS: Obtener objetos de usuario y mostrar grid
        $friends = get_users( array( 'include' => $friend_ids ) );
        ?>
        <div class="anima-connections-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px;">
            <?php foreach ( $friends as $friend ) : 
                $f_uid = $friend->ID;
                $f_name = $friend->display_name;
                $f_avatar = get_avatar_url( $f_uid, ['size' => 120] );
                $f_profile_url = get_author_posts_url( $f_uid );
                // Intentar obtener nivel (si la función del tema existe)
                $f_level = function_exists('anima_get_user_level_info') ? anima_get_user_level_info($f_uid)['level'] : 'N/A';
            ?>
            <div class="anima-connection-card" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05); border-radius: 16px; padding: 25px 20px; text-align: center; transition: all 0.3s ease; position:relative; display: flex; flex-direction: column; align-items: center;">
                
                <div style="position: relative; width: 90px; height: 90px; margin-bottom: 15px;">
                    <div style="width:100%; height:100%; border-radius: 50%; overflow: hidden; border: 3px solid #4fd69c; padding: 3px; box-shadow: 0 0 15px rgba(79, 214, 156, 0.3);">
                            <img src="<?php echo esc_url($f_avatar); ?>" alt="<?php echo esc_attr($f_name); ?>" style="width:100%; height:100%; object-fit:cover; border-radius:50%;">
                    </div>
                    <span style="position: absolute; bottom: 5px; right: 5px; width: 16px; height: 16px; background-color: #4fd69c; border: 3px solid #1a1a2e; border-radius: 50%; box-shadow: 0 0 8px #4fd69c;" title="Enlace Activo"></span>
                </div>

                <h3 style="margin: 0 0 5px; font-size: 1.2em; color: white;"><?php echo esc_html($f_name); ?></h3>
                <span style="background: rgba(111, 101, 255, 0.2); color: #9f95ff; font-size: 0.75em; padding: 4px 10px; border-radius: 20px; font-weight: 600; letter-spacing: 1px; margin-bottom: 20px; border: 1px solid rgba(111, 101, 255, 0.3);">
                    NIVEL <?php echo esc_html($f_level); ?>
                </span>

                <div style="width: 100%; margin-top: auto;">
                    <a href="<?php echo esc_url($f_profile_url); ?>" class="anima-btn anima-btn-small ghost" style="width:100%; display:block; padding: 10px 5px; font-size: 0.85em; border-color: rgba(255,255,255,0.2); color:white; text-decoration:none; border:1px solid rgba(255,255,255,0.2); border-radius:4px; transition:all 0.3s;">Ver Perfil Público</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <style>.anima-connection-card a:hover { background: rgba(255,255,255,0.1); border-color:white; }</style>
        <?php
    }

    // Devolvemos el contenido del buffer
    return ob_get_clean();
}
// Registramos el shortcode. ESTA LÍNEA ES LA CLAVE.
add_shortcode( 'anima_my_connections_list', 'anima_sn_my_connections_shortcode' );