<?php Session::init(); ?>
<?php

class Informe_Model extends Model
{
    public function __construct()
    {
        parent::__construct();
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

    public function Caja()
    {
        try
        {      
            return $this->db->selectAll('SELECT * FROM tm_caja');
        }
        catch(Exception $e)
        {
            die($e->getMessage());
        }
    }

    public function Cliente()
    {
        try
        {      
            return $this->db->selectAll('SELECT id_cliente,nombre FROM v_clientes');
        }
        catch(Exception $e)
        {
            die($e->getMessage());
        }
    }

    public function Categoria()
    {
        try
        {      
            return $this->db->selectAll('SELECT * FROM tm_producto_catg');
        }
        catch(Exception $e)
        {
            die($e->getMessage());
        }
    }

    public function Producto()
    {
        try
        {      
            return $this->db->selectAll('SELECT * FROM tm_producto');
        }
        catch(Exception $e)
        {
            die($e->getMessage());
        }
    }

    public function Presentacion()
    {
        try
        {      
            return $this->db->selectAll('SELECT * FROM tm_producto_pres');
        }
        catch(Exception $e)
        {
            die($e->getMessage());
        }
    }

    public function Mozo()
    {
        try
        {      
            return $this->db->selectAll("SELECT id_usu,CONCAT(nombres,' ',ape_paterno,' ',ape_materno) AS nombre FROM v_usuarios WHERE id_rol = 5");
        }
        catch(Exception $e)
        {
            die($e->getMessage());
        }
    }

    public function Proveedor()
    {
        try
        {      
            return $this->db->selectAll("SELECT * FROM tm_proveedor");
        }
        catch(Exception $e)
        {
            die($e->getMessage());
        }
    }

    public function Cajero()
    {
        try
        {    
            return $this->db->selectAll("SELECT id_usu,ape_paterno,ape_materno,nombres FROM tm_usuario WHERE (id_rol = 1 OR id_rol = 2 OR id_rol = 3) AND id_usu <> 1 AND estado = 'a'");
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

    public function Repartidor()
    {
        try
        {    
            return $this->db->selectAll("SELECT * FROM tm_usuario WHERE id_rol = 6");
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

    public function TipoPago()
    {
        try
        {   
            return $this->db->selectAll('SELECT * FROM tm_tipo_pago');
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

    /* INICIO VENTAS */

    public function venta_all_list()
    {
        try
        {
            $ifecha = date('Y-m-d H:i:s',strtotime($_POST['ifecha']));
            $ffecha = date('Y-m-d H:i:s',strtotime($_POST['ffecha']));
            $stm = $this->db->prepare("SELECT v.id_ven,v.id_ped,v.id_tped,v.id_tpag,v.pago_efe,v.pago_tar,v.desc_monto,v.comis_tar,v.comis_del,v.total AS stotal,v.fec_ven,v.desc_td,v.ser_doc,v.nro_doc,v.estado,IFNULL((v.pago_efe + v.pago_tar),0) AS total,v.id_cli,v.iva,v.id_usu,v.desc_tipo,v.desc_personal,c.desc_caja FROM v_ventas_con AS v INNER JOIN v_caja_aper AS c ON v.id_apc = c.id_apc WHERE (v.fec_ven >= ? AND v.fec_ven <= ?) AND v.id_tped like ? AND v.id_tdoc like ? AND v.id_cli like ? AND v.estado like ? GROUP BY v.id_ven");
            $stm->execute(array($ifecha,$ffecha,$_POST['tped'],$_POST['tdoc'],$_POST['cliente'],$_POST['estado']));
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
                $c[$k]->{'Personal'} = $this->db->query("SELECT CONCAT(nombres,' ',ape_paterno,' ',ape_materno) AS nombres FROM tm_usuario WHERE id_usu = ".$d->desc_personal)
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

    public function venta_all_det($data)
    {
        try
        {
            $stm = $this->db->prepare("SELECT id_prod,SUM(cantidad) AS cantidad,precio FROM tm_detalle_venta WHERE id_venta = ? GROUP BY id_prod, precio");
            $stm->execute(array($data['id_venta']));
            $c = $stm->fetchAll(PDO::FETCH_OBJ);
            foreach($c as $k => $d)
            {
                $c[$k]->{'Comision'} = $this->db->query("SELECT comision_delivery AS total FROM tm_venta WHERE id_venta = ".$data['id_venta'])
                    ->fetch(PDO::FETCH_OBJ);
           
                $c[$k]->{'Descuento'} = $this->db->query("SELECT descuento_monto AS total FROM tm_venta WHERE id_venta = ".$data['id_venta'])
                    ->fetch(PDO::FETCH_OBJ);
        
                $c[$k]->{'Producto'} = $this->db->query("SELECT pro_nom,pro_pre FROM v_productos WHERE id_pres = ".$d->id_prod)
                    ->fetch(PDO::FETCH_OBJ);
            }
            return $c;
        }
        catch(Exception $e)
        {
            die($e->getMessage());
        }
    }

    public function venta_delivery_list()
    {
        try
        {
            $ifecha = date('Y-m-d',strtotime($_POST['ifecha']));
            $ffecha = date('Y-m-d',strtotime($_POST['ffecha']));
            $stm = $this->db->prepare("SELECT v.id_ven,v.id_ped,v.id_cli,v.id_apc,v.desc_td,v.ser_doc,v.nro_doc,v.pago_efe,v.pago_tar,IFNULL((v.pago_efe + v.pago_tar),0) AS total,v.fec_ven,d.tipo_entrega,d.id_repartidor,d.desc_repartidor FROM v_ventas_con AS v INNER JOIN v_pedido_delivery AS d ON v.id_ped = d.id_pedido WHERE (DATE(v.fec_ven) >= ? AND DATE(v.fec_ven) <= ?) AND d.id_repartidor LIKE ? AND d.tipo_entrega LIKE ? AND d.id_repartidor <> 1");
            $stm->execute(array($ifecha,$ffecha,$_POST['id_repartidor'],$_POST['tipo_entrega']));
            $c = $stm->fetchAll(PDO::FETCH_OBJ);           
            foreach($c as $k => $d)
            {
                $c[$k]->{'Cliente'} = $this->db->query("SELECT nombre FROM v_clientes WHERE id_cliente = ".$d->id_cli)
                    ->fetch(PDO::FETCH_OBJ);

                $c[$k]->{'Caja'} = $this->db->query("SELECT desc_caja FROM v_caja_aper WHERE id_apc = ".$d->id_apc)
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

    public function venta_culqi_list()
    {
        try
        {
            $ifecha = date('Y-m-d',strtotime($_POST['ifecha']));
            $ffecha = date('Y-m-d',strtotime($_POST['ffecha']));
            $stm = $this->db->prepare("SELECT v.desc_td,v.ser_doc,v.nro_doc,v.total,v.iva,d.tipo_entrega,d.nombre_cliente,d.email_cliente,d.fecha_pedido FROM v_ventas_con AS v INNER JOIN v_pedido_delivery AS d ON v.id_ped = d.id_pedido WHERE d.tipo_pago = 4 AND (DATE(v.fec_ven) >= ? AND DATE(v.fec_ven) <= ?) AND d.tipo_entrega LIKE ?");
            $stm->execute(array($ifecha,$ffecha,$_POST['tipo_entrega']));
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

    public function venta_prod_list($data)
    {
        try
        {
            $ifecha = date('Y-m-d',strtotime($data['ifecha']));
            $ffecha = date('Y-m-d',strtotime($data['ffecha']));
            $stm = $this->db->prepare("SELECT dp.id_prod,
            SUM(CASE WHEN v.id_tipo_pedido = 1 THEN dp.cantidad ELSE 0 END) AS cantidad_salon,
            SUM(CASE WHEN v.id_tipo_pedido = 2 THEN dp.cantidad ELSE 0 END) AS cantidad_mostrador,
            SUM(CASE WHEN v.id_tipo_pedido = 3 THEN dp.cantidad ELSE 0 END) AS cantidad_delivery,
            SUM(dp.cantidad) AS cantidad_total,dp.precio,IFNULL((SUM(dp.cantidad)*dp.precio),0) AS total,v.fecha_venta 
            FROM tm_detalle_venta AS dp 
            INNER JOIN tm_venta AS v ON dp.id_venta = v.id_venta 
            INNER JOIN v_productos AS vp ON vp.id_pres = dp.id_prod 
            WHERE (DATE(v.fecha_venta) >= ? AND DATE(v.fecha_venta) <= ?) AND vp.id_catg LIKE ?
            AND vp.id_prod LIKE ? AND vp.id_pres LIKE ? AND v.estado = 'a' GROUP BY dp.id_prod, dp.precio
            ORDER BY v.fecha_venta DESC, SUM(dp.cantidad) DESC;");
            /*
            $stm = $this->db->prepare("SELECT dp.id_prod,SUM(dp.cantidad) AS cantidad,dp.precio,IFNULL((SUM(dp.cantidad)*dp.precio),0) AS total,v.fecha_venta FROM tm_detalle_venta AS dp INNER JOIN tm_venta AS v ON dp.id_venta = v.id_venta INNER JOIN v_productos AS vp ON vp.id_pres = dp.id_prod WHERE (DATE(v.fecha_venta) >= ? AND DATE(v.fecha_venta) <= ?) AND vp.id_catg like ? AND vp.id_prod like ? AND vp.id_pres like ? AND v.estado = 'a' GROUP BY dp.id_prod, dp.precio , DATE(v.fecha_venta) ORDER BY v.fecha_venta DESC, SUM(dp.cantidad) DESC");
            */
            $stm->execute(array($ifecha,$ffecha,$data['id_catg'],$data['id_prod'],$data['id_pres']));
            $c = $stm->fetchAll(PDO::FETCH_OBJ);
            foreach($c as $k => $d)
            {
                $c[$k]->{'Producto'} = $this->db->query("SELECT pro_nom,pro_pre,pro_cat FROM v_productos WHERE id_pres = ".$d->id_prod)
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

    public function venta_prod_kardex_list($data)
    {
        try
        {
            $ifecha = date('Y-m-d',strtotime($data['ifecha']));
            $ffecha = date('Y-m-d',strtotime($data['ffecha']));
            $query = $this->db->prepare("SELECT v.fec_ven AS fecha_venta, v.desc_td AS documento_venta, CONCAT(v.ser_doc,'-',v.nro_doc) AS nro_documento, CONCAT(p.pro_nom,' - ',p.pro_pre) AS producto_presentacion, p.pro_cat AS producto_categoria, dv.cantidad AS cantidad_vendida, dv.precio AS precio_venta, (dv.cantidad*dv.precio) AS total
            FROM tm_detalle_venta AS dv 
            INNER JOIN v_ventas_con AS v ON dv.id_venta = v.id_ven 
            INNER JOIN v_productos AS p ON p.id_pres = dv.id_prod 
            WHERE (DATE(v.fec_ven) >= ? AND DATE(v.fec_ven) <= ?) AND p.id_catg = ?
            AND p.id_prod = ? AND p.id_pres = ? AND v.estado = 'a' ORDER BY v.fec_ven DESC;");
            $query->execute(array($ifecha,$ffecha,$data['id_catg'],$data['id_prod'],$data['id_pres']));
            $a = $query->fetchAll(PDO::FETCH_OBJ);

            $data = array("data" => $a);
            $json = json_encode($data);
            echo $json;
        }
        catch(Exception $e)
        {
            die($e->getMessage());
        }
    }

    public function venta_prod_kardex_graphic($data)
    {
        try
        {
            $ifecha = date('Y-m-d',strtotime($data['ifecha']));
            $ffecha = date('Y-m-d',strtotime($data['ffecha']));

            $query = $this->db->prepare("SELECT DATE(v.fec_ven) AS y, SUM(dv.cantidad) AS a FROM tm_detalle_venta AS dv INNER JOIN v_ventas_con AS v ON dv.id_venta = v.id_ven INNER JOIN v_productos AS p ON p.id_pres = dv.id_prod WHERE (DATE(v.fec_ven) >= ? AND DATE(v.fec_ven) <= ?) AND p.id_catg = ? AND p.id_prod = ? AND p.id_pres = ? AND v.estado = 'a' GROUP BY DATE(v.fec_ven) ORDER BY v.fec_ven DESC");
            $query->execute(array($ifecha,$ffecha,$data['id_catg'],$data['id_prod'],$data['id_pres']));
            $a = $query->fetchAll(PDO::FETCH_OBJ);

            $data = array('data' => $a);
            $json = json_encode($data);
            echo $json;
        }
        catch(Exception $e)
        {
            die($e->getMessage());
        }
    } 
    public function venta_prod_margen_list($data)
    {
        try
        {
            $ifecha = date('Y-m-d',strtotime($data['ifecha']));
            $ffecha = date('Y-m-d',strtotime($data['ffecha']));
            $stm = $this->db->prepare("SELECT CONCAT(p.pro_nom,' - ',p.pro_pre) AS producto_presentacion, p.pro_cat AS producto_categoria, SUM(dv.cantidad) AS cantidad_vendida, dv.costo AS costo_unitario, (SUM(dv.cantidad)*dv.costo) AS costo_total, dv.precio AS precio_venta, (dv.precio-dv.costo) AS margen_unitario, ((SUM(dv.cantidad)*dv.precio) - (SUM(dv.cantidad)*dv.costo)) AS margen_total, (SUM(dv.cantidad)*dv.precio) AS total 
                FROM tm_detalle_venta AS dv 
                INNER JOIN tm_venta AS v ON v.id_venta = dv.id_venta
                INNER JOIN v_productos AS p ON p.id_pres = dv.id_prod
                WHERE (DATE(v.fecha_venta) >= ? AND DATE(v.fecha_venta) <= ?) AND p.id_catg LIKE ? AND p.id_prod LIKE ? AND p.id_pres LIKE ? AND v.estado = 'a' GROUP BY dv.precio,dv.costo ORDER BY v.fecha_venta DESC;");
            $stm->execute(array($ifecha,$ffecha,$data['id_catg'],$data['id_prod'],$data['id_pres']));
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
    public function combPro($data)
    {
        try
        {
            $stm = $this->db->prepare("SELECT * FROM tm_producto WHERE id_catg = ?");
            $stm->execute(array($data['cod']));
            $c = $stm->fetchAll(PDO::FETCH_OBJ);
            return $c;
        }
        catch(Exception $e)
        {
            die($e->getMessage());
        }
    }
    public function venta_cort_list($data)
    {
        try
        {
            $ifecha = date('Y-m-d H:i:s',strtotime($data['ifecha']));
            $ffecha = date('Y-m-d H:i:s',strtotime($data['ffecha']));
            $stm = $this->db->prepare("SELECT v.id_ven,v.id_ped,v.desc_monto,v.desc_tipo,v.desc_motivo,v.comis_tar,v.comis_del,v.total AS stotal,v.fec_ven,v.desc_td,CONCAT(v.ser_doc,'-',v.nro_doc) AS numero,IFNULL(SUM(v.desc_monto),0) AS total_descuento, v.total ,v.id_cli,v.iva,v.id_usu,c.desc_caja,v.desc_usu FROM v_ventas_con AS v INNER JOIN v_caja_aper AS c ON v.id_apc = c.id_apc WHERE (v.fec_ven >= ? AND v.fec_ven <= ?) AND v.estado = 'a' AND v.desc_tipo = 1 GROUP BY v.id_ven ORDER BY DATE(v.fec_ven) ASC");
            $stm->execute(array($ifecha,$ffecha));
            $c = $stm->fetchAll(PDO::FETCH_OBJ);
            /*        
            foreach($c as $k => $d)
            {
                $c[$k]->{'Cliente'} = $this->db->query("SELECT nombre FROM v_clientes WHERE id_cliente = ".$d->id_cli)
                    ->fetch(PDO::FETCH_OBJ);
            }
            */
            $data = array("data" => $c);
            $json = json_encode($data);
            echo $json;       
        }
        catch(Exception $e)
        {
            die($e->getMessage());
        }
    }
    public function combPre($data)
    {
        try
        {
            $stm = $this->db->prepare("SELECT * FROM tm_producto_pres WHERE id_prod = ?");
            $stm->execute(array($data['cod']));
            $c = $stm->fetchAll(PDO::FETCH_OBJ);
            return $c;
        }
        catch(Exception $e)
        {
            die($e->getMessage());
        }
    }

    public function venta_mozo_list($data)
    {
        try
        {
            $ifecha = date('Y-m-d H:i:s',strtotime($data['ifecha']));
            $ffecha = date('Y-m-d H:i:s',strtotime($data['ffecha']));
            $stm = $this->db->prepare("SELECT v.fec_ven,v.desc_td,CONCAT(v.ser_doc,'-',v.nro_doc) AS numero,IFNULL(SUM(v.pago_efe+v.pago_tar),0) AS total,v.id_cli,pm.id_mozo FROM v_ventas_con AS v INNER JOIN tm_pedido_mesa AS pm ON v.id_ped = pm.id_pedido WHERE (v.fec_ven >= ? AND v.fec_ven <= ?) AND pm.id_mozo like ? AND v.estado = 'a' GROUP BY v.id_ven");
            $stm->execute(array($ifecha,$ffecha,$data['id_mozo']));
            $c = $stm->fetchAll(PDO::FETCH_OBJ);        
            foreach($c as $k => $d)
            {
                $c[$k]->{'Mozo'} = $this->db->query("SELECT CONCAT(nombres,' ',ape_paterno,' ',ape_materno) AS nombre FROM v_usuarios WHERE id_usu = ".$d->id_mozo)
                    ->fetch(PDO::FETCH_OBJ);

                $c[$k]->{'Cliente'} = $this->db->query("SELECT nombre FROM v_clientes WHERE id_cliente = ".$d->id_cli)
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


    public function venta_fpago_list($data)
    {
        try
        {
            $ifecha = date('Y-m-d H:i:s',strtotime($data['ifecha']));
            $ffecha = date('Y-m-d H:i:s',strtotime($data['ffecha']));
            /*
            if($data['id_tpag'] == 1){
                $tipo_pago = 'AND (v.id_tpag = 1 OR v.id_tpag = 3) ';
            } else if($data['id_tpag'] == 2){
                $tipo_pago = 'AND (v.id_tpag = 2 OR v.id_tpag = 3) ';
            } else if($data['id_tpag'] == 3){
                $tipo_pago = 'AND (v.id_tpag = 1 OR v.id_tpag = 2 OR v.id_tpag = 3) ';
            } else {
                $tipo_pago = '';
            }
            */
            $stm = $this->db->prepare("SELECT v.id_ven,v.id_ped,v.id_tpag,v.pago_efe,v.pago_tar,v.desc_monto,v.comis_tar,v.comis_del,v.total AS stotal,v.fec_ven,v.desc_td,CONCAT(v.ser_doc,'-',v.nro_doc) AS numero,IFNULL(SUM(v.pago_efe+v.pago_tar),0) AS total,v.id_cli,v.iva,v.id_usu,c.desc_caja,v.estado,v.codigo_operacion FROM v_ventas_con AS v INNER JOIN v_caja_aper AS c ON v.id_apc = c.id_apc WHERE (v.fec_ven >= ? AND v.fec_ven <= ?) AND v.id_tpag LIKE ? AND v.estado = 'a' GROUP BY v.id_ven ORDER BY DATE(v.fec_ven) ASC");
            $stm->execute(array($ifecha,$ffecha,$data['id_tpag']));
            $c = $stm->fetchAll(PDO::FETCH_OBJ);           
            foreach($c as $k => $d)
            {
                $c[$k]->{'Cliente'} = $this->db->query("SELECT nombre FROM v_clientes WHERE id_cliente = ".$d->id_cli)
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

    public function venta_desc_list($data)
    {
        try
        {
            $ifecha = date('Y-m-d H:i:s',strtotime($data['ifecha']));
            $ffecha = date('Y-m-d H:i:s',strtotime($data['ffecha']));
            $stm = $this->db->prepare("SELECT v.id_ven,v.id_ped,v.id_tpag,v.pago_efe,v.pago_tar,v.desc_monto,v.desc_tipo,v.desc_motivo,v.comis_tar,v.comis_del,v.total AS stotal,v.fec_ven,v.desc_td,CONCAT(v.ser_doc,'-',v.nro_doc) AS numero,IFNULL(SUM(v.pago_efe+v.pago_tar),0) AS total,v.id_cli,v.iva,v.id_usu,c.desc_caja,v.desc_usu FROM v_ventas_con AS v INNER JOIN v_caja_aper AS c ON v.id_apc = c.id_apc WHERE (v.fec_ven >= ? AND v.fec_ven <= ?) AND v.desc_tipo LIKE ? AND v.estado = 'a' AND v.desc_monto > 0 GROUP BY v.id_ven ORDER BY DATE(v.fec_ven) ASC");
            $stm->execute(array($ifecha,$ffecha,$data['desc_tipo']));
            $c = $stm->fetchAll(PDO::FETCH_OBJ);
            /*        
            foreach($c as $k => $d)
            {
                $c[$k]->{'Cliente'} = $this->db->query("SELECT nombre FROM v_clientes WHERE id_cliente = ".$d->id_cli)
                    ->fetch(PDO::FETCH_OBJ);
            }
            */
            $data = array("data" => $c);
            $json = json_encode($data);
            echo $json;       
        }
        catch(Exception $e)
        {
            die($e->getMessage());
        }
    }

    public function venta_all_imp($data)
    {
        try
        {      
            $stm = $this->db->prepare("SELECT * FROM v_ventas_con WHERE id_ven = ?");
            $stm->execute(array($data));
            $c = $stm->fetch(PDO::FETCH_OBJ);
            $c->{'Empresa'} = $this->db->query("SELECT * FROM tm_empresa")
                ->fetch(PDO::FETCH_OBJ);
            $c->{'Cliente'} = $this->db->query("SELECT * FROM v_clientes WHERE id_cliente = " . $c->id_cli)
                ->fetch(PDO::FETCH_OBJ);
            $c->{'Pedido'} = $this->db->query("SELECT vm.desc_salon, vm.nro_mesa  FROM tm_pedido_mesa AS pm INNER JOIN v_mesas AS vm ON pm.id_mesa = vm.id_mesa WHERE pm.id_pedido = " . $c->id_ped)
                ->fetch(PDO::FETCH_OBJ);
            /* Traemos el detalle */
            $c->{'Detalle'} = $this->db->query("SELECT v_productos.pro_cod AS codigo_producto, 
                CONCAT(v_productos.pro_nom,' ',) AS nombre_producto, 
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
                WHERE tm_venta.id_tipo_doc  IN ('1','2','3') AND tm_detalle_venta.precio > 0 AND tm_detalle_venta.id_venta = ".$data)
                ->fetchAll(PDO::FETCH_OBJ);
            return $c;
        }
        catch(Exception $e)
        {
            die($e->getMessage());
        }
    }

    public function venta_all_imp_($data)
    {
        try
        {      
            $stm = $this->db->prepare("SELECT * FROM v_ventas_con WHERE id_ven = ?");
            $stm->execute(array($data));
            $c = $stm->fetch(PDO::FETCH_OBJ);
            $c->{'Cliente'} = $this->db->query("SELECT * FROM v_clientes WHERE id_cliente = " . $c->id_cli)
                ->fetch(PDO::FETCH_OBJ);
            $c->{'Pedido'} = $this->db->query("SELECT vm.desc_salon, vm.nro_mesa  FROM tm_pedido_mesa AS pm INNER JOIN v_mesas AS vm ON pm.id_mesa = vm.id_mesa WHERE pm.id_pedido = " . $c->id_ped)
                ->fetch(PDO::FETCH_OBJ);

            $c->{'Detalle'} = $this->db->query("SELECT v_productos.pro_cod AS codigo_producto, 
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
                WHERE tm_venta.id_tipo_doc  IN ('1','2','3') AND tm_detalle_venta.precio > 0 AND tm_detalle_venta.id_venta = ".$data)
                ->fetchAll(PDO::FETCH_OBJ);
            /*
            $c->{'Detalle'} = $this->db->query("SELECT id_prod,SUM(cantidad) AS cantidad, precio FROM tm_detalle_venta WHERE id_venta = " . $c->id_ven." GROUP BY id_prod, precio")
                ->fetchAll(PDO::FETCH_OBJ);
            foreach($c->Detalle as $k => $d)
            {
                $c->Detalle[$k]->{'Producto'} = $this->db->query("SELECT pro_nom, pro_pre FROM v_productos WHERE id_pres = " . $d->id_prod)
                    ->fetch(PDO::FETCH_OBJ);
            }
            */
            return $c;
        }
        catch(Exception $e)
        {
            die($e->getMessage());
        }
    }

    /* FIN MODULO VENTAS */

    /* INICIO MODULO COMPRAS */

    public function compra_all_list($data)
    {
        try
        {
            $ifecha = date('Y-m-d',strtotime($data['ifecha']));
            $ffecha = date('Y-m-d',strtotime($data['ffecha']));
            $stm = $this->db->prepare("SELECT * FROM v_compras WHERE (fecha_c >= ? AND fecha_c <= ?) AND id_prov LIKE ? AND id_tipo_compra LIKE ? AND id_tipo_doc LIKE ? AND estado LIKE ? GROUP BY id_compra");
            $stm->execute(array($ifecha,$ffecha,$data['id_prov'],$data['id_tipo_compra'],$data['id_tipo_doc'],$data['estado']));
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

    public function compra_all_det($data)
    {
        try
        {
            $stm = $this->db->prepare("SELECT * FROM tm_compra_detalle WHERE id_compra = ?");
            $stm->execute(array($data['id_compra']));
            $c = $stm->fetchAll(PDO::FETCH_OBJ);
            foreach($c as $k => $d)
            {
                $c[$k]->{'Producto'} = $this->db->query("SELECT ins_cod,ins_nom,ins_med,ins_cat FROM v_insprod WHERE id_tipo_ins = ".$d->id_tp."  AND id_ins = ".$d->id_pres)
                    ->fetch(PDO::FETCH_OBJ);
            }
            return $c;
        }
        catch(Exception $e)
        {
            die($e->getMessage());
        }
    }

    public function compra_all_det_cuota($data)
    {
        try
        {
            $stm = $this->db->prepare("SELECT * FROM tm_compra_credito WHERE id_compra = ?");
            $stm->execute(array($data['id_compra']));
            $c = $stm->fetchAll(PDO::FETCH_OBJ);
            return $c;
        }
        catch(Exception $e)
        {
            die($e->getMessage());
        }
    }

    public function compra_all_det_subcuota($data)
    {
        try
        {
            $stm = $this->db->prepare("SELECT * FROM tm_credito_detalle WHERE id_credito = ?");
            $stm->execute(array($data['id_credito']));
            $c = $stm->fetchAll(PDO::FETCH_OBJ);
            foreach($c as $k => $d)
            {
                $c[$k]->{'Usuario'} = $this->db->query("SELECT CONCAT(ape_paterno,' ',ape_materno,' ',nombres) AS nombre FROM v_usuarios WHERE id_usu = ".$d->id_usu)
                    ->fetch(PDO::FETCH_OBJ);
            }
            return $c;
        }
        catch(Exception $e)
        {
            die($e->getMessage());
        }
    }

    /* FIN MODULO COMPRAS */

    /* INICIO MODULO FINANZAS */

    public function finanza_arq_list($data)
    {
        try
        {
            $ifecha = date('Y-m-d H:i:s',strtotime($data['ifecha']));
            $ffecha = date('Y-m-d H:i:s',strtotime($data['ffecha']));
            $stm = $this->db->prepare("SELECT * FROM v_caja_aper WHERE fecha_aper >= ? AND fecha_aper <= ? AND id_usu like ? ORDER BY id_apc DESC");
            $stm->execute(array($ifecha,$ffecha,$data['id_usu']));
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

    public function finanza_arq_resumen_default($data)
    {
        try
        {    
            $stm = $this->db->prepare("SELECT v.id_apc,v.id_ped,IFNULL(SUM(v.pago_efe),0) AS pago_efe, IFNULL(SUM(v.pago_tar),0) AS pago_tar, IFNULL(SUM(v.desc_monto),0) AS descu, IFNULL(SUM(v.comis_tar),0) AS comis_tar, IFNULL(SUM(v.comis_del),0) AS comis_del, IFNULL(SUM(v.pago_efe+v.pago_tar),0) AS total, v.estado FROM v_ventas_con AS v LEFT JOIN tm_pedido_delivery AS d ON v.id_ped = d.id_pedido WHERE v.id_apc = ? AND v.estado <> 'i' AND (d.id_repartidor IS NULL OR d.id_repartidor = 1 OR d.id_repartidor < 2000)");
            $stm->execute(array($data['cod_ape']));
            $c = $stm->fetch(PDO::FETCH_OBJ);
            $c->{'Apertura'} = $this->db->query("SELECT * FROM v_caja_aper WHERE id_apc = ".$data['cod_ape'])
            ->fetch(PDO::FETCH_OBJ);
            $c->{'Ingresos'} = $this->db->query("SELECT IFNULL(SUM(importe),0) AS total FROM tm_ingresos_adm WHERE id_apc = {$data['cod_ape']} AND estado='a'")
            ->fetch(PDO::FETCH_OBJ);
            $c->{'EgresosA'} = $this->db->query("SELECT IFNULL(SUM(importe),0) AS total FROM v_gastosadm WHERE id_apc = {$data['cod_ape']} AND (id_tg = 1 OR id_tg = 2 OR id_tg = 3) AND estado='a'")
            ->fetch(PDO::FETCH_OBJ);
            $c->{'EgresosB'} = $this->db->query("SELECT IFNULL(SUM(importe),0) AS total FROM v_gastosadm WHERE id_apc = {$data['cod_ape']} AND id_tg = 4 AND estado='a'")
            ->fetch(PDO::FETCH_OBJ);
            return $c;
        }
        catch(Exception $e)
        {
            die($e->getMessage());
        }
    }

    public function finanza_arq_resumen_venta_list($data)
    {
        try
        {   
            if($data['cod_filtro'] == 1){
                $stm = $this->db->prepare("SELECT IFNULL((v.pago_efe+v.pago_tar),0) AS monto_total,v.estado,v.ser_doc,v.nro_doc,v.desc_td,v.desc_monto FROM v_ventas_con AS v LEFT JOIN tm_pedido_delivery AS d ON v.id_ped = d.id_pedido WHERE v.id_apc = ? AND v.estado = ? AND (d.id_repartidor IS NULL OR d.id_repartidor = 1 OR d.id_repartidor < 2000)");
            } else {
                $stm = $this->db->prepare("SELECT IFNULL((v.pago_efe+v.pago_tar),0) AS monto_total,v.estado,v.ser_doc,v.nro_doc,v.desc_td,v.desc_monto FROM v_ventas_con AS v LEFT JOIN tm_pedido_delivery AS d ON v.id_ped = d.id_pedido WHERE v.id_apc = ? AND v.estado = ? AND v.desc_monto <> '0.00' AND (d.id_repartidor IS NULL OR d.id_repartidor = 1 OR d.id_repartidor < 2000)");
            }
            $stm->execute(array($data['cod_ape'],$data['estado']));
            $c = $stm->fetchAll(PDO::FETCH_OBJ);
            return $c;
        }
        catch(Exception $e)
        {
            die($e->getMessage());
        }
    }

    public function finanza_arq_resumen_venta_delivery_list($data)
    {
        try
        {   
            $stm = $this->db->prepare("SELECT IFNULL((v.pago_efe+v.pago_tar),0) AS monto_total,v.estado,v.ser_doc,v.nro_doc,v.desc_td FROM v_ventas_con AS v INNER JOIN tm_pedido_delivery AS d ON v.id_ped = d.id_pedido WHERE v.id_apc = ? AND v.estado = ?");
            $stm->execute(array($data['cod_ape'],$data['estado']));
            $c = $stm->fetchAll(PDO::FETCH_OBJ);
            return $c;
        }
        catch(Exception $e)
        {
            die($e->getMessage());
        }
    }

    public function finanza_arq_resumen_caja_list_i($data)
    {
        try
        {   
            $stm = $this->db->prepare("SELECT * FROM tm_ingresos_adm WHERE id_apc = ? AND estado = ?");
            $stm->execute(array($data['id_apc'],$data['estado']));
            $c = $stm->fetchAll(PDO::FETCH_OBJ);
            return $c;
        }
        catch(Exception $e)
        {
            die($e->getMessage());
        }
    }

    public function finanza_arq_resumen_caja_list_e($data)
    {
        try
        {   
            $stm = $this->db->prepare("SELECT * FROM v_gastosadm WHERE id_apc = ? AND estado = ?");
            $stm->execute(array($data['id_apc'],$data['estado']));
            $c = $stm->fetchAll(PDO::FETCH_OBJ);
            return $c;
        }
        catch(Exception $e)
        {
            die($e->getMessage());
        }
    }

    public function finanza_arq_resumen_productos($data)
    {
        try
        {
            $stm = $this->db->prepare("SELECT d.id_prod,SUM(d.cantidad) AS cantidad, d.precio FROM tm_venta AS v INNER JOIN tm_detalle_venta AS d ON v.id_venta = d.id_venta WHERE v.id_apc = ? AND v.estado = 'a' GROUP BY d.id_prod, d.precio ORDER BY cantidad DESC");
            $stm->execute(array($data['id_apc']));
            $c = $stm->fetchAll(PDO::FETCH_OBJ);
            foreach($c as $k => $d)
            {
                $c[$k]->{'Producto'} = $this->db->query("SELECT pro_nom,pro_pre FROM v_productos WHERE id_pres = ".$d->id_prod)
                    ->fetch(PDO::FETCH_OBJ);
            }
            return $c;
        }
        catch(Exception $e)
        {
            die($e->getMessage());
        }
    }

    public function finanza_arq_resumen_anulaciones($data)
    {
        try
        {
            $stm = $this->db->prepare("SELECT dp.cant, dp.precio, dp.id_pres FROM tm_detalle_pedido AS dp INNER JOIN tm_pedido AS p ON dp.id_pedido = p.id_pedido WHERE dp.estado = 'z' AND p.id_apc = ?");
            $stm->execute(array($data['cod_ape']));
            $c = $stm->fetchAll(PDO::FETCH_OBJ);
            foreach($c as $k => $d)
            {
                $c[$k]->{'Producto'} = $this->db->query("SELECT pro_nom,pro_pre FROM v_productos WHERE id_pres = ".$d->id_pres)
                    ->fetch(PDO::FETCH_OBJ);
            }
            return $c;
        }
        catch(Exception $e)
        {
            die($e->getMessage());
        }
    }

    public function finanza_ing_list($data)
    {
        try
        {
            $ifecha = date('Y-m-d',strtotime($data['ifecha']));
            $ffecha = date('Y-m-d',strtotime($data['ffecha']));
            $stm = $this->db->prepare("SELECT * FROM tm_ingresos_adm WHERE (DATE(fecha_reg) >= ? AND DATE(fecha_reg) <= ?) AND id_usu LIKE ? AND estado LIKE ?");
            $stm->execute(array($ifecha,$ffecha,$data['id_usu'],$data['estado']));
            $c = $stm->fetchAll(PDO::FETCH_OBJ);
            foreach($c as $k => $d)
            {
                $c[$k]->{'Caja'} = $this->db->query("SELECT desc_caja FROM v_caja_aper WHERE id_apc = ".$d->id_apc)
                    ->fetch(PDO::FETCH_OBJ);
            }
            foreach($c as $k => $d)
            {
                $c[$k]->{'Cajero'} = $this->db->query("SELECT CONCAT(nombres,' ',ape_paterno,' ',ape_materno) AS desc_usu FROM tm_usuario WHERE id_usu = ".$d->id_usu)
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

    public function finanza_egr_list($data)
    {
        try
        {
            $ifecha = date('Y-m-d',strtotime($data['ifecha']));
            $ffecha = date('Y-m-d',strtotime($data['ffecha']));
            $stm = $this->db->prepare("SELECT * FROM v_gastosadm WHERE (DATE(fecha_re) >= ? AND DATE(fecha_re) <= ?) AND id_tg LIKE ? AND id_usu LIKE ? AND estado LIKE ?");
            $stm->execute(array($ifecha,$ffecha,$data['tipo_gasto'],$data['id_usu'],$data['estado']));
            $c = $stm->fetchAll(PDO::FETCH_OBJ);
            foreach($c as $k => $d)
            {
                $c[$k]->{'Caja'} = $this->db->query("SELECT desc_caja FROM v_caja_aper WHERE id_apc = ".$d->id_apc)
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

    public function finanza_rem_list($data)
    {
        try
        {
            $ifecha = date('Y-m-d',strtotime($data['ifecha']));
            $ffecha = date('Y-m-d',strtotime($data['ffecha']));
            $stm = $this->db->prepare("SELECT id_usu,fecha_re,id_apc,des_tg,desc_usu,desc_per,motivo,importe,estado FROM v_gastosadm WHERE id_tg = 3 AND (DATE(fecha_re) >= ? AND DATE(fecha_re) <= ?) AND id_per LIKE ? AND estado LIKE ?");
            $stm->execute(array($ifecha,$ffecha,$data['id_per'],$data['estado']));
            $c = $stm->fetchAll(PDO::FETCH_OBJ);
            foreach($c as $k => $d)
            {
                $c[$k]->{'Caja'} = $this->db->query("SELECT desc_caja FROM v_caja_aper WHERE id_apc = ".$d->id_apc)
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

    public function oper_anul_list($data)
    {
        try
        {
            $ifecha = date('Y-m-d H:i:s',strtotime($data['ifecha']));
            $ffecha = date('Y-m-d H:i:s',strtotime($data['ffecha']));
            $stm = $this->db->prepare("SELECT * FROM tm_detalle_pedido WHERE (fecha_pedido >= ? AND fecha_pedido <= ?) AND id_usu like ? AND estado = 'z'");
            $stm->execute(array($ifecha,$ffecha,$data['id_usu']));
            $c = $stm->fetchAll(PDO::FETCH_OBJ);
            foreach($c as $k => $d)
            {
                $c[$k]->{'Personal'} = $this->db->query("SELECT id_usu,CONCAT(ape_paterno,' ',ape_materno,' ',nombres) AS nombres FROM tm_usuario WHERE id_usu = ".$d->id_usu)
                    ->fetch(PDO::FETCH_OBJ);
            }
            foreach($c as $k => $d)
            {
                $c[$k]->{'TipoPedido'} = $this->db->query("SELECT id_tipo_pedido FROM tm_pedido WHERE id_pedido = ".$d->id_pedido)
                    ->fetch(PDO::FETCH_OBJ);
            }
            foreach($c as $k => $d)
            {
                $c[$k]->{'Producto'} = $this->db->query("SELECT * FROM v_productos WHERE id_pres = ".$d->id_pres)
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

    public function finanza_arq_imp($data)
    {
        try
        {      
            $stm = $this->db->prepare("SELECT * FROM v_caja_aper WHERE id_apc = ?");
            $stm->execute(array($data));
            $c = $stm->fetch(PDO::FETCH_OBJ);

            $c->{'Principal'} = $this->db->query("SELECT v.id_apc,v.id_ped,IFNULL(SUM(v.pago_efe),0) AS pago_efe, IFNULL(SUM(v.pago_tar),0) AS pago_tar, IFNULL(SUM(v.desc_monto),0) AS descu, IFNULL(SUM(v.comis_tar),0) AS comis_tar, IFNULL(SUM(v.comis_del),0) AS comis_del, IFNULL(SUM(v.pago_efe+v.pago_tar),0) AS total, v.estado FROM v_ventas_con AS v LEFT JOIN tm_pedido_delivery AS d ON v.id_ped = d.id_pedido WHERE v.id_apc = {$data} AND v.estado <> 'i' AND (d.id_repartidor IS NULL OR d.id_repartidor = 1 OR d.id_repartidor < 2000)")
            ->fetch(PDO::FETCH_OBJ);

            $c->{'Efectivo'} = $this->db->query("SELECT IF(v.id_tpag = 1 OR v.id_tpag = 3,SUM(v.pago_efe),0) AS total, COUNT(*) AS cant FROM v_ventas_con AS v LEFT JOIN tm_pedido_delivery AS d ON v.id_ped = d.id_pedido WHERE v.id_apc = {$data} AND v.estado <> 'i' AND (d.id_repartidor IS NULL OR d.id_repartidor = 1 OR d.id_repartidor < 2000) AND (v.id_tpag = 1 OR v.id_tpag = 3)")
            ->fetch(PDO::FETCH_OBJ);

            $c->{'Pagos'} = $this->db->query("SELECT b.DESCRIPCION, 
            CASE WHEN b.id_tipo_pago = 1 OR b.id_tipo_pago = 3 THEN nvl(COUNT(b.id_tipo_pago),0) + nvl((SELECT COUNT(1) FROM tm_venta c WHERE c.id_tipo_pago = 3 AND c.id_apc = a.id_apc),0)
                 WHEN b.id_tipo_pago = 2 THEN nvl(COUNT(b.id_tipo_pago),0)
                 ELSE nvl(COUNT(b.id_tipo_pago),0) END TRANSACCIONES, 
            CASE WHEN b.id_tipo_pago = 1 OR b.id_tipo_pago = 3 THEN nvl(sum(a.pago_efe),0) + nvl((SELECT sum(c.pago_efe) FROM tm_venta c WHERE c.id_tipo_pago = 3 AND c.id_apc = a.id_apc),0)
                 WHEN b.id_tipo_pago = 2 OR b.id_tipo_pago = 3 THEN nvl(sum(a.pago_tar),0) + nvl((SELECT sum(c.pago_tar) FROM tm_venta c WHERE c.id_tipo_pago = 3 AND c.id_apc = a.id_apc),0)
                 ELSE nvl(sum(a.pago_tar),0) END MONTO FROM tm_venta a, tm_tipo_pago b 
            WHERE a.id_tipo_pago = b.id_tipo_pago AND a.id_tipo_pago != 3 AND a.id_apc = {$data} GROUP BY a.id_tipo_pago")
            ->fetchAll(PDO::FETCH_OBJ);                

            $c->{'Glovo'} = $this->db->query("SELECT IFNULL(SUM(v.pago_efe + v.pago_tar),0) AS total, COUNT(*) AS cant FROM v_ventas_con AS v LEFT JOIN tm_pedido_delivery AS d ON v.id_ped = d.id_pedido WHERE v.id_apc = {$data} AND v.estado <> 'i' AND d.id_repartidor = 4444")
            ->fetch(PDO::FETCH_OBJ);

            $c->{'Rappi'} = $this->db->query("SELECT IFNULL(SUM(v.pago_efe + v.pago_tar),0) AS total, COUNT(*) AS cant FROM v_ventas_con AS v LEFT JOIN tm_pedido_delivery AS d ON v.id_ped = d.id_pedido WHERE v.id_apc = {$data} AND v.estado <> 'i' AND d.id_repartidor = 2222")
            ->fetch(PDO::FETCH_OBJ);

            $c->{'Ingresos'} = $this->db->query("SELECT IFNULL(SUM(importe),0) AS total FROM tm_ingresos_adm WHERE id_apc = {$data} AND estado='a'")
            ->fetch(PDO::FETCH_OBJ);
            
            $c->{'Egresos'} = $this->db->query("SELECT IFNULL(SUM(importe),0) AS total FROM v_gastosadm WHERE id_apc = {$data} AND (id_tg = 1 OR id_tg = 2 OR id_tg = 3 OR id_tg = 4) AND estado='a'")
            ->fetch(PDO::FETCH_OBJ);

            $c->{'EgresosA'} = $this->db->query("SELECT IFNULL(SUM(importe),0) AS total FROM v_gastosadm WHERE id_apc = {$data} AND id_tg = 1 AND estado='a'")
            ->fetch(PDO::FETCH_OBJ);

            $c->{'EgresosB'} = $this->db->query("SELECT IFNULL(SUM(importe),0) AS total FROM v_gastosadm WHERE id_apc = {$data} AND id_tg = 2 AND estado='a'")
            ->fetch(PDO::FETCH_OBJ);

            $c->{'EgresosC'} = $this->db->query("SELECT IFNULL(SUM(importe),0) AS total FROM v_gastosadm WHERE id_apc = {$data} AND id_tg = 3 AND estado='a'")
            ->fetch(PDO::FETCH_OBJ);

            $c->{'EgresosD'} = $this->db->query("SELECT IFNULL(SUM(importe),0) AS total FROM v_gastosadm WHERE id_apc = {$data} AND id_tg = 4 AND estado='a'")
            ->fetch(PDO::FETCH_OBJ);

            $c->{'Descuentos'} = $this->db->query("SELECT COUNT(id_ven) AS cant FROM v_ventas_con WHERE id_apc = {$data} AND desc_monto > '0.00' AND estado <> 'i'")
            ->fetch(PDO::FETCH_OBJ);

            $c->{'ComisionDelivery'} = $this->db->query("SELECT COUNT(id_ven) AS cant FROM v_ventas_con WHERE id_apc = {$data} AND id_tped = 3 AND estado <> 'i'")
            ->fetch(PDO::FETCH_OBJ);

            $c->{'Anulaciones'} = $this->db->query("SELECT COUNT(*) AS cant, SUM(pago_efe + pago_tar) total FROM tm_venta WHERE estado = 'i' AND id_apc = {$data}")
            ->fetch(PDO::FETCH_OBJ);

            $c->{'Deliverys'} = $this->db->query("SELECT SUM(IFNULL((v.pago_efe+v.pago_tar),0)) AS total, COUNT(*) AS cant FROM v_ventas_con AS v INNER JOIN tm_pedido_delivery AS d ON v.id_ped = d.id_pedido WHERE v.id_apc = {$data} AND v.estado = 'a'")
            ->fetch(PDO::FETCH_OBJ);

            $c->{'PollosVendidos'} = $this->db->query("SELECT p.id_pres,p.pro_nom,p.pro_pre,dv.precio,SUM(dv.cantidad) AS cantidad, i.cant FROM tm_detalle_venta AS dv INNER JOIN tm_venta AS v ON dv.id_venta = v.id_venta INNER JOIN v_productos AS p ON dv.id_prod = p.id_pres INNER JOIN tm_producto_ingr AS i ON dv.id_prod = i.id_pres WHERE v.id_apc = {$data} AND v.estado = 'a' AND i.id_ins = 1 AND p.pro_mar = 1 GROUP BY dv.id_prod, dv.precio ORDER BY total DESC")
                ->fetchAll(PDO::FETCH_OBJ);

            $c->{'PolloStock'} = $this->db->query("SELECT (ent-sal) AS total FROM v_stock WHERE id_tipo_ins = 1 AND id_ins = 1")
            ->fetch(PDO::FETCH_OBJ);
                   
            $c->{'Detalle'} = $this->db->query("SELECT d.id_prod,SUM(d.cantidad) AS cantidad, d.precio FROM tm_venta AS v INNER JOIN tm_detalle_venta AS d ON v.id_venta = d.id_venta WHERE v.id_apc = {$data} AND v.estado = 'a' GROUP BY d.id_prod, d.precio ORDER BY 2 DESC")
                ->fetchAll(PDO::FETCH_OBJ);
            foreach($c->Detalle as $k => $d)
            {
                $c->Detalle[$k]->{'Producto'} = $this->db->query("SELECT pro_nom, pro_pre FROM v_productos WHERE id_pres = " . $d->id_prod)
                    ->fetch(PDO::FETCH_OBJ);
            }

            return $c;
        }
        catch(Exception $e)
        {
            die($e->getMessage());
        }
    }

    public function finanza_adel_list_a($data)
    {
        try
        {
            $ifecha = date('Y-m-d H:i:s',strtotime($data['ifecha']));
            $ffecha = date('Y-m-d H:i:s',strtotime($data['ffecha']));
            $stm = $this->db->prepare("SELECT * FROM v_gastosadm WHERE (fecha_re >= ? AND fecha_re <= ?) AND id_per = ? AND id_tg = 3 AND estado = 'a'");
            $stm->execute(array($ifecha,$ffecha,$data['id_personal']));
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

    public function finanza_adel_list_b($data)
    {
        try
        {
            $ifecha = date('Y-m-d H:i:s',strtotime($data['ifecha']));
            $ffecha = date('Y-m-d H:i:s',strtotime($data['ffecha']));
            $stm = $this->db->prepare("SELECT *, (total + comis_del - desc_monto) AS total_venta FROM v_ventas_con WHERE (fec_ven >= ? AND fec_ven <= ?) AND desc_personal = ? AND desc_tipo = 3 AND estado = 'a'");
            $stm->execute(array($ifecha,$ffecha,$data['id_personal']));
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

    /* FIN MODULO FINANZAS */
   /* KARDEX VALORIZADO */
   public function kardex_list()
   {
       try
       {
           $tipo_ip = $_POST['tipo_ip'];
           $id_ip = $_POST['id_ip'];
           $ifecha = date('Y-m-d',strtotime($_POST['ifecha']));
           $ffecha = date('Y-m-d',strtotime($_POST['ffecha']));

           $stm = $this->db->prepare("SELECT id_inv,id_tipo_ope,id_ope,id_tipo_ins,id_ins,cos_uni,cant,fecha_r,estado,
                   IF(id_tipo_ope = 1 OR id_tipo_ope = 3,FORMAT(cant,6),0) AS cantidad_entrada, 
                   IF(id_tipo_ope = 1 OR id_tipo_ope = 3,cos_uni,0) AS costo_entrada, 
                   IF(id_tipo_ope = 1 OR id_tipo_ope = 3,(cant*cos_uni),0) AS total_entrada, 
                   IF(id_tipo_ope = 2 OR id_tipo_ope = 4,FORMAT(cant,6),0) AS cantidad_salida, 
                   IF(id_tipo_ope = 2 OR id_tipo_ope = 4,cos_uni,'-') AS costo_salida, 
                   IF(id_tipo_ope = 2 OR id_tipo_ope = 4,(cant*cos_uni),0) AS total_salida
               FROM tm_inventario WHERE id_tipo_ins = ? AND id_ins = ? AND (date(fecha_r) >= ? AND date(fecha_r) <= ?)");
           $stm->execute(array($tipo_ip,$id_ip,$ifecha,$ffecha));
           $c = $stm->fetchAll(PDO::FETCH_OBJ);
           foreach($c as $k => $d)
           {
               $c[$k]->{'Precio'} = $this->db->query("SELECT ROUND(AVG(cos_uni),2) AS cos_pro FROM tm_inventario WHERE id_tipo_ins = ".$d->id_tipo_ins." AND id_ins = ".$d->id_ins)
                   ->fetch(PDO::FETCH_OBJ);

               $c[$k]->{'Medida'} = $this->db->query("SELECT ins_med FROM v_insprod WHERE id_tipo_ins = ".$d->id_tipo_ins." AND id_ins = ".$d->id_ins)
                   ->fetch(PDO::FETCH_OBJ);

               $c[$k]->{'Stock'} = $this->db->query("SELECT SUM(ent - sal) AS total FROM v_stock WHERE id_tipo_ins = ".$tipo_ip." AND id_ins = ".$id_ip)
                   ->fetch(PDO::FETCH_OBJ);

               if($d->id_tipo_ope == 1){
                   $c[$k]->{'Comp'} = $this->db->query("SELECT serie_doc AS ser_doc,num_doc AS nro_doc,desc_td FROM v_compras WHERE id_compra = ".$d->id_ope)
                   ->fetch(PDO::FETCH_OBJ);
               } else if($d->id_tipo_ope == 2){
                   $c[$k]->{'Comp'} = $this->db->query("SELECT ser_doc,nro_doc,desc_td FROM v_ventas_con WHERE id_ven = ".$d->id_ope)
                   ->fetch(PDO::FETCH_OBJ);
               } else if($d->id_tipo_ope == 3 OR $d->id_tipo_ope == 4){
                   $c[$k]->{'Comp'} = $this->db->query("SELECT i.motivo, CONCAT(u.nombres,' ',u.ape_paterno,' ',u.ape_materno) AS responsable FROM tm_inventario_entsal AS i INNER JOIN tm_usuario AS u ON i.id_responsable = u.id_usu WHERE i.id_es = ".$d->id_ope)
                   ->fetch(PDO::FETCH_OBJ);
               }
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

   public function ComboInsumoProducto($data)
   {
       try
       {   
           $stmm = $this->db->prepare("SELECT id_ins,ins_cod,ins_nom,ins_cat FROM v_insprod WHERE id_tipo_ins = ? AND est_b = 'a' AND est_c = 'a'");
           $stmm->execute(array($data['id_tipo_ins']));
           $var = $stmm->fetchAll(PDO::FETCH_ASSOC);
           foreach($var as $v){
               echo '<option value="'.$v['id_ins'].'">'.$v['ins_cod'].' | '.$v['ins_cat'].' | '.$v['ins_nom'].'</option>';
           }
       }
       catch(Exception $e)
       {
           die($e->getMessage());
       }
   }



}