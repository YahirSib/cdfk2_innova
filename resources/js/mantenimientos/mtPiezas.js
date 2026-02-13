import Swal from "sweetalert2";

$(function () {
    function limpiarFormulario() {
        $("#frmPiezas")[0].reset();
        $("#tblPiezas").DataTable().ajax.reload();
    }

    //Guardado o edicion de piezas
    $("#frmPiezas").on("submit", function (e) {
        e.preventDefault();
        var method = $(this).attr("method");
        var action = $(this).attr("action");
        var formData = new FormData(this);
        formData.append("_method", method);
        var individual = $("#individual").is(":checked") ? 1 : 0;
        formData.append("individual_valor", individual);

        //CUANDO EL METODO ES PUT SE ACTUALIZA EL TRABAJADOR
        if (method == "PUT") {
            Swal.fire({
                title: "¿Está seguro de actualizar la pieza?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Actualizar",
            }).then((result) => {
                if (result.isConfirmed) {
                    //SI CONFIRMA SE ENVIA EL FORMULARIO
                    formData.append(
                        "id_pieza",
                        $("#frmPiezas").attr("data_id"),
                    );
                    $.ajax({
                        url: action,
                        type: "POST",
                        data: formData,
                        contentType: false,
                        processData: false,
                        success: function (response) {
                            if (response.success) {
                                Swal.fire({
                                    icon: "success",
                                    title: "Pieza actualizada",
                                    text: response.message,
                                    showConfirmButton: true,
                                    timer: 1500,
                                }).then(() => {
                                    $("#btnCrear").click();
                                });
                            } else {
                                Swal.fire({
                                    icon: "error",
                                    title: "Error",
                                    text: response.message,
                                    showConfirmButton: true,
                                });
                            }
                        },
                        error: function (xhr, status, error) {
                            // Manejo de errores
                            var responseText = xhr.responseText;
                            if (xhr.status === 422) {
                                // Validación fallida
                                var errors = JSON.parse(responseText);
                                var errorMessage = "";
                                $.each(errors.errors, function (key, value) {
                                    errorMessage += value[0] + "<br>";
                                });
                                responseText = errorMessage;
                            } else if (xhr.status === 500) {
                                console.log(xhr);
                                if (xhr.responseJSON.message) {
                                    responseText = xhr.responseJSON.message;
                                } else {
                                    responseText =
                                        "Error interno de sistema, contacte con soporte tecnico.";
                                }
                            }
                            Swal.fire({
                                icon: "error",
                                title: "Error",
                                html: responseText,
                                showConfirmButton: true,
                            });
                        },
                    });
                } else {
                    $("#btnCrear").click();
                }
            });
            return;
        } else {
            //SI EL METODO ES POST SE CREA UN NUEVA PIEZA
            $.ajax({
                url: action,
                type: "POST",
                data: formData,
                contentType: false,
                processData: false,
                success: function (response) {
                    if (response.success) {
                        Swal.fire({
                            icon: "success",
                            title: "Pieza guardada",
                            text: response.message,
                            showConfirmButton: true,
                            timer: 1500,
                        }).then(() => {
                            limpiarFormulario();
                        });
                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: response.message,
                            showConfirmButton: true,
                        });
                    }
                },
                error: function (xhr, status, error) {
                    // Manejo de errores
                    var responseText = xhr.responseText;
                    if (xhr.status === 422) {
                        // Validación fallida
                        var errors = JSON.parse(responseText);
                        var errorMessage = "";
                        $.each(errors.errors, function (key, value) {
                            errorMessage += value[0] + "<br>";
                        });
                        responseText = errorMessage;
                    } else if (xhr.status === 500) {
                        console.log(xhr);
                        if (xhr.responseJSON.message) {
                            responseText = xhr.responseJSON.message;
                        } else {
                            responseText =
                                "Error interno de sistema, contacte con soporte tecnico.";
                        }
                    }
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        html: responseText,
                        showConfirmButton: true,
                    });
                },
            });
        }
    });

    //Eliminar trabajador
    $(document).on("click", "#btn_eliminar", function (e) {
        e.preventDefault();
        var id = $(this).attr("data_id");
        let baseDeleteUrl = $('meta[name="delete"]').attr("content");
        let finalUrl = baseDeleteUrl.replace("__ID__", id);
        Swal.fire({
            title: "¿Está seguro de eliminar la pieza?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Eliminar",
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: finalUrl,
                    type: "DELETE",
                    success: function (response) {
                        if (response.success) {
                            Swal.fire({
                                icon: "success",
                                title: "Pieza eliminada",
                                text: response.message,
                                showConfirmButton: true,
                                timer: 1500,
                            }).then(() => {
                                limpiarFormulario();
                            });
                        } else {
                            Swal.fire({
                                icon: "error",
                                title: "Error",
                                text: response.message,
                                showConfirmButton: true,
                            });
                        }
                    },
                    error: function (xhr, status, error) {
                        // Manejo de errores
                        var responseText = xhr.responseText;
                        if (xhr.status === 500) {
                            console.log(xhr);
                            if (xhr.responseJSON.message) {
                                responseText = xhr.responseJSON.message;
                            } else {
                                responseText =
                                    "Error interno de sistema, contacte con soporte tecnico.";
                            }
                        }
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            html: responseText,
                            showConfirmButton: true,
                        });
                    },
                });
            }
        });
    });

    //cargar tabla de piezas
    $("#tblPiezas").DataTable({
        processing: true,
        serverSide: true,
        searching: true,
        paging: true,
        pageLength: 25,
        order: [[0, "desc"]],
        responsive: true,
        autoWidth: false,
        ajax: {
            url: $('meta[name="datatable"]').attr("content"),
            type: "GET",
        },
        language: {
            searchPlaceholder: "Buscar...",
            processing: "Cargando...",
            search: "", // Oculta el texto "Search"
        },
        columns: [
            { data: "id_pieza" },
            { data: "codigo" },
            { data: "nombre" },
            {
                data: "estado",
                render: function (data, type, row) {
                    if (data === 1) return "Activo";
                    if (data === 2) return "Inactivo";
                    return data;
                },
            },
            { data: "existencia" },
            { data: "acciones", orderable: false, searchable: false },
        ],
        dom:
            '<"flex justify-end mb-4"f>' + // Buscador alineado a la derecha
            '<"overflow-x-auto border border-gray-200 rounded-lg"t>' + // Tabla con borde y scroll
            '<"flex justify-end mt-4"p>', // Paginación alineada a la derecha

        drawCallback: function () {
            // Estilos para el input de búsqueda
            $("div.dataTables_filter input")
                .addClass(
                    "bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5",
                )
                .attr("placeholder", "Buscar...");

            // Eliminar el label del filtro (deja solo el input)
            $("div.dataTables_filter label")
                .contents()
                .filter(function () {
                    return this.nodeType === 3;
                })
                .remove();
        },
    });

    $(document).on("click", "#btn_editar", function (e) {
        e.preventDefault();

        var id = $(this).attr("data_id");
        let baseEditUrl = $('meta[name="edit"]').attr("content");
        let finalUrl = baseEditUrl.replace("__ID__", id);

        $.ajax({
            url: finalUrl,
            type: "GET",
            success: function (response) {
                console.log(response);
                if (response.success) {
                    //Cargar datos en el formulario
                    $("#codigo").val(response.data.codigo);
                    // $('#codigo').attr('readonly', true);
                    $("#nombre").val(response.data.nombre);
                    $("#estado").val(response.data.estado);
                    $("#costo_tapicero").val(response.data.costo_tapicero);
                    $("#costo_cacastero").val(response.data.costo_cacastero);
                    $("#precio_venta").val(response.data.precio_venta);
                    $("#existencia").val(response.data.existencia);
                    $("#descripcion").val(response.data.descripcion);
                    if (response.data.individual == 1) {
                        $("#individual").prop("checked", true);
                    } else {
                        $("#individual").prop("checked", false);
                    }

                    //Cambio form para editar
                    $("#frmPiezas").attr(
                        "action",
                        $('meta[name="update"]').attr("content"),
                    );
                    $("#frmPiezas").attr("data_id", id);
                    $("#frmPiezas").attr("method", "PUT");
                    $("#btnForm").html("Actualizar");
                    $("#btnCrear").removeClass("hidden");
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: response.message,
                        showConfirmButton: true,
                    });
                }
            },
            error: function (xhr, status, error) {
                // Manejo de errores
                var responseText = xhr.responseText;
                if (xhr.status === 500) {
                    console.log(xhr);
                    if (xhr.responseJSON.message) {
                        responseText = xhr.responseJSON.message;
                    } else {
                        responseText =
                            "Error interno de sistema, contacte con soporte tecnico.";
                    }
                }
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    html: responseText,
                    showConfirmButton: true,
                });
            },
        });
    });

    $("#btnCrear").on("click", function (e) {
        e.preventDefault();
        $("#frmPiezas").attr("action", $('meta[name="store"]').attr("content"));
        $("#frmPiezas").attr("method", "POST");
        $("#frmPiezas").removeAttr("data_id");
        $("#btnForm").html("Guardar");
        $("#btnCrear").addClass("hidden");
        $("#codigo").focus();
        $("#codigo").attr("readonly", false);
        limpiarFormulario();
    });

    $(document).on("click", "#btn_ver", function () {
        const id = $(this).attr("data_id");
        let baseEditUrl = $('meta[name="edit"]').attr("content");
        let finalUrl = baseEditUrl.replace("__ID__", id);
        $.ajax({
            url: finalUrl,
            type: "GET",
            success: function (response) {
                if (response.success) {
                    const pieza = response.data;
                    const estadoTexto =
                        pieza.estado == 1
                            ? "Activo"
                            : pieza.tipo == 2
                              ? "Inactivo"
                              : "Desconocido";

                    Swal.fire({
                        title: "<strong>Información del la Pieza</strong>",
                        html: `
                            <div class="text-left">
                                <p class="p-1"><strong>Pieza:</strong> ${pieza.codigo} | ${pieza.nombre} </p>
                                <p class="p-1"><strong>Estado:</strong> ${estadoTexto}</p>
                                <p class="p-1"><strong>Costo Tapicero:</strong> ${pieza.costo_tapicero ?? "N/A"}</p>
                                <p class="p-1"><strong>Costo Cacastero:</strong> ${pieza.costo_cacastero ?? "N/A"}</p>
                                <p class="p-1"><strong>Precio Venta:</strong> ${pieza.precio_venta ?? "N/A"}</p>
                                <p class="p-1"><strong>Descipción:</strong> ${pieza.descripcion ?? "N/A"}</p>
                            </div>
                        `,
                        showCloseButton: true,
                        showConfirmButton: false,
                        focusConfirm: false,
                        customClass: {
                            popup: "rounded-xl p-6",
                        },
                        width: "600px",
                    });
                } else {
                    Swal.fire("Error", response.message, "error");
                }
            },
            error: function (xhr) {
                Swal.fire(
                    "Error",
                    "No se pudo obtener la información del la pieza.",
                    "error",
                );
            },
        });
    });
});
