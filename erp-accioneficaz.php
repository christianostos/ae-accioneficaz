<?php


/*
Plugin Name: AE ERP
Plugin URI: https://accioneficaz.com/
Description: Sistema de gestión de propuestas de AE.
Author: Acción Eficaz
Version: 1.1.1
Author URI: https://accioneficaz.com/
Text Domain: erp-ae
*/


// If this file is called directly, abort
if ( ! defined( 'ABSPATH' ) ) {
     die ('Se ha detectado un acceso no autorizado');
}

function cliente_panel_enqueue_assets() {
    // Encola el archivo CSS desde la carpeta 'css'
    wp_enqueue_style('cliente-panel-styles', plugin_dir_url(__FILE__) . 'css/ae-erp-styles.css');
    
    // Encola el archivo JavaScript desde la carpeta 'js'
    wp_enqueue_script('cliente-panel-scripts', plugin_dir_url(__FILE__) . 'js/ae-erp-script.js', array('jquery'), null, true);
}
add_action('wp_enqueue_scripts', 'cliente_panel_enqueue_assets');

function wpp_enqueue_snow_effect() {
    if (get_option('wpp_snow_effect_enabled', 'no') === 'yes') {
        wp_enqueue_style('wpp-snow-effect', plugin_dir_url(__FILE__) . 'css/ae-erp-styles-nieve.css');
        wp_enqueue_script('wpp-snow-effect', plugin_dir_url(__FILE__) . 'js/ae-erp-script-nieve.js', array(), false, true);
    }
}
add_action('wp_enqueue_scripts', 'wpp_enqueue_snow_effect');


function enqueue_admin_calculations_script($hook) {
    // Verifica si estamos en el editor de propuestas
    if ('post.php' == $hook || 'post-new.php' == $hook) {
        $screen = get_current_screen();
        if ($screen->post_type === 'propuesta') {
            // Encolar el script que realiza los cálculos
            wp_enqueue_script(
                'admin-calculations',
                plugin_dir_url(__FILE__) . 'js/ae-erp-script-admin.js',
                array('jquery'),
                null,
                true
            );
        }
    }
}
add_action('admin_enqueue_scripts', 'enqueue_admin_calculations_script');


// The core plugin file that is used to define internationalization, hooks and functions
require( plugin_dir_path( __FILE__ ) . '/include/plugin-functions.php');