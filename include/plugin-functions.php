<?php

// Incluye los archivos de las diferentes secciones
require( plugin_dir_path( __FILE__ ) . '/admin/admin-functions.php');
require( plugin_dir_path( __FILE__ ) . '/public/public-functions.php');

// Añadir el menú principal "ERP AE"
function wpp_register_erp_ae_menu() {
    add_menu_page(
        'ERP AE',             // Título de la página
        'ERP AE',             // Título del menú
        'manage_options',     // Capacidad requerida
        'erp-ae',             // Slug del menú principal
        '',                   // Callback vacío, porque solo es contenedor
        'dashicons-businessman', // Icono del menú
        3                     // Posición en el menú
    );
}
add_action('admin_menu', 'wpp_register_erp_ae_menu');

// Registrar tipo de contenido personalizado "Propuesta" en el submenú "ERP AE"
function wpp_register_cpts_propuestas() {
    $labels = array(
        "name" => __( "Propuestas", "" ),
        "singular_name" => __( "Propuesta", "" ),
        "menu_name" => __( "Propuestas AE", "" ),
        "all_items" => __( "Propuestas", "" ),
        "add_new" => __( "Crear propuesta", "" ),
        "add_new_item" => __( "Crear nueva propuesta", "" ),
        "edit_item" => __( "Editar propuesta", "" ),
        "new_item" => __( "Nueva propuesta", "" ),
        "view_item" => __( "Ver propuesta", "" ),
        "search_items" => __( "Buscar propuestas", "" ),
        "not_found" => __( "No se encontraron propuestas", "" ),
        "not_found_in_trash" => __( "No hay propuestas en la papelera", "" ),
    );

    $args = array(
        "label" => __( "Propuestas", "" ),
        "labels" => $labels,
        "public" => true,
        "publicly_queryable" => true,
        "show_ui" => true,
        "show_in_rest" => false,
        "has_archive" => false,
        "show_in_menu" => 'erp-ae',  // Asigna al menú principal "ERP AE"
        "capability_type" => "post",
        "map_meta_cap" => true,
        "hierarchical" => false,
        "rewrite" => array( 'slug' => 'propuesta' ),
        "query_var" => true,
        "menu_icon" => 'dashicons-welcome-widgets-menus',
        "supports" => array( "title", "thumbnail", "editor" ),
    );

    register_post_type( "propuesta", $args );
}
add_action( 'init', 'wpp_register_cpts_propuestas' );

// Registrar tipo de contenido personalizado "Orden de pago" en el submenú "ERP AE"
function wpp_register_cpts_orden_pago() {
    $labels = array(
        "name" => __( "Órdenes de pago", "" ),
        "singular_name" => __( "Órden de pago", "" ),
        "menu_name" => __( "Órdenes de pago", "" ),
        "all_items" => __( "Órdenes de pago", "" ),
        "add_new" => __( "Crear Órden de pago", "" ),
        "add_new_item" => __( "Crear nueva Órden de pago", "" ),
        "edit_item" => __( "Editar Órden de pago", "" ),
        "new_item" => __( "Nueva Órden de pago", "" ),
        "view_item" => __( "Ver Órden de pago", "" ),
        "search_items" => __( "Buscar Órden de pago", "" ),
        "not_found" => __( "No se encontraron Órdenes de pago", "" ),
        "not_found_in_trash" => __( "No hay Órdenes de pago en la papelera", "" ),
    );

    $args = array(
        "label" => __( "Órdenes de pago", "" ),
        "labels" => $labels,
        "public" => true,
        "publicly_queryable" => true,
        "show_ui" => true,
        "show_in_rest" => false,
        "has_archive" => false,
        "show_in_menu" => 'erp-ae',  // Asigna al menú principal "ERP AE"
        "capability_type" => "post",
        "map_meta_cap" => true,
        "hierarchical" => false,
        "rewrite" => array( 'slug' => 'orden-pago' ),
        "query_var" => true,
        "menu_icon" => 'dashicons-welcome-widgets-menus',
        "supports" => array( "title", "thumbnail", "editor" ),
    );

    register_post_type( "orden-pago", $args );
}
add_action( 'init', 'wpp_register_cpts_orden_pago' );

// Registrar tipo de contenido personalizado "Factura" en el submenú "ERP AE"
function wpp_register_cpts_factura() {
    $labels = array(
        "name" => __( "Facturas Electrónicas", "" ),
        "singular_name" => __( "Factura Electrónica", "" ),
        "menu_name" => __( "Facturas", "" ),
        "all_items" => __( "Facturas Electrónicas", "" ),
        "add_new" => __( "Crear Factura Electrónica", "" ),
        "add_new_item" => __( "Crear nueva Factura Electrónica", "" ),
        "edit_item" => __( "Editar Factura Electrónica", "" ),
        "new_item" => __( "Nueva Factura Electrónica", "" ),
        "view_item" => __( "Ver Factura Electrónica", "" ),
        "search_items" => __( "Buscar Factura Electrónica", "" ),
        "not_found" => __( "No se encontraron Facturas Electrónicas", "" ),
        "not_found_in_trash" => __( "No hay Facturas Electrónicas en la papelera", "" ),
    );

    $args = array(
        "label" => __( "Facturas", "" ),
        "labels" => $labels,
        "public" => true,
        "publicly_queryable" => true,
        "show_ui" => true,
        "show_in_rest" => false,
        "has_archive" => false,
        "show_in_menu" => 'erp-ae',  // Asigna al menú principal "ERP AE"
        "capability_type" => "post",
        "map_meta_cap" => true,
        "hierarchical" => false,
        "rewrite" => array( 'slug' => 'factura' ),
        "query_var" => true,
        "menu_icon" => 'dashicons-welcome-widgets-menus',
        "supports" => array( "title", "thumbnail", "editor" ),
    );

    register_post_type( "factura", $args );
}
add_action( 'init', 'wpp_register_cpts_factura' );


// Registrar tipo de contenido personalizado "servicios" en el submenú "ERP AE"
function wpp_register_cpts_servicio() {
    $labels = array(
        "name" => __( "Servicios", "" ),
        "singular_name" => __( "Servicio", "" ),
        "menu_name" => __( "Servicio", "" ),
        "all_items" => __( "Servicios Clientes", "" ),
        "add_new" => __( "Crear Servicio", "" ),
        "add_new_item" => __( "Crear nuevo Servicio", "" ),
        "edit_item" => __( "Editar Servicio", "" ),
        "new_item" => __( "Nuevo Servicio", "" ),
        "view_item" => __( "Ver Servicios", "" ),
        "search_items" => __( "Buscar Servicio", "" ),
        "not_found" => __( "No se encontraron Servicios", "" ),
        "not_found_in_trash" => __( "No hay Servicios en la papelera", "" ),
    );

    $args = array(
        "label" => __( "Servicios", "" ),
        "labels" => $labels,
        "public" => true,
        "publicly_queryable" => true,
        "show_ui" => true,
        "show_in_rest" => false,
        "has_archive" => false,
        "show_in_menu" => 'erp-ae',  // Asigna al menú principal "ERP AE"
        "capability_type" => "post",
        "map_meta_cap" => true,
        "hierarchical" => false,
        "rewrite" => array( 'slug' => 'servicio' ),
        "query_var" => true,
        "menu_icon" => 'dashicons-welcome-widgets-menus',
        "supports" => array( "title", "thumbnail", "editor" ),
    );

    register_post_type( "servicio", $args );
}
add_action( 'init', 'wpp_register_cpts_servicio' );

//registrar tipo de contenido proyecto
function wpp_register_cpts_proyecto() {
    $labels = array(
        "name" => __( "Proyectos", "" ),
        "singular_name" => __( "Proyecto", "" ),
        "menu_name" => __( "Proyectos AE", "" ),
        "all_items" => __( "Proyectos", "" ),
        "add_new" => __( "Crear proyecto", "" ),
        "add_new_item" => __( "Crear nuevo proyecto", "" ),
        "edit_item" => __( "Editar proyecto", "" ),
        "new_item" => __( "Nueva proyecto", "" ),
        "view_item" => __( "Ver proyecto", "" ),
        "search_items" => __( "Buscar proyecto", "" ),
        "not_found" => __( "No se encontraron proyectos", "" ),
        "not_found_in_trash" => __( "No hay proyecto en la papelera", "" ),
    );

    $args = array(
        "label" => __( "Proyectos", "" ),
        "labels" => $labels,
        "public" => true,
        "publicly_queryable" => true,
        "show_ui" => true,
        "show_in_rest" => false,
        "has_archive" => false,
        "show_in_menu" => 'erp-ae',  // Asigna al menú principal "ERP AE"
        "capability_type" => "post",
        "map_meta_cap" => true,
        "hierarchical" => false,
        "rewrite" => array( 'slug' => 'proyecto' ),
        "query_var" => true,
        "menu_icon" => 'dashicons-welcome-widgets-menus',
        "supports" => array( "title", "thumbnail", "editor" ),
    );

    register_post_type( "proyecto", $args );
}
add_action( 'init', 'wpp_register_cpts_proyecto' );

// Registrar tipo de contenido personalizado "notificaciones" en el submenú "ERP AE"
function wpp_register_cpts_notificacion() {
    $labels = array(
        "name" => __( "Notificaciones", "" ),
        "singular_name" => __( "Notificación", "" ),
        "menu_name" => __( "Notificaciones", "" ),
        "all_items" => __( "Notificaciones", "" ),
        "add_new" => __( "Crear Notificación", "" ),
        "add_new_item" => __( "Crear nueva Notificación", "" ),
        "edit_item" => __( "Editar Notificación", "" ),
        "new_item" => __( "Nueva Notificación", "" ),
        "view_item" => __( "Ver Notificaciones", "" ),
        "search_items" => __( "Buscar Notificaciones", "" ),
        "not_found" => __( "No se encontraron Notificaciones", "" ),
        "not_found_in_trash" => __( "No hay Notificaciones en la papelera", "" ),
    );

    $args = array(
        "label" => __( "Notificaciones", "" ),
        "labels" => $labels,
        "public" => true,
        "publicly_queryable" => true,
        "show_ui" => true,
        "show_in_rest" => false,
        "has_archive" => false,
        "show_in_menu" => 'erp-ae',  // Asigna al menú principal "ERP AE"
        "capability_type" => "post",
        "map_meta_cap" => true,
        "hierarchical" => false,
        "rewrite" => array( 'slug' => 'notificacion' ),
        "query_var" => true,
        "menu_icon" => 'dashicons-welcome-widgets-menus',
        "supports" => array( "title", "editor" ),
    );

    register_post_type( "notificacion", $args );
}
add_action( 'init', 'wpp_register_cpts_notificacion' );







// Sets propuesta pages to do not index
function wpp_noindex_for_propuesta() {
    if ( is_singular( 'propuesta' ) ) {
        echo '<meta name="robots" content="noindex, follow">';
    }
}

// Sets ordenes compra pages to do not index
function wpp_noindex_for_orden_pago() {
    if ( is_singular( 'orden-pago' ) ) {
        echo '<meta name="robots" content="noindex, follow">';
    }
}

// Sets facturas pages to do not index
function wpp_noindex_for_facturas() {
    if ( is_singular( 'factura' ) ) {
        echo '<meta name="robots" content="noindex, follow">';
    }
}

// Sets servicios pages to do not index
function wpp_noindex_for_servicios() {
    if ( is_singular( 'servicio' ) ) {
        echo '<meta name="robots" content="noindex, follow">';
    }
}

// Sets servicios pages to do not index
function wpp_noindex_for_notificaciones() {
    if ( is_singular( 'notificacion' ) ) {
        echo '<meta name="robots" content="noindex, follow">';
    }
}

// Agregar cron al activar el plugin/tema
function registrar_cron_notificacion_servicios() {
    if (!wp_next_scheduled('cron_notificacion_servicios')) {
        wp_schedule_event(time(), 'daily', 'cron_notificacion_servicios');
    }
}
add_action('wp', 'registrar_cron_notificacion_servicios');

// Eliminar cron al desactivar el plugin/tema
function eliminar_cron_notificacion_servicios() {
    $timestamp = wp_next_scheduled('cron_notificacion_servicios');
    if ($timestamp) {
        wp_unschedule_event($timestamp, 'cron_notificacion_servicios');
    }
}
register_deactivation_hook(__FILE__, 'eliminar_cron_notificacion_servicios');

// Agregar una página de configuración para habilitar/deshabilitar el efecto de nieve
function wpp_add_snow_effect_setting() {
    add_submenu_page(
        'erp-ae',               // Menú padre
        'Ajustes ERP AE',      // Título de la página
        'Ajustes ERP AE',      // Título del menú
        'manage_options',       // Capacidad
        'erp-ae-snow-effect',   // Slug
        'wpp_snow_effect_page'  // Función de callback
    );
}
add_action('admin_menu', 'wpp_add_snow_effect_setting');
