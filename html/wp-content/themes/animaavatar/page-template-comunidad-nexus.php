<?php
/**
 * Template Name: Comunidad Nexus Demo
 * Description: Plantilla para listar Avatares con FILTROS, paginación y PANTALLA DE ACCESO restringido.
 */

get_header(); ?>

<div id="primary" class="content-area nexus-container">
    <main id="main" class="site-main container">

        <?php
        // ==============================================================================
        // LÓGICA DE CONTROL DE ACCESO (La nueva "Puerta")
        // ==============================================================================
        if ( ! is_user_logged_in() ) : ?>

            <div class="nexus-access-denied-wrapper">
                <div class="cyberpunk-box access-box">
                    <span class="dashicons dashicons-lock" style="font-size: 4em; color: var(--nexus-neon-pink, #f0f); margin-bottom: 20px;"></span>
                    <h1 class="entry-title glitch-text" data-text="ACCESO RESTRINGIDO">ACCESO RESTRINGIDO</h1>
                    <p class="cyber-subtitle" style="margin-bottom: 30px;">Esta zona del Nexus requiere identificación neuronal.</p>
                    
                    <h2 style="color: var(--nexus-neon-green, #39ff14); text-transform: uppercase; letter-spacing: 2px; margin: 40px 0;">
                        UNETE AL METAVERSO
                    </h2>
                    
                    <a href="https://animaavataragency.com/login" class="anima-nexus-btn nexus-login-btn">
                        <span class="dashicons dashicons-admin-network"></span> INICIAR SESIÓN / REGISTRO
                    </a>
                </div>
            </div>

        <?php 
        // ==============================================================================
        // CONTENIDO PARA USUARIOS LOGUEADOS (El Directorio Completo)
        // ==============================================================================
        else : 
            $current_user_id = get_current_user_id();
            // --- CAPTURAR PARÁMETROS DE FILTRADO ---
            $filter_status = isset( $_GET['nexus_filter'] ) ? sanitize_text_field( $_GET['nexus_filter'] ) : 'all';
            $search_term   = isset( $_GET['nexus_search'] ) ? sanitize_text_field( $_GET['nexus_search'] ) : '';
            // --- CONFIGURACIÓN DE PAGINACIÓN ---
            $nexus_per_page = 12;
            $nexus_paged    = ( get_query_var( 'paged' ) ) ? absint( get_query_var( 'paged' ) ) : 1;
            if ( $nexus_paged < 1 ) { $nexus_paged = 1; }
            $nexus_offset   = ( $nexus_paged - 1 ) * $nexus_per_page;
        ?>

            <header class="entry-header">
                <h1 class="entry-title glitch-text" data-text="NEXUS DE AVATARES">NEXUS DE AVATARES</h1>
                <p class="cyber-subtitle">Directorio de Neural Links activas y pendientes.</p>
            </header>

            <?php
            if ( function_exists( 'anima_nexus_get_incoming_requests_ids' ) ) {
                $incoming_requests_ids = anima_nexus_get_incoming_requests_ids( $current_user_id );
                if ( ! empty( $incoming_requests_ids ) ) : ?>
                    <section class="nexus-section incoming-transmissions">
                        <h2 class="section-title"><span class="dashicons dashicons-wifi"></span> Transmisiones Entrantes (<?php echo count($incoming_requests_ids); ?>)</h2>
                        <div class="nexus-requests-list">
                            <?php foreach ( $incoming_requests_ids as $requester_id ) : 
                                $requester_data = get_userdata( $requester_id );
                                if ( ! $requester_data ) continue; ?>
                                <div class="nexus-request-card cyberpunk-box" data-requester-id="<?php echo esc_attr( $requester_id ); ?>">
                                    <div class="request-user-info">
                                        <?php echo get_avatar( $requester_id, 50 ); ?>
                                        <span class="agent-name">Avatar: <strong><?php echo esc_html( $requester_data->display_name ); ?></strong></span>
                                        <span class="request-label">Solicita enlace neuronal...</span>
                                    </div>
                                    <div class="nexus-request-actions">
                                        <button class="anima-nexus-btn nexus-accept-btn"><span class="dashicons dashicons-yes"></span> Autorizar</button>
                                        <button class="anima-nexus-btn nexus-block-btn"><span class="dashicons dashicons-no"></span> Bloquear Señal</button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; 
            } ?>

            <section class="nexus-section all-agents-directory">
                <h2 class="section-title"><span class="dashicons dashicons-groups"></span> Explorar la Red</h2>
                
                <div class="nexus-filter-bar cyberpunk-box">
                    <form method="get" action="<?php echo esc_url( get_permalink() ); ?>" class="nexus-filter-form">
                        <div class="filter-group status-filter">
                            <label for="nexus_filter" class="filter-label"><span class="dashicons dashicons-filter"></span> Modo de Visualización:</label>
                            <select name="nexus_filter" id="nexus_filter" class="cyber-input" onchange="this.form.submit()">
                                <option value="all" <?php selected( $filter_status, 'all' ); ?>>🌐 Toda la Red Global</option>
                                <option value="friends" <?php selected( $filter_status, 'friends' ); ?>>🧠 Mis Enlaces Neuronales (Amigos)</option>
                            </select>
                        </div>
                        <div class="filter-group search-filter">
                            <label for="nexus_search" class="filter-label"><span class="dashicons dashicons-search"></span> Buscar Avatar:</label>
                            <div class="search-input-wrapper">
                                <input type="text" name="nexus_search" id="nexus_search" class="cyber-input" placeholder="Nombre del avatar..." value="<?php echo esc_attr( $search_term ); ?>">
                                <button type="submit" class="anima-nexus-btn nexus-search-btn">Filtrar</button>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="agents-grid nexus-grid-layout">
                    <?php
                    // CONSTRUCCIÓN DE LA CONSULTA
                    $query_args = array(
                        'number'  => $nexus_per_page,
                        'offset'  => $nexus_offset,
                        'orderby' => 'registered',
                        'order'   => 'DESC',
                        'fields'  => 'all_with_meta',
                    );
                    if ( ! empty( $search_term ) ) {
                        $query_args['search'] = '*' . esc_attr( $search_term ) . '*';
                        $query_args['search_columns'] = array( 'display_name', 'user_login', 'nicename' );
                    }
                    $filtering_by_friends = false;
                    if ( $filter_status === 'friends' && function_exists( 'anima_nexus_get_my_connections_ids' ) ) {
                        $filtering_by_friends = true;
                        $friend_ids = anima_nexus_get_my_connections_ids( $current_user_id );
                        $query_args['include'] = ! empty( $friend_ids ) ? $friend_ids : array(0);
                    } else {
                        $query_args['exclude'] = array( $current_user_id );
                    }

                    // EJECUTAR CONSULTA
                    $user_query = new WP_User_Query( $query_args );
                    $avatars = $user_query->get_results();

                    // CALCULOS PARA PAGINACIÓN
                    $total_query_args = $query_args;
                    unset( $total_query_args['number'] );
                    unset( $total_query_args['offset'] );
                    $total_query_args['fields'] = 'ID';
                    $total_query = new WP_User_Query( $total_query_args );
                    $total_users = $total_query->get_total();
                    $total_pages = ceil( $total_users / $nexus_per_page );

                    // LOOP DE RESULTADOS
                    if ( ! empty( $avatars ) ) {
                        foreach ( $avatars as $agent ) {
                            $agent_id = $agent->ID;
                            $status = ( function_exists( 'anima_nexus_get_connection_status' ) ) ? anima_nexus_get_connection_status( $current_user_id, $agent_id ) : 'none';
                            ?>
                            <div class="agent-card cyberpunk-box">
                                <div class="agent-header">
                                    <?php echo get_avatar( $agent_id, 80 ); ?>
                                    <h3 class="agent-title"><?php echo esc_html( $agent->display_name ); ?></h3>
                                </div>
                                <div class="agent-actions">
                                    <?php switch ( $status ) :
                                        case 'none': ?>
                                            <button class="anima-nexus-btn anima-nexus-connect-btn full-width" data-recipient-id="<?php echo esc_attr( $agent_id ); ?>"><span class="dashicons dashicons-networking"></span> Establecer Enlace</button>
                                            <?php break;
                                        case 'pending_sent': ?>
                                            <button class="anima-nexus-btn anima-nexus-pending-btn disabled full-width"><span class="dashicons dashicons-hourglass"></span> Señal en Espera</button>
                                            <?php break;
                                        case 'pending_received': ?>
                                            <span class="nexus-status-label pending-label"><span class="dashicons dashicons-arrow-down-alt"></span> Solicitud recibida</span>
                                            <?php break;
                                        case 'accepted': ?>
                                            <span class="nexus-status-label connected-label"><span class="dashicons dashicons-yes-alt"></span> Enlace Neuronal Activo</span>
                                            <?php break;
                                    endswitch; ?>
                                </div>
                            </div>
                            <?php
                        }
                    } else {
                        echo '<div class="nexus-no-results cyberpunk-box">';
                        if ( $filtering_by_friends && empty( $friend_ids ) ) { echo '<p>Aún no tienes Enlaces Neuronales activos. ¡Explora la Red Global para conectar!</p>'; } 
                        elseif ( ! empty( $search_term ) ) { echo '<p>No se encontraron avatares que coincidan con la señal de búsqueda: "' . esc_html($search_term) . '".</p>'; } 
                        else { echo '<p>No se detectan datos en este sector de la red.</p>'; }
                        echo '</div>';
                    }
                    ?>
                </div> <?php if ( $total_pages > 1 ) : ?>
                    <div class="nexus-pagination-container">
                        <?php
                        $big = 999999999; 
                        $base_url = str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) );
                        echo paginate_links( array(
                            'base'      => $base_url, 'format' => '', 'current' => $nexus_paged, 'total' => $total_pages, 'type' => 'list',
                            'prev_text' => '<span class="dashicons dashicons-arrow-left-alt2"></span> ANTERIOR',
                            'next_text' => 'SIGUIENTE <span class="dashicons dashicons-arrow-right-alt2"></span>',
                        ) );
                        ?>
                    </div>
                <?php endif; ?>
            </section>

        <?php 
        // Fin del bloque ELSE (Usuarios logueados)
        endif; 
        ?>

    </main>
</div>

<style>
    .nexus-container { padding: 40px 0; color: #eee; }
    .cyber-subtitle { color: var(--nexus-neon-blue, #0ff); letter-spacing: 1px; text-transform: uppercase; margin-bottom: 40px; }
    .nexus-section { margin-bottom: 60px; }
    .section-title { border-bottom: 2px solid var(--nexus-neon-blue, #0ff); padding-bottom: 10px; margin-bottom: 30px; display: flex; align-items: center; gap: 10px; }
    .cyberpunk-box { background: rgba(10, 10, 10, 0.8); border: 1px solid #333; padding: 20px; transition: all 0.3s ease; position: relative; }
    .cyberpunk-box:hover { border-color: var(--nexus-neon-blue, #0ff); box-shadow: 0 0 15px rgba(0, 255, 255, 0.2); }
    .nexus-grid-layout { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 25px; margin-bottom: 40px; }
    .agent-card { text-align: center; }
    .agent-card .avatar { border-radius: 50%; border: 3px solid var(--nexus-neon-pink, #f0f); margin-bottom: 15px; }
    .agent-title { margin: 0 0 20px 0; font-size: 1.2rem; word-break: break-word; }
    .full-width { width: 100%; }
    .nexus-status-label { display: flex; align-items: center; justify-content: center; gap: 5px; padding: 10px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; font-size: 0.9em; }
    .connected-label { color: var(--nexus-neon-green, #39ff14); border: 1px solid var(--nexus-neon-green, #39ff14); background: rgba(57, 255, 20, 0.1); }
    .pending-label { color: #ffaa00; border: 1px solid #ffaa00; background: rgba(255, 170, 0, 0.1); }
    /* Estilos Barra de Filtros */
    .nexus-filter-bar { margin-bottom: 30px; border-color: var(--nexus-neon-green, #39ff14); }
    .nexus-filter-form { display: flex; flex-wrap: wrap; gap: 20px; align-items: flex-end; }
    .filter-group { flex: 1; min-width: 250px; }
    .filter-label { display: block; margin-bottom: 8px; font-weight: bold; color: var(--nexus-neon-green, #39ff14); font-family: 'Rajdhani', sans-serif; text-transform: uppercase; }
    .cyber-input { width: 100%; background: rgba(0,0,0,0.5); border: 1px solid var(--nexus-neon-blue, #0ff); color: #fff; padding: 10px 15px; font-family: 'Rajdhani', sans-serif; outline: none; transition: all 0.3s ease; }
    .cyber-input:focus { box-shadow: 0 0 10px var(--nexus-neon-blue, #0ff); background: rgba(0, 255, 255, 0.1); }
    select.cyber-input { cursor: pointer; appearance: none; -webkit-appearance: none; background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%230ff%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E'); background-repeat: no-repeat; background-position: right 10px center; background-size: 12px; padding-right: 30px; }
    .search-input-wrapper { display: flex; gap: 10px; }
    .nexus-search-btn { padding: 10px 20px; height: auto; }
    .nexus-no-results { text-align: center; padding: 40px; font-size: 1.2em; color: #aaa; border-style: dashed; }
    /* Paginación */
    .nexus-pagination-container { display: flex; justify-content: center; margin-top: 40px; }
    .nexus-pagination-container ul.page-numbers { display: flex; list-style: none; padding: 0; margin: 0; gap: 10px; flex-wrap: wrap; }
    .nexus-pagination-container .page-numbers a, .nexus-pagination-container .page-numbers span.dots { display: flex; align-items: center; padding: 10px 15px; background: rgba(10, 10, 10, 0.8); border: 1px solid var(--nexus-neon-blue, #0ff); color: var(--nexus-neon-blue, #0ff); text-decoration: none; font-family: 'Rajdhani', sans-serif; font-weight: bold; text-transform: uppercase; transition: all 0.3s ease; }
    .nexus-pagination-container .page-numbers a:hover { background: rgba(0, 255, 255, 0.2); box-shadow: 0 0 10px var(--nexus-neon-blue, #0ff); }
    .nexus-pagination-container .page-numbers span.current { display: flex; align-items: center; padding: 10px 15px; background: var(--nexus-neon-blue, #0ff); color: #000; border: 1px solid var(--nexus-neon-blue, #0ff); font-weight: bold; box-shadow: 0 0 15px var(--nexus-neon-blue, #0ff); }
    
    /* === NUEVO: Estilos Caja de Acceso Denegado === */
    .nexus-access-denied-wrapper {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 50vh; /* Centrado vertical */
        padding: 40px 20px;
    }
    .access-box {
        text-align: center;
        max-width: 600px;
        width: 100%;
        padding: 50px 30px;
        border-color: var(--nexus-neon-pink, #f0f);
    }
    .access-box:hover {
        box-shadow: 0 0 30px rgba(255, 0, 255, 0.3);
        border-color: var(--nexus-neon-pink, #f0f);
    }
    .nexus-login-btn {
        padding: 15px 30px;
        font-size: 1.2em;
        border-color: var(--nexus-neon-green, #39ff14);
        color: var(--nexus-neon-green, #39ff14);
    }
    .nexus-login-btn:hover {
        background: rgba(57, 255, 20, 0.2);
        box-shadow: 0 0 20px var(--nexus-neon-green, #39ff14);
    }
</style>
<?php get_footer(); ?>