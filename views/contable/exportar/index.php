<?php
date_default_timezone_set($_SESSION["zona_horaria"]);
setlocale(LC_ALL,"es_ES@euro","es_ES","esp");
$fecha = date("d-m-Y");
$fechaa = date("m-Y");
?>
<input type="hidden" id="moneda" value="<?php echo Session::get('moneda'); ?>"/>
<div class="row page-titles">
    <div class="col-md-5 col-8 align-self-center">
        <h4 class="m-b-0 m-t-0">Exportar</h4>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo URL; ?>contable" class="link">Inicio</a></li>
            <li class="breadcrumb-item"><a href="<?php echo URL; ?>contable" class="link">Contable</a></li>
            <li class="breadcrumb-item active">Exportar</li>
        </ol>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">        
            <form action="<?php echo URL; ?>contable/excel" method="post" target="_blank" id="myForm">  
                <div class="card-body p-b-0">
                    <h4 class="card-title">Exportar ventas </h4> 
                    <div class="message-box contact-box">
                        <div class="row floating-labels mt-5">
                            <div class="col-lg-12">
                                <div class="form-group m-b-40">
                                    <div class="input-group">
                                        <input type="text" class="form-control font-14 text-center" name="start" id="start" value="<?php echo '01-'.$fechaa; ?>" autocomplete="off"/>
                                        <span class="input-group-text bg-gris">al</span>
                                        <input type="text" class="form-control font-14 text-center" name="end" id="end" value="<?php echo $fecha; ?>" autocomplete="off"/>
                                    </div>
                                    <label>Rango de fechas</label>
                                </div>
                            </div>                      
                        </div>
                    </div>
                </div>
                <div class="card-footer text-right">
                    <button id="generarexcel" class="btn btn-success">Generar</button>
                </div>
            </form>

        </div>
    </div>
</div>