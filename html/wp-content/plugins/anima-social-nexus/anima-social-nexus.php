<?php
/**
 * Plugin Name: Anima Social Nexus
 * Description: Sistema de conexiones neuronales y transmisiones sociales pro-activas para la comunidad Cyberpunk.
 * Version: 1.0.0 alpha
 * Author: Anima Avatar Agency
 * Text Domain: anima-nexus
 */

// Evitar acceso directo si no es WordPress
if (!defined('ABSPATH')) {
	exit;
}

// Definir constantes para rutas del plugin (útiles para más adelante)
define('ANIMA_NEXUS_PATH', plugin_dir_path(__FILE__));
define('ANIMA_NEXUS_URL', plugin_dir_url(__FILE__));
define('ANIMA_NEXUS_DB_TABLE', 'anima_connections'); // Nombre base de nuestra tabla

/**
 * =================================================================
 * 1. ACTIVACIÓN E INSTALACIÓN DE LA BASE DE DATOS
 * =================================================================
 * Se ejecuta solo una vez al activar el plugin para crear la tabla.
 */
register_activation_hook(__FILE__, 'anima_nexus_install_db');

function anima_nexus_install_db()
{
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
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

	// dbDelta examina la estructura actual de la BD y aplica los cambios necesarios
	dbDelta($sql);

	// Opcional: Guardar versión de la DB por si necesitamos actualizarla en el futuro
	add_option('anima_nexus_db_version', '1.0');
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
function anima_nexus_get_table_name()
{
	global $wpdb;
	return $wpdb->prefix . ANIMA_NEXUS_DB_TABLE;
}

/**
 * A) Comprobar el estado del enlace entre dos usuarios.
 * Devuelve: 'none', 'pending_sent' (enviada por mí), 'pending_received' (recibida por mí), 'accepted', 'blocked'.
 */
function anima_nexus_get_connection_status($user1_id, $user2_id)
{
	global $wpdb;
	$table_name = anima_nexus_get_table_name();

	// Seguridad básica: no puedes conectar contigo mismo
	if ($user1_id == $user2_id)
		return 'self';

	// Buscamos si existe una relación en cualquier dirección (A->B o B->A)
	$sql = $wpdb->prepare(
		"SELECT * FROM $table_name WHERE (requester_id = %d AND recipient_id = %d) OR (requester_id = %d AND recipient_id = %d) LIMIT 1",
		$user1_id,
		$user2_id,
		$user2_id,
		$user1_id
	);

	$connection = $wpdb->get_row($sql);

	if (!$connection) {
		return 'none'; // No hay relación
	}

	if ($connection->status === 'accepted') {
		return 'accepted'; // Enlace establecido
	} elseif ($connection->status === 'blocked') {
		return 'blocked'; // Enlace bloqueado
	} elseif ($connection->status === 'pending') {
		// Si está pendiente, debemos saber si yo la envié o la recibí
		if ($connection->requester_id == $user1_id) {
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
function anima_nexus_send_request($requester_id, $recipient_id)
{
	global $wpdb;
	$table_name = anima_nexus_get_table_name();

	// 1. Verificar si ya existe una relación para evitar duplicados
	$current_status = anima_nexus_get_connection_status($requester_id, $recipient_id);
	if ($current_status !== 'none') {
		return new WP_Error('nexus_exists', 'Ya existe un enlace o solicitud entre estos agentes.');
	}

	// 2. Insertar la nueva solicitud 'pending'
	$result = $wpdb->insert(
		$table_name,
		array(
			'requester_id' => $requester_id,
			'recipient_id' => $recipient_id,
			'status' => 'pending',
			'created_at' => current_time('mysql') // Fecha actual
		),
		array('%d', '%d', '%s', '%s') // Formatos de los datos (entero, entero, string, string)
	);

	if ($result === false) {
		return new WP_Error('nexus_db_error', 'Error en la matriz al intentar establecer el enlace.');
	}

	return true; // Éxito
}


/**
 * C) Actualizar un Enlace (Aceptar o Bloquear)
 * Esta función busca la solicitud específica donde alguien me pidió a mí.
 */
function anima_nexus_update_request_status($recipient_id_me, $requester_id_them, $new_status)
{
	global $wpdb;
	$table_name = anima_nexus_get_table_name();

	// Solo permitimos estados válidos
	if (!in_array($new_status, array('accepted', 'blocked'))) {
		return false;
	}

	// Actualizamos la fila donde ELLO me pidieron a MÍ y estaba pendiente
	$result = $wpdb->update(
		$table_name,
		array('status' => $new_status), // Qué cambiamos
		array(
			'requester_id' => $requester_id_them,
			'recipient_id' => $recipient_id_me,
			'status' => 'pending' // Seguridad: solo actualizar si estaba pendiente
		),
		array('%s'), // Formato del nuevo valor
		array('%d', '%d', '%s') // Formato de las condiciones WHERE
	);

	return ($result !== false && $result > 0); // Devuelve true si se actualizó al menos una fila
}


/**
 * D) Eliminar un Enlace (Cancelar solicitud o eliminar amigo)
 */
function anima_nexus_delete_connection($user1_id, $user2_id)
{
	global $wpdb;
	$table_name = anima_nexus_get_table_name();

	// Borra la relación bidireccionalmente
	$sql = $wpdb->prepare(
		"DELETE FROM $table_name WHERE (requester_id = %d AND recipient_id = %d) OR (requester_id = %d AND recipient_id = %d)",
		$user1_id,
		$user2_id,
		$user2_id,
		$user1_id
	);

	$result = $wpdb->query($sql);
	return ($result !== false);
}


/**
 * E) HELPER: Obtener solicitudes pendientes entrantes (para notificaciones)
 * Devuelve un array con los IDs de los usuarios que quieren conectar conmigo.
 */
function anima_nexus_get_incoming_requests_ids($my_user_id)
{
	global $wpdb;
	$table_name = anima_nexus_get_table_name();

	// Selecciona quién (requester_id) me ha pedido (recipient_id = yo) y está 'pending'
	$sql = $wpdb->prepare(
		"SELECT requester_id FROM $table_name WHERE recipient_id = %d AND status = 'pending'",
		$my_user_id
	);

	// get_col devuelve un array simple de resultados [12, 45, 88]
	return $wpdb->get_col($sql);
}

/**
 * F) HELPER: Obtener lista de IDs de mis conexiones aceptadas (Amigos)
 */
function anima_nexus_get_my_connections_ids($my_user_id)
{
	global $wpdb;
	$table_name = anima_nexus_get_table_name();

	// Esta query es un poco más compleja porque puede que yo enviara la solicitud O que la recibiera.
	// Usamos UNION para combinar ambos casos donde el estado sea 'accepted'.
	$sql = $wpdb->prepare(
		"(SELECT recipient_id AS friend_id FROM $table_name WHERE requester_id = %d AND status = 'accepted')
        UNION
        (SELECT requester_id AS friend_id FROM $table_name WHERE recipient_id = %d AND status = 'accepted')",
		$my_user_id,
		$my_user_id
	);

	return $wpdb->get_col($sql);
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
add_action('wp_ajax_anima_nexus_send_request', 'anima_nexus_ajax_send_request_handler');

function anima_nexus_ajax_send_request_handler()
{
	// 1. Seguridad: Verificar Nonce (Token de seguridad)
	// Esto asegura que la petición viene de nuestra web y no de un ataque externo.
	if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'anima_nexus_action_nonce')) {
		wp_send_json_error(array('message' => 'Fallo de seguridad en el protocolo.'));
	}

	// 2. Seguridad: Verificar que el usuario está logueado
	if (!is_user_logged_in()) {
		wp_send_json_error(array('message' => 'Acceso denegado: Agente no identificado.'));
	}

	// 3. Obtener datos
	$requester_id = get_current_user_id(); // Yo
	$recipient_id = isset($_POST['recipient_id']) ? intval($_POST['recipient_id']) : 0; // A quién

	if ($recipient_id === 0 || $requester_id === $recipient_id) {
		wp_send_json_error(array('message' => 'Destinatario inválido.'));
	}

	// 4. Llamar a la función lógica (Fase 2)
	$result = anima_nexus_send_request($requester_id, $recipient_id);

	if (is_wp_error($result)) {
		// Si hubo un error lógico (ej. ya existía)
		wp_send_json_error(array('message' => $result->get_error_message()));
	} else {
		// Éxito: Devolvemos una respuesta JSON al navegador
		wp_send_json_success(array(
			'message' => 'Enlace neuronal solicitado. Esperando sincronización.',
			'new_status' => 'pending_sent' // Para que JS actualice el botón
		));
	}
	wp_die();
}

/**
 * MANEJADOR B) Responder solicitud (Aceptar / Bloquear)
 */
add_action('wp_ajax_anima_nexus_respond_request', 'anima_nexus_respond_request_handler');

function anima_nexus_respond_request_handler()
{
	// 1. Seguridad: Verificar Nonce
	if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'anima_nexus_action_nonce')) {
		wp_send_json_error(array('message' => 'Fallo de seguridad.'));
	}

	// 2. Seguridad: Verificar login
	if (!is_user_logged_in()) {
		wp_send_json_error(array('message' => 'Acceso denegado.'));
	}

	// 3. Obtener datos
	$my_id = get_current_user_id(); // Yo soy el que responde (recipient)
	// El ID de quien me lo pidió
	$requester_id_them = isset($_POST['requester_id']) ? intval($_POST['requester_id']) : 0;
	// La acción que tomo ('accept' o 'block')
	$action = isset($_POST['nexus_action']) ? sanitize_text_field($_POST['nexus_action']) : '';

	if ($requester_id_them === 0 || !in_array($action, array('accept', 'block'))) {
		wp_send_json_error(array('message' => 'Datos de respuesta inválidos.'));
	}

	// Mapear la acción del frontend al estado de la base de datos
	$new_status = ($action === 'accept') ? 'accepted' : 'blocked';
	$message = ($action === 'accept') ? 'Enlace establecido. Sincronización completa.' : 'Señal bloqueada.';

	// 4. Llamar a la función lógica (Fase 2)
	$success = anima_nexus_update_request_status($my_id, $requester_id_them, $new_status);

	if ($success) {
		wp_send_json_success(array(
			'message' => $message,
			'action_taken' => $action
		));
	} else {
		wp_send_json_error(array('message' => 'No se pudo actualizar el estado del enlace.'));
	}

	wp_die();
}

/**
 * MANEJADOR C) Filtrar Usuarios (Nexus)
 */
add_action('wp_ajax_anima_nexus_filter_users', 'anima_nexus_ajax_filter_users');
add_action('wp_ajax_nopriv_anima_nexus_filter_users', 'anima_nexus_ajax_filter_users');

function anima_nexus_ajax_filter_users()
{
	// 1. Obtener parámetros
	$filter_status = isset($_POST['filter']) ? sanitize_text_field($_POST['filter']) : 'all';
	$search_term = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
	$rank_filter = isset($_POST['rank']) ? sanitize_text_field($_POST['rank']) : '';
	$paged = isset($_POST['paged']) ? absint($_POST['paged']) : 1;
	$current_user_id = get_current_user_id();

	// 2. Configurar Query
	$nexus_per_page = 12;
	$nexus_offset = ($paged - 1) * $nexus_per_page;

	$query_args = array(
		'number' => $nexus_per_page,
		'offset' => $nexus_offset,
		'orderby' => 'registered',
		'order' => 'DESC',
		'fields' => 'all_with_meta',
	);

	if (!empty($search_term)) {
		$query_args['search'] = '*' . $search_term . '*';
		$query_args['search_columns'] = array('display_name', 'user_login', 'nicename');
	}

	// Filtro por Rango
	if (!empty($rank_filter) && $rank_filter !== 'all') {
		$query_args['meta_key'] = 'anima_karma_rank';
		$query_args['meta_value'] = $rank_filter;
	}

	$filtering_by_friends = false;
	$friend_ids = array();

	if ($filter_status === 'friends' && is_user_logged_in()) {
		$filtering_by_friends = true;
		$friend_ids = anima_nexus_get_my_connections_ids($current_user_id);
		$query_args['include'] = !empty($friend_ids) ? $friend_ids : array(0);
	} else {
		if (is_user_logged_in()) {
			$query_args['exclude'] = array($current_user_id);
		}
	}

	// 3. Ejecutar Query
	$user_query = new WP_User_Query($query_args);
	$avatars = $user_query->get_results();

	// 4. Generar HTML
	ob_start();
	if (!empty($avatars)) {
		foreach ($avatars as $agent) {
			$agent_id = $agent->ID;
			$status = (is_user_logged_in()) ? anima_nexus_get_connection_status($current_user_id, $agent_id) : 'none';
			?>
			<div class="agent-card cyberpunk-box">
				<div class="agent-header">
					<?php echo get_avatar($agent_id, 80); ?>
					<h3 class="agent-title"><?php echo esc_html($agent->display_name); ?></h3>
					<?php
					$rank = get_user_meta($agent_id, 'anima_karma_rank', true);
					if ($rank)
						echo '<span class="agent-rank" style="font-size:0.8em; color:#888; display:block; margin-bottom:10px;">' . esc_html($rank) . '</span>';
					?>
				</div>
				<div class="agent-actions">
					<?php if (is_user_logged_in()): ?>
						<?php switch ($status):
							case 'none': ?>
								<button class="anima-nexus-btn anima-nexus-connect-btn full-width"
									data-recipient-id="<?php echo esc_attr($agent_id); ?>"><span class="dashicons dashicons-networking"></span>
									Establecer Enlace</button>
								<?php break;
							case 'pending_sent': ?>
								<button class="anima-nexus-btn anima-nexus-pending-btn disabled full-width"><span
										class="dashicons dashicons-hourglass"></span> Señal en Espera</button>
								<?php break;
							case 'pending_received': ?>
								<span class="nexus-status-label pending-label"><span class="dashicons dashicons-arrow-down-alt"></span>
									Solicitud recibida</span>
								<?php break;
							case 'accepted': ?>
								<span class="nexus-status-label connected-label"><span class="dashicons dashicons-yes-alt"></span> Enlace
									Neuronal Activo</span>
								<?php break;
						endswitch; ?>
					<?php else: ?>
						<a href="<?php echo wp_login_url(); ?>" class="anima-nexus-btn full-width">Iniciar Sesión</a>
					<?php endif; ?>
				</div>
			</div>
			<?php
		}
	} else {
		echo '<div class="nexus-no-results cyberpunk-box">';
		if ($filtering_by_friends && empty($friend_ids)) {
			echo '<p>Aún no tienes Enlaces Neuronales activos.</p>';
		} elseif (!empty($search_term)) {
			echo '<p>No se encontraron avatares.</p>';
		} else {
			echo '<p>No se detectan datos.</p>';
		}
		echo '</div>';
	}
	$html = ob_get_clean();

	// 5. Paginación
	$total_query_args = $query_args;
	unset($total_query_args['number']);
	unset($total_query_args['offset']);
	$total_query_args['fields'] = 'ID';
	$total_query = new WP_User_Query($total_query_args);
	$total_users = $total_query->get_total();
	$total_pages = ceil($total_users / $nexus_per_page);

	$pagination = paginate_links(array(
		'base' => '%_%',
		'format' => '?paged=%#%',
		'current' => $paged,
		'total' => $total_pages,
		'type' => 'list',
		'prev_text' => '<span class="dashicons dashicons-arrow-left-alt2"></span> ANTERIOR',
		'next_text' => 'SIGUIENTE <span class="dashicons dashicons-arrow-right-alt2"></span>',
	));

	wp_send_json_success(array(
		'html' => $html,
		'pagination' => $pagination
	));
	wp_die();
}

/**
 * Encola los archivos JS y CSS del plugin y pasa variables necesarias de PHP a JS.
 */
add_action('wp_enqueue_scripts', 'anima_nexus_enqueue_scripts');

function anima_nexus_enqueue_scripts()
{
	// Solo cargar si el usuario está logueado (el sistema es para miembros)
	if (!is_user_logged_in()) {
		// return; // Permitimos cargar scripts para Tablón público si es necesario, pero por ahora mantenemos la lógica original
		// UPDATE: Tablón necesita scripts también.
	}

	// 1. Registrar y encolar el CSS
	wp_enqueue_style(
		'anima-nexus-style', // Handle único
		plugins_url('anima-nexus.css', __FILE__), // URL del archivo
		array(), // Dependencias
		'1.0.0'  // Versión
	);

	// 2. Registrar el JS (pero no encolarlo todavía)
	wp_register_script(
		'anima-nexus-script',
		plugins_url('anima-nexus.js', __FILE__),
		array('jquery'), // Depende de jQuery
		'1.0.0',
		true // Cargar en el footer
	);

	// 3. Localizar el script: Pasar datos de PHP a un objeto JS llamado 'anima_nexus_vars'
	// Esto es CRÍTICO para que AJAX funcione.
	wp_localize_script(
		'anima-nexus-script',
		'anima_nexus_vars',
		array(
			'ajax_url' => admin_url('admin-ajax.php'), // La URL universal para AJAX en WP
			'nonce' => wp_create_nonce('anima_nexus_action_nonce') // Token de seguridad generado
		)
	);

	// 4. Finalmente, encolar el JS ya localizado
	wp_enqueue_script('anima-nexus-script');
}

// ================================================================
// NUEVO SHORTCODE: Mostrar Mis Conexiones Aceptadas
// Uso: [anima_my_connections_list]
// ================================================================
function anima_sn_my_connections_shortcode()
{
	// 1. Seguridad: Solo para usuarios logueados
	if (!is_user_logged_in()) {
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
		$current_uid,
		$current_uid,
		$current_uid
	);

	// Obtenemos la lista de IDs
	$friend_ids = $wpdb->get_col($query);

	// Iniciamos el buffer de salida (necesario para shortcodes)
	ob_start();

	if (empty($friend_ids)) {
		// ESTADO VACÍO
		?>
		<div class="anima-empty-state"
			style="text-align:center; padding:60px 20px; border: 2px dashed rgba(255,255,255,0.1); border-radius: 16px; background: rgba(255,255,255,0.02);">
			<span style="font-size:4em; display:block; margin-bottom:20px; opacity:0.3; filter: grayscale(1);">🔗</span>
			<h3 style="color:white; font-size: 1.3em; margin-bottom: 10px;">Sin enlaces activos</h3>
			<p class="anima-muted" style="font-size: 1em;">Aún no tienes conexiones confirmadas en tu red neural.</p>
		</div>
		<?php
	} else {
		// ESTADO CON AMIGOS: Obtener objetos de usuario y mostrar grid
		$friends = get_users(array('include' => $friend_ids));
		?>
		<div class="anima-connections-grid"
			style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px;">
			<?php foreach ($friends as $friend):
				$f_uid = $friend->ID;
				$f_name = $friend->display_name;
				$f_avatar = get_avatar_url($f_uid, ['size' => 120]);
				$f_profile_url = get_author_posts_url($f_uid);
				// Intentar obtener nivel (si la función del tema existe)
				$f_level = function_exists('anima_get_user_level_info') ? anima_get_user_level_info($f_uid)['level'] : 'N/A';
				?>
				<div class="anima-connection-card"
					style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05); border-radius: 16px; padding: 25px 20px; text-align: center; transition: all 0.3s ease; position:relative; display: flex; flex-direction: column; align-items: center;">

					<div style="position: relative; width: 90px; height: 90px; margin-bottom: 15px;">
						<div
							style="width:100%; height:100%; border-radius: 50%; overflow: hidden; border: 3px solid #4fd69c; padding: 3px; box-shadow: 0 0 15px rgba(79, 214, 156, 0.3);">
							<img src="<?php echo esc_url($f_avatar); ?>" alt="<?php echo esc_attr($f_name); ?>"
								style="width:100%; height:100%; object-fit:cover; border-radius:50%;">
						</div>
						<span
							style="position: absolute; bottom: 5px; right: 5px; width: 16px; height: 16px; background-color: #4fd69c; border: 3px solid #1a1a2e; border-radius: 50%; box-shadow: 0 0 8px #4fd69c;"
							title="Enlace Activo"></span>
					</div>

					<h3 style="margin: 0 0 5px; font-size: 1.2em; color: white;"><?php echo esc_html($f_name); ?></h3>
					<span
						style="background: rgba(111, 101, 255, 0.2); color: #9f95ff; font-size: 0.75em; padding: 4px 10px; border-radius: 20px; font-weight: 600; letter-spacing: 1px; margin-bottom: 20px; border: 1px solid rgba(111, 101, 255, 0.3);">
						NIVEL <?php echo esc_html($f_level); ?>
					</span>

					<div style="width: 100%; margin-top: auto;">
						<a href="<?php echo esc_url($f_profile_url); ?>" class="anima-btn anima-btn-small ghost"
							style="width:100%; display:block; padding: 10px 5px; font-size: 0.85em; border-color: rgba(255,255,255,0.2); color:white; text-decoration:none; border:1px solid rgba(255,255,255,0.2); border-radius:4px; transition:all 0.3s;">Ver
							Perfil Público</a>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
		<style>
			.anima-connection-card a:hover {
				background: rgba(255, 255, 255, 0.1);
				border-color: white;
			}
		</style>
		<?php
	}

	// Devolvemos el contenido del buffer
	return ob_get_clean();
}
// Registramos el shortcode. ESTA LÍNEA ES LA CLAVE.
add_shortcode('anima_my_connections_list', 'anima_sn_my_connections_shortcode');

/**
 * =================================================================
 * MANEJADOR AJAX PARA EL TABLÓN (FILTROS Y PAGINACIÓN)
 * =================================================================
 */
add_action('wp_ajax_anima_nexus_tablon_filter', 'anima_nexus_ajax_tablon_filter');
add_action('wp_ajax_nopriv_anima_nexus_tablon_filter', 'anima_nexus_ajax_tablon_filter');

function anima_nexus_ajax_tablon_filter()
{
	// 1. Verificar Nonce (opcional si es público, pero recomendado)
	// if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'anima_nexus_action_nonce' ) ) {
	//     wp_send_json_error( array( 'message' => 'Security check failed' ) );
	// }

	$filter = isset($_POST['filter']) ? sanitize_text_field($_POST['filter']) : 'all';
	$paged = isset($_POST['paged']) ? absint($_POST['paged']) : 1;

	$args = [
		'post_type' => 'nexus_post',
		'posts_per_page' => 12,
		'paged' => $paged,
		'orderby' => 'date',
		'order' => 'DESC',
		'post_status' => 'publish'
	];

	if ($filter !== 'all') {
		$args['meta_key'] = '_anima_nexus_message_type';
		$args['meta_value'] = $filter;
	}

	$feed_query = new WP_Query($args);

	if ($feed_query->have_posts()) {
		// Lógica Masonry PHP
		$columns = [[], [], []];
		$posts = $feed_query->get_posts();
		foreach ($posts as $index => $post_obj) {
			$columns[$index % 3][] = $post_obj;
		}

		ob_start();
		?>
		<div class="nexus-masonry-fixed">
			<?php foreach ($columns as $col_posts): ?>
				<div class="masonry-col">
					<?php foreach ($col_posts as $post):
						setup_postdata($post);
						$pid = $post->ID;
						$author_id = $post->post_author;
						$type = get_post_meta($pid, '_anima_nexus_message_type', true);
						$likes = function_exists('anima_get_likes_count') ? anima_get_likes_count($pid) : 0;
						$liked = function_exists('anima_user_has_liked') ? anima_user_has_liked($pid, get_current_user_id()) : false;
						$comments_num = get_comments_number($pid);
						?>
						<article class="holo-card type-<?php echo esc_attr($type); ?>" id="post-<?php echo $pid; ?>">
							<div class="card-header">
								<div class="author-box">
									<?php echo get_avatar($author_id, 45); ?>
									<div class="author-meta">
										<span class="author-nick"><?php echo get_the_author_meta('display_name', $author_id); ?></span>
										<span
											class="post-time"><?php echo human_time_diff(get_the_time('U', $pid), current_time('timestamp')) . ' atrás'; ?></span>
									</div>
								</div>
								<span class="post-badge badge-<?php echo esc_attr($type); ?>"><?php echo strtoupper($type); ?></span>
							</div>

							<div class="card-content">
								<a href="<?php echo get_permalink($pid); ?>" class="content-link">
									<h3 class="post-title"><?php echo get_the_title($pid); ?></h3>
								</a>
								<div class="post-excerpt"><?php echo apply_filters('the_content', $post->post_content); ?></div>
								<?php if ($type === 'evento'):
									$edate = get_post_meta($pid, '_anima_nexus_event_date', true);
									?>
									<div class="event-data-display">📅 <?php echo date_i18n('d M, H:i', strtotime($edate)); ?></div>
								<?php endif; ?>
							</div>

							<div class="card-footer">
								<div class="interactions">
									<button class="interact-btn like-btn <?php echo $liked ? 'liked' : ''; ?>"
										data-id="<?php echo $pid; ?>">
										<span class="icon">♥</span> <span class="count"><?php echo $likes; ?></span>
									</button>
									<button class="interact-btn comment-toggle-btn" data-id="<?php echo $pid; ?>">
										<span class="icon">💬</span> <?php echo $comments_num; ?> Respuestas
									</button>
								</div>
							</div>

							<div class="comments-drawer" id="comments-<?php echo $pid; ?>" style="display:none;">
								<div class="comments-list-container"></div>
								<div class="comment-input-wrapper">
									<textarea class="mini-textarea" placeholder="Respuesta..."></textarea>
									<button class="mini-send-btn" data-id="<?php echo $pid; ?>">></button>
								</div>
							</div>
						</article>
					<?php endforeach; ?>
				</div>
			<?php endforeach;
			wp_reset_postdata(); ?>
		</div>
		<?php
		$html = ob_get_clean();

		// Paginación
		$pagination = paginate_links([
			'total' => $feed_query->max_num_pages,
			'current' => $paged,
			'format' => '?paged=%#%',
			'base' => '%_%' // Importante para AJAX, aunque lo manejaremos con JS
		]);

		wp_send_json_success([
			'html' => $html,
			'pagination' => $pagination
		]);

	} else {
		wp_send_json_success([
			'html' => '<div class="cyber-panel empty-state"><h2>SIN SEÑAL</h2></div>',
			'pagination' => ''
		]);
	}

	wp_die();
}

/**
 * =================================================================
 * 4. SISTEMA DE INTERACCIONES (LIKES Y COMENTARIOS)
 * =================================================================
 */

/**
 * HELPER: Obtener conteo de likes
 */
function anima_get_likes_count($post_id)
{
	return (int) get_post_meta($post_id, '_anima_nexus_likes_count', true);
}

/**
 * HELPER: Verificar si usuario dio like
 */
function anima_user_has_liked($post_id, $user_id)
{
	$likes = get_post_meta($post_id, '_anima_nexus_liked_by', true);
	if (!is_array($likes))
		$likes = array();
	return in_array($user_id, $likes);
}

/**
 * AJAX: Manejar Like
 */
add_action('wp_ajax_anima_nexus_like', 'anima_nexus_ajax_like');
function anima_nexus_ajax_like()
{
	if (!is_user_logged_in())
		wp_send_json_error();

	$post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
	$user_id = get_current_user_id();

	if (!$post_id)
		wp_send_json_error();

	$likes_list = get_post_meta($post_id, '_anima_nexus_liked_by', true);
	if (!is_array($likes_list))
		$likes_list = array();

	$action = 'added';
	if (in_array($user_id, $likes_list)) {
		// Quitar like
		$key = array_search($user_id, $likes_list);
		unset($likes_list[$key]);
		$action = 'removed';
	} else {
		// Agregar like
		$likes_list[] = $user_id;
	}

	// Guardar array actualizado
	update_post_meta($post_id, '_anima_nexus_liked_by', $likes_list);

	// Actualizar conteo
	$count = count($likes_list);
	update_post_meta($post_id, '_anima_nexus_likes_count', $count);

	wp_send_json_success(array('count' => $count, 'action' => $action));
}

/**
 * AJAX: Publicar Comentario
 */
add_action('wp_ajax_anima_post_comment', 'anima_nexus_post_comment');
function anima_nexus_post_comment()
{
	if (!is_user_logged_in())
		wp_send_json_error();

	$post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
	$content = isset($_POST['content']) ? sanitize_textarea_field($_POST['content']) : '';
	$user = wp_get_current_user();

	if (!$post_id || !$content)
		wp_send_json_error();

	$comment_data = array(
		'comment_post_ID' => $post_id,
		'comment_content' => $content,
		'comment_author' => $user->display_name,
		'comment_author_email' => $user->user_email,
		'user_id' => $user->ID,
		'comment_approved' => 1,
	);

	$comment_id = wp_insert_comment($comment_data);

	if ($comment_id) {
		wp_send_json_success();
	} else {
		wp_send_json_error();
	}
}

/**
 * AJAX: Cargar Comentarios
 */
add_action('wp_ajax_anima_load_comments', 'anima_nexus_load_comments');
add_action('wp_ajax_nopriv_anima_load_comments', 'anima_nexus_load_comments');

function anima_nexus_load_comments()
{
	$post_id = isset($_GET['post_id']) ? intval($_GET['post_id']) : 0;
	if (!$post_id)
		wp_send_json_error();

	$comments = get_comments(array(
		'post_id' => $post_id,
		'status' => 'approve',
		'order' => 'ASC'
	));

	ob_start();
	if ($comments) {
		echo '<ul class="nexus-comments-list">';
		foreach ($comments as $comment) {
			?>
			<li class="nexus-comment">
				<div class="comment-avatar">
					<?php echo get_avatar($comment, 32); ?>
				</div>
				<div class="comment-body">
					<strong class="comment-author"><?php echo get_comment_author($comment); ?></strong>
					<div class="comment-text"><?php comment_text($comment); ?></div>
				</div>
			</li>
			<?php
		}
		echo '</ul>';
	} else {
		echo '<p class="no-comments">Sé el primero en responder.</p>';
	}
	$html = ob_get_clean();

	wp_send_json_success(array('html' => $html));
}