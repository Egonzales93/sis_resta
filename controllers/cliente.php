<?php 
    Session::init(); 
    $ver = (Session::get('rol') == 1 OR Session::get('rol') == 2 OR Session::get('rol') == 3) ? '' :  header('location: ' . URL . 'err/danger'); 
    $ver = (Session::get('bloqueo') == 0 OR Session::get('bloqueo') == null) ? '' :  header('location: ' . URL . 'err/bloqueo');
?>
<?php

class Cliente extends Controller {

	function __construct() {
		parent::__construct();
	}
	
	function index() 
	{	
		Auth::handleLogin();
        $this->view->title_page = 'Clientes';
		$this->view->js = array('cliente/js/func_cliente.js');
		$this->view->render('cliente/index');
	}

	function cliente_list()
    {
        $this->model->cliente_list($_POST);
    }

    function cliente_datos(){
        $this->model->cliente_datos($_POST);
    }

    function cliente_crud(){
        if($_POST['id_cliente'] != ''){
            $this->model->cliente_crud_update($_POST);
            print_r(json_encode(2));
        } else {
           $row = $this->model->cliente_crud_create($_POST);
            if ($row['cod'] == 1){
                print_r(json_encode(0));
            } else {
                print_r(json_encode(1));
            }
        }
    }

    function cliente_ventas(){
        $this->model->cliente_ventas($_POST);
    }

    function cliente_estado(){
        print_r(json_encode($this->model->cliente_estado($_POST)));
    }

    function cliente_delete(){
        print_r(json_encode($this->model->cliente_delete($_POST)));
    }
	
}