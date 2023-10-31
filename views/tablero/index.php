<?php
    $title = "Tablero";
    date_default_timezone_set($_SESSION["zona_horaria"]);
    setlocale(LC_ALL,"es_ES@euro","es_ES","esp");
    $fecha = date("d-m-Y h:i A");
    $fechaa = date("d-m-Y 07:00");
?>
<input type="hidden" id="moneda" value="<?php echo Session::get('moneda'); ?>"/>
<input type="hidden" id="bloqueo" value="<?php echo Session::get('bloqueo'); ?>"/>
<br>
<div class="row">
    <div class="col-sm-12 col-lg-3">
            <div>
                <div class="row floating-labels m-t-20">
                    <div class="col-sm-12">
                        <div class="form-group">
                            <select class="selectpicker form-control" name="id_caja" id="id_caja" data-style="form-control btn-default" data-size="5" data-live-search-style="begins" data-live-search="true" autocomplete="off" required>
                                <?php foreach($this->Caja as $key => $value): ?>
                                    <option value="<?php echo $value['id_apc']; ?>"><?php echo $value['desc_caja']; ?></option>
                                <?php endforeach; ?>
                            </select>
                            <span class="bar"></span>
                            <label for="id_caja">Resumen general</label>
                        </div>
                    </div>
                </div>
            </div>
    </div>

    <div class="col-sm-12 col-lg-12">
        <div class="row">
            <div class="col-sm-6 col-lg-4">
                <div class="card card-outline-success">
                    <div class="card-body">
                        <h4 class="card-title">Ventas en efectivo</h4>
                        <div class="text-right"> <span class="text-muted">Total</span>
                            <h1 class="font-light"><sup><i class="ti-arrow-up text-success"></i></sup> <span class="pago_efe"></span></h1>
                        </div>
                        <span class="text-success pago_efe_porcentaje"></span>
                        <div class="progress">
                            <div class="progress-bar bg-success pago_efe_progressbar" role="progressbar" style="width: 0.00%; height: 6px;" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <span class="font-13">No incluye apertura de caja</span>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-4">
                <div class="card card-outline-info">
                    <div class="card-body">
                        <h4 class="card-title">Ventas otros medio de pago</h4>
                        <div class="text-right"> <span class="text-muted">Total</span>
                            <h1 class="font-light"><sup><i class="ti-arrow-up text-info"></i></sup> <span class="pago_tar"></span></h1>
                        </div>
                        <span class="text-info pago_tar_porcentaje"></span>
                        <div class="progress">
                            <div class="progress-bar bg-info pago_tar_progressbar" role="progressbar" style="width: 0.00%; height: 6px;" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <span class="font-13">Incluye tarjetas y otros medio de pago <i class="ti-info-alt text-warning font-10" data-original-title="Yape, Transferencias, Visa, Mastercard, etc." data-toggle="tooltip" data-placement="top"></i></span>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-4">
                <div class="card card-outline-primary">
                    <div class="card-body">
                        <h4 class="card-title">Total de ventas</h4>
                        <div class="text-right"> <span class="text-muted">Efectivo + + OMP <i class="ti-info-alt text-warning font-10" data-original-title="Otros medio de pago"data-toggle="tooltip" data-placement="top"></i></span>
                            <h1 class="font-light"><sup><i class="ti-arrow-up text-primary"></i></sup> <span class="total_ventas"></span></h1>
                        </div>
                        <span class="text-primary">100%</span>
                        <div class="progress">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: 100.00%; height: 6px;" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <span class="font-13">Incluye descuentos, comisi&oacute;n delivery</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6 col-lg-5 col-xl-4 order-2 order-md-1">
        <div class="card card-body p-0">
            <h4 class="card-title p-t-20 p-l-20 p-r-20 m-b-10">Por canal de venta</h4>
            <ul class="nav nav-tabs justify-content-end customtab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" data-toggle="tab" href="#tab1" role="tab" aria-selected="true"><span class="hidden-sm-up">Aprobadas</span> <span class="hidden-xs-down">Aprobadas</span></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#tab2" role="tab" aria-selected="false"><span class="hidden-sm-up">Anuladas</span> <span class="hidden-xs-down">Anuladas</span></a>
                </li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane active" id="tab1" role="tabpanel">
                    <div class="message-box p-0">
                        <div class="message-widget m-t-0">
                            <!-- Message -->
                            <a href="#">
                                <div class="user-img"><span class="round bg-success"><i class="fas fa-box"></i></span></div>
                                <div class="mail-contnet">
                                    <h5 class="monto-venta-salon"></h5> <span class="mail-desc">Nro de ventas: <span class="font-14 cantidad-venta-salon"></span></span> <span class="time">SALONES</span>
                                </div>
                            </a>
                            <!-- Message -->
                            <a href="#">
                                <div class="user-img"><span class="round bg-primary"><i class="fas fa-shopping-basket"></i></span></div>
                                <div class="mail-contnet">
                                    <h5 class="monto-venta-mostrador"></h5> <span class="mail-desc">Nro de ventas: <span class="font-14 cantidad-venta-mostrador"></span></span> <span class="time">MOSTRADOR</span>
                                </div>
                            </a>
                            <!-- Message -->
                            <a href="#">
                                <div class="user-img"><span class="round bg-warning"><i class="fas fa-motorcycle"></i></span></div>
                                <div class="mail-contnet">
                                    <h5 class="monto-venta-delivery"></h5> <span class="mail-desc">Nro de ventas: <span class="font-14 cantidad-venta-delivery"></span></span> <span class="time">DELIVERY</span>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="tab-pane" id="tab2" role="tabpanel">
                    <div class="message-box p-0">
                        <div class="message-widget m-t-0">
                            <!-- Message -->
                            <a href="#">
                                <div class="user-img"><span class="round bg-success"><i class="fas fa-box"></i></span></div>
                                <div class="mail-contnet">
                                    <h5 class="monto-venta-salon-i"></h5> <span class="mail-desc">Nro de ventas: <span class="font-14 cantidad-venta-salon-i"></span></span> <span class="time">SALONES</span>
                                </div>
                            </a>
                            <!-- Message -->
                            <a href="#">
                                <div class="user-img"><span class="round bg-primary"><i class="fas fa-shopping-basket"></i></span></div>
                                <div class="mail-contnet">
                                    <h5 class="monto-venta-mostrador-i"></h5> <span class="mail-desc">Nro de ventas: <span class="font-14 cantidad-venta-mostrador-i"></span></span> <span class="time">MOSTRADOR</span>
                                </div>
                            </a>
                            <!-- Message -->
                            <a href="#">
                                <div class="user-img"><span class="round bg-warning"><i class="fas fa-motorcycle"></i></span></div>
                                <div class="mail-contnet">
                                    <h5 class="monto-venta-delivery-i"></h5> <span class="mail-desc">Nro de ventas: <span class="font-14 cantidad-venta-delivery-i"></span></span> <span class="time">DELIVERY</span>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-7 col-xl-8 order-1 order-md-2">
        <div class="row">
            
            <div class="col-lg-6">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex no-block align-items-center">
                            <i class="fas fa-donate display-6 text-white"></i>
                            <div class="ml-3 mt-2">
                                <h4 class="font-weight-medium mb-0 text-white">Ingresos caja</h4>
                                <h5 class="text-white ingresos"></h5>
                            </div>
                        </div>
                    </div>
                </div>    
            </div>

            <div class="col-lg-6">
                <div class="card bg-danger text-white">
                    <div class="card-body">
                        <div class="d-flex no-block align-items-center">
                            <i class="fas fa-hand-holding-usd display-6 text-white"></i>
                            <div class="ml-3 mt-2">
                                <h4 class="font-weight-medium mb-0 text-white">Egresos caja</h4>
                                <h5 class="text-white egresos"></h5>
                            </div>
                        </div>
                    </div>
                </div>    
            </div>

            <div class="col-lg-6">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex no-block align-items-center">
                            <i class="fas fa-tag display-6 text-white"></i>
                            <div class="ml-3 mt-2">
                                <h4 class="font-weight-medium mb-0 text-white">Descuentos</h4>
                                <h5 class="text-white descuentos"></h5>
                            </div>
                        </div>
                    </div>
                </div>    
            </div>
            <div class="col-lg-6">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex no-block align-items-center">
                            <i class="fas fa-motorcycle display-6 text-white"></i>
                            <div class="ml-3 mt-2">
                                <h4 class="font-weight-medium mb-0 text-white">Comisi&oacute;n delivery</h4>
                                <h5 class="text-white comision-delivery"></h5>
                            </div>
                        </div>
                    </div>
                </div>    
            </div>
        </div>
    </div>


</div>



<div class="row">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-body p-0">
                <div class="d-flex no-block p-20">
                    <h4 class="card-title">10 Productos mas vendidos</h4>
                </div>
                <div class="table-responsive b-t m-b-0">
                    <table class="table stylish-table">
                        <thead class="table-head">
                            <tr>
                                <th colspan="2">Producto</th>
                                <th>Ventas</th>
                                <th>Importe</th>
                                <th class="text-right">% Ventas</th>
                            </tr>
                        </thead>
                        <tbody id="lista_productos"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-body p-0">
                <div class="d-flex no-block p-20">
                    <h4 class="card-title">10 Platos mas vendidos</h4>
                </div>
                <div class="table-responsive b-t m-b-0">
                    <table class="table stylish-table">
                        <thead class="table-head">
                            <tr>
                                <th colspan="2">Producto</th>
                                <th>Ventas</th>
                                <th>Importe</th>
                                <th class="text-right">% Ventas</th>
                            </tr>
                        </thead>
                        <tbody id="lista_platos"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>