$(function() {
    $('#contable').addClass("active");
    moment.locale('es');

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

    // $("#generarexcel").click(function(){        
    //     $("#myForm").submit(); // Submit the form
    // });

});

