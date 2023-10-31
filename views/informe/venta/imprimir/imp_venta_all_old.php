<?php
require_once ('public/lib/print/num_letras.php');
require_once ('public/lib/pdf/cellfit.php');
require_once ('public/lib/phpqrcode/qrlib.php');

class FPDF_CellFiti extends FPDF_CellFit
{
	function AutoPrint($dialog=false)
	{
		//Open the print dialog or start printing immediately on the standard printer
		$param=($dialog ? 'true' : 'false');
		$script="print($param);";
		$this->IncludeJS($script);
	}

	function AutoPrintToPrinter($server, $printer, $dialog=false)
	{
		//Print on a shared printer (requires at least Acrobat 6)
		$script = "var pp = getPrintParams();";
		if($dialog)
			$script .= "pp.interactive = pp.constants.interactionLevel.full;";
		else
			$script .= "pp.interactive = pp.constants.interactionLevel.automatic;";
		$script .= "pp.printerName = '\\\\\\\\".$server."\\\\".$printer."';";
		$script .= "print(pp);";
		$this->IncludeJS($script);
	}
}

define('EURO',chr(128));
$pdf = new FPDF_CellFiti('P','mm',array(72,250));
$pdf->AddPage();
$pdf->SetMargins(5,0,0,0);

// dimension de 
// CABECERA
if($this->empresa['logo']){
	$url_logo = URL."public/images/".$this->empresa['logo'];
	$pdf->Image($url_logo,L_CENTER,2,L_DIMENSION,0,L_FORMATO);
	$pdf->Cell(60,L_ESPACIO,'',0,1,'C');
}
$pdf->SetFont('Helvetica','',7);
$pdf->Cell(60,4,'',0,1,'C');
$pdf->SetFont('Helvetica','',12);
$pdf->Cell(60,4,utf8_decode($this->empresa['nombre_comercial']),0,1,'C');
// $pdf->Cell(60,4,utf8_decode($url_logo),0,1,'C');
$pdf->SetFont('Helvetica','',9);
$pdf->Cell(60,4,utf8_decode(Session::get('tribAcr')).': '.utf8_decode($this->empresa['nit']),0,1,'C');
$pdf->MultiCell(60,4,utf8_decode($this->empresa['direccion_comercial']),0,'C');
$pdf->Cell(60,4,'TELF: '.utf8_decode($this->empresa['celular']),0,1,'C');
 
// DATOS FACTURA
$elec = (($this->dato->id_tdoc == 1 || $this->dato->id_tdoc == 2) && Session::get('impuestos') == 1) ? 'ELECTRONICA' : '';     
$pdf->Ln(3);
$pdf->SetFont('Helvetica', 'B', 10);
$pdf->Cell(60,4,utf8_decode($this->dato->desc_td).' '.$elec,0,1,'C');
$pdf->Cell(60,4,utf8_decode($this->dato->ser_doc).'-'.utf8_decode($this->dato->nro_doc),0,1,'C');
$pdf->Ln(2);
$pdf->SetFont('Helvetica', '', 8);
$pdf->Cell(60,4,'FECHA DE EMISION: '.date('d-m-Y h:i A',strtotime($this->dato->fec_ven)),0,1,'');
if($this->dato->id_tped == 1){
	$pdf->Cell(60,4,utf8_decode('TIPO DE ATENCION').': '.utf8_decode($this->dato->Pedido->desc_salon).' - MESA: '.utf8_decode($this->dato->Pedido->nro_mesa),0,1,'');
}else if ($this->dato->id_tped == 2){
	$pdf->Cell(60,4,'TIPO DE ATENCION: MOSTRADOR',0,1,'');
}else if ($this->dato->id_tped == 3){
	$pdf->Cell(60,4,'TIPO DE ATENCION: DELIVERY',0,1,'');
}
$pdf->MultiCell(60,4,'CLIENTE: '.utf8_decode($this->dato->Cliente->nombre),0,1,'');
if($this->dato->Cliente->tipo_cliente == 1){
$pdf->Cell(60,4,utf8_decode(Session::get('diAcr')).': '.utf8_decode($this->dato->Cliente->ci),0,1,'');
}else{
$pdf->Cell(60,4,utf8_decode(Session::get('tribAcr')).': '.utf8_decode($this->dato->Cliente->nit),0,1,'');
}
$pdf->MultiCell(60,4,'TELEFONO: '.utf8_decode($this->dato->Cliente->telefono),0,1,'');
$pdf->MultiCell(60,4,'DIRECCION: '.utf8_decode($this->dato->Cliente->direccion),0,1,'');
$pdf->MultiCell(60,4,'REFERENCIA: '.utf8_decode($this->dato->Cliente->referencia),0,1,'');
 
// COLUMNAS
$pdf->SetFont('Helvetica', 'B', 9);
$pdf->Cell(35, 10, 'PRODUCTO', 0);
$pdf->Cell(5, 10, 'CANT.',0,0,'R');
$pdf->Cell(10, 10, 'P.U.',0,0,'R');
$pdf->Cell(10, 10, 'IMP.',0,0,'R');
$pdf->Ln(8);
$pdf->Cell(60,0,'','T');
$pdf->Ln(1);
 
// PRODUCTOS
$total_ope_gravadas = 0;
$total_iva_gravadas = 0;
$total_ope_exoneradas = 0;
$total_iva_exoneradas = 0;

foreach($this->dato->Detalle as $d){

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

	$pdf->SetFont('Helvetica', '', 9);
	$pdf->MultiCell(35,4,utf8_decode($d->nombre_producto),0,'L'); 
	$pdf->Cell(40, -4, $d->cantidad,0,0,'R');
	$pdf->Cell(10, -4, $d->precio_unitario,0,0,'R');
	$pdf->Cell(10, -4, number_format(($d->cantidad * $d->precio_unitario),2),0,0,'R');
	$pdf->Ln(1);
}
 
// SUMATORIO DE LOS PRODUCTOS Y EL IVA
$sbt = (($this->dato->total + $this->dato->comis_tar + $this->dato->comis_del - $this->dato->desc_monto) / (1 + $this->dato->iva));
$iva = ($sbt * $this->dato->iva);
$pdf->SetFont('Helvetica', '', 9);
$pdf->Cell(60,0,'','T');
$pdf->Ln(0);    
$pdf->Cell(25, 10, 'SUB TOTAL', 0);    
$pdf->Cell(20, 10, '', 0);
$pdf->Cell(15, 10, number_format(($this->dato->total),2),0,0,'R');
if($this->dato->id_tped == 3){
$pdf->Ln(4); 
$pdf->Cell(25, 10, 'COSTO POR DELIVERY', 0);    
$pdf->Cell(20, 10, '', 0);
$pdf->Cell(15, 10, number_format(($this->dato->comis_del),2),0,0,'R');
}
$pdf->Ln(4); 
$pdf->Cell(25, 10, 'DESCUENTO', 0);    
$pdf->Cell(20, 10, '', 0);
$pdf->Cell(15, 10, number_format(($this->dato->desc_monto),2),0,0,'R');
$pdf->Ln(4);
$pdf->Cell(25, 10, 'OP. GRAVADA', 0);    
$pdf->Cell(20, 10, '', 0);
$pdf->Cell(15, 10, number_format($total_ope_gravadas,2),0,0,'R');
$pdf->Ln(4);
$pdf->Cell(25, 10, 'OP. EXONERADA', 0);    
$pdf->Cell(20, 10, '', 0);
$pdf->Cell(15, 10, number_format($total_ope_exoneradas,2),0,0,'R');
$pdf->Ln(4);    
$pdf->Cell(25, 10,Session::get('impAcr'), 0);    
$pdf->Cell(20, 10, '', 0);
$pdf->Cell(15, 10, number_format(($total_iva_gravadas + $total_iva_exoneradas),2),0,0,'R');
$pdf->Ln(9); 
$pdf->Cell(60,0,'','T');  
$pdf->Ln(0);
$pdf->Cell(25, 10, 'IMPORTE A PAGAR', 0);    
$pdf->Cell(20, 10, '', 0);
$pdf->Cell(15, 10, number_format(($this->dato->total + $this->dato->comis_del - $this->dato->desc_monto),2),0,0,'R');
$pdf->Ln(8);
$pdf->MultiCell(60,4,'SON: '.numtoletras($this->dato->total + $this->dato->comis_del - $this->dato->desc_monto),0,'L');
$pdf->Ln(2);
$pdf->Cell(60,0,'','T');
if($this->dato->id_tpag == 1){
$pdf->Ln(0);
$pdf->Cell(25, 10, 'EFECTIVO', 0);    
$pdf->Cell(20, 10, '', 0);
$pdf->Cell(15, 10, number_format(($this->dato->pago_efe),2),0,0,'R');
} else if($this->dato->id_tpag == 2){
$pdf->Ln(0);
$pdf->Cell(25, 10, 'TARJETA', 0);    
$pdf->Cell(20, 10, '', 0);
$pdf->Cell(15, 10, number_format(($this->dato->pago_tar),2),0,0,'R');
} else if($this->dato->id_tpag == 3){
$pdf->Ln(0);
$pdf->Cell(25, 10, 'EFECTIVO', 0);    
$pdf->Cell(20, 10, '', 0);
$pdf->Cell(15, 10, number_format(($this->dato->pago_efe),2),0,0,'R');
$pdf->Ln(4);
$pdf->Cell(25, 10, 'TARJETA', 0);    
$pdf->Cell(20, 10, '', 0);
$pdf->Cell(15, 10, number_format(($this->dato->pago_tar),2),0,0,'R');
}
if($this->dato->id_tpag == 1 OR $this->dato->id_tpag == 3){
$pdf->Ln(8);
$pdf->Cell(60,0,'','T');
$pdf->Ln(0);
$pdf->Cell(25, 10, 'PAGO CON', 0);    
$pdf->Cell(20, 10, '', 0);
$pdf->Cell(15, 10, number_format(($this->dato->pago_efe_none),2),0,0,'R');
$pdf->Ln(4);
$pdf->Cell(25, 10, 'VUELTO', 0);    
$pdf->Cell(20, 10, '', 0);
$vuelto = ($this->dato->pago_efe_none - $this->dato->pago_efe);
$pdf->Cell(15, 10, strtoupper(number_format(($vuelto),2)),0,0,'R');
} 

if($this->dato->id_tpag > 3) {
$pdf->Ln(0);
$pdf->Cell(25, 10, 'PAGO CON', 0);    
$pdf->Cell(20, 10, '', 0);
$pdf->Cell(15, 10, $this->dato->desc_tp,0,0,'R');
}
$pdf->Ln(10);
// CODIGO QR
if(($this->dato->id_tdoc == 1 || $this->dato->id_tdoc == 2) && Session::get('') == 1){
	$td="";
	$tc="";
	$tdc="";
	if($this->dato->id_tdoc==1){
		$tc="03";
		$tdd="1";
		$tdc=$this->dato->Cliente->ci;
	}
	if($this->dato->id_tdoc==2){
		$tc="01";
		$tdd="6";
		$tdc=$this->dato->Cliente->nit;
	}
	/*$nombreqr=$tc."-".$td.$this->dato->ser_doc."-".$this->dato->nro_doc;
	$text_qr = $this->empresa['nit'] . '|' . $tc . '|' . $this->dato->ser_doc . '|' . $this->dato->nro_doc . '|' . $tdd . '|' . $tdc . '|';
    //$ruta_qr = 'api_fact/UBL21/archivos_xml_sunat/imgqr/QR_' . $nombreqr . '.png';
    //$qr = 'api_fact/UBL21/archivos_xml_sunat/imgqr/QR_' . $nombreqr . '.png';

    if (!file_exists($ruta_qr)) {
        QRcode::png($text_qr, $qr, 'Q', 15, 0);
    }
	*/
    $pdf->Cell(0,0,$pdf->Image($ruta_qr,26,$pdf->GetY(),20),0,0,'C');
    $pdf->Ln(20);
}
 
// PIE DE PAGINA
$pdf->Ln(5);
// $pdf->Cell(60,0,'DESARROLLADO POR SOLTEC',0,1,'C');
// $pdf->Ln(4);
$pdf->Cell(60,0,'GRACIAS POR SU PREFERENCIA',0,1,'C');
$pdf->Ln(5);
$pdf->Cell(60,0,'',0,1,'C');
$pdf->Ln(10);
//$pdf->Output('ticket.pdf','F');
$pdf->Output('ticket.pdf','I');
?>