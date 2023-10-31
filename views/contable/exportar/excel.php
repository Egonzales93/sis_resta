<style>
    .text-center {
        text-align: center;
    }

    .font-weight {
        font-weight: bold;
    }
</style>

<?php
    $col_span = 25;
    // DATOS DE EMPRESA
    $Empresa = $this->Empresa;
?>
<table id="bootstrap-table">
    <tr>
        <td colspan="<?php print $col_span; ?>"><?php print(utf8_decode($this->Empresa['nombre_comercial'])); ?></td>
    </tr>
    <tr>
        <td colspan="<?php print $col_span; ?>"><?php print(utf8_decode($this->Empresa['nit'])); ?></td>
    </tr>
    <tr>
        <td colspan="<?php print $col_span; ?>" class="text-center font-weight">FORMATO 14.1 : "REGISTRO DE VENTAS E INGRESOS DEL
            PERIODO <?php print(utf8_decode($this->periodo)); ?>"
        </td>
    </tr>
    <tr>
        <td colspan="2">
            NUMERO CORRELATIVO DEL REGISTRO O CUO.
        </td>
        <td>
            FECHA DE EMISION DEL COMPROBANTE DE PAGO O EMISION DEL DOCUMENTO
        </td>
        <td>
            FECHA VENC.
        </td>
        <td colspan="3">
            COMPROBANTE DE PAGO
        </td>
        <td colspan="3">
            INFORMACON DE CLIENTE
        </td>
        <td>
            VALOR<br/>FACTURADO<br/>EXPORTACION
        </td>
        <td>
            BASE<br/>IMPONIBLE<br/>GRAVADA
        </td>
        <td colspan="2">
            IMPORTE TOTAL
        </td>
        <td>
            ISC
        </td>
        <td>VENTA DIFERIDA</td>
        <td>
            IVA Y/O<br/>IMP.
        </td>
        <td>
            OTROS<br/>TRIBUTOS
        </td>
        <td>
            IMPORTE TOTAL
        </td>
        <td>
            TIPO DE<br/>CAMBIO
        </td>
        <td>
            MONEDA
        </td>
        <td colspan="4">
            REFERENCIA DEL COMPROBANTE O<br/>
            DOC. ORIGINAL QUE SE MODIFICA
        </td>
    </tr>
    <tr>
        <td colspan="2"></td>
        <td></td>
        <td></td>
        <td>TIPO</td>
        <td>SERIE</td>
        <td>NUMERO</td>
        <td>TIPO</td>
        <td>N.I.T.</td>
        <td>APELLIDOS Y NOMBRES</td>
        <td></td>
        <td></td>
        <td>EXONERADA</td>
        <td>INAFECTA</td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td>FECHA</td>
        <td>TIPO</td>
        <td>SERIE</td>
        <td>Nro.COMP.</td>
    </tr>
    <?php
        $loop = 1;
        // print_r($this->dato);
        foreach($this->dato as $id => $row) {
    ?>
     <tr>
            <?php
            
            $date_of_issue = date('d-m-Y',strtotime($row->fec_ven));
            $tipo = array('BOLETA DE VENTA'=>"03","FACTURA" =>'01');
            $document_type_id = $tipo[$row->desc_td];
            
            
            $total_ope_gravadas = 0;
            $total_iva_gravadas = 0;
            $total_ope_exoneradas = 0;
            $total_iva_exoneradas = 0;

            foreach($row->Detalle as $d){

                if($d->codigo_afectacion == '10'){
                    $total_ope_gravadas = $total_ope_gravadas + $d->valor_venta;
                    $total_iva_gravadas = $total_iva_gravadas + $d->total_iva;
                    $total_ope_exoneradas = $total_ope_exoneradas + 0;
                    $total_iva_exoneradas = $total_iva_exoneradas + 0;
                } else{
                    $total_ope_gravadas = $total_ope_gravadas + 0;
                    $total_iva_gravadas = $total_iva_gravadas + 0;
                    $total_ope_exoneradas = $total_ope_exoneradas + $d->valor_venta;
                    $total_iva_exoneradas = $total_iva_exoneradas + $d->total_iva;
                }
            }

            $series = $row->ser_doc;
            $number = $row->nro_doc;

            if($row->Cliente->tipo_cliente == '2'){
                $customer_identity_document_type_id = '6';
                $customer_number = $row->Cliente->nit;
            }else{
                $customer_identity_document_type_id = $row->Cliente->tipo_cliente;
                $customer_number = $row->Cliente->ci;
            }
            $customer_name = $row->Cliente->nombre;
            $exchange_rate_sale = '';
            $currency_type_symbol = 'S/';

            if ($row->estado == 'i') : 
                $total_exportation = '0';
                $total_taxed = '0';
                $total_exonerated = '0';
                $total_unaffected = '0';
                $total_plastic_bag_taxes = '0';
                $total_iva = '0';
                $total = '0';
            else : 
                $total_exportation = '0';
                $total_taxed = $total_ope_gravadas;
                $total_exonerated = $total_ope_exoneradas;
                $total_unaffected = '0';
                $total_plastic_bag_taxes = '0';
                $total_iva = $total_iva_gravadas;
                $total = $total_taxed + $total_iva + $total_exonerated; // temporal 
                // $total = $row->total;
            endif; 


                // $total_exportation = '0';
                // $total_taxed = number_format($row->total / 1.18, 2);
                // $total_exonerated = '0';
                // $total_unaffected = '0';
                // $total_plastic_bag_taxes = '0';
                // $total_iva = number_format($total_taxed * 0.18, 2);
                // $total = $row->total;
            //     $total_isc = $row['total_isc'];
            //     $ok = 1;

            // }
            ?>
            <td>06</td>
            <td><?php print $loop++; ?></td>
            <td><?php print $date_of_issue ; ?></td>
            <td></td>
            <td><?php print $document_type_id ; ?></td>
            <td><?php print $series ; ?></td>
            <td><?php print $number ; ?></td>
            <td><?php print $customer_identity_document_type_id ; ?></td>
            <td><?php print $customer_number ; ?></td>
            <td><?php print $customer_name ; ?></td>

            <td><?php print $total_exportation ; ?></td>

            <td><?php print$total_taxed ; ?></td>
            <td><?php print $total_exonerated ; ?></td>
            <td><?php print $total_unaffected  ; ?></td>
            <!--  print-- Aqui deberia ir $total_isc --;-->
            <td><?php print $total_plastic_bag_taxes ; ?> </td>
            <td></td>
            <td><?php print $total_iva ; ?></td>
            <td></td>
            <td><?php print $total ; ?></td>

            <td><?php print $exchange_rate_sale ; ?></td>
            <td><?php print $currency_type_symbol ; ?></td>
            <!-- @if($row['affected_document']) -->
    
            <!-- @else -->
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            <!-- @endif -->
        </tr>
    <?php }?>
</table>