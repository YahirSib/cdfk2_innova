import Swal from "sweetalert2";
import 'jquery-ui-dist/jquery-ui.js';
import 'jquery-ui-dist/jquery-ui.css';
import notyf from '../../js/app.js';
import { each } from "jquery";

$(function () {

    let tabla = $('#tblSalas').DataTable({
        processing: true,
        serverSide: true,
        searching: true,
        paging: true,
        pageLength: 25,
        order: [[0, 'desc']],
        responsive: true,
        autoWidth: false,
        lengthChange: false,
        ajax: {
            url: $('meta[name="datatable"]').attr('content'),
            type: 'GET',
            data: function(d) {
                d.mes = $('#filtroMes').val(); // Enviamos el valor del filtro
            }
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
                if (data === "I") return 'Impreso';
                if (data === "Z") return 'Anulada';
                return data;
            }},
            { data: 'acciones', orderable: false, searchable: false }
        ],
        dom: 
            '<"flex justify-end items-center mb-4 "lf>' + // l = length, f = filter, alineados en la misma fila
            '<"overflow-x-auto border border-gray-200 rounded-lg"t>' +
            '<"flex justify-end mt-4"p>',


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

    $('#filtroMes').on('change', function() {
        tabla.ajax.reload();
    });



    $.get($('meta[name="meses-url"]').attr('content'), function(data) {
        data.forEach(function(mes) {
            $('#filtroMes').append(
                `<option value="${mes.mes}">${mes.nombre_mes}</option>`
            );
        });
    });


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
                                    title: 'Agrupación de Sala actualizada',
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
                        }, 1000);
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
                        notyf.error('Ocurrió un error al crear la agrupación de sala.');
                    }
                }
            });
        }
    });

    $(document).on('click', '.btn_eliminar', function(e) {
        e.preventDefault();
        var id = $(this).attr('data-id');
        Swal.fire({
            title: '¿Está seguro de eliminar esta agrupación de sala?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Eliminar'
        }).then((result) => {
            if (result.isConfirmed) {
                var baseDeleteUrl = $('meta[name="delete"]').attr('content');
                let finalUrl = baseDeleteUrl.replace('__ID__', id);
                $.ajax({
                    url: finalUrl,
                    type: 'DELETE',
                    success: function(response) {
                        if(response.success) {
                            notyf.success(response.message);
                            $('#tblSalas').DataTable().ajax.reload();
                        }else{
                            notyf.error(response.message);
                        }
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
        });
    });


    $(document).on('submit', '#frmAnexarSala', function(e) {
        e.preventDefault();
        var id_enca = $('#frmCrear').attr('data-id');
        var id_sala = $('#sala-anexar').attr('data-id');
        var cantidad = $('#cant-sala').val();
        $.ajax({
            url: $('meta[name="store_sala"]').attr('content'),
            type: 'POST',
            data: {
                id_enca: id_enca,
                id_sala: id_sala,
                cantidad: cantidad
            },
            success: function(response) {
                if(response.success) {
                    notyf.success(response.message);
                    $('#frmAnexarSala')[0].reset();
                    $('#sala-anexar').removeAttr('data-id');
                    // cargarDetallesNotaPieza($('#frmCrear').attr('data-id'));
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
    });

     $('#sala-anexar').autocomplete({
        source: function(request, response) {
            $.ajax({
                url: $('meta[name="salas"]').attr('content'),
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
                            id_sala: item.id
                        };
                    }));
                }
            });
        },
        minLength: 2,
        select: function(event, ui) {
            $(this).val(ui.item.value);
            $(this).attr('data-id', ui.item.id_sala);
            return false;
        },
        focus: function(event, ui) {
            $(this).val(ui.item.label);
            return false;
        }
    });

    function renderDetalle(id) {
        var url = $('meta[name="cargar_sala"]').attr('content').replace('__ID__', id);

        $.ajax({
            url: url,
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    let items = response.data.detalles;

                    let html = '';
                    $.each(items, function(index, item) {
                        let uid = 'accordion-' + Date.now() + '-' + index;

                        html += `
                            <div class="mb-4 border border-gray-200 rounded-lg">
                                <button
                                    type="button"
                                    class="accordion-button flex justify-between items-center w-full py-4 px-5 text-left font-medium text-gray-800 bg-white hover:bg-gray-700 hover:text-white transition-all"
                                    data-target="#${uid}-content"
                                    aria-expanded="false"
                                >
                                    <span class="font-bold">${item.sala.sala.nombre}</span>
                                    <div>
                                        <span class="inline-block bg-orange-100 text-orange-700 text-xs font-semibold px-2 py-0.5 rounded-full">
                                            0/${item.sala.unidades}
                                        </span>
                                    </div>
                                    <i class="bx bx-chevron-down w-4 h-4 shrink-0 transition-transform duration-300"></i>
                                </button>

                                <div
                                    id="${uid}-content"
                                    class="accordion-content hidden px-5 py-4 text-gray-700 border-t border-gray-200"
                                >
                                    <p class="text-sm text-gray-500">Sin piezas aún</p>
                                </div>
                            </div>
                        `;
                    });

                    $('#accordion').html(html);

                    // Activar comportamiento de acordeón
                    $('.accordion-button').off('click').on('click', function () {
                        const targetSelector = $(this).data('target');
                        const $target = $(targetSelector);
                        const isVisible = $target.is(':visible');

                        // Cerrar todos los acordeones
                        $('.accordion-content').slideUp();
                        $('.accordion-button').attr('aria-expanded', 'false')
                            .removeClass('bg-gray-700 text-white')
                            .addClass('bg-white text-gray-800');
                        $('.accordion-button i').removeClass('rotate-180');

                        // Si estaba cerrado, abrirlo y aplicar estilos de activo
                        if (!isVisible) {
                            $target.slideDown();
                            $(this).attr('aria-expanded', 'true')
                                .removeClass('bg-white text-gray-800')
                                .addClass('bg-gray-700 text-white');
                            $(this).find('i').addClass('rotate-180');
                        }
                    });

                } else {
                    notyf.error(response.message);
                }
            },
            error: function() {
                notyf.error('Error al cargar los detalles de la sala.');
            }
        });
    }

    if($('[name="action"]').attr('content') == "editar"){
        renderDetalle($('#frmCrear').attr('data-id'));
    }

});