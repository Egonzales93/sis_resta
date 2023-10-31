<?php Session::init(); ?>
<?php

class Ajuste_Model extends Model
{
	public function __construct()
	{
		parent::__construct();
	}

    public function AreaProduccion()
    {
        try
        {      
            return $this->db->selectAll('SELECT * FROM tm_area_prod WHERE estado = "a"');
        }
        catch(Exception $e)
        {
            die($e->getMessage());
        }
    }

    public function Rol()
    {
        try
        {      
            return $this->db->selectAll('SELECT * FROM tm_rol WHERE id_rol <> 1');
        }
        catch(Exception $e)
        {
            die($e->getMessage());
        }
    }

    public function UnidadMedida()
    {
        try
        {      
            return $this->db->selectAll('SELECT * FROM tm_tipo_medida');
        }
        catch(Exception $e)
        {
            die($e->getMessage());
        }
    }

    public function Impresora()
    {
        try
        {      
            return $this->db->selectAll('SELECT * FROM tm_impresora WHERE estado = "a"');
        }
        catch(Exception $e)
        {
            die($e->getMessage());
        }
    }

    /* INICIO MODULO EMPRESA */

	public function datosempresa_data()
    {
        try
        {    
            $stm = $this->db->prepare("SELECT * FROM tm_empresa");
            $stm->execute();
            $c = $stm->fetch(PDO::FETCH_OBJ);
            return $c;
        }
        catch(Exception $e)
        {
            die($e->getMessage());
        }
    }

    public function datosempresa_crud($data)
    {
        try 
        {                 
            if($data['usuid'] == 1){

                if( !empty( $_FILES['imagen']['name'] ) ){
                    switch ($_FILES['imagen']['type']) 
                    { 
                        case 'image/jpeg': 
                        $ext = "jpg"; 
                        break;
                        case 'image/gif': 
                        $ext = "gif"; 
                        break; 
                        case 'image/png': 
                        $ext = "png"; 
                        break;
                    }
                    $imagen = 'logoprint.'.$ext;
                    move_uploaded_file ($_FILES['imagen']['tmp_name'], 'public/images/'.$imagen);
                    $data['logo'] =  $imagen;
                    } else {
                        $imagen = $data['imagen'];
        
                    }
                    /*
                    // subir certifico pfx 
                    if(!empty( $_FILES['file_certificado']['name'] )){
        
                        $directoriobeta         = 'api_fact/UBL21/archivos_xml_sunat/certificados/beta/'.$data['nit'];
                        $directorioproduccion   = 'api_fact/UBL21/archivos_xml_sunat/certificados/produccion/'.$data['nit'];
        
                        if (!file_exists($directorioproduccion)) {
                            mkdir($directorioproduccion, 0777, true);
                            $urlsproduccion = 'api_fact/UBL21/archivos_xml_sunat/certificados/produccion/'.$data['nit'].'/'.$data['nit'].'.pfx';
                            if(move_uploaded_file($_FILES['file_certificado']['tmp_name'], $urlsproduccion)){
                                $cpeproduccion ='api_fact/UBL21/archivos_xml_sunat/cpe_xml/produccion/'.$data['nit'];
                                    mkdir($cpeproduccion, 0777, true);
                                    mkdir($directoriobeta, 0777, true);
                                $urlsbeta = 'api_fact/UBL21/archivos_xml_sunat/certificados/beta/'.$data['nit'].'/'.$data['nit'].'.pfx';
                                if(copy($urlsproduccion, $urlsbeta)){
                                    $cpebeta ='api_fact/UBL21/archivos_xml_sunat/cpe_xml/beta/'.$data['nit'];
                                    mkdir($cpebeta, 0777, true);
                                }
                            }
                        }   
                    }
                    */
                $sql = "UPDATE tm_empresa SET nit = ?,razon_social  = ?, nombre_comercial = ?, direccion_comercial = ?, direccion_fiscal = ?, celular = ?, ubigeo = ?, departamento = ?, provincia = ?, distrito = ?, usuariosol = ?, clavesol = ?, clavecertificado = ?, client_id = ?, client_secret = ?, logo = ?, impuestos = ?, modo = ?";
                $this->db->prepare($sql)->execute(array(
                    $data['nit'],
                    $data['razon_social'],
                    $data['nombre_comercial'],
                    $data['direccion_comercial'],
                    $data['direccion_fiscal'],
                    $data['celular'],
                    $data['ubigeo'],
                    $data['departamento'],
                    $data['provincia'],
                    $data['distrito'],
                    $data['usuariosol'],
                    $data['clavesol'],
                    $data['clavecertificado'],
                    $data['client_id'],
                    $data['client_secret'],
                    $imagen,
                    $data['impuestos'],
                    $data['modo']
                ));
                //Session::set('sunat', $data['sunat']);
                Session::set('modo', $data['modo']);

                // return $_FILES['file_certificado']['type'];
            } else {
                $sql = "UPDATE tm_empresa SET nit = ?,razon_social  = ?, nombre_comercial = ?, direccion_comercial = ?, celular = ?";
                $this->db->prepare($sql)->execute(array(
                    $data['nit'],
                    $data['razon_social'],
                    $data['nombre_comercial'],
                    $data['direccion_comercial'],
                    $data['celular']
                ));
            }

        } catch (Exception $e) 
        {
            die($e->getMessage());
        }
    }

    public function tipodoc_list()
    {
        try
        {      
            $stm = $this->db->prepare("SELECT * FROM tm_tipo_doc");
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

    public function tipodoc_crud($data)
    {
        try 
        {
            $sql = "UPDATE tm_tipo_doc SET serie = ?, numero = ?, estado = ? WHERE id_tipo_doc = ?";
            $this->db->prepare($sql)->execute(array($data['serie'],$data['numero'],$data['estado'],$data['id_tipo_doc']));
        } catch (Exception $e) 
        {
            die($e->getMessage());
        }
    }

    public function usuario_list()
    {
        try
        {
            $stm = $this->db->prepare("SELECT * FROM v_usuarios WHERE id_rol <> 1");
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

    public function usuario_data($id)
    {
        return $this->db->selectOne('SELECT * FROM v_usuarios WHERE id_usu = :id_usu', 
            array('id_usu' => $id));
    }

    public function usuario_crud_create($data)
    {
        try 
        {
            if( !empty( $_FILES['imagen']['name'] ) ){
                switch ($_FILES['imagen']['type']) 
                { 
                    case 'image/jpeg': 
                    $ext = "jpg"; 
                    break;
                    case 'image/gif': 
                    $ext = "gif"; 
                    break; 
                    case 'image/png': 
                    $ext = "png"; 
                    break;
                    case 'application/pdf':
                    $ext = "pdf";
                    break;
                }
                $imagen = date('ymdhis').'.'.$ext;
                move_uploaded_file ($_FILES['imagen']['tmp_name'], 'public/images/
                
                s/'.$imagen);
            } else {
                $imagen = $data['imagen'];
            }

            $area = (isset($data['id_areap'])) ? $data['id_areap'] : 0;

            $consulta = "call usp_configUsuario( :flag, @a, :id_rol, :id_areap, :ci, :ape_paterno, :ape_materno, :nombres, :email, :usuario, :contrasena, :imagen);";
            $arrayParam =  array(
                ':flag' => 1,
                ':id_rol' => $data['id_rol'],
                ':id_areap' => $area,
                ':ci' => $data['ci'],
                ':ape_paterno' => $data['ape_paterno'],
                ':ape_materno' => $data['ape_materno'],
                ':nombres' => $data['nombres'],
                ':email' => $data['email'],
                ':usuario' => $data['usuario'],
                ':contrasena' => base64_encode($data['contrasena']),
                ':imagen' => $imagen
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

    public function usuario_crud_update($data)
    {
        try 
        {
            if( !empty( $_FILES['imagen']['name'] ) ){
                switch ($_FILES['imagen']['type']) 
                { 
                    case 'image/jpeg': 
                    $ext = "jpg"; 
                    break;
                    case 'image/gif': 
                    $ext = "gif"; 
                    break; 
                    case 'image/png': 
                    $ext = "png"; 
                    break;
                    case 'application/pdf':
                    $ext = "pdf";
                    break;
                }
                $imagen = date('ymdhis').'.'.$ext;
                move_uploaded_file ($_FILES['imagen']['tmp_name'], 'public/images/users'.$imagen);

            } else {
                $imagen = $data['imagen'];
            }

            $area = (isset($data['id_areap'])) ? $data['id_areap'] : 0;

            $consulta = "call usp_configUsuario( :flag, :id_usu, :id_rol, :id_areap, :ci, :ape_paterno, :ape_materno, :nombres, :email, :usuario, :contrasena, :imagen);";
            $arrayParam =  array(
                ':flag' => 2,
                ':id_usu' => $data['id_usu'],
                ':id_rol' => $data['id_rol'],
                ':id_areap' => $area,
                ':ci' => $data['ci'],
                ':ape_paterno' => $data['ape_paterno'],
                ':ape_materno' => $data['ape_materno'],
                ':nombres' => $data['nombres'],
                ':email' => $data['email'],
                ':usuario' => $data['usuario'],
                ':contrasena' => base64_encode($data['contrasena']),
                ':imagen' => $imagen
            );
            $st = $this->db->prepare($consulta);
            $st->execute($arrayParam);
        } catch (Exception $e) 
        {
            die($e->getMessage());
        }
    }

    public function usuario_estado($data)
    {
        try 
        {
            $sql = "UPDATE tm_usuario SET estado = ? WHERE id_usu = ?";
            $this->db->prepare($sql)
                ->execute(array($data['estado'],$data['id_usu']));
        } catch (Exception $e) 
        {
            die($e->getMessage());
        }
    }

    public function usuario_delete($data)
    {
        try 
        {
        if($data['id_rol'] == 1 OR $data['id_rol'] == 2){
            $consulta = "SELECT count(*) AS total FROM tm_pedido WHERE id_usu = ?";
        } else {
            $consulta = "SELECT count(*) AS total FROM tm_pedido_mesa WHERE id_mozo = ?";
        }
        $result = $this->db->prepare($consulta);
        $result->execute(array($data['id_usu']));
        $result->execute();
            if($result->fetchColumn()==0){
                $stm = $this->db->prepare("DELETE FROM tm_usuario WHERE id_usu = ?");          
                $stm->execute(array($data['id_usu']));
                return 1;
            }else{
                return 0;
            }
        } catch (Exception $e) 
        {
            die($e->getMessage());
        }
    }

    /* FIN MODULO EMPRESA */
    public function TipoPago()
    {
        try
        {      
            return $this->db->selectAll('SELECT * FROM tm_pago');
        }
        catch(Exception $e)
        {
            die($e->getMessage());
        }
    }
    public function tipopago_list($data)
    {
        try
        {
            $stm = $this->db->prepare("SELECT * FROM tm_tipo_pago WHERE id_tipo_pago LIKE ? AND id_tipo_pago > 3");
            $stm->execute(array($data['id_pago']));
            $c = $stm->fetchAll(PDO::FETCH_OBJ);
            foreach($c as $k => $d)
            {
                $c[$k]->{'Tipo'} = $this->db->query("SELECT descripcion AS nombre FROM tm_pago WHERE id_pago = ".$d->id_pago)
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

    public function tipopago_crud_create($data)
    {

        try
        {

            $sql = "INSERT INTO tm_tipo_pago SET id_pago = ? ,descripcion = ? ,estado = ?";
            $resultado = $this->db->prepare($sql)->execute(array($data['id_tipo_pago'],$data['nombre'],$data['estado']));


            if ($resultado){
                return '1';
            }
        } catch (Exception $e) 
        {
            die($e->getMessage());
        }
    }

    public function tipopago_crud_update($data)
    {
        try 
        {
            $sql = "UPDATE tm_tipo_pago SET id_pago = ?, descripcion = ?, estado = ? WHERE tm_tipo_pago.id_tipo_pago = ?";
            $resultado = $this->db->prepare($sql)->execute(array($data['id_tipo_pago'],$data['nombre'],$data['estado'],$data['id_pago']));

            if ($resultado){
                return '2';
            }
        } catch (Exception $e) 
        {
            die($e->getMessage());
        }
    }

    /* INICIO MODULO RESTAURANTE */

    public function caja_list()
    {
        try
        {
            $stm = $this->db->prepare("SELECT * FROM tm_caja");
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

    public function caja_crud_create($data)
    {
        try
        {
            $consulta = "call usp_configCajas( :flag, @a, :descripcion, :estado);";
            $arrayParam =  array(
                ':flag' => 1,
                ':descripcion' => $data['descripcion'],
                ':estado' => $data['estado']
            );
            $st = $this->db->prepare($consulta);
            $st->execute($arrayParam);
            while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
                return $row['cod'];
            }
        } catch (Exception $e) 
        {
            die($e->getMessage());
        }
    }

    public function caja_crud_update($data)
    {
        try 
        {
            $consulta = "call usp_configCajas( :flag, :id_caja, :descripcion, :estado);";
            $arrayParam =  array(
                ':flag' => 2,
                ':id_caja' => $data['id_caja'],
                ':descripcion' => $data['descripcion'],
                ':estado' => $data['estado']
            );
            $st = $this->db->prepare($consulta);
            $st->execute($arrayParam);
            while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
                return $row['cod'];
            }
        } catch (Exception $e) 
        {
            die($e->getMessage());
        }
    }

    public function areaprod_list($data)
    {
        try
        {
           
            $stm = $this->db->prepare("SELECT * FROM tm_area_prod WHERE id_areap like ?");
            $stm->execute(array($data['id_areap']));
            $c = $stm->fetchAll(PDO::FETCH_OBJ);
            foreach($c as $k => $d)
            {
                $c[$k]->{'Impresora'} = $this->db->query("SELECT nombre FROM tm_impresora WHERE id_imp = ".$d->id_imp)
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

    public function areaprod_crud_create($data)
    {
        try
        {
            $consulta = "call usp_configAreasProd( :flag, @a, :id_imp, :nombre, :estado);";
            $arrayParam =  array(
                ':flag' => 1,                
                ':id_imp' => $data['id_imp'],
                ':nombre' => $data['nombre'],
                ':estado' => $data['estado']
            );
            $st = $this->db->prepare($consulta);
            $st->execute($arrayParam);
            while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
                return $row['cod'];
            }
        } catch (Exception $e) 
        {
            die($e->getMessage());
        }
    }

    public function areaprod_crud_update($data)
    {
        try 
        {
            $consulta = "call usp_configAreasProd( :flag, :id_areap, :id_imp, :nombre, :estado);";
            $arrayParam =  array(
                ':flag' => 2,
                ':id_areap' => $data['id_areap'],
                ':id_imp' => $data['id_imp'],
                ':nombre' => $data['nombre'],
                ':estado' => $data['estado']                
            );
            $st = $this->db->prepare($consulta);
            $st->execute($arrayParam);
            while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
                return $row['cod'];
            }
        } catch (Exception $e) 
        {
            die($e->getMessage());
        }
    }

    public function salon_list()
    {
        try
        {
            $stm = $this->db->prepare("SELECT * FROM tm_salon ORDER BY id_salon ASC");
            $stm->execute();
            $c = $stm->fetchAll(PDO::FETCH_OBJ);
            foreach($c as $k => $d)
            {
                $c[$k]->{'Mesas'} = $this->db->query("SELECT COUNT(id_mesa) AS total FROM tm_mesa WHERE id_salon = ".$d->id_salon)
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

    public function mesa_list()
    {
        try
        {
            $stm = $this->db->prepare("SELECT * FROM tm_mesa WHERE id_salon like ? ORDER BY nro_mesa ASC");
            $stm->execute(array($_POST['id_salon']));
            $c = $stm->fetchAll(PDO::FETCH_OBJ);
            foreach($c as $k => $d)
            {
                $c[$k]->{'Salon'} = $this->db->query("SELECT descripcion FROM tm_salon WHERE id_salon = ".$d->id_salon)
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

    public function salon_crud_create($data)
    {
        try 
        {
            $consulta = "call usp_configSalones( :flag, @a, :descripcion, :estado);";
            $arrayParam =  array(
                ':flag' => 1,
                ':descripcion' => $data['descripcion'],
                ':estado' => $data['estado']
            );
            $st = $this->db->prepare($consulta);
            $st->execute($arrayParam);
            while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
                return $row['cod'];
            }
        }
        catch (Exception $e) 
        {
            return false;
        }
    }

    public function salon_crud_update($data)
    {
        try 
        {
            $consulta = "call usp_configSalones( :flag, :id_salon, :descripcion, :estado);";
            $arrayParam =  array(
                ':flag' => 2,
                ':id_salon' => $data['id_salon'],
                ':descripcion' => $data['descripcion'],
                ':estado' => $data['estado']
            );
            $st = $this->db->prepare($consulta);
            $st->execute($arrayParam);
            while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
                return $row['cod'];
            }
        }
        catch (Exception $e) 
        {
            return false;
        }
    }

    public function salon_crud_delete($data)
    {
        try 
        {
            $consulta = "call usp_configSalones( :flag, :id_salon, @a, @b);";
            $arrayParam =  array(
                ':flag' => 3,
                ':id_salon' => $data['id_salon']
            );
            $st = $this->db->prepare($consulta);
            $st->execute($arrayParam);
            while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
                return $row['cod'];
            }
        } catch (Exception $e) 
        {
            die($e->getMessage());
        }
    }

    public function mesa_crud_create($data)
    {
        try 
        {
            $consulta = "call usp_configMesas( :flag, @a, :id_salon, :nro_mesa, @b);";
            $arrayParam =  array(
                ':flag' => 1,
                ':id_salon' => $data['id_salon'],
                ':nro_mesa' => $data['nro_mesa']
            );
            $st = $this->db->prepare($consulta);
            $st->execute($arrayParam);
            while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
                return $row['cod'];
            }
        }
        catch (Exception $e) 
        {
            return false;
        }
    }

    public function mesa_crud_update($data)
    {
        try 
        {
            $consulta = "call usp_configMesas( :flag, :id_mesa, :id_salon, :nro_mesa, :estado);";
            $arrayParam =  array(
                ':flag' => 2,
                ':id_mesa' => $data['id_mesa'],
                ':id_salon' => $data['id_salon'],
                ':nro_mesa' => $data['nro_mesa'],
                ':estado' => $data['estado']                        
            );
            $st = $this->db->prepare($consulta);
            $st->execute($arrayParam);
            while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
                return $row['cod'];
            }
        }
        catch (Exception $e) 
        {
            return false;
        }
    }

    public function mesa_crud_delete($data)
    {
        try 
        {
            $consulta = "call usp_configMesas( :flag, :id_mesa, @a, @b, @c);";
            $arrayParam =  array(
                ':flag' => 3,
                ':id_mesa' => $data['id_mesa']
            );
            $st = $this->db->prepare($consulta);
            $st->execute($arrayParam);
            while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
                return $row['cod'];
            }
        } catch (Exception $e) 
        {
            die($e->getMessage());
        }
    }

    /*
    public function mesa_estado($data)
    {
        try 
        {
            $sql = "UPDATE tm_mesa SET estado = ? WHERE id_mesa = ?";
            $this->db->prepare($sql)->execute(array($data['est_mesa'],$data['codi_mesa']));    
        } catch (Exception $e) 
        {
            die($e->getMessage());
        }
    }
    */

    /* ===================================== PRODUCTO*/
    public function producto_list()
    {
        try
        {
            $stm = $this->db->prepare("SELECT * FROM tm_producto WHERE id_prod like ? AND id_catg like ? AND id_catg <> 1 ORDER BY id_prod DESC");
            $stm->execute(array($_POST['id_prod'],$_POST['id_catg']));
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

    public function producto_pres_list()
    {
        try
        {
            $stm = $this->db->prepare("SELECT * FROM tm_producto_pres WHERE id_prod LIKE ? AND id_pres LIKE ?");
            $stm->execute(array($_POST['id_prod'],$_POST['id_pres']));
            $c = $stm->fetchAll(PDO::FETCH_OBJ);
            foreach($c as $k => $d)
            {
                $c[$k]->{'TipoProd'} = $this->db->query("SELECT id_tipo FROM tm_producto WHERE id_prod = ".$d->id_prod)
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

    public function producto_cat_list()
    {
        try
        {
            $stm = $this->db->prepare("SELECT * FROM tm_producto_catg WHERE id_catg <> 1 ORDER BY orden ASC");
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

    public function producto_pres_ing($data)
    {
        try
        {
            $stm = $this->db->prepare("SELECT * FROM tm_producto_ingr WHERE id_pres = ?");
            $stm->execute(array($data['id_pres']));
            $c = $stm->fetchAll(PDO::FETCH_OBJ);
            foreach($c as $k => $d)
            {
                $c[$k]->{'Insumo'} = $this->db->query("SELECT ins_med,ins_nom,ins_cat FROM v_insprod WHERE id_tipo_ins = ".$d->id_tipo_ins." AND id_ins = ".$d->id_ins)
                ->fetch(PDO::FETCH_OBJ);
            }
            foreach($c as $k => $d)
            {
                $c[$k]->{'Medida'} = $this->db->query("SELECT descripcion FROM tm_tipo_medida WHERE id_med = ".$d->id_med)
                ->fetch(PDO::FETCH_OBJ);
            }
            return $c;
        }
        catch(Exception $e)
        {
            die($e->getMessage());
        }
    }

    public function producto_buscar_ins($data)
    {
        try
        {        
            $cadena = $data['cadena'];
            $tipo = $data['tipo'];
            $stm = $this->db->prepare("SELECT * FROM v_insprod WHERE (ins_nom LIKE '%$cadena%' OR ins_cod LIKE '%$cadena%') AND est_b = 'a' AND est_c = 'a' AND id_tipo_ins <> ".$tipo." ORDER BY ins_nom LIMIT 5");
            $stm->execute();
            return $stm->fetchAll(PDO::FETCH_OBJ);
        }
        catch(Exception $e)
        {
            die($e->getMessage());
        }
    }

    public function producto_cat_crud_create($data)
    {
        try 
        {
            if( !empty( $_FILES['imagen']['name'] ) ){
                switch ($_FILES['imagen']['type']) 
                { 
                    case 'image/jpeg': 
                    $ext = "jpg"; 
                    break;
                    case 'image/gif': 
                    $ext = "gif"; 
                    break; 
                    case 'image/png': 
                    $ext = "png"; 
                    break;
                    case 'application/pdf':
                    $ext = "pdf";
                    break;
                }
                $imagen = date('ymdhis').'.'.$ext;
                move_uploaded_file ($_FILES['imagen']['tmp_name'], 'public/images/productos/'.$imagen);
            } else {
                $imagen = $data['imagen'];
            }
            $consulta = "call usp_configProductoCatgs( :flag, @a, :descripcion, :delivery, :orden, :imagen, :estado);";
            $arrayParam =  array(
                ':flag' => 1,
                ':descripcion' => $data['descripcion_categoria'],
                ':delivery' => $data['hidden_delivery_categoria'],
                ':orden' => 100,
                ':imagen' => $imagen,
                ':estado' => $data['hidden_estado_categoria']
            );
            $st = $this->db->prepare($consulta);
            $st->execute($arrayParam);
            while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
                return $row['cod'];
            }
        }
        catch (Exception $e) 
        {
            return false;
        }
    }

    public function producto_cat_crud_update($data)
    {
        try 
        {
            if( !empty( $_FILES['imagen']['name'] ) ){
                switch ($_FILES['imagen']['type']) 
                { 
                    case 'image/jpeg': 
                    $ext = "jpg"; 
                    break;
                    case 'image/gif': 
                    $ext = "gif"; 
                    break; 
                    case 'image/png': 
                    $ext = "png"; 
                    break;
                    case 'application/pdf':
                    $ext = "pdf";
                    break;
                }
                $imagen = date('ymdhis').'.'.$ext;
                move_uploaded_file ($_FILES['imagen']['tmp_name'], 'public/images/productos/'.$imagen);
            } else {
                $imagen = $data['imagen'];
            }
            $consulta = "call usp_configProductoCatgs( :flag, :id_catg, :descripcion, :delivery, :orden, :imagen, :estado);";
            $arrayParam =  array(
                ':flag' => 2,
                ':id_catg' => $data['id_catg_categoria'],
                ':descripcion' => $data['descripcion_categoria'],
                ':delivery' => $data['hidden_delivery_categoria'],
                ':orden' => $data['orden_categoria'],
                ':imagen' => $imagen,
                ':estado' => $data['hidden_estado_categoria']
            );
            $st = $this->db->prepare($consulta);
            $st->execute($arrayParam);
            while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
                return $row['cod'];
            }
        }
        catch (Exception $e) 
        {
            return false;
        }
    }

    public function producto_crud_create($data)
    {
        try
        {
            $consulta = "call usp_configProducto( :flag, @a, :id_tipo, :id_catg, :id_areap, :nombre, :notas, :delivery, @b);";
            $arrayParam =  array(
                ':flag' => 1,
                ':id_tipo' => $data['id_tipo'],
                ':id_catg' => $data['id_catg'],
                ':id_areap' => $data['id_areap'],
                ':nombre' => $data['nombre'],
                ':notas' => $data['notas'],
                ':delivery' => $data['delivery']
            );
            $st = $this->db->prepare($consulta);
            $st->execute($arrayParam);
            while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
                return $row['cod'];
            }
        } catch (Exception $e) 
        {
            die($e->getMessage());
        }
    }

    public function producto_crud_update($data)
    {
        try 
        {
            $consulta = "call usp_configProducto( :flag, :id_prod, :id_tipo, :id_catg, :id_areap, :nombre, :notas, :delivery, :estado);";
            $arrayParam =  array(
                ':flag' => 2,
                ':id_prod' => $data['id_prod'],
                ':id_tipo' => $data['id_tipo'],
                ':id_catg' => $data['id_catg'],
                ':id_areap' => $data['id_areap'],
                ':nombre' => $data['nombre'],
                ':notas' => $data['notas'],
                ':delivery' => $data['delivery'],
                ':estado' => $data['estado']
            );
            $st = $this->db->prepare($consulta);
            $st->execute($arrayParam);
            while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
                return $row['cod'];
            }
        } catch (Exception $e) 
        {
            die($e->getMessage());
        }
    }

    public function producto_pres_crud_create($data)
    {
        try 
        {
            if( !empty( $_FILES['imagen']['name'] ) ){
                switch ($_FILES['imagen']['type']) 
                { 
                    case 'image/jpeg': 
                    $ext = "jpg"; 
                    break;
                    case 'image/gif': 
                    $ext = "gif"; 
                    break; 
                    case 'image/png': 
                    $ext = "png"; 
                    break;
                    case 'application/pdf':
                    $ext = "pdf";
                    break;
                }
                $imagen = date('ymdhis').'.'.$ext;
                move_uploaded_file ($_FILES['imagen']['tmp_name'], 'public/images/productos/'.$imagen);
            } else {
                $imagen = $data['imagen'];
            }
            $consulta = "call usp_configProductoPres( :flag, @a, :id_prod, :cod_prod, :presentacion, :descripcion, :precio, :precio_delivery, :receta, :stock_min,:stock_limit, :impuesto, :delivery, :margen, :iva, :imagen, :estado);";
            $arrayParam =  array(
                ':flag' => 1,
                ':id_prod' => $data['id_prod_presentacion'],
                ':cod_prod' => $data['cod_prod_presentacion'],
                ':presentacion' => $data['presentacion_presentacion'],
                ':descripcion' => $data['descripcion_presentacion'],
                ':precio' => $data['precio_presentacion'],
                ':precio_delivery' => $data['precio_delivery'] ? $data['precio_delivery'] : "0",
                ':receta' => $data['hidden_receta_presentacion'],
                ':stock_min' => $data['stock_min_presentacion'],
                ':stock_limit' => $data['hidden_stock_limit'],
                ':impuesto' => $data['hidden_impuesto_presentacion'],
                ':delivery' => $data['hidden_delivery_presentacion'],
                ':margen' => $data['hidden_insumo_principal_presentacion'],
                ':iva' => Session::get('iva'),
                ':imagen' => $imagen,
                ':estado' => $data['hidden_estado_presentacion']
            );
            $st = $this->db->prepare($consulta);
            $st->execute($arrayParam);

            $id_registro            = $this->db->lastInsertId();
            while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
                return $row['cod'];
            }
        }
        catch (Exception $e) 
        {
            return false;
        }
    }

    public function producto_pres_crud_update($data)
    {
        try 
        {
            if( !empty( $_FILES['imagen']['name'] ) ){
                switch ($_FILES['imagen']['type']) 
                { 
                    case 'image/jpeg': 
                    $ext = "jpg"; 
                    break;
                    case 'image/gif': 
                    $ext = "gif"; 
                    break; 
                    case 'image/png': 
                    $ext = "png"; 
                    break;
                    case 'application/pdf':
                    $ext = "pdf";
                    break;
                }
                $imagen = date('ymdhis').'.'.$ext;
                move_uploaded_file ($_FILES['imagen']['tmp_name'],'public/images/productos/'.$imagen);
            } else {
                $imagen = $data['imagen'];
            }
            $consulta = "call usp_configProductoPres( :flag, :id_pres, :id_prod, :cod_prod, :presentacion, :descripcion, :precio, :precio_delivery, :receta, :stock_min,:stock_limit, :impuesto, :delivery, :margen, :iva, :imagen, :estado);";
            $arrayParam =  array(
                ':flag' => 2,
                ':id_pres' => $data['id_pres_presentacion'],
                ':id_prod' => $data['id_prod_presentacion'],
                ':cod_prod' => $data['cod_prod_presentacion'],
                ':presentacion' => $data['presentacion_presentacion'],
                ':descripcion' => $data['descripcion_presentacion'],
                ':precio' => $data['precio_presentacion'],
                ':precio_delivery' => $data['precio_delivery'],
                ':receta' => $data['hidden_receta_presentacion'],
                ':stock_min' => $data['stock_min_presentacion'],
                ':stock_limit' => $data['hidden_stock_limit'],
                ':impuesto' => $data['hidden_impuesto_presentacion'],
                ':delivery' => $data['hidden_delivery_presentacion'],
                ':margen' => $data['hidden_insumo_principal_presentacion'],
                ':iva' => Session::get('iva'),
                ':imagen' => $imagen,
                ':estado' => $data['hidden_estado_presentacion']
            );
            $st = $this->db->prepare($consulta);
            $st->execute($arrayParam);
            while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
                return $row['cod'];
            }
        }
        catch (Exception $e) 
        {
            return false;
        }
    }

    public function producto_combo_cat()
    {
        try
        {
            $stm = $this->db->prepare("SELECT * FROM tm_producto_catg WHERE id_catg <> 1");
            $stm->execute();
            $c = $stm->fetchAll(PDO::FETCH_OBJ);
            return $c;
        }
        catch(Exception $e)
        {
            die($e->getMessage());
        }
    }

    public function producto_combo_unimed($data)
    {
        try
        {   
            $stmm = $this->db->prepare("SELECT * FROM tm_tipo_medida WHERE grupo = ? OR grupo = ?");
            $stmm->execute(array($data['va1'],$data['va2']));
            $var = $stmm->fetchAll(PDO::FETCH_ASSOC);
            foreach($var as $v){
                echo '<option value="'.$v['id_med'].'">'.$v['descripcion'].'</option>';
            }
        }
        catch(Exception $e)
        {
            die($e->getMessage());
        }
    }

    public function producto_ingrediente_create($data)
    {
        try 
        {          
            $consulta = "call usp_configProductoIngrs( :flag, @a, :id_pres, :id_tipo_ins, :id_ins, :id_med, :cant);";
            $arrayParam =  array(
                ':flag' => 1,
                ':id_pres' => $data['id_pres'],
                ':id_tipo_ins' => $data['id_tipo_ins'],
                ':id_ins' => $data['id_ins'],
                ':id_med' => $data['id_med'],
                ':cant' => $data['cant']
            );
            $st = $this->db->prepare($consulta);
            $st->execute($arrayParam);
        }
        catch (Exception $e) 
        {
            return false;
        }
    }

    /*
    public function producto_ingrediente_update($data)
    {
        try 
        {
            $consulta = "call usp_configProductoIngrs( :flag, :idPres, :idIns, :cant, :idPi);";
            $arrayParam =  array(
                ':flag' => 2,
                ':idPres' => 1,
                ':idIns' => 1,
                ':cant' => $data['cant'],
                ':idPi' => $data['cod'],
            );
            $st = $this->db->prepare($consulta);
            $st->execute($arrayParam);
        }
        catch (Exception $e) 
        {
            return false;
        }
    }
    */

    public function producto_ingrediente_delete($data)
    {
        try 
        {
            $consulta = "call usp_configProductoIngrs( :flag, :id_pi, @a, @b, @c, @d, @e);";
            $arrayParam =  array(
                ':flag' => 3,
                ':id_pi' => $data['id_pi']
            );
            $st = $this->db->prepare($consulta);
            $st->execute($arrayParam);
        }
        catch (Exception $e) 
        {
            return false;
        }
    }

    public function producto_cat_delete($data)
    {
        try 
        {
            $consulta = "call usp_configEliminarCategoriaProd(:id_catg);";
            $arrayParam =  array(
                ':id_catg' => $data['id_catg']
            );
            $st = $this->db->prepare($consulta);
            $st->execute($arrayParam);
            while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
                return $row['cod'];
            }
        } catch (Exception $e) 
        {
            die($e->getMessage());
        }
    }
    public function producto_prod_delete($data)
    {
        try 
        {
            $consulta = "DELETE FROM tm_producto WHERE id_prod = ?;";
            $st = $this->db->prepare($consulta);
            $st->execute(array($data['id_prod']));
            $st->fetch(PDO::FETCH_ASSOC);
            if($st->rowCount()){
                return 1;
            }else{
                return 0;
            }
        } catch (Exception $e) 
        {
            die($e->getMessage());
        }
    }

    public function producto_pres_delete($data)
    {
        try 
        {
            // DELETE FROM tm_producto_pres WHERE tm_producto_pres.id_pres = 2
            $consulta = "DELETE FROM tm_producto_pres WHERE id_pres = ?";
            $st = $this->db->prepare($consulta);
            $st->execute(array($data['id_pres']));
            $st->fetch(PDO::FETCH_ASSOC);
            if($st->rowCount()){
                return 1;
            }else{
                return 0;
            }
        } catch (Exception $e) 
        {
            die($e->getMessage());
        }
    }
        /* ======================= FIN PRODUCTO */

    /* ======================= INCIO COMBO */
    public function combo_list()
    {
        try
        {
            $stm = $this->db->prepare("SELECT * FROM tm_producto WHERE id_prod like ? AND id_catg = 1 ORDER BY id_prod DESC");
            $stm->execute(array($_POST['id_prod']));
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

    /* ======================= FIN COMBO */

    /* ======================= INICIO INSUMO */

    public function insumo_cat_list()
    {
        try
        {
            $stm = $this->db->prepare("SELECT * FROM tm_insumo_catg");
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

    public function insumo_list()
    {
        try
        {
            $stm = $this->db->prepare("SELECT * FROM v_insumos WHERE id_ins like ? AND id_catg like ? ORDER BY id_ins DESC");
            $stm->execute(array($_POST['id_ins'],$_POST['id_catg']));
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

    public function insumo_combo_cat()
    {
        try
        {
            $stm = $this->db->prepare("SELECT * FROM tm_insumo_catg");
            $stm->execute();
            $c = $stm->fetchAll(PDO::FETCH_OBJ);
            return $c;
        }
        catch(Exception $e)
        {
            die($e->getMessage());
        }
    }

    public function insumo_cat_crud_create($data)
    {
        try 
        {
            $consulta = "call usp_configInsumoCatgs( :flag, :descC, @a);";
            $arrayParam =  array(
                ':flag' => 1,
                ':descC' => $data['descripcion']
            );
            $st = $this->db->prepare($consulta);
            $st->execute($arrayParam);
            while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
                return $row['cod'];
            }
        }
        catch (Exception $e) 
        {
            return false;
        }
    }

    public function insumo_cat_crud_update($data)
    {
        try 
        {
            $consulta = "call usp_configInsumoCatgs( :flag, :descC, :idCatg);";
            $arrayParam =  array(
                ':flag' => 2,
                ':descC' => $data['descripcion'],
                ':idCatg' => $data['id_catg']
            );
            $st = $this->db->prepare($consulta);
            $st->execute($arrayParam);
            while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
                return $row['cod'];
            }
        }
        catch (Exception $e) 
        {
            return false;
        }
    }

    public function insumo_crud_create($data)
    {
        try
        {
            $consulta = "call usp_configInsumo( :flag, :idCatg, :idMed, :cod, :nombre, :stock, :costo, @a, @b);";
            $arrayParam =  array(
                ':flag' => 1,
                ':idCatg' => $data['id_catg'],
                ':idMed' => $data['id_med'],
                ':cod' => $data['cod_ins'],
                ':nombre' => $data['nomb_ins'],
                ':stock' => $data['stock_min'],
                ':costo' => $data['cos_uni']
            );
            $st = $this->db->prepare($consulta);
            $st->execute($arrayParam);
            while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
                return $row['cod'];
            }
        } catch (Exception $e) 
        {
            die($e->getMessage());
        }
    }

    public function insumo_crud_update($data)
    {
        try 
        {
            $consulta = "call usp_configInsumo( :flag, :idCatg, :idMed, :cod, :nombre, :stock, :costo, :estado, :idIns);";
            $arrayParam =  array(
                ':flag' => 2,
                ':idCatg' => $data['id_catg'],
                ':idMed' => $data['id_med'],
                ':cod' => $data['cod_ins'],
                ':nombre' => $data['nomb_ins'],
                ':stock' => $data['stock_min'],
                ':costo' => $data['cos_uni'],
                ':estado' => $data['estado'],
                ':idIns' => $data['id_ins']
            );
            $st = $this->db->prepare($consulta);
            $st->execute($arrayParam);
            while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
                return $row['cod'];
            }
        } catch (Exception $e) 
        {
            die($e->getMessage());
        }
    }

    public function insumo_cat_delete($data)
    {
        try 
        {
            $consulta = "call usp_configEliminarCategoriaIns(:id_catg);";
            $arrayParam =  array(
                ':id_catg' => $data['id_catg']
            );
            $st = $this->db->prepare($consulta);
            $st->execute($arrayParam);
            while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
                return $row['cod'];
            }
        } catch (Exception $e) 
        {
            die($e->getMessage());
        }
    }

    public function print_list($data)
    {
        try
        {
           
            $stm = $this->db->prepare("SELECT * FROM tm_impresora WHERE id_imp <> 1 AND id_imp LIKE ?");
            $stm->execute(array($data['id_imp']));
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

    public function print_crud_create($data)
    {
        try
        {
            $query = "INSERT INTO `tm_impresora` (`id_imp`, `nombre`, `ip`, `estado`) 
                      VALUES (NULL, :nombre, :ip, :estado)";

            //$consulta = "call usp_configImpresoras( :flag, @a, :nombre, :estado);";
            // $arrayParam =  array(
            //     ':flag' => 1,
            //     ':nombre' => $data['nombre'],
            //     ':ip' => $data['ip'],
            //     ':estado' => $data['estado']
            // );

            $st = $this->db->prepare($query);
            $st->bindParam(':nombre', $data['nombre'], PDO::PARAM_STR);
            $st->bindParam(':ip', $data['ip'], PDO::PARAM_STR);
            $st->bindParam(':estado', $data['estado'], PDO::PARAM_STR);
            $success = $st->execute();

            if ($success && $st->errorInfo()[0] === '00000') {
                return 1;
            } else {
                return 0;
            }

            //$st->execute($arrayParam);
            
            //return $cod = $this->db->lastInsertId();
            //while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
                //return $row['cod'];
            //}

        } catch (Exception $e) 
        {
            die($e->getMessage());
        }
    }

    public function print_crud_update($data)
    {
        try 
        {
            $query = "UPDATE `tm_impresora` SET `nombre` = :nombre, `ip`= :ip, `estado` = :estado 
                      WHERE `id_imp` = :id_imp";

            $st = $this->db->prepare($query);
            $st->bindParam(':nombre', $data['nombre'], PDO::PARAM_STR);
            $st->bindParam(':ip', $data['ip'], PDO::PARAM_STR);
            $st->bindParam(':estado', $data['estado'], PDO::PARAM_STR);
            $st->bindParam(':id_imp', $data['id_imp'], PDO::PARAM_INT);
            $success = $st->execute();

            if ($success && $st->errorInfo()[0] === '00000') {
                return 2;
            } else {
                return 0;
            }

            /*
            $consulta = "call usp_configImpresoras( :flag, :id_imp, :nombre, :estado);";
            $arrayParam =  array(
                ':flag' => 2,
                ':id_imp' => $data['id_imp'],
                ':nombre' => $data['nombre'],
                ':estado' => $data['estado']
            );
            $st = $this->db->prepare($consulta);
            $st->execute($arrayParam);
            while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
                return $row['cod'];
            }*/
        } catch (Exception $e) 
        {
            die($e->getMessage());
        }
    }

    /* ======================= FIN INSUMO */

    /* FIN MODULO RESTAURANTE */

    public function optimizar_pedidos()
    {
        try
        {
            $consulta = "call usp_optPedidos(:flag);";
            $arrayParam =  array(
                ':flag' => 1
            );
            $st = $this->db->prepare($consulta);
            $st->execute($arrayParam);
            while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
                return $row['cod'];
            }
        } catch (Exception $e) 
        {
            die($e->getMessage());
        }
    }

    public function optimizar_ventas()
    {
        try
        {
            $consulta = "call usp_optPedidos(:flag);";
            $arrayParam =  array(
                ':flag' => 2
            );
            $st = $this->db->prepare($consulta);
            $st->execute($arrayParam);
            while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
                return $row['cod'];
            }
        } catch (Exception $e) 
        {
            die($e->getMessage());
        }
    }

    public function optimizar_productos()
    {
        try
        {
            $consulta = "call usp_optPedidos(:flag);";
            $arrayParam =  array(
                ':flag' => 3
            );
            $st = $this->db->prepare($consulta);
            $st->execute($arrayParam);
            while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
                return $row['cod'];
            }
        } catch (Exception $e) 
        {
            die($e->getMessage());
        }
    }

    public function optimizar_insumos()
    {
        try
        {
            $consulta = "call usp_optPedidos(:flag);";
            $arrayParam =  array(
                ':flag' => 4
            );
            $st = $this->db->prepare($consulta);
            $st->execute($arrayParam);
            while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
                return $row['cod'];
            }
        } catch (Exception $e) 
        {
            die($e->getMessage());
        }
    }

    public function optimizar_clientes()
    {
        try
        {
            $consulta = "call usp_optPedidos(:flag);";
            $arrayParam =  array(
                ':flag' => 5
            );
            $st = $this->db->prepare($consulta);
            $st->execute($arrayParam);
            while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
                return $row['cod'];
            }
        } catch (Exception $e) 
        {
            die($e->getMessage());
        }
    }

    public function optimizar_proveedores()
    {
        try
        {
            $consulta = "call usp_optPedidos(:flag);";
            $arrayParam =  array(
                ':flag' => 6
            );
            $st = $this->db->prepare($consulta);
            $st->execute($arrayParam);
            while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
                return $row['cod'];
            }
        } catch (Exception $e) 
        {
            die($e->getMessage());
        }
    }

    public function optimizar_mesas()
    {
        try
        {
            $consulta = "call usp_optPedidos(:flag);";
            $arrayParam =  array(
                ':flag' => 7
            );
            $st = $this->db->prepare($consulta);
            $st->execute($arrayParam);
            while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
                return $row['cod'];
            }
        } catch (Exception $e) 
        {
            die($e->getMessage());
        }
    }

    public function datosistema_data()
    {
        try
        {    
            $stm = $this->db->prepare("SELECT * FROM tm_configuracion");
            $stm->execute();
            $c = $stm->fetch(PDO::FETCH_OBJ);
            return $c;
        }
        catch(Exception $e)
        {
            die($e->getMessage());
        }
    }

    public function datosistema_crud($data)
    {
        try 
        {
            $sql = "UPDATE tm_configuracion SET zona_hora = ?,trib_acr = ?,trib_car = ?,di_acr = ?,di_car = ?,imp_acr = ?,imp_val = ?,mon_acr = ?,mon_val = ?,pc_name = ?,pc_ip = ?,print_com = ?,print_pre = ?,print_cpe = ?,cod_seg = ?, opc_01 = ?";
            $this->db->prepare($sql)->execute(array($data['zona_hora'],$data['trib_acr'],$data['trib_car'],$data['di_acr'],$data['di_car'],$data['imp_acr'],$data['imp_val'],$data['mon_acr'],$data['mon_val'],$data['pc_name'],$data['pc_ip'],$data['print_com'],$data['print_pre'],$data['print_cpe'],$data['cod_seg'],$data['opc_01']));

            /* ACTUALIZAR DATOS */
            Session::set('moneda', $data['mon_val']);
            Session::set('iva', $data['imp_val']);
            Session::set('tribAcr', $data['trib_acr']);
            Session::set('tribCar', $data['trib_car']);
            Session::set('diAcr', $data['di_acr']);
            Session::set('diCar', $data['di_car']);
            Session::set('impAcr', $data['imp_acr']);
            Session::set('monAcr', $data['mon_acr']);
            Session::set('zona_hor', $data['zona_hora']);
            Session::set('pc_name', $data['pc_name']);
            Session::set('pc_ip', $data['pc_ip']);
            Session::set('print_com', $data['print_com']);
            Session::set('print_pre', $data['print_pre']);
            Session::set('print_cpe', $data['print_cpe']); //funcion impresion directa 
            Session::set('cod_seg', $data['cod_seg']); //funcion codigo de seguridad 
            Session::set('opc_01', $data['opc_01']); //funcion codigo de seguridad 

        } catch (Exception $e) 
        {
            die($e->getMessage());
        }
    }

    public function anularlogo()
    {
        try
        {    
            $stm = $this->db->prepare("UPDATE tm_empresa SET logo = NULL WHERE tm_empresa.id_de = 1");
            $stm->execute();
            // return $c;
            if($stm){
                return '1';
            }
        }
        catch(Exception $e)
        {
            die($e->getMessage());
        }
    }

    public function bloqueoplataforma($data)
    {
        try
        {    

            // id_bloqueo: 2109172516
            // UPDATE tm_configuracion SET bloqueo = '0' WHERE tm_configuracion.id_cfg = 1;
            // $sql = "UPDATE tm_configuracion SET zona_hora = ?,trib_acr = ?,trib_car = ?,di_acr = ?,di_car = ?,imp_acr = ?,imp_val = ?,mon_acr = ?,mon_val = ?,pc_name = ?,pc_ip = ?,print_com = ?,print_pre = ?,print_cpe = ?";
            // $this->db->prepare($sql)->execute();




            $stm = $this->db->prepare("UPDATE tm_configuracion SET bloqueo = ? WHERE tm_configuracion.id_cfg = '1' ");
            $stm->execute(array($data['tipo_bloqueo']));
            // return $c;
            if($stm){
                // Session::set('moneda', $data['mon_val']);
                Session::set('bloqueo_id', $data['tipo_bloqueo']); 
                return '1';
            }
        }
        catch(Exception $e)
        {
            die($e->getMessage());
        }
    }
    // bloqueoplataforma

    public function importarexcel()
    {   
        try {
            error_reporting(0);
            include "libs/class.upload.php";
            if(isset($_FILES["file"])){
                $up = new Upload($_FILES["file"]);
                if($up->uploaded){
                    $up->Process("libs/uploads/");
                    if($up->processed){
                        /// leer el archivo excel
                        // require_once "../libs/PHPExcel/Classes/PHPExcel.php";
                        require_once "libs/PHPExcel/Classes/PHPExcel.php";

                        $archivo = "libs/uploads/".$up->file_dst_name;
                        $inputFileType = PHPExcel_IOFactory::identify($archivo);
                        $objReader = PHPExcel_IOFactory::createReader($inputFileType);
                        $objPHPExcel = $objReader->load($archivo);
                        $sheet = $objPHPExcel->getSheet(0); 
                        $highestRow = $sheet->getHighestRow(); 
                        $highestColumn = $sheet->getHighestColumn();
                        for ($row = 2; $row <= $highestRow; $row++){ 
                            $categoria      = $sheet->getCell("A".$row)->getValue();
                            $x_producto     = $sheet->getCell("B".$row)->getValue();
                            $x_cod_pro      = $sheet->getCell("C".$row)->getValue();
                            $x_area         = $sheet->getCell("D".$row)->getValue();
                            $x_trasformado  = $sheet->getCell("E".$row)->getValue();
                            $x_notas        = $sheet->getCell("F".$row)->getValue();
                            $x_desc         = $sheet->getCell("G".$row)->getValue();
                            $x_delivery     = $sheet->getCell("H".$row)->getValue();
            
                            if($categoria){

            
                                $sql_categoria = $this->db->selectOne("SELECT * FROM tm_producto_catg WHERE descripcion LIKE '".$categoria."' ");
                                       
                                if ($sql_categoria) {
                                    $id_catg = $sql_categoria['id_catg'];
                                }
                                else{
                                    $insert_categoria   = "INSERT INTO tm_producto_catg SET descripcion = ?";
                                    $result_categoria   = $this->db->prepare($insert_categoria)->execute(array($categoria));
                                    $id_catg            = $this->db->lastInsertId(); // se obtiene el id 
                                }                                
                                    $sql_areapro = $this->db->selectOne("SELECT * FROM tm_area_prod WHERE nombre LIKE '".$x_area."' ");

                                if ($sql_areapro) {
                                    $id_areap = $sql_areapro['id_areap'];
                                }
                                else{
                                    $id_areap = "";

                                }
                                $x_trasformado = ($x_trasformado == 'si') ? '2': '1';
                                
                                $sql_producto1 = $this->db->selectOne("SELECT * FROM tm_producto WHERE cod_pro LIKE '".$x_cod_pro."'");
                                if(!$sql_producto1){
                                    $insert_producto = "INSERT INTO tm_producto SET id_tipo = ? ,id_catg = ?, id_areap = ?, nombre = ?, notas = ?, descripcion = ?, delivery = ?, cod_pro  = ?";
                                    $result_producto = $this->db->prepare($insert_producto)->execute(array($x_trasformado, $id_catg, $id_areap, $x_producto, $x_notas, $x_desc, $x_delivery, $x_cod_pro)); 
                                }
                            }
            
                        }
                        // presentacion se activa     
                        $sheetpre = $objPHPExcel->getSheet(1); 
                        $highestRowp = $sheetpre->getHighestRow(); 
                        $highestColumn = $sheetpre->getHighestColumn();
                        for ($row = 2; $row <= $highestRowp; $row++){ 
                            $p_cod_producto     = $sheetpre->getCell("A".$row)->getValue();
                            $p_presentacion     = $sheetpre->getCell("B".$row)->getValue();
                            $p_cod_presentacion = $sheetpre->getCell("C".$row)->getValue();
                            $p_descripcion      = $sheetpre->getCell("D".$row)->getValue();
                            $p_precio           = $sheetpre->getCell("E".$row)->getValue();
                            $p_precio_delivery  = $sheetpre->getCell("F".$row)->getValue();
                            $p_stock_min        = $sheetpre->getCell("G".$row)->getValue();
                            $p_control_stock    = $sheetpre->getCell("H".$row)->getValue();
                            $p_iva              = $sheetpre->getCell("I".$row)->getValue();
                            $p_delivery         = $sheetpre->getCell("H".$row)->getValue();
                            // REMPLAZAR 
                            $p_control_stock    = (strtoupper($p_control_stock) == 'SI') ? '1': '0';
                            $p_iva              = (strtoupper($p_iva)   == 'SI') ? '1': '0';
                            $p_delivery         = (strtoupper($p_delivery) == 'SI') ? '1': '0';

                            $p_iva_monto        = Session::get('iva');
                            // extraer id producto
                            $sql_producto = $this->db->selectOne("SELECT * FROM tm_producto WHERE cod_pro LIKE '".$p_cod_producto."'");
                                       
                            if ($sql_producto) {
                                $id_p_producto = $sql_producto['id_prod'];
                                $p_descripcion = (strlen($p_descripcion) >= 1)? $p_descripcion : '';
                                $sql_presentacion = $this->db->selectOne("SELECT * FROM tm_producto_pres WHERE cod_prod LIKE '".$p_cod_presentacion."'");
                                if(!$sql_presentacion){
                                    $insert_presentacion = "INSERT INTO tm_producto_pres SET id_prod = ?,cod_prod = ?,presentacion = ?,descripcion = ?,precio = ?,precio_delivery = ?,receta = ?,stock_min = ?,crt_stock = ?,impuesto = ?,delivery = ?,margen = ?,iva = ?";
                                    $result_presentacion = $this->db->prepare($insert_presentacion)->execute(array($id_p_producto, $p_cod_presentacion, $p_presentacion, $p_descripcion, $p_precio, $p_precio_delivery, '0',$p_stock_min,$p_control_stock,$p_iva, $p_delivery,'0', $p_iva_monto)); 
                                } 
                            }
                        }
                        return ($result_presentacion)? '1' : '0';


                    unlink($archivo);
                    }	
            
                }
            }
            



        } 
        catch (Exception $e) 
        {
            die($e->getMessage());
        }

    }

    public function importarexcelinsumos()
    {   
        try {
            error_reporting(0);
            include "libs/class.upload.php";
            if(isset($_FILES["file"])){
                $up = new Upload($_FILES["file"]);
                if($up->uploaded){
                    $up->Process("libs/uploads/");
                    if($up->processed){
                        /// leer el archivo excel
                        // require_once "../libs/PHPExcel/Classes/PHPExcel.php";
                        require_once "libs/PHPExcel/Classes/PHPExcel.php";

                        $archivo = "libs/uploads/".$up->file_dst_name;
                        $inputFileType = PHPExcel_IOFactory::identify($archivo);
                        $objReader = PHPExcel_IOFactory::createReader($inputFileType);
                        $objPHPExcel = $objReader->load($archivo);
                        $sheet = $objPHPExcel->getSheet(0); 
                        $highestRow = $sheet->getHighestRow(); 
                        $highestColumn = $sheet->getHighestColumn();
                        for ($row = 2; $row <= $highestRow; $row++){ 
                            $categoria      = $sheet->getCell("A".$row)->getValue();
                            $x_nombre       = $sheet->getCell("B".$row)->getValue();
                            $x_cod          = $sheet->getCell("C".$row)->getValue();
                            $x_medida       = $sheet->getCell("D".$row)->getValue();
                            $x_stock_min    = $sheet->getCell("E".$row)->getValue();
                            $x_costo_und    = $sheet->getCell("F".$row)->getValue();

            
                            if($categoria){

            
                                $sql_categoria = $this->db->selectOne("SELECT * FROM tm_insumo_catg WHERE descripcion LIKE '".$categoria."' ");
                                       
                                if ($sql_categoria) {
                                    $id_catg = $sql_categoria['id_catg'];
                                }
                                else{
                                    $insert_categoria   = "INSERT INTO tm_insumo_catg SET descripcion = ?";
                                    $result_categoria   = $this->db->prepare($insert_categoria)->execute(array($categoria));
                                    $id_catg            = $this->db->lastInsertId(); // se obtiene el id 
                                }                                
                                    $sql_medida = $this->db->selectOne("SELECT * FROM tm_tipo_medida WHERE descripcion LIKE '".$x_medida."' ");

                                if ($sql_medida) {
                                    $id_medida = $sql_medida['id_med'];
                                }
                                else{
                                    $id_medida = "";

                                }
                                
                                $sql_producto1 = $this->db->selectOne("SELECT * FROM tm_insumo WHERE cod_ins LIKE '".$x_cod."'");
                                if(!$sql_producto1){
                                    $insert_producto = "INSERT INTO tm_insumo SET id_catg = ?, id_med = ?,cod_ins = ?, nomb_ins = ?, stock_min = ?, cos_uni = ?";
                                    $result_producto = $this->db->prepare($insert_producto)->execute(array($id_catg,$id_medida,$x_cod,$x_nombre,$x_stock_min,$x_costo_und)); 
                                }
                            }            
                        }
                        return ($result_producto)? '1' : '0';


                    unlink($archivo);
                    }	
            
                }
            }
            



        } 
        catch (Exception $e) 
        {
            die($e->getMessage());
        }

    }

}
