<?php 
    Session::init(); 
    $ver = (Session::get('rol') == 1 OR Session::get('rol') == 2 OR Session::get('rol') == 3) ? '' :  header('location: ' . URL . 'err/danger'); 
    $ver = (Session::get('bloqueo') == 0 OR Session::get('bloqueo') == null) ? '' :  header('location: ' . URL . 'err/bloqueo');
?>
<?php

class Credito extends Controller {

	function __construct() {
		parent::__construct();
	}
	
	function index() 
	{	
		Auth::handleLogin();
        $this->view->title_page = 'Creditos';
		$this->view->Proveedores = $this->model->Proveedores();
		$this->view->js = array('credito/js/func_credito.js');
		$this->view->render('credito/index', false);
	}

	function credito_compra_list()
    {
        $this->model->credito_compra_list($_POST);
    }

    function credito_compra_det()
    {
        print_r(json_encode($this->model->credito_compra_det($_POST)));
    }

    function credito_compra_cuota_list()
    {
        $this->model->credito_compra_cuota_list($_POST);
    }

    function credito_compra_cuota_pago()
    {
        print_r(json_encode($this->model->credito_compra_cuota_pago($_POST)));
    }
}