$(function() {
    obtenerDatos();
    $('#config').addClass("active");
    $('#form').formValidation({
        framework: 'bootstrap',
        excluded: ':disabled',
        fields: {
        }
    }).on('success.form.fv', function(e) {
        // Prevent form submission
        e.preventDefault();
        var $form = $(e.target),
        fv = $form.data('formValidation');

        var parametros = new FormData($('#form')[0]);

        $.ajax({
            url: $('#url').val()+'ajuste/datosempresa_crud',
            type: 'POST',
            data: parametros,
            dataType: 'json',
            contentType: false,
            processData: false,
         })
         .done(function(response){
            var html_terminado = '<div>Datos actualizados correctamente</div>\
                <br><a href="'+$('#url').val()+'ajuste/datosempresa" class="btn btn-success">Aceptar</button>'
            Swal.fire({
                title: 'Proceso Terminado',
                html: html_terminado,
                icon: 'success',
                showConfirmButton: false
            });
            obtenerDatos();
        })
        .fail(function(){
            swal('Oops...', 'Problemas con la conexión a internet!', 'error');
        });
    });
});

var obtenerDatos = function(){
    $.ajax({
        type: "POST",
        url: $('#url').val()+"ajuste/datosempresa_data",
        dataType: "json",
        success: function(item){
            if($('#usuid').val() == 1){
                $('.impuestos').show();
                $(".impuestos").prop('disabled', false);
            } else {
                $('.impuestos').hide();
                $(".impuestos").prop('disabled', true);
            }
            $('#tribAcr').val(item.trib_acr);
            $('#nit').val(item.nit);
            $('#razon_social').val(item.razon_social);
            if(item.logo == null || item.logo == ''){
                $('#wizardPicturePreview-2').attr('src',$("#url").val()+'public/images/productos/default.png');
            }else{
                $('#wizardPicturePreview-2').attr('src',$("#url").val()+'public/images/'+item.logo+'');  
            }
            // $('#wizardPicturePreview-2').attr('src',$("#url").val()+'public/images/'+item.logo+'');
            $('#imagen').val(item.logo);
            $('#nombre_comercial').val(item.nombre_comercial);
            $('#direccion_comercial').val(item.direccion_comercial);
            $('#direccion_fiscal').val(item.direccion_fiscal);
            $('#celular').val(item.celular);        
            $('#ubigeo').val(item.ubigeo);        
            $('#departamento').val(item.departamento);        
            $('#provincia').val(item.provincia);        
            $('#distrito').val(item.distrito);       
            $('#usuariosol').val(item.usuariosol);       
            $('#clavesol').val(item.clavesol);       
            $('#clavecertificado').val(item.clavecertificado);
            $('#client_id').val(item.client_id);       
            $('#client_secret').val(item.client_secret);
            $('#impuestos_hidden').val(item.impuestos);   
            $('#modo_hidden').val(item.modo);
            if(item.impuestos == '1'){$('#impuestos').prop('checked', true)};
            if(item.modo == '1'){$('#modo').prop('checked', true)};       
        }
    });
}

$('#impuestos').on('change', function(event){
    if($(this).prop('checked')){
        $('#impuestos_hidden').val('1');
    }else{
        $('#impuestos_hidden').val('0');
        $('#impuestos_hidden').val('0');
    }
});

$('#modo').on('change', function(event){
    if($(this).prop('checked')){
        $('#modo_hidden').val('1');
    }else{
        $('#modo_hidden').val('3');
    }
});

$("#nit").keyup(function(event) {
    var that = this,
    value = $(this).val();
    if (value.length == $("#nit").attr("maxlength")) {
        $.ajax({
            type: "POST",
            url: "/validarNit/"+value+"",
            dataType : "json",
            success: function(data){
                console.log(data);
                if(!$.isEmptyObject(data)){
                    cargardatosempresa(data[0]);
                    }else{
                        swal({ title: "",  text: "El nit no se encuentra registrado.",  icon: "warning"}).then((result))
                        
                }
            }
        
        })
        .done(function(data) {
            //$("#ci").val(data.nit);
            $("#nit").val(data.nit);
            $("#razon_social").val(data.razonSocial);
            $("#nombre_comercial").val(data.nombreComercial);
            $("#direccion_fiscal").val(data.direccion);
            $("#celular").val(data.telefonos);
            $("#ubigeo").val(data.ubigeo);
            $("#departamento").val(data.departamento);
            $("#provincia").val(data.provincia);
            $("#distrito").val(data.distrito);
            $('#form').formValidation('revalidateField', 'razon_social');
            $('#form').formValidation('revalidateField', 'nombreComercial');
            $('#form').formValidation('revalidateField', 'direccion');
            $('#form').formValidation('revalidateField', 'telefonos');
            $('#form').formValidation('revalidateField', 'departamento');
            $('#form').formValidation('revalidateField', 'provincia');
            $('#form').formValidation('revalidateField', 'distrito');
        });
    }
});


var anularlogo = function(){

    var html_confirm = '<div>Se procederá a eliminar el logo</div><br>\
        <div><span class="text-success" style="font-size: 17px;">¿Está Usted de Acuerdo?</span></div>';

    Swal.fire({
        title: 'Necesitamos de tu Confirmación',
        html: html_confirm,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#34d16e',
        confirmButtonText: 'Si, Adelante!',
        cancelButtonText: "No!",
        showLoaderOnConfirm: true,
        preConfirm: function() {
          return new Promise(function(resolve) {
             $.ajax({
                url: $('#url').val()+'ajuste/anularlogo',
                dataType: 'json'
             })
             .done(function(response){
                if(response == 1){
                Swal.fire({
                    title: 'Proceso Terminado',
                    text: 'Datos eliminados correctamente',
                    icon: 'success',
                    confirmButtonColor: "#34d16e",   
                    confirmButtonText: "Aceptar"
                });
                obtenerDatos();
                }else{
                    Swal.fire({
                        title: 'Proceso No Culminado',
                        text: 'no se pueden eliminar',
                        icon: 'error',
                        confirmButtonColor: "#34d16e",   
                        confirmButtonText: "Aceptar"
                    });
                }
             })
             .fail(function(){
                Swal.fire('Oops...', 'Problemas con la conexión a internet!', 'error');
             });
          });
        },
        allowOutsideClick: false              
    });
}