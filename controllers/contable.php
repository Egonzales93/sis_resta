<?php 
	Session::init(); 
	$ver = (Session::get('rol') == 1 OR Session::get('rol') == 2 OR Session::get('rol') == 3) ? '' :  header('location: ' . URL . 'err/danger'); 
	$ver = (Session::get('bloqueo') == 0 OR Session::get('bloqueo') == null) ? '' :  header('location: ' . URL . 'err/bloqueo');
?>
<?php

class Contable extends Controller {

	function __construct() {
		parent::__construct();
	}
	
	function index() 
	{	
		Auth::handleLogin();
		$this->view->title_page = 'Contabilidad';
		$this->view->js = array('contable/js/conta.js');
		$this->view->render('contable/index', false);
	}
    function exportar() 
	{	
		Auth::handleLogin();
		$this->view->title_page = 'Exportar Ventas';
        $this->view->TipoDocumento = $this->model->TipoDocumento();
        $this->view->js = array('contable/js/exportar.js');
		$this->view->render('contable/exportar/index', false);
	}
    function excel() 
	{	
        header("Pragma: public");
        header("Expires: 0");
        $filename = "Reporte_Formato_Ventas_".date("Ymdhis").".xls";
        header("Content-type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=$filename");
        header("Pragma: no-cache");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        $this->view->Empresa = $this->model->Empresa();
        $this->view->dato = $this->model->venta_all_list($_POST);
        $this->view->periodo = date('Y-m',strtotime($_POST['start']));
        $this->view->render('contable/exportar/excel', true);
	}
    function validador() 
	{	
		Auth::handleLogin();
		$this->view->title_page = 'Validar Comprobantes';
		$this->view->js = array('contable/js/validador.js');
		$this->view->TipoDocumento = $this->model->TipoDocumento();
		$this->view->render('contable/validador/index', false);
	}  
	function validador_list()
    {
        $this->model->validador_list($_POST);
    }
}