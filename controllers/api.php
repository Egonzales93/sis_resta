<?php 
Session::init(); 
$ver = (Session::get('rol') == 1 OR Session::get('rol') == 2 OR Session::get('rol') == 3) ? '' :  header('location: ' . URL . 'err/danger'); 
?>
<?php

class Api extends Controller {

	function __construct() {
		parent::__construct();
		Auth::handleLogin();
	}
    function ci($ci) 
	{	
        if(strlen($ci) == '8') : 
        print_r(json_encode($this->model->ci(API_TOKEN,$ci)));
        else :
            echo 'null';
        endif;
	}
    function nit($nit) 
	{	
        if(strlen($nit) == '11') : 
        print_r(json_encode($this->model->nit(API_TOKEN,$nit)));
        else :
            echo 'null';
        endif;
	}
    function liberarbloqueo(){
        // echo 'hola';
        print_r(json_encode($this->model->liberarbloqueo()));
    }
	
}