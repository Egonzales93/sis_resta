<?php 
    Session::init(); 
    $ver = (Session::get('rol') == 1) OR Session::get('rol') == 2 ? '' :  header('location: ' . URL . 'err/danger'); 
    $ver = (Session::get('bloqueo') == 0 OR Session::get('bloqueo') == null) ? '' :  header('location: ' . URL . 'err/bloqueo');
?>
<?php 

class Ajuste extends Controller {

	function __construct() {
		parent::__construct();
	}
	
	function index() 
	{	
        $this->view->title_page = 'Ajustes';
		$this->view->render('ajuste/index', false);
	}

	/* INICIO MODULO EMPRESA */

	function datosempresa(){
        $this->view->title_page = 'Datos de la Empresa';
		$this->view->js = array('ajuste/js/aju_emp_datos.js','');
		$this->view->render('ajuste/all/empresa', false);
	}

	function datosempresa_data()
    {
        print_r(json_encode($this->model->datosempresa_data()));
    }

    function datosempresa_crud()
    {
        print_r(json_encode($this->model->datosempresa_crud($_POST)));
    }

    function tipodoc(){
        $this->view->title_page = 'Tipo de documentos';
		$this->view->js = array('ajuste/js/aju_emp_tdoc.js');
		$this->view->render('ajuste/all/tipodoc', false);
	}

	function tipodoc_list(){
        $this->model->tipodoc_list();
    }

    function tipodoc_crud(){
        print_r(json_encode($this->model->tipodoc_crud($_POST)));
    }

    function tipopago(){
        $this->view->title_page = 'Tipos de pago';
        $this->view->TipoPago = $this->model->TipoPago();
        $this->view->js = array('ajuste/js/aju_emp_tpago.js');
        $this->view->render('ajuste/all/tipopago', false);
    }

    function tipopago_list()
    {
        $this->model->tipopago_list($_POST);
    }

    function tipopago_crud()
    {
        if($_POST['id_pago'] != ''){
           print_r(json_encode( $this->model->tipopago_crud_update($_POST)));
        } else{
           print_r(json_encode( $this->model->tipopago_crud_create($_POST)));
        }
    }


    function usuario(){
        $this->view->title_page = 'Usuarios';
        $this->view->js = array('ajuste/js/aju_emp_usu.js');
        $this->view->render('ajuste/all/usu_all', false);
    }

    function usuario_list()
    {
        $this->model->usuario_list($_POST);
    }

    function usuario_nuevo(){
        $this->view->title_page = 'Nuevo usuario';
        $this->view->Rol = $this->model->Rol();
        $this->view->AreaProduccion = $this->model->AreaProduccion();
        $this->view->js = array('ajuste/js/wizard/jquery.bootstrap.wizard.js','ajuste/js/wizard/wizard.js','ajuste/js/aju_emp_usu_edit.js');
        $this->view->render('ajuste/all/usu_edit', false);
    }

    function usuario_edit($id){
        $this->view->title_page = 'Editar usuario';
        $this->view->Rol = $this->model->Rol();
        $this->view->AreaProduccion = $this->model->AreaProduccion();
        $this->view->js = array('ajuste/js/wizard/jquery.bootstrap.wizard.js','ajuste/js/wizard/wizard.js','ajuste/js/aju_emp_usu_edit.js');
        $this->view->usuario = $this->model->usuario_data($id);
        $this->view->render('ajuste/all/usu_edit', false);
    }

    function usuario_crud(){   
        if($_POST['id_usu'] != ''){
           $this->model->usuario_crud_update($_POST);
           print_r(json_encode(2));
        } else{
           $row=$this->model->usuario_crud_create($_POST);
           if ($row['cod'] == 1){
                print_r(json_encode(0));
            } else {
                print_r(json_encode(1));
            }
        }
    }

    function usuario_estado(){
        print_r(json_encode($this->model->usuario_estado($_POST)));
    }

    function usuario_delete(){
        print_r(json_encode($this->model->usuario_delete($_POST)));
    }

	/* FIN MODULO EMPRESA */


	/* INICIO MODULO RESTAURANTE */

	function caja(){
        $this->view->title_page = 'Cajas';
		$this->view->js = array('ajuste/js/aju_res_caja.js');
		$this->view->render('ajuste/all/caja', false);
	}

	function caja_list(){
        $this->model->caja_list();
    }

    function caja_crud(){
        if($_POST['id_caja'] != ''){
           print_r(json_encode($this->model->caja_crud_update($_POST)));
        } else{
           print_r(json_encode($this->model->caja_crud_create($_POST)));
        }
    }

    function areaprod(){
        $this->view->title_page = 'Áreas de producción';
    	$this->view->Impresora = $this->model->Impresora();
		$this->view->js = array('ajuste/js/aju_res_areaprod.js');
		$this->view->render('ajuste/all/areaprod', false);
	}

    function areaprod_list()
    {
        $this->model->areaprod_list($_POST);
    }

    function areaprod_crud()
    {
        if($_POST['id_areap'] != ''){
           print_r(json_encode( $this->model->areaprod_crud_update($_POST)));
        } else{
           print_r(json_encode( $this->model->areaprod_crud_create($_POST)));
        }
    }

    function salonmesa(){
        $this->view->title_page = 'Salones y mesas';
		$this->view->js = array('ajuste/js/aju_res_salmes.js');
		$this->view->render('ajuste/all/salonymesa', false);
	}

    function salon_list()
    {
        $this->model->salon_list($_POST);
    }

    function salon_crud()
    {
        if($_POST['id_salon'] != ''){
           print_r(json_encode( $this->model->salon_crud_update($_POST)));
        } else{
           print_r(json_encode( $this->model->salon_crud_create($_POST)));
        }
    }

    function salon_crud_delete()
    {
        if($_POST['id_salon'] != ''){
           print_r(json_encode( $this->model->salon_crud_delete($_POST)));
        } 
    }

    function mesa_list()
    {
        $this->model->mesa_list($_POST);
    }

    function mesa_crud()
    {
        if($_POST['id_mesa'] != '' and $_POST['id_salon'] != ''){
           print_r(json_encode( $this->model->mesa_crud_update($_POST)));
        } else{
           print_r(json_encode( $this->model->mesa_crud_create($_POST)));
        }
    }

    function mesa_crud_delete()
    {
        if($_POST['id_mesa'] != ''){
           print_r(json_encode( $this->model->mesa_crud_delete($_POST)));
        } 
    }

    /* ======================= INICIO PRODUCTO */

    function producto(){
        $this->view->title_page = 'Productos';
    	$this->view->AreaProduccion = $this->model->AreaProduccion();
		$this->view->js = array('ajuste/js/wizard/jquery.bootstrap.wizard.js','ajuste/js/wizard/wizard.js','ajuste/js/wizard/jquery-ui.min.js','ajuste/js/aju_res_prod.js','ajuste/js/aju_res_prod_ins.js');
		$this->view->render('ajuste/all/producto', false);
	}

	function producto_cat_list()
    {
        $this->model->producto_cat_list($_POST);
    }

	function producto_list()
    {
        $this->model->producto_list($_POST);
    }

    function producto_pres_list()
    {
        $this->model->producto_pres_list($_POST);
    }

    function producto_pres_ing()
    {
        print_r(json_encode($this->model->producto_pres_ing($_POST)));
    }

    function producto_combo_cat()
    {
        print_r(json_encode($this->model->producto_combo_cat()));
    }

    function producto_combo_unimed()
    {
        $this->model->producto_combo_unimed($_POST);
    }

    function producto_buscar_ins()
    {
        print_r(json_encode($this->model->producto_buscar_ins($_POST)));
    }

    function producto_ingrediente_create()
    {
        print_r(json_encode( $this->model->producto_ingrediente_create($_POST)));
    }

    function producto_ingrediente_update()
    {
        print_r(json_encode($this->model->producto_ingrediente_update($_POST)));
    }

    function producto_ingrediente_delete()
    {
        print_r(json_encode($this->model->producto_ingrediente_delete($_POST)));
    }

    function producto_crud()
    {
        if($_POST['id_prod'] != ''){
           print_r(json_encode($this->model->producto_crud_update($_POST)));
        } else{
           print_r(json_encode($this->model->producto_crud_create($_POST)));
        }
    }

    function producto_pres_crud()
    {    
        if($_POST['id_pres_presentacion'] != ''){
           print_r(json_encode($this->model->producto_pres_crud_update($_POST)));
        } else{
           print_r(json_encode($this->model->producto_pres_crud_create($_POST)));
        }
    }

    function producto_cat_crud()
    {
        if($_POST['id_catg_categoria'] != ''){
           print_r(json_encode($this->model->producto_cat_crud_update($_POST)));
        } else{
           print_r(json_encode($this->model->producto_cat_crud_create($_POST)));
        }
    }

    function producto_cat_delete()
    {
        print_r(json_encode($this->model->producto_cat_delete($_POST)));
    }
    function producto_prod_delete()
    {
        print_r(json_encode($this->model->producto_prod_delete($_POST)));
    }
    function producto_pres_delete()
    {
        print_r(json_encode($this->model->producto_pres_delete($_POST)));
    }

	/* ======================== FIN PRODUCTO */

    /* ======================== INICIO COMBOS */
    function combo(){
        $this->view->title_page = 'Combos';
        $this->view->AreaProduccion = $this->model->AreaProduccion();
        $this->view->js = array('ajuste/js/wizard/jquery.bootstrap.wizard.js','ajuste/js/wizard/wizard.js','ajuste/js/wizard/jquery-ui.min.js','ajuste/js/aju_res_comb.js','ajuste/js/aju_res_prod_ins.js');
        $this->view->render('ajuste/all/combo', false);
    }

    function combo_list()
    {
        $this->model->combo_list($_POST);
    }

    /* ======================== FIN COMBOS */

	/* ======================== INICIO INSUMO */

	function insumo(){
        $this->view->title_page = 'Insumos';
    	$this->view->UnidadMedida = $this->model->UnidadMedida();
		$this->view->js = array('ajuste/js/aju_res_ins.js');
		$this->view->render('ajuste/all/insumo', false);
	}

	function insumo_cat_list()
    {
        $this->model->insumo_cat_list($_POST);
    }

    function insumo_list()
    {
        $this->model->insumo_list($_POST);
    }

    function insumo_combo_cat()
    {
        print_r(json_encode($this->model->insumo_combo_cat()));
    }

   function insumo_cat_crud()
    {
        if($_POST['id_catg'] != ''){
           print_r(json_encode( $this->model->insumo_cat_crud_update($_POST)));
        } else{
           print_r(json_encode( $this->model->insumo_cat_crud_create($_POST)));
        }
    }

    function insumo_crud()
    {
        if($_POST['id_ins'] != ''){
           print_r(json_encode( $this->model->insumo_crud_update($_POST)));
        } else{
           print_r(json_encode( $this->model->insumo_crud_create($_POST)));
        }
    }

    function insumo_cat_delete()
    {
        print_r(json_encode($this->model->insumo_cat_delete($_POST)));
    }

    function printer(){
        $this->view->title_page = 'Impresoras';
        $this->view->js = array('ajuste/js/aju_res_print.js');
        $this->view->render('ajuste/all/print', false);
    }

    function print_list()
    {
        $this->model->print_list($_POST);
    }

    function print_crud()
    {
        if($_POST['id_imp'] != ''){
           print_r(json_encode( $this->model->print_crud_update($_POST)));
        } else{
           print_r(json_encode( $this->model->print_crud_create($_POST)));
        }
    }
    /* ======================== FIN INSUMO */

    /* FIN MODULO RESTAURANTE */

    /* INICIO MODULO SISTEMA */

    /* ======================== INICIO OPTIMIZACION */
    function optimizar(){
        $this->view->title_page = 'Resetear';
        $this->view->js = array('ajuste/js/aju_opt_pedidos.js');
        $this->view->render('ajuste/all/optimizar', false);
    }

    function optimizar_pedidos()
    {
        print_r(json_encode($this->model->optimizar_pedidos($_POST)));
    }

    function optimizar_ventas()
    {
        print_r(json_encode($this->model->optimizar_ventas($_POST)));
    }

    function optimizar_productos()
    {
        print_r(json_encode($this->model->optimizar_productos($_POST)));
    }

    function optimizar_insumos()
    {
        print_r(json_encode($this->model->optimizar_insumos($_POST)));
    }

    function optimizar_clientes()
    {
        print_r(json_encode($this->model->optimizar_clientes($_POST)));
    }

    function optimizar_proveedores()
    {
        print_r(json_encode($this->model->optimizar_proveedores($_POST)));
    }

    function optimizar_mesas()
    {
        print_r(json_encode($this->model->optimizar_mesas($_POST)));
    }

    /* ======================== FIN OPTIMIZACION */


    /* ======================== INCIO OTROS AJUSTES */

    function sistema(){
        $this->view->title_page = 'Configuración inicial';
        $this->view->js = array('ajuste/js/aju_opt_sis.js');
        $this->view->render('ajuste/all/sistema', false);
    }

    function datosistema_data()
    {
        print_r(json_encode($this->model->datosistema_data()));
    }

    function datosistema_crud()
    {
        print_r(json_encode($this->model->datosistema_crud($_POST)));
    }

    function anularlogo()
    {
        print_r(json_encode($this->model->anularlogo()));
        // print_r(json_encode('1'));
    }
    function bloqueo()
    {
        if($_POST){
            print_r(json_encode($this->model->bloqueoplataforma($_POST)));
        }
        // print_r(json_encode('1'));
    }
    /* ======================== FIN OTROS AJUSTES */

    /* FIN MODULO SISTEMA */
    // importar excel 
    function importarexcel(){
        print $this->model->importarexcel();
    }
    function importarexcelinsumos(){
        print $this->model->importarexcelinsumos();
    }
}