import Swal from "sweetalert2";

$(function() {
    
    //Guardado de trabajadores
    $('#frmTrabajadores').on('submit', function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        $.ajax({
            url: $(this).attr('action'),
            type: $(this).attr('method'),
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
               if(response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Trabajador guardado',
                        text: response.message,
                        showConfirmButton: true,
                        timer: 1500
                    }).then(() => {
                        $('#frmTrabajadores')[0].reset();
                        $('#tablTrabajadores').DataTable().ajax.reload();
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
    })


    //cargar tabla de trabajadores
    $('#tblTrabajadores').DataTable({
        processing: true,
        serverSide: true,
        searching: true,
        paging: true,
        pageLength: 50,
        order: [[0, 'desc']],
        responsive: true,
        autoWidth: false,
        ajax: {
            url: $('meta[name="datatable"]').attr('content'),
            type: 'GET',
        },
        language: {
            searchPlaceholder: "Buscar...",
            processing: "Cargando..."
        },
        columns: [
            { data: 'id_trabajador',  },
            { data: 'nombre_completo'},
            { data: 'tipo', render: function(data, type, row) {
                if (data === "1") {
                    return 'Tapicero'; 
                } else if (data === "2") {
                    return 'Carpintero'; 
                }
                return data; 
            }},
            { data: 'acciones', orderable: false, searchable: false }
        ],
        dom: '<"row mb-2"<"col-sm-6"f>>' +
         '<"table-responsive"t>' +
         '<"row mt-3"<"col-sm-12 text-end"p>>',});

});