import Swal from "sweetalert2";
import 'jquery-ui-dist/jquery-ui.js';
import 'jquery-ui-dist/jquery-ui.css';
import notyf from '../../js/app.js';


$(function() {

    //CARGAR DETALLES DE PIEZAS DE LA NOTA DE PIEZA

    $(".card-detalle").on("click", function() {
        let target = $(this).data("target"); // tomamos el id del form a mostrar

        // Ocultar todos los formularios
        $("#frmAnexarPieza, #frmAnexarSala").addClass("hidden");

        // Mostrar el form correspondiente
        $("#" + target).removeClass("hidden");

        // Quitar selección previa de todas las cards
        $(".card-detalle").removeClass("ring-2 ring-green-500 bg-green-50");

        // Resaltar la card activa
        $(this).addClass("ring-2 ring-green-500 bg-green-50");
    });

    function cargarDetallesNotaPieza(id) {
        let baseDeleteUrl = $('meta[name="cargar_pieza"]').attr('content');
        let finalUrl = baseDeleteUrl.replace('__ID__', id);

        $.ajax({
            url: finalUrl,
            type: 'GET',
            success: function(response) {
                const detalles = response.data;
                var htmlPieza = "";
                var htmlSala = "";
                if (detalles.length > 0) {
                    $.each(detalles, function(index, detalle) {
                        if(detalle.fk_pieza == null){
                            htmlSala += `<div class="bg-white p-4 rounded-lg shadow-md flex items-center justify-between w-full md:w-[48%] lg:w-[32%]">
                                    <div class="flex flex-col w-full">
                                        <h3 class="text-sm text-gray-500">${detalle.sala.codigo}</h3>
                                        <h1 class="text-md font-semibold text-gray-800">${detalle.sala.nombre} </h1>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 btnQuitar" data-id="${detalle.id_detalle}" data-type="sala">-</button>
                                        <input readonly class="txtCant${detalle.id_detalle} bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 text-center" value="${detalle.unidades}">
                                        <button class="bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600 btnAgregar" data-id="${detalle.id_detalle}" data-type="sala">+</button>
                                    </div>
                                </div>`;
                        }else{
                            htmlPieza += `<div class="bg-white p-4 rounded-lg shadow-md flex items-center justify-between w-full md:w-[48%] lg:w-[32%]">
                                    <div class="flex flex-col w-full">
                                        <h3 class="text-sm text-gray-500">${detalle.pieza.codigo}</h3>
                                        <h1 class="text-md font-semibold text-gray-800">${detalle.pieza.nombre} </h1>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 btnQuitar" data-id="${detalle.id_detalle}" data-type="pieza">-</button>
                                        <input readonly class="txtCant${detalle.id_detalle} bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 text-center" value="${detalle.unidades}">
                                        <button class="bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600 btnAgregar" data-id="${detalle.id_detalle}" data-type="pieza">+</button>
                                    </div>
                                </div>`;
                        }

                        
                    });
                }else{
                    htmlPieza = `<div class="bg-white p-4 rounded-lg shadow-md w-full text-center">
                                <p class="text-gray-500">No hay detalles anexados a este traslado.</p>
                            </div>`;
                }

                $('#divPiezas').html(htmlPieza);
                $('#divSalas').html(htmlSala);

                console.log(response.total_pieza);

                $('#totalPiezas').text(response.total_pieza);
                $('#totalSalas').text(response.total_sala);


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
                                    title: 'Traslado actualizado',
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
                        notyf.error('Ocurrió un error al crear el traslado.');
                    }
                }
            });
        }
    });


    //datatable de notas de piezas

    let tabla = $('#tblTrasladoTapiceria').DataTable({
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



    $('#piezas').autocomplete({
        source: function(request, response) {
            $.ajax({
                url: $('meta[name="piezas"]').attr('content'),
                type: 'POST',
                dataType: 'json',
                data: {
                    val: request.term,
                    id_trabajador : $('#cacastero').val(),
                    individual: 1
                },
                success: function(data) {
                    response($.map(data, function(item) {
                        return {
                            label: item.text + ' ( Disp: ' + item.disponibilidad + ' )',
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
            $(this).val(ui.item.value);
            return false;
        }
    });

    $('#salas').autocomplete({
        source: function(request, response) {
            $.ajax({
                url: $('meta[name="salas"]').attr('content'),
                type: 'POST',
                dataType: 'json',
                data: {
                    val: request.term,
                    id_trabajador : $('#cacastero').val()
                },
                success: function(data) {
                    response($.map(data, function(item) {
                        return {
                            label: item.text + ' ( Disp: ' + item.disponibilidad + ' )',
                            value: item.text,
                            id_salas: item.id
                        };
                    }));
                }
            });
        },
        minLength: 2,
        select: function(event, ui) {
            $(this).val(ui.item.value);
            $(this).attr('data-id', ui.item.id_salas);
            return false;
        },
        focus: function(event, ui) {
            $(this).val(ui.item.value);
            return false;
        }
    });

    $(document).on('submit', '#frmAnexarPieza', function(e) {
        e.preventDefault();
        var id_enca = $('#frmCrear').attr('data-id');
        var id_pieza = $('#piezas').attr('data-id');
        var cantidad = $('#cantidad_piezas').val();
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
                    $('#piezas').removeAttr('data-id');
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

    $(document).on('submit', '#frmAnexarSala', function(e) {
        e.preventDefault();
        var id_enca = $('#frmCrear').attr('data-id');
        var id_sala = $('#salas').attr('data-id');
        var cantidad = $('#cantidad_salas').val();
        $.ajax({
            url: $('meta[name="store_pieza"]').attr('content'),
            type: 'POST',
            data: {
                id_enca: id_enca,
                id_salas: id_sala,
                cantidad: cantidad
            },
            success: function(response) {
                if(response.success) {
                    notyf.success(response.message);
                    $('#frmAnexarSala')[0].reset();
                    $('#salas').removeAttr('data-id');
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

    //quitar detalles de la nota de pieza

    $(document).on('click', '.btnQuitar', function(e) {
        e.preventDefault();
        var id_detalle = $(this).attr('data-id');
        var txtCant = $('.txtCant' + id_detalle).val();
        var tipo = $(this).attr('data-type');

        if(txtCant <= 1) {
            Swal.fire({
                title: '¿Está seguro de quitar esta pieza?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Quitar'
            }).then((result) => {
                if (result.isConfirmed) {
                    let baseDeleteUrl = $('meta[name="delete_pieza"]').attr('content');
                    let finalUrl = baseDeleteUrl.replace('__ID__', id_detalle);
                    $.ajax({
                        url: finalUrl,
                        type: 'DELETE',
                        data: {
                            tipo: tipo
                        },
                        success: function(response) {
                            if(response.success) {
                                notyf.success(response.message);
                                cargarDetallesNotaPieza($('#frmCrear').attr('data-id'));
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
        }else{
            let baseDeleteUrl = $('meta[name="update_pieza"]').attr('content');
            let finalUrl = baseDeleteUrl.replace('__ID__', id_detalle);
            finalUrl = finalUrl.replace('__CANT__', parseInt(txtCant) - 1);
            var tipo = $(this).attr('data-type');

            $.ajax({
                url: finalUrl,
                type: 'PUT',
                data: {
                    tipo: tipo
                },
                success: function(response) {
                    if(response.success) {
                        notyf.success(response.message);
                        cargarDetallesNotaPieza($('#frmCrear').attr('data-id'));
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
    

    $(document).on('click', '.btnAgregar', function(e) {
        e.preventDefault();
        var id_detalle = $(this).attr('data-id');
        var txtCant = $('.txtCant' + id_detalle).val();

        let baseUpdateUrl = $('meta[name="update_pieza"]').attr('content');
        let finalUrl = baseUpdateUrl.replace('__ID__', id_detalle);
        finalUrl = finalUrl.replace('__CANT__', parseInt(txtCant) + 1);
        var tipo = $(this).attr('data-type');

        $.ajax({
            url: finalUrl,
            type: 'PUT',
            data: {
                tipo: tipo
            },
            success: function(response) {
                if(response.success) {
                    notyf.success(response.message);
                    cargarDetallesNotaPieza($('#frmCrear').attr('data-id'));
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
    });

    $(document).on('click', '.btn_eliminar', function(e) {
        e.preventDefault();
        var id = $(this).attr('data-id');
        Swal.fire({
            title: '¿Está seguro de eliminar este traslado?',
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
                            $('#tblTrasladoTapiceria').DataTable().ajax.reload();
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
    }
    );

    $(document).on('click', '.btn_imprimir', function(e) {
        e.preventDefault();
        var id = $(this).attr('data-id');
        Swal.fire({
            title: '¿Está seguro de imprimir este traslado?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Imprimir'
        }).then((result) => {
            if (result.isConfirmed) {
                var basePrintUrl = $('meta[name="print"]').attr('content');
                let finalUrl = basePrintUrl.replace('__ID__', id);
                window.open(finalUrl, '_blank');
            }
        });
    });

    $(document).on('click', '#btnImprimirPre', function(e) {

        e.preventDefault();
        var id = $('#frmCrear').attr('data-id');
        var basePrintUrl = $('meta[name="print"]').attr('content');
        let finalUrl = basePrintUrl.replace('__ID__', id);
        // Abrir como ventana emergente
        let popup = window.open(finalUrl, 'popup', 'width=800,height=600,scrollbars=yes,resizable=yes');

        if (!popup || popup.closed || typeof popup.closed === 'undefined') {
            Swal.fire('Error', 'El navegador bloqueó la ventana emergente.', 'error');
            return;
        }
    });

    $(document).on('click', '#btnImprimir', function(e) {
        e.preventDefault();

        Swal.fire({
            title: '¿Está seguro de imprimir este traslado?',
            text: 'Esta acción generará un documento PDF con los detalles del traslado que no se podrá revertir.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Imprimir'
        }).then((result) => {
            if (result.isConfirmed) {
                var id = $('#frmCrear').attr('data-id');
                var basePrintUrl = $('meta[name="print_real"]').attr('content');
                var redirectUrl = $('meta[name="redirect"]').attr('content'); // Asegúrate que este meta exista
                let finalUrl = basePrintUrl.replace('__ID__', id);

                // Abrir como ventana emergente
                let popup = window.open(finalUrl, 'popup', 'width=800,height=600,scrollbars=yes,resizable=yes');

                if (!popup || popup.closed || typeof popup.closed === 'undefined') {
                    Swal.fire('Error', 'El navegador bloqueó la ventana emergente.', 'error');
                    return;
                }

                // Redirige luego de un pequeño retraso
                setTimeout(() => {
                    window.location.href = redirectUrl;
                }, 1000); // Puedes ajustar el tiempo si lo deseas
            }
        });
    });


    $(document).on( 'click' ,'.btnReporte', function(e){
        e.preventDefault();
        
        var estado = $(this).attr('data-estado');
        var id = $(this).attr('data-id');

        var basePrintUrl = $('meta[name="print_historico"]').attr('content');
        let finalUrl = basePrintUrl.replace('__ID__', id);
        // Abrir como ventana emergente
        let popup = window.open(finalUrl, 'popup', 'width=800,height=600,scrollbars=yes,resizable=yes');

        if (!popup || popup.closed || typeof popup.closed === 'undefined') {
            Swal.fire('Error', 'El navegador bloqueó la ventana emergente.', 'error');
            return;
        }

        
    });

    $(document).on('click', '.btnAnular', function(e){
        e.preventDefault();
        var id = $(this).attr('data-id');
        Swal.fire({
            title: '¿Está seguro de anular este traslado?',
            text: 'Esta acción generará un documento PDF de anulación con los detalles del traslado que no se podrá revertir.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Anular'
        }).then((result) => {
            if (result.isConfirmed) {
                var basePrintUrl = $('meta[name="print_anulada"]').attr('content');
                let finalUrl = basePrintUrl.replace('__ID__', id);
                // Abrir como ventana emergente
                let popup = window.open(finalUrl, 'popup', 'width=800,height=600,scrollbars=yes,resizable=yes');

                $('#tblTrasladoTapiceria').DataTable().ajax.reload();

                if (!popup || popup.closed || typeof popup.closed === 'undefined') {
                    Swal.fire('Error', 'El navegador bloqueó la ventana emergente.', 'error');
                    return;
                }
            }
        }); 
    });

});
