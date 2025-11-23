<?php
/**
 * Gestor de Datos del Curso (Visual Builder)
 * Reemplaza los campos JSON manuales por una interfaz gráfica.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// 1. REGISTRAR METABOX
add_action( 'add_meta_boxes', 'anima_add_improved_course_metaboxes' );

function anima_add_improved_course_metaboxes() {
    // Quitamos otros metaboxes antiguos si existen para limpiar
    remove_meta_box( 'anima_course_data', 'curso', 'normal' ); 

    add_meta_box(
        'anima_course_manager',
        '🚀 GESTOR DE CURSO (Anima Engine)',
        'anima_render_course_manager',
        'curso',
        'normal',
        'high'
    );
}

// 2. RENDERIZAR INTERFAZ
function anima_render_course_manager( $post ) {
    wp_nonce_field( 'anima_save_course_data', 'anima_course_nonce' );

    // Recuperar datos existentes
    $product_id   = get_post_meta( $post->ID, '_anima_product_id', true );
    $subtitle     = get_post_meta( $post->ID, '_anima_course_subtitle', true );
    $level        = get_post_meta( $post->ID, '_anima_course_level', true );
    $duration     = get_post_meta( $post->ID, '_anima_course_duration', true );
    $video_url    = get_post_meta( $post->ID, '_anima_video_url', true ); // Nuevo campo Trailer
    
    // JSONs (Si están vacíos, iniciamos array vacío)
    $syllabus_json = get_post_meta( $post->ID, '_anima_syllabus_json', true );
    $downloads_json = get_post_meta( $post->ID, '_anima_course_downloads', true );
    
    // Productos de WooCommerce para el select
    $products = function_exists('wc_get_products') ? wc_get_products(['limit' => -1, 'status' => 'publish']) : [];
    ?>

    <div class="anima-manager-wrapper">
        
        <style>
            .anima-manager-wrapper { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
            .anima-row { display: flex; gap: 20px; margin-bottom: 15px; }
            .anima-col { flex: 1; }
            .anima-col label { display: block; font-weight: 600; margin-bottom: 5px; color: #2c3338; }
            .anima-input { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; }
            
            /* Secciones */
            .anima-section-title { 
                border-bottom: 2px solid #007cba; padding-bottom: 10px; margin: 30px 0 15px; 
                font-size: 1.1em; font-weight: bold; text-transform: uppercase; color: #007cba; 
            }

            /* Constructor de Temario */
            .module-block { background: #fff; border: 1px solid #ccd0d4; border-left: 4px solid #007cba; margin-bottom: 15px; padding: 15px; box-shadow: 0 1px 1px rgba(0,0,0,0.04); }
            .module-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
            .module-header input { font-weight: bold; font-size: 1.1em; width: 70%; }
            .lesson-list { margin-left: 20px; border-left: 2px solid #eee; padding-left: 15px; }
            .lesson-item { display: flex; gap: 10px; margin-bottom: 8px; align-items: center; }
            .lesson-item input[type="text"] { flex: 2; } /* Título */
            .lesson-item input[type="text"].video-input { flex: 1; color: #666; } /* Video File */
            
            /* Botones */
            .btn-anima { cursor: pointer; padding: 6px 12px; border: none; border-radius: 3px; font-size: 13px; }
            .btn-primary { background: #2271b1; color: #fff; }
            .btn-secondary { background: #f0f0f1; color: #2271b1; border: 1px solid #2271b1; }
            .btn-danger { color: #b32d2e; background: none; text-decoration: underline; font-size: 12px; }
            .btn-add-lesson { margin-top: 10px; display: inline-block; }

            /* Descargas */
            .download-item { display: flex; gap: 10px; margin-bottom: 10px; align-items: center; background: #f9f9f9; padding: 10px; border: 1px solid #eee; }
        </style>

        <div class="anima-section-title">Configuración Principal</div>
        
        <div class="anima-row">
            <div class="anima-col">
                <label>Producto WooCommerce Asociado:</label>
                <select name="_anima_product_id" class="anima-input">
                    <option value="">-- Seleccionar Producto --</option>
                    <?php foreach($products as $prod): ?>
                        <option value="<?php echo $prod->get_id(); ?>" <?php selected($product_id, $prod->get_id()); ?>>
                            <?php echo $prod->get_name(); ?> (ID: <?php echo $prod->get_id(); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="anima-col">
                <label>Video Trailer / Intro (URL):</label>
                <input type="url" name="_anima_video_url" class="anima-input" value="<?php echo esc_attr($video_url); ?>" placeholder="https://vimeo.com/...">
            </div>
        </div>

        <div class="anima-row">
            <div class="anima-col">
                <label>Nivel:</label>
                <select name="_anima_course_level" class="anima-input">
                    <option value="Inicial" <?php selected($level, 'Inicial'); ?>>Inicial</option>
                    <option value="Intermedio" <?php selected($level, 'Intermedio'); ?>>Intermedio</option>
                    <option value="Avanzado" <?php selected($level, 'Avanzado'); ?>>Avanzado</option>
                </select>
            </div>
            <div class="anima-col">
                <label>Duración (Texto):</label>
                <input type="text" name="_anima_course_duration" class="anima-input" value="<?php echo esc_attr($duration); ?>" placeholder="Ej: 3h 20m">
            </div>
        </div>
        
        <div class="anima-row">
            <div class="anima-col">
                <label>Subtítulo Corto:</label>
                <input type="text" name="_anima_course_subtitle" class="anima-input" value="<?php echo esc_attr($subtitle); ?>">
            </div>
        </div>

        <div class="anima-section-title">Plan de Estudios (Temario)</div>
        <div id="syllabus-builder"></div>
        <button type="button" class="btn-anima btn-primary" onclick="addModule()">+ Añadir Módulo</button>
        
        <textarea name="_anima_syllabus_json" id="_anima_syllabus_json" style="display:none;"><?php echo esc_textarea($syllabus_json); ?></textarea>


        <div class="anima-section-title">Materiales y Descargas</div>
        <div id="downloads-builder"></div>
        <button type="button" class="btn-anima btn-secondary" onclick="addDownload()">+ Añadir Archivo</button>
        
        <textarea name="_anima_course_downloads" id="_anima_course_downloads" style="display:none;"><?php echo esc_textarea($downloads_json); ?></textarea>

    </div>

    <script>
        // --- SYLLABUS LOGIC ---
        let syllabusData = <?php echo $syllabus_json ? $syllabus_json : '[]'; ?>;
        const syllabusContainer = document.getElementById('syllabus-builder');
        const syllabusInput = document.getElementById('_anima_syllabus_json');

        function renderSyllabus() {
            syllabusContainer.innerHTML = '';
            syllabusData.forEach((mod, modIndex) => {
                let lessonsHtml = '';
                if(mod.lessons) {
                    mod.lessons.forEach((lesson, lessonIndex) => {
                        lessonsHtml += `
                            <div class="lesson-item">
                                <span>📄</span>
                                <input type="text" placeholder="Título lección" value="${lesson.title || ''}" oninput="updateLesson(${modIndex}, ${lessonIndex}, 'title', this.value)">
                                <input type="text" class="video-input" placeholder="Nombre archivo (ej: modulo-1.mp4)" value="${lesson.video || ''}" oninput="updateLesson(${modIndex}, ${lessonIndex}, 'video', this.value)">
                                <button type="button" class="btn-danger" onclick="removeLesson(${modIndex}, ${lessonIndex})">X</button>
                            </div>
                        `;
                    });
                }

                const html = `
                    <div class="module-block">
                        <div class="module-header">
                            <input type="text" placeholder="Título del Módulo (Ej: Introducción)" value="${mod.title || ''}" oninput="updateModule(${modIndex}, 'title', this.value)">
                            <button type="button" class="btn-danger" onclick="removeModule(${modIndex})">Eliminar Módulo</button>
                        </div>
                        <div class="lesson-list">
                            ${lessonsHtml}
                            <button type="button" class="btn-anima btn-secondary btn-add-lesson" onclick="addLesson(${modIndex})">+ Añadir Lección</button>
                        </div>
                    </div>
                `;
                syllabusContainer.insertAdjacentHTML('beforeend', html);
            });
            syllabusInput.value = JSON.stringify(syllabusData);
        }

        window.addModule = function() {
            syllabusData.push({ title: "", lessons: [] });
            renderSyllabus();
        };
        window.removeModule = function(i) {
            if(confirm('¿Borrar módulo?')) { syllabusData.splice(i, 1); renderSyllabus(); }
        };
        window.updateModule = function(i, key, val) {
            syllabusData[i][key] = val;
            syllabusInput.value = JSON.stringify(syllabusData);
        };
        window.addLesson = function(modIndex) {
            if(!syllabusData[modIndex].lessons) syllabusData[modIndex].lessons = [];
            syllabusData[modIndex].lessons.push({ title: "", video: "" });
            renderSyllabus();
        };
        window.removeLesson = function(modI, lessI) {
            syllabusData[modI].lessons.splice(lessI, 1);
            renderSyllabus();
        };
        window.updateLesson = function(modI, lessI, key, val) {
            syllabusData[modI].lessons[lessI][key] = val;
            syllabusInput.value = JSON.stringify(syllabusData);
        };

        // --- DOWNLOADS LOGIC ---
        let downloadsData = <?php echo $downloads_json ? $downloads_json : '[]'; ?>;
        const dlContainer = document.getElementById('downloads-builder');
        const dlInput = document.getElementById('_anima_course_downloads');

        function renderDownloads() {
            dlContainer.innerHTML = '';
            downloadsData.forEach((dl, i) => {
                const html = `
                    <div class="download-item">
                        <span>📂</span>
                        <input type="text" class="anima-input" placeholder="Nombre del archivo" value="${dl.name || ''}" oninput="updateDownload(${i}, 'name', this.value)">
                        <input type="text" class="anima-input" placeholder="Nombre archivo (ej: guia.pdf)" value="${dl.file || ''}" oninput="updateDownload(${i}, 'file', this.value)">
                        <button type="button" class="btn-danger" onclick="removeDownload(${i})">X</button>
                    </div>
                `;
                dlContainer.insertAdjacentHTML('beforeend', html);
            });
            dlInput.value = JSON.stringify(downloadsData);
        }

        window.addDownload = function() {
            downloadsData.push({ name: "", file: "" });
            renderDownloads();
        };
        window.removeDownload = function(i) {
            downloadsData.splice(i, 1);
            renderDownloads();
        };
        window.updateDownload = function(i, key, val) {
            downloadsData[i][key] = val;
            dlInput.value = JSON.stringify(downloadsData);
        };

        // Inicializar
        renderSyllabus();
        renderDownloads();
    </script>
    <?php
}

// 3. GUARDAR DATOS
add_action( 'save_post', 'anima_save_improved_course_data' );

function anima_save_improved_course_data( $post_id ) {
    if ( ! isset( $_POST['anima_course_nonce'] ) || ! wp_verify_nonce( $_POST['anima_course_nonce'], 'anima_save_course_data' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    // Campos simples
    $fields = ['_anima_product_id', '_anima_video_url', '_anima_course_level', '_anima_course_duration', '_anima_course_subtitle'];
    foreach($fields as $field) {
        if(isset($_POST[$field])) update_post_meta($post_id, $field, sanitize_text_field($_POST[$field]));
    }

    // Campos JSON (Temario y Descargas)
    // Guardamos el string JSON tal cual (el JS ya lo genera formateado)
    if(isset($_POST['_anima_syllabus_json'])) update_post_meta($post_id, '_anima_syllabus_json', wp_kses_post($_POST['_anima_syllabus_json']));
    if(isset($_POST['_anima_course_downloads'])) update_post_meta($post_id, '_anima_course_downloads', wp_kses_post($_POST['_anima_course_downloads']));
}