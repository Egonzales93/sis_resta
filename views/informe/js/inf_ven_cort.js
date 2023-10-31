$(function() {
    $('#informes').addClass("active");
    moment.locale('es');
    listar();

    $('#start').bootstrapMaterialDatePicker({
        format: 'DD-MM-YYYY LT',
        lang: 'es-do',
        cancelText: 'Cancelar',
        okText: 'Aceptar'
    });

    $('#end').bootstrapMaterialDatePicker({
        useCurrent: false,
        format: 'DD-MM-YYYY LT',
        lang: 'es-do',
        cancelText: 'Cancelar',
        okText: 'Aceptar'
    });

    $('#start,#end').change( function() {
        listar();
    });

    /* BOTON DATATABLES */
    var org_buildButton = $.fn.DataTable.Buttons.prototype._buildButton;
    $.fn.DataTable.Buttons.prototype._buildButton = function(config, collectionButton) {
    var button = org_buildButton.apply(this, arguments);
    $(document).one('init.dt', function(e, settings, json) {
        if (config.container && $(config.container).length) {
           $(button.inserter[0]).detach().appendTo(config.container)
        }
    })    
    return button;
    }
});

var listar = function(){

    var moneda = $("#moneda").val();
    ifecha = $("#start").val();
    ffecha = $("#end").val();
    desc_tipo = $("#filtro_desc_tipo").val();

    var table = $('#table')
    .DataTable({
        buttons: [
            {
                extend: 'excel', title: 'Cortesias', className: 'dropdown-item p-t-0 p-b-0', text: '<i class="fas fa-file-excel"></i> Descargar en excel', titleAttr: 'Descargar Excel',
                container: '#excel', exportOptions: { columns: [0,1,2,3,4,5,6] }
            },
            {
                extend: 'pdf', title: 'Cortesias', className: 'dropdown-item p-t-0 p-b-0', text: '<i class="fas fa-file-pdf"></i> Descargar en pdf', titleAttr: 'Descargar Pdf',
                container: '#pdf', exportOptions: { columns: [0,1,2,3,4,5,6] }, orientation: 'landscape', 
                customize : function(doc){ 
                    doc.styles.tableHeader.alignment = 'left'; 
                    doc.content[1].table.widths = [60,'*','*','*','*','*','*'];
                }
            }
        ],
        "destroy": true,
        "responsive": true,
        "dom": "tip",
        "bSort": true,
        "ajax":{
            "method": "POST",
            "url": $('#url').val()+"informe/venta_cort_list",
            "data": {
                ifecha: ifecha,
                ffecha: ffecha,
                desc_tipo
            }
        },
        "columns":[
            {"data":"fec_ven","render": function ( data, type, row ) {
                return '<i class="ti-calendar"></i> '+moment(data).format('DD-MM-Y')
                +'<br><span class="font-12"><i class="ti-time"></i> '+moment(data).format('h:mm A')+'</span>';
            }},
            {"data":null,"render": function ( data, type, row ) {
                return (data.desc_td).toUpperCase()
                +'<br><span class="font-12"> '+data.numero+'</span>';
            }},
            {"data":"desc_tipo","render": function ( data, type, row ) {
                if(data == 1){
                    var tipo = 'CORTESIA';
                } else if (data == 2){
                    var tipo = 'DESCUENTO';                    
                } else if (data == 3){
                    var tipo = 'CREDITO PERSONAL';                    
                } else {
                    var tipo = '-';
                }
                return '<div class="mayus">'+tipo+'</div>';
            }},
            {"data":"desc_motivo"},
            {"data":"desc_usu"},
            {"data":"total_descuento","render": function ( data, type, row) {              
                return '<p class="text-right bold m-b-0"> '+moneda+' '+formatNumber(data)+'</p>';
            }},
            {"data":"total","render": function ( data, type, row) {
                return '<p class="text-right bold m-b-0"> '+moneda+' '+formatNumber(data)+'</p>';
            }},
        ],
        "footerCallback": function ( row, data, start, end, display ) {
            var api = this.api(), data;

            var intVal = function ( i ) {
                return typeof i === 'string' ?
                    i.replace(/[\$,]/g, '')*1 :
                    typeof i === 'number' ?
                        i : 0;
            };
 
            total = api
                .column( 5 /*, { search: 'applied', page: 'current'} */)
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0 );

            operaciones = api
                .rows()
                .data()
                .count();

            $('.cortesias-total').text(moneda+' '+formatNumber(total));
            $('.cortesias-operaciones').text(operaciones);
        }
    });
}