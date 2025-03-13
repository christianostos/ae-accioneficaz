// Cambia de sección al hacer clic en los enlaces de la barra lateral
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.cliente-panel-link').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            document.querySelectorAll('.cliente-panel-section').forEach(section => section.style.display = 'none');
            document.getElementById(this.dataset.section).style.display = 'block';
        });
    });
});


jQuery(document).ready(function($) {
    $('#approvalForm').off('submit').on('submit', function(e) {
        e.preventDefault();

        var nombreAprobador = $('#nombre_aprobador').val();
        var postId = ae_erp_data.post_id;
        var respuestaHumana = parseInt($('#verificacion_humana').val());
        var sumaCorrecta = parseInt($('#suma_correcta').val());

        // Validación de la respuesta humana
        if (respuestaHumana !== sumaCorrecta) {
            $('#approvalMessage').html('<p style="color: red;">La respuesta de verificación es incorrecta. Intente de nuevo.</p>');
            return;
        }

        // Deshabilitar el botón y mostrar "Procesando..."
        var $boton = $(this).find('input[type="submit"]');
        $boton.prop('disabled', true).val('Procesando...');

        $.ajax({
            url: ae_erp_data.ajax_url,
            type: 'POST',
            data: {
                action: 'aprobar_propuesta',
                nombre_aprobador: nombreAprobador,
                post_id: postId,
                nonce: ae_erp_data.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('#approvalMessage').html('<p style="color: green;">' + response.data + '</p>');
                    $('#approvalForm').hide();

                    // Efecto de serpentinas (confetti)
                    confetti({
                        particleCount: 100,
                        spread: 70,
                        origin: { y: 0.6 }
                    });

                    // Refresca la página 5 segundos después
                    setTimeout(function() {
                        location.reload();
                    }, 5000);
                } else {
                    $('#approvalMessage').html('<p style="color: red;">' + response.data + '</p>');
                    $boton.prop('disabled', false).val('Aprobar Propuesta'); // Reactivar el botón
                }
            },
            error: function() {
                $('#approvalMessage').html('<p style="color: red;">Error al aprobar la propuesta. Intente nuevamente.</p>');
                $boton.prop('disabled', false).val('Aprobar Propuesta'); // Reactivar el botón
            }
        });
    });
});


document.addEventListener('DOMContentLoaded', function () {
    const icono = document.querySelector('.icono-notificacion');
    const desplegable = document.querySelector('.desplegable-notificaciones');

    icono.addEventListener('click', function () {
        // Alternar la clase "mostrar" para el efecto de transición
        if (desplegable.classList.contains('mostrar')) {
            desplegable.classList.remove('mostrar');
            setTimeout(() => {
                desplegable.style.display = 'none';
            }, 300); // Espera a que la animación termine antes de ocultarlo
        } else {
            desplegable.style.display = 'block';
            setTimeout(() => {
                desplegable.classList.add('mostrar');
            }, 10); // Permitir que se ejecute la transición
        }
    });

    // Cerrar desplegable al hacer clic fuera
    document.addEventListener('click', function (event) {
        if (!icono.contains(event.target) && !desplegable.contains(event.target)) {
            desplegable.classList.remove('mostrar');
            setTimeout(() => {
                desplegable.style.display = 'none';
            }, 300);
        }
    });
});


document.addEventListener('DOMContentLoaded', function () {
    const botones = document.querySelectorAll('.dejar-recordar');

    botones.forEach((boton) => {
        boton.addEventListener('click', function () {
            const notificacionId = boton.getAttribute('data-id');
            if (!notificacionId) return;

            fetch(ajaxurl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'cambiar_estado_notificacion',
                    notificacion_id: notificacionId
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        boton.closest('.notificacion-item').classList.remove('Activa');
                        boton.closest('.notificacion-item').classList.add('Inactiva');
                        boton.remove();
                    } else {
                        alert('Error al actualizar la notificación');
                    }
                });
        });
    });
});

