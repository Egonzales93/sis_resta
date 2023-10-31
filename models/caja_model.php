<?php Session::init(); ?>
<?php

class Caja_Model extends Model
{
	public function __construct()
	{
		parent::__construct();
	}

    public function Cajero()
    {
        try
        {      
            return $this->db->selectAll("SELECT id_usu,CONCAT(ape_paterno,' ',ape_materno,' ',nombres) AS nombres FROM tm_usuario WHERE (id_rol = 1 OR id_rol = 2 OR id_rol = 3) AND id_usu <> 1 AND estado = 'a'");
        }
        catch(Exception $e)
        {
            die($e->getMessage());
        }
    }

    public function Caja()
    {
        try
        {      
            return $this->db->selectAll("SELECT * FROM tm_caja WHERE estado = 'a'");
        }
        catch(Exception $e)
        {
            die($e->getMessage());
        }
    }

    public function Turno()
    {
        try
        {      
            return $this->db->selectAll("SELECT * FROM tm_turno");
        }
        catch(Exception $e)
        {
            die($e->getMessage());
        }
    }

    public function Personal()
    {
        try
        {      
            return $this->db->selectAll("SELECT * FROM tm_usuario WHERE id_usu <> 1 AND estado = 'a' GROUP BY ci");
        }
        catch(Exception $e)
        {
            die($e->getMessage());
        }
    }

    public function TipoPago()
    {
        try
        {   
            return $this->db->selectAll('SELECT * FROM tm_tipo_pago WHERE estado = "a"');
        }
        catch(Exception $e)
        {
            die($e->getMessage());
        }
    }
    public function TipoPedido()
    {
        try
        {      
            return $this->db->selectAll('SELECT * FROM tm_tipo_pedido');
        }
        catch(Exception $e)
        {
            die($e->getMessage());
        }
    }
    public function TipoDocumento()
    {
        try
        {   
            return $this->db->selectAll('SELECT * FROM tm_tipo_doc WHERE estado = "a"');
        }
        catch(Exception $e)
        {
            die($e->getMessage());
        }
    }

    /* INICIO MODULO APERTURA Y CIERRE */
    public function apercie_list()
    {
        try
        {
            $stm = $this->db->prepare("SELECT * FROM v_caja_aper WHERE id_usu = ? AND estado = 'a'");
            $stm->execute(array(Session::get('usuid')));
            $c = $stm->fetch(PDO::FETCH_OBJ);
            return $c;
        }
        catch(Exception $e)
        {
            die($e->getMessage());
        }
    }

    public function apercie_montosist($data)
    {
        try
        {
            $stm = $this->db->prepare("SELECT IFNULL(SUM(pago_efe),0) AS pago_efe, IFNULL(SUM(pago_tar),0) AS pago_tar, IFNULL(SUM(desc_monto),0) AS descu, IFNULL(SUM(total-desc_monto),0) AS total FROM v_ventas_con WHERE id_apc = ? AND estado <> 'i'");
            $stm->execute(array($data['id_apc']));
            $c = $stm->fetch(PDO::FETCH_OBJ);
            $c->{'Apertura'} = $this->db->query("SELECT * FROM v_caja_aper WHERE id_apc = ".$data['id_apc'])
            ->fetch(PDO::FETCH_OBJ);
            $c->{'Ingresos'} = $this->db->query("SELECT IFNULL(SUM(importe),0) AS total FROM tm_ingresos_adm WHERE id_apc = {$data['id_apc']} AND estado='a'")
            ->fetch(PDO::FETCH_OBJ);
            $c->{'EgresosA'} = $this->db->query("SELECT IFNULL(SUM(importe),0) AS total FROM v_gastosadm WHERE id_apc = {$data['id_apc']} AND (id_tg = 1 OR id_tg = 2 OR id_tg = 3) AND estado='a'")
            ->fetch(PDO::FETCH_OBJ);
            $c->{'EgresosB'} = $this->db->query("SELECT IFNULL(SUM(importe),0) AS total FROM v_gastosadm WHERE id_apc = {$data['id_apc']} AND id_tg = 4 AND estado='a'")
            ->fetch(PDO::FETCH_OBJ);
            return $c;
        }
        catch(Exception $e)
        {
            die($e->getMessage());
        }
    }

    public function stock_pollo()
    {
        try
        {
            $st = $this->db->prepare("SELECT (ent-sal) AS total FROM v_stock WHERE id_tipo_ins = 1 AND id_ins = 1");
            $st->execute();
            $row = $st->fetch(PDO::FETCH_OBJ);
            return $row;
        }
        catch(Exception $e)
        {
            die($e->getMessage());
        }
    }

    public function aperturar_caja($data)
    {
        try
        {
            date_default_timezone_set($_SESSION["zona_horaria"]);
            setlocale(LC_ALL,"es_ES@euro","es_ES","esp");
            $fecha = date("Y-m-d H:i:s");
            $consulta = "call usp_cajaAperturar( :flag, :id_usu, :id_caja, :id_turno, :fecha_aper, :monto_aper);";
            $arrayParam =  array(
                ':flag' => 1,
                ':id_usu' => Session::get('usuid'),
                ':id_caja' => $data['id_caja'],
                ':id_turno' => $data['id_turno'],
                ':fecha_aper' =>  $fecha,
                ':monto_aper' => $data['monto_aper']
            );
            $st = $this->db->prepare($consulta);
            $st->execute($arrayParam);
            $row = $st->fetch(PDO::FETCH_ASSOC);
            return $row;
        } catch (Exception $e) 
        {
            die($e->getMessage());
        }
    }

    public function cerrar_caja($data)
    {
        try
        {
            date_default_timezone_set($_SESSION["zona_horaria"]);
            setlocale(LC_ALL,"es_ES@euro","es_ES","esp");
            $fecha = date("Y-m-d H:i:s");
            $consulta = "call usp_cajaCerrar( :flag, :id_apc, :fecha_cierre, :monto_cierre, :monto_sistema, :stock_pollo);";
            $arrayParam =  array(
                ':flag' => 1,
                ':id_apc' => $data['id_apc'],
                ':fecha_cierre' => $fecha,
                ':monto_cierre' => $data['monto_cierre'],
                ':monto_sistema' => $data['monto_sistema'],
                ':stock_pollo' => $data['stock_pollo']
            );
            $st = $this->db->prepare($consulta);
            $st->execute($arrayParam);
            $row = $st->fetch(PDO::FETCH_ASSOC);
            return $row;
        } catch (Exception $e) 
        {
            die($e->getMessage());
        }
    }
    /* FIN MODULO APERTURA Y CIERRE */

    /* INICIO MODULO INGRESO */
    public function ingreso_list($data)
    {
        try
        {   
            date_default_timezone_set($_SESSION["zona_horaria"]);
            setlocale(LC_ALL,"es_ES@euro","es_ES","esp");
            $fecha = date("Y-m-d");
            $id_usu = Session::get('usuid');
            $stm = $this->db->prepare("SELECT * FROM tm_ingresos_adm WHERE DATE(fecha_reg) = ? AND id_usu = ? AND estado like ?");
            $stm->execute(array($fecha,$id_usu,$data['estado']));            
            $c = $stm->fetchAll(PDO::FETCH_OBJ);
            $data = array("data" => $c);
            $json = json_encode($data);
            echo $json; 
        }
        catch(Exception $e)
        {
            die($e->getMessage());
        }
    }

    public function ingreso_crud_create($data)
    {
        try
        {
            date_default_timezone_set($_SESSION["zona_horaria"]);
            setlocale(LC_ALL,"es_ES@euro","es_ES","esp");
            $fecha = date("Y-m-d H:i:s");
            $id_usu = Session::get('usuid');
            $id_apc = Session::get('apcid');
            $sql = "INSERT INTO tm_ingresos_adm (id_usu,id_apc,importe,responsable,motivo,fecha_reg) VALUES (?,?,?,?,?,?)";
            $this->db->prepare($sql)->execute(array($id_usu,$id_apc,$data['importe'],$data['responsable'],$data['motivo'],$fecha));
            $this->db=null; 
        } catch (Exception $e) 
        {
            die($e->getMessage());
        }
    }

    public function ingreso_estado($data)
    {
        try 
        {
            $sql = "UPDATE tm_ingresos_adm SET estado = 'i' WHERE id_ing = ?";
            $this->db->prepare($sql)->execute(array($data['id_ing']));
        } catch (Exception $e) 
        {
            die($e->getMessage());
        }
    }

    /* FIN MODULO INGRESO */


    /* INICIO MODULO EGRESO */
    public function egreso_list($data)
    {
        try
        {   
            date_default_timezone_set($_SESSION["zona_horaria"]);
            setlocale(LC_ALL,"es_ES@euro","es_ES","esp");
            $fecha = date("Y-m-d");
            $id_usu = Session::get('usuid');
            $stm = $this->db->prepare("SELECT * FROM v_gastosadm WHERE DATE(fecha_re) = ? AND id_usu = ? AND id_tg LIKE ? AND estado like ?");
            $stm->execute(array($fecha,$id_usu,$data['tipo_gasto'],$data['estado']));            
            $c = $stm->fetchAll(PDO::FETCH_OBJ);
            $data = array("data" => $c);
            $json = json_encode($data);
            echo $json; 
        }
        catch(Exception $e)
        {
            die($e->getMessage());
        }
    }

    public function egreso_crud_create($data)
    {
        try
        {
            date_default_timezone_set($_SESSION["zona_horaria"]);
            setlocale(LC_ALL,"es_ES@euro","es_ES","esp");
            $fecha = date("Y-m-d H:i:s");
            $id_usu = Session::get('usuid');
            $id_apc = Session::get('apcid');
            $sql = "INSERT INTO tm_gastos_adm (id_tipo_gasto,id_per,id_usu,id_apc,importe,responsable,motivo,fecha_registro) VALUES (?,?,?,?,?,?,?,?)";
            $this->db->prepare($sql)->execute(array(
                $data['id_tipo_gasto'],$data['id_per'],$id_usu,$id_apc,$data['importe'],$data['responsable'],$data['motivo'],$fecha));
            $this->db=null; 
        } catch (Exception $e) 
        {
            die($e->getMessage());
        }
    }

    public function egreso_estado($data)
    {
        try 
        {
            $sql = "UPDATE tm_gastos_adm SET estado = 'i' WHERE id_ga = ?";
            $this->db->prepare($sql)->execute(array($data['id_ga']));
        } catch (Exception $e) 
        {
            die($e->getMessage());
        }
    }

    /* FIN MODULO EGRESO */

    /* INICIO MODULO MONITOR DE VENTAS */

    public function monitor_list()
    {
        try
        {
            $stm = $this->db->prepare("SELECT * FROM v_caja_aper WHERE estado <> 'c'");
            $stm->execute();
            $c = $stm->fetchAll(PDO::FETCH_OBJ);
            $data = array("data" => $c);
            $json = json_encode($data);
            echo $json; 
        }
        catch(Exception $e)
        {
            die($e->getMessage());
        }
    }

    public function monitor_ventas_list()
    {
        try
        {
            $ifecha = date('Y-m-d H:i:s',strtotime($_POST['ifecha']));
            $ffecha = date('Y-m-d H:i:s',strtotime($_POST['ffecha']));

            if(Session::get('rol') == 1 OR Session::get('rol') == 2){
                $consulta = "";
            }else{
                // $consulta = " WHERE id_apc = ".Session::get('apcid');
                $consulta = " AND id_apc = ".Session::get('apcid');
            }
            $stm = $this->db->prepare("SELECT *,IFNULL((pago_efe+pago_tar),0) AS monto_total FROM  v_ventas_con WHERE (fec_ven >= ? AND fec_ven <= ?) AND id_tped like ? AND id_tdoc like ? ".$consulta."");
            $stm->execute(array($ifecha,$ffecha,$_POST['tped'],$_POST['tdoc']));
            $c = $stm->fetchAll(PDO::FETCH_OBJ);

            foreach($c as $k => $d)
            {
                $c[$k]->{'Pedido'} = $this->db->query("SELECT vm.desc_salon, vm.nro_mesa FROM tm_pedido_mesa AS pm INNER JOIN v_mesas AS vm ON pm.id_mesa = vm.id_mesa WHERE pm.id_pedido = ".$d->id_ped)
                    ->fetch(PDO::FETCH_OBJ);
            }

            foreach($c as $k => $d)
            {
                $c[$k]->{'Cliente'} = $this->db->query("SELECT nombre FROM v_clientes WHERE id_cliente = ".$d->id_cli)
                    ->fetch(PDO::FETCH_OBJ);
            }

            foreach($c as $k => $d)
            {
                $c[$k]->{'Tipopago'} = $this->db->query("SELECT descripcion AS nombre FROM tm_tipo_pago WHERE id_tipo_pago = " . $d->id_tpag)
                    ->fetch(PDO::FETCH_OBJ);
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

    public function monitor_mesas_list()
    {
        try
        {
            $stm = $this->db->prepare("SELECT id_pedido, desc_salon, nro_mesa FROM v_listar_mesas WHERE estado = 'i' OR estado = 'p'");
            $stm->execute();
            $c = $stm->fetchAll(PDO::FETCH_OBJ);
            foreach($c as $k => $d)
            {
                $c[$k]->{'Total'} = $this->db->query("SELECT SUM(precio*cant) AS total FROM tm_detalle_pedido WHERE id_pedido = ".$d->id_pedido." AND estado <> 'z'")
                    ->fetch(PDO::FETCH_OBJ);
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
    public function impresion_ingreso($id_pedido)
    {
        try
        {    
            // SELECT * FROM tm_ingresos_adm WHERE id_ing = ?
            $stm = $this->db->prepare("SELECT * FROM tm_ingresos_adm WHERE id_ing = ?");
            $stm->execute(array($id_pedido));
            $c = $stm->fetch(PDO::FETCH_OBJ);
            // SELECT * FROM tm_usuario WHERE id_usu = 2
            $c->{'usuario'} = $this->db->query("SELECT * FROM tm_usuario WHERE id_usu = " . $c->id_usu."")->fetchAll(PDO::FETCH_OBJ);
            /* Traemos el detalle */
            return $c;
        }
        catch(Exception $e)
        {
            die($e->getMessage());
        }
    }
    public function impresion_egreso($id_pedido)
    {
        try
        {    
            // SELECT * FROM tm_gastos_adm WHERE id_ga = ?

            $stm = $this->db->prepare("SELECT * FROM tm_gastos_adm WHERE id_ga = ?");
            $stm->execute(array($id_pedido));
            $c = $stm->fetch(PDO::FETCH_OBJ);
            // if(!$c->id_per== 0){
            //     // $c->{'trabajador'} = $this->db->query("SELECT * FROM tm_usuario WHERE id_usu = " . $c->id_usu."")->fetchAll(PDO::FETCH_OBJ);
            // }
            // SELECT * FROM tm_usuario WHERE id_usu = 2
            // SELECT * FROM tm_tipo_gasto WHERE id_tipo_gasto = 3

            $c->{'tipogasto'} = $this->db->query(" SELECT * FROM tm_tipo_gasto WHERE id_tipo_gasto = ".$c->id_tipo_gasto."")->fetchAll(PDO::FETCH_OBJ);
            $c->{'usuario'} = $this->db->query("SELECT * FROM tm_usuario WHERE id_usu = " . $c->id_usu."")->fetchAll(PDO::FETCH_OBJ);
            /* Traemos el detalle */
            return $c;
        }
        catch(Exception $e)
        {
            die($e->getMessage());
        }
    }

    /*
    public function monitor_ventas_porcobrar()
    {
        try
        {   
            $stm = $this->db->prepare("SELECT id_pedido FROM v_listar_mesas WHERE estado = 'i'");
            $stm->execute();
            $c = $stm->fetchAll(PDO::FETCH_OBJ);
            foreach($c as $k => $d)
            {
                $c[$k]->{'VentasPorCobrar'} = $this->db->query("SELECT SUM(precio*cant) AS total FROM tm_detalle_pedido WHERE id_pedido = ".$d->id_pedido." AND estado <> 'z'")
                    ->fetch(PDO::FETCH_OBJ);
            }
            return $c;
        }
        catch(Exception $e)
        {
            die($e->getMessage());
        }
    }
    */

    /* FIN MODULO MONITOR DE VENTAS */

}