<?php
/**
 * Anima Avatar Agency functions and definitions
 *
 * @package Anima_Avatar_Agency
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

// =====================================================================
// 1. CONFIGURACIÓN BÁSICA DEL TEMA
// =====================================================================

function animaavatar_setup()
{
	load_theme_textdomain('animaavatar', get_template_directory() . '/languages');
	add_theme_support('automatic-feed-links');
	add_theme_support('title-tag');
	add_theme_support('post-thumbnails');
	register_nav_menus(
		array(
			'menu-principal' => esc_html__('Menú Principal', 'animaavatar'),
		)
	);
	add_theme_support(
		'html5',
		array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
		)
	);
	add_theme_support(
		'custom-logo',
		array(
			'height' => 250,
			'width' => 250,
			'flex-width' => true,
			'flex-height' => true,
		)
	);
}
add_action('after_setup_theme', 'animaavatar_setup');

function animaavatar_widgets_init()
{
	register_sidebar(
		array(
			'name' => esc_html__('Barra Lateral', 'animaavatar'),
			'id' => 'sidebar-1',
			'description' => esc_html__('Añade widgets aquí.', 'animaavatar'),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget' => '</section>',
			'before_title' => '<h2 class="widget-title">',
			'after_title' => '</h2>',
		)
	);
}
add_action('widgets_init', 'animaavatar_widgets_init');

// =====================================================================
// 2. SCRIPTS Y ESTILOS
// =====================================================================

function animaavatar_scripts()
{
	wp_enqueue_style('animaavatar-style', get_stylesheet_uri());

	// Enqueue the Hero Script as a module
	wp_enqueue_script('anima-three-hero', get_template_directory_uri() . '/assets/js/three-hero.js', array(), '1.0.0', true);

	// Localize script for dynamic settings
	wp_localize_script('anima-three-hero', 'animaSettings', array(
		'modelUrl' => get_option('anima_home_model_url', 'https://models.readyplayer.me/64d61e9e17d0505b63025255.glb')
	));
}
add_action('wp_enqueue_scripts', 'animaavatar_scripts');

// Add type="module" to specific scripts
function anima_add_type_attribute($tag, $handle, $src)
{
	if ('anima-three-hero' === $handle) {
		$tag = '<script type="module" src="' . esc_url($src) . '"></script>';
	}
	return $tag;
}
add_filter('script_loader_tag', 'anima_add_type_attribute', 10, 3);

function anima_add_importmap()
{
	echo '<script type="importmap">
    {
        "imports": {
            "three": "https://unpkg.com/three@0.160.0/build/three.module.js",
            "three/addons/": "https://unpkg.com/three@0.160.0/examples/jsm/"
        }
    }
    </script>';
}
add_action('wp_head', 'anima_add_importmap');

// =====================================================================
// 4. FUNCIONES PARA NEXUS (CUSTOM POST TYPE Y METABOXES)
// =====================================================================

// Registrar el Custom Post Type 'nexus_post'
add_action('init', 'anima_nexus_register_feed_post_type');
if (!function_exists('anima_nexus_register_feed_post_type')) {
	function anima_nexus_register_feed_post_type()
	{
		$labels = array(
			'name' => _x('Posts del Nexus', 'Post Type General Name', 'animaavatar'),
			'singular_name' => _x('Post del Nexus', 'Post Type Singular Name', 'animaavatar'),
			'menu_name' => __('Nexus Feed', 'animaavatar'),
			'all_items' => __('Todos los Posts', 'animaavatar'),
			'add_new_item' => __('Añadir nuevo Post', 'animaavatar'),
			'add_new' => __('Añadir nuevo', 'animaavatar'),
			'edit_item' => __('Editar Post', 'animaavatar'),
			'update_item' => __('Actualizar Post', 'animaavatar'),
			'search_items' => __('Buscar Posts', 'animaavatar'),
			'not_found' => __('No encontrado', 'animaavatar'),
			'not_found_in_trash' => __('No encontrado en la papelera', 'animaavatar'),
		);
		$args = array(
			'label' => __('Post del Nexus', 'animaavatar'),
			'description' => __('Publicaciones para el tablón de comunidad de Anima', 'animaavatar'),
			'labels' => $labels,
			'supports' => array('title', 'editor', 'author', 'thumbnail', 'excerpt', 'custom-fields'),
			'hierarchical' => false,
			'public' => true,
			'show_ui' => true,
			'show_in_menu' => true,
			'menu_position' => 5,
			'menu_icon' => 'dashicons-groups',
			'show_in_admin_bar' => true,
			'show_in_nav_menus' => true,
			'can_export' => true,
			'has_archive' => false,
			'exclude_from_search' => false,
			'publicly_queryable' => true,
			'capability_type' => 'post',
			'show_in_rest' => true,
		);
		register_post_type('nexus_post', $args);
	}
}

// Añadir Meta Boxes para el tipo de mensaje y datos de evento
add_action('add_meta_boxes', 'anima_nexus_add_meta_boxes_cpt');
if (!function_exists('anima_nexus_add_meta_boxes_cpt')) {
	function anima_nexus_add_meta_boxes_cpt()
	{
		add_meta_box(
			'anima_nexus_post_type_meta_box',
			__('Tipo de Publicación y Detalles', 'animaavatar'),
			'anima_nexus_post_type_callback',
			'nexus_post',
			'normal',
			'high'
		);
	}
}

// Callback para mostrar el contenido del metabox
if (!function_exists('anima_nexus_post_type_callback')) {
	function anima_nexus_post_type_callback($post)
	{
		wp_nonce_field('anima_nexus_save_meta', 'anima_nexus_meta_nonce');

		$message_type = get_post_meta($post->ID, '_anima_nexus_message_type', true);
		$event_date = get_post_meta($post->ID, '_anima_nexus_event_date', true);
		$event_location = get_post_meta($post->ID, '_anima_nexus_event_location', true);
		$event_link = get_post_meta($post->ID, '_anima_nexus_event_link', true);
		?>
		<p>
			<label for="anima_nexus_message_type"><?php _e('Tipo de Mensaje:', 'animaavatar'); ?></label>
			<select name="anima_nexus_message_type" id="anima_nexus_message_type">
				<option value="general" <?php selected($message_type, 'general'); ?>>
					<?php _e('Mensaje General', 'animaavatar'); ?>
				</option>
				<option value="duda" <?php selected($message_type, 'duda'); ?>><?php _e('Duda/Pregunta', 'animaavatar'); ?>
				</option>
				<option value="idea" <?php selected($message_type, 'idea'); ?>>
					<?php _e('Idea/Sugerencia', 'animaavatar'); ?>
				</option>
				<option value="evento" <?php selected($message_type, 'evento'); ?>>
					<?php _e('Evento/Streaming', 'animaavatar'); ?>
				</option>
			</select>
		</p>

		<div id="nexus-event-details" style="display: <?php echo ($message_type === 'evento') ? 'block' : 'none'; ?>;">
			<h4><?php _e('Detalles del Evento', 'animaavatar'); ?></h4>
			<p>
				<label for="anima_nexus_event_date"><?php _e('Fecha y Hora del Evento:', 'animaavatar'); ?></label>
				<input type="datetime-local" id="anima_nexus_event_date" name="anima_nexus_event_date"
					value="<?php echo esc_attr($event_date); ?>">
			</p>
			<p>
				<label for="anima_nexus_event_location"><?php _e('Ubicación/Plataforma:', 'animaavatar'); ?></label>
				<input type="text" id="anima_nexus_event_location" name="anima_nexus_event_location"
					value="<?php echo esc_attr($event_location); ?>">
			</p>
			<p>
				<label for="anima_nexus_event_link"><?php _e('Enlace para Unirse:', 'animaavatar'); ?></label>
				<input type="url" id="anima_nexus_event_link" name="anima_nexus_event_link"
					value="<?php echo esc_url($event_link); ?>">
			</p>
		</div>

		<script>
			document.addEventListener('DOMContentLoaded', function () {
				var messageTypeSelect = document.getElementById('anima_nexus_message_type');
				var eventDetails = document.getElementById('nexus-event-details');

				function toggleEventDetails() {
					if (messageTypeSelect.value === 'evento') {
						eventDetails.style.display = 'block';
					} else {
						eventDetails.style.display = 'none';
					}
				}

				messageTypeSelect.addEventListener('change', toggleEventDetails);
				toggleEventDetails();
			});
		</script>
		<?php
	}
}

// Guardar los datos del metabox
add_action('save_post_nexus_post', 'anima_nexus_save_meta_data_cpt');
if (!function_exists('anima_nexus_save_meta_data_cpt')) {
	function anima_nexus_save_meta_data_cpt($post_id)
	{
		if (!isset($_POST['anima_nexus_meta_nonce']) || !wp_verify_nonce($_POST['anima_nexus_meta_nonce'], 'anima_nexus_save_meta')) {
			return;
		}
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return;
		}
		if (!current_user_can('edit_post', $post_id)) {
			return;
		}

		if (isset($_POST['anima_nexus_message_type'])) {
			update_post_meta($post_id, '_anima_nexus_message_type', sanitize_text_field($_POST['anima_nexus_message_type']));
		}
		// Si no es evento, limpiamos los campos de evento
		if (isset($_POST['anima_nexus_message_type']) && $_POST['anima_nexus_message_type'] !== 'evento') {
			delete_post_meta($post_id, '_anima_nexus_event_date');
			delete_post_meta($post_id, '_anima_nexus_event_location');
			delete_post_meta($post_id, '_anima_nexus_event_link');
		} elseif (isset($_POST['anima_nexus_message_type']) && $_POST['anima_nexus_message_type'] === 'evento') {
			// Si es evento, guardamos los campos
			if (isset($_POST['anima_nexus_event_date'])) {
				update_post_meta($post_id, '_anima_nexus_event_date', sanitize_text_field($_POST['anima_nexus_event_date']));
			}
			if (isset($_POST['anima_nexus_event_location'])) {
				update_post_meta($post_id, '_anima_nexus_event_location', sanitize_text_field($_POST['anima_nexus_event_location']));
			}
			if (isset($_POST['anima_nexus_event_link'])) {
				update_post_meta($post_id, '_anima_nexus_event_link', esc_url_raw($_POST['anima_nexus_event_link']));
			}
		}
	}
}

// Función helper para obtener posts (útil en templates)
if (!function_exists('anima_nexus_get_posts_for_feed')) {
	function anima_nexus_get_posts_for_feed($paged = 1, $posts_per_page = 10)
	{
		$args = array(
			'post_type' => 'nexus_post',
			'post_status' => 'publish',
			'posts_per_page' => $posts_per_page,
			'paged' => $paged,
			'orderby' => 'date',
			'order' => 'DESC',
		);
		return new WP_Query($args);
	}
}

// =====================================================================
// 5. PROCESAMIENTO DE FORMULARIO FRONTEND
// =====================================================================

add_action('admin_post_nopriv_submit_nexus_post', 'anima_nexus_submit_post_frontend');
add_action('admin_post_submit_nexus_post', 'anima_nexus_submit_post_frontend');

if (!function_exists('anima_nexus_submit_post_frontend')) {
	function anima_nexus_submit_post_frontend()
	{
		// Verificar si el usuario está logueado
		if (!is_user_logged_in()) {
			wp_redirect(home_url('/login/'));
			exit;
		}

		// Verificar Nonce
		if (!isset($_POST['nexus_post_nonce']) || !wp_verify_nonce($_POST['nexus_post_nonce'], 'submit_nexus_post')) {
			wp_die(__('Acción no permitida o sesión expirada.', 'animaavatar'));
		}

		// Sanitizar entradas
		$title = isset($_POST['post_title']) ? sanitize_text_field($_POST['post_title']) : '';
		$content = isset($_POST['post_content']) ? sanitize_textarea_field($_POST['post_content']) : '';
		$message_type = isset($_POST['message_type']) ? sanitize_text_field($_POST['message_type']) : 'general';
		$event_date = isset($_POST['event_date']) ? sanitize_text_field($_POST['event_date']) : '';
		$event_location = isset($_POST['event_location']) ? sanitize_text_field($_POST['event_location']) : '';
		$event_link = isset($_POST['event_link']) ? esc_url_raw($_POST['event_link']) : '';

		// Validar campos obligatorios básicos
		if (empty($title) || empty($content)) {
			wp_redirect(add_query_arg('status', 'error_empty', home_url('/comunidad/'))); // Ajusta la URL de redirección
			exit;
		}

		// Preparar datos del post
		$post_data = array(
			'post_title' => $title,
			'post_content' => $content,
			'post_status' => 'pending', // Pendiente de revisión
			'post_type' => 'nexus_post',
			'post_author' => get_current_user_id(),
		);

		// Insertar el post
		$post_id = wp_insert_post($post_data);

		if (!is_wp_error($post_id)) {
			// Guardar metadatos
			exit;
		}
	}
}

// =====================================================================
// 6. INTERCEPTOR DE MENÚ PARA USUARIOS NO REGISTRADOS (MODAL)
// =====================================================================

add_action('wp_footer', 'anima_restricted_menu_interceptor');

function anima_restricted_menu_interceptor()
{
	// 1. Si el usuario YA está logueado, no hacemos nada y salimos.
	if (is_user_logged_in()) {
		return;
	}

	// 2. Configuración: Slugs a bloquear y URL de login
	$restricted_slugs = array(
		'comunidad',
		'nexus',
	);

	// URL de tu página de inicio de sesión/registro
	$url_registro = home_url('/login/');

	?>
	<div id="anima-restricted-modal-overlay" style="display: none;">
		<div class="anima-restricted-modal-content">
			<button class="modal-close-btn">✕</button>
			<div class="modal-icon">
				<span class="dashicons dashicons-lock"></span>
			</div>
			<h3>Acceso Restringido</h3>
			<p class="main-message">Únete al metaverso</p>
			<p class="sub-message">El Nexus y la Comunidad son áreas exclusivas para agentes registrados. Crea tu identidad
				digital para acceder.</p>

			<a href="<?php echo esc_url($url_registro); ?>" class="anima-btn modal-register-btn">
				INICIAR SESIÓN / REGISTRO
				<span class="dashicons dashicons-arrow-right-alt"></span>
			</a>
		</div>
	</div>

	<script type="text/javascript">
		jQuery(document).ready(function ($) {
			// Selectores
			var slugsToRestrict = <?php echo json_encode($restricted_slugs); ?>;
			// Asegúrate de que '#site-navigation' es el ID correcto del contenedor de tu menú en header.php
			var navMenu = $('#site-navigation');
			var modalOverlay = $('#anima-restricted-modal-overlay');
			var closeBtn = $('.anima-restricted-modal-content .modal-close-btn');

			// Función para mostrar el modal
			function showRestrictedModal(e) {
				e.preventDefault(); // DETIENE la navegación al enlace
				modalOverlay.fadeIn(300).css('display', 'flex'); // Muestra el modal
			}

			// Función para cerrar el modal
			function closeRestrictedModal() {
				modalOverlay.fadeOut(300);
			}

			// Iterar sobre los slugs restringidos y añadir el listener a los enlaces del menú
			if (navMenu.length) {
				$.each(slugsToRestrict, function (index, slug) {
					// Busca enlaces que contengan el slug en su href
					navMenu.find('a[href*="' + slug + '"]').on('click', showRestrictedModal);
				});
			}

			// Listeners para cerrar el modal
			closeBtn.on('click', closeRestrictedModal);
			modalOverlay.on('click', function (e) {
				// Cierra si se hace clic fuera del contenido del modal
				if ($(e.target).is('#anima-restricted-modal-overlay')) {
					closeRestrictedModal();
				}
			});
		});
	</script>
	<?php
}

// =====================================================================
// 7. FUNCIONES CRÍTICAS DEL DASHBOARD / PERFIL DE USUARIO
// =====================================================================

// 7.1. HELPER: Obtener cursos del usuario (Necesario para 'Entrenamiento')
// 7.1. HELPER: Obtener cursos del usuario (CORREGIDO)
if (!function_exists('anima_get_user_courses')) {
	function anima_get_user_courses($user_id)
	{
		// Verificaciones básicas
		if (!function_exists('wc_get_orders') || !$user_id)
			return [];

		// 1. Buscar pedidos del usuario (Completados o Procesando)
		$orders = wc_get_orders([
			'customer_id' => $user_id,
			'status' => ['completed', 'processing'],
			'limit' => -1,
		]);

		$seen_products = [];
		$courses = [];

		foreach ($orders as $order) {
			foreach ($order->get_items() as $item) {
				if (!is_a($item, 'WC_Order_Item_Product')) {
					continue;
				}
				$product_id = $item->get_product_id(); // ID de lo que compró (puede ser variación)
				$product = $item->get_product();

				// Recopilar IDs a buscar: El producto comprado Y su padre (si es variación)
				$ids_to_check = [$product_id];
				if ($product && $product->get_parent_id()) {
					$ids_to_check[] = $product->get_parent_id();
				}

				// Evitar procesar el mismo producto varias veces en el bucle
				// Usamos el ID principal para el control de duplicados
				$main_id = ($product && $product->get_parent_id()) ? $product->get_parent_id() : $product_id;
				if (in_array($main_id, $seen_products))
					continue;
				$seen_products[] = $main_id;

				// 2. Buscar curso vinculado a CUALQUIERA de estos IDs
				$course_query = new WP_Query([
					'post_type' => 'curso',
					'post_status' => 'publish',
					'meta_query' => [
						[
							'key' => '_anima_product_id',
							'value' => $ids_to_check,
							'compare' => 'IN' // Busca si el ID guardado está en la lista (Hijo o Padre)
						]
					],
					'posts_per_page' => 1,
					'fields' => 'ids'
				]);

				if ($course_query->have_posts()) {
					$course_id = $course_query->posts[0];

					$courses[] = [
						'course_id' => $course_id,
						'title' => get_the_title($course_id),
						'url' => get_permalink($course_id),
						'thumb' => get_the_post_thumbnail_url($course_id, 'medium') ?: wc_placeholder_img_src(),
					];
				}
			}
		}
		return $courses;
	}
}

// 7.2. HELPER: Obtener pedidos recientes (Necesario para 'Pedidos Recientes')
if (!function_exists('anima_get_recent_orders') && class_exists('WooCommerce')) {
	function anima_get_recent_orders($user_id, $count = 3)
	{
		$customer_orders = wc_get_orders([
			'limit' => $count,
			'customer_id' => $user_id,
			'orderby' => 'date',
			'order' => 'DESC',
			'status' => ['completed', 'processing'],
		]);

		$orders_data = [];
		if (!empty($customer_orders)) {
			foreach ($customer_orders as $order) {
				$orders_data[] = [
					'id' => $order->get_id(),
					'date' => wc_format_datetime($order->get_date_created()),
					'status' => wc_get_order_status_name($order->get_status()),
					'status_slug' => $order->get_status(),
					'total' => $order->get_formatted_order_total(),
					'view_url' => $order->get_view_order_url(),
				];
			}
		}
		return $orders_data;
	}
}

// 7.3. PROCESADOR: Maneja la subida de la foto de perfil (FIX DE LA FOTO)
function anima_handle_profile_picture_upload()
{
	// 1. Comprobaciones de seguridad y login
	if (!is_user_logged_in()) {
		wp_die('Usuario no logueado.');
	}

	// Verificar el "nonce" de seguridad del formulario en page-perfil.php
	if (!isset($_POST['profile_picture_nonce']) || !wp_verify_nonce($_POST['profile_picture_nonce'], 'upload_profile_picture_action')) {
		wp_die('Error de seguridad (Nonce).');
	}

	// Verificar que se ha enviado un archivo
	if (empty($_FILES['profile_picture']['name'])) {
		wp_redirect(wp_get_referer()); // Simplemente recargar si no hay archivo
		exit;
	}

	// 2. Preparar el entorno de WordPress para manejar subidas
	require_once(ABSPATH . 'wp-admin/includes/image.php');
	require_once(ABSPATH . 'wp-admin/includes/file.php');
	require_once(ABSPATH . 'wp-admin/includes/media.php');

	// 3. Manejar la subida del archivo e insertarlo en la biblioteca de medios
	$upload_overrides = array('test_form' => false);
	$movefile = wp_handle_upload($_FILES['profile_picture'], $upload_overrides);

	if ($movefile && !isset($movefile['error'])) {
		// Inserción en la Biblioteca
		$attach_id = wp_insert_attachment(
			array(
				'guid' => $movefile['url'],
				'post_mime_type' => $movefile['type'],
				'post_title' => preg_replace('/\.[^.]+$/', '', basename($movefile['file'])),
				'post_content' => '',
				'post_status' => 'inherit'
			),
			$movefile['file']
		);

		// Generar metadatos (miniaturas)
		$attach_data = wp_generate_attachment_metadata($attach_id, $movefile['file']);
		wp_update_attachment_metadata($attach_id, $attach_data);

		// 4. Guardar el ID de la imagen en los metadatos del usuario actual
		update_user_meta(get_current_user_id(), 'profile_picture', $attach_id);

		// Redirigir con éxito
		wp_redirect(add_query_arg('upload_status', 'success', wp_get_referer()));
		exit;

	} else {
		// Error de subida: (Muestra el error en el log si WP_DEBUG está activo)
		wp_redirect(add_query_arg('upload_status', 'error_upload', wp_get_referer()));
		exit;
	}
}

// Conectar esta función a la acción del formulario
add_action('admin_post_upload_profile_picture', 'anima_handle_profile_picture_upload');


// 7.4. FILTRO: Mostrar la foto de perfil custom en lugar de Gravatar
add_filter('get_avatar', 'anima_get_custom_profile_picture', 10, 5);
if (!function_exists('anima_get_custom_profile_picture')) {
	function anima_get_custom_profile_picture($avatar, $id_or_email, $size, $default, $alt)
	{
		$user_id = 0;

		// Determinar el ID del usuario
		if (is_numeric($id_or_email)) {
			$user_id = (int) $id_or_email;
		} elseif (is_string($id_or_email) && ($user = get_user_by('email', $id_or_email))) {
			$user_id = $user->ID;
		} elseif (is_object($id_or_email) && !empty($id_or_email->user_id)) {
			$user_id = (int) $id_or_email->user_id;
		}

		if ($user_id) {
			$custom_image_id = get_user_meta($user_id, 'profile_picture', true);
			if ($custom_image_id) {
				$image_url = wp_get_attachment_image_url($custom_image_id, 'thumbnail'); // Usamos el tamaño 'thumbnail'
				if ($image_url) {
					// Generamos el HTML del avatar personalizado
					return '<img alt="' . esc_attr($alt) . '" src="' . esc_url($image_url) . '" class="avatar avatar-' . esc_attr($size) . ' photo" height="' . esc_attr($size) . '" width="' . esc_attr($size) . '" />';
				}
			}
		}
		return $avatar; // Devolver Gravatar si no hay imagen custom
	}
}

// =====================================================================
// 8. FUNCIONES DE COMERCIO (WOOCOMMERCE)
// =====================================================================

/**
 * Obtiene el precio formateado de un producto de WooCommerce por ID.
 * Necesario para mostrar los precios en la vista de recarga.
 */
if (!function_exists('anima_get_product_price_html')) {
	function anima_get_product_price_html($product_id)
	{
		// La función solo debe ejecutarse si WooCommerce está activo
		if (!class_exists('WooCommerce'))
			return '';

		$product = wc_get_product($product_id);

		// Devolver el precio formateado si el producto existe
		return $product ? $product->get_price_html() : 'N/A';
	}
}

/* ===========================================================
   9. MOBILE APP DOCK (BOTTOM NAV)
   =========================================================== */
add_action('wp_footer', 'anima_render_mobile_dock');

function anima_render_mobile_dock()
{
	if (!wp_is_mobile())
		return;

	$home_cls = is_front_page() ? 'active' : '';
	$nexus_cls = is_page('comunidad') ? 'active' : '';
	$profile_cls = is_page('perfil') ? 'active' : '';
	$courses_cls = is_page('cursos') ? 'active' : '';
	?>
	<nav class="anima-mobile-dock">
		<a href="<?php echo home_url('/'); ?>" class="dock-item <?php echo $home_cls; ?>">
			<span class="dashicons dashicons-admin-home"></span>
			<span>Inicio</span>
		</a>
		<a href="<?php echo home_url('/cursos/'); ?>" class="dock-item <?php echo $courses_cls; ?>">
			<span class="dashicons dashicons-welcome-learn-more"></span>
			<span>Cursos</span>
		</a>
		<a href="<?php echo home_url('/comunidad/'); ?>" class="dock-item dock-main <?php echo $nexus_cls; ?>">
			<div class="dock-circle">
				<span class="dashicons dashicons-groups"></span>
			</div>
		</a>
		<a href="<?php echo home_url('/perfil/'); ?>" class="dock-item <?php echo $profile_cls; ?>">
			<span class="dashicons dashicons-id"></span>
			<span>Perfil</span>
		</a>
		<a href="<?php echo home_url('/menu/'); ?>" class="dock-item">
			<span class="dashicons dashicons-menu"></span>
			<span>Menú</span>
		</a>
	</nav>
	<?php
}

/* ===========================================================
   10. PUBLIC PROFILE REWRITE RULES & TEMPLATE LOADER
   =========================================================== */
function anima_profile_rewrite_rule()
{
	add_rewrite_rule('^profile/([^/]*)/?', 'index.php?anima_profile_user=$matches[1]', 'top');
}
add_action('init', 'anima_profile_rewrite_rule');

function anima_profile_query_vars($vars)
{
	$vars[] = 'anima_profile_user';
	return $vars;
}
add_filter('query_vars', 'anima_profile_query_vars');

function anima_profile_template_include($template)
{
	if (get_query_var('anima_profile_user')) {
		$new_template = locate_template(array('page-public-profile.php'));
		if ('' != $new_template) {
			return $new_template;
		}
	}
	return $template;
}
add_filter('template_include', 'anima_profile_template_include');

function anima_custom_author_link($link, $author_id, $author_nicename)
{
	return home_url('/profile/' . $author_nicename);
}
add_filter('author_link', 'anima_custom_author_link', 10, 3);

// =====================================================================
// 11. CUSTOM CSS & DYNAMIC STYLES
// =====================================================================
function anima_custom_css_output()
{
	$accent_color = get_option('anima_app_accent_color', '#00F0FF');
	$custom_css = get_option('anima_custom_css', '');
	?>
	<style type="text/css">
		:root {
			--anima-app-accent:
				<?php echo esc_attr($accent_color); ?>
			;
		}

		<?php echo wp_strip_all_tags($custom_css); ?>
	</style>
	<?php
}
add_action('wp_head', 'anima_custom_css_output');

// PWA Integration
function anima_pwa_integration()
{
	?>
	<link rel="manifest" href="<?php echo get_template_directory_uri(); ?>/manifest.json">
	<script>
		if ('serviceWorker' in navigator) {
			window.addEventListener('load', function () {
				navigator.serviceWorker.register('<?php echo get_template_directory_uri(); ?>/sw.js')
					.then(function (registration) {
						console.log('ServiceWorker registration successful with scope: ', registration.scope);
					}, function (err) {
						console.log('ServiceWorker registration failed: ', err);
					});
			});
		}
	</script>
	<?php
}
add_action('wp_head', 'anima_pwa_integration');

/**
 * Mentorship Integration
 * Add booking link to order emails for Mentorship products.
 */
add_action('woocommerce_email_order_details', 'anima_add_mentorship_link_to_email', 10, 4);
function anima_add_mentorship_link_to_email($order, $sent_to_admin, $plain_text, $email)
{
	if ($sent_to_admin)
		return;

	$has_mentorship = false;
	foreach ($order->get_items() as $item_id => $item) {
		$product = $item->get_product();
		if ($product && has_term('mentorship', 'product_cat', $product->get_id())) {
			$has_mentorship = true;
			break;
		}
	}

	if ($has_mentorship) {
		echo '<div style="margin: 20px 0; padding: 15px; background-color: #f0f0f0; border: 1px solid #ddd; border-radius: 5px;">';
		echo '<h3 style="color: #6a0dad;">Book Your Mentorship Session</h3>';
		echo '<p>Thank you for purchasing a mentorship session! Please click the link below to schedule your time with our experts.</p>';
		echo '<a href="https://calendly.com/your-calendly-link" style="display: inline-block; padding: 10px 20px; background-color: #6a0dad; color: #fff; text-decoration: none; border-radius: 5px;">Schedule Now</a>';
		echo '</div>';
	}
}
