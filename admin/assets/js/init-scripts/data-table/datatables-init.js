(function ($) {
    //    "use strict";


    /*  Data Table
    -------------*/



    $('#bootstrap-data-table-export').DataTable({
		language: {
			"decimal": "",
			"emptyTable": "No hay información",
			"info": "Mostrando de _START_ a _END_",
			"infoEmpty": "Mostrando 0 to 0 of 0 Entradas",
			"infoFiltered": "(Filtrado de _MAX_ total )",
			"infoPostFix": "",
			"thousands": ",",
			"lengthMenu": "Mostrar _MENU_ ",
			"loadingRecords": "Cargando...",
			"processing": "Procesando...",
			"search": "Buscar:",
			"zeroRecords": "Sin resultados encontrados",
			"paginate": {
				"first": "Primero",
				"last": "Ultimo",
				"next": "Siguiente",
				"previous": "Anterior"
			}
		},
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Todos"]],
        buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
    });

	

		

})(jQuery);
