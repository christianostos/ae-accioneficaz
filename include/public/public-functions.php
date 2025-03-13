<?php

require_once __DIR__ . '/../../lib/dompdf/vendor/autoload.php';

// Declarar el uso de las clases necesarias
use Dompdf\Dompdf;
use Dompdf\Options;


//AREA DE CLIENTES
//Shortcode para pagina
function cliente_panel_shortcode() {
    if (!is_user_logged_in()) {
        $login_url = wp_login_url(); // Obtiene la URL de inicio de sesión
        return 'Debes iniciar sesión para ver esta sección.<br><br> <a href="' . esc_url($login_url) . '" target="_blank" style="display: inline-block; padding: 8px 12px; background-color: #0E3950; color: #fff; text-decoration: none; border-radius: 4px;">Iniciar sesión</a>';
    }

    ob_start();
    cliente_panel_html();
    return ob_get_clean();
}
add_shortcode('cliente_panel', 'cliente_panel_shortcode');

//Shortcode para pagina
function cliente_panel_shortcode_panel() {
    if (!is_user_logged_in()) {
        $login_url = wp_login_url(); // Obtiene la URL de inicio de sesión
        return 'Debes iniciar sesión para ver esta sección.<br><br> <a href="' . esc_url($login_url) . '" target="_blank" style="display: inline-block; padding: 8px 12px; background-color: #0E3950; color: #fff; text-decoration: none; border-radius: 4px;">Iniciar sesión</a>';
    }

    ob_start();
    cliente_panel_html_panel();
    return ob_get_clean();
}
add_shortcode('cliente_panel_panel', 'cliente_panel_shortcode_panel');

function cargar_recursos_cliente_panel() {
    wp_enqueue_style(
        'cce-cliente-panel-styles',
        plugin_dir_url(__FILE__) . 'css/styles.css',
        [],
        '1.0.0'
    );

    wp_enqueue_script(
        'cce-cliente-panel-scripts',
        plugin_dir_url(__FILE__) . 'js/script.js',
        ['jquery'],
        '1.0.0',
        true
    );
}
add_action('wp_enqueue_scripts', 'cargar_recursos_cliente_panel');



function cliente_panel_html_panel() {
    $user_id = get_current_user_id();
    ?>
    <div class="grid">
        
        <!-- Solicitudes (SupportCandy) -->
        <div>
            <a href="/panel-de-clientes/solicitudes-y-soporte/">
                <span class="icon">🛟</span> <!-- Icono de solicitudes -->
                <div>
                    <h3>Solicitudes</h3>
                    <p>Pendientes: 
                        <span class="text-blue-600">
                            <?php
                            global $wpdb;
                            // Obtener el customer_id basado en el user_id de WordPress
                            $customer_id = $wpdb->get_var(
                                $wpdb->prepare(
                                    "SELECT id 
                                     FROM {$wpdb->prefix}psmsc_customers 
                                     WHERE user = %d",
                                    $user_id
                                )
                            );
        
                            if ($customer_id) {
                                // Obtener el número de tickets pendientes (estado "Abierto" o "En espera")
                                $tickets_pendientes = $wpdb->get_var(
                                    $wpdb->prepare(
                                        "SELECT COUNT(*) 
                                         FROM {$wpdb->prefix}psmsc_tickets 
                                         WHERE customer = %d 
                                         AND status IN (1, 2, 3)", // Estados: 1 = Abierto, 2 = En espera, 3 = En progreso
                                        $customer_id
                                    )
                                );
                                echo $tickets_pendientes;
                            } else {
                                echo '0';
                            }
                            ?>
                        </span>
                    </p>
                    <p>Total: 
                        <span class="text-gray-700">
                            <?php
                            if ($customer_id) {
                                // Obtener el total de tickets del cliente
                                $total_tickets = $wpdb->get_var(
                                    $wpdb->prepare(
                                        "SELECT COUNT(*) 
                                         FROM {$wpdb->prefix}psmsc_tickets 
                                         WHERE customer = %d",
                                        $customer_id
                                    )
                                );
                                echo $total_tickets;
                            } else {
                                echo '0';
                            }
                            ?>
                        </span>
                    </p>
                </div>
            </a>
        </div>
        
        <!-- Propuestas -->
        <div>
            <a href="/panel-de-clientes/mis-propuestas/">
                <span class="icon">📄</span> <!-- Icono de propuestas -->
                <div>
                    <h3>Propuestas</h3>
                    <p>Pendientes: 
                        <span class="text-blue-600">
                            <?php
                            $pendientes_propuestas = new WP_Query([
                                'post_type' => 'propuesta',
                                'meta_query' => [
                                    ['key' => 'cliente', 'value' => $user_id, 'compare' => '='],
                                    ['key' => 'estado', 'value' => 'Aprobada', 'compare' => '!=']
                                ]
                            ]);
                            echo $pendientes_propuestas->found_posts;
                            wp_reset_postdata();
                            ?>
                        </span>
                    </p>
                    <p>Total: 
                        <span class="text-gray-700">
                            <?php
                            $total_propuestas = new WP_Query([
                                'post_type' => 'propuesta',
                                'meta_query' => [['key' => 'cliente', 'value' => $user_id, 'compare' => '=']]
                            ]);
                            echo $total_propuestas->found_posts;
                            wp_reset_postdata();
                            ?>
                        </span>
                    </p>
                </div>
            </a>
        </div>
    
        <!-- Órdenes de Pago -->
        <div>
            <a href="/panel-de-clientes/mis-ordenes-de-compra/">
                <span class="icon">💳</span> <!-- Icono de órdenes de pago -->
                <div>
                    <h3>Órdenes de Pago</h3>
                    <p>Pendientes: 
                        <span class="text-blue-600">
                            <?php
                            $pendientes_ordenes = new WP_Query([
                                'post_type' => 'orden-pago',
                                'meta_query' => [
                                    ['key' => 'id_cliente', 'value' => $user_id, 'compare' => '='],
                                    ['key' => 'estado_pago', 'value' => ['Pagada', 'Rechazada'], 'compare' => 'NOT IN']
                                ]
                            ]);
                            echo $pendientes_ordenes->found_posts;
                            wp_reset_postdata();
                            ?>
                        </span>
                    </p>
                    <p>Total: 
                        <span class="text-gray-700">
                            <?php
                            $total_ordenes = new WP_Query([
                                'post_type' => 'orden-pago',
                                'meta_query' => [['key' => 'id_cliente', 'value' => $user_id, 'compare' => '=']]
                            ]);
                            echo $total_ordenes->found_posts;
                            wp_reset_postdata();
                            ?>
                        </span>
                    </p>
                </div>
            </a>
        </div>
    
        <!-- Facturas -->
        <div>
            <a href="/panel-de-clientes/mis-facturas-electronicas/">
                <span class="icon">🧾</span> <!-- Icono de facturas -->
                <div>
                    <h3>Facturas</h3>
                    <p>Pendientes: 
                        <span class="text-blue-600">
                            <?php
                            $pendientes_facturas = new WP_Query([
                                'post_type' => 'factura',
                                'meta_query' => [
                                    ['key' => 'cliente_factura', 'value' => $user_id, 'compare' => '='],
                                    ['key' => 'estado_factura', 'value' => 'Emitida', 'compare' => '=']
                                ]
                            ]);
                            echo $pendientes_facturas->found_posts;
                            wp_reset_postdata();
                            ?>
                        </span>
                    </p>
                    <p>Total: 
                        <span class="text-gray-700">
                            <?php
                            $total_facturas = new WP_Query([
                                'post_type' => 'factura',
                                'meta_query' => [['key' => 'cliente_factura', 'value' => $user_id, 'compare' => '=']]
                            ]);
                            echo $total_facturas->found_posts;
                            wp_reset_postdata();
                            ?>
                        </span>
                    </p>
                </div>
            </a>
        </div>
    
        <!-- Servicios -->
        <div>
            <a href="/panel-de-clientes/estado-de-servicios/">
                <span class="icon">📡</span> <!-- Icono de servicios -->
                <div>
                    <h3>Servicios</h3>
                    <p>Total: 
                        <span class="text-gray-700">
                            <?php
                            // Obtener todos los posts del tipo 'servicio'
                            $servicios = get_posts([
                                'post_type' => 'servicio',
                                'posts_per_page' => -1,
                                'post_status' => 'publish',
                                'meta_key' => 'cliente_servicio',
                                'meta_value' => $user_id, // Filtrar por el ID del usuario actual
                            ]);
        
                            $total_servicios = 0; // Inicializar el contador de servicios
        
                            // Recorrer cada post de tipo 'servicio'
                            foreach ($servicios as $servicio) {
                                // Obtener el campo repetidor 'servicios_activos'
                                $campos_repetidor = get_field('servicios_activos', $servicio->ID);
        
                                // Si el campo repetidor existe y es un array, contar sus elementos
                                if ($campos_repetidor && is_array($campos_repetidor)) {
                                    $total_servicios += count($campos_repetidor);
                                }
                            }
        
                            echo $total_servicios; // Mostrar el total de servicios
                            ?>
                        </span>
                    </p>
                </div>
            </a>
        </div>
    </div>
    <?php
}


function cliente_panel_html() {
    ?>
    <div class="elementor-tabs-wrapper">
        <!-- Tabs superiores -->
        <div class="cliente-panel-tabs">
            <button class="cliente-panel-tab active" data-section="solicitudes"><span class="dashicons dashicons-sos"></span> Solicitudes</button>
            <button class="cliente-panel-tab" data-section="propuestas"><span class="dashicons dashicons-analytics"></span> Cotizaciones</button>
            <button class="cliente-panel-tab" data-section="ordenes"><span class="dashicons dashicons-media-document"></span> Órdenes de Compra</button>
            <button class="cliente-panel-tab" data-section="facturas"><span class="dashicons dashicons-media-spreadsheet"></span> Facturas</button>
            <button class="cliente-panel-tab" data-section="servicios"><span class="dashicons dashicons-dashboard"></span> Estado de servicios</button>
        </div>

        <!-- Contenido de los tabs -->
        <div class="cliente-panel-content">
            <div id="propuestas" class="cliente-panel-section active">
                <?php mostrar_propuestas_usuario(); ?>
            </div>
            <div id="ordenes" class="cliente-panel-section">
                <?php mostrar_ordenes_pago_usuario(); ?>
            </div>
            <div id="facturas" class="cliente-panel-section">
                <?php mostrar_facturas_usuario(); ?>
            </div>
            <div id="solicitudes" class="cliente-panel-section">
                <?php mostrar_solicitudes_usuario(); ?>
            </div>
            <div id="servicios" class="cliente-panel-section">
                <?php mostrar_servicios_activos(); ?>
            </div>
        </div>
    </div>

    
    <?php
}


//Shortcode para sección propuestas
function cliente_panel_shortcode_propuestas() {
    if (!is_user_logged_in()) {
        $login_url = wp_login_url(); // Obtiene la URL de inicio de sesión
        return 'Debes iniciar sesión para ver esta sección.<br><br> <a href="' . esc_url($login_url) . '" target="_blank" style="display: inline-block; padding: 8px 12px; background-color: #0E3950; color: #fff; text-decoration: none; border-radius: 4px;">Iniciar sesión</a>';
    }

    ob_start();
    mostrar_propuestas_usuario();
    return ob_get_clean();
}
add_shortcode('cliente_panel_propuestas', 'cliente_panel_shortcode_propuestas');

//Shortcode para sección ordenes de pago
function cliente_panel_shortcode_ordenes() {
    if (!is_user_logged_in()) {
        $login_url = wp_login_url(); // Obtiene la URL de inicio de sesión
        return 'Debes iniciar sesión para ver esta sección.<br><br> <a href="' . esc_url($login_url) . '" target="_blank" style="display: inline-block; padding: 8px 12px; background-color: #0E3950; color: #fff; text-decoration: none; border-radius: 4px;">Iniciar sesión</a>';
    }

    ob_start();
    mostrar_ordenes_pago_usuario();
    return ob_get_clean();
}
add_shortcode('cliente_panel_ordenes', 'cliente_panel_shortcode_ordenes');

//Shortcode para sección ordenes de pago
function cliente_panel_shortcode_facturas() {
    if (!is_user_logged_in()) {
        $login_url = wp_login_url(); // Obtiene la URL de inicio de sesión
        return 'Debes iniciar sesión para ver esta sección.<br><br> <a href="' . esc_url($login_url) . '" target="_blank" style="display: inline-block; padding: 8px 12px; background-color: #0E3950; color: #fff; text-decoration: none; border-radius: 4px;">Iniciar sesión</a>';
    }

    ob_start();
    mostrar_facturas_usuario();
    return ob_get_clean();
}
add_shortcode('cliente_panel_facturas', 'cliente_panel_shortcode_facturas');

//Shortcode para sección ordenes de pago
function cliente_panel_shortcode_servicios() {
    if (!is_user_logged_in()) {
        $login_url = wp_login_url(); // Obtiene la URL de inicio de sesión
        return 'Debes iniciar sesión para ver esta sección.<br><br> <a href="' . esc_url($login_url) . '" target="_blank" style="display: inline-block; padding: 8px 12px; background-color: #0E3950; color: #fff; text-decoration: none; border-radius: 4px;">Iniciar sesión</a>';
    }

    ob_start();
    mostrar_servicios_activos();
    return ob_get_clean();
}
add_shortcode('cliente_panel_servicios', 'cliente_panel_shortcode_servicios');

//Shortcode para sección propuestas
function cliente_panel_shortcode_proyectos() {
    if (!is_user_logged_in()) {
        $login_url = wp_login_url(); // Obtiene la URL de inicio de sesión
        return 'Debes iniciar sesión para ver esta sección.<br><br> <a href="' . esc_url($login_url) . '" target="_blank" style="display: inline-block; padding: 8px 12px; background-color: #0E3950; color: #fff; text-decoration: none; border-radius: 4px;">Iniciar sesión</a>';
    }

    ob_start();
    mostrar_proyectos_usuario();
    return ob_get_clean();
}
add_shortcode('cliente_panel_proyectos', 'cliente_panel_shortcode_proyectos');

function mostrar_servicios_activos() {
    $user_id = get_current_user_id();

    // Configuración de la consulta para obtener servicios del usuario actual usando el campo personalizado "cliente_servicio"
    $paged = get_query_var('paged') ? get_query_var('paged') : 1;
    $args = array(
        'post_type' => 'servicio',
        'meta_query' => array(
            array(
                'key' => 'cliente_servicio',
                'value' => $user_id,
                'compare' => '='
            )
        ),
        'orderby' => 'date', 
        'order' => 'DESC',
        'posts_per_page' => 5, 
        'paged' => $paged
    );

    $servicios_query = new WP_Query($args);

    if ($servicios_query->have_posts()) {
        
        // Imprimir los valores de los subcampos
                echo "<div style='overflow-x: auto;'>";
                echo "<table style='width: 100%; border-collapse: collapse;'>";
                echo "<tr><th>Servicio</th><th>Frecuencia</th><th>Fecha Inicio</th><th>Fecha terminación</th><th>Estado</th><th>Acción</th></tr>";
                
        
        while ($servicios_query->have_posts()) {
            $servicios_query->the_post();
            
            // Obtener el ID del post actual
            $post_id = get_the_ID();

            // Verificar si el repetidor 'servicios_activos' tiene filas
            if (have_rows('servicios_activos', $post_id)) {
                
                while (have_rows('servicios_activos', $post_id)) {
                    the_row();
                    
                    // Obtener cada subcampo del repetidor
                    $item_servicio = get_sub_field('item_servicio');
                    $frecuencia_servicio = get_sub_field('frecuencia_servicio');
                    $fecha_inicio = get_sub_field('fecha_inicio');
                    $fecha_terminacion = get_sub_field('fecha_terminacion');

                    // Convertir la fecha de terminación al formato DateTime
                    $fecha_terminacion_dt = DateTime::createFromFormat('d/m/Y', $fecha_terminacion);
                    $fecha_actual_dt = new DateTime(); // Fecha actual del sistema
                    
                    // Comprobar si la fecha de terminación es válida
                    if ($fecha_terminacion_dt) {
                        // Calcular la diferencia en días entre la fecha actual y la fecha de terminación
                        $dias_restantes = $fecha_actual_dt->diff($fecha_terminacion_dt)->days;
                    
                        // Verificar el estado del servicio según los días restantes
                        if ($fecha_terminacion_dt < $fecha_actual_dt) {
                            $estado_servicio = "Vencido";
                        } elseif ($dias_restantes <= 30) {
                            $estado_servicio = "Próximo a vencer";
                        } else {
                            $estado_servicio = "Activo";
                        }
                    } else {
                        $estado_servicio = "Fecha de terminación no válida";
                    }
                    $orden_pago_servicio = get_sub_field('orden_pago_servicio');
                    
                    if ($estado_servicio == "Activo") {
                        $color = "green";
                    } elseif ($estado_servicio == "Vencido") {
                        $color = "red";
                    } else {
                        $color = "#ECAD20";
                    }
                    
                    echo "<tr>";
                    echo "<td style='text-align:center;'><b>$item_servicio</b></td>";
                    echo "<td style='text-align:center;'>$frecuencia_servicio</td>";
                    echo "<td style='text-align:center;'>$fecha_inicio</td>";
                    echo "<td style='text-align:center;'>$fecha_terminacion</td>";
                    echo "<td style='text-align:center;'><a href='' style='display: inline-block; padding: 8px 12px; background-color: $color; color: #fff; text-decoration: none; border-radius: 4px;'>$estado_servicio</a></td>";
                    
                    if ($estado_servicio != "Activo"){
                        if ($orden_pago_servicio) {
                            echo "<td style='text-align:center;'><a href='" . get_permalink($orden_pago_servicio) . "' target='_blank' style='display: inline-block; padding: 8px 12px; background-color: #0E3950; color: #fff; text-decoration: none; border-radius: 4px;'>Renovar</a></td>";
                        } else {
                            echo "<td style='text-align:center;'> - - </td>";
                        }
                    } else {
                            echo "<td style='text-align:center;'> - - </td>";
                        }

                }
                
                echo "</table>";
                echo "</div>";

            } else {
                echo "<p>No hay servicios activos para este servicio.</p>";
            }
        }
    } else {
        echo "<p>No se encontraron servicios para el usuario actual.</p>";
    }

    wp_reset_postdata();
}


function mostrar_propuestas_usuario() {
    $user_id = get_current_user_id();
    
    // Configuración de la consulta para obtener propuestas del usuario actual usando el campo personalizado "cliente"
    $paged = get_query_var('paged') ? get_query_var('paged') : 1;
    $args = array(
        'post_type' => 'propuesta',
        'meta_query' => array(
            array(
                'key' => 'cliente',
                'value' => $user_id,
                'compare' => '='
            )
        ),
        'orderby' => 'date', 
        'order' => 'DESC',
        'posts_per_page' => 5, 
        'paged' => $paged
    );

    $propuestas_query = new WP_Query($args);
    
    if ($propuestas_query->have_posts()) {
        echo "<div style='overflow-x: auto;'>";
        echo "<table style='width: 100%; border-collapse: collapse;'>";
        echo "<tr><th>Referencia</th><th>Fecha</th><th>Proyecto</th><th>Estado</th><th>Acción</th></tr>";
        
        while ($propuestas_query->have_posts()) {
            $propuestas_query->the_post();
            echo "<tr>";
            echo "<td>" . get_post_meta(get_the_ID(), 'id_propuesta', true) . "</td>";
            echo "<td style='text-align:center;'>" . date_format(date_create(get_post_meta(get_the_ID(), 'fecha_propuesta', true)), 'Y-m-d') . "</td>";
            echo "<td>" . get_post_meta(get_the_ID(), 'proyecto', true) . "</td>";
            
            if (get_post_meta(get_the_ID(), 'estado', true) == "Aprobada") {
                        $color = "green";
                        $accion = "Ver";
                    } else {
                        $color = "#ECAD20";
                        $accion = "Aprobar";
                    }
                    
            echo "<td style='text-align:center;'><a href='' style='display: inline-block; padding: 8px 12px; background-color: $color; color: #fff; text-decoration: none; border-radius: 4px;'>" . get_post_meta(get_the_ID(), 'estado', true) . "</a></td>";
            echo "<td style='text-align:center;'><a href='" . get_permalink() . "' target='_blank' style='display: inline-block; padding: 8px 12px; background-color: #0E3950; color: #fff; text-decoration: none; border-radius: 4px;'>$accion</a></td>";
            
            echo "</tr>";
        }
        
        echo "</table>";
        echo "</div>";

        // Paginación personalizada que conserva la pestaña activa
        echo "<div class='pagination' style='text-align: center; margin-top: 20px;'>";
        echo paginate_links(array(
            'total' => $propuestas_query->max_num_pages,
            'current' => $paged,
            'format' => '?paged=%#%',
            'add_args' => array('activeSection' => 'propuestas'), // Agrega la pestaña activa a la URL de paginación
            'prev_text' => __('&laquo; Anterior'),
            'next_text' => __('Siguiente &raquo;')
        ));
        echo "</div>";
    } else {
        echo "<p>No tienes propuestas actualmente.</p>";
    }

    wp_reset_postdata();
}

function mostrar_ordenes_pago_usuario() {
    $user_id = get_current_user_id();
    $paged = get_query_var('paged') ? get_query_var('paged') : 1;
    $args = array(
        'post_type' => 'orden-pago',
        'meta_query' => array(
            array(
                'key' => 'id_cliente',
                'value' => $user_id,
                'compare' => '='
            )
        ),
        'orderby' => 'date', 
        'order' => 'DESC',
        'posts_per_page' => 5, 
        'paged' => $paged
    );

    $ordenes_query = new WP_Query($args);
    
    if ($ordenes_query->have_posts()) {
        echo "<div style='overflow-x: auto;'>";
        echo "<table style='width: 100%; border-collapse: collapse;'>";
        echo "<tr><th>Referencia</th><th>Proyecto</th><th>Fecha</th><th>Total orden de pago</th><th>Estado</th><th>Acción</th></tr>";
        
        while ($ordenes_query->have_posts()) {
            $ordenes_query->the_post();
            echo "<tr>";
            echo "<td>" . get_the_title() . "</td>";
            echo "<td style='text-align:center;'>" . get_post_meta(get_the_ID(), 'asunto_propuesta', true) . "</td>";
            echo "<td style='text-align:center;'>" . date_format(date_create(get_post_meta(get_the_ID(), 'fecha_pago', true)), 'Y-m-d') . "</td>";
            echo "<td style='text-align:center;'>$" . number_format(get_post_meta(get_the_ID(), 'total_orden_pago', true), 2, '.', ',') . "</td>";
            
            if (get_post_meta(get_the_ID(), 'estado_pago', true) == "Pagada") {
                        $color = "green";
                        $accion = "Ver";
                    } else {
                        $color = "#ECAD20";
                        $accion = "Pagar";
                    }
                    
            echo "<td style='text-align:center;'><a href='' style='display: inline-block; padding: 8px 12px; background-color: $color; color: #fff; text-decoration: none; border-radius: 4px;'>" . get_post_meta(get_the_ID(), 'estado_pago', true) . "</a></td>";
            echo "<td style='text-align:center;'><a href='" . get_permalink() . "' target='_blank' style='display: inline-block; padding: 8px 12px; background-color: #0E3950; color: #fff; text-decoration: none; border-radius: 4px;'>$accion</a></td>";
            
            echo "</tr>";
        }
        
        echo "</table>";
        echo "</div>";

        // Paginación con el parámetro `section`
        echo "<div class='pagination' style='text-align: center; margin-top: 20px;'>";
        echo paginate_links(array(
            'total' => $ordenes_query->max_num_pages,
            'current' => $paged,
            'format' => '?paged=%#%&section=ordenes',
            'prev_text' => __('&laquo; Anterior'),
            'next_text' => __('Siguiente &raquo;')
        ));
        echo "</div>";
    } else {
        echo "<p>No tienes órdenes de pago actualmente.</p>";
    }

    wp_reset_postdata();
}

function mostrar_facturas_usuario() {
    $user_id = get_current_user_id();
    
    // Configuración de la consulta para obtener propuestas del usuario actual usando el campo personalizado "cliente"
    $paged = get_query_var('paged') ? get_query_var('paged') : 1;
    $args = array(
        'post_type' => 'factura',
        'meta_query' => array(
            array(
                'key' => 'cliente_factura',
                'value' => $user_id,
                'compare' => '='
            )
        ),
        'orderby' => 'title', 
        'order' => 'DESC',
        'posts_per_page' => 5, 
        'paged' => $paged
    );

    $facturas_query = new WP_Query($args);
    
    if ($facturas_query->have_posts()) {
        echo "<div style='overflow-x: auto;'>";
        echo "<table style='width: 100%; border-collapse: collapse;'>";
        echo "<tr><th>Referencia</th><th>Fecha</th><th>Total</th><th>Estado</th><th>Acción</th></tr>";
        
        while ($facturas_query->have_posts()) {
            $facturas_query->the_post();
            echo "<tr>";
            echo "<td style='text-align:center;'>" . get_the_title() . "</td>";
            echo "<td style='text-align:center;'>" . date_format(date_create(get_post_meta(get_the_ID(), 'fecha_factura', true)), 'Y-m-d') . "</td>";
            echo "<td style='text-align:center;'>" . number_format(get_post_meta(get_the_ID(), 'total_factura', true), 2, '.', ',') . "</td>";
            
            if (get_post_meta(get_the_ID(), 'estado_factura', true) == "Pagada") {
                        $color = "green";
                        $accion = "Ver";
                    } else {
                        $color = "#ECAD20";
                        $accion = "Pagar";
                    }

            echo "<td style='text-align:center;'><a href='' style='display: inline-block; padding: 8px 12px; background-color: $color; color: #fff; text-decoration: none; border-radius: 4px;'>" . get_post_meta(get_the_ID(), 'estado_factura', true) . "</a></td>";
            echo "<td style='text-align:center;'><a href='" . get_permalink() . "' target='_blank' style='display: inline-block; padding: 8px 12px; background-color: #0E3950; color: #fff; text-decoration: none; border-radius: 4px;'>$accion</a></td>";
            echo "</tr>";
        }
        
        echo "</table>";
        echo "</div>";

        // Paginación personalizada que conserva la pestaña activa
        echo "<div class='pagination' style='text-align: center; margin-top: 20px;'>";
        echo paginate_links(array(
            'total' => $facturas_query->max_num_pages,
            'current' => $paged,
            'format' => '?paged=%#%',
            'add_args' => array('activeSection' => 'propuestas'), // Agrega la pestaña activa a la URL de paginación
            'prev_text' => __('&laquo; Anterior'),
            'next_text' => __('Siguiente &raquo;')
        ));
        echo "</div>";
    } else {
        echo "<p>No tienes Facturas Electrónicas actualmente.</p>";
    }

    wp_reset_postdata();
}

//Funcion ver proyectos usuarios
function mostrar_proyectos_usuario() {
    $user_id = get_current_user_id();
    
    // Configuración de la consulta para obtener propuestas del usuario actual usando el campo personalizado "cliente"
    $paged = get_query_var('paged') ? get_query_var('paged') : 1;
    $args = array(
        'post_type' => 'proyecto',
        'meta_query' => array(
            array(
                'key' => 'cliente_proyecto',
                'value' => $user_id,
                'compare' => '='
            )
        ),
        'orderby' => 'date', 
        'order' => 'DESC',
        'posts_per_page' => 5, 
        'paged' => $paged
    );

    $propuestas_query = new WP_Query($args);
    
    if ($propuestas_query->have_posts()) {
        echo "<div style='overflow-x: auto;'>";
        echo "<table style='width: 100%; border-collapse: collapse;'>";
        echo "<tr><th>Proyecto</th><th>Fecha de inicio</th><th>Fecha de finalización</th><th>Estado</th><th>Acción</th></tr>";
        
        while ($propuestas_query->have_posts()) {
            $propuestas_query->the_post();
            echo "<tr>";
            echo "<td>" . get_the_title(get_the_ID(), 'id_propuesta', true) . "</td>";
            echo "<td style='text-align:center;'>" . date_format(date_create(get_post_meta(get_the_ID(), 'fecha_inicio_proyecto', true)), 'Y-m-d') . "</td>";
            
            $fecha_fin = get_post_meta(get_the_ID(), 'fecha_fin_proyecto', true);
            if (!$fecha_fin){
                $fecha_fin = "-- En curso --";
                echo "<td style='text-align:center;'>$fecha_fin</td>";
            } else {
                echo "<td style='text-align:center;'>" . date_format(date_create($fecha_fin), 'Y-m-d') . "</td>";
            }
            
            
            if (get_post_meta(get_the_ID(), 'estado_proyecto', true) == "7 - Finalizado") {
                        $color = "green";
                        $accion = "Ver";
                    } else {
                        $color = "#ECAD20";
                        $accion = "Gestionar";
                    }
            
            $estado = get_post_meta(get_the_ID(), 'estado_proyecto', true);
                    
            echo "<td style='text-align:center;'><a href='#' style='display: inline-block; padding: 8px 12px; background-color: $color; color: #fff; text-decoration: none; border-radius: 4px;'>$estado</a></td>";
            echo "<td style='text-align:center;'><a href='" . get_permalink() . "' target='_blank' style='display: inline-block; padding: 8px 12px; background-color: #0E3950; color: #fff; text-decoration: none; border-radius: 4px;'>$accion</a></td>";
            
            echo "</tr>";
        }
        
        echo "</table>";
        echo "</div>";

        // Paginación personalizada que conserva la pestaña activa
        echo "<div class='pagination' style='text-align: center; margin-top: 20px;'>";
        echo paginate_links(array(
            'total' => $propuestas_query->max_num_pages,
            'current' => $paged,
            'format' => '?paged=%#%',
            'add_args' => array('activeSection' => 'propuestas'), // Agrega la pestaña activa a la URL de paginación
            'prev_text' => __('&laquo; Anterior'),
            'next_text' => __('Siguiente &raquo;')
        ));
        echo "</div>";
    } else {
        echo "<p>No tienes proyectos actualmente con Acción Eficaz.</p>";
    }

    wp_reset_postdata();
}


function mostrar_solicitudes_usuario() {
    $user_id = get_current_user_id();
    echo "<div class='wp-block-columns is-layout-flex wp-container-core-columns-is-layout-1 wp-block-columns-is-layout-flex'>";
    echo "<div class='wp-block-column is-layout-flow wp-block-column-is-layout-flow' style='flex-basis:75%'>";
    echo "<p>Gestione todas las solicitudes relacionadas con nuestros servicios de Soporte técnico, Desarrollo web y/o Diseño gráfico desde un mismo lugar. Al momento de registrar su solicitud, por favor tenga en cuenta nuestros <a href='https://www.portal.accioneficaz.com/document/acuerdos-de-nivel-de-servicio-de-accion-eficaz/' target='_blank'><b>Acuerdos de Nivel de Servicio</b></a></p>";
    echo "</div>";
    echo "<div class='wp-block-column is-layout-flow wp-block-column-is-layout-flow' style='flex-basis:25%; text-align:center;'>";
    echo "<br><a href='https://www.portal.accioneficaz.com/knowledge-base/' target='_blank' style='padding: 10px 20px; margin: 0 auto; background-color: #ECAD20; color: #fff; border: none; border-radius: 5px; cursor: pointer;'>Ir a Base de conocimiento</a>";
    echo "</div>";
    echo "</div>";
    echo do_shortcode("[supportcandy]");
    // Lógica para obtener y mostrar las facturas del usuario
}

function mostrar_servicios() {
    $user_id = get_current_user_id();
    echo "<div class='wp-block-columns is-layout-flex wp-container-core-columns-is-layout-1 wp-block-columns-is-layout-flex'>";
    echo "<div class='wp-block-column is-layout-flow wp-block-column-is-layout-flow' style='flex-basis:75%'>";
    echo "<p>Gestione todas las solicitudes relacionadas con nuestros servicios de Soporte técnico, Desarrollo web y/o Diseño gráfico desde un mismo lugar. Al momento de registrar su solicitud, por favor tenga en cuenta nuestros <a href='https://www.portal.accioneficaz.com/document/acuerdos-de-nivel-de-servicio-de-accion-eficaz/' target='_blank'><b>Acuerdos de Nivel de Servicio</b></a></p>";
    echo "</div>";
    echo "<div class='wp-block-column is-layout-flow wp-block-column-is-layout-flow' style='flex-basis:25%; text-align:center;'>";
    echo "<br><a href='https://www.portal.accioneficaz.com/knowledge-base/' target='_blank' style='padding: 10px 20px; margin: 0 auto; background-color: #ECAD20; color: #fff; border: none; border-radius: 5px; cursor: pointer;'>Ir a Base de conocimiento</a>";
    echo "</div>";
    echo "</div>";
    // Lógica para obtener y mostrar las facturas del usuario
}

//Función para generar referencia pago
function generar_numero_random_seguro_tres_digitos() {
    // Generar un número aleatorio de tres dígitos
    $numero_random = random_int(100, 999);
    return $numero_random;
}

//VISTA DE TIPOS DE CONTENIDO


// Hook the custom fields display function into the_content
add_filter('the_content', 'custom_fields_display');

function custom_fields_display($content) {
    global $post;

    // Verificar si estamos en el tipo de contenido "propuesta"
    if ($post && $post->post_type === 'propuesta' && is_single() && function_exists('get_field')) {
        
        $post_id = get_the_ID();

        // Obtener los valores de los campos personalizados
        
        $id_propuesta = get_field('id_propuesta', $post_id);
        $fecha_propuesta = get_field('fecha_propuesta', $post_id);
        $proyecto = get_field('proyecto', $post_id);
        $propuesta_content = get_post_field('post_content', $post_id);
        $cliente = get_field('cliente', $post_id);
        $identificacion_cliente = get_field('identificacion_cliente', $post_id);
        $nombre_cliente = get_field('nombre_cliente', $post_id);
        $direccion_cliente = get_field('direccion_cliente', $post_id);
        $telefono_cliente = get_field('telefono_cliente', $post_id);
        $email_cliente = get_field('email_cliente', $post_id);
        $cotizacion = get_field('cotizacion', $post_id);
        $overall_total = get_field('overall_total', $post_id);
        $estimacion_tiempos = get_field('estimacion_tiempos', $post_id);
        $condiciones_pago = get_field('condiciones_pago', $post_id);
        $recomendaciones = get_field('recomendaciones', $post_id);
        $estado = get_field('estado', $post_id);
        $aprobado_por = get_field('aprobado_por', $post_id);
        $fecha_aprobacion = get_field('fecha_aprobacion', $post_id);
        $orden_pago = get_field('orden_pago', $post_id);
        
        $overall_total_number = number_format($overall_total, 2, '.', ',');
        
        if($cotizacion){
            foreach( $cotizacion as $row ) {
                $descripcion_propuesta = $row['descripcion_producto'];
                $precio_propuesta = $row['precio_unitario'];
                $cantidad_propuesta = $row['cantidad'];
                $descuento_propuesta = $row['descuento'];
                $total_propuesta = $row['total_unitario'];
            }
        }

        $content = "
        <div style='color: #333; max-width: 900px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; box-shadow: 0 0 10px rgba(0,0,0,0.1);'>
            <div class='wp-block-columns is-layout-flex wp-container-core-columns-is-layout-2 wp-block-columns-is-layout-flex'>
            <div class='wp-block-column is-layout-flow wp-block-column-is-layout-flow' style='flex-basis:25%'>
            <img src='https://www.portal.accioneficaz.com/wp-content/uploads/2024/11/Logo-nuevo-300x248.png' alt='Logo de tu sitio web' width='150' style='padding-top: 15px; padding-left: 25px;'>
            </div>
            <div class='wp-block-column is-layout-flow wp-block-column-is-layout-flow' style='flex-basis:75%'>
            <h2 style='color: #0E3950; text-align: left;'>Presupuesto Comercial Acción Eficaz</h2>
            <h4 style='color: #FFFFFF; background-color: #0E3950; text-align: left; padding: 5px;'>$id_propuesta</h4>
            </div>
            </div>
            
            <hr style='border: 1px solid #ddd;'>
            
            <div class='wp-block-columns is-layout-flex wp-container-core-columns-is-layout-1 wp-block-columns-is-layout-flex'>
            <div class='wp-block-column is-layout-flow wp-block-column-is-layout-flow'>
            <p><strong>Cliente:</strong> $nombre_cliente</p>
            <p><strong>Identificación:</strong> $identificacion_cliente</p>
            <p><strong>Email:</strong> $email_cliente</p>
            </div>
            <div class='wp-block-column is-layout-flow wp-block-column-is-layout-flow'>
            <p><strong>Dirección:</strong> $direccion_cliente</p>
            <p><strong>Teléfono:</strong> $telefono_cliente</p>
            </div>
            <div class='wp-block-column is-layout-flow wp-block-column-is-layout-flow'>
            <p><strong>Fecha de la propuesta:</strong> $fecha_propuesta</p>
            <p><strong>Estado de la propuesta:</strong> $estado</p>
            </div>
            </div>
            
            <div style='background-color: #0E3950;'>
            <h4 style='color: #FFFFFF; text-align: center;'>Detalles de la propuesta</h4>
            </div>
            <p style='text-align:justify;'>Estamos comprometidos a ofrecer soluciones eficaces en <b>$proyecto</b> que impulsen el crecimiento de su negocio y lograr los objetivos que desea con la implementación del proyecto. Por lo anterior, presentamos a continuación nuestra propuesta comercial. Para nosotros sería un privilegío poder ser un aliado estratégico en el desarrollo de su proyecto y cumplimiento de metas.</p>
            
            <h5>Especificaciones técnicas y comerciales</h5>
            <p style='text-align:justify;'>$propuesta_content</p>
            
            <h4>Relación de productos y/o servicios</h4>
            <div style='overflow-x: auto;'>
            <table style='width: 100%; border-collapse: collapse;'>
                <thead>
                    <tr style='background-color: #f8f8f8;'>
                        <th style='border: 1px solid #ddd; padding: 8px;'><b>Descripción del Producto</b></th>
                        <th style='border: 1px solid #ddd; padding: 8px;'>Precio Unitario</th>
                        <th style='border: 1px solid #ddd; padding: 8px;'>Cantidad</th>
                        <th style='border: 1px solid #ddd; padding: 8px;'>Descuento</th>
                        <th style='border: 1px solid #ddd; padding: 8px;'>Total</th>
                    </tr>
                </thead>
                <tbody>";
        
        if ($cotizacion) {
            foreach ($cotizacion as $row) {
                $descripcion_propuesta = $row['descripcion_producto'];
                $precio_propuesta = number_format($row['precio_unitario'], 2, '.', ',');
                $cantidad_propuesta = $row['cantidad'];
                $descuento_propuesta = $row['descuento'];
                $descuento_porcen = $descuento_propuesta * 100;
                $total_propuesta = number_format($row['total_unitario'], 2, '.', ',');

                $content .= "
                    <tr>
                        <td style='text-align:left; border: 1px solid #ddd; padding: 8px;'>$descripcion_propuesta</td>
                        <td style='text-align:right; border: 1px solid #ddd; padding: 8px;'>$ $precio_propuesta</td>
                        <td style='text-align:center; border: 1px solid #ddd; padding: 8px;'>$cantidad_propuesta</td>
                        <td style='text-align:center; border: 1px solid #ddd; padding: 8px;'>$descuento_porcen%</td>
                        <td style='text-align:right; border: 1px solid #ddd; padding: 8px;'>$ $total_propuesta</td>
                    </tr>";
            }
        }

        $content .= "
                </tbody>
            </table>
            <table style='width: 100%; border-collapse: collapse;'>
                <thead>
                    <tr style='background-color: #f8f8f8;'>
                        <th style='text-align:right; border: 1px solid #ddd; padding: 8px;'><b>Total de la inversión: $$overall_total_number</b></th>
                </thead>
            </table>
            </div>
            <hr style='border: 1px solid #ddd;'>

            <div class='wp-block-columns is-layout-flex wp-container-core-columns-is-layout-1 wp-block-columns-is-layout-flex'>
            <div class='wp-block-column is-layout-flow wp-block-column-is-layout-flow'>
            <h4>Estimación de tiempos</h4>
            <p>Para el desarrollo y entrega del proyecto, estimamos una duración de: <b>$estimacion_tiempos.</b></p>
            </div>
            <div class='wp-block-column is-layout-flow wp-block-column-is-layout-flow'>
            <h4>Condiciones de Pago</h4>
            <p>Para el inicio del proyecto, se requiere las siguientes condiciones de pago: <b>$condiciones_pago.</b></p>
            </div>
            </div>
            
            <div style='background-color: #0E3950;'>
            <h4 style='color: #FFFFFF; text-align: center;'>Requisitos y recomendaciones</h4>
            </div>
            <p style='text-align:justify;'>$recomendaciones</p>
            
            <h5>Soporte y Mesa de ayuda</h5>
            <p style='text-align:justify;'>Acción Eficaz cuenta con un área y sistema de soporte y mesa de ayuda, en la que usted podrá gestionar todas sus solicitudes y requerimientos de manera ágil y rápida. Por ello contamos con unos tiempos óptimos de respuesta que podrá conocer en nuestro <a href='https://www.portal.accioneficaz.com/document/acuerdos-de-nivel-de-servicio-de-accion-eficaz' target='_blank'>Acuerdo de Nivel de Servicio</a>; así miso podrá registrar y realizar seguimiento a sus solicitudes en nuestro <a href='https://www.portal.accioneficaz.com' target='_blank'>Portal de Clientes</a>, el cual es el único medio de recepción de solicitudes de soporte. </p>
            
            <div style='background-color: #0E3950;'>
            <h4 style='color: #FFFFFF; text-align: center;'>Aprobación de la Propuesta</h4>
            </div>";
            
            if ($estado != "Aprobada") {
                
                // Generar dos números aleatorios para la pregunta de verificación
                $num1 = rand(1, 20);
                $num2 = rand(1, 30);
                $suma_correcta = $num1 + $num2;
                
            $content .= "
            
            <form id='approvalForm' style='margin: 0 auto;'>
                <p style='text-align: justify;'>
                    Embárquese en un viaje de éxito junto a nosotros. Estamos comprometidos a brindarle servicios de calidad superior que superarán sus expectativas y lo ayudarán a alcanzar sus metas.
                    <br><br>Para aceptar y aprobar la propuesta, por favor complete los campos que se presentan a continuación y haga clic en el botón aprobar:
                </p>
        
                <div style='display: flex; flex-direction: column; margin-bottom: 10px;'>
                    <label for='nombre_aprobador' style='font-weight: bold;'>Nombre del aprobador:</label>
                    <input type='text' id='nombre_aprobador' name='nombre_aprobador' required style='width: 100%; padding: 8px;' placeholder='Escriba el nombre de quien está aprobando'>
                </div>
        
                <div style='display: flex; flex-direction: column; margin-bottom: 10px;'>
                    <label style='font-weight: bold;'>Verificación humana, ¿Cuánto es $num1 + $num2?</label>
                    <input type='number' id='verificacion_humana' name='verificacion_humana' required style='width: 100%; padding: 8px;' placeholder='Escriba la respuesta'>
                    <input type='hidden' id='suma_correcta' value='$suma_correcta'>
                </div>
        
                <div style='text-align: center;'>
                    <input type='submit' value='Aprobar Propuesta' style='padding: 10px 20px; background-color: green; color: #fff; border: none; border-radius: 5px; cursor: pointer;'>
                </div>
            </form>
        
            <div id='approvalMessage' style='margin-top: 20px; text-align: center;'></div>
        
            ";
            } else {
            
            $content .= "
            
                <h4>La propuesta ya ha sido aprobada</h4>
                <p>Gracias por permitirnos ser un aliado estratégico para su proyecto; responderemos a su confianza con un alto grado de responsabilidad y compromiso para el cumplimiento de las metas y tiempos establecidos.</p>
                <div class='wp-block-columns is-layout-flex wp-container-core-columns-is-layout-1 wp-block-columns-is-layout-flex'>
                <div class='wp-block-column is-layout-flow wp-block-column-is-layout-flow'>
                <p><b>Aprobada por</b>: $aprobado_por.</p>
                </div>
                <div class='wp-block-column is-layout-flow wp-block-column-is-layout-flow'>
                <p><b>Fecha de aprobación</b>: $fecha_aprobacion.</p>
                </div>
                </div>
                <p style='background-color: #f2efe6; padding: 5px;'>
                    <span class='dashicons dashicons-media-spreadsheet'></span>
                    <b>Orden de pago</b>: Para ver la orden de pago ingresa <a href='$orden_pago' target='_blank'><b>aquí</b></a>, o puedes ingresar al <a href='https://www.portal.accioneficaz.com/panel-de-clientes' target='_blank'><b>Área comercial de clientes</b></a>.
                </p>
                
                ";
            }
            $content .= "
            </p>
            <hr style='border: 1px solid #ddd;'>
            <p style='text-align:center; padding: 5px;'>
                    RUT: 1.073.232.264 - Correo: info@accioneficaz.com - WhatsApp: 57 318 601 3557.
            </p>
            
            <div style='text-align: center; margin-top: 20px;'>
                <form method='post' action='" . admin_url('admin-ajax.php') . "'>
                    <input type='hidden' name='action' value='generar_pdf'>
                    <input type='hidden' name='post_id' value='$post_id'>
                    <button type='submit' style='padding: 10px 20px; background-color: #EDEDED; color: #0E3950; border: none; border-radius: 5px; cursor: pointer;'>
                        Descargar PDF
                    </button>
                </form>
            </div>
            
        </div>";
        
        return $content;
        
    } else if ($post && $post->post_type === 'orden-pago' && is_single() && function_exists('get_field')) {
        
        $post_id = get_the_ID();

        // Obtener los valores de los campos personalizados
        
        $fecha_pago = get_field('fecha_pago', $post_id);
        $nombre_cliente_pago = get_field('nombre_cliente_pago', $post_id);
        $nit_cliente_pago = get_field('nit_cliente_pago', $post_id);
        $correo_cliente_pago = get_field('correo_cliente_pago', $post_id);
        $numero_propuesta_pago = get_field('numero_propuesta_pago', $post_id);
        $telefono_cliente_pago = get_field('telefono_cliente_pago', $post_id);
        $valor_total_propuesta = get_field('valor_total_propuesta', $post_id);
        $condicion_pago_pro = get_field('condicion_pago_pro', $post_id);
        $total_orden_pago = get_field('total_orden_pago', $post_id);
        $id_cliente = get_field('id_cliente', $post_id);
        $estado_pago = get_field('estado_pago', $post_id);
        $enlace_propuesta = get_field('enlace_propuesta', $post_id);
        $asunto_propuesta = get_field('asunto_propuesta', $post_id);
        $moneda = "COP";
        $url_respuesta = "https://www.portal.accioneficaz.com/knowledge-base/respuestawompi";
        $numero_referencia = generar_numero_random_seguro_tres_digitos();
        
        
        $valor_total_pro = number_format($valor_total_propuesta, 2, '.', ',');
        $total_orden_pago_f = number_format($total_orden_pago, 2, '.', ',');
        $condicion_pago_pro_f = $condicion_pago_pro * 100;
        
        $fecha_parts = explode('/', $fecha_pago);
        if (count($fecha_parts) == 3) {
            $day = $fecha_parts[0];
            $month = $fecha_parts[1];
            $year = $fecha_parts[2];
        }
        
        //Datos Wompi
        
        $codigo_inte = 'prod_integrity_GNeDHZX89aM9mtl3Gzw71NuFBH4RBRNr';
        $referencia_pago = $year . $month . $numero_referencia . 'AE-' . $post_id;
        $monto_pago = $total_orden_pago * 100;
        $cadena = $referencia_pago . $monto_pago . $moneda . $codigo_inte;
        $cadena_inte = generar_hash_sha256($cadena);
        $public_key = 'pub_prod_CAB7mJwhkD6H0uy4rW0K5lEprbfU2SeE';
        

        $content = "
        <div style='color: #333; max-width: 900px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; box-shadow: 0 0 10px rgba(0,0,0,0.1);'>
            <div class='wp-block-columns is-layout-flex wp-container-core-columns-is-layout-2 wp-block-columns-is-layout-flex'>
            <div class='wp-block-column is-layout-flow wp-block-column-is-layout-flow' style='flex-basis:25%'>
            <img src='https://www.portal.accioneficaz.com/wp-content/uploads/2024/11/Logo-nuevo-300x248.png' alt='Logo de tu sitio web' width='150' style='padding-top: 15px; padding-left: 25px;'>
            </div>
            <div class='wp-block-column is-layout-flow wp-block-column-is-layout-flow' style='flex-basis:75%'>
            <h2 style='color: #0E3950; text-align: left;'>Orden de compra y pago</h2>
            <h4 style='color: #FFFFFF; background-color: #0E3950; text-align: left; padding: 5px;'>Orden de Pago - $numero_propuesta_pago</h4>
            </div>
            </div>
            
            <hr style='border: 1px solid #ddd;'>
            
            <div class='wp-block-columns is-layout-flex wp-container-core-columns-is-layout-1 wp-block-columns-is-layout-flex'>
            <div class='wp-block-column is-layout-flow wp-block-column-is-layout-flow'>
            <p><strong>Cliente:</strong> $nombre_cliente_pago</p>
            <p><strong>Identificación:</strong> $nit_cliente_pago</p>
            </div>
            <div class='wp-block-column is-layout-flow wp-block-column-is-layout-flow'>
            <p><strong>Email:</strong> $correo_cliente_pago</p>
            <p><strong>Teléfono:</strong> $telefono_cliente_pago</p>
            </div>
            <div class='wp-block-column is-layout-flow wp-block-column-is-layout-flow'>
            <p><strong>Fecha de la orden:</strong> $fecha_pago</p>
            <p><strong>Estado de la orden:</strong> $estado_pago</p>
            </div>
            </div>
            
            <div style='background-color: #0E3950;'>
            <h4 style='color: #FFFFFF; text-align: center;'>Detalles de la propuesta aprobada y orden de pago</h4>
            </div>
            <p style='text-align:justify;'>Hemos recido la aprobación de la propuesta comercial emitida por nosotros. Para continuar con el proceso e iniciar la ejecución por nuestra parte, le agradecemos realizar el pago del anticipo correspondiente establecido en la propuesta, los datos se muestran a continuación:</p>
            
            <div class='wp-block-columns is-layout-flex wp-container-core-columns-is-layout-1 wp-block-columns-is-layout-flex'>
            <div class='wp-block-column is-layout-flow wp-block-column-is-layout-flow'>
            <p><strong>Número de propuesta:</strong> $numero_propuesta_pago</p>
            <p><strong>Enlace de la propuesta:</strong> <a href='$enlace_propuesta' target='_blank'>$enlace_propuesta</a></p>
            <p><strong>Nombre del proyecto:</strong> $asunto_propuesta</p>
            </div>
            <div class='wp-block-column is-layout-flow wp-block-column-is-layout-flow'>
            <p><strong>Valor total de la propuesta:</strong> $$valor_total_pro</p>
            <p><strong>Anticipo acordado en la propuesta:</strong> $condicion_pago_pro_f%</p>
            <p><strong>Valor total a pagar anticipo:</strong> $$total_orden_pago_f</p>
            <p><strong>Moneda:</strong> $moneda</p>
            </div>
            </div>
            <hr style='border: 1px solid #ddd;'>
            <p style='text-align:justify;'>Puede hacer uso de nuestra pasarela de pagos Wompi - Bancolombia, la cual es rápida, segura y cuenta con todos los medios de pago disponible. Para continuar con el pago del anticipo, por favor haga clic en el botón de pagar.</p>

            ";
            
            if ($estado_pago !== 'Pagada') {
            
            $content .= "
            
            <form>
              <script
                src='https://checkout.wompi.co/widget.js'
                data-render='button'
                data-public-key='$public_key'
                data-currency='$moneda'
                data-amount-in-cents='$monto_pago'
                data-reference='$referencia_pago'
                data-signature:integrity='$cadena_inte'
                data-redirect-url='$url_respuesta'
              ></script>
            </form>
            
            ";
            
            } else {
                
                $content .= "
                <h4 style='text-align:center; font-weight:bold; color:green;'>
                    Anticipo pagado - Gracias por su pago
                </h4>
                ";
            }
            
            $content .= "
            <hr style='border: 1px solid #ddd;'>
            <p style='background-color: #f2efe6; text-align:justify; padding: 5px;'>
                    <span class='dashicons dashicons-info-outline'></span>
                    <b>¡Importante!</b>: Una vez realice el pago, por favor enviar el respectivo comprobante al correo <a href='mailto:info@accioneficaz.com'><b>info@accioneficaz.com</b></a>, relacionando el numero de la <b>$numero_propuesta_pago</b>; posterior a la validación del pago, se enviará la correspondiente factura electrónica, la cual también podrá visualizar en el <a href='https://www.portal.accioneficaz.com/panel-de-clientes' target='_blank'><b>Área comercial de clientes</b></a> en la pestaña Mis Facturas.
                </p>
            <hr style='border: 1px solid #ddd;'>
            <p style='text-align:center; padding: 5px;'>
                    RUT: 1.073.232.264 - Correo: info@accioneficaz.com - WhatsApp: 57 318 601 3557.
                </p>
            </div>
            
            ";
        return $content;
    } else if ($post && $post->post_type === 'factura' && is_single() && function_exists('get_field')) {
        
        $post_id = get_the_ID();
        $user_id = get_current_user_id();
        $cliente_factura = get_field('cliente_factura', $post_id); // ID del cliente propietario de la factura
    
        // Verifica si el usuario es administrador o si es propietario de la factura
        if (!current_user_can('administrator') && $user_id != $cliente_factura) {
            return "<div style='color: red; text-align: center; padding: 20px; font-size: 18px;'>
                        <h4>Acceso negado al documento</h4>
                        <p><strong>Usted no es el propietario del documento y/o no tiene permisos para acceder al mismo.</strong></p>
                        <a href='/panel-de-clientes' target='_blank' style='display: inline-block; padding: 8px 12px; background-color: #0E3950; color: #fff; text-decoration: none; border-radius: 4px;'>Ir a Panel de Clientes</a>
                    </div>";
        }

        // Obtener los valores de los campos personalizados
        
        $fecha_factura = get_field('fecha_factura', $post_id);
        $nombre_cliente = get_field('nombre_cliente', $post_id);
        $nit_cliente = get_field('nit_cliente', $post_id);
        $cufe_factura = get_field('cufe_factura', $post_id);
        $total_factura = get_field('total_factura', $post_id);
        $anticipos_factura = get_field('anticipos_factura', $post_id);
        $total_pagar_factura = get_field('total_pagar_factura', $post_id);
        $estado_factura = get_field('estado_factura', $post_id);
        $enlace_factura = get_field('enlace_factura', $post_id);
        $referencia_factura = get_the_title( $post_id );
        $moneda = "COP";
        $url_respuesta = "https://www.portal.accioneficaz.com/knowledge-base/respuestawompi";
        $numero_referencia = generar_numero_random_seguro_tres_digitos();
        
        
        $valor_total_fac = number_format($total_factura, 2, '.', ',');
        $valor_anticipos_fac = number_format($anticipos_factura, 2, '.', ',');
        $total_pagar_factura_n = number_format($total_pagar_factura, 2, '.', ',');
        
        $fecha_parts = explode('/', $fecha_factura);
        if (count($fecha_parts) == 3) {
            $day = $fecha_parts[0];
            $month = $fecha_parts[1];
            $year = $fecha_parts[2];
        }
        
        //Datos Wompi
        
        $codigo_inte = 'prod_integrity_GNeDHZX89aM9mtl3Gzw71NuFBH4RBRNr';
        $referencia_pago = $year . $month . $numero_referencia . 'AE-' . $post_id;
        $monto_pago = $total_pagar_factura * 100;
        $cadena = $referencia_pago . $monto_pago . $moneda . $codigo_inte;
        $cadena_inte = generar_hash_sha256($cadena);
        $public_key = 'pub_prod_CAB7mJwhkD6H0uy4rW0K5lEprbfU2SeE';
        

        $content = "
        <div style='color: #333; max-width: 900px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; box-shadow: 0 0 10px rgba(0,0,0,0.1);'>
            <div class='wp-block-columns is-layout-flex wp-container-core-columns-is-layout-2 wp-block-columns-is-layout-flex'>
            <div class='wp-block-column is-layout-flow wp-block-column-is-layout-flow' style='flex-basis:25%'>
            <img src='https://www.portal.accioneficaz.com/wp-content/uploads/2024/11/Logo-nuevo-300x248.png' alt='Logo de tu sitio web' width='150' style='padding-top: 15px; padding-left: 25px;'>
            </div>
            <div class='wp-block-column is-layout-flow wp-block-column-is-layout-flow' style='flex-basis:75%'>
            <h2 style='color: #0E3950; text-align: left;'>Resumen de Facturación Electrónica</h2>
            <h4 style='color: #FFFFFF; background-color: #0E3950; text-align: left; padding: 5px;'>Factura Electrónica $referencia_factura</h4>
            </div>
            </div>
            
            <hr style='border: 1px solid #ddd;'>
            
            <div class='wp-block-columns is-layout-flex wp-container-core-columns-is-layout-1 wp-block-columns-is-layout-flex'>
            <div class='wp-block-column is-layout-flow wp-block-column-is-layout-flow'>
            <p><strong>Cliente:</strong> $nombre_cliente</p>
            <p><strong>Identificación:</strong> $nit_cliente</p>
            </div>
            <div class='wp-block-column is-layout-flow wp-block-column-is-layout-flow'>
            <p><strong>Fecha de la Factura:</strong> $fecha_factura</p>
            <p><strong>Estado de la Factura:</strong> $estado_factura</p>
            </div>
            </div>
            
            <div style='background-color: #0E3950;'>
            <h4 style='color: #FFFFFF; text-align: center;'>Detalles de la factura electrónica</h4>
            </div>
            <p style='text-align:justify;'>Estimado cliente, esta representación es un resumen de la factura electrónica emitida por nosotros, es decir, esta no es la representación gráfica oficial de la factura electrónica emitida por nuestro sistema y enviada a la DIAN, ésta es un resumen con algunos datos para su control y gestión. Si desea conocer la factura real emitida a la DIAN, por favor haga clic sobre el botón '<b>Ver  factura Electrónica</b>'. El resumen de su factura se presentan a continuación:</p>
            
            <div class='wp-block-columns is-layout-flex wp-container-core-columns-is-layout-1 wp-block-columns-is-layout-flex'>
            <div class='wp-block-column is-layout-flow wp-block-column-is-layout-flow'>
            <p><strong>Número de Factura Electrónica:</strong> $referencia_factura</p>
            <p><strong>CUFE de Factura Electrónica:</strong> $cufe_factura</p>
            <p><strong>Enlace de la Factura Electrónica:</strong></p>
            <a href='$enlace_factura' target='_blank' style='display: inline-block; padding: 8px 12px; background-color: #0E3950; color: #fff; text-decoration: none; border-radius: 4px;'>Ver factura electrónica</a>
            </div>
            <div class='wp-block-column is-layout-flow wp-block-column-is-layout-flow'>
            <p><strong>Valor total de Factura:</strong> $$valor_total_fac</p>
            <p><strong>Valor anticipos de Factura:</strong> $$valor_anticipos_fac</p>
            <h5>Valor a pagar de la Factura Electrónica:<strong> $$total_pagar_factura_n</strong></h5>
            <p><strong>Moneda:</strong> $moneda</p>
            </div>
            </div>
            <hr style='border: 1px solid #ddd;'>
            <p style='text-align:justify;'>Puede hacer uso de nuestra pasarela de pagos Wompi - Bancolombia, la cual es rápida, segura y cuenta con todos los medios de pago disponible. Para continuar con el pago de la factura electrónica, por favor haga clic en el botón de pagar.</p>

            ";
            
            if ($estado_factura !== 'Pagada') {
            
            $content .= "
            <form>
              <script
                src='https://checkout.wompi.co/widget.js'
                data-render='button'
                data-public-key='$public_key'
                data-currency='$moneda'
                data-amount-in-cents='$monto_pago'
                data-reference='$referencia_pago'
                data-signature:integrity='$cadena_inte'
                data-redirect-url='$url_respuesta'
              ></script>
            </form>
            
            ";
            
            } else {
                
                $content .= "
                <h4 style='text-align:center; font-weight:bold; color:green;'>
                    Factura pagada - Gracias por su pago
                </h4>
                ";
            }
            
            $content .= " 
            <hr style='border: 1px solid #ddd;'>
            <p style='background-color: #f2efe6; text-align:justify; padding: 5px;'>
                    <span class='dashicons dashicons-info-outline'></span>
                    <b>¡Importante!</b>: Una vez realice el pago, por favor enviar el respectivo comprobante al correo <a href='mailto:info@accioneficaz.com'><b>info@accioneficaz.com</b></a>, relacionando el numero de la Factura Electrónica <b>$referencia_factura</b>.
                </p>
            <hr style='border: 1px solid #ddd;'>
            <p style='text-align:center; padding: 5px;'>
                    RUT: 1.073.232.264 - Correo: info@accioneficaz.com - WhatsApp: 57 318 601 3557.
                </p>
            </div>
            
            ";
        return $content;
    } else if ($post && $post->post_type === 'notificacion' && is_single() && function_exists('get_field')) {
        
        $post_id = get_the_ID();
        $user_id = get_current_user_id();
        $destino_notificacion = get_field('destino_notificacion', $post_id); 
        $usuario_notificacion = get_field('usuario_notificacion', $post_id);
        $estado_notificacion = get_field('estado_notificacion', $post_id);
        $asunto_not = get_the_title();
        $contenido_not = get_the_content();
        $fecha_publicacion = get_the_date();
        $estado = get_post_meta(get_the_ID(), 'estado_notificacion', true);

        $content = "
            <span class='dashicons dashicons-megaphone' style='color:#0E3950;'></span>
                    <b style='color:#0E3950; font-size:24px;'>$asunto_not</b>
            <hr>
            <span style='display: block; text-align:right; font-size:12px; background-color:#f2efe6; padding:5px;'><b>Fecha de notificación:</b> $fecha_publicacion</span>
            <p>$contenido_not</p>
            <br>
            <hr>
            <a href='https://www.portal.accioneficaz.com/todas-las-notificaciones' style='display: inline-block; padding: 8px 12px; background-color: #0E3950; color: #fff; text-decoration: none; border-radius: 4px;'>Regresar a todas las notificaciones</a>
        ";

        return $content;
    } else if ($post && $post->post_type === 'proyecto' && is_single() && function_exists('get_field')) {
        
        $post_id = get_the_ID();

        // Obtener los valores de los campos personalizados
        $clientea = get_field('cliente_proyecto', $post_id);
        $fecha_inicio = get_field('fecha_inicio_proyecto', $post_id);
        $estado_proyecto = get_field('estado_proyecto', $post_id);
        $observaciones_proyecto = get_field('observaciones_proyecto', $post_id);
        $contenido = get_the_content();
        

        // Iniciar el contenido personalizado
        $custom_content = '<div class="proyecto-detalles">';
        $custom_content .= '<h2>' . get_the_title() . '</h2>';
        
        // Mostrar los círculos de estado
        $custom_content .= '<div class="estado-circulos">';
        for ($i = 1; $i <= 7; $i++) {
            $class = ($i <= $estado_proyecto) ? 'azul' : 'gris';
            $custom_content .= '<span class="circulo ' . $class . '">' . $i . '</span>';
        }
        $custom_content .= '</div>';
        
        // Mostrar el estado actual del proyecto
        $custom_content .= '<div class="proyecto-estado">' . get_estado_proyecto_texto($estado_proyecto) . '</div>';
        $custom_content .= '</div>';
        $custom_content .= '<br>';

        // Mostrar el cliente, la fecha de inicio y la descripción del post
        $custom_content .= '<div class="proyecto-campos">';
        $custom_content .= '<div class="contenedor">';
        $custom_content .= '<div class="columna"><strong>Cliente:</strong> ' . get_the_author_meta('display_name', $clientea) . '</div>';
        $custom_content .= '<div class="columna"><strong>Fecha de inicio:</strong> ' . $fecha_inicio . '</div>';
        $custom_content .= '<div><strong>Descripción:</strong><br> ' . $contenido . '</div>';
        $custom_content .= '</div>';
        $custom_content .= '</div>';
        $custom_content .= '<br>';

        // Mostrar el historial de observaciones
        $custom_content .= '<div class="proyecto-campos">';
        if ($observaciones_proyecto) {
            $custom_content .= '<h3>Historial de Observaciones</h3>';
            $custom_content .= '<ul class="observaciones-lista">';
            foreach ($observaciones_proyecto as $observacion) {
                $custom_content .= '<li>' . $observacion['retroalimentacion_proyecto'] . '</li>';
            }
            $custom_content .= '</ul>';
        }

        // Formulario para agregar comentarios
        $custom_content .= '<h3>Agregar Comentario</h3>';
        $custom_content .= '<form method="post" action="" class="comentario-form">';
        $custom_content .= '<textarea name="nuevo_comentario" placeholder="Escribe tu comentario aquí..." required></textarea>';
        $custom_content .= '<input type="hidden" name="proyecto_id" value="' . $post_id . '">';
        $custom_content .= '<input type="submit" name="agregar_comentario" value="Agregar Comentario">';
        $custom_content .= '</form>';

        // Botón para cambiar el estado del proyecto
        if ($estado_proyecto < 8) {
            $custom_content .= '<form method="post" action="" class="cambiar-estado-form">';
            $custom_content .= '<input type="hidden" name="proyecto_id" value="' . $post_id . '">';
            $custom_content .= '<input type="hidden" name="nuevo_estado" value="' . ($estado_proyecto + 1) . '">';
            $custom_content .= '<input type="submit" name="cambiar_estado" value="Cambiar al siguiente estado">';
            $custom_content .= '</form>';
        }

        // Finalizar el contenido personalizado
        $custom_content .= '</div>';
        $custom_content .= '</div>';

        // Agregar el contenido personalizado al contenido original
        $content = $custom_content;
    
        return $content;
        
        } else {
            
        return $content;
    }
}




// Función para obtener el texto del estado del proyecto
function get_estado_proyecto_texto($estado) {
    $estados = array(
        1 => 'Iniciado',
        2 => 'Pendiente de información',
        3 => 'En proceso',
        4 => 'En revisión del cliente',
        5 => 'Aplicando cambios sugeridos',
        6 => 'Entregado con cambios sugeridos',
        7 => 'Finalizado',
        8 => 'Cancelado'
    );
    return isset($estados[$estado]) ? $estados[$estado] : 'Desconocido';
}

// Manejar el cambio de estado del proyecto y agregar comentarios
function manejar_comentarios_y_estado() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['proyecto_id'])) {
        $proyecto_id = intval($_POST['proyecto_id']);

        // Agregar un nuevo comentario
        if (isset($_POST['agregar_comentario']) && !empty($_POST['nuevo_comentario'])) {
            $nuevo_comentario = sanitize_text_field($_POST['nuevo_comentario']);
            $observacion = array(
                'retroalimentacion_proyecto' => $nuevo_comentario
            );
            add_row('observaciones_proyecto', $observacion, $proyecto_id);
        }

        // Cambiar el estado del proyecto
        if (isset($_POST['cambiar_estado']) && isset($_POST['nuevo_estado'])) {
            $nuevo_estado = intval($_POST['nuevo_estado']);
            update_field('estado_proyecto', $nuevo_estado, $proyecto_id);

            // Agregar una observación automática al cambiar el estado
            $observacion = array(
                'retroalimentacion_proyecto' => 'El estado del proyecto ha cambiado a ' . get_estado_proyecto_texto($nuevo_estado)
            );
            add_row('observaciones_proyecto', $observacion, $proyecto_id);
        }
    }
}
add_action('init', 'manejar_comentarios_y_estado');





function generar_hash_sha256($cadena) {
    return hash("sha256", $cadena);
}

//Construcción del PDF de propuestas
add_action('wp_ajax_generar_pdf', 'generar_pdf_propuesta');
add_action('wp_ajax_nopriv_generar_pdf', 'generar_pdf_propuesta');
function generar_pdf_propuesta() {
    if (isset($_POST['post_id'])) {
        $post_id = intval($_POST['post_id']);
        
        //reconstruir el documento
        $id_propuesta = get_field('id_propuesta', $post_id);
        $fecha_propuesta = get_field('fecha_propuesta', $post_id);
        $proyecto = get_field('proyecto', $post_id);
        $propuesta_content = get_post_field('post_content', $post_id);
        $cliente = get_field('cliente', $post_id);
        $identificacion_cliente = get_field('identificacion_cliente', $post_id);
        $nombre_cliente = get_field('nombre_cliente', $post_id);
        $direccion_cliente = get_field('direccion_cliente', $post_id);
        $telefono_cliente = get_field('telefono_cliente', $post_id);
        $email_cliente = get_field('email_cliente', $post_id);
        $cotizacion = get_field('cotizacion', $post_id);
        $overall_total = get_field('overall_total', $post_id);
        $estimacion_tiempos = get_field('estimacion_tiempos', $post_id);
        $condiciones_pago = get_field('condiciones_pago', $post_id);
        $recomendaciones = get_field('recomendaciones', $post_id);
        $estado = get_field('estado', $post_id);
        $aprobado_por = get_field('aprobado_por', $post_id);
        $fecha_aprobacion = get_field('fecha_aprobacion', $post_id);
        $orden_pago = get_field('orden_pago', $post_id);
        $enlace_propuesta = get_permalink($post_id);
        
        $overall_total_number = number_format($overall_total, 2, '.', ',');
        
        if($cotizacion){
            foreach( $cotizacion as $row ) {
                $descripcion_propuesta = $row['descripcion_producto'];
                $precio_propuesta = $row['precio_unitario'];
                $cantidad_propuesta = $row['cantidad'];
                $descuento_propuesta = $row['descuento'];
                $total_propuesta = $row['total_unitario'];
            }
        }

        $content = "
        
            <table style='width: 100%; border-collapse: collapse;'>
                <tr>
                  <td>
                    <img src='https://www.portal.accioneficaz.com/wp-content/uploads/2024/11/Logo-nuevo-300x248.png' alt='Logo de tu sitio web' width='150' style='padding-top: 15px; padding-left: 25px;'>
                  </td>
                  <td colspan='2'>
                    <h2>Presupuesto Comercial Acción Eficaz</h2>
                    <div style='background-color: #0E3950;'>
                    <h4 style='color: #FFFFFF; text-align: left; padding: 10px;'>$id_propuesta</h4>
                    </div>
                  </td>
                </tr>
            </table>
            <hr style='border: 1px solid #ddd;'>
            <table style='width: 100%; border-collapse: collapse;'>
                <tr>
                  <td>
                    <p><strong>Cliente:</strong> $nombre_cliente</p>
                    <p><strong>Dirección:</strong> $direccion_cliente</p>
                    <p><strong>Teléfono:</strong> $telefono_cliente</p>
                  </td>
                  <td>
                    <p><strong>Identificación:</strong> $identificacion_cliente</p>
                    <p><strong>Email:</strong> $email_cliente</p>
                    <p><strong>Fecha de la propuesta:</strong> $fecha_propuesta</p>
                    <p><strong>Estado de la propuesta:</strong> $estado</p>
                  </td>
                </tr>
            </table>
            
            <div style='background-color: #0E3950;'>
            <h4 style='color: #FFFFFF; text-align: center; padding: 7px;'>Detalles de la propuesta</h4>
            </div>
            <p style='text-align:justify;'>Estamos comprometidos a ofrecer soluciones eficaces en <b>$proyecto</b> que impulsen el crecimiento de su negocio y lograr los objetivos que desea con la implementación del proyecto. Por lo anterior, presentamos a continuación nuestra propuesta comercial. Para nosotros sería un privilegío poder ser un aliado estratégico en el desarrollo de su proyecto y cumplimiento de metas.</p>
            
            <p><b>Especificaciones técnicas y comerciales</b><p>
            <p style='text-align:justify;'>$propuesta_content</p>
            
            <h4>Relación de productos y/o servicios</h4>
            <div style='overflow-x: auto;'>
            
            <table style='width: 100%; border-collapse: collapse;'>
                <thead>
                    <tr style='background-color: #f8f8f8;'>
                        <th style='border: 1px solid #ddd; padding: 8px;'><b>Descripción del Producto</b></th>
                        <th style='border: 1px solid #ddd; padding: 8px;'>Precio Unitario</th>
                        <th style='border: 1px solid #ddd; padding: 8px;'>Cantidad</th>
                        <th style='border: 1px solid #ddd; padding: 8px;'>Descuento</th>
                        <th style='border: 1px solid #ddd; padding: 8px;'>Total</th>
                    </tr>
                </thead>
                <tbody>";
        
        if ($cotizacion) {
            foreach ($cotizacion as $row) {
                $descripcion_propuesta = $row['descripcion_producto'];
                $precio_propuesta = number_format($row['precio_unitario'], 2, '.', ',');
                $cantidad_propuesta = $row['cantidad'];
                $descuento_propuesta = $row['descuento'];
                $descuento_porcen = $descuento_propuesta * 100;
                $total_propuesta = number_format($row['total_unitario'], 2, '.', ',');

                $content .= "
                    <tr>
                        <td style='text-align:left; border: 1px solid #ddd; padding: 8px;'>$descripcion_propuesta</td>
                        <td style='text-align:right; border: 1px solid #ddd; padding: 8px;'>$ $precio_propuesta</td>
                        <td style='text-align:center; border: 1px solid #ddd; padding: 8px;'>$cantidad_propuesta</td>
                        <td style='text-align:center; border: 1px solid #ddd; padding: 8px;'>$descuento_porcen%</td>
                        <td style='text-align:right; border: 1px solid #ddd; padding: 8px;'>$ $total_propuesta</td>
                    </tr>";
            }
        }

        $content .= "
                </tbody>
            </table>
            <table style='width: 100%; border-collapse: collapse;'>
                <thead>
                    <tr style='background-color: #f8f8f8;'>
                        <th style='text-align:right; border: 1px solid #ddd; padding: 8px;'><b>Total de la inversión: $$overall_total_number</b></th>
                </thead>
            </table>
            </div>
            <hr style='border: 1px solid #ddd;'>

            <div class='wp-block-columns is-layout-flex wp-container-core-columns-is-layout-1 wp-block-columns-is-layout-flex'>
            <div class='wp-block-column is-layout-flow wp-block-column-is-layout-flow'>
            <h4>Estimación de tiempos</h4>
            <p>Para el desarrollo y entrega del proyecto, estimamos una duración de: <b>$estimacion_tiempos.</b></p>
            </div>
            <div class='wp-block-column is-layout-flow wp-block-column-is-layout-flow'>
            <h4>Condiciones de Pago</h4>
            <p>Para el inicio del proyecto, se requiere las siguientes condiciones de pago: <b>$condiciones_pago.</b></p>
            </div>
            </div>
            
            <div style='background-color: #0E3950;'>
            <h4 style='color: #FFFFFF; text-align: center; padding: 7px;'>Requisitos y recomendaciones</h4>
            </div>
            <p style='text-align:justify;'>$recomendaciones</p>
            
            <h5>Soporte y Mesa de ayuda</h5>
            <p style='text-align:justify;'>Acción Eficaz cuenta con un área y sistema de soporte y mesa de ayuda, en la que usted podrá gestionar todas sus solicitudes y requerimientos de manera ágil y rápida. Por ello contamos con unos tiempos óptimos de respuesta que podrá conocer en nuestro <a href='https://www.portal.accioneficaz.com/document/acuerdos-de-nivel-de-servicio-de-accion-eficaz' target='_blank'>Acuerdo de Nivel de Servicio</a>; así miso podrá registrar y realizar seguimiento a sus solicitudes en nuestro <a href='https://www.portal.accioneficaz.com' target='_blank'>Portal de Clientes</a>, el cual es el único medio de recepción de solicitudes de soporte. </p>
            
            <div style='background-color: #0E3950;'>
            <h4 style='color: #FFFFFF; text-align: center; padding: 7px;'>Aprobación de la Propuesta</h4>
            </div>";
            
            if ($estado != "Aprobada") {
                
            $content .= "
            
                <p style='text-align: justify;'>
                    Embárquese en un viaje de éxito junto a nosotros. Estamos comprometidos a brindarle servicios de calidad superior que superarán sus expectativas y lo ayudarán a alcanzar sus metas.
                </p>
                <p style='background-color: #f2efe6; padding: 5px;'>
                    <span class='dashicons dashicons-media-spreadsheet'></span>
                    <b>Aprobación de la propuesta</b>: Para realizar la aprobación de esta propuesta, por favor ingresa <a href='$enlace_propuesta' target='_blank'><b>aquí</b></a>, o puedes ingresar al <a href='https://www.portal.accioneficaz.com/panel-de-clientes' target='_blank'><b>Área comercial de clientes</b></a>.
                </p>
        
        
            ";
            } else {
            
            $content .= "
            
                <p><b>La propuesta ya ha sido aprobada</b></p>
                <p>Gracias por permitirnos ser un aliado estratégico para su proyecto; responderemos a su confianza con un alto grado de responsabilidad y compromiso para el cumplimiento de las metas y tiempos establecidos.</p>
                <div class='wp-block-columns is-layout-flex wp-container-core-columns-is-layout-1 wp-block-columns-is-layout-flex'>
                <div class='wp-block-column is-layout-flow wp-block-column-is-layout-flow'>
                <p><b>Aprobada por</b>: $aprobado_por.</p>
                </div>
                <div class='wp-block-column is-layout-flow wp-block-column-is-layout-flow'>
                <p><b>Fecha de aprobación</b>: $fecha_aprobacion.</p>
                </div>
                </div>
                <p style='background-color: #f2efe6; padding: 5px;'>
                    <span class='dashicons dashicons-media-spreadsheet'></span>
                    <b>Orden de pago</b>: Para ver la orden de pago ingresa <a href='$orden_pago' target='_blank'><b>aquí</b></a>, o puedes ingresar al <a href='https://www.portal.accioneficaz.com/panel-de-clientes' target='_blank'><b>Área comercial de clientes</b></a>.
                </p>
                
                ";
            }
            $content .= "
            </p>
            <hr style='border: 1px solid #ddd;'>
            <p style='text-align:center; padding: 5px;'>
                    RUT: 1.073.232.264 - Correo: info@accioneficaz.com - WhatsApp: 57 318 601 3557.
            </p>
            <hr style='border: 1px solid #ddd;'>
            
        </div>";

        // Obtener el contenido del post
        $post_content = $content;
        if (!$post_content) {
            wp_send_json_error('No se pudo obtener el contenido del post.');
            exit;
        }

        // Configurar opciones de Dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

        // Crear una instancia de Dompdf
        $dompdf = new Dompdf($options);

        // Generar el contenido del PDF
        $html = "<html>
            
            <head>
                <style>
                    body {
                        font-family: 'Arial', sans-serif;
                        line-height: 1.6;
                        color: #333;
                    }
                    h1, h2, h3 {
                        color: #0E3950;
                    }
                    .table {
                        width: 100%;
                        border-collapse: collapse;
                    }
                    .table th, .table td {
                        border: 1px solid #ddd;
                        padding: 8px;
                    }
                    .table th {
                        background-color: #f2f2f2;
                        text-align: left;
                    }
                </style>
            </head>
            
            <body>";
        $html .= $post_content; // Aquí puedes personalizar el contenido
        $html .= "</body></html>";

        // Cargar el HTML en Dompdf
        $dompdf->loadHtml($html);

        // (Opcional) Configurar tamaño de papel y orientación
        $dompdf->setPaper('letter', 'portrait');

        // Renderizar el PDF
        $dompdf->render();

        // Enviar el PDF como respuesta al navegador
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $id_propuesta . '.pdf"');
        echo $dompdf->output();
        exit;
    }

    wp_send_json_error('ID de post no proporcionado.');
    exit;
}



// Función PHP para procesar la aprobación de la propuesta
function aprobar_propuesta() {
    // Verificación de seguridad con el nonce
    check_ajax_referer('aprobar_propuesta_nonce', 'nonce');

    // Verificar si los datos necesarios están presentes
    if (isset($_POST['nombre_aprobador']) && isset($_POST['post_id'])) {
        $nombre_aprobador = sanitize_text_field($_POST['nombre_aprobador']);
        $post_id = intval($_POST['post_id']);
        
        // Actualizar los campos en la propuesta
        update_field('aprobado_por', $nombre_aprobador, $post_id);
        update_field('fecha_aprobacion', current_time('Y-m-d'), $post_id);
        update_field('estado', 'Aprobada', $post_id);

        // Obtener los datos de la propuesta para crear el registro de "orden-pago"
        $titulo_propuesta = get_the_title($post_id);
        $fecha_propuesta = get_field('fecha_propuesta', $post_id);
        $nombre_cliente = get_field('nombre_cliente', $post_id);
        $nit_cliente = get_field('identificacion_cliente', $post_id);
        $correo_cliente = get_field('email_cliente', $post_id);
        $numero_propuesta = get_field('id_propuesta', $post_id);
        $telefono_cliente = get_field('telefono_cliente', $post_id);
        $valor_total_pro = get_field('overall_total', $post_id);
        $condicion_pago_pro = get_field('condiciones_pago', $post_id);
        $id_cliente = get_field('cliente', $post_id);
        $titulo_orden_pago = 'Orden de pago para ' . $numero_propuesta;
        $asunto_propuesta = get_field('proyecto', $post_id);
        $enlace_propuesta = get_permalink($post_id);
        $fechac_orden_pago = current_time('Y-m-d');
        
        
        switch ($condicion_pago_pro) {
            case '100% Anticipo':
                $condicion_pago_pro_real = 1;
                break;
            case '60% anticipo y 40% a la entrega':
                $condicion_pago_pro_real = 0.6;
                break;
            case '50% anticipo y 50% a la entrega':
                $condicion_pago_pro_real = 0.5;
                break;
            case '40% anticipo y 60% a la entrega':
                $condicion_pago_pro_real = 0.4;
                break;
            case '30% anticipo y 70% a la entrega':
                $condicion_pago_pro_real = 0.3;
                break;
        }
        
        $total_orden_pago = $valor_total_pro * $condicion_pago_pro_real;

        // Crear el nuevo registro de "orden-pago"
        $orden_pago_id = wp_insert_post(array(
            'post_title'    => $titulo_orden_pago,
            'post_type'     => 'orden-pago',
            'post_status'   => 'publish'
        ));

        if ($orden_pago_id) {
            // Asignar los campos personalizados en el nuevo registro "orden-pago"
            update_field('fecha_pago', $fechac_orden_pago, $orden_pago_id);
            update_field('nombre_cliente_pago', $nombre_cliente, $orden_pago_id);
            update_field('nit_cliente_pago', $nit_cliente, $orden_pago_id);
            update_field('correo_cliente_pago', $correo_cliente, $orden_pago_id);
            update_field('numero_propuesta_pago', $numero_propuesta, $orden_pago_id);
            update_field('telefono_cliente_pago', $telefono_cliente, $orden_pago_id);
            update_field('valor_total_propuesta', $valor_total_pro, $orden_pago_id);
            update_field('condicion_pago_pro', $condicion_pago_pro_real, $orden_pago_id);
            update_field('total_orden_pago', $total_orden_pago, $orden_pago_id);
            update_field('id_cliente', $id_cliente, $orden_pago_id);
            update_field('estado_pago', 'Emitida', $orden_pago_id);
            update_field('enlace_propuesta', $enlace_propuesta, $orden_pago_id);
            update_field('asunto_propuesta', $asunto_propuesta, $orden_pago_id);
            

            // Obtener el enlace del nuevo registro "orden-pago"
            $enlace_orden_pago = get_permalink($orden_pago_id);

            // Guardar el enlace en el campo personalizado "orden_pago" de la propuesta original
            update_field('orden_pago', $enlace_orden_pago, $post_id);
            
            // Enviar correo al cliente
            $asunto_cliente = "Propuesta Aprobada - Acción Eficaz";
            $mensaje_cliente = "
                <html>
                <body>
                <div>
                <h4 style='text-align:center;'>Gracias por confiar en Acción Eficaz</h4>
                <p>Estimado/a <strong>$nombre_cliente</strong>,</p>
                <p>Nos complace informar que su $numero_propuesta ha sido aprobada; agradecemos su confianza depositada en nosotros, a lo que responderemos con responsabilidad y compromiso en la ejecución del proyecto y tiempos establecidos.</p>
                <p>Para continuar con el proceso e inicio de ejecución del proyecto, le compartimos el enlace de la orden de pago para su respectivo proceso, por favor ingrese  aquí:$enlace_orden_pago.</p>
                <p>Cualquier duda o inquietud, por favor no dude en contactarnos.</p>
                </div>
                </body>
                </html>
            ";
            wp_mail($correo_cliente, $asunto_cliente, $mensaje_cliente);

            // Enviar correo al administrador
            $correo_admin = get_option('admin_email');
            $asunto_admin = "Notificación de Propuesta Aprobada";
            $mensaje_admin = "
                La '$numero_propuesta' ha sido aprobada por $nombre_aprobador.

                Detalles:
                - Cliente: $nombre_cliente
                - Total: $valor_total_pro
                - Fecha de aprobación: " . current_time('Y-m-d') . "
                - Enlace de la orden de pago: $enlace_orden_pago
            ";
            wp_mail($correo_admin, $asunto_admin, $mensaje_admin);

            // Mensaje de éxito
            wp_send_json_success('Gracias por Confiar en Acción Eficaz; la propuesta ha sido aprobada y se ha creado exitosamente la orden de pago. Por favor espere un momento para obtener acceso a la orden de pago.');
            
        } else {
            // Mensaje de error si no se pudo crear la orden de pago
            wp_send_json_error('Error al crear la orden de pago.');
        }
    } else {
        // Mensaje de error si faltan datos
        wp_send_json_error('Datos incompletos para aprobar la propuesta.');
    }

    wp_die();
}
//add_action('wp_ajax_aprobar_propuesta', 'aprobar_propuesta');
add_action('wp_ajax_aprobar_propuesta', 'aprobar_propuesta');
add_action('wp_ajax_nopriv_aprobar_propuesta', 'aprobar_propuesta');

//Encolar js de confetti
function enqueue_confetti_script() {
    wp_enqueue_script(
        'confetti-js',
        'https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js',
        [],
        '1.6.0',
        true
    );
}
add_action('wp_enqueue_scripts', 'enqueue_confetti_script');


// Función para encolar el script en el frontend
function enqueue_ae_erp_script() {
    if (is_singular('propuesta')) {
        wp_enqueue_script('ae-erp-script', plugin_dir_url(__FILE__) . '../../js/ae-erp-script.js', array('jquery'), null, true);
        
        // Pasar datos al script
        wp_localize_script('ae-erp-script', 'ae_erp_data', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'post_id' => get_the_ID(),
            'nonce' => wp_create_nonce('aprobar_propuesta_nonce')
        ));
    }
}
add_action('wp_enqueue_scripts', 'enqueue_ae_erp_script');



//shortcode
add_shortcode('probar_notificacion_servicios', function () {
    notificar_servicios_por_vencer();
    return 'Función notificar_servicios_por_vencer ejecutada.';
});



function ae_erp_enqueue_assets() {
    // Registrar y cargar CSS
    wp_enqueue_style(
        'ae-erp-styles', // Handle único
        plugin_dir_url(__FILE__) . '../../css/ae-erp-styles.css', // Ruta del archivo CSS
        [],
        '1.0.0' // Versión del archivo
    );

    // Registrar y cargar JS
    wp_enqueue_script(
        'ae-erp-script', // Handle único
        plugin_dir_url(__FILE__) . '../../js/ae-erp-script.js', // Ruta del archivo JS
        ['jquery'], // Dependencias
        '1.0.0', // Versión del archivo
        true // Cargar en el footer
    );
}
add_action('wp_enqueue_scripts', 'ae_erp_enqueue_assets');



function mostrar_icono_notificacion() {
    // Obtener el ID del usuario actual
    $usuario_actual = get_current_user_id();
    if (!$usuario_actual) {
        return ''; // Si no hay usuario conectado, no mostramos nada
    }

    // Consultar las notificaciones relevantes
    $notificaciones = new WP_Query([
        'post_type' => 'notificacion',
        'meta_query' => [
            'relation' => 'AND',
            [
                'key'     => 'estado_notificacion',
                'value'   => 'Activa',
                'compare' => '='
            ],
            [
                'relation' => 'OR',
                [
                    'key'     => 'destino_notificacion',
                    'value'   => 'Todos',
                    'compare' => '='
                ],
                [
                    'relation' => 'AND',
                    [
                        'key'     => 'destino_notificacion',
                        'value'   => 'Especifico',
                        'compare' => '='
                    ],
                    [
                        'key'     => 'usuario_notificacion',
                        'value'   => $usuario_actual,
                        'compare' => '='
                    ]
                ]
            ]
        ]
    ]);

    $tiene_notificaciones = $notificaciones->have_posts();

    $notificaciones_html = '';
    if ($tiene_notificaciones) {
        while ($notificaciones->have_posts()) {
            $notificaciones->the_post();
            $titulo = get_the_title();
            $descripcion = get_the_excerpt();
            $descripcion_truncada = (strlen($descripcion) > 60) ? substr($descripcion, 0, 60) . '…' : $descripcion;
            $url_notificacion = get_permalink();

            $notificaciones_html .= "
            <div class='notificacion-item'>
                <strong>$titulo</strong>
                <p>$descripcion_truncada <a href='$url_notificacion' class='ver-mas' title='Ver más'>
                    <span class='dashicons dashicons-visibility'></span>
                </a></p>
            </div>
            ";
        }
    } else {
        $notificaciones_html = "<p>No tienes notificaciones activas.</p>";
    }
    wp_reset_postdata();

    $html = "
    <div class='icono-notificacion-contenedor'>
        <div class='icono-notificacion " . ($tiene_notificaciones ? 'tiene-notificaciones' : 'sin-notificaciones') . "'>
            <span class='dashicons dashicons-bell'></span>
        </div>
        <div class='desplegable-notificaciones'>
            <div class='notificaciones-header'><span class='dashicons dashicons-megaphone'></span>
                <strong>Notificaciones</strong>
            </div>
            <div class='notificaciones-contenido'>
                $notificaciones_html
            </div>
            <div class='notificaciones-footer'>
                <a href='/todas-las-notificaciones' class='ver-todas'>Ver todas</a>
            </div>
        </div>
    </div>
    ";

    return $html;
}
add_shortcode('icono_notificacion', 'mostrar_icono_notificacion');


//Paginas de notificaciones

function mostrar_pagina_notificaciones() {
    // Obtener el ID del usuario actual
    $usuario_actual = get_current_user_id();
    if (!$usuario_actual) {
        return '<p>Por favor inicia sesión para ver tus notificaciones.</p>';
    }

    // Consultar las notificaciones del usuario
    $notificaciones = new WP_Query([
        'post_type' => 'notificacion',
        'meta_query' => [
            'relation' => 'AND',
            [
                'relation' => 'OR',
                [
                    'key'     => 'destino_notificacion',
                    'value'   => 'Todos',
                    'compare' => '='
                ],
                [
                    'key'     => 'usuario_notificacion',
                    'value'   => $usuario_actual,
                    'compare' => '='
                ]
            ]
        ]
    ]);

    if (!$notificaciones->have_posts()) {
        return '<p>No tienes notificaciones disponibles.</p>';
    }

    ob_start();
    echo '<div class="notificaciones-listado">';

    while ($notificaciones->have_posts()) {
        $notificaciones->the_post();
        $estado = get_post_meta(get_the_ID(), 'estado_notificacion', true);
        $destino = get_post_meta(get_the_ID(), 'destino_notificacion', true);
        $titulo = get_the_title();
        $descripcion = get_the_content();
        $url_detalle = get_permalink();

        echo '<div class="notificacion-item ' . esc_attr($estado) . '">';
        echo '<span class="dashicons dashicons-megaphone"></span>
                    <b>¡Notificación!</b><p style="color:#0E3950; font-size:18px;"><strong>' . esc_html($titulo) . '</strong></p>';
        echo '<p>' . wp_trim_words($descripcion, 150, '...') . '</p><br>';
        if ($estado === 'Activa') {
            if ($destino === 'Todos') {
                echo '<a class="ver-detalle" href="' . esc_url($url_detalle) . '">Ver más</a>';
            } else {
            echo '<button class="dejar-recordar" data-id="' . esc_attr(get_the_ID()) . '">Dejar de recordar</button>';
            echo '<a class="ver-detalle" href="' . esc_url($url_detalle) . '">Ver más</a>';
            }
        } else {
            echo '<a class="ver-detalle" href="' . esc_url($url_detalle) . '">Ver más</a>';
        }
        echo '</div>';
    }

    echo '</div>';
    wp_reset_postdata();
    return ob_get_clean();
}
add_shortcode('pagina_notificaciones', 'mostrar_pagina_notificaciones');

function cambiar_estado_notificacion() {
    // Verificar permisos
    if (!is_user_logged_in() || !isset($_POST['notificacion_id'])) {
        wp_send_json_error('No autorizado', 403);
    }

    $notificacion_id = intval($_POST['notificacion_id']);
    $usuario_actual = get_current_user_id();

    // Validar que la notificación pertenece al usuario actual
    $usuario_notificacion = get_post_meta($notificacion_id, 'usuario_notificacion', true);
    if ($usuario_notificacion != $usuario_actual) {
        wp_send_json_error('No autorizado', 403);
    }

    // Cambiar el estado a Inactiva
    update_post_meta($notificacion_id, 'estado_notificacion', 'Inactiva');

    wp_send_json_success('Notificación actualizada');
}
add_action('wp_ajax_cambiar_estado_notificacion', 'cambiar_estado_notificacion');


