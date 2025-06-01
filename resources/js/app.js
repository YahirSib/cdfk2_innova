import $ from 'jquery';
window.$ = $;
window.jQuery = $;

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

import { Notyf } from 'notyf'
import 'notyf/notyf.min.css'

// Puedes personalizarla si deseas
const notyf = new Notyf({
  duration: 3000,
  ripple: true,
  position: {
    x: 'right',
    y: 'top',
  },
  types: [
    {
      type: 'success',
      background: '#10b981', // Tailwind green-500
      icon: false
    },
    {
      type: 'error',
      background: '#ef4444', // Tailwind red-500
      icon: false
    }
  ]
})

export default notyf