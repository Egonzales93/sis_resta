<?php Session::init(); ?>
<?php

class Contable_Model extends Model
{
    public function __construct()
    {
        parent::__construct();
    }
    public function TipoDocumento()
    {
        try
        {   
            return $this->db->selectAll('SELECT * FROM tm_tipo_doc WHERE id_tipo_doc != "3" AND estado = "a"');
        }
        catch(Exception $e)
        {
            die($e->getMessage());
        }
    }
    public function Empresa()
    {
        try
        {      
            return $this->db->selectOne("SELECT * FROM tm_empresa");
        }
        catch(Exception $e)
        {
            die($e->getMessage());
        }
    }
    public function venta_all_list($data)
    {
        try
        {
            $ifecha = date('Y-m-d',strtotime($data['start']));
            $ffecha = date('Y-m-d',strtotime($data['end']));
            $stm = $this->db->prepare("SELECT v.id_ven,v.id_ped,v.id_tped,v.id_tpag,v.pago_efe,v.pago_tar,v.desc_monto,v.comis_tar,v.comis_del,v.total AS stotal,v.fec_ven,v.desc_td,v.ser_doc,v.nro_doc,v.estado,IFNULL((v.pago_efe + v.pago_tar),0) AS total,v.id_cli,v.iva,v.id_usu,v.desc_tipo,v.desc_personal,c.desc_caja FROM v_ventas_con AS v INNER JOIN v_caja_aper AS c ON v.id_apc = c.id_apc WHERE (v.fec_ven >= ? AND v.fec_ven <= ?) AND v.id_tped like '%' AND v.id_tdoc <> '3' AND v.id_cli like '%' AND v.estado like '%' GROUP BY v.id_ven");

            $stm->execute(array($ifecha,$ffecha));
            $c = $stm->fetchAll(PDO::FETCH_OBJ);
                       
            foreach($c as $k => $d)
            {
                $c[$k]->{'Pedido'} = $this->db->query("SELECT vm.desc_salon, vm.nro_mesa FROM tm_pedido_mesa AS pm INNER JOIN v_mesas AS vm ON pm.id_mesa = vm.id_mesa WHERE pm.id_pedido = ".$d->id_ped)
                    ->fetch(PDO::FETCH_OBJ);

                $c[$k]->{'Detalle'} = $this->db->query("SELECT v_productos.pro_cod AS codigo_producto, 
                    CONCAT(v_productos.pro_nom,' ',v_productos.pro_pre) AS nombre_producto, 
                    IF(v_productos.pro_imp='1','10','20') AS codigo_afectacion, 
                    CAST(tm_detalle_venta.cantidad AS DECIMAL(7,2)) AS cantidad, 
                    IF(v_productos.pro_imp='1',ROUND((tm_detalle_venta.precio/(1 + 0.18)),2),tm_detalle_venta.precio) AS valor_unitario,
                    tm_detalle_venta.precio AS precio_unitario,
                    IF(v_productos.pro_imp='1',ROUND((tm_detalle_venta.precio/(1 + 0.18))*tm_detalle_venta.cantidad,2),
                    ROUND(tm_detalle_venta.precio*tm_detalle_venta.cantidad,2)) AS valor_venta,
                    IF(v_productos.pro_imp='1',ROUND((tm_detalle_venta.precio/(1 + 0.18)*tm_detalle_venta.cantidad)*0.18,2),0) AS total_iva 
                    FROM tm_detalle_venta 
                    INNER JOIN tm_venta ON tm_detalle_venta.id_venta = tm_venta.id_venta 
                    INNER JOIN v_productos ON tm_detalle_venta.id_prod = v_productos.id_pres 
                    WHERE tm_venta.id_tipo_doc  IN ('1','2','3') AND tm_detalle_venta.precio > 0 AND tm_detalle_venta.id_venta = ".$d->id_ven)
                    ->fetchAll(PDO::FETCH_OBJ);

            }
            
            foreach($c as $k => $d)
            {
                $c[$k]->{'Cliente'} = $this->db->query("SELECT * FROM v_clientes WHERE id_cliente = ".$d->id_cli)
                    ->fetch(PDO::FETCH_OBJ);
            }

            return $c;      
        }
        catch(Exception $e)
        {
            die($e->getMessage());
        }
    }

    public function validador_list($data)
    {
        try
        {
            $empresa = $this->Empresa();
            if($data['tdoc'] == '%'):
                $estado = "v.id_tdoc <> 3";
            else: 
                $estado = "v.id_tdoc = ".$data['tdoc'];
            endif;
            $ifecha = date('Y-m-d',strtotime($data['ifecha']));
            $ffecha = date('Y-m-d',strtotime($data['ffecha']));
            $stm = $this->db->prepare("SELECT v.*,IFNULL((v.total+v.comis_del-v.desc_monto),0) AS total FROM v_ventas_con AS v INNER JOIN v_caja_aper AS c ON v.id_apc = c.id_apc WHERE (DATE_FORMAT(v.fec_ven,'%Y-%m-%d') >= ? AND DATE_FORMAT(v.fec_ven,'%Y-%m-%d') <= ?) AND v.id_tdoc like ? AND ".$estado." GROUP BY v.id_ven");
            $stm->execute(array($ifecha,$ffecha,$_POST['tdoc']));
            $c = $stm->fetchAll(PDO::FETCH_OBJ);           
            foreach($c as $k => $d)
            {
                $c[$k]->{'Cliente'} = $this->db->query("SELECT ci,nit,nombre FROM v_clientes WHERE id_cliente = ".$d->id_cli)
                    ->fetch(PDO::FETCH_OBJ);
            }
            foreach($c as $k => $d)
            {
                $codComp = array('BOLETA DE VENTA'=> "03", "FACTURA" => '01');
                $form_params = [
                    'numNit' => $empresa['nit'],
                    'codComp' => $codComp[$d->desc_td],
                    'numeroSerie' => $d->ser_doc,
                    'numero' => $d->nro_doc,
                    'fechaEmision' => date('d/m/Y',strtotime($d->fec_ven)),
                    'monto' => $d->total,
                ];
                $c[$k]->{'Estado_Inpuestos'} = $this->search($form_params);
            }
            $data = array("data" => $c);
            $json = json_encode($data);
            echo $json;       
        }
        catch(Exception $e)
        {
            die($e->getMessage());
        }
    }
    /* funcion consulta key para el validador   */
    public function api_validador()
    {
        $empresa = $this->Empresa();
		//$GRANT_TYPE = 'client_credentials';
		//$SCOPE = 'https://api.sunat.gob.pe/v1/contribuyente/contribuyentes';

		$curl = curl_init();
            
		$form_params = [
			//'grant_type' => $GRANT_TYPE,
			//'scope' => $SCOPE,
			'client_id' => $empresa['client_id'] ,
			'client_secret' => $empresa['client_secret'], 
		];

		curl_setopt_array($curl, array(
			//CURLOPT_URL => "https://api-seguridad.sunat.gob.pe/v1/clientesextranet/".$empresa['client_id']."/oauth2/token",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'POST',
			CURLOPT_POSTFIELDS => http_build_query($form_params),
			CURLOPT_HTTPHEADER => array(
				'Content-Type: application/x-www-form-urlencoded',
			),
		));

		$response = curl_exec($curl);

		curl_close($curl);
		$data = json_decode($response, true);

		if(array_key_exists('access_token', $data)){
			
			return [
				'success' => true,
				'data' => [
					'access_token' => $data['access_token'],
					'token_type' => $data['token_type'],
					'expires_in' => $data['expires_in'],
				],
			];
		}

		$error_description = $data['error_description'] ?? '';
		$error = $data['error'] ?? '';
		
		return [
			'success' => false,
			'message' => 'Error al obtener token - error_description: '.$error_description.' error: '.$error
		];
    }
    /*  VALIDARA POR COMPROBANTE INDIVIDUAL */
    public function validarComprobanteIndividual($comprobantes){
        foreach ($comprobantes as $value) {
            if(!isset($value->fechaEmision) || !isset($value->tipoComp)) 
            return ['validado'=>false,'mensaje'=>"Falta la fecha de emisión o el tipo del comprobante"];
            //Validamos que los campos obligatorios esten llenos
            if((!isset($value->numero) && empty($value->numero))|| (!isset($value->importeTotalOperaciones)&&empty($value->importeTotalOperaciones)));
            
           
        }
    }
    
    public function search($parametros)
    {

        try {
            //$BASE_URL = 'https://api.sunat.gob.pe/v1/contribuyente/contribuyentes';
            $empresa = $this->Empresa();
            //$token   = $this->api_validador();

            $form_params = [
                'numNit' => $parametros['numNit'],
                'codComp' => $parametros['codComp'],
                'numeroSerie' => $parametros['numeroSerie'],
                'numero' => $parametros['numero'],
                'fechaEmision' => $parametros['fechaEmision'],
                'monto' => $parametros['monto'],
            ];

            
            $curl = curl_init();
            
            curl_setopt_array($curl, array(
                //CURLOPT_URL => $BASE_URL."/".$empresa['nit']."/validarcomprobante",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($form_params),
                CURLOPT_HTTPHEADER => array(
                    "Authorization: Bearer ".$token['data']['access_token'],
                    'Content-Type: application/json'
                ),
            ));
            
            $response = curl_exec($curl);
            
            curl_close($curl);

            $res = json_decode($response, true);

            if($res['success']){
                return $res['data']['estadoCp'] ?? null;
            }

            return $res;

        } catch (Exception $e) {

            die($e->getMessage());

        }

    } 

}