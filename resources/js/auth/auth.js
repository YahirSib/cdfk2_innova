 import Swal from "sweetalert2";
 import notyf from '../../js/app.js';

 $(function () {
    $('#loginForm').on('submit', function (e) {
        e.preventDefault();
        var action = $(this).attr('action');
        var data = new FormData(this);
        console.log(data);
        $.ajax({
            url: action,
            method: "POST",
            data: data,
            contentType: false,
            processData: false,
            success: function (res) {
                console.log(res);
                if(res.success){
                    window.location.href = res.redirect;
                }else{
                    notyf.error(res.message);
                }
            },
            error: function (xhr) {
                notyf.error(xhr.responseJSON.message);
            }
        });
    });
});