<?php
date_default_timezone_set($_SESSION["zona_horaria"]);
setlocale(LC_ALL,"es_ES@euro","es_ES","esp");
$fecha = date("d-m-Y");
$fechaa = date("m-Y");
?>
<input type="hidden" id="moneda" value="<?php echo Session::get('moneda'); ?>"/>
<div class="row page-titles">
    <div class="col-md-12 align-self-center">
        <h4 class="m-b-0 m-t-0">Informes</h4>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo URL; ?>informe" class="link">Inicio</a></li>
            <li class="breadcrumb-item"><a href="<?php echo URL; ?>informe" class="link">Informe de ventas</a></li>
            <li class="breadcrumb-item active">Margen de ganancia por productos vendidos</li>
        </ol>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <form>
            <div class="card-body p-b-0">
                <div class="message-box contact-box">
                    <h2 class="add-ct-btn">                 
                        <div class="ml-auto">
                            <div class="btn-group">
                            <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-download"></i>
                            </button>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <a class="" href="javascript:void();" id="excel"></a>
                                    <a class="" href="javascript:void();" id="pdf"></a>
                                </div>
                            </div>
                        </div>
                    </h2>
                    <br>
                    <div class="row floating-labels m-t-5">
                        <div class="col-lg-3">
                            <div class="form-group m-b-40">
                                <div class="input-group">
                                    <input type="text" class="form-control font-14 text-center" name="start" id="start" value="<?php echo '01-'.$fechaa; ?>" autocomplete="off"/>
                                    <span class="input-group-text bg-gris">al</span>
                                    <input type="text" class="form-control font-14 text-center" name="end" id="end" value="<?php echo $fecha; ?>" autocomplete="off"/>
                                </div>
                                <label>Rango de fechas</label>
                            </div>
                        </div>
                        <div class="col-sm-5 col-lg-2">
                            <div class="form-group m-b-40">
                                <select class="selectpicker form-control" name="filtro_categoria" id="filtro_categoria" data-style="form-control btn-default" data-live-search="true" autocomplete="off" data-size="5">
                                    <option value="%" active>Mostrar todo</option>
                                    <optgroup>
                                        <?php foreach($this->Categoria as $key => $value): ?>
                                            <option value="<?php echo $value['id_catg']; ?>"><?php echo $value['descripcion']; ?></option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                </select>
                                <span class="bar"></span>
                                <label for="filtro_categoria">Categor&iacute;a</label>
                            </div>
                        </div>
                        <div class="col-sm-7 col-lg-4">
                            <div class="form-group m-b-40">
                                <select class="selectpicker form-control" name="filtro_producto" id="filtro_producto" data-style="form-control btn-default" data-live-search="true" autocomplete="off" data-size="5" disabled>
                                    <option value="%" active>Mostrar todo</option>
                                    <optgroup>
                                        <?php foreach($this->Producto as $key => $value): ?>
                                            <option value="<?php echo $value['id_prod']; ?>"><?php echo $value['nombre']; ?></option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                </select>
                                <span class="bar"></span>
                                <label for="filtro_producto">Producto</label>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="form-group m-b-40">
                                <select class="selectpicker form-control" name="filtro_presentacion" id="filtro_presentacion" data-style="form-control btn-default" data-live-search="true" autocomplete="off" data-size="5" disabled>
                                    <option value="%" active>Mostrar todo</option>
                                    <optgroup>
                                        <?php foreach($this->Presentacion as $key => $value): ?>
                                            <option value="<?php echo $value['id_pres']; ?>"><?php echo $value['presentacion']; ?></option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                </select>
                                <span class="bar"></span>
                                <label for="filtro_presentacion">Presentaci&oacute;n</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            </form>
            <div class="text-center m-b-20">
                <div class="row">
                    <div class="col-lg-3">
                        <h2 class="font-medium text-warning m-b-0 font-30 cantidad-vendida"></h2>
                        <h6 class="font-bold m-b-10">Cantidad vendida</h6>                            
                    </div>
                    <div class="col-lg-3">
                        <h2 class="font-medium text-warning m-b-0 font-30 costo-total"></h2>
                        <h6 class="font-bold m-b-10">Costo total</h6>
                    </div>
                    <div class="col-lg-3">
                        <h2 class="font-medium text-warning m-b-0 font-30 margen-total"></h2>
                        <h6 class="font-bold m-b-10">Margen total</h6>
                    </div>
                    <div class="col-lg-3">
                        <h2 class="font-medium text-warning m-b-0 font-30 ventas-total"></h2>
                        <h6 class="font-bold m-b-10">Total</h6>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive b-t m-b-10">
                    <table id="table" class="table table-hover table-condensed stylish-table" width="100%">
                        <thead class="table-head">
                            <tr>
                                <th width="20%">Producto</th>
                                <th width="10%">Categor&iacute;a</th>
                                <th class="text-right" width="10%">Cantidad vendida</th>
                                <th class="text-right" width="10%">Costo unitario</th>
                                <th class="text-right" width="10%">Costo total</th>
                                <th class="text-right" width="10%">Precio venta</th>
                                <th class="text-right" width="10%">Margen unitario</th>
                                <th class="text-right" width="10%">Margen total</th>
                                <th class="text-right" width="10%">Total</th>
                            </tr>
                        </thead>
                        <tbody class="tb-st"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
