$(function(){
	listar();
    $('#config').addClass("active");
});

$(function() {
    $('#form')
    .formValidation({
        framework: 'bootstrap',
        excluded: ':disabled',
        fields: {
        }
    })
    .on('success.form.fv', function(e) {

    e.preventDefault();
    var $form = $(e.target),
    fv = $form.data('formValidation');
            
    id_pago = $('#id_pago').val();
    id_tipo_pago = $('#id_tipo_pago').val();
    nombre = $('#nombre').val();
    comunicacion = $('#comunicacion').val();
    color = $('#color').val();
    delivery = $('#delivery').val();
    estado = $('#estado').val();

        $.ajax({
            dataType: 'JSON',
            type: 'POST',
            url: $('#url').val()+'ajuste/tipopago_crud',
            data: {
                id_pago: id_pago,
                id_tipo_pago: id_tipo_pago,
                nombre: nombre,
                comunicacion: comunicacion,
                color: color,
                delivery: delivery,
                estado: estado
            },
            success: function (cod) {
                if(cod == 0){
                    Swal.fire({   
                        title:'Proceso No Culminado',   
                        text: 'Datos duplicados',
                        icon: "error", 
                        confirmButtonColor: "#34d16e",   
                        confirmButtonText: "Aceptar",
                        allowOutsideClick: false,
                        showCancelButton: false,
                        showConfirmButton: true
                    }, function() {
                        return false
                    });
                } else if(cod == 1){
                    $('#modal').modal('hide');
                    Swal.fire({   
                        title:'Proceso Terminado',   
                        text: 'Datos registrados correctamente',
                        icon: "success", 
                        confirmButtonColor: "#34d16e",   
                        confirmButtonText: "Aceptar",
                        allowOutsideClick: false,
                        showCancelButton: false,
                        showConfirmButton: true
                    }, function() {
                        return false
                    });
                    listar();
                } else if(cod == 2) {
                    $('#modal').modal('hide');
                    Swal.fire({   
                        title:'Proceso Terminado',   
                        text: 'Datos actualizados correctamente',
                        icon: "success", 
                        confirmButtonColor: "#34d16e",   
                        confirmButtonText: "Aceptar",
                        allowOutsideClick: false,
                        showCancelButton: false,
                        showConfirmButton: true
                    }, function() {
                        return false
                    });
                    listar();
                }
            },
            error: function(jqXHR, textStatus, errorThrown){
                console.log(errorThrown + ' ' + textStatus);
            }   
        });
    return false;
    });
});

/* Mostrar datos en la tabla Area de produccion */
var listar = function(){

    function filterGlobal () {
        $('#table').DataTable().search( 
            $('#global_filter').val()
        ).draw();
    }

	var table = $('#table')
	.DataTable({
        "order": [[ 2, "asc" ]],
        "destroy": true,
        "responsive": true,
        "dom": "tip",
        "bSort": true,
        "ajax":{
            "method": "POST",
            "url": $('#url').val()+"ajuste/tipopago_list",
            "data": {
                id_pago : '%'
            }
        },
        "columns":[
            {"data":"descripcion"},
            {"data":"Tipo.nombre"},
            // {"data":null,"render": function ( data, type, row) {
            //     if(data.delivery == 1){
            //         return '<span class="text-success font-bold"><i class="fas fa-check"></i></span>';
            //     } else if (data.delivery == 0){
            //         return '<span class="text-danger font-bold"><i class="fas fa-ban"></i></span>'
            //     }
            // }},
            {"data":null,"render": function ( data, type, row) {
                if(data.estado == 'a'){
                    return '<span class="label label-success">ACTIVO</span>';
                } else if (data.estado == 'i'){
                    return '<span class="label label-danger">INACTIVO</span>'
                }
            }},
            {"data":null,"render": function ( data, type, row ) {
                return '<div class="text-right"><a href="javascript:void(0)" class="text-info edit" onclick="editar('+data.id_tipo_pago+');"><i data-feather="edit" class="feather-sm fill-white"></i></a></div>';
            }}
        ]
	});

    $('input.global_filter').on( 'keyup click', function () {
        filterGlobal();
    });

    $('#table').DataTable().on("draw", function(){
        feather.replace();
    });
}

/* Editar Area de produccion */
var editar = function(id_pago){
    $(".f").addClass("focused");
    $.ajax({
        type: "POST",
        url: $('#url').val()+"ajuste/tipopago_list",
        data: {
            id_pago: id_pago
        },
        dataType: "json",
        success: function(item){
            $.each(item.data, function(i, campo) {
                $('#id_pago').val(campo.id_tipo_pago);
                $('#nombre').val(campo.descripcion);
                // $('#comunicacion').val(campo.comunicacion);
                $('#id_tipo_pago').selectpicker('val', campo.id_pago);
                // $('#color').val(campo.color);
                // $('#delivery').selectpicker('val', campo.delivery);
                $('#estado').selectpicker('val', campo.estado);
                $('.modal-title').text('Editar');
                $('#modal').modal('show');
            });
        }
    });
}

/* Boton nueva area de produccion */
$('.btn-nuevo').click( function() {
    $(".f").removeClass("focused");
    $('#id_tipo_pago').val('');
    $('.modal-title').text('Nuevo');
    $('#modal').modal('show');
});

$('#modal').on('hidden.bs.modal', function() {
    $(this).find('form')[0].reset();
    $('#form').formValidation('resetForm', true);
    $('#id_tipo_pago').selectpicker('val', '');
    $('#estado').selectpicker('val', 'a');
    $('#delivery').selectpicker('val', '0');
});