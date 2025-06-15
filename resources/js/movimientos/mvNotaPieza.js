import Swal from "sweetalert2";
import 'jquery-ui-dist/jquery-ui.js';
import 'jquery-ui-dist/jquery-ui.css';
import notyf from '../../js/app.js';

$(function() {

    //CARGAR DETALLES DE PIEZAS DE LA NOTA DE PIEZA

    function cargarDetallesNotaPieza(id) {
        let baseDeleteUrl = $('meta[name="cargar_pieza"]').attr('content');
        let finalUrl = baseDeleteUrl.replace('__ID__', id);

        $.ajax({
            url: finalUrl,
            type: 'GET',
            success: function(response) {
                const detalles = response.detalles;
                var html = "";
                if (detalles.length > 0) {
                    $.each(detalles, function(index, detalle) {
                        html += `<div class="bg-white p-4 rounded-lg shadow-md flex items-center justify-between w-full md:w-[48%] lg:w-[32%]">
                                    <div>
                                        <h1 class="text-md font-semibold text-gray-800">${detalle.pieza.codigo} | ${detalle.pieza.nombre} </h1>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">-</button>
                                        <span class="text-gray-700 font-medium mx-2">${detalle.unidades}</span>
                                        <button class="bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600">+</button>
                                    </div>
                                </div>`;
                    });
                }else{
                    html = `<div class="bg-white p-4 rounded-lg shadow-md w-full text-center">
                                <p class="text-gray-500">No hay piezas anexadas a esta nota.</p>
                            </div>`;
                }

                $('#divPiezas').html(html);

                $('#totalPiezas').text(response.total);


            },error: function(xhr, status, error) {
                // Manejo de errores
                var responseText = xhr.responseText;
                if (xhr.status === 500) {
                    if(xhr.responseJSON.message){
                        responseText = xhr.responseJSON.message;
                    }else{
                        responseText = 'Error interno de sistema, contacte con soporte tecnico.';
                    }
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    html: responseText,
                    showConfirmButton: true,
                });
            }
        });
    }


    //CREACION DE LA NOTA DE PIEZA

    $('#frmCrear').on('submit', function(e) {
        e.preventDefault();
        var method = $(this).attr('method');
        var formData = new FormData(this);
        if(method == 'PUT'){
            Swal.fire({
                title: '¿Está seguro de actualizar el documento?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Actualizar'
            }).then((result) => {
                if (result.isConfirmed) {
                    var action = $('[name="update"]').attr('content');
                    //SI CONFIRMA SE ENVIA EL FORMULARIO
                    formData.append('id_movimiento', $('#frmCrear').attr('data-id'));
                    $.ajax({
                        url: action,
                        type: 'POST',
                        data: formData,
                        contentType: false,
                        processData: false,
                        success: function(response) {
                            if(response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Nota de Pieza actualizada',
                                    text: response.message,
                                    showConfirmButton: true,
                                    timer: 1500
                                });
                            }else{
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: response.message,
                                    showConfirmButton: true,
                                });
                            }
                        },error: function(xhr, status, error) {
                            // Manejo de errores
                            var responseText = xhr.responseText;
                            if (xhr.status === 422) {
                                // Validación fallida
                                var errors = JSON.parse(responseText);
                                var errorMessage = '';
                                $.each(errors.errors, function(key, value) {
                                    errorMessage += value[0] + "<br>";
                                });
                                responseText = errorMessage;
                            } else if (xhr.status === 500) {
                                if(xhr.responseJSON.message){
                                    responseText = xhr.responseJSON.message;
                                }else{
                                    responseText = 'Error interno de sistema, contacte con soporte tecnico.';
                                }
                            }
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                html: responseText,
                                showConfirmButton: true,
                            });
                        }
                    });
                }
            });
            return;
        }else{
            var action = $('[name="store"]').attr('content');
            $.ajax({
                type: 'POST',
                url: action,
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if(response.success){
                        notyf.success(response.message);
                        setTimeout(function() {
                            window.location.href = response.redirect;
                        }
                        , 1000);
                    }else{
                        notyf.error(response.message);
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON.errors;
                        let errorMessage = '';
                        $.each(errors, function(key, value) {
                            errorMessage += value[0] + '<br>';
                        });
                        Swal.fire({
                            icon: 'error',
                            title: 'Error de validación',
                            html: errorMessage
                        });
                    } else {
                        notyf.error('Ocurrió un error al crear la nota de pieza.');
                    }
                }
            });
        }
    });


    //datatable de notas de piezas

    //cargar tabla de salas
    $('#tblPiezas').DataTable({
        processing: true,
        serverSide: true,
        searching: true,
        paging: true,
        pageLength: 25,
        order: [[0, 'desc']],
        responsive: true,
        autoWidth: false,
        ajax: {
            url: $('meta[name="datatable"]').attr('content'),
            type: 'GET',
        },
        language: {
            searchPlaceholder: "Buscar...",
            processing: "Cargando...",
            search: "" // Oculta el texto "Search"
        },
        columns: [
            { data: 'correlativo' },
            { data: 'nombre_cacastero' },
            { data: 'fecha_ingreso' },
            { data: 'estado', render: function(data, type, row) {
                if (data === "A") return 'Activo';
                if (data === "X") return 'Impreso';
                return data;
            }},
            { data: 'acciones', orderable: false, searchable: false }
        ],
        dom: 
            '<"flex justify-end mb-4"f>' + // Buscador alineado a la derecha
            '<"overflow-x-auto border border-gray-200 rounded-lg"t>' + // Tabla con borde y scroll
            '<"flex justify-end mt-4"p>', // Paginación alineada a la derecha

        drawCallback: function() {
            // Estilos para el input de búsqueda
            $('div.dataTables_filter input')
                .addClass('bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5')
                .attr('placeholder', 'Buscar...');
            
            // Eliminar el label del filtro (deja solo el input)
            $('div.dataTables_filter label').contents().filter(function() {
                return this.nodeType === 3;
            }).remove();
        }
    });


    $('#pieza_sala').autocomplete({
        source: function(request, response) {
            $.ajax({
                url: $('meta[name="piezas"]').attr('content'),
                type: 'POST',
                dataType: 'json',
                data: {
                    val: request.term
                },
                success: function(data) {
                    response($.map(data, function(item) {
                        return {
                            label: item.text,
                            value: item.text,
                            id_piezas: item.id
                        };
                    }));
                }
            });
        },
        minLength: 2,
        select: function(event, ui) {
            $(this).val(ui.item.value);
            $(this).attr('data-id', ui.item.id_piezas);
            return false;
        },
        focus: function(event, ui) {
            $(this).val(ui.item.label);
            return false;
        }
    });

    $(document).on('submit', '#frmAnexarPieza', function(e) {
        e.preventDefault();
        var id_enca = $('#frmCrear').attr('data-id');
        var id_pieza = $('#pieza_sala').attr('data-id');
        var cantidad = $('#cantidad').val();
        $.ajax({
            url: $('meta[name="store_pieza"]').attr('content'),
            type: 'POST',
            data: {
                id_enca: id_enca,
                id_pieza: id_pieza,
                cantidad: cantidad
            },
            success: function(response) {
                if(response.success) {
                    notyf.success(response.message);
                    $('#frmAnexarPieza')[0].reset();
                    $('#pieza_sala').removeAttr('data-id');
                    cargarDetallesNotaPieza($('#frmCrear').attr('data-id'));
                }else{
                    notyf.error(response.message);
                }
            },error: function(xhr, status, error) {
                // Manejo de errores
                var responseText = xhr.responseText;
                if (xhr.status === 422) {
                    // Validación fallida
                    var errors = JSON.parse(responseText);
                    var errorMessage = '';
                    $.each(errors.errors, function(key, value) {
                        errorMessage += value[0] + "<br>";
                    });
                    responseText = errorMessage;
                } else if (xhr.status === 500) {
                    if(xhr.responseJSON.message){
                        responseText = xhr.responseJSON.message;
                    }else{
                        responseText = 'Error interno de sistema, contacte con soporte tecnico.';
                    }
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    html: responseText,
                    showConfirmButton: true,
                });
            }
        });
    }
    );

    if($('[name="action"]').attr('content') == "editar"){
        cargarDetallesNotaPieza($('#frmCrear').attr('data-id'));
    }

});
