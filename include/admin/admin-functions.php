<?php


// Función para actualizar la descripción del post con los valores de todos los campos ACF
function actualizar_propuesta_con_campos_acf($post_id) {
    // Verificar si se está guardando un post
    if (get_post_type($post_id) === 'propuesta') {

        // Obtener el ID del post
        $post_id = get_the_ID();

        // Obtener los valores de los campos ACF
        $cliente = get_field('cliente', $post_id);
        $fecha = get_field('fecha_propuesta', $post_id);
        $fecha_parts = explode('/', $fecha);
        if (count($fecha_parts) == 3) {
            $day = $fecha_parts[0];
            $month = $fecha_parts[1];
            $year = $fecha_parts[2];
        }
        $id_propuesta = "Propuesta AE-" . $year . $month . $post_id;

        if ($cliente) {
            $customer = new WC_Customer($cliente);
            $user_info = get_userdata($cliente);
            $user_nit = $user_info->user_login;
            $user_name = $user_info->first_name;
            $user_email = $user_info->user_email;
            $user_direccion = $customer->get_billing_address_1();
            $user_telefono = $customer->get_billing_phone();
        }


        // Actualizar los campos personalizados de la propuesta
        update_field('id_propuesta', $id_propuesta, $post_id);
        update_field('identificacion_cliente', $user_nit, $post_id);
        update_field('nombre_cliente', $user_name, $post_id);
        update_field('email_cliente', $user_email, $post_id);
        update_field('direccion_cliente', $user_direccion, $post_id);
        update_field('telefono_cliente', $user_telefono, $post_id);

    } else if (get_post_type($post_id) === 'factura') {

        // Obtener el ID del post
        $post_id = get_the_ID();

        // Obtener los valores de los campos ACF
        $xml_factura = get_field('xml_factura', $post_id);
        if ($xml_factura != "Procesado"){
            
            $xml = simplexml_load_string($xml_factura);
            
            // Registrar los espacios de nombres necesarios
            $namespaces = $xml->getNamespaces(true);
            $xml->registerXPathNamespace('cbc', $namespaces['cbc']);
            $xml->registerXPathNamespace('cac', $namespaces['cac']);
            $invoice_cdata = $xml->xpath('//cbc:Description')[0];
            $invoice_xml = simplexml_load_string($invoice_cdata);
            $invoice_namespaces = $invoice_xml->getNamespaces(true);
            $invoice_xml->registerXPathNamespace('cbc', $invoice_namespaces['cbc']);
            $invoice_xml->registerXPathNamespace('cac', $invoice_namespaces['cac']);
            
            $nit_cliente = $xml->xpath('//cac:ReceiverParty/cac:PartyTaxScheme/cbc:CompanyID')[0];
            $fecha_factura = $xml->xpath('//cbc:IssueDate')[0];
            $valor_total = $invoice_xml->xpath('//cac:LegalMonetaryTotal/cbc:PayableAmount')[0];
            $valor_factura = intval($valor_total);
            $valor_anticipo = $invoice_xml->xpath('//cac:LegalMonetaryTotal/cbc:PrepaidAmount')[0];
            $valor_anticipo_fac = intval($valor_anticipo);
            $total_final_factura = $valor_factura - $valor_anticipo_fac;
            $cufe = strval($xml->xpath('//cbc:UUID')[0]);
            $nit_factura = strval($nit_cliente);
            
            // Buscar el usuario por el NIT
            
            $user = get_user_by('login', $nit_cliente);
            
            // Si se encuentra un usuario
            if ($user) {
                $user_id = $user->ID;
                $nombre_cliente = $user->first_name;
            }
            
            // Formatear la fecha
            $fecha_factura = date('Y-m-d', strtotime((string)$fecha_factura));
            $enlace_cufe = "https://catalogo-vpfe.dian.gov.co/Document/ShowDocumentToPublic/" . $cufe;
            
            $nuevo_contenido = "Procesado";
            
            $args = array(
                'ID'           => $post_id,
                'post_content' => $nuevo_contenido
            );
            
            wp_update_post( $args );
    
            // Actualizar los campos personalizados de la propuesta
            update_field('cliente_factura', $user_id, $post_id);
            update_field('fecha_factura', $fecha_factura, $post_id);
            update_field('cufe_factura', $cufe, $post_id);
            update_field('total_factura', $valor_factura, $post_id);
            update_field('anticipos_factura', $valor_anticipo_fac, $post_id);
            update_field('total_pagar_factura', $total_final_factura, $post_id);
            update_field('enlace_factura', $enlace_cufe, $post_id);
            update_field('nombre_cliente', $nombre_cliente, $post_id);
            update_field('nit_cliente', $nit_factura, $post_id);
            update_field('xml_factura', $nuevo_contenido, $post_id);
        }
    } else if (get_post_type($post_id) === 'servicio') {

        // Obtener el ID del post
        $post_id = get_the_ID();

        // Obtener los valores de los campos ACF
        $cliente = get_field('cliente_servicio', $post_id);

        if ($cliente) {
            $user_info = get_userdata($cliente);
            $user_nit = $user_info->user_login;
            $user_name = $user_info->first_name;
            $user_email = $user_info->user_email;
        }
        
        $titulo = "Servicios " . $user_name;
        $nuevo_contenido ="Procesado";
        
        $args = array(
                'ID'            => $post_id,
                'post_title'         => $titulo,
                'post_content'  => $nuevo_contenido
            );
            
            wp_update_post( $args );

    }
}

// Hook para actualizar la descripción del post después de guardar un post
add_action('acf/save_post', 'actualizar_propuesta_con_campos_acf', 20);

function agregar_boton_enviar_propuesta() {
    global $post;
    if ($post->post_type === 'propuesta') {
        add_meta_box(
            'enviar_propuesta_box',
            'Enviar Propuesta',
            'renderizar_boton_enviar_propuesta',
            'propuesta',
            'side',
            'high'
        );
    }
}
add_action('add_meta_boxes', 'agregar_boton_enviar_propuesta');

function renderizar_boton_enviar_propuesta($post) {
    // Generar un nonce para seguridad
    wp_nonce_field('enviar_propuesta_nonce', 'enviar_propuesta_nonce_field');

    echo '<p>Haga clic en el botón para enviar la propuesta por correo al cliente.</p>';
    echo '<button id="enviar-propuesta" class="button button-primary" data-post-id="' . $post->ID . '">Enviar Propuesta</button>';
}

function enviar_propuesta_por_correo() {
    // Verificar nonce
    check_ajax_referer('enviar_propuesta_nonce', 'nonce');

    // Verificar que se envió el ID del post
    if (isset($_POST['post_id'])) {
        $post_id = intval($_POST['post_id']);

        // Obtener los datos del cliente
        $correo_cliente = get_field('email_cliente', $post_id);
        $nombre_cliente = get_field('nombre_cliente', $post_id);
        $titulo_propuesta = get_the_title($post_id);
        $numero_propuesta = get_field('id_propuesta', $post_id);
        $enlace_propuesta = get_permalink($post_id);

        // Verificar que el correo existe
        if (!$correo_cliente) {
            wp_send_json_error('El cliente no tiene un correo registrado.');
        }

        // Construir el correo
        $asunto = "Propuesta Comercial - Acción Eficaz ";
        $mensaje = "
            <html>
            <body>
            <div>
            	<h4 style='text-align:center;'>Propuesta Comercial de Acción Eficaz</h4><hr>
              	<p>Estimado/a <strong>$nombre_cliente</strong>,</p>
              	<p>Agradecemos su invitación de cotización para la ejecución de su proyecto, razón por la cual, por medio del presente correo nos permitimos enviar nuestra <strong>$numero_propuesta</strong>. Por favor ingrese al siguiente enlace: $enlace_propuesta para visualizar los detalles de la propuesta; no dude en contactarnos si tiene alguna pregunta.</p>
              	<p>Gracias por confiar en <strong>Acción Eficaz</strong>.</p>
            </div>
            </body>
            </html>
        ";

        // Enviar el correo
        $enviado = wp_mail($correo_cliente, $asunto, $mensaje);

        if ($enviado) {
            // Actualizar el estado de la propuesta
            update_field('estado', 'Enviada', $post_id);

            wp_send_json_success(array('message' => 'La propuesta ha sido enviada con éxito.'));
        } else {
            wp_send_json_error(array('message' => 'No se pudo enviar el correo.'));
        }
    } else {
        wp_send_json_error('ID de propuesta no proporcionado.');
    }

    wp_die();
}
add_action('wp_ajax_enviar_propuesta', 'enviar_propuesta_por_correo');

function encolar_script_enviar_propuesta($hook) {
    if ('post.php' === $hook || 'post-new.php' === $hook) {
        wp_enqueue_script('enviar-propuesta-js', plugin_dir_url(__FILE__) . '../../js/ae-erp-script-admin.js', array('jquery'), '1.0', true);

        wp_localize_script('enviar-propuesta-js', 'enviarPropuestaData', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('enviar_propuesta_nonce'),
        ));
    }
}
add_action('admin_enqueue_scripts', 'encolar_script_enviar_propuesta');


//Cron de notificación
function notificar_servicios_por_vencer() {
    // Obtener todos los posts del tipo 'servicio'
    $servicios = get_posts([
        'post_type' => 'servicio',
        'posts_per_page' => -1,
        'post_status' => 'publish',
    ]);
    
    foreach ($servicios as $servicio) {
        $usuario_id = get_field('cliente_servicio', $servicio->ID); 
        $campos_repetidor = get_field('servicios_activos', $servicio->ID); 

        if ($campos_repetidor && is_array($campos_repetidor)) {
            foreach ($campos_repetidor as $campo) {
                $fecha_terminacion = $campo['fecha_terminacion'];
                $nombre_servicio = $campo['item_servicio'];

                if ($fecha_terminacion) {

                    // Convertir la fecha de terminación al formato DateTime
                    $fecha_terminacion_dt = DateTime::createFromFormat('d/m/Y', $fecha_terminacion);
                    $fecha_actual_dt = new DateTime(); // Fecha actual del sistema
                    
                    // Comprobar si la fecha de terminación es válida
                    if ($fecha_terminacion_dt) {
                        // Calcular la diferencia en días entre la fecha actual y la fecha de terminación
                        $dias_restantes = $fecha_actual_dt->diff($fecha_terminacion_dt)->days;
                        
                        // Verificar el estado del servicio según los días restantes
                        if ($fecha_terminacion_dt > $fecha_actual_dt) {
                            
                            if ($dias_restantes == 3) {

                                // Obtener el email del usuario asociado
                                $usuario = get_user_by('id', $usuario_id);
                                if ($usuario && isset($usuario->user_email)) {
                                    $email = $usuario->user_email;
                                    $email_admin = get_option('admin_email');
                                    $emails = [$email, $email_admin];
                                    $user_nombre = $usuario->first_name;
                                    $asunto = "Urgente - Servicios contratados con Acción Eficaz vencen en 3 días";
                                    $mensaje = "
                                        <html>
                                        <body>
                                        <div>
                                        	<h4 style='text-align:center;'>Aviso de vencimiento de servicios con Acción Eficaz</h4><hr>
                                          	<p>Estimado/a <strong>$user_nombre</strong>,</p>
                                          	<p>Nos permitimos informar que el  servicio contratado con Acción Eficaz <b>$nombre_servicio</b> está próximo a vencer; la fecha de vencimiento es <strong>$fecha_terminacion</strong>. Por favor ingrese a nuestra <a href='https://www.portal.accioneficaz.com/panel-de-clientes/'>Área de clientes</a> para visualizar el estado y las opciones de renovación.</p>
                                          	<p>Gracias por confiar en <strong>Acción Eficaz</strong>.</p>
                                        </div>
                                        </body>
                                        </html>
                                    ";
        
                                    // Enviar el correo
                                    wp_mail($emails,$asunto,$mensaje,['Content-Type: text/html; charset=UTF-8']);
                                }
                            } elseif ($dias_restantes == 8) {

                                // Obtener el email del usuario asociado
                                $usuario = get_user_by('id', $usuario_id);
                                if ($usuario && isset($usuario->user_email)) {
                                    $email = $usuario->user_email;
                                    $email_admin = get_option('admin_email');
                                    $emails = [$email, $email_admin];
                                    $user_nombre = $usuario->first_name;
                                    $asunto = "Servicios contratados con Acción Eficaz vencen en 8 días";
                                    $mensaje = "
                                        <html>
                                        <body>
                                        <div>
                                        	<h4 style='text-align:center;'>Aviso de vencimiento de servicios con Acción Eficaz</h4><hr>
                                          	<p>Estimado/a <strong>$user_nombre</strong>,</p>
                                          	<p>Nos permitimos informar que el  servicio contratado con Acción Eficaz <b>$nombre_servicio</b>  está próximo a vencer; la fecha de vencimiento es <strong>$fecha_terminacion</strong>. Por favor ingrese a nuestra <a href='https://www.portal.accioneficaz.com/panel-de-clientes/'>Área de clientes</a> para visualizar el estado y las opciones de renovación.</p>
                                          	<p>Gracias por confiar en <strong>Acción Eficaz</strong>.</p>
                                        </div>
                                        </body>
                                        </html>
                                    ";
        
                                    // Enviar el correo
                                    wp_mail($emails,$asunto,$mensaje,['Content-Type: text/html; charset=UTF-8']);
                                }
                            } elseif ($dias_restantes == 15) {

                                // Obtener el email del usuario asociado
                                $usuario = get_user_by('id', $usuario_id);
                                if ($usuario && isset($usuario->user_email)) {
                                    $email = $usuario->user_email;
                                    $email_admin = get_option('admin_email');
                                    $emails = [$email, $email_admin];
                                    $user_nombre = $usuario->first_name;
                                    $asunto = "Servicios contratados con Acción Eficaz vencen en 15 días";
                                    $mensaje = "
                                        <html>
                                        <body>
                                        <div>
                                        	<h4 style='text-align:center;'>Aviso de vencimiento de servicios con Acción Eficaz</h4><hr>
                                          	<p>Estimado/a <strong>$user_nombre</strong>,</p>
                                          	<p>Nos permitimos informar que el  servicio contratado con Acción Eficaz <b>$nombre_servicio</b> está próximo a vencer; la fecha de vencimiento es <strong>$fecha_terminacion</strong>. Por favor ingrese a nuestra <a href='https://www.portal.accioneficaz.com/panel-de-clientes/'>Área de clientes</a> para visualizar el estado y las opciones de renovación.</p>
                                          	<p>Gracias por confiar en <strong>Acción Eficaz</strong>.</p>
                                        </div>
                                        </body>
                                        </html>
                                    ";
        
                                    // Enviar el correo
                                    wp_mail($emails,$asunto,$mensaje,['Content-Type: text/html; charset=UTF-8']);
                                }
                            } elseif ($dias_restantes == 28) {

                                // Obtener el email del usuario asociado
                                $usuario = get_user_by('id', $usuario_id);
                                if ($usuario && isset($usuario->user_email)) {
                                    $email = $usuario->user_email;
                                    $email_admin = get_option('admin_email');
                                    $emails = [$email, $email_admin];
                                    $user_nombre = $usuario->first_name;
                                    $asunto = "Servicios contratados con Acción Eficaz vencen en 28 días";
                                    $mensaje = "
                                        <html>
                                        <body>
                                        <div>
                                        	<h4 style='text-align:center;'>Aviso de vencimiento de servicios con Acción Eficaz</h4><hr>
                                          	<p>Estimado/a <strong>$user_nombre</strong>,</p>
                                          	<p>Nos permitimos informar que el  servicio contratado con Acción Eficaz <b>$nombre_servicio</b> está próximo a vencer; la fecha de vencimiento es <strong>$fecha_terminacion</strong>. Por favor ingrese a nuestra <a href='https://www.portal.accioneficaz.com/panel-de-clientes/'>Área de clientes</a> para visualizar el estado y las opciones de renovación.</p>
                                          	<p>Gracias por confiar en <strong>Acción Eficaz</strong>.</p>
                                        </div>
                                        </body>
                                        </html>
                                    ";
        
                                    // Enviar el correo
                                    wp_mail($emails,$asunto,$mensaje,['Content-Type: text/html; charset=UTF-8']);
                                    
                                    // Crear la publicación personalizada de tipo notificacion
                                    $mensaje_not = "
                                        <p>Nos permitimos informar que el  servicio contratado con Acción Eficaz <b>$nombre_servicio</b> está próximo a vencer; la fecha de vencimiento es <strong>$fecha_terminacion</strong>. Por favor ingrese a nuestra <a href='https://www.portal.accioneficaz.com/panel-de-clientes/'>Área de clientes</a> para visualizar el estado y las opciones de renovación.</p>
                                    ";
                                    
                                    $notificacion_args = [
                                        'post_title'    => 'Servicios contratados con Acción Eficaz próximos a vencer',
                                        'post_content'  => $mensaje_not,
                                        'post_status'   => 'publish',
                                        'post_author'   => 1,
                                        'post_type'     => 'notificacion',
                                    ];
                            
                                    $notificacion_id = wp_insert_post($notificacion_args);
                            
                                    if ($notificacion_id) {
                                        // Actualizar los campos personalizados de ACF
                                        update_field('destino_notificacion', 'Especifico', $notificacion_id);
                                        update_field('usuario_notificacion', $usuario_id, $notificacion_id);
                                        update_field('estado_notificacion', 'Activa', $notificacion_id);
                                    }
                                }
                            } elseif ($dias_restantes == 30) {

                                // Obtener el email del usuario asociado
                                $usuario = get_user_by('id', $usuario_id);
                                if ($usuario && isset($usuario->user_email)) {
                                    $email = get_option('admin_email');
                                    $user_nombre = $usuario->first_name;
                                    $asunto = "Servicios contratados con cliente de Acción Eficaz vencen en 30 días";
                                    $mensaje = "
                                        <html>
                                        <body>
                                        <div>
                                        	<h4 style='text-align:center;'>Aviso de vencimiento de servicios con Acción Eficaz</h4><hr>
                                          	<p>Estimado Administrador</strong>,</p>
                                          	<p>Nos permitimos informar que que el  servicio contratado <b>$nombre_servicio</b> con el cliente <b>$user_nombre</b> está próximo a vencer; la fecha de vencimiento es <strong>$fecha_terminacion</strong>. Por favor ingrese al sistema para publicar las opciones de renovación.</p>
                                          	<p>Gracias por confiar en <strong>Acción Eficaz</strong>.</p>
                                        </div>
                                        </body>
                                        </html>
                                    ";
        
                                    // Enviar el correo
                                    wp_mail($email,$asunto,$mensaje,['Content-Type: text/html; charset=UTF-8']);
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    
    //aqui
    $email = get_option('admin_email');
    $asunto = "Ejecución de cron Exitosa";
    $mensaje = "
        <html>
        <body>
        <div>
        	<h4 style='text-align:center;'>Se ha ejecutado cron de servicios con Acción Eficaz</h4><hr>
          	<p>Estimado Administrador</strong>,</p>
          	<p>Nos permitimos informar que que el  cron establecido para la notificación de vencimiento de servicios con Acción Eficaz se ha ejecutado correctamente.</p>
          	<p>Gracias por confiar en <strong>Acción Eficaz</strong>.</p>
        </div>
        </body>
        </html>
    ";

    // Enviar el correo
    wp_mail($email,$asunto,$mensaje,['Content-Type: text/html; charset=UTF-8']);
}
add_action('cron_notificacion_servicios', 'notificar_servicios_por_vencer');

// Página de configuración
function wpp_snow_effect_page() {
    // Manejo del envío del formulario
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Guardar el valor como "yes" si está marcado o "no" si no lo está
        $enabled = isset($_POST['wpp_snow_effect_enabled']) ? 'yes' : 'no';
        update_option('wpp_snow_effect_enabled', sanitize_text_field($enabled));
        
        // Aquí puedes agregar más ajustes a guardar si es necesario

        echo '<div class="updated"><p>Configuración guardada.</p></div>';
    }

    // Obtener el valor guardado
    $enabled = get_option('wpp_snow_effect_enabled', 'no');
    ?>
    
    <!-- Contenido de la página -->
    <div class="wrap">
        <h1>Ajustes</h1><hr>
        <form method="post">
            <div id="wpp-settings-tabs" class="wpp-tabs">
                <!-- Tabs -->
                <ul class="wpp-tabs-nav">
                    <li><a href="#tab-empresa">Ajustes de la empresa</a></li>
                    <li><a href="#tab-modulo">Ajustes del módulo</a></li>
                    <li><a href="#tab-adicionales">Ajustes adicionales</a></li>
                    <li><a href="#tab-videos">Video tutoriales</a></li>
                </ul>

                <!-- Contenido de los tabs -->
                <div id="tab-empresa" class="wpp-tab-content">
                    <h2>Ajustes de la empresa</h2>
                    <p>Aquí puedes agregar los campos para los ajustes de la empresa.</p>
                </div>

                <div id="tab-modulo" class="wpp-tab-content">
                    <h2>Ajustes del módulo</h2>
                    <p>Aquí puedes agregar los campos para los ajustes del módulo.</p>
                </div>

                <div id="tab-adicionales" class="wpp-tab-content">
                    <h2>Ajustes adicionales</h2>
                    <label for="wpp_snow_effect_enabled">
                        <input type="checkbox" id="wpp_snow_effect_enabled" name="wpp_snow_effect_enabled" value="yes" <?php checked($enabled, 'yes'); ?>>
                        Habilitar efecto de nieve
                    </label>
                </div>

                <div id="tab-videos" class="wpp-tab-content">
                    <h2>Video tutoriales</h2>
                    <p>Aquí puedes agregar videos de ayuda o tutoriales.</p>
                </div>
            </div>
            
            <br>
            <button type="submit" class="button button-primary">Guardar cambios</button>
        </form>
    </div>
    <?php
}

function wpp_enqueue_admin_assets($hook) {
    // Verifica que estamos en la página de ajustes específica de tu plugin
    if ($hook !== 'erp-ae_page_erp-ae-snow-effect') {
        return;
    }

    // Encolar el CSS
    wp_enqueue_style(
        'ae-erp-styles', plugin_dir_url(__FILE__) . '../../css/ae-erp-styles.css', 
        [], 
        '1.0.0'
    );

    // Encolar el JS
    wp_enqueue_script(
        'ae-erp-script', 
        plugin_dir_url(__FILE__) . '../../js/ae-erp-script-admin.js', 
        ['jquery'], // Dependencia de jQuery
        '1.0.0', 
        true // Cargar en el footer
    );
}
add_action('admin_enqueue_scripts', 'wpp_enqueue_admin_assets');