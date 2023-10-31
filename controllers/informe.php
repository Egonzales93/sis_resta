<?php 
	Session::init(); 
	$ver = (Session::get('rol') == 1 OR Session::get('rol') == 2 OR Session::get('rol') == 3) ? '' :  header('location: ' . URL . 'err/danger'); 
	$ver = (Session::get('bloqueo') == 0 OR Session::get('bloqueo') == null) ? '' :  header('location: ' . URL . 'err/bloqueo');
?>
<?php

class Informe extends Controller {

	function __construct() {
		parent::__construct();
	}
	
	function index() 
	{	
		Auth::handleLogin();
		$this->view->js = array('informe/js/inf.js');
		$this->view->render('informe/index', false);
	}

	/* INICIO MODULO VENTAS */

	function venta_all(){
		$this->view->TipoPedido = $this->model->TipoPedido();
		$this->view->Caja = $this->model->Caja();
		$this->view->Cliente = $this->model->Cliente();
		$this->view->TipoDocumento = $this->model->TipoDocumento();
		$this->view->js = array('informe/js/inf_ven_all.js');
		$this->view->render('informe/venta/venta', false);
	}

	function venta_all_list()
    {
        $this->model->venta_all_list($_POST);
    }

    function venta_all_det()
    {
        print_r(json_encode($this->model->venta_all_det($_POST)));
    }

    function venta_del(){
		$this->view->Repartidor = $this->model->Repartidor();
		$this->view->js = array('informe/js/inf_ven_delivery.js');
		$this->view->render('informe/venta/delivery', false);
	}

	function venta_delivery_list()
    {
        $this->model->venta_delivery_list($_POST);
    }

    function venta_culqi(){
		$this->view->js = array('informe/js/inf_ven_culqi.js');
		$this->view->render('informe/venta/culqi', false);
	}

	function listado_impresoras(){
		$json_imp = $this->model->obtenerImpresoras();
		echo $json_imp;
		//$_impresoras = json_decode($json_imp, true);
    }

	function venta_culqi_list()
    {
        $this->model->venta_culqi_list($_POST);
    }

    function venta_prod(){
    	$this->view->Categoria = $this->model->Categoria();
		$this->view->Producto = $this->model->Producto();
		$this->view->Presentacion = $this->model->Presentacion();
		$this->view->js = array('informe/js/inf_ven_prod.js');
		$this->view->render('informe/venta/producto', false);
	}

    function venta_prod_kardex(){
    	$this->view->Categoria = $this->model->Categoria();
		$this->view->Producto = $this->model->Producto();
		$this->view->Presentacion = $this->model->Presentacion();
		$this->view->js = array('informe/js/inf_ven_prod_kardex.js');
		$this->view->render('informe/venta/producto_kardex', false);
	}
	function venta_prod_kardex_list()
    {
        $this->model->venta_prod_kardex_list($_POST);
    }

    function venta_prod_kardex_graphic()
    {
        $this->model->venta_prod_kardex_graphic($_POST);
    }
	function venta_prod_margen(){
    	$this->view->Categoria = $this->model->Categoria();
		$this->view->Producto = $this->model->Producto();
		$this->view->Presentacion = $this->model->Presentacion();
		$this->view->js = array('informe/js/inf_ven_prod_margen.js');
		$this->view->render('informe/venta/producto_margen', false);
	}

	function venta_prod_margen_list()
    {
        $this->model->venta_prod_margen_list($_POST);
    }

	function venta_cort(){
		$this->view->js = array('informe/js/inf_ven_cort.js');
		$this->view->render('informe/venta/cortesia', false);
	}

	function venta_cort_list(){
		$this->model->venta_cort_list($_POST);
	}

	function venta_prod_list()
    {
        $this->model->venta_prod_list($_POST);
    }

    function combPro()
    {
        print_r(json_encode($this->model->combPro($_POST)));
    }

    function combPre()
    {
        print_r(json_encode($this->model->combPre($_POST)));
    }

    function venta_mozo(){
		$this->view->Mozo = $this->model->Mozo();
		$this->view->js = array('informe/js/inf_ven_mozo.js');
		$this->view->render('informe/venta/mozo', false);
	}

	function venta_mozo_list(){
		$this->model->venta_mozo_list($_POST);
	}

	function venta_fpago(){
		$this->view->TipoPago = $this->model->TipoPago();
		$this->view->js = array('informe/js/inf_ven_fpago.js');
		$this->view->render('informe/venta/fpago', false);
	}

	function venta_fpago_list(){
		$this->model->venta_fpago_list($_POST);
	}

	function venta_desc(){
		$this->view->js = array('informe/js/inf_ven_desc.js');
		$this->view->render('informe/venta/descuento', false);
	}

	function venta_desc_list(){
		$this->model->venta_desc_list($_POST);
	}

	function venta_all_imp_($id_venta)
    {	
		$ip_printer = '';
		$json_imp = $this->model->obtenerImpresoras();
		$_impresoras = json_decode($json_imp, true);

		foreach ($_impresoras as $row) {
			if($row['nombre']=='CAJA'){ $ip_printer = $row['ip']; }
		}

        $dato = $this->model->venta_all_imp($id_venta);
		
		//[MODIFICAMOS ACA]
        header('location: http://'.Session::get('pc_ip').'/imprimir/comprobante_venta.php?ip='.$ip_printer.'&data='.urlencode(json_encode($dato)));
    }


    function venta_all_imp($id)
    {
        $this->view->empresa = $this->model->Empresa();
        $this->view->dato = $this->model->venta_all_imp_($id);
        $this->view->render('informe/venta/imprimir/imp_venta_all', true);
    }

	function venta_all_imp_a4($id)
    {
        $this->view->empresa = $this->model->Empresa();
        $this->view->dato = $this->model->venta_all_imp_($id);
        $this->view->render('informe/venta/imprimir/imp_venta_all_a4', true);
    }

	/* FIN MODULO VENTAS */

	/* INICIO MODULO COMPRAS */

	function compra_all(){
		$this->view->Proveedor = $this->model->Proveedor();
		$this->view->js = array('informe/js/inf_com_all.js');
		$this->view->render('informe/compra/compra', false);
	}

	function compra_all_list(){
		$this->model->compra_all_list($_POST);
	}

	function compra_all_det()
    {
        print_r(json_encode($this->model->compra_all_det($_POST)));
    }

    function compra_all_det_cuota()
    {
        print_r(json_encode($this->model->compra_all_det_cuota($_POST)));
    }
    
    function compra_all_det_subcuota()
    {
        print_r(json_encode($this->model->compra_all_det_subcuota($_POST)));
    }

	/* FIN MODULO COMPRAS */

	/* INICIO MODULO FINANZAS */

	function finanza_arq(){
		$this->view->Cajero = $this->model->Cajero();
		$this->view->js = array('informe/js/inf_fin_arqueo.js');
		$this->view->render('informe/finanza/arqueo', false);
	}

	function finanza_arq_list(){
		$this->model->finanza_arq_list($_POST);
	}

	function finanza_arq_resumen($id){
        $this->view->apc = $id;
        $this->view->js = array('informe/js/inf_fin_arqueo_resumen.js');
        $this->view->render('informe/finanza/arqueo/detalle', false);
    }

	function finanza_arq_resumen_default()
    {
        print_r(json_encode($this->model->finanza_arq_resumen_default($_POST)));
    }

    function finanza_arq_resumen_venta_list(){
        print_r(json_encode($this->model->finanza_arq_resumen_venta_list($_POST)));
    }

    function finanza_arq_resumen_venta_delivery_list(){
        print_r(json_encode($this->model->finanza_arq_resumen_venta_delivery_list($_POST)));
    }

    function finanza_arq_resumen_caja_list_i(){
        print_r(json_encode($this->model->finanza_arq_resumen_caja_list_i($_POST)));
    }

    function finanza_arq_resumen_caja_list_e(){
        print_r(json_encode($this->model->finanza_arq_resumen_caja_list_e($_POST)));
    }

    function finanza_arq_resumen_productos(){
        print_r(json_encode($this->model->finanza_arq_resumen_productos($_POST)));
    }

    function finanza_arq_resumen_anulaciones(){
        print_r(json_encode($this->model->finanza_arq_resumen_anulaciones($_POST)));
    }

    function finanza_ing(){
    	$this->view->Cajero = $this->model->Cajero();
		$this->view->js = array('informe/js/inf_fin_ingreso.js');
		$this->view->render('informe/finanza/ingreso', false);
	}

	function finanza_ing_list(){
		$this->model->finanza_ing_list($_POST);
	}

	function finanza_egr(){
		$this->view->Cajero = $this->model->Cajero();
		$this->view->js = array('informe/js/inf_fin_egreso.js');
		$this->view->render('informe/finanza/egreso', false);
	}

	function finanza_egr_list(){
		$this->model->finanza_egr_list($_POST);
	}

	function finanza_rem(){
		$this->view->Personal = $this->model->Personal();
		$this->view->js = array('informe/js/inf_fin_remun.js');
		$this->view->render('informe/finanza/remuneracion', false);
	}

	function finanza_rem_list(){
		$this->model->finanza_rem_list($_POST);
	}

	function finanza_arq_imp($id)
    {
        $this->view->empresa = $this->model->Empresa();
        $this->view->dato = $this->model->finanza_arq_imp($id);
        $this->view->render('informe/finanza/imprimir/imp_cierre', true);
    }

    function finanza_adel(){
    	$this->view->Personal = $this->model->Personal();
		$this->view->js = array('informe/js/inf_fin_adelanto.js');
		$this->view->render('informe/finanza/adelanto', false);
	}

	function finanza_adel_list_a(){
		$this->model->finanza_adel_list_a($_POST);
	}

	function finanza_adel_list_b(){
		$this->model->finanza_adel_list_b($_POST);
	}

	/* FIN MODULO FINANZAS */

	/* INICIO MODULO INVENTARIO */
	function inventario_kardex() 
	{
		$this->view->js = array('informe/js/inf_inv_kardex.js');
		$this->view->render('informe/inventario/kardex', false);
	}

	// function inventario_kardex_list()
    // {
    //     $this->model->inventario_kardex_list($_POST);
    // }

    // function inventario_ComboInsumoProducto()
    // {
    //     $this->model->inventario_ComboInsumoProducto($_POST);
    // }
	/* MODULO KARDEX */
	// function kardex() 
	// {
	// 	$this->view->js = array('inventario/js/func_kardexv.js');
	// 	$this->view->render('inventario/kardex', false);
	// }

	function kardex_list()
    {
        $this->model->kardex_list($_POST);
    }

    function ComboInsumoProducto()
    {
        $this->model->ComboInsumoProducto($_POST);
    }






    /* FIN MODULO INVENTARIO */
	/* INICIO MODULO OPERACIONES */

	function oper_anul(){
		$this->view->Cajero = $this->model->Cajero();
		$this->view->js = array('informe/js/inf_ope_anul_pedido.js');
		$this->view->render('informe/operacion/anulacion_pedido', false);
	}

	function oper_anul_list()
    {
        $this->model->oper_anul_list($_POST);
    }

	/* FIN MODULO OPERACIONES */
       
}