import jQuery from 'jquery';
window.$ = jQuery;

import 'bootstrap';
import Swal from 'sweetalert2';
window.Swal = Swal;

import 'datatables.net';
import 'datatables.net-dt/css/dataTables.dataTables.css';

$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});