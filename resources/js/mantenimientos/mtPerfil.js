import notyf from '../../js/app.js';
import Swal from "sweetalert2";

$(function() {

    function limpiarFormulario() {
        $('#frmPerfil')[0].reset();
        $('#tblPerfil').DataTable().ajax.reload();
    }

    $('#tblPerfil').DataTable({
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
            { data: 'id' },
            { data: 'nombre' },
            { data: 'estado', render: function(data, type, row) {
                if (data === "1") return 'Activo';
                if (data === "2") return 'Inactivo';
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

    $('#frmPerfil').on('submit', function(e) {
        e.preventDefault();
        var method = $(this).attr('method');
        var action = $(this).attr('action');
        var formData = new FormData(this);
        formData.append('_method', method);
        //CUANDO EL METODO ES PUT SE ACTUALIZA EL TRABAJADOR
        if(method == 'PUT'){
            Swal.fire({
                title: '¿Está seguro de actualizar el perfil?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Actualizar'
            }).then((result) => {
                if (result.isConfirmed) {
                    //SI CONFIRMA SE ENVIA EL FORMULARIO
                    formData.append('id', $('#frmPerfil').attr('data_id'));
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
                                    title: 'Perfil actualizado',
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
                                console.log(xhr);
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
                            title: 'Perfil guardado',
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
                        console.log(xhr);
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

    $(document).on('click', '#btn_editar', function(e) {
        e.preventDefault();
        
        var id = $(this).attr('data_id');
        let baseEditUrl = $('meta[name="edit"]').attr('content');
        let finalUrl = baseEditUrl.replace('__ID__', id);

        $.ajax({
            url: finalUrl, 
            type: 'GET',
            success: function(response) {
                console.log(response);
                if (response.success) {

                    $('#nombre').val(response.data.nombre);
                    $('#estado').val(response.data.estado);
                    //Cambio form para editar
                    $('#frmPerfil').attr('action', $('meta[name="update"]').attr('content'));
                    $('#frmPerfil').attr('data_id', id);
                    $('#frmPerfil').attr('method', 'PUT');
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
                    console.log(xhr);
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
        $('#frmPerfil').attr('action', $('meta[name="store"]').attr('content'));
        $('#frmPerfil').attr('method', 'POST');
        $('#frmPerfil').removeAttr('data_id');
        $('#btnForm').html('Guardar');
        $('#btnCrear').addClass('hidden');
        $('#nombre').focus();
        limpiarFormulario();
    }); 

    $(document).on('click', '#btn_eliminar', function(e) {
        e.preventDefault();
        var id = $(this).attr('data_id');
        let baseDeleteUrl = $('meta[name="delete"]').attr('content');
        let finalUrl = baseDeleteUrl.replace('__ID__', id);
        Swal.fire({
            title: '¿Está seguro de eliminar el perfil?',
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
                                title: 'Perfil eliminado',
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
                            console.log(xhr);
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

});