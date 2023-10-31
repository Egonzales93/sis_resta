<?php
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
define('EURO',chr(128));
$pdf = new FPDF_CellFiti('P','mm',array(80,200));
$pdf->AddPage();
$pdf->SetMargins(0,0,0,0);
 

$pdf->Ln(3);
$pdf->SetFont('Courier','B',10);
$pdf->Cell(72,4,'RECIBO DE GASTO',0,1,'C'); 
$pdf->Ln(3);
$pdf->SetFont('Courier','',9);
$pdf->Cell(15, 4, 'FECHA:', 0);    
$pdf->Cell(20, 4, '', 0);
$pdf->Cell(37, 4, date('d-m-Y h:i A',strtotime($this->dato->fecha_registro)),0,1,'R');   
$pdf->Ln(3);
$pdf->Cell(15, 4, 'TIPO:', 0);    
$pdf->Cell(20, 4, '', 0);
foreach($this->dato->tipogasto as $de){
    $pdf->Cell(37, 4, utf8_decode($de->descripcion),0,1,'R'); 
}
$pdf->Ln(3);
if($this->dato->id_per >0){
    $pdf->Cell(15, 4, 'TRABAJADOR:', 0);    
    $pdf->Cell(20, 4, '', 0);
    $pdf->Cell(37, 4, $this->dato->responsable,0,1,'R'); 
    $pdf->Ln(3);
    $pdf->Cell(15, 4, 'IMPORTE DE:', 0);    
    $pdf->Cell(20, 4, '', 0);
    $pdf->Cell(37, 4, "Bs/. ".$this->dato->importe,0,1,'R'); 
    $pdf->Ln(3);
}else{
    $pdf->Cell(15, 4, 'ENTREGADO A:', 0);    
    $pdf->Cell(20, 4, '', 0);
    $pdf->Cell(37, 4, $this->dato->responsable,0,1,'R'); 
    $pdf->Ln(3);
    $pdf->Cell(15, 4, 'IMPORTE DE:', 0);    
    $pdf->Cell(20, 4, '', 0);
    $pdf->Cell(37, 4, "Bs/. ".$this->dato->importe,0,1,'R'); 
    $pdf->Ln(3);
}
$pdf->Cell(15, 4, 'MOTIVO:', 0);    
$pdf->Cell(20, 4, '', 0);
// $pdf->Cell(37, 4, $this->dato->motivo,0,1,'R'); 
$pdf->MultiCell(37,4,utf8_decode($this->dato->motivo),0,'R');

// $pdf->SetFont('Courier','B',9);
// $pdf->Cell(72,4,'RESPONSABLE',0,1,'');
// $pdf->SetFont('Courier','',9);
// $pdf->Cell(72, 4, $this->dato->responsable,0,1,'C');  
// $pdf->Ln(3);
// $pdf->SetFont('Courier','B',9);
// $pdf->Cell(72,4,'MOTIVO DEL INGRESO',0,1,'');
// $pdf->Ln(1);
// $pdf->SetFont('Courier','',9);
// $pdf->Cell(72, 4, $this->dato->motivo,0,1,'C');  
// $pdf->Ln(3);
// $pdf->SetFont('Courier','B',9);
// $pdf->Cell(15, 4, 'IMPORTE DE:', 0);    
// $pdf->Cell(20, 4, '', 0);
// $pdf->Cell(37, 4, "Bs/. ".$this->dato->importe,0,1,'R');  

// COLUMNAS
$pdf->Ln(6); 
$pdf->SetFont('Courier','',9);
$pdf->Cell(72,4,'DATOS DE IMPRESION',0,1,'');
date_default_timezone_set($_SESSION["zona_horaria"]);
setlocale(LC_ALL,"es_ES@euro","es_ES","esp");
$pdf->Cell(72,4,'FECHA: '.date("d-m-Y h:i A"),0,1,'');
$pdf->Ln(8);
if($this->dato->id_per >0){
    $pdf->Cell(72,4,'___________________________________',0,1,'C');
    $pdf->Cell(72,4,$this->dato->responsable,0,1,'C');
}else{
$pdf->Cell(72,4,'___________________________________',0,1,'C');
foreach($this->dato->usuario as $d){
$pdf->Cell(72,4,utf8_decode($d->nombres)." ".utf8_decode($d->ape_paterno)." ".utf8_decode($d->ape_materno),0,1,'C');
}
}
 
// PIE DE PAGINA
$pdf->Ln(10);
$pdf->Output('ticket.pdf','i');
?>