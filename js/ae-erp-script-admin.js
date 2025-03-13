document.addEventListener('DOMContentLoaded', function() {
    // Encontrar el contenedor del grupo repetidor (ajusta el selector si es necesario)
    const repeaterContainer = document.querySelector('.acf-repeater');

    // Delegar los eventos de escucha al contenedor del repetidor
    repeaterContainer.addEventListener('input', function(event) {
        // Verificar si el evento se originó en un input dentro de una fila
        if (event.target.closest('.acf-row')) {
            const row = event.target.closest('.acf-row');
            updateLineTotal(row);
        }
    });
    
    // Función para actualizar el total de cada línea en el grupo repetidor
    function updateLineTotal(row) {
        let price = parseFloat(row.querySelector('[data-name="precio_unitario"] input').value) || 0;
        let quantity = parseFloat(row.querySelector('[data-name="cantidad"] input').value) || 0;
        let discount = parseFloat(row.querySelector('[data-name="descuento"] input').value) || 0;

        // Cálculo del total de la línea
        let lineDescuento = (price * discount * quantity);
        let lineTotal = (price * quantity) - lineDescuento;
        row.querySelector('[data-name="total_unitario"] input').value = lineTotal.toFixed(0);

        // Actualizar el total general
        updateOverallTotal();
    }

    // Función para actualizar el total general sumando todas las líneas
    function updateOverallTotal() {
        let total = 0;

        document.querySelectorAll('[data-name="total_unitario"] input').forEach(input => {
            total += parseFloat(input.value) || 0;
        });

        // Asignar el total general al campo ACF 'overall_total'
        const overallTotalField = document.querySelector('[data-name="overall_total"] input');
        if (overallTotalField) {
            overallTotalField.value = total.toFixed(0);
        }
    }

    // Función para agregar eventos a cada fila
    function addEventListenersToRow(row) {
        row.querySelectorAll('[data-name="precio_unitario"] input, [data-name="cantidad"] input, [data-name="descuento"] input').forEach(input => {
            input.addEventListener('input', function() {
                updateLineTotal(row);
            });
        });
    }

    // Agregar eventos de entrada en cada fila existente al cargar la página
    document.querySelectorAll('.acf-row').forEach(row => {
        addEventListenersToRow(row);
    });

    // Escuchar el evento de clic para cuando se agreguen nuevas filas
    document.addEventListener('click', function(event) {
        // Comprobar si se hizo clic en el botón para agregar filas
        if (event.target.closest('.add-row')) {
            setTimeout(() => {
                // Añadir eventos a la nueva fila agregada
                const newRow = document.querySelector('.acf-row:last-child');
                if (newRow) {
                    addEventListenersToRow(newRow);
                }
            }, 100); // Retardo para asegurarnos de que la fila se agregue antes de añadir los eventos
        }
    });

    // Actualizar los valores de total al cargar la página
    updateOverallTotal();
    
    // Encontrar el campo overall_total y hacer que sea de solo lectura
    const overallTotalField = document.querySelector('[data-name="overall_total"] input');
    overallTotalField.setAttribute('readonly', true);
});


jQuery(document).ready(function ($) {
    // Remueve cualquier evento previo para evitar duplicación.
    $('#enviar-propuesta').off('click').on('click', function (event) {
        event.preventDefault(); // Evita la recarga de la página.

        if (!confirm("¿Estás seguro de que deseas enviar esta propuesta?")) {
            return;
        }

        const postId = $(this).data('post-id');

        $.ajax({
            url: enviarPropuestaData.ajax_url,
            type: 'POST',
            data: {
                action: 'enviar_propuesta',
                post_id: postId,
                nonce: enviarPropuestaData.nonce
            },
            success: function (response) {
                if (response.success) {
                    alert(response.data.message); // Mensaje de éxito.
                    location.reload(); // Recarga la página después de enviar la propuesta.
                } else {
                    alert(response.data.message || 'Hubo un error al enviar la propuesta.');
                }
            },
            error: function () {
                alert('Error en la comunicación con el servidor.');
            }
        });
    });
});

document.addEventListener('DOMContentLoaded', () => {
    const tabs = document.querySelectorAll('.wpp-tabs-nav a');
    const contents = document.querySelectorAll('.wpp-tab-content');

    tabs.forEach((tab, index) => {
        tab.addEventListener('click', (e) => {
            e.preventDefault();

            // Desactivar todos los tabs y contenidos
            tabs.forEach((t) => t.classList.remove('active'));
            contents.forEach((c) => c.classList.remove('active'));

            // Activar el tab actual y su contenido
            tab.classList.add('active');
            contents[index].classList.add('active');
        });
    });

    // Activar el primer tab por defecto
    if (tabs.length > 0) {
        tabs[0].classList.add('active');
        contents[0].classList.add('active');
    }
});
