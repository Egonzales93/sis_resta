<input type="hidden" id="url" value="<?php echo URL; ?>"/>
<body class="fix-header fix-sidebar card-no-border">
    <section id="wrapper" class="error-page" >
        <div class="error-box">
            <div class="error-body text-center">
            <h1 class="text-danger m-b-0"> <img src="<?php echo URL; ?>public/images/faltapago.png" width="250px" alt=""></h1>
                <h3 class="p-20">¡bloqueo por falta de pago!</h3>
                <p class="text-muted m-t-0 m-b-30">Su plataforma fue bloqueada por falta de pago. Comuniquese al +591 71116260 o en el siguiente link ⬇</p>
                <a href="https://wa.me/59171116260" target="_blank" class="btn btn-success btn-rounded waves-effect waves-light m-b-40"><i class="fab fa-whatsapp"></i> CONTACTAR</a></div>
                <footer class="footer text-center bg-dark"><img src="<?php echo URL; ?>public/images/mpw-rest.png"  alt=""></div></footer>
        </div>
    </section>
</body>
<style>
.text-warning {
    color: #ea5b5d !important;
}
</style>
<script type="text/javascript" src="<?php echo URL; ?>public/plugins/jquery/jquery.min.js"></script>
<script src="<?php echo URL; ?>public/plugins/moment/moment.js"></script>
<script type="text/javascript">
$(function() {
    liberarbloqueo();
    setInterval(liberarbloqueo, 10000);
    moment.locale('es');
});

var liberarbloqueo = function(){
    $.ajax({     
        type: "post",
        dataType: "json",
        url: $("#url").val()+'api/liberarbloqueo',
        success: function (data){
            console.log(data);
            if(data.status == 'liberado'){
                 window.location.href = $("#url").val()+'tablero';
            }           
        }
    })
}


</script>
