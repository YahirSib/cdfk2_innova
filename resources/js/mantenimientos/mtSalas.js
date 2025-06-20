import Swal from "sweetalert2";
import 'jquery-ui-dist/jquery-ui.js';
import 'jquery-ui-dist/jquery-ui.css';
import notyf from '../../js/app.js';


$(function() {

    function limpiarFormulario() {
        $('#frmSalas')[0].reset();
        $('#tblSalas').DataTable().ajax.reload();
    }
    
    //Guardado o edicion de salas
    $('#frmSalas').on('submit', function(e) {
        e.preventDefault();
        var method = $(this).attr('method');
        var action = $(this).attr('action');
        var formData = new FormData(this);
        formData.append('_method', method);
        //CUANDO EL METODO ES PUT SE ACTUALIZA EL TRABAJADOR
        if(method == 'PUT'){
            Swal.fire({
                title: '¿Está seguro de actualizar la sala?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Actualizar'
            }).then((result) => {
                if (result.isConfirmed) {
                    //SI CONFIRMA SE ENVIA EL FORMULARIO
                    formData.append('id_salas', $('#frmSalas').attr('data_id'));
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
                                    title: 'Sala actualizada',
                                    text: response.message,
                                    showConfirmButton: true,
                                    timer: 1500
                                }).then(() => {
                                    $("#btnCrear").click();
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
                }else{
                    $("#btnCrear").click();
                }
            });
            return;

        }else{
            //SI EL METODO ES POST SE CREA UN NUEVA PIEZA
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
                            title: 'Sala guardada',
                            text: response.message,
                            showConfirmButton: true,
                            timer: 1500
                        }).then(() => {
                            limpiarFormulario();
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
    })

    //Eliminar trabajador
    $(document).on('click', '#btn_eliminar', function(e) {
        e.preventDefault();
        var id = $(this).attr('data_id');
        let baseDeleteUrl = $('meta[name="delete"]').attr('content');
        let finalUrl = baseDeleteUrl.replace('__ID__', id);
        Swal.fire({
            title: '¿Está seguro de eliminar la sala?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Eliminar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: finalUrl,
                    type: 'DELETE',
                    success: function(response) {
                        if(response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Sala eliminada',
                                text: response.message,
                                showConfirmButton: true,
                                timer: 1500
                            }).then(() => {
                                limpiarFormulario();
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
        }
        );
    });

    //cargar tabla de salas
    $('#tblSalas').DataTable({
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
            { data: 'id_salas' },
            { data: 'codigo' },
            { data: 'nombre' },
            { data: 'estado', render: function(data, type, row) {
                if (data === 1) return 'Activo';
                if (data === 2) return 'Inactivo';
                return data;
            }},
            { data: 'existencia', },
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


    //cargar informacion de la sala al hacer click en el boton editar    
    $(document).on('click', '#btn_editar', function(e) {
        e.preventDefault();
        
        var id = $(this).attr('data_id');
        let baseEditUrl = $('meta[name="edit"]').attr('content');
        let finalUrl = baseEditUrl.replace('__ID__', id);

        $.ajax({
            url: finalUrl, 
            type: 'GET',
            success: function(response) {
                if (response.success) {

                    //Cargar datos en el formulario
                    $('#codigo').val(response.data.codigo);
                    $('#codigo').attr('readonly', true);
                    $('#nombre').val(response.data.nombre);
                    $('#estado').val(response.data.estado);
                    $('#costo_tapicero').val(response.data.costo_tapicero);
                    $('#costo_cacastero').val(response.data.costo_cacastero);
                    $('#existencia').val(response.data.existencia);
                    $('#descripcion').val(response.data.descripcion);

                    //Cambio form para editar
                    $('#frmSalas').attr('action', $('meta[name="update"]').attr('content'));
                    $('#frmSalas').attr('data_id', id);
                    $('#frmSalas').attr('method', 'PUT');
                    $('#btnForm').html('Actualizar');
                    $('#btnCrear').removeClass('hidden');

                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message,
                        showConfirmButton: true,
                    });
                }
            },
            error: function(xhr, status, error) {
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
        })
    });

    $("#btnCrear").on('click', function(e) {
        e.preventDefault();
        $('#frmSalas').attr('action', $('meta[name="store"]').attr('content'));
        $('#frmSalas').attr('method', 'POST');
        $('#frmSalas').removeAttr('data_id');
        $('#btnForm').html('Guardar');
        $('#btnCrear').addClass('hidden');
        $('#codigo').focus();
        $('#codigo').attr('readonly', false);
        limpiarFormulario();
    }); 

    //modal con datos de la sala general
    $(document).on('click', '#btn_ver', function () {
        const id = $(this).attr('data_id');
        let baseEditUrl = $('meta[name="edit"]').attr('content');
        let finalUrl = baseEditUrl.replace('__ID__', id);
        $.ajax({
            url: finalUrl,
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    const sala = response.data;
                    const estadoTexto = sala.estado == 1 ? 'Activo' : sala.tipo == 2 ? 'Inactivo' : 'Desconocido';

                    Swal.fire({
                        title: '<strong>Información del la Sala</strong>',
                        html: `
                            <div class="text-left">
                                <p class="p-1"><strong>Sala:</strong> ${sala.codigo} | ${sala.nombre} </p>
                                <p class="p-1"><strong>Estado:</strong> ${estadoTexto}</p>
                                <p class="p-1"><strong>Costo Tapicero:</strong> ${sala.costo_tapicero ?? 'N/A'}</p>
                                <p class="p-1"><strong>Costo Cacastero:</strong> ${sala.costo_cacastero ?? 'N/A'}</p>
                                <p class="p-1"><strong>Descipción:</strong> ${sala.descripcion ?? 'N/A'}</p>
                            </div>
                        `,
                        showCloseButton: true,
                        showConfirmButton: false,
                        focusConfirm: false,
                        customClass: {
                            popup: 'rounded-xl p-6'
                        },
                        width: '600px'
                    });
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            },
            error: function(xhr) {
                Swal.fire('Error', 'No se pudo obtener la información del la sala.', 'error');
            }
        });
    });

    //modal para conformación de piezas
    $(document).on('click', '#btn_settings', function () {
        const id = $(this).attr('data_id');
        const sala = $(this).attr('data_sala');
            Swal.fire({
            title: '<strong>Conformación de Piezas <br> Sala: '+sala+'</strong>',
            html: `
                <form class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-10 gap-2 bg-white p-6 rounded-lg shadow-md mb-8" id="frmPiezaSala" data-id = ${id} method="POST" action="" class="mb-4">
                    <div class="md:col-span-2 lg:col-span-4" mb-2>
                        <label for="pieza_sala" class="block mb-2  md:text-left lg:text-center text-sm font-medium text-gray-900">Pieza<span class="text-red-500">(*)</span> </label>
                        <input type="text" id="pieza_sala" name="pieza_sala" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" placeholder="Buscar pieza">
                    </div>

                    <div class="md:col-span-2 lg:col-span-4 mb-2">
                        <label for="cantidad" class="block mb-2 text-sm md:text-left lg:text-center font-medium text-gray-900">Cantidad<span class="text-red-500">(*)</span> </label>
                        <input type="text" id="cantidad" name="cantidad" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" placeholder="Ingrese la cantidad de piezas">
                    </div>

                    <div class="w-full flex items-center justify-center md:col-span-5 lg:col-span-2 mb-2">
                        <button id="btnGuardarPieza" type="submit" class="text-white bg-green-700 hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-2xl w-full px-5 py-2.5 text-center m-1 h-full">
                            +
                        </button>
                    </div>

                </form>

                <div class="text-left mt-4 mb-2 bg-white p-6 rounded-lg shadow-md mb-8">
                    <div id="conformacion_piezas" class="overflow-y-auto max-h-60">
                        <table class="table-auto w-lg sm:w-full p-3">
                            <thead>
                                <tr>
                                    <th class="px-2 py-2">Pieza</th>
                                    <th class="px-2 py-2">Cantidad</th>
                                    <th class="px-2 py-2"></th>
                                </tr>
                            </thead>
                            <tbody id="piezas_table_body">
                                
                            </tbody>
                        </table>
                    </div>
                </div>
            `,
            showCloseButton: true,
            showConfirmButton: false,
            focusConfirm: false,
            customClass: {
                popup: 'rounded-xl p-6 !bg-neutral-100'
            },
            width: '95%',
            didOpen: () => {
                cargarPiezas(id);
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
                $('.ui-autocomplete').css('z-index', 2000);
            }
        });
    });


    //guardado de la pieza para la sala

    $(document).on('submit', '#frmPiezaSala', function(e) {
        e.preventDefault();
        var idSala = $('#frmPiezaSala').attr('data-id');
        var id_pieza = $('#pieza_sala').attr('data-id');
        var cantidad = $('#cantidad').val();
        $.ajax({
            url: $('meta[name="store_pieza"]').attr('content'),
            type: 'POST',
            data: {
                id_sala: idSala,
                id_pieza: id_pieza,
                cantidad: cantidad
            },
            success: function(response) {
                if(response.success) {
                    cargarPiezas(idSala);
                    notyf.success(response.message);
                    $('#frmPiezaSala')[0].reset();
                    $('#pieza_sala').removeAttr('data-id');
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

    //cargar piezas de la sala

    function cargarPiezas(id_sala){
        $.ajax({
            url: $('meta[name="piezas_sala"]').attr('content'),
            type: 'POST',
            data: {
                id_sala: id_sala
            },
            success: function(response) {
                $('#piezas_table_body').empty();
                if(response.success) {
                    response.data.forEach(function(item) {
                        $('#piezas_table_body').append(`
                            <tr>
                                <td class="px-2 py-1"> ${item.codigo} | ${item.nombre}</td>
                                <td class="px-2 py-1">  
                                    <input type="number" class="txtCantidad bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" value="${item.cantidad}" data-id=${item.id_relacion} data-last="${item.cantidad}">
                                </td>
                                <td class="px-2 py-1">
                                   <button id="btn_eliminar_pieza" data-id="${item.id_relacion}" class="btn btn-sm btn-danger cursor-pointer"><i class=" text-2xl text-red-600 hover:text-red-400 bx bxs-trash"></i></button>
                                </td>
                            </tr>
                        `);
                    });
                }else{
                    $('#piezas_table_body').append(`
                            <tr>
                                <td colspan="2" class="px-2 py-2 text-center"> No existen piezas relacionadas.</td>
                            </tr>
                    `);
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

    $(document).on('click', '#btn_eliminar_pieza', function(e) {
        e.preventDefault();
        console.log('Eliminando pieza de la sala');
        var id = $(this).attr('data-id');
        let baseEditUrl = $('meta[name="delete_pieza"]').attr('content');
        let finalUrl = baseEditUrl.replace('__ID__', id);
        Swal.fire({
            title: '¿Está seguro de eliminar la pieza de la sala?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Eliminar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: finalUrl,
                    type: 'DELETE',
                    success: function(response) {
                        if(response.success) {
                            cargarPiezas($('#frmPiezaSala').attr('data-id'));
                            notyf.success(response.message);
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

    $(document).on('change', '.txtCantidad', function(e) {
        e.preventDefault();
        var id = $(this).attr('data-id');
        var cantidad = $(this).val();
        var lastCantidad = $(this).attr('data-last');
        $.ajax({
            url: $('meta[name="update_pieza"]').attr('content'),
            type: 'PUT',
            data: {
                id_relacion: id,
                cantidad: cantidad
            },
            success: function(response) {
                if(response.success) {
                    notyf.success(response.message);
                }else{
                    notyf.error(response.message);
                    $(`.txtCantidad[data-id="${id}"]`).val(lastCantidad);
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

});