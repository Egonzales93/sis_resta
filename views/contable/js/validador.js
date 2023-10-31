$(function() {
    $('#contable').addClass("active");
    moment.locale('es');
    // listar();

    $('#start').bootstrapMaterialDatePicker({
        time: false,
        format: 'DD-MM-YYYY',
        cancelText: 'Cancelar',
        okText: 'Aceptar'
    });

    $('#end').bootstrapMaterialDatePicker({
        time: false,
        format: 'DD-MM-YYYY',
        cancelText: 'Cancelar',
        okText: 'Aceptar'
    });

    $('.scroll_detalle').slimscroll({
        height: '100%'
    });
    var scroll_detalle = function () {
        var topOffset = 405;
        var height = ((window.innerHeight > 0) ? window.innerHeight : this.screen.height) - 1;
        height = height - topOffset;
        $(".scroll_detalle").css("height", (height) + "px");
    };
    $(window).ready(scroll_detalle);
    $(window).on("resize", scroll_detalle);

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
    $("#generarvalidador").click(function(){ 
        ifecha = $("#start").val();
        ffecha = $("#end").val();
        tdoc = $("#tipo_doc").selectpicker('val');

        // alert(tdoc);
        listar();










        // $("#myForm").submit(); // Submit the form
    });



});

var listar = function(){

    var moneda = $("#moneda").val();
    ifecha = $("#start").val();
    ffecha = $("#end").val();
    tdoc = $("#tipo_doc").selectpicker('val');

 var table = $('#table').DataTable({
        "destroy": true,
        "responsive": true,
        "dom": "tip",
        "bSort": true,
        "order": [[0,"desc"]],
        "ajax":{
            "method": "POST",
            "url": $('#url').val()+"contable/validador_list",
            "data": {
                ifecha: ifecha,
                ffecha: ffecha,
                tdoc: tdoc
            }
        },
        "columns":[
            {"data":"fec_ven","render": function ( data, type, row ) {
                return '<i class="ti-calendar"></i> '+moment(data).format('DD-MM-Y')
                +'<br><span class="font-12"><i class="ti-time"></i> '+moment(data).format('h:mm A')+'</span>';
            }},
            {"data":"Cliente.nombre","render": function ( data, type, row ) {
                return '<div class="mayus">'+data+'</div>';
            }},
            {"data":null,"render": function ( data, type, row ) {
                if(data.desc_tipo == 1){
                    var tooltip = ' <i class="ti-info-alt text-warning font-10" data-original-title="Cortesia" data-toggle="tooltip" data-placement="top"></i>';
                } else if(data.desc_tipo == 3){
                    var tooltip = ' <i class="ti-info-alt text-warning font-10" data-original-title="Credito Personal: '+data.Personal.nombres+'" data-toggle="tooltip" data-placement="top"></i>';
                } else {
                    var tooltip = '';
                }
                return data.desc_td
                +'<br><span class="font-12">'+data.ser_doc+'-'+data.nro_doc+'</span>'+tooltip;
            }},
            {"data":null,"render": function ( data, type, row ) {
                if(data.estado == 'a' && data.enviado_impuestos == '1'){
                    return '<span class="label label-primary">ENVIADO A IMPUESTO</span></a>';
                }else if(data.estado == 'i'){
                    return '<span class="label label-danger">ANULADO</span></a></div>';
                } else {
                    return '<span class="label label-warning">SIN ENVIAR</span></a></div>';
                }
            }},
            {"data":null,"render": function ( data, type, row ) {
                if(data.Estado_Impuestos == '1'){
                    return '<span class="label label-success">ACEPTADO</span></a>';
                }else if(data.Estado_Impuestos== '2'){
                    return '<span class="label label-danger">ANULADO</span></a></div>';
                } else {
                    return '<span class="label label-warning">NO EXISTE</span></a></div>';
                }
            }},
           
        ],
    });

};

// var detalle = function(id_venta,doc,num){
//     var moneda = $("#moneda").val();
//     var totalconsumido = 0,
//         totalcomision = 0,
//         totaldescuento = 0;
//     $('#lista_pedidos').empty();
//     $('#detalle').modal('show');
//     $('.title-detalle').text(doc+': '+num);
//     $.ajax({
//       type: "post",
//       dataType: "json",
//       data: {
//           id_venta: id_venta
//       },
//       url: $('#url').val()+'informe/venta_all_det',
//       success: function (data){
//         $.each(data, function(i, item) {
//             var calc = item.precio * item.cantidad;
//             $('#lista_pedidos')
//             .append(
//               $('<tr/>')
//                 .append($('<td width="10%"/>').html(item.cantidad))
//                 .append($('<td width="60%"/>').html(item.Producto.pro_nom+' <span class="label label-warning">'+item.Producto.pro_pre+'</span>'))
//                 .append($('<td width="15%"/>').html(moneda+' '+formatNumber(item.precio)))
//                 .append($('<td width="15%" class="text-right"/>').html(moneda+' '+formatNumber(calc)))
//                 );
//             totalconsumido += calc;
//             totalcomision = item.Comision.total;
//             totaldescuento = item.Descuento.total;
//             });
            
//             $('.total-consumido').text(moneda+' '+formatNumber(totalconsumido));
//             $('.total-comision').text(moneda+' '+totalcomision);
//             $('.total-descuento').text(moneda+' '+totaldescuento);
//             $('.total-facturado').text(moneda+' '+formatNumber(parseFloat(totalconsumido)+parseFloat(totalcomision)-parseFloat(totaldescuento)));
//         }
//     });
// };