<?php

class Login_Model extends Model
{
	public function __construct()
	{
		parent::__construct();
	}

	public function run()
	{
		$du = $this->db->prepare("SELECT * FROM tm_usuario WHERE 
				usuario = :usuario AND contrasena = :password AND estado = 'a'");
		$du->execute(array(
			':usuario' => $_POST['usuario'],
			':password' => base64_encode($_POST['password'])
		));
		
		$data_u = $du->fetch();
		
		$count_u =  $du->rowCount();
		if ($count_u > 0) {
			//datos de usuario
			Session::init();
			Session::set('loggedIn', true);
			Session::set('rol', $data_u['id_rol']);
			Session::set('usuid', $data_u['id_usu']);
			Session::set('areaid', $data_u['id_areap']);
			Session::set('nombres', $data_u['nombres']);
			Session::set('apellidos', $data_u['ape_paterno'].' '.$data_u['ape_materno']);
			Session::set('imagen', $data_u['imagen']);

			//datos de empresa
			$de = $this->db->prepare("SELECT * FROM tm_empresa");
			$de->execute();
			$data_e = $de->fetch();
			Session::set('nit', $data_e['nit']);
			//Session::set('raz_soc', $data_e['razon_social']);
			//Session::set('impuestos', $data_e['impuestos']);
			Session::set('modo', $data_e['modo']);

			//datos de sistema
			$ds = $this->db->prepare("SELECT * FROM tm_configuracion");
			$ds->execute();
			$data_s = $ds->fetch();
			Session::set('zona_hor', $data_s['zona_hora']);
			Session::set('moneda', $data_s['mon_val']);
			Session::set('iva', ($data_s['imp_val']));//  / 100
			Session::set('tribAcr', $data_s['trib_acr']);
			Session::set('tribCar', $data_s['trib_car']);
			Session::set('diAcr', $data_s['di_acr']);
			Session::set('diCar', $data_s['di_car']);
			Session::set('impAcr', $data_s['imp_acr']);
			Session::set('monAcr', $data_s['mon_acr']);
			Session::set('pc_name', $data_s['pc_name']);
			Session::set('pc_ip', $data_s['pc_ip']);
			Session::set('print_com', $data_s['print_com']);
			Session::set('print_pre', $data_s['print_pre']);
			Session::set('print_cpe', $data_s['print_cpe']); // funcion imprimir cpe 
			Session::set('cod_seg', $data_s['cod_seg']); //funcion codigo de seguridad 
			Session::set('opc_01', $data_s['opc_01']);
			Session::set('opc_02', $data_s['opc_02']);
			Session::set('opc_03', $data_s['opc_03']);

			/* modulo de bloqueo  */
			if($data_u['id_rol'] == 1){
				Session::set('bloqueo', '0');
				Session::set('bloqueo_id', $data_s['bloqueo']);
			}else{
				Session::set('bloqueo', $data_s['bloqueo']); 
				Session::set('bloqueo_id', $data_s['bloqueo']); 
			}

			// si cumple apertura
			if($data_u['id_rol'] == 1 OR $data_u['id_rol'] == 2 OR $data_u['id_rol'] == 3){
				$da = $this->db->prepare("SELECT * FROM tm_aper_cierre WHERE id_usu = ? AND estado = 'a'");
				$da->execute(array($data_u['id_usu']));
				$data_a = $da->fetch();
				$count_a =  $da->rowCount();
				if ($count_a > 0) {
					Session::set('aperturaIn', true);
					Session::set('apcid', $data_a['id_apc']);
				} else {
					Session::set('aperturaIn', false);
				}

				//Condicionar a que el administrador no visualice el tablero
				if($data_u['id_rol'] == 3){
					print_r(json_encode(3));
				} else {
					print_r(json_encode(1));
				}
				
			} elseif($data_u['id_rol'] == 4){
				Session::set('aperturaIn', true);
				print_r(json_encode(2));
			} elseif($data_u['id_rol'] == 5){
				Session::set('aperturaIn', true);
				print_r(json_encode(3));
			}			
		} else {
			//header('location: ../login');
			print_r(json_encode(4));
		}
		
	}
	
}