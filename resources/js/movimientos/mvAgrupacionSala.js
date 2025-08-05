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
                    renderDetalle(id_enca);
                    renderResumen(id_enca);
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

    function renderResumen(id) {
        var url = $('meta[name="resumen"]').attr('content').replace('__ID__', id);
        var html = '';
        $.ajax({
            url: url,
            type: 'GET',
            success: function(response) {
                if (response.success) {

                    html += `
                        <div class="w-full mb-6">
                            <table class="w-full text-sm text-left border border-gray-200 rounded-lg overflow-hidden shadow-sm">
                                <thead class="bg-gray-100 text-gray-700">
                                    <tr class="bg-gray-700 text-white">
                                        <th colspan="5" class="px-4 py-3 text-base font-semibold border-b border-gray-300">Entrada de Sala - Resumen</th>
                                    </tr>
                                    <tr class="text-xs text-gray-600 uppercase">
                                        <th class="px-4 py-2 border-b border-gray-200">Código</th>
                                        <th class="px-4 py-2 border-b border-gray-200">Sala</th>
                                        <th class="px-4 py-2 border-b border-gray-200 text-center">Cantidad</th>
                                        <th class="px-4 py-2 border-b border-gray-200 text-right">Costo</th>
                                        <th class="px-4 py-2 border-b border-gray-200 text-right">Costo Total</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white">
                    `;

                    $.each(response.data.detalles_entrada, function(index, item) {
                        html += `
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border-b border-gray-100">${item.sala.codigo}</td>
                                <td class="px-4 py-2 border-b border-gray-100">${item.sala.nombre}</td>
                                <td class="px-4 py-2 border-b border-gray-100 text-center">${item.unidades}</td>
                                <td class="px-4 py-2 border-b border-gray-100 text-right">${item.costo_unitario ? item.costo_unitario : '0.00'}</td>
                                <td class="px-4 py-2 border-b border-gray-100 text-right">${item.costo_total ? item.costo_total : '0.00'}</td>
                            </tr>
                        `;
                    });

                    console.log(response.data.entrada);

                    html += `
                                    <tr>
                                        <td colspan="4" class="px-4 py-2 border-b border-gray-100 text-right font-semibold text-xl">Total:</td>
                                        <td class="px-4 py-2 border-b border-gray-100 text-right text-xl">${response.data.entrada.total ? response.data.entrada.total : '0.00'}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="w-full">
                            <table class="w-full text-sm text-left border border-gray-200 rounded-lg overflow-hidden shadow-sm">
                                <thead class="bg-gray-100 text-gray-700">
                                    <tr class="bg-gray-700 text-white">
                                        <th colspan="5" class="px-4 py-3 text-base font-semibold border-b border-gray-300">Salida de Piezas - Resumen</th>
                                    </tr>
                                    <tr class="text-xs text-gray-600 uppercase">
                                        <th class="px-4 py-2 border-b border-gray-200">Código</th>
                                        <th class="px-4 py-2 border-b border-gray-200">Pieza</th>
                                        <th class="px-4 py-2 border-b border-gray-200 text-center">Cantidad</th>
                                        <th class="px-4 py-2 border-b border-gray-200 text-right">Costo</th>
                                        <th class="px-4 py-2 border-b border-gray-200 text-right">Costo Total</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white">
                    `;

                    $.each(response.data.detalles_salida, function(index, item) {
                        html += `
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border-b border-gray-100">${item.pieza.codigo}</td>
                                <td class="px-4 py-2 border-b border-gray-100">${item.pieza.nombre}</td>
                                <td class="px-4 py-2 border-b border-gray-100 text-center">${item.unidades}</td>
                                <td class="px-4 py-2 border-b border-gray-100 text-right">${item.costo_unitario ? item.costo_unitario : '0.00'}</td>
                                <td class="px-4 py-2 border-b border-gray-100 text-right">${item.costo_total ? item.costo_total : '0.00'}</td>
                            </tr>
                        `;
                    });

                    html += `
                                    <tr>
                                        <td colspan="4" class="px-4 py-2 border-b border-gray-100 text-right font-semibold text-xl">Total:</td>
                                        <td class="px-4 py-2 border-b border-gray-100 text-right text-xl">${response.data.salida.total ? response.data.salida.total : '0.00'}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    `;
                    $('#resumen_sala').html(html);

                } else {
                    notyf.error(response.message);
                }
            },error: function() {
                notyf.error('Error al cargar el resumen de la sala.');
            }
        });
    }

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

                        var colorSpan = '';
                        var status = false;

                        if(item.armadas == item.sala.unidades) {
                            colorSpan = 'bg-green-100 text-green-700';
                            status = true;
                        } else {
                            colorSpan = 'bg-orange-100 text-orange-700';
                            status = false;
                        }

                        html += `
                            <div class="w-full md:w-[calc(33%-0.5rem)] border border-gray-200 rounded-lg">
                                <button
                                    type="button"
                                    class="accordion-button grid grid-cols-6 gap-2 w-full py-4 px-5 text-left font-medium bg-gray-700 text-white hover:bg-gray-700 hover:text-white transition-all"
                                    data-target="#${uid}-content"
                                    aria-expanded="true"
                                    data-id="${item.sala.id_detalle}"
                                    data-status="${status}"
                                >
                                    <div class="flex flex-col items-start justify-start col-span-3">
                                        <span class="font-bold">${item.sala.sala.nombre}</span>
                                        <span class="font-semibold">${item.sala.sala.codigo}</span>
                                    </div>
                                    <div class="flex items-center justify-center">
                                        <span class="inline-block ${colorSpan} text-xs font-semibold px-2 py-0.5 rounded-full">
                                            <span class="completo-${item.sala.id_detalle}"></span>${item.armadas}/${item.sala.unidades}
                                        </span>
                                    </div>
                                    <div class="flex items-center justify-end z-20">
                                        <a type="button" class="btn_eliminar_det px-2 py-1 rounded-lg bg-red-500 text-white hover:bg-red-700" data-id="${item.sala.id_detalle}"><i class='bx bx-trash text-lg'></i></a>
                                    </div>
                                    <div class="flex items-center justify-end">
                                        <i class="bx bx-chevron-down w-4 h-4 shrink-0 transition-transform duration-300 rotate-180"></i>
                                    </div>
                                </button>

                                <div
                                    id="${uid}-content"
                                    class="accordion-content px-5 py-4 text-gray-700 border-t border-gray-200"
                                >`;

                                var iteracion = 0;
                                var loopCount = item.requeridos.length;
                                var cumplePiezas = false;

                                item.requeridos.forEach(element => {
                                    let disponibilidad = response.data.disponibilidad[element.id_pieza] ? response.data.disponibilidad[element.id_pieza].disponibilidad : 0;

                                    var salidas = item.salidas.find(req => req.fk_pieza === element.id_pieza);

                                    let cantidadObligatoria = element.cantidad;

                                    let cantidadFinal = cantidadObligatoria * item.sala.unidades;

                                    if( salidas && cantidadFinal == salidas.unidades) {
                                        var coloresUnidades = `bg-blue-100 text-blue-700`;
                                        var coloreIcon = `text-blue-500`; 
                                    }else{
                                        var coloresUnidades = `bg-red-100 text-red-700`;
                                        var coloreIcon = `text-red-500`;
                                    }

                                    if(iteracion != loopCount - 1) {
                                        var border = `border-b mb-2 border-gray-200 pb-2`;
                                    }else{
                                        var border = `mb-2`;
                                    }

                                     html += `
                                    <div class="grid grid-cols-4 items-center gap-2 text-sm ${border}" data-id="${salidas ? salidas.id_detalle : ''}">
                                        <!-- Código y nombre -->
                                        <div class="flex flex-col items-start col-span-1">
                                            <span class="text-gray-800 font-semibold">${element.codigo}</span>
                                            <span class="text-gray-500 text-xs">${element.nombre}</span>
                                        </div>

                                        <!-- Disponible -->
                                        <div class="flex items-center justify-center gap-1 col-span-1" title="Disponible">
                                            <i class='bx bx-check-circle text-green-500 text-base'></i>
                                            <span class="inline-block bg-green-100 text-green-700 text-xs font-semibold px-2 py-0.5 rounded-full">
                                                ${disponibilidad}
                                            </span>
                                        </div>

                                        <!-- Solicitada -->
                                        <div class="flex items-center justify-center gap-1 col-span-1" title="Solicitada">
                                            <i class='bx bx-package ${coloreIcon} text-base'></i>
                                            <span class="inline-block ${coloresUnidades} text-xs font-semibold px-2 py-0.5 rounded-full">
                                                ${salidas ? salidas.unidades : 0}/${cantidadFinal}
                                            </span>
                                        </div>

                                        <!-- Botones + y - -->
                                        <div class="flex items-center gap-2 justify-end col-span-1">
                                            <button type="button"
                                                class="w-8 h-8 flex items-center justify-center bg-gray-200 hover:bg-gray-300 text-gray-700 rounded text-lg font-bold restar" data-id="${salidas ? salidas.id_detalle : ''}" data-pieza="${element.id_pieza}" data-entra-detalle="${item.sala.id_detalle}">
                                                <i class='bx bx-minus'></i>
                                            </button>

                                            <button type="button"
                                                class="w-8 h-8 flex items-center justify-center bg-gray-200 hover:bg-gray-300 text-gray-700 rounded text-lg font-bold sumar" data-id="${salidas ? salidas.id_detalle : ''}" data-pieza="${element.id_pieza}" data-entra-detalle="${item.sala.id_detalle}">
                                                <i class='bx bx-plus'></i>
                                            </button>
                                        </div>
                                    </div>`;
                                    iteracion++;
                                });


                            html += `        
                                </div>
                            </div>
                        `;

                    });

                    $('#accordion').html(html);

                    // Activar comportamiento de acordeón
                    $('.accordion-button').off('click').on('click', function (e) {

                        if ($(e.target).closest('.btn_eliminar_det').length > 0) {
                            return; // No ejecutar el toggle
                        }

                        const targetSelector = $(this).data('target');
                        const $target = $(targetSelector);
                        const isVisible = $target.is(':visible');

                       if (isVisible) {
                            $target.slideUp();
                            $(this).attr('aria-expanded', 'false')
                                .removeClass('bg-gray-700 text-white')
                                .addClass('bg-white text-gray-800');
                            $(this).find('i.bx-chevron-down').removeClass('rotate-180');
                        } else {
                            $target.slideDown();
                            $(this).attr('aria-expanded', 'true')
                                .removeClass('bg-white text-gray-800')
                                .addClass('bg-gray-700 text-white');
                            $(this).find('i.bx-chevron-down').addClass('rotate-180');
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
        renderResumen($('#frmCrear').attr('data-id'));
    }

    $(document).on('click', '.sumar', function() {
        var id = $(this).attr('data-id');
        var pieza = $(this).attr('data-pieza');
        var detalle = $(this).attr('data-entra-detalle');
        $.ajax({
            url: $('meta[name="sumar"]').attr('content'),
            type: 'POST',
            data: {
                id: id,
                pieza: pieza,
                detalle: detalle
            },
            success: function(response) {
                if(response.success) {
                    notyf.success(response.message);
                    renderDetalle($('#frmCrear').attr('data-id'));
                    renderResumen($('#frmCrear').attr('data-id'));
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

    $(document).on('click', '.restar', function() {
        var id = $(this).attr('data-id');
        var pieza = $(this).attr('data-pieza');
        var detalle = $(this).attr('data-entra-detalle');
        $.ajax({
            url: $('meta[name="restar"]').attr('content'),
            type: 'POST',
            data: {
                id: id,
                pieza: pieza,
                detalle: detalle
            },
            success: function(response) {
                if(response.success) {
                    notyf.success(response.message);
                    renderDetalle($('#frmCrear').attr('data-id'));
                    renderResumen($('#frmCrear').attr('data-id'));
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

    $(document).on('click', '.btn_eliminar_det', function (e) {
        e.preventDefault();
        var id = $(this).attr('data-id');
        Swal.fire({
            title: '¿Está seguro de eliminar este detalle de sala?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Eliminar'
        }).then((result) => { 
            if(result.isConfirmed) {
                var baseDeleteUrl = $('meta[name="eliminar-detalle"]').attr('content');
                let finalUrl = baseDeleteUrl.replace('__ID__', id);
                $.ajax({
                    url: finalUrl,
                    type: 'DELETE',
                    success: function(response) {
                        if(response.success) {
                            notyf.success(response.message);
                            renderDetalle($('#frmCrear').attr('data-id'));
                            renderResumen($('#frmCrear').attr('data-id'));
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

});