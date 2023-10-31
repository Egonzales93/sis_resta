<?php 
    Session::init(); 
    //$ver = (Session::get('rol') == 1) OR Session::get('rol') == 2 ? '' :  header('location: ' . URL . 'err/danger'); 
    //$ver = (Session::get('bloqueo') == 0 OR Session::get('bloqueo') == null) ? '' :  header('location: ' . URL . 'err/bloqueo');
?>
<?php

class Login extends Controller {

	function __construct() {
		parent::__construct();	
		// Auth::handleLogin();
	}
	
	function index() 
	{	
		//echo Hash::create('sha256', 'jonathan', HASH_PASSWORD_KEY);
		if(Session::get('loggedIn') <> 1){
			Session::init();
			Session::set('loggedIn', false);
			$this->view->js = array('login/js/login.js');
			$this->view->render('login/index',false);
		}else{

			if(Session::get('rol') == 3 || Session::get('rol') == 5){ // cajero y mozo 
				Session::init();
				header('location: ' . URL . 'venta');
			}elseif(Session::get('rol') == 4){ // produccion
				Session::init();
				header('location: ' . URL . 'produccion');
			}else{ 
				Session::init();
				header('location: ' . URL . 'tablero');
			}
		}
	}

	
	function run()
	{
		$this->model->run($_POST);
	}
	
}