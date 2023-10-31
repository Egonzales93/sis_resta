<?php
// /venta/impresion_comanda/
require_once ('public/lib/print/num_letras.php');
require_once ('public/lib/pdf/cellfit.php');

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


// Array ( [pedido_tipo] => 1 [pedido_numero] => SALON 01 [pedido_cliente] => MESA: 3B [pedido_mozo] => CINTHYA ELISA CHAVEZ [correlativo_imp] => 000046 [nombre_imp] => COCINA [nombre_pc] => DESKTOP-F1QI6FD [codigo_anulacion] => 0 [items] => Array ( [0] => Array ( [producto_id] => 8 [area_id] => 1 [nombre_imp] => COCINA [producto] => CHULETA A LO POBRE [presentacion] => PLATO [comentario] => PALTA, ARROZ, ENSALADA FRESCA [cantidad] => 1 [precio] => 18.00 [total] => 18 [id] => 0 ) ) )



date_default_timezone_set('America/La_Paz');
setlocale(LC_ALL,"es_ES@euro","es_ES","esp");
$hora = date("g:i:s A");
$fecha = date("d/m/y");

$data = json_decode($_GET['data'],true);

define('EURO',chr(128));
$pdf = new FPDF_CellFiti('P','mm',array(75,200));
$pdf->AddPage();
$pdf->SetMargins(0,0,0,0);
$pdf->SetFont('Helvetica','',9);
$pdf->Cell(72,4,''.$data['nombre_imp'].'',0,1,'L');
$pdf->SetFont('Helvetica','',9);
$pdf->Cell(72,0,'================================',0,1,'C');
$pdf->SetFont('Helvetica','',13);
$pdf->Ln(1);
$pdf->Cell(72,4,'',0,1,'C');
if($data['pedido_tipo'] == 1){
	$pdf->SetFont('Helvetica','',14);
	$pdf->Cell(72,4,'MESA',0,1,'C');
}elseif($data['pedido_tipo'] == 2){
	$pdf->SetFont('Helvetica','',14);
	$pdf->Cell(72,4,'MOSTRADOR',0,1,'C');
}elseif($data['pedido_tipo'] == 3){
	$pdf->SetFont('Helvetica','',14);
	$pdf->Cell(72,4,'DELIVERY',0,1,'C');
}
if($data['codigo_anulacion'] <> 1){
	$pdf->Ln(1);
	$pdf->SetFont('Helvetica','',14);
	$pdf->Cell(72,4,'Comanda #'.$data['correlativo_imp'].'',0,1,'C');
	$pdf->Cell(72,4,'',0,1,'C');
	$pdf->SetFont('Helvetica','',9);
	$pdf->Cell(72,0,'================================',0,1,'C');
}
$pdf->Ln(3);
$pdf->SetFont('Helvetica','',13);
$pdf->Cell(72,4,"".$fecha." - ".$hora."",0,1,'R');
$pdf->Ln(1);
if($data['pedido_tipo'] == 1){
	$pdf->SetFont('Helvetica','',14);
	$pdf->Cell(72,4,$data['pedido_numero']." - ".$data['pedido_cliente']."\n",0,1,'R');
	$pdf->Ln(1);
	$pdf->SetFont('Helvetica','',10);
	$pdf->Cell(72,4,"Mesero: ".$data['pedido_mozo']."\n",0,1,'R');
}elseif($data['pedido_tipo'] == 2){
	$pdf->SetFont('Helvetica','',12);
	$pdf->MultiCell(72,6,"LLEVAR #".$data['pedido_numero']." - CLIENTE:".$data['pedido_cliente']."\n",0,'R'); 
	$pdf->Ln(2);
}elseif($data['pedido_tipo'] == 3){
	$pdf->SetFont('Helvetica','',12);
	$pdf->MultiCell(72,6,"DELIVERY #".$data['pedido_numero']." - CLIENTE:".$data['pedido_cliente']."\n",0,'R'); 
	$pdf->Ln(2);
}
$pdf->SetFont('Helvetica', '', 9);
$pdf->Cell(72,4,'___________________________________',0,1,'C');
$pdf->Ln(3);
// PRODUCTOS
foreach($data['items'] as $value){
	$pdf->SetFont('Helvetica', 'B', 11);
	$pdf->MultiCell(72,6,utf8_decode($value['cantidad']).' '.utf8_decode($value['producto']).' | '.utf8_decode($value['presentacion']),0,'L'); 
$pdf->SetFont('Helvetica', 'B', 11);
$pdf->MultiCell(0,4,"*".$value['comentario'],0,'L'); 
$pdf->Ln(1);
}

$pdf->SetFont('Helvetica', '', 9);
$pdf->Cell(72,4,'_______________________________',0,1,'C');
$pdf->Ln(5);
$pdf->SetFont('Helvetica', 'B', 15);
$pdf->Cell(72,0,'*****************************',0,1,'C');
// PIE DE PAGINA
$pdf->Ln(10);
$pdf->Output('ticket.pdf','i');

?>

