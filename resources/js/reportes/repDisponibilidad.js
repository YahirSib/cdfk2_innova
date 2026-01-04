import $ from 'jquery';

$(function () {

    // 1. Funcionalidad de Marcar/Desmarcar todos
    $('.btn-toggle-all').on('click', function() {
        const target = $(this).data('target');
        const checkboxes = $(`.chk-${target}`);
        const allChecked = checkboxes.length === checkboxes.filter(':checked').length;
        
        checkboxes.prop('checked', !allChecked).trigger('change'); // trigger change para limpiar errores
        $(this).text(allChecked ? 'Marcar todos' : 'Desmarcar todos');
    });

    // 2. Limpiar error al hacer click
    $('input[type="checkbox"]').on('change', function() {
        const groupName = $(this).attr('name'); // 'tipo[]' o 'estado[]'
        // Detectar si es tipo o estado quitando los corchetes si es necesario o buscando substring
        const target = groupName.includes('tipo') ? 'tipo' : 'bodega';
        
        if($(`input[name="${groupName}"]:checked`).length > 0) {
            $(`#error-${target}`).addClass('hidden');
        }
    });

    // 3. Submit del formulario
    $(document).on('submit', '#formReporteDisponibilidad', function(e){
        let isValid = true;

        // Validar Tipo
        if ($('input[name="tipo[]"]:checked').length === 0) {
            $('#error-tipo').removeClass('hidden');
            isValid = false;
        } else {
            $('#error-tipo').addClass('hidden');
        }

        // Validar Bodega
        if ($('input[name="estado[]"]:checked').length === 0) {
            $('#error-bodega').removeClass('hidden');
            isValid = false;
        } else {
            $('#error-bodega').addClass('hidden');
        }

        if (!isValid) {
            e.preventDefault(); // Detenemos el envío solo si hay error
            
            $('html, body').animate({
                scrollTop: $(".text-red-500:visible").first().offset().top - 100
            }, 500);
        }
        
        // NOTA: Si es válido, NO hacemos preventDefault() ni window.open().
        // Dejamos que el form con target="_blank" haga el POST nativo.
    });
});