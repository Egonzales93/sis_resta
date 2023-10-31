$(function() {
	$('#form-proveedor').formValidation({
        framework: 'bootstrap',
        excluded: ':disabled',
        fields: {
            nit: {
                validators: {
                    stringLength: {
                        message: 'El '+$(".c-nit").text()+' debe tener '+$("#nit").attr("maxlength")+' digitos'
                    }
                }
            }
        }
    }).on('success.form.fv', function(e) {

        e.preventDefault();
        var $form = $(e.target),
        fv = $form.data('formValidation');

        var nit = $('#nit').val(),
			razon_social = $('#razon_social').val(),
			direccion = $('#direccion').val(),
			telefono = $('#telefono').val(),
			email = $('#email').val(),
			contacto = $('#contacto').val();

		$.ajax({
			type: 'POST',
			dataType: 'json',
			data: {
				nit : nit,
				razon_social : razon_social,
				direccion : direccion,
				telefono : telefono,
				email : email,
				contacto : contacto
			},
			url: $('#url').val()+'compra/compra_proveedor_nuevo',
			success: function(data){
				if(data.cod == 1){
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
				}else {
					$('#id_prov').val(data.id_prov);
                    $('#datos_proveedor').val(razon_social);
                    $('#modal-proveedor').modal('hide');
				}
			}
		});
        return false;
    });
});

/* Nuevo Proveedor */
var nuevoProveedor = function(){
    $('#modal-proveedor').modal('show');
}

/* Consultar nit del nuevo cliente */
$("#nit").keyup(function(event) {
    var that = this,
    value = $(this).val();
    if (value.length == $("#nit").attr("maxlength")) {
        $.getJSON($('#url').val()+"api/nit/"+$("#nit").val(), {
            format: "json"
        })
        .done(function(data) {
            $("#nit").val(data.nit);
            $("#razon_social").val(data.razonSocial);
            $("#direccion").val(data.direccion);
            $('#form-proveedor').formValidation('revalidateField', 'razon_social');
            $('#form-proveedor').formValidation('revalidateField', 'direccion');
        });
    } else if($("#nit").val() == "") {
        $('#nit').val("");
        $('#razon_social').val("");
        $('#direccion').val("");
        $('#telefono').val("");
        $('#email').val("");
        $('#contacto').val("");
        $('#form-proveedor').Console.log("");('resetForm', true);
    }
});

$('#modal-proveedor').on('hidden.bs.modal', function() {
	//$('#nit_numero').val('');
    $(this).find('form')[0].reset();
    $('#form-proveedor').formValidation('resetForm', true);
});