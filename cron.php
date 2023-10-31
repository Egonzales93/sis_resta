<?php
// actualizado al 28-10-21 
set_time_limit (1800); 

require 'config.php';
require 'libs/Session.php';
require 'libs/Database.php';
//require_once 'api_fact/controller/api.php';
require_once ('public/lib/print/num_letras.php');
require_once ('public/lib/pdf/cellfit.php');
Session::set('rol', '2');


    $db = new Database(DB_TYPE, DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_CHARSET);

    $ds = $db->prepare("SELECT * FROM tm_venta WHERE estado <> 'i' AND id_tipo_doc != '3' AND (enviado_impuestos is null or enviado_impuestos = '0')");
    $ds->execute();
    $data_s = $ds->fetchAll();

    foreach($data_s as $row):
         print $row['id_venta'];
        $cod_ven = $row['id_venta'];
        //$invoice = new ApiImpuestos();
       // $data = $invoice->sendDocSunaht($cod_ven,1); 
    endforeach;