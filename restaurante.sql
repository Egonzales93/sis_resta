-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 31-10-2023 a las 06:35:07
-- Versión del servidor: 10.4.28-MariaDB
-- Versión de PHP: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `restaurante`
--

DELIMITER $$
--
-- Procedimientos
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_actualizar_cdr_baja` (`p_id_comunicacion` INT, `p_hash_cpe` VARCHAR(100), `p_hash_cdr` VARCHAR(100), `p_code_respuesta_impuestos` VARCHAR(5), `p_descripcion_impuestos_cdr` VARCHAR(300), `p_name_file_impuestos` VARCHAR(80), OUT `mensaje` VARCHAR(100))   BEGIN
	IF(NOT EXISTS(SELECT * FROM comunicacion_baja WHERE id_comunicacion=p_id_comunicacion))THEN
		SET mensaje='No existe la comunicación de baja';
	ELSE
		UPDATE comunicacion_baja SET enviado_impuestos=1,hash_cpe=p_hash_cpe,hash_cdr=p_hash_cdr,code_respuesta_impuestos=p_code_respuesta_impuestos,descripcion_impuestos_cdr=p_descripcion_impuestos_cdr,name_file_impuestos=p_name_file_impuestos WHERE id_comunicacion=p_id_comunicacion;
		SET mensaje='Actualizado correctamente';
	END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_actualizar_cdr_resumen` (`p_id_resumen` INT, `p_hash_cpe` VARCHAR(100), `p_hash_cdr` VARCHAR(100), `p_code_respuesta_impuestos` VARCHAR(5), `p_descripcion_impuestos_cdr` VARCHAR(300), `p_name_file_impuestos` VARCHAR(80), OUT `mensaje` VARCHAR(100))   BEGIN
	IF(NOT EXISTS(SELECT * FROM resumen_diario WHERE id_resumen=p_id_resumen))THEN
		SET mensaje='No existe el resumen diario';
	ELSE
		UPDATE resumen_diario SET enviado_impuestos=1,hash_cpe=p_hash_cpe,hash_cdr=p_hash_cdr,code_respuesta_impuestos=p_code_respuesta_impuestos,descripcion_impuestos_cdr=p_descripcion_impuestos_cdr,name_file_impuestos=p_name_file_impuestos WHERE id_resumen=p_id_resumen;
		SET mensaje='Actualizado correctamente';
		
		block:BEGIN
		DECLARE done INT DEFAULT FALSE;
		DECLARE idven BIGINT;
		DECLARE venta CURSOR FOR SELECT dr.id_venta FROM resumen_diario AS rd INNER JOIN resumen_diario_detalle AS dr ON rd.id_resumen = dr.id_resumen WHERE dr.id_resumen = p_id_resumen;
		DECLARE CONTINUE HANDLER FOR NOT FOUND SET done=TRUE;
		OPEN venta;
		
			read_loop: LOOP
			FETCH venta INTO idven;
				IF done THEN
					LEAVE read_loop;
				END IF;
				UPDATE tm_venta SET code_respuesta_impuestos=p_code_respuesta_impuestos,descripcion_impuestos_cdr=p_descripcion_impuestos_cdr,name_file_impuestos=p_name_file_impuestos,hash_cpe=p_hash_cpe,hash_cdr=p_hash_cdr WHERE id_venta = idven;
			END LOOP;
			
		CLOSE venta;
		END block;
	END IF;
    END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_consultar_boletas_resumen` (`p_fecha_resumen` DATE)   BEGIN
	SELECT
		'03' AS 'tipo_comprobante',DATE_FORMAT(v.fecha_venta,'%Y-%m-%d') AS 'fecha_resumen',IF(c.ci="" OR c.ci="-",0,1) AS 'tipo_documento',
		IF(c.ci="" OR c.ci="-","00000000",c.ci) AS "ci",CONCAT(c.nombres," ",c.ape_paterno," ",c.ape_materno) AS 'cliente',v.serie_doc AS 'serie_doc',
		v.nro_doc AS 'nro_doc',"PEN" AS 'tipo_moneda',ROUND((v.total/(1 + v.iva)) *(v.iva),2) AS 'total_iva',
		ROUND((v.total/(1 + v.iva)),2) AS 'total_gravadas',ROUND(v.total,2) AS 'total_facturado',IF(v.estado="a",1,3) AS 'status_code',v.id_venta
	FROM tm_venta v INNER JOIN tm_cliente c ON c.id_cliente=v.id_cliente
	WHERE v.id_tipo_doc=1 AND v.code_respuesta_impuestos="" AND DATE_FORMAT(v.fecha_venta,"%Y-%m-%d") = p_fecha_resumen
	ORDER BY v.fecha_venta ASC;
    END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_consultar_documento` (`p_id_venta` INT)   BEGIN
	SELECT
		IF(id_tipo_doc='1','03','01') AS tipo_comprobante, IF(c.ci="" OR c.ci="-",0,1) AS 'tipo_documento',
		IF(c.ci="" OR c.ci="-","00000000",c.ci) AS "ci",v.serie_doc AS 'serie_doc', v.nro_doc AS 'nro_doc',"PEN" AS 'tipo_moneda',ROUND((v.total/(1 + v.iva)) *(v.iva),2) AS 'total_iva',
		ROUND((v.total/(1 + v.iva)),2) AS 'total_gravadas',ROUND(v.total,2) AS 'total_facturado',v.id_venta, v.estado
	FROM tm_venta v INNER JOIN tm_cliente c ON c.id_cliente=v.id_cliente
	WHERE v.id_venta = p_id_venta;
    END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_generar_numerobaja` (`p_tipo_doc` CHAR(3), OUT `numerobaja` CHAR(5))   BEGIN
	DECLARE contador INT;
	IF(NOT EXISTS(SELECT * FROM comunicacion_baja WHERE tipo_doc = p_tipo_doc))THEN
		SET contador:= (SELECT IFNULL(MAX(correlativo), 0)+1 AS 'codigo' FROM comunicacion_baja WHERE tipo_doc = p_tipo_doc);
		SET numerobaja:= (SELECT LPAD(contador,5,'0') AS 'correlativo');
	ELSE		
		SET contador:= (SELECT IFNULL(MAX(correlativo), 0)+1 AS 'codigo' FROM comunicacion_baja WHERE tipo_doc = p_tipo_doc);
		SET numerobaja:= (SELECT LPAD(contador,5,'0') AS 'correlativo');
	END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_generar_numeroresumen` (OUT `numeroresumen` CHAR(5))   BEGIN
	DECLARE contador INT;
	IF(NOT EXISTS(SELECT * FROM resumen_diario))THEN
		SET contador:= (SELECT IFNULL(MAX(correlativo), 0)+1 AS 'codigo' FROM resumen_diario);
		SET numeroresumen:= (SELECT LPAD(contador,5,'0') AS 'correlativo');
	ELSE		
		SET contador:= (SELECT IFNULL(MAX(correlativo), 0)+1 AS 'codigo' FROM resumen_diario);
		SET numeroresumen:= (SELECT LPAD(contador,5,'0') AS 'correlativo');
	END IF;
    END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `usp_cajaAperturar` (IN `_flag` INT(11), IN `_id_usu` INT(11), IN `_id_caja` INT(11), IN `_id_turno` INT(11), IN `_fecha_aper` DATETIME, IN `_monto_aper` DECIMAL(10,2))   BEGIN
	DECLARE _filtro INT DEFAULT 1;
	
	IF _flag = 1 THEN
	
		SELECT COUNT(*) INTO _filtro FROM tm_aper_cierre WHERE (id_usu = _id_usu or id_caja = _id_caja) AND estado = 'a';
		
		IF _filtro = 0 THEN
			INSERT INTO tm_aper_cierre (id_usu,id_caja,id_turno,fecha_aper,monto_aper) VALUES (_id_usu, _id_caja, _id_turno, _fecha_aper, _monto_aper);
			
			SELECT @@IDENTITY INTO @id;
			
			SELECT @id AS id_apc, _filtro AS cod;
		ELSE
			SELECT _filtro AS cod;
		END IF;
		
	END IF;
    END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `usp_cajaCerrar` (IN `_flag` INT(11), IN `_id_apc` INT(11), IN `_fecha_cierre` DATETIME, IN `_monto_cierre` DECIMAL(10,2), IN `_monto_sistema` DECIMAL(10,2), IN `_stock_pollo` VARCHAR(11))   BEGIN
		DECLARE _filtro INT DEFAULT 0;
		DECLARE _id_usu INT DEFAULT 0;
		
		IF _flag = 1 THEN
		
			SELECT COUNT(*) INTO _filtro FROM tm_aper_cierre WHERE id_apc = _id_apc AND estado = 'a';
			SELECT id_usu INTO _id_usu FROM tm_aper_cierre WHERE id_apc = _id_apc AND estado = 'a';
			
			IF _filtro = 1 THEN
			
				UPDATE tm_aper_cierre SET fecha_cierre = _fecha_cierre, monto_cierre = _monto_cierre, monto_sistema = _monto_sistema, stock_pollo = _stock_pollo, estado = 'c' 
				WHERE id_apc = _id_apc;
				
				SELECT _filtro AS cod, _id_usu AS id_usu;
			ELSE
				SELECT _filtro AS cod, _id_usu AS id_usu;
			END IF;
		END IF;
	END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `usp_comprasAnular` (IN `_flag` INT(11), IN `_id_compra` INT(11))   BEGIN
	DECLARE _filtro INT DEFAULT 0;
	if _flag = 1 then
	
		SELECT COUNT(*) INTO _filtro FROM tm_compra WHERE estado = 'a' AND id_compra = _id_compra;
		
		IF _filtro = 1 THEN
			UPDATE tm_compra SET estado = 'i' WHERE id_compra = _id_compra;
			DELETE FROM tm_inventario WHERE id_tipo_ope = 1 AND id_ope = _id_compra;
			SELECT _filtro AS cod;
		ELSE
			SELECT _filtro AS cod;
		END IF;
	end if;
    END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `usp_comprasCreditoCuotas` (IN `_flag` INT(11), IN `_id_credito` INT(11), IN `_id_usu` INT(11), IN `_id_apc` INT(11), IN `_importe` DECIMAL(10,2), IN `_fecha` DATETIME, IN `_egreso` INT(11), IN `_monto_egreso` DECIMAL(10,2), IN `_monto_amortizado` DECIMAL(10,2), IN `_total_credito` DECIMAL(10,2))   BEGIN
	DECLARE tcuota DECIMAL(10,2) DEFAULT 0;
	DECLARE motivo VARCHAR(100);
	
	IF _flag = 1 THEN
	
		INSERT INTO tm_credito_detalle (id_credito,id_usu,importe,fecha,egreso)
		VALUES (_id_credito, _id_usu, _importe, _fecha, _egreso);
	
			IF (_egreso = 1) THEN
	
				SELECT v.desc_prov INTO @descP
				FROM v_compras AS v INNER JOIN tm_compra_credito AS c ON v.id_compra = c.id_compra
				WHERE c.id_credito = _id_credito;
		
			SET motivo = @descP;
		
				INSERT INTO tm_gastos_adm (id_tipo_gasto,id_usu,id_apc,importe,motivo,fecha_registro)
				VALUES (4,_id_usu,_id_apc,_monto_egreso,motivo,_fecha);
	
			END IF;
	
		SET tcuota = _monto_amortizado + _importe;
	
		IF ( _total_credito <= tcuota ) THEN
	
			UPDATE tm_compra_credito SET estado = 'a' WHERE id_credito = _id_credito;
	
		END IF;
	
	END IF;
	
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `usp_comprasRegProveedor` (IN `_flag` INT(11), IN `_id_prov` INT(11), IN `_nit` VARCHAR(13), IN `_razon_social` VARCHAR(100), IN `_direccion` VARCHAR(100), IN `_telefono` INT(9), IN `_email` VARCHAR(45), IN `_contacto` VARCHAR(45))   BEGIN
		DECLARE _filtro INT DEFAULT 1;
		
		IF _flag = 1 THEN
		
			SELECT count(*) INTO _filtro FROM tm_proveedor WHERE nit = _nit;
		
			IF _filtro = 0 THEN
			
				INSERT INTO tm_proveedor (nit,razon_social,direccion,telefono,email,contacto) 
				VALUES (_nit, _razon_social, _direccion, _telefono, _email, _contacto);
				
				SELECT @@IDENTITY INTO @id;
			
				SELECT _filtro AS cod,@id AS id_prov;
			ELSE
				SELECT _filtro AS cod;
			END IF;	
			
		END IF;
		
		if _flag = 2 then
		
			UPDATE tm_proveedor SET nit = _nit, razon_social = _razon_social, direccion = _direccion, telefono = _telefono, email = _email, contacto = _contacto
			WHERE id_prov = _id_prov;
			
		end if;
	END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `usp_configAlmacenes` (IN `_flag` INT(11), IN `_nombre` VARCHAR(45), IN `_estado` VARCHAR(5), IN `_idAlm` INT(11))   BEGIN
	DECLARE _cont INT DEFAULT 0;
	DECLARE _cod0 INT DEFAULT 0;
	DECLARE	_cod1 INT DEFAULT 1;
	DECLARE	_cod2 INT DEFAULT 2;
	
	IF _flag = 1 THEN
		SELECT COUNT(*) INTO _cont FROM tm_almacen WHERE nombre = _nombre;
	
		IF _cont = 0 THEN
			INSERT INTO tm_almacen (nombre,estado) VALUES (_nombre, _estado);
			SELECT _cod1 AS cod;
		ELSE
			SELECT _cod0 AS cod;
		END IF;
	END IF;
	
	IF _flag = 2 THEN
		SELECT COUNT(*) INTO _cont FROM tm_almacen WHERE nombre = _nombre AND estado = _estado;
	
		IF _cont = 0 THEN
			UPDATE tm_almacen SET nombre = _nombre, estado = _estado WHERE id_alm = _idAlm;
			SELECT _cod2 AS cod;
		ELSE
			SELECT _cod2 AS cod;
		END IF;
	END IF;
	END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `usp_configAreasProd` (IN `_flag` INT(11), IN `_id_areap` INT(11), IN `_id_imp` INT(11), IN `_nombre` VARCHAR(45), IN `_estado` VARCHAR(5))   BEGIN
	DECLARE _cont INT DEFAULT 0;
	DECLARE _cod0 INT DEFAULT 0;
	DECLARE	_cod1 INT DEFAULT 1;
	DECLARE	_cod2 INT DEFAULT 2;
	
	IF _flag = 1 THEN
		SELECT COUNT(*) INTO _cont FROM tm_area_prod WHERE nombre = _nombre;
	
		IF _cont = 0 THEN
			INSERT INTO tm_area_prod (id_imp,nombre,estado) VALUES (_id_imp, _nombre, _estado);
			SELECT _cod1 AS cod;
		ELSE
			SELECT _cod0 AS cod;
		END IF;
	END IF;
	
	IF _flag = 2 THEN
		SELECT COUNT(*) INTO _cont FROM tm_area_prod WHERE id_imp = _id_imp AND nombre = _nombre AND estado = _estado;
	
		IF _cont = 0 THEN
			UPDATE tm_area_prod SET id_imp = _id_imp, nombre = _nombre, estado = _estado WHERE id_areap = _id_areap;
			SELECT _cod2 AS cod;
		ELSE
			SELECT _cod2 AS cod;
		END IF;
	END IF;
	END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `usp_configCajas` (IN `_flag` INT(11), IN `_id_caja` INT(11), IN `_descripcion` VARCHAR(45), IN `_estado` VARCHAR(5))   BEGIN
	DECLARE _filtro INT DEFAULT 0;
	DECLARE _cod0 INT DEFAULT 0;
	DECLARE	_cod1 INT DEFAULT 1;
	DECLARE	_cod2 INT DEFAULT 2;
	
	IF _flag = 1 THEN
		SELECT COUNT(*) INTO _filtro FROM tm_caja WHERE descripcion = _descripcion;
	
		IF _filtro = 0 THEN
			INSERT INTO tm_caja (descripcion,estado) VALUES (_descripcion, _estado);
			SELECT _cod1 AS cod;
		ELSE
			SELECT _cod0 AS cod;
		END IF;
	END IF;
	
	IF _flag = 2 THEN
	
		SELECT COUNT(*) INTO _filtro FROM tm_caja WHERE descripcion = _descripcion AND estado = _estado;
	
		IF _filtro = 0 THEN
			UPDATE tm_caja SET descripcion = _descripcion, estado = _estado WHERE id_caja = _id_caja;
			SELECT _cod2 AS cod;
		ELSE
			SELECT _cod2 AS cod;
		END IF;
	END IF;
	END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `usp_configEliminarCategoriaIns` (IN `_id_catg` INT(11))   BEGIN
	DECLARE _filtro INT DEFAULT 0;
	DECLARE _cod0 INT DEFAULT 0;
	DECLARE	_cod1 INT DEFAULT 1;
	
	SELECT COUNT(*) INTO _filtro FROM tm_insumo WHERE id_catg = _id_catg;
	IF _filtro = 0 THEN
		DELETE FROM tm_insumo_catg WHERE id_catg = _id_catg;
		SELECT _cod1 AS cod;
	ELSE
		SELECT _cod0 AS cod;
	END IF;
    END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `usp_configEliminarCategoriaProd` (IN `_id_catg` INT(11))   BEGIN
	DECLARE _filtro INT DEFAULT 0;
	DECLARE _cod0 INT DEFAULT 0;
	DECLARE	_cod1 INT DEFAULT 1;
	
	SELECT COUNT(*) INTO _filtro FROM tm_producto WHERE id_catg = _id_catg;
	IF _filtro = 0 THEN
		DELETE FROM tm_producto_catg WHERE id_catg = _id_catg;
		SELECT _cod1 AS cod;
	ELSE
		SELECT _cod0 AS cod;
	END IF;
    END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `usp_configImpresoras` (IN `_flag` INT(11), IN `_id_imp` INT(11), IN `_nombre` VARCHAR(50), IN `_estado` VARCHAR(5))   BEGIN
	DECLARE _filtro INT DEFAULT 0;
	DECLARE _cod0 INT DEFAULT 0;
	DECLARE	_cod1 INT DEFAULT 1;
	DECLARE	_cod2 INT DEFAULT 2;
	
	IF _flag = 1 THEN
		SELECT COUNT(*) INTO _filtro FROM tm_impresora WHERE nombre = _nombre;
	
		IF _filtro = 0 THEN
			INSERT INTO tm_impresora (nombre,estado) VALUES (_nombre,_estado);
			SELECT _cod1 AS cod;
		ELSE
			SELECT _cod0 AS cod;
		END IF;
	END IF;
	
	IF _flag = 2 THEN
		SELECT COUNT(*) INTO _filtro FROM tm_impresora WHERE nombre = _nombre AND estado = _estado;
	
		IF _filtro = 0 THEN
			UPDATE tm_impresora SET nombre = _nombre, estado = _estado WHERE id_imp = _id_imp;
			SELECT _cod2 AS cod;
		ELSE
			SELECT _cod2 AS cod;
		END IF;
	END IF;
    END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `usp_configInsumo` (IN `_flag` INT(11), IN `_idCatg` INT(11), IN `_idMed` INT(11), IN `_cod` VARCHAR(10), IN `_nombre` VARCHAR(45), IN `_stock` INT(11), IN `_costo` DECIMAL(10,2), IN `_estado` VARCHAR(5), IN `_idIns` INT(11))   BEGIN
	DECLARE _cont INT DEFAULT 0;
	DECLARE _cod0 INT DEFAULT 0;
	DECLARE	_cod1 INT DEFAULT 1;
	DECLARE	_cod2 INT DEFAULT 2;
	
	IF _flag = 1 THEN
	
		SELECT COUNT(*) INTO _cont FROM tm_insumo WHERE nomb_ins = _nombre and cod_ins = _cod and id_catg = _idCatg;
	
		IF _cont = 0 THEN
			INSERT INTO tm_insumo (id_catg,id_med,cod_ins,nomb_ins,stock_min,cos_uni) VALUES ( _idCatg, _idMed, _cod, _nombre, _stock, _costo);
			SELECT _cod1 AS cod;
		ELSE
			SELECT _cod0 AS cod;
		END IF;
		
	END IF;
	
	IF _flag = 2 THEN
	
		SELECT COUNT(*) INTO _cont FROM tm_insumo WHERE id_catg = _idCatg AND id_med = _idMed AND cod_ins = _cod AND nomb_ins = _nombre AND stock_min = _stock AND cos_uni = _costo AND estado = _estado;
	
		IF _cont = 0 THEN
			UPDATE tm_insumo SET id_catg = _idCatg, id_med = _idMed, cod_ins = _cod, nomb_ins = _nombre, stock_min = _stock, cos_uni = _costo, estado = _estado WHERE id_ins = _idIns;
			SELECT _cod2 AS cod;
		ELSE
			SELECT _cod2 AS cod;
		END IF;
	END IF;
    END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `usp_configInsumoCatgs` (IN `_flag` INT(11), IN `_descC` VARCHAR(45), IN `_idCatg` INT(11))   BEGIN
	DECLARE _cont INT DEFAULT 0;
	DECLARE _cod0 INT DEFAULT 0;
	DECLARE	_cod1 INT DEFAULT 1;
	DECLARE	_cod2 INT DEFAULT 2;
	
	IF _flag = 1 THEN
	
		SELECT COUNT(*) INTO _cont FROM tm_insumo_catg WHERE descripcion = _descC;
		
		IF _cont = 0 THEN
			INSERT INTO tm_insumo_catg (descripcion) VALUES (_descC);
			SELECT _cod1 AS cod;
		ELSE
			SELECT _cod0 AS cod;
		END IF;
	
	END IF;
	
	IF _flag = 2 THEN
	
		SELECT COUNT(*) INTO _cont FROM tm_insumo_catg WHERE descripcion = _descC;
		
		IF _cont = 0 THEN
			UPDATE tm_insumo_catg SET descripcion = _descC WHERE id_catg = _idCatg;
			SELECT _cod2 AS cod;
		ELSE
			SELECT _cod2 AS cod;
		END IF;
	
	END IF;
    END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `usp_configMesas` (IN `_flag` INT(11), IN `_id_mesa` INT(11), IN `_id_salon` INT(11), IN `_nro_mesa` VARCHAR(5), IN `_estado` VARCHAR(45))   BEGIN
	DECLARE _filtro INT DEFAULT 0;
	DECLARE _cod0 INT DEFAULT 0;
	DECLARE	_cod1 INT DEFAULT 1;
	DECLARE	_cod2 INT DEFAULT 2;
	
	IF _flag = 1 THEN
	
		SELECT COUNT(*) INTO _filtro FROM tm_mesa WHERE id_salon = _id_salon AND nro_mesa = _nro_mesa;
	
		IF _filtro = 0 THEN
			INSERT INTO tm_mesa (id_salon,nro_mesa) VALUES (_id_salon, _nro_mesa);
			SELECT _cod1 AS cod;
		ELSE
			SELECT _cod0 AS cod;
		END IF;
	
	end if;
	
	IF _flag = 2 THEN
	
		SELECT COUNT(*) INTO _filtro FROM tm_mesa WHERE id_salon = _id_salon AND nro_mesa = _nro_mesa AND estado = _estado;
	
		IF _filtro = 0 THEN
			UPDATE tm_mesa SET nro_mesa = _nro_mesa, estado = _estado WHERE id_mesa = _id_mesa;
			SELECT _cod2 AS cod;
		ELSE
			SELECT _cod2 AS cod;
		END IF;
	
	END IF;
	
	IF _flag = 3 THEN
	
		SELECT count(*) INTO _filtro FROM tm_pedido_mesa WHERE id_mesa = _id_mesa;
	
		IF _filtro = 0 THEN
			DELETE FROM tm_mesa WHERE id_mesa = _id_mesa;
			SELECT _cod1 AS cod;
		ELSE
			SELECT _cod0 AS cod;
		END IF;
	
	END IF;
	END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `usp_configProducto` (IN `_flag` INT(11), IN `_id_prod` INT(11), IN `_id_tipo` INT(11), IN `_id_catg` INT(11), IN `_id_areap` INT(11), IN `_nombre` VARCHAR(45), IN `_notas` VARCHAR(200), IN `_delivery` INT(1), IN `_estado` VARCHAR(1))   BEGIN
	DECLARE _filtro INT DEFAULT 0;
	DECLARE _cod0 INT DEFAULT 0;
	DECLARE	_cod1 INT DEFAULT 1;
	DECLARE	_cod2 INT DEFAULT 2;
	
	IF _flag = 1 THEN
		SELECT COUNT(*) INTO _filtro FROM tm_producto WHERE id_tipo = _id_tipo AND id_catg = _id_catg AND id_areap = _id_areap AND nombre = _nombre;
		IF _filtro = 0 THEN
			INSERT INTO tm_producto (id_tipo,id_catg,id_areap,nombre,notas,delivery) 
			VALUES ( _id_tipo, _id_catg, _id_areap, _nombre, _notas, _delivery);
			SELECT _cod1 AS cod;
		else
			SELECT _cod0 AS cod;
		end if;
	end if;
	
	if _flag = 2 then
		SELECT COUNT(*) INTO _filtro FROM tm_producto WHERE id_tipo = _id_tipo AND id_catg = _id_catg AND id_areap = _id_areap AND nombre = _nombre AND notas = _notas AND delivery = _delivery and estado = _estado;
		IF _filtro = 0 THEN
			UPDATE tm_producto SET id_tipo = _id_tipo, id_catg = _id_catg, id_areap = _id_areap, nombre = _nombre, notas = _notas, delivery = _delivery, estado = _estado 
			WHERE id_prod = _id_prod;
			SELECT _cod2 AS cod;
		ELSE
			SELECT _cod2 AS cod;
		END IF;
	end if;
	
	END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `usp_configProductoCatgs` (IN `_flag` INT(11), IN `_id_catg` INT(11), IN `_descripcion` VARCHAR(45), IN `_delivery` INT(1), IN `_orden` INT(11), IN `_imagen` VARCHAR(200), IN `_estado` VARCHAR(1))   BEGIN	
	DECLARE _filtro INT DEFAULT 0;
	DECLARE _cod0 INT DEFAULT 0;
	DECLARE	_cod1 INT DEFAULT 1;
	DECLARE	_cod2 INT DEFAULT 2;
	
	IF _flag = 1 THEN	
		
		SELECT COUNT(*) INTO _filtro FROM tm_producto_catg WHERE descripcion = _descripcion;
		IF _filtro = 0 THEN
			INSERT INTO tm_producto_catg (descripcion,delivery,orden,imagen,estado) VALUES (_descripcion,_delivery,100,_imagen,_estado);
			SELECT _cod1 AS cod;
		ELSE
			SELECT _cod0 AS cod;
		END IF;
	end if;
		
	IF _flag = 2 THEN
		SELECT COUNT(*) INTO _filtro FROM tm_producto_catg WHERE descripcion = _descripcion and delivery = _delivery and orden = _orden AND imagen = _imagen AND estado = _estado;
		IF _filtro = 0 THEN
			UPDATE tm_producto_catg SET descripcion = _descripcion, delivery = _delivery, orden =_orden, imagen = _imagen, estado = _estado WHERE id_catg = _id_catg;
			SELECT _cod2 AS cod;
		ELSE
			SELECT _cod2 AS cod;
		END IF;
	END IF;
	
	END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `usp_configProductoIngrs` (IN `_flag` INT(11), IN `_id_pi` INT(11), IN `_id_pres` INT(11), IN `_id_tipo_ins` INT(11), IN `_id_ins` INT(11), IN `_id_med` INT(11), IN `_cant` FLOAT)   BEGIN
	if _flag = 1 then
		INSERT INTO tm_producto_ingr (id_pres,id_tipo_ins,id_ins,id_med,cant) VALUES (_id_pres, _id_tipo_ins, _id_ins, _id_med, _cant);
	end if;
	if _flag = 2 then
		UPDATE tm_producto_ingr SET cant = _cant WHERE id_pi = _id_pi;
	end if;
	if _flag = 3 then
		DELETE FROM tm_producto_ingr WHERE id_pi = _id_pi;
	end if;
    END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `usp_configProductoPres` (IN `_flag` INT(11), IN `_id_pres` INT(11), IN `_id_prod` INT(11), IN `_cod_prod` VARCHAR(45), IN `_presentacion` VARCHAR(45), IN `_descripcion` VARCHAR(200), IN `_precio` DECIMAL(10,2), IN `_precio_delivery` DECIMAL(10,2), IN `_receta` INT(1), IN `_stock_min` INT(11), IN `_stock_limit` INT(1), IN `_impuesto` INT(1), IN `_delivery` INT(1), IN `_margen` INT(1), IN `_iva` DECIMAL(10,2), IN `_imagen` VARCHAR(200), IN `_estado` VARCHAR(1))   BEGIN
		
	DECLARE _cont INT DEFAULT 0;
	DECLARE _cod0 INT DEFAULT 0;
	DECLARE	_cod1 INT DEFAULT 1;
	DECLARE	_cod2 INT DEFAULT 2;
	IF _flag = 1 THEN
	
		SELECT COUNT(*) INTO _cont FROM tm_producto_pres WHERE presentacion = _presentacion AND id_prod = _id_prod;
		
		IF _cont = 0 THEN
			INSERT INTO tm_producto_pres (id_prod,cod_prod,presentacion,descripcion,precio,precio_delivery,receta,stock_min,crt_stock,impuesto,delivery,margen,iva,imagen,estado) 
			VALUES (_id_prod, _cod_prod, _presentacion, _descripcion, _precio, _precio_delivery, _receta, _stock_min, _stock_limit, _impuesto, _delivery, _margen, _iva, _imagen, _estado);
			SELECT _cod1 AS cod;
		ELSE
			SELECT _cod0 AS cod;
		END IF;
		
	end if;
	
	IF _flag = 2 THEN
	
		UPDATE tm_producto_pres SET cod_prod = _cod_prod, presentacion = _presentacion, descripcion = _descripcion, precio = _precio, precio_delivery = _precio_delivery, receta = _receta, stock_min = _stock_min, crt_stock = _stock_limit, impuesto = _impuesto, delivery = _delivery, margen = _margen, iva = _iva, imagen = _imagen, estado = _estado 
		WHERE id_pres = _id_pres;
		SELECT _cod2 AS cod;
		
	END IF;
	END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `usp_configRol` (IN `_flag` INT(11), IN `_desc` VARCHAR(45), IN `_idRol` INT(11))   BEGIN
		DECLARE _duplicado INT DEFAULT 1;
		
		IF _flag = 1 THEN
		
				SELECT count(*) INTO _duplicado FROM tm_rol WHERE descripcion = _desc;
			
			IF _duplicado = 0 THEN
				INSERT INTO tm_rol (descripcion) VALUES (_desc);
				
				SELECT _duplicado AS dup;
			ELSE
				SELECT _duplicado AS dup;
			END IF;
		
		end if;
		
		IF _flag = 2 THEN
		
				SELECT COUNT(*) INTO _duplicado FROM tm_rol WHERE descripcion = _desc;
			
			IF _duplicado = 0 THEN
				UPDATE tm_rol SET descripcion = _desc WHERE id_rol = _idRol;
				
				SELECT _duplicado AS dup;
			ELSE
				SELECT _duplicado AS dup;
			END IF;
		
		END IF;
	END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `usp_configSalones` (IN `_flag` INT(11), IN `_id_salon` INT(11), IN `_descripcion` VARCHAR(45), IN `_estado` VARCHAR(5))   BEGIN
	DECLARE _filtro INT DEFAULT 0;
	DECLARE _filtro2 INT DEFAULT 0;
	DECLARE _cod0 INT DEFAULT 0;
	DECLARE	_cod1 INT DEFAULT 1;
	DECLARE	_cod2 INT DEFAULT 2;
	
	IF _flag = 1 THEN
	
		SELECT COUNT(*) INTO _filtro FROM tm_salon WHERE descripcion = _descripcion AND estado = _estado;
	
		IF _filtro = 0 THEN
			INSERT INTO tm_salon (descripcion,estado) VALUES (_descripcion,_estado);
			SELECT _cod1 AS cod;
		ELSE
			SELECT _cod0 AS cod;
		END IF;
	
	end if;
	
	IF _flag = 2 THEN
	
		SELECT COUNT(*) INTO _filtro FROM tm_salon WHERE descripcion = _descripcion AND estado = _estado;
	
		IF _filtro = 0 THEN
			UPDATE tm_salon SET descripcion = _descripcion, estado = _estado WHERE id_salon = _id_salon;
			SELECT _cod2 AS cod;
		ELSE
			SELECT _cod2 AS cod;
		END IF;
	
	END IF;
	
	IF _flag = 3 THEN
	
		SELECT count(*) INTO _filtro FROM tm_mesa WHERE id_salon = _id_salon;
	
		IF _filtro = 0 THEn
			
			SELECT COUNT(*) AS _filtro2 FROM tm_salon;
			
			if _filtro2 = 1 then
			
				DELETE FROM tm_salon WHERE id_salon = _id_salon;
				ALTER TABLE tm_salon AUTO_INCREMENT = 1;
				SELECT _cod1 AS cod;
			
			else 
		
				DELETE FROM tm_salon WHERE id_salon = _id_salon;
				SELECT _cod1 AS cod;
	
			end if;		
			
		ELSE
			SELECT _cod0 AS cod;
		END IF;
	
	END IF;
	END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `usp_configUsuario` (IN `_flag` INT(11), IN `_id_usu` INT(11), IN `_id_rol` INT(11), IN `_id_areap` INT(11), IN `_ci` VARCHAR(10), IN `_ape_paterno` VARCHAR(45), IN `_ape_materno` VARCHAR(45), IN `_nombres` VARCHAR(45), IN `_email` VARCHAR(100), IN `_usuario` VARCHAR(45), IN `_contrasena` VARCHAR(45), IN `_imagen` VARCHAR(45))   BEGIN
		DECLARE _filtro INT DEFAULT 1;
		
		IF _flag = 1 THEN
		
			SELECT count(*) INTO _filtro FROM tm_usuario WHERE usuario = _usuario;
		
			IF _filtro = 0 THEN
			
				INSERT INTO tm_usuario (id_rol,id_areap,ci,ape_paterno,ape_materno,nombres,email,usuario,contrasena,imagen) 
				VALUES (_id_rol,_id_areap,_ci,_ape_paterno,_ape_materno,_nombres,_email,_usuario,_contrasena,_imagen);
				
				SELECT _filtro AS cod;
			ELSE
				SELECT _filtro AS cod;
			END IF;
		
		end if;
		
		IF _flag = 2 THEN
			UPDATE tm_usuario SET id_rol = _id_rol, id_areap = _id_areap, ci = _ci, ape_paterno = _ape_paterno, ape_materno = _ape_materno, nombres = _nombres, email = _email, usuario = _usuario, contrasena = _contrasena, imagen = _imagen
			WHERE id_usu = _id_usu;
		END IF;
	END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `usp_invESAnular` (IN `_flag` INT(11), IN `_id_es` INT(11), IN `_id_tipo` INT(11))   BEGIN
	DECLARE _filtro INT DEFAULT 0;
	IF _flag = 1 THEN
	
		SELECT COUNT(*) INTO _filtro FROM tm_inventario_entsal WHERE estado = 'a' AND id_es = _id_es;
		
		IF _filtro = 1 THEN
			UPDATE tm_inventario_entsal SET estado = 'i' WHERE id_es = _id_es;
			UPDATE tm_inventario SET estado = 'i' WHERE id_tipo_ope = _id_tipo AND id_ope = _id_es;
			SELECT _filtro AS cod;
		ELSE
			SELECT _filtro AS cod;
		END IF;
	END IF;
    END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `usp_optPedidos` (IN `_flag` INT(11))   BEGIN
	DECLARE _cont INT DEFAULT 0;
	DECLARE _cod0 INT DEFAULT 0;
	DECLARE	_cod1 INT DEFAULT 1;
	
	IF _flag = 1 THEN
	
		SELECT COUNT(*) FROM tm_aper_cierre WHERE estado = 'a';
		
		IF _cont = 0 THEN
			DELETE FROM tm_detalle_pedido;
			UPDATE tm_pedido SET estado = 'z' WHERE estado = 'a';
			/*mostrador*/
			UPDATE tm_pedido SET estado = 'd' WHERE estado = 'b' AND id_tipo_pedido = 2;
			/*delivery*/
			UPDATE tm_pedido SET estado = 'd' WHERE estado = 'c' AND id_tipo_pedido = 3;
			UPDATE tm_pedido SET estado = 'z' WHERE estado = 'b' AND id_tipo_pedido = 3;
			UPDATE tm_mesa SET estado = 'a';
			SELECT _cod1 AS cod;
		ELSE
			SELECT _cod0 AS cod;
		END IF;
	
	END IF;
	
	IF _flag = 2 THEN
	
		DELETE FROM tm_detalle_pedido;
		DELETE FROM tm_pedido_mesa;
		DELETE FROM tm_pedido_llevar;
		DELETE FROM tm_pedido_delivery;
		DELETE FROM tm_pedido;
		ALTER TABLE tm_pedido AUTO_INCREMENT = 1;
		DELETE FROM tm_compra_detalle;
		DELETE FROM tm_credito_detalle;
		DELETE FROM tm_compra_credito;
		ALTER TABLE tm_compra_credito AUTO_INCREMENT = 1;
		DELETE FROM tm_compra;
		ALTER TABLE tm_compra AUTO_INCREMENT = 1;
		DELETE FROM tm_gastos_adm;
		ALTER TABLE tm_gastos_adm AUTO_INCREMENT = 1;
		DELETE FROM tm_ingresos_adm;
		ALTER TABLE tm_ingresos_adm AUTO_INCREMENT = 1;
		DELETE FROM tm_detalle_venta;
		DELETE FROM comunicacion_baja;
		ALTER TABLE comunicacion_baja AUTO_INCREMENT = 1;
		DELETE FROM resumen_diario_detalle;
		ALTER TABLE resumen_diario_detalle AUTO_INCREMENT = 1;
		DELETE FROM resumen_diario;
		ALTER TABLE resumen_diario AUTO_INCREMENT = 1;			
		DELETE FROM tm_venta;
		ALTER TABLE tm_venta AUTO_INCREMENT = 1;
		DELETE FROM tm_aper_cierre;
		ALTER TABLE tm_aper_cierre AUTO_INCREMENT = 1;
		DELETE FROM tm_inventario_entsal;
		ALTER TABLE tm_inventario_entsal AUTO_INCREMENT = 1;
		DELETE FROM tm_inventario;
		ALTER TABLE tm_inventario AUTO_INCREMENT = 1;
		UPDATE tm_mesa SET estado = 'a' WHERE estado <> 'm';
		SELECT _cod1 AS cod;
		
	END IF;
	
	IF _flag = 3 THEN
	
		SELECT COUNT(*) INTO _cont FROM tm_detalle_venta;
		
		IF _cont = 0 THEN
			DELETE FROM tm_producto_ingr;
			ALTER TABLE tm_producto_ingr AUTO_INCREMENT = 1;
			DELETE FROM tm_producto_pres;
			ALTER TABLE tm_producto_pres AUTO_INCREMENT = 1;
			DELETE FROM tm_producto;
			ALTER TABLE tm_producto AUTO_INCREMENT = 1;
			DELETE FROM tm_producto_catg WHERE id_catg <> 1;
			ALTER TABLE tm_producto_catg AUTO_INCREMENT = 1;
			SELECT _cod1 AS cod;
		ELSE
			SELECT _cod0 AS cod;
		END IF;
		
	END IF;
	
	IF _flag = 4 THEN
	
		SELECT COUNT(*) INTO _cont FROM tm_producto_ingr;
		
		IF _cont = 0 THEN
			DELETE FROM tm_insumo;
			ALTER TABLE tm_insumo AUTO_INCREMENT = 1;
			DELETE FROM tm_insumo_catg;
			ALTER TABLE tm_insumo_catg AUTO_INCREMENT = 1;
			SELECT _cod1 AS cod;
		ELSE
			SELECT _cod0 AS cod;
		END IF;
		
	END IF;
	
	IF _flag = 5 THEN
	
		DELETE FROM tm_cliente where id_cliente <> 1;
		ALTER TABLE tm_cliente AUTO_INCREMENT = 2;
		SELECT _cod1 AS cod;
		
	END IF;
	
	IF _flag = 6 THEN
	
		DELETE FROM tm_proveedor;
		ALTER TABLE tm_proveedor AUTO_INCREMENT = 1;
		SELECT _cod1 AS cod;
		
	END IF;
	
	IF _flag = 7 THEN
	
		DELETE FROM tm_mesa;
		ALTER TABLE tm_mesa AUTO_INCREMENT = 1;
		DELETE FROM tm_salon;
		ALTER TABLE tm_salon AUTO_INCREMENT = 1;
		SELECT _cod1 AS cod;
		
	END IF;
			
    END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `usp_restCancelarPedido` (IN `_flag` INT(11), IN `_id_usu` INT(11), IN `_id_pres` INT(11), IN `_id_pedido` INT(11), IN `_estado_pedido` VARCHAR(5), IN `_fecha_pedido` DATETIME, IN `_fecha_envio` DATETIME, IN `_codigo_seguridad` VARCHAR(50), IN `_filtro_seguridad` VARCHAR(50))   BEGIN
	DECLARE _filtro INT DEFAULT 0;
	DECLARE _cod0 INT DEFAULT 0;
	DECLARE	_cod1 INT DEFAULT 1;
	DECLARE	_cod2 INT DEFAULT 2;
	
	IF _flag = 1 THEN
		/*
		SELECT COUNT(*) INTO _filtro FROM tm_detalle_pedido WHERE id_pedido = _id_pedido AND id_pres = _id_pres AND fecha_pedido = _fecha_pedido AND (_estado_pedido = 'a' OR _estado_pedido = 'y');
		*/
		iF _estado_pedido = 'a' or _estado_pedido = 'y' THEN		
			if _codigo_seguridad = _filtro_seguridad then
				UPDATE tm_detalle_pedido SET estado = 'z', id_usu = _id_usu, fecha_envio = _fecha_envio WHERE id_pedido = _id_pedido AND id_pres = _id_pres AND fecha_pedido = _fecha_pedido AND estado = _estado_pedido LIMIT 1;
				SELECT _cod1 AS cod;			
			else
				SELECT _cod0 AS cod;
			end if;			
		ELSE
			SELECT _cod2 AS cod;
		END IF;	
	END IF;
	
    END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `usp_restDesocuparMesa` (`_flag` INT(11), `_id_pedido` INT(11))   BEGIN
	DECLARE result INT DEFAULT 1;
	IF _flag = 1 THEN
		SELECT id_mesa INTO @codmesa FROM tm_pedido_mesa WHERE id_pedido = _id_pedido;
		UPDATE tm_mesa SET estado = 'a' WHERE id_mesa = @codmesa;
		UPDATE tm_pedido SET estado = 'z' WHERE id_pedido = _id_pedido;
		SELECT result AS resultado;
	END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `usp_restEditarVentaDocumento` (`_flag` INT(11), `_id_venta` INT(11), `_id_cliente` INT(11), `_id_tipo_documento` INT(11))   BEGIN
	DECLARE _cod INT DEFAULT 1;
	
	IF _flag = 1 THEN
		SELECT td.serie,CONCAT(LPAD(COUNT(id_venta)+(td.numero),8,'0')) AS numero INTO @serie, @numero
		FROM tm_venta AS v INNER JOIN tm_tipo_doc AS td ON v.id_tipo_doc = td.id_tipo_doc
		WHERE v.id_tipo_doc = _id_tipo_documento AND v.serie_doc = td.serie;
		UPDATE tm_venta SET id_cliente = _id_cliente, id_tipo_doc = _id_tipo_documento, serie_doc = @serie, nro_doc = @numero WHERE id_venta = _id_venta;
	END IF;
    END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `usp_restEmitirVenta` (`_flag` INT(11), `_dividir_cuenta` INT(11), `_id_pedido` INT(11), `_tipo_pedido` INT(11), `_tipo_entrega` VARCHAR(1), `_id_cliente` INT(11), `_id_tipo_doc` INT(11), `_id_tipo_pago` INT(11), `_id_usu` INT(11), `_id_apc` INT(11), `_pago_efe_none` DECIMAL(10,2), `_pago_tar` DECIMAL(10,2), `_descuento_tipo` CHAR(1), `_descuento_personal` INT(11), `_descuento_monto` DECIMAL(10,2), `_descuento_motivo` VARCHAR(200), `_comision_tarjeta` DECIMAL(10,2), `_comision_delivery` DECIMAL(10,2), `_iva` DECIMAL(10,2), `_total` DECIMAL(10,2), `_codigo_operacion` VARCHAR(20), `_fecha_venta` DATETIME)   BEGIN
	DECLARE pago_efe DECIMAL(10,2) DEFAULT 0;
	DECLARE pago_tar DECIMAL(10,2) DEFAULT 0;
	
	if (_descuento_tipo = 1 or _descuento_tipo = 3) then
		SET pago_efe = 0;
		SET pago_tar = 0;
	else 
		IF _id_tipo_pago = 1 THEN
			SET pago_efe = ( _total + _comision_delivery - _descuento_monto);
			SET pago_tar = 0;
		ELSEIF _id_tipo_pago = 2 THEN
			SET pago_efe = 0;
			SET pago_tar = ( _total + _comision_delivery - _descuento_monto);
		ELSEIF _id_tipo_pago = 3 THEN
			SET pago_efe = ( _total + _comision_delivery - _descuento_monto) - _pago_tar;
			SET pago_tar = _pago_tar;
		ELSE
			SET pago_efe = 0;
			SET pago_tar = ( _total + _comision_delivery - _descuento_monto);
		END IF;
	end if;
	
	IF _flag = 1 THEN
	
		SELECT td.serie,CONCAT(LPAD(COUNT(id_venta)+(td.numero),8,'0')) AS numero INTO @serie, @numero
		FROM tm_venta AS v INNER JOIN tm_tipo_doc AS td ON v.id_tipo_doc = td.id_tipo_doc
		WHERE v.id_tipo_doc = _id_tipo_doc AND v.serie_doc = td.serie;
		INSERT INTO tm_venta (id_pedido, id_tipo_pedido, id_cliente, id_tipo_doc, id_tipo_pago, id_usu, id_apc, serie_doc, nro_doc, pago_efe, pago_efe_none, pago_tar, descuento_tipo, descuento_personal, descuento_monto, descuento_motivo, comision_tarjeta, comision_delivery, iva, total, codigo_operacion, fecha_venta)
		VALUES (_id_pedido, _tipo_pedido, _id_cliente, _id_tipo_doc, _id_tipo_pago,_id_usu,_id_apc, @serie,@numero, pago_efe, _pago_efe_none, pago_tar, _descuento_tipo, _descuento_personal, _descuento_monto, _descuento_motivo, _comision_tarjeta, _comision_delivery, _iva, _total, _codigo_operacion, _fecha_venta );
		
		SELECT @@IDENTITY INTO @id;
		
		/* DIVIDIR CUENTA 1 = FALSE, 2 = TRUE */
		IF _dividir_cuenta = 1 THEN
		
			IF _tipo_pedido = 1 THEN	
				SELECT id_mesa INTO @idMesa FROM tm_pedido_mesa WHERE id_pedido = _id_pedido;
				UPDATE tm_mesa SET estado = 'a' WHERE id_mesa = @idMesa;
				UPDATE tm_pedido SET estado = 'd' WHERE id_pedido = _id_pedido;
			elseIF _tipo_pedido = 2 then
				UPDATE tm_pedido SET estado = 'b' WHERE id_pedido = _id_pedido;
				UPDATE tm_pedido_llevar SET fecha_entrega = _fecha_venta WHERE id_pedido = _id_pedido;
			ELSEIF _tipo_pedido = 3 THEN
			
				UPDATE tm_pedido SET id_apc = _id_apc, id_usu = _id_usu, estado = _tipo_entrega WHERE id_pedido = _id_pedido;
				
				if _tipo_entrega = 'c' then
					UPDATE tm_pedido_delivery SET fecha_envio = _fecha_venta WHERE id_pedido = _id_pedido;
				elseif _tipo_entrega = 'd' then
					UPDATE tm_pedido_delivery SET fecha_entrega = _fecha_venta WHERE id_pedido = _id_pedido;
				end if;
				/*
				UPDATE tm_pedido SET id_apc = _id_apc, id_usu = _id_usu, estado = 'b' WHERE id_pedido = _id_pedido;
				UPDATE tm_pedido_delivery SET fecha_preparacion = _fecha_venta WHERE id_pedido = _id_pedido;
				*/
			END IF;
			
		END IF;
			
		SELECT @id AS id_venta;
			
	END IF;
	
	END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `usp_restEmitirVentaDet` (`_flag` INT(11), `_id_venta` INT(11), `_id_pedido` INT(11), `_fecha` DATETIME)   BEGIN
    
	DECLARE _idprod INT; 
	DECLARE _cantidad1 INT;
	DECLARE _precio1 FLOAT;
	DECLARE _receta INT;
	DECLARE _tipopedido INT;
	DECLARE done INT DEFAULT 0;
	DECLARE primera CURSOR FOR SELECT dv.id_prod, SUM(dv.cantidad) AS cantidad, dv.precio, pp.receta, p.id_tipo FROM tm_detalle_venta AS dv INNER JOIN tm_producto_pres AS pp
	ON dv.id_prod = pp.id_pres LEFT JOIN tm_producto AS p ON pp.id_prod = p.id_prod WHERE dv.id_venta = _id_venta GROUP BY dv.id_prod;
	DECLARE segunda CURSOR FOR SELECT i.id_tipo_ins,i.id_ins,i.cant,v.ins_cos FROM tm_producto_ingr AS i INNER JOIN v_insprod AS v ON i.id_ins = v.id_ins AND i.id_tipo_ins = v.id_tipo_ins WHERE i.id_pres = _idprod;
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;
	
	OPEN primera;
	REPEAT
	
	FETCH primera INTO _idprod, _cantidad1, _precio1, _receta, _tipopedido;
	IF NOT done THEN
			
		UPDATE tm_detalle_pedido SET cantidad = (cantidad - _cantidad1) WHERE id_pedido = _id_pedido AND id_pres = _idprod AND estado <> 'i' LIMIT 1;
	
		IF _receta = 1 THEN
			
			IF _tipopedido = 2 THEN
				
				INSERT INTO tm_inventario (id_tipo_ope,id_ope,id_tipo_ins,id_ins,cos_uni,cant,fecha_r) VALUES (2,_id_venta,2,_idprod,_precio1,_cantidad1,_fecha);
			
			ELSEIF _tipopedido = 1 THEN
				
				block2: BEGIN
				
						DECLARE donesegunda INT DEFAULT 0;
						DECLARE _tipoinsumo2 INT;
						DECLARE _idinsumo2 INT;
						DECLARE xx FLOAT;
						DECLARE _cantidad2 FLOAT;
						DECLARE _precio2 FLOAT;
						DECLARE tercera CURSOR FOR SELECT i.id_tipo_ins,i.id_ins,i.cant,v.ins_cos FROM tm_producto_ingr AS i INNER JOIN v_insprod AS v ON i.id_ins = v.id_ins AND i.id_tipo_ins = v.id_tipo_ins WHERE i.id_pres = _idinsumo2;
						DECLARE CONTINUE HANDLER FOR NOT FOUND SET donesegunda = 1;
					
					OPEN segunda;
					REPEAT
			
					FETCH segunda INTO _tipoinsumo2,_idinsumo2,_cantidad2, _precio2;
						IF NOT donesegunda THEN
						
							IF _tipoinsumo2 = 1 OR _tipoinsumo2 = 2 THEN
							
								SET xx = _cantidad2 * _cantidad1;
								INSERT INTO tm_inventario (id_tipo_ope,id_ope,id_tipo_ins,id_ins,cos_uni,cant,fecha_r) VALUES (2,_id_venta,_tipoinsumo2,_idinsumo2,_precio2,xx,_fecha);
							
							ELSEIF _tipoinsumo2 = 3 then
							
								block3: BEGIN
										DECLARE donetercera INT DEFAULT 0;
										DECLARE _tipoinsumo3 INT;
										DECLARE _idinsumo3 INT;
										DECLARE yy FLOAT;
										DECLARE _cantidad3 FLOAT;
										DECLARE _precio3 FLOAT;
										DECLARE CONTINUE HANDLER FOR NOT FOUND SET donetercera = 1;
							
									OPEN tercera;
									REPEAT
							
									FETCH tercera INTO _tipoinsumo3,_idinsumo3,_cantidad3,_precio3;
										IF NOT donetercera THEN
											
										SET yy = _cantidad1 * _cantidad2 * _cantidad3;
										INSERT INTO tm_inventario (id_tipo_ope,id_ope,id_tipo_ins,id_ins,cos_uni,cant,fecha_r) VALUES (2,_id_venta,_tipoinsumo3,_idinsumo3,_precio3,yy,_fecha);
									
										END IF;
									UNTIL donetercera END REPEAT;
									CLOSE tercera;
									
								END block3;
								
							end if;
							
						END IF;
							
					UNTIL donesegunda END REPEAT;
					CLOSE segunda;
					
				END block2;
				
			END IF;
		END IF;	
	END IF;
	UNTIL done END REPEAT;
	CLOSE primera;
    END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `usp_restOpcionesMesa` (IN `_flag` INT(11), IN `_cod_mesa_origen` INT(11), IN `_cod_mesa_destino` INT(11))   BEGIN
	DECLARE _filtro INT DEFAULT 0;
	if _flag = 1 then
			
			SELECT COUNT(*) INTO _filtro FROM tm_mesa WHERE id_mesa = _cod_mesa_origen AND estado = 'i';
		
		if _filtro = 1 then 
			SELECT id_pedido INTO @cod FROM v_listar_mesas WHERE id_mesa = _cod_mesa_origen;
			UPDATE tm_mesa SET estado = 'a' WHERE id_mesa = _cod_mesa_origen;
			UPDATE tm_mesa SET estado = 'i' WHERE id_mesa = _cod_mesa_destino;
			UPDATE tm_pedido_mesa SET id_mesa = _cod_mesa_destino WHERE id_pedido = @cod;
			
			SELECT _filtro AS cod;
		ELSE
			SELECT _filtro AS cod;
		end if;
	end if;
	
	IF _flag = 2 THEN
			
			SELECT COUNT(*) INTO _filtro FROM tm_mesa WHERE id_mesa = _cod_mesa_origen AND estado = 'i';
		
		IF _filtro = 1 THEN 
			SELECT id_pedido INTO @cod_1 FROM v_listar_mesas WHERE id_mesa = _cod_mesa_origen;
			SELECT id_pedido INTO @cod_2 FROM v_listar_mesas WHERE id_mesa = _cod_mesa_destino;
			UPDATE tm_detalle_pedido SET id_pedido = @cod_2 WHERE id_pedido = @cod_1;
			
				if _cod_mesa_origen = _cod_mesa_destino then
					UPDATE tm_mesa SET estado = 'i' WHERE id_mesa = _cod_mesa_origen;
				else
					UPDATE tm_mesa SET estado = 'a' WHERE id_mesa = _cod_mesa_origen;
					UPDATE tm_pedido SET estado = 'z' WHERE id_pedido = @cod_1;
				end if;
			
			SELECT _filtro AS cod;
		ELSE
			SELECT _filtro AS cod;
		END IF;
	END IF;
    END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `usp_restRegCliente` (IN `_flag` INT(11), IN `_id_cliente` INT(11), IN `_tipo_cliente` INT(11), IN `_ci` VARCHAR(10), IN `_nit` VARCHAR(13), IN `_nombres` VARCHAR(200), IN `_razon_social` VARCHAR(100), IN `_telefono` INT(11), IN `_fecha_nac` DATE, IN `_correo` VARCHAR(100), IN `_direccion` VARCHAR(100), IN `_referencia` VARCHAR(100))   BEGIN
	DECLARE _filtro INT DEFAULT 1;
	DECLARE _numero_documento INT DEFAULT 0;
	
	IF _flag = 1 THEN
	
		IF _tipo_cliente = 1 THEN
			SELECT COUNT(*) INTO _filtro FROM tm_cliente WHERE ci = _ci;
			SET _numero_documento = _ci;
		ELSEIF _tipo_cliente = 2 THEN
			SELECT COUNT(*) INTO _filtro FROM tm_cliente WHERE nit = _nit;
			SET _numero_documento = '2';
		END IF;
	
		IF _filtro = 0 OR _numero_documento = '00000000' THEN
		
			INSERT INTO tm_cliente (tipo_cliente,ci,nit,nombres,razon_social,telefono,fecha_nac,correo,direccion,referencia) 
			VALUES (_tipo_cliente, _ci, _nit, _nombres, _razon_social, _telefono, _fecha_nac, _correo, _direccion, _referencia);
			
			SELECT @@IDENTITY INTO @id;
			
			SELECT _filtro AS cod,@id AS id_cliente;
		ELSE
			SELECT _filtro AS cod;
		END IF;
	END IF;
	
	IF _flag = 2 THEN
	
		UPDATE tm_cliente SET tipo_cliente = _tipo_cliente, ci = _ci, nit = _nit, nombres = _nombres, 
		razon_social = _razon_social, telefono = _telefono, fecha_nac = _fecha_nac, correo = _correo, direccion = _direccion, referencia = _referencia
		WHERE id_cliente = _id_cliente;
		
		SELECT _id_cliente AS id_cliente;
		
	END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `usp_restRegDelivery` (IN `_flag` INT(11), IN `_tipo_canal` INT(11), IN `_id_tipo_pedido` INT(11), IN `_id_apc` INT(11), IN `_id_usu` INT(11), IN `_fecha_pedido` DATETIME, IN `_id_cliente` INT(11), IN `_id_repartidor` INT(11), IN `_tipo_entrega` INT(11), IN `_tipo_pago` INT(11), IN `_pedido_programado` INT(11), IN `_hora_entrega` TIME, IN `_nombre_cliente` VARCHAR(100), IN `_telefono_cliente` VARCHAR(20), IN `_direccion_cliente` VARCHAR(100), IN `_referencia_cliente` VARCHAR(100), IN `_email_cliente` VARCHAR(200))   BEGIN
	DECLARE _filtro INT DEFAULT 1;
	
	IF _flag = 1 THEN
		
		INSERT INTO tm_pedido (id_tipo_pedido,id_apc,id_usu,fecha_pedido) VALUES (_id_tipo_pedido, _id_apc, _id_usu, _fecha_pedido);
		
		SELECT @@IDENTITY INTO @id;
		
		SELECT CONCAT(LPAD(count(t.nro_pedido)+1,5,'0')) AS codigo INTO @nro_pedido FROM tm_pedido_delivery AS t INNER JOIN tm_pedido AS p ON t.id_pedido = p.id_pedido WHERE p.id_tipo_pedido = 3 AND p.estado <> 'z'; 
		
			IF _id_cliente = 1 THEN
				INSERT INTO tm_cliente (tipo_cliente,nombres,telefono,direccion,referencia) VALUES (1,_nombre_cliente,_telefono_cliente,_direccion_cliente,_referencia_cliente);
				SELECT @@IDENTITY INTO @id_cliente;
				INSERT INTO tm_pedido_delivery (id_pedido,tipo_canal,id_cliente,id_repartidor,tipo_entrega,tipo_pago,pedido_programado,hora_entrega,nro_pedido,nombre_cliente,telefono_cliente,direccion_cliente,referencia_cliente,email_cliente) VALUES (@id, _tipo_canal, @id_cliente, _id_repartidor, _tipo_entrega, _tipo_pago, _pedido_programado, _hora_entrega, @nro_pedido, _nombre_cliente, _telefono_cliente, _direccion_cliente, _referencia_cliente, _email_cliente);
			ELSE
				UPDATE tm_cliente SET nombres = _nombre_cliente, telefono = _telefono_cliente, direccion = _direccion_cliente, referencia = _referencia_cliente WHERE id_cliente = _id_cliente; 		
				INSERT INTO tm_pedido_delivery (id_pedido,tipo_canal,id_cliente,id_repartidor,tipo_entrega,tipo_pago,pedido_programado,hora_entrega,nro_pedido,nombre_cliente,telefono_cliente,direccion_cliente,referencia_cliente,email_cliente) VALUES (@id, _tipo_canal, _id_cliente, _id_repartidor, _tipo_entrega, _tipo_pago, _pedido_programado, _hora_entrega, @nro_pedido, _nombre_cliente, _telefono_cliente, _direccion_cliente, _referencia_cliente, _email_cliente);
			END IF;
			
		SELECT _filtro AS fil, @id AS id_pedido;
	
	END IF;
    END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `usp_restRegMesa` (IN `_flag` INT(11), IN `_id_tipo_pedido` INT(11), IN `_id_apc` INT(11), IN `_id_usu` INT(11), IN `_fecha_pedido` DATETIME, IN `_id_mesa` INT(11), IN `_id_mozo` INT(11), IN `_nomb_cliente` VARCHAR(45), IN `_nro_personas` INT(11))   BEGIN
	DECLARE _filtro INT DEFAULT 0;
	
		IF _flag = 1 THEN
		
			SELECT COUNT(*) INTO _filtro FROM tm_mesa WHERE id_mesa = _id_mesa AND estado = 'a';
			
			if _filtro = 1 THEN
				
				INSERT INTO tm_pedido (id_tipo_pedido,id_apc,id_usu,fecha_pedido) VALUES (_id_tipo_pedido, _id_apc, _id_usu, _fecha_pedido);
				
				SELECT @@IDENTITY INTO @id;
				
				INSERT INTO tm_pedido_mesa (id_pedido,id_mesa,id_mozo,nomb_cliente,nro_personas) VALUES (@id, _id_mesa, _id_mozo, _nomb_cliente, _nro_personas);
				
				SELECT _filtro AS fil, @id AS id_pedido;
				
				UPDATE tm_mesa SET estado = 'i' WHERE id_mesa = _id_mesa;
			ELSE
				SELECT _filtro AS fil;
			END IF;
		END IF;
	END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `usp_restRegMostrador` (IN `_flag` INT(11), IN `_id_tipo_pedido` INT(11), IN `_id_apc` INT(11), IN `_id_usu` INT(11), IN `_fecha_pedido` DATETIME, IN `_nomb_cliente` VARCHAR(45))   BEGIN
	DECLARE _filtro INT DEFAULT 1;
	
	IF _flag = 1 THEN
		
		INSERT INTO tm_pedido (id_tipo_pedido,id_apc,id_usu,fecha_pedido) VALUES (_id_tipo_pedido, _id_apc, _id_usu, _fecha_pedido);
		
		SELECT @@IDENTITY INTO @id;
		
		SELECT CONCAT(LPAD(count(t.nro_pedido)+1,5,'0')) AS codigo INTO @nro_pedido FROM tm_pedido_llevar AS t INNER JOIN tm_pedido AS p ON t.id_pedido = p.id_pedido WHERE p.id_tipo_pedido = 2 and p.estado <> 'z'; 
		
		INSERT INTO tm_pedido_llevar (id_pedido,nro_pedido,nomb_cliente) VALUES (@id, @nro_pedido, _nomb_cliente);
		
		SELECT _filtro AS fil, @id AS id_pedido;
	
	END IF;
	END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `usp_tableroControl` (IN `_flag` INT(11), IN `_codDia` INT(11), IN `_fecha` DATE, IN `_feSei` DATE, IN `_feCin` DATE, IN `_feCua` DATE, IN `_feTre` DATE, IN `_feDos` DATE, IN `_feUno` DATE)   BEGIN
	if _flag = 1 then
				SELECT dia,margen into @dia,@margen FROM tm_margen_venta WHERE cod_dia = _codDia;
				SELECT IFNULL(SUM(total-descuento),0) into @siete FROM tm_venta WHERE DATE(fecha_venta) = _fecha;
				SELECT IFNULL(SUM(total-descuento),0) into @seis FROM tm_venta WHERE DATE(fecha_venta) = _feSei;
				SELECT IFNULL(SUM(total-descuento),0) into @cinco FROM tm_venta WHERE DATE(fecha_venta) = _feCin;
				SELECT IFNULL(SUM(total-descuento),0) into @cuatro FROM tm_venta WHERE DATE(fecha_venta) = _feCua;
				SELECT IFNULL(SUM(total-descuento),0) into @tres FROM tm_venta WHERE DATE(fecha_venta) = _feTre;
				SELECT IFNULL(SUM(total-descuento),0) into @dos FROM tm_venta WHERE DATE(fecha_venta) = _feDos;
				SELECT IFNULL(SUM(total-descuento),0) into @uno FROM tm_venta WHERE DATE(fecha_venta) = _feUno;
		
		select @dia as dia,@margen as margen,@siete as siete,@seis as seis,@cinco as cinco,@cuatro as cuatro,@tres as tres,@dos as dos,@uno as uno;	
	end if;
    END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `comunicacion_baja`
--

CREATE TABLE `comunicacion_baja` (
  `id_comunicacion` int(11) NOT NULL,
  `fecha_registro` datetime DEFAULT NULL,
  `fecha_baja` date DEFAULT NULL,
  `fecha_referencia` date DEFAULT NULL,
  `tipo_doc` char(2) DEFAULT NULL,
  `serie_doc` char(4) DEFAULT NULL,
  `num_doc` varchar(8) DEFAULT NULL,
  `nombre_baja` varchar(200) DEFAULT NULL,
  `correlativo` varchar(5) DEFAULT NULL,
  `enviado_impuestos` char(1) DEFAULT NULL,
  `hash_cpe` varchar(100) DEFAULT NULL,
  `hash_cdr` varchar(100) DEFAULT NULL,
  `code_respuesta_impuestos` varchar(5) DEFAULT NULL,
  `descripcion_impuestos_cdr` varchar(300) DEFAULT NULL,
  `name_file_impuestos` varchar(80) DEFAULT NULL,
  `estado` varchar(12) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `resumen_diario`
--

CREATE TABLE `resumen_diario` (
  `id_resumen` int(11) NOT NULL,
  `fecha_registro` datetime DEFAULT NULL,
  `fecha_resumen` date DEFAULT NULL,
  `fecha_referencia` date DEFAULT NULL,
  `correlativo` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `enviado_impuestos` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `hash_cpe` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `hash_cdr` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `code_respuesta_impuestos` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `descripcion_impuestos_cdr` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `name_file_impuestos` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `estado` varchar(12) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `resumen_diario_detalle`
--

CREATE TABLE `resumen_diario_detalle` (
  `id_detalle` int(11) NOT NULL,
  `id_resumen` int(11) DEFAULT NULL,
  `id_venta` int(11) DEFAULT NULL,
  `status_code` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tm_almacen`
--

CREATE TABLE `tm_almacen` (
  `id_alm` int(11) NOT NULL,
  `nombre` varchar(45) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `estado` varchar(5) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT 'a'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `tm_almacen`
--

INSERT INTO `tm_almacen` (`id_alm`, `nombre`, `estado`) VALUES
(1, 'ABARROTES E INSUMOS', 'a'),
(2, 'BEBIDAS, GASEOSAS Y CERVEZAS', 'a');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tm_aper_cierre`
--

CREATE TABLE `tm_aper_cierre` (
  `id_apc` int(11) NOT NULL,
  `id_usu` int(11) NOT NULL,
  `id_caja` int(11) NOT NULL,
  `id_turno` int(11) NOT NULL,
  `fecha_aper` datetime DEFAULT NULL,
  `monto_aper` decimal(10,2) DEFAULT 0.00,
  `fecha_cierre` datetime DEFAULT NULL,
  `monto_cierre` decimal(10,2) DEFAULT 0.00,
  `monto_sistema` decimal(10,2) DEFAULT 0.00,
  `stock_pollo` varchar(11) NOT NULL DEFAULT '0',
  `estado` varchar(5) DEFAULT 'a'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `tm_aper_cierre`
--

INSERT INTO `tm_aper_cierre` (`id_apc`, `id_usu`, `id_caja`, `id_turno`, `fecha_aper`, `monto_aper`, `fecha_cierre`, `monto_cierre`, `monto_sistema`, `stock_pollo`, `estado`) VALUES
(1, 49, 3, 1, '2023-10-25 02:27:42', 0.00, '2023-10-25 08:17:57', 45.00, 45.00, '', 'c'),
(2, 60, 3, 1, '2023-10-25 08:18:22', 0.00, '2023-10-30 01:42:02', 0.00, 70.00, '', 'c'),
(3, 49, 1, 1, '2023-10-25 10:05:30', 0.00, '2023-10-25 10:09:53', 45.00, 55.00, '', 'c'),
(4, 52, 1, 1, '2023-10-25 10:10:33', 0.00, '2023-10-30 01:47:39', 0.00, 0.00, '', 'c'),
(5, 60, 3, 1, '2023-10-30 01:48:51', 0.00, NULL, 0.00, 0.00, '0', 'a');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tm_area_prod`
--

CREATE TABLE `tm_area_prod` (
  `id_areap` int(11) NOT NULL,
  `id_imp` int(11) NOT NULL,
  `nombre` varchar(45) NOT NULL,
  `estado` varchar(5) NOT NULL DEFAULT 'a'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `tm_area_prod`
--

INSERT INTO `tm_area_prod` (`id_areap`, `id_imp`, `nombre`, `estado`) VALUES
(1, 2, 'COCINA', 'a'),
(2, 3, 'BAR', 'a'),
(3, 7, 'CAJA', 'a'),
(4, 6, 'PARRILLA', 'a');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tm_caja`
--

CREATE TABLE `tm_caja` (
  `id_caja` int(11) NOT NULL,
  `descripcion` varchar(45) NOT NULL,
  `estado` varchar(5) DEFAULT 'a'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `tm_caja`
--

INSERT INTO `tm_caja` (`id_caja`, `descripcion`, `estado`) VALUES
(1, 'CAJA PRINCIPAL', 'a'),
(3, 'CAJA1', 'a');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tm_cliente`
--

CREATE TABLE `tm_cliente` (
  `id_cliente` int(11) NOT NULL,
  `tipo_cliente` int(11) NOT NULL,
  `ci` varchar(10) NOT NULL DEFAULT '00000000',
  `nit` varchar(13) NOT NULL,
  `nombres` varchar(100) NOT NULL,
  `razon_social` varchar(100) NOT NULL,
  `telefono` int(11) NOT NULL,
  `fecha_nac` date NOT NULL,
  `correo` varchar(100) NOT NULL,
  `direccion` varchar(100) NOT NULL DEFAULT 'S/DIRECCION',
  `referencia` varchar(100) NOT NULL,
  `estado` varchar(5) NOT NULL DEFAULT 'a'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `tm_cliente`
--

INSERT INTO `tm_cliente` (`id_cliente`, `tipo_cliente`, `ci`, `nit`, `nombres`, `razon_social`, `telefono`, `fecha_nac`, `correo`, `direccion`, `referencia`, `estado`) VALUES
(1, 1, '00000000', '', 'PUBLICO EN GENERAL', '', 0, '1970-01-01', '', '-', '', 'a'),
(2, 1, '1224567', '', 'ALISO GUZMAN', '', 0, '1970-01-01', '', '', '', 'a'),
(3, 1, '4444444', '', 'RODRIGO CUELHO', '', 0, '1970-01-01', '', 'Av/Fernandez Molina', '', 'a'),
(4, 2, '', '1234567890', '', 'HEROES DE LA DISTANCIA', 0, '1970-01-01', '', 'Av/Circunvalacion', '', 'a'),
(5, 1, '4567899', '', 'JOEL CHAO PAZ', '', 0, '1970-01-01', '', 'AV. 9 DE FEBRERO', '', 'a'),
(6, 1, '4445678', '', 'DORA GUTIERREZ', '', 0, '1970-01-01', '', 'C/Buyuyumanu', '', 'a'),
(7, 2, '', '1111111111', '', 'LA PALLOZA', 77777777, '1970-01-01', '', 'C/Bahia', '', 'a');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tm_compra`
--

CREATE TABLE `tm_compra` (
  `id_compra` int(11) NOT NULL,
  `id_prov` int(11) NOT NULL,
  `id_tipo_compra` int(11) NOT NULL,
  `id_tipo_doc` int(11) NOT NULL,
  `id_usu` int(11) DEFAULT NULL,
  `fecha_c` date DEFAULT NULL,
  `hora_c` varchar(45) DEFAULT NULL,
  `serie_doc` varchar(45) DEFAULT NULL,
  `num_doc` varchar(45) DEFAULT NULL,
  `iva` decimal(10,2) DEFAULT NULL,
  `total` decimal(10,2) DEFAULT NULL,
  `descuento` decimal(10,2) DEFAULT NULL,
  `estado` varchar(1) DEFAULT 'a',
  `observaciones` varchar(100) DEFAULT NULL,
  `fecha_reg` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tm_compra_credito`
--

CREATE TABLE `tm_compra_credito` (
  `id_credito` int(11) NOT NULL,
  `id_compra` int(11) NOT NULL,
  `total` decimal(10,2) DEFAULT NULL,
  `interes` decimal(10,2) DEFAULT NULL,
  `fecha` date DEFAULT NULL,
  `estado` varchar(5) DEFAULT 'p'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tm_compra_detalle`
--

CREATE TABLE `tm_compra_detalle` (
  `id_compra` int(11) NOT NULL,
  `id_tp` int(11) NOT NULL,
  `id_pres` int(11) NOT NULL,
  `cant` decimal(10,2) NOT NULL,
  `precio` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tm_configuracion`
--

CREATE TABLE `tm_configuracion` (
  `id_cfg` int(11) NOT NULL,
  `zona_hora` varchar(100) DEFAULT NULL,
  `trib_acr` varchar(20) DEFAULT NULL,
  `trib_car` int(5) DEFAULT NULL,
  `di_acr` varchar(20) DEFAULT NULL,
  `di_car` int(5) DEFAULT NULL,
  `imp_acr` varchar(20) DEFAULT NULL,
  `imp_val` decimal(10,2) DEFAULT NULL,
  `mon_acr` varchar(20) DEFAULT NULL,
  `mon_val` varchar(5) DEFAULT NULL,
  `pc_name` varchar(50) DEFAULT NULL,
  `pc_ip` varchar(20) DEFAULT NULL,
  `print_com` int(1) DEFAULT NULL,
  `print_pre` int(1) DEFAULT NULL,
  `print_cpe` int(1) DEFAULT NULL,
  `opc_01` int(1) DEFAULT NULL,
  `opc_02` int(1) DEFAULT NULL,
  `opc_03` int(1) DEFAULT NULL,
  `bloqueo` int(1) DEFAULT NULL,
  `cod_seg` varchar(45) NOT NULL DEFAULT '123456'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `tm_configuracion`
--

INSERT INTO `tm_configuracion` (`id_cfg`, `zona_hora`, `trib_acr`, `trib_car`, `di_acr`, `di_car`, `imp_acr`, `imp_val`, `mon_acr`, `mon_val`, `pc_name`, `pc_ip`, `print_com`, `print_pre`, `print_cpe`, `opc_01`, `opc_02`, `opc_03`, `bloqueo`, `cod_seg`) VALUES
(1, 'America/LA_PAZ', 'NIT', 10, 'CI', 7, 'IVA', 0.18, 'Boliviano', 'Bs/', 'http://localhost', '192.168.1.7', 0, 0, 0, 0, 0, 1, 0, '123456');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tm_credito_detalle`
--

CREATE TABLE `tm_credito_detalle` (
  `id_credito` int(11) DEFAULT NULL,
  `id_usu` int(11) DEFAULT NULL,
  `importe` decimal(10,2) DEFAULT NULL,
  `fecha` datetime DEFAULT NULL,
  `egreso` int(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tm_detalle_pedido`
--

CREATE TABLE `tm_detalle_pedido` (
  `id_pedido` int(11) NOT NULL,
  `id_usu` int(11) NOT NULL,
  `id_pres` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `cant` int(11) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `comentario` varchar(100) NOT NULL,
  `fecha_pedido` datetime NOT NULL,
  `fecha_envio` datetime NOT NULL,
  `estado` varchar(5) NOT NULL DEFAULT 'a'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `tm_detalle_pedido`
--

INSERT INTO `tm_detalle_pedido` (`id_pedido`, `id_usu`, `id_pres`, `cantidad`, `cant`, `precio`, `comentario`, `fecha_pedido`, `fecha_envio`, `estado`) VALUES
(1, 49, 4, 0, 2, 15.00, '', '2023-10-25 02:28:13', '0000-00-00 00:00:00', 'a'),
(2, 49, 1, 0, 1, 15.00, '', '2023-10-25 03:07:25', '0000-00-00 00:00:00', 'a'),
(4, 49, 4, 0, 1, 15.00, '', '2023-10-25 08:11:01', '0000-00-00 00:00:00', 'a'),
(4, 49, 1, 0, 1, 15.00, '', '2023-10-25 08:11:01', '0000-00-00 00:00:00', 'a'),
(5, 51, 2, 0, 1, 40.00, '', '2023-10-25 08:20:19', '2023-10-25 08:21:46', 'b'),
(6, 49, 1, 0, 1, 15.00, '', '2023-10-25 10:06:26', '0000-00-00 00:00:00', 'a'),
(6, 49, 2, 0, 1, 40.00, '', '2023-10-25 10:06:26', '0000-00-00 00:00:00', 'a'),
(8, 59, 6, 1, 1, 40.00, '', '2023-10-30 01:35:46', '0000-00-00 00:00:00', 'y'),
(8, 59, 7, 1, 1, 50.00, '', '2023-10-30 01:35:46', '0000-00-00 00:00:00', 'y'),
(10, 60, 7, 0, 1, 50.00, '', '2023-10-30 01:51:04', '0000-00-00 00:00:00', 'a'),
(11, 59, 8, 0, 1, 50.00, '', '2023-10-30 01:55:44', '0000-00-00 00:00:00', 'a'),
(11, 59, 5, 0, 1, 50.00, '', '2023-10-30 01:55:44', '0000-00-00 00:00:00', 'a'),
(12, 59, 9, 0, 1, 70.00, '', '2023-10-30 02:00:37', '0000-00-00 00:00:00', 'a'),
(13, 59, 4, 0, 1, 15.00, '', '2023-10-30 02:05:29', '0000-00-00 00:00:00', 'a'),
(9, 60, 2, 0, 1, 40.00, '', '2023-10-30 02:08:11', '0000-00-00 00:00:00', 'a');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tm_detalle_venta`
--

CREATE TABLE `tm_detalle_venta` (
  `id_venta` int(11) NOT NULL,
  `id_prod` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `tm_detalle_venta`
--

INSERT INTO `tm_detalle_venta` (`id_venta`, `id_prod`, `cantidad`, `precio`) VALUES
(1, 4, 2, 15.00),
(2, 1, 1, 15.00),
(3, 1, 1, 15.00),
(3, 4, 1, 15.00),
(4, 2, 1, 40.00),
(5, 1, 1, 15.00),
(5, 2, 1, 40.00),
(6, 7, 1, 50.00),
(7, 5, 1, 50.00),
(7, 8, 1, 50.00),
(8, 9, 1, 70.00),
(9, 4, 1, 15.00),
(10, 2, 1, 40.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tm_empresa`
--

CREATE TABLE `tm_empresa` (
  `id_de` int(11) NOT NULL,
  `nit` varchar(20) DEFAULT NULL,
  `razon_social` varchar(200) DEFAULT NULL,
  `nombre_comercial` varchar(200) DEFAULT NULL,
  `direccion_comercial` varchar(200) DEFAULT NULL,
  `direccion_fiscal` varchar(200) DEFAULT NULL,
  `ubigeo` varchar(8) DEFAULT NULL,
  `departamento` varchar(50) DEFAULT NULL,
  `provincia` varchar(50) DEFAULT NULL,
  `distrito` varchar(50) DEFAULT NULL,
  `impuestos` int(1) NOT NULL,
  `modo` int(1) DEFAULT NULL,
  `usuariosol` varchar(50) DEFAULT NULL,
  `clavesol` varchar(50) DEFAULT NULL,
  `clavecertificado` varchar(50) DEFAULT NULL,
  `client_id` varchar(45) DEFAULT NULL,
  `client_secret` varchar(45) DEFAULT NULL,
  `logo` varchar(45) DEFAULT NULL,
  `celular` varchar(50) DEFAULT NULL,
  `email` varchar(120) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `tm_empresa`
--

INSERT INTO `tm_empresa` (`id_de`, `nit`, `razon_social`, `nombre_comercial`, `direccion_comercial`, `direccion_fiscal`, `ubigeo`, `departamento`, `provincia`, `distrito`, `impuestos`, `modo`, `usuariosol`, `clavesol`, `clavecertificado`, `client_id`, `client_secret`, `logo`, `celular`, `email`) VALUES
(1, '2020202020', 'MI PERU', 'MI PERU', 'AV. 9 DE FEBRERO ', 'AV. 9 DE FEBRERO', '150132', 'PANDO', 'NICOLAS SUAREZ', 'COBIJA', 0, 3, 'MODDATOS', 'MODDATOS', 'MODDATOS', 'MODDATOS', 'MODDATOS', 'logoprint.jpg', '74776035', '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tm_gastos_adm`
--

CREATE TABLE `tm_gastos_adm` (
  `id_ga` int(11) NOT NULL,
  `id_tipo_gasto` int(11) NOT NULL,
  `id_usu` int(11) NOT NULL,
  `id_apc` int(11) NOT NULL,
  `id_per` int(11) DEFAULT NULL,
  `importe` decimal(10,2) DEFAULT NULL,
  `responsable` varchar(100) DEFAULT NULL,
  `motivo` varchar(100) DEFAULT NULL,
  `fecha_registro` datetime DEFAULT NULL,
  `estado` varchar(5) DEFAULT 'a'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tm_impresora`
--

CREATE TABLE `tm_impresora` (
  `id_imp` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `ip` varchar(25) DEFAULT NULL,
  `estado` varchar(5) NOT NULL DEFAULT 'a'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `tm_impresora`
--

INSERT INTO `tm_impresora` (`id_imp`, `nombre`, `ip`, `estado`) VALUES
(1, 'NINGUNO', NULL, 'a'),
(2, 'COCINA', '192.168.1.7', 'a'),
(3, 'BAR', NULL, 'a'),
(6, 'PARRILLA', NULL, 'a'),
(7, 'CAJA', NULL, 'a');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tm_ingresos_adm`
--

CREATE TABLE `tm_ingresos_adm` (
  `id_ing` int(11) NOT NULL,
  `id_usu` int(11) NOT NULL,
  `id_apc` int(11) NOT NULL,
  `importe` decimal(10,2) DEFAULT NULL,
  `responsable` varchar(100) DEFAULT NULL,
  `motivo` varchar(200) DEFAULT NULL,
  `fecha_reg` datetime DEFAULT NULL,
  `estado` varchar(5) DEFAULT 'a'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tm_insumo`
--

CREATE TABLE `tm_insumo` (
  `id_ins` int(11) NOT NULL,
  `id_catg` int(11) NOT NULL,
  `id_med` int(11) NOT NULL,
  `cod_ins` varchar(10) DEFAULT NULL,
  `nomb_ins` varchar(45) DEFAULT NULL,
  `stock_min` int(11) DEFAULT NULL,
  `cos_uni` decimal(10,2) DEFAULT NULL,
  `estado` varchar(5) DEFAULT 'a'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tm_insumo_catg`
--

CREATE TABLE `tm_insumo_catg` (
  `id_catg` int(11) NOT NULL,
  `descripcion` varchar(45) NOT NULL,
  `estado` varchar(5) DEFAULT 'a'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tm_inventario`
--

CREATE TABLE `tm_inventario` (
  `id_inv` int(11) NOT NULL,
  `id_tipo_ope` int(11) NOT NULL,
  `id_ope` int(11) NOT NULL,
  `id_tipo_ins` int(11) NOT NULL,
  `id_ins` int(11) NOT NULL,
  `cos_uni` decimal(10,2) NOT NULL,
  `cant` float NOT NULL,
  `fecha_r` datetime NOT NULL,
  `estado` varchar(5) NOT NULL DEFAULT 'a'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tm_inventario_entsal`
--

CREATE TABLE `tm_inventario_entsal` (
  `id_es` int(11) NOT NULL,
  `id_usu` int(11) NOT NULL,
  `id_tipo` int(11) NOT NULL,
  `id_responsable` int(11) NOT NULL,
  `motivo` varchar(200) NOT NULL,
  `fecha` datetime NOT NULL,
  `estado` varchar(5) NOT NULL DEFAULT 'a'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tm_margen_venta`
--

CREATE TABLE `tm_margen_venta` (
  `id` int(11) NOT NULL,
  `cod_dia` int(11) NOT NULL,
  `dia` varchar(45) NOT NULL,
  `margen` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `tm_margen_venta`
--

INSERT INTO `tm_margen_venta` (`id`, `cod_dia`, `dia`, `margen`) VALUES
(1, 1, 'Lunes', 150.00),
(2, 2, 'Martes', 750.00),
(3, 3, 'Miércoles', 750.00),
(4, 4, 'Jueves', 850.00),
(5, 5, 'Viernes', 1200.00),
(6, 6, 'Sábado', 1800.00),
(7, 0, 'Domingo', 2500.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tm_mesa`
--

CREATE TABLE `tm_mesa` (
  `id_mesa` int(11) NOT NULL,
  `id_salon` int(11) NOT NULL,
  `nro_mesa` varchar(5) NOT NULL,
  `estado` varchar(45) NOT NULL DEFAULT 'a'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `tm_mesa`
--

INSERT INTO `tm_mesa` (`id_mesa`, `id_salon`, `nro_mesa`, `estado`) VALUES
(1, 1, '1', 'a'),
(2, 1, '2', 'a'),
(3, 1, '3', 'a'),
(4, 1, '4', 'a'),
(5, 1, '5', 'a'),
(6, 1, '6', 'a'),
(7, 1, '7', 'a'),
(8, 1, '8', 'a'),
(9, 1, '9', 'a'),
(10, 1, '10', 'a'),
(11, 2, '1', 'a'),
(12, 2, '2', 'a'),
(13, 2, '3', 'a'),
(14, 3, '1', 'a'),
(15, 3, '2', 'a'),
(16, 3, '3', 'a');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tm_pago`
--

CREATE TABLE `tm_pago` (
  `id_pago` int(2) NOT NULL,
  `descripcion` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `tm_pago`
--

INSERT INTO `tm_pago` (`id_pago`, `descripcion`) VALUES
(1, 'EFECTIVO'),
(2, 'TARJETAS'),
(3, 'MIXTO'),
(4, 'EN LINEA'),
(5, 'TRANSFERENCIAS'),
(6, 'VALES');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tm_pedido`
--

CREATE TABLE `tm_pedido` (
  `id_pedido` int(11) NOT NULL,
  `id_tipo_pedido` int(11) NOT NULL,
  `id_apc` int(11) DEFAULT NULL,
  `id_usu` int(11) NOT NULL,
  `fecha_pedido` datetime NOT NULL,
  `estado` varchar(5) NOT NULL DEFAULT 'a'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `tm_pedido`
--

INSERT INTO `tm_pedido` (`id_pedido`, `id_tipo_pedido`, `id_apc`, `id_usu`, `fecha_pedido`, `estado`) VALUES
(1, 1, 1, 49, '2023-10-25 02:28:07', 'd'),
(2, 1, 1, 49, '2023-10-25 03:07:12', 'd'),
(3, 1, 1, 49, '2023-10-25 03:11:28', 'z'),
(4, 1, 1, 49, '2023-10-25 08:10:50', 'd'),
(5, 1, NULL, 51, '2023-10-25 08:20:08', 'd'),
(6, 1, 3, 49, '2023-10-25 10:05:49', 'd'),
(7, 1, NULL, 59, '2023-10-30 01:06:12', 'z'),
(8, 3, NULL, 59, '2023-10-30 01:35:34', 'a'),
(9, 2, NULL, 59, '2023-10-30 01:40:10', 'b'),
(10, 2, 5, 60, '2023-10-30 01:50:50', 'b'),
(11, 1, NULL, 59, '2023-10-30 01:55:30', 'd'),
(12, 1, NULL, 59, '2023-10-30 02:00:23', 'd'),
(13, 2, NULL, 59, '2023-10-30 02:05:21', 'b');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tm_pedido_delivery`
--

CREATE TABLE `tm_pedido_delivery` (
  `id_pedido` int(11) NOT NULL,
  `tipo_canal` int(11) NOT NULL,
  `id_cliente` int(11) NOT NULL,
  `id_repartidor` int(11) NOT NULL,
  `tipo_pago` int(11) NOT NULL,
  `tipo_entrega` int(11) NOT NULL,
  `pedido_programado` int(11) DEFAULT 0,
  `hora_entrega` time DEFAULT '00:00:00',
  `paga_con` decimal(10,2) NOT NULL,
  `comision_delivery` decimal(10,2) NOT NULL,
  `amortizacion` decimal(10,2) NOT NULL,
  `nro_pedido` varchar(10) NOT NULL,
  `nombre_cliente` varchar(100) NOT NULL,
  `telefono_cliente` varchar(20) NOT NULL,
  `direccion_cliente` varchar(100) NOT NULL,
  `referencia_cliente` varchar(100) NOT NULL,
  `email_cliente` varchar(200) NOT NULL,
  `fecha_preparacion` datetime NOT NULL,
  `fecha_envio` datetime NOT NULL,
  `fecha_entrega` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `tm_pedido_delivery`
--

INSERT INTO `tm_pedido_delivery` (`id_pedido`, `tipo_canal`, `id_cliente`, `id_repartidor`, `tipo_pago`, `tipo_entrega`, `pedido_programado`, `hora_entrega`, `paga_con`, `comision_delivery`, `amortizacion`, `nro_pedido`, `nombre_cliente`, `telefono_cliente`, `direccion_cliente`, `referencia_cliente`, `email_cliente`, `fecha_preparacion`, `fecha_envio`, `fecha_entrega`) VALUES
(8, 1, 2, 1, 1, 2, 1, '14:00:00', 0.00, 0.00, 0.00, '00001', 'ALISO GUZMAN', '0', '', '', 'example@email.com', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tm_pedido_llevar`
--

CREATE TABLE `tm_pedido_llevar` (
  `id_pedido` int(11) NOT NULL,
  `nro_pedido` varchar(10) NOT NULL,
  `nomb_cliente` varchar(100) NOT NULL,
  `fecha_entrega` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `tm_pedido_llevar`
--

INSERT INTO `tm_pedido_llevar` (`id_pedido`, `nro_pedido`, `nomb_cliente`, `fecha_entrega`) VALUES
(9, '00001', 'VENTA RAPIDA - ADMIN', '2023-10-30 02:08:17'),
(10, '00002', 'GRABRIELA', '2023-10-30 01:51:33'),
(13, '00003', 'ALISON ROMERO', '2023-10-30 02:07:14');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tm_pedido_mesa`
--

CREATE TABLE `tm_pedido_mesa` (
  `id_pedido` int(11) NOT NULL,
  `id_mesa` int(11) NOT NULL,
  `id_mozo` int(11) NOT NULL,
  `nomb_cliente` varchar(45) NOT NULL,
  `nro_personas` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `tm_pedido_mesa`
--

INSERT INTO `tm_pedido_mesa` (`id_pedido`, `id_mesa`, `id_mozo`, `nomb_cliente`, `nro_personas`) VALUES
(1, 1, 59, 'Mesa: 1', 1),
(2, 1, 59, 'Mesa: 1', 1),
(3, 14, 59, 'Mesa: 1', 1),
(4, 6, 59, 'Mesa: 6', 1),
(5, 1, 51, 'Mesa 1', 1),
(6, 7, 51, 'Mesa: 7', 1),
(7, 1, 59, 'Mesa 1', 1),
(11, 1, 59, 'Mesa 1', 2),
(12, 2, 59, 'Mesa 2', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tm_producto`
--

CREATE TABLE `tm_producto` (
  `id_prod` int(11) NOT NULL,
  `id_tipo` int(11) NOT NULL,
  `id_catg` int(11) NOT NULL DEFAULT 0,
  `id_areap` int(11) NOT NULL,
  `nombre` varchar(45) DEFAULT NULL,
  `notas` varchar(200) DEFAULT NULL,
  `descripcion` varchar(200) DEFAULT NULL,
  `delivery` int(1) DEFAULT 0,
  `estado` varchar(1) DEFAULT 'a',
  `cod_pro` varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `tm_producto`
--

INSERT INTO `tm_producto` (`id_prod`, `id_tipo`, `id_catg`, `id_areap`, `nombre`, `notas`, `descripcion`, `delivery`, `estado`, `cod_pro`) VALUES
(1, 1, 2, 1, 'PECHO', '', NULL, 0, 'a', ''),
(2, 1, 3, 1, 'CHURRASCO', '', NULL, 0, 'a', ''),
(3, 1, 2, 1, 'PIERNA', '', NULL, 0, 'a', ''),
(4, 1, 4, 1, 'CEVICHE DE PESCADO', '', NULL, 0, 'a', ''),
(5, 1, 4, 1, 'LECHE DE TIGRE', '', NULL, 0, 'a', ''),
(6, 1, 4, 1, 'ARROZ CHAUFA DE POLLO', '', NULL, 0, 'a', ''),
(7, 1, 4, 1, 'ARROZ CHAUFA DE CARNE', '', NULL, 0, 'a', ''),
(8, 1, 4, 1, 'ARROZ CHAUFA ESPECIAL', '', NULL, 0, 'a', ''),
(9, 1, 4, 1, 'LOMO SALTADO', '', NULL, 0, 'a', ''),
(10, 1, 4, 1, 'TALLARIN SALTADO DE POLLO', '', NULL, 0, 'a', ''),
(11, 1, 4, 1, 'TALLARIN SALTADO DE CARNE', '', NULL, 0, 'a', ''),
(12, 1, 4, 1, 'TALLARIN MIXTO', '', NULL, 0, 'a', '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tm_producto_catg`
--

CREATE TABLE `tm_producto_catg` (
  `id_catg` int(11) NOT NULL,
  `descripcion` varchar(45) NOT NULL,
  `delivery` int(1) NOT NULL DEFAULT 0,
  `orden` int(11) NOT NULL DEFAULT 100,
  `imagen` varchar(200) NOT NULL DEFAULT 'default.png',
  `estado` varchar(1) NOT NULL DEFAULT 'a'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `tm_producto_catg`
--

INSERT INTO `tm_producto_catg` (`id_catg`, `descripcion`, `delivery`, `orden`, `imagen`, `estado`) VALUES
(1, 'COMBOS', 0, 0, 'default.png', 'a'),
(2, 'POLLO', 0, 100, 'default.png', 'a'),
(3, 'CHURRASCO', 0, 100, '231022095700.jpg', 'a'),
(4, 'PLATOS PERUANOS', 0, 100, 'default.png', 'a');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tm_producto_ingr`
--

CREATE TABLE `tm_producto_ingr` (
  `id_pi` int(11) NOT NULL,
  `id_pres` int(11) NOT NULL,
  `id_tipo_ins` int(11) NOT NULL,
  `id_ins` int(11) NOT NULL,
  `id_med` int(11) NOT NULL,
  `cant` float(10,6) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tm_producto_pres`
--

CREATE TABLE `tm_producto_pres` (
  `id_pres` int(11) NOT NULL,
  `id_prod` int(11) NOT NULL,
  `cod_prod` varchar(45) NOT NULL,
  `presentacion` varchar(45) NOT NULL,
  `descripcion` varchar(200) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `precio_delivery` decimal(10,2) NOT NULL,
  `receta` int(1) NOT NULL,
  `stock_min` int(11) NOT NULL,
  `crt_stock` int(1) NOT NULL DEFAULT 0,
  `impuesto` int(1) NOT NULL,
  `delivery` int(1) NOT NULL DEFAULT 0,
  `margen` int(11) NOT NULL DEFAULT 0,
  `iva` decimal(10,2) NOT NULL,
  `imagen` varchar(200) NOT NULL DEFAULT 'default.png',
  `estado` varchar(1) NOT NULL DEFAULT 'a'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `tm_producto_pres`
--

INSERT INTO `tm_producto_pres` (`id_pres`, `id_prod`, `cod_prod`, `presentacion`, `descripcion`, `precio`, `precio_delivery`, `receta`, `stock_min`, `crt_stock`, `impuesto`, `delivery`, `margen`, `iva`, `imagen`, `estado`) VALUES
(1, 1, 'PEPEC0', 'PECHO', 'Lleva porción de papa y arroz', 15.00, 0.00, 1, 100, 0, 0, 0, 0, 0.18, '231021014211.jpg', 'a'),
(2, 2, 'CHPLA0', 'PLATOS DE CHURRASCO', '', 40.00, 0.00, 0, 100, 0, 0, 0, 0, 0.18, '231022095846.jpg', 'a'),
(4, 3, 'PI1/415', '1/4 DE PIENA', '', 15.00, 0.00, 1, 100, 0, 0, 0, 0, 0.18, '231022100308.jpg', 'a'),
(5, 4, 'CECEV0', 'CEVICHE', '', 50.00, 0.00, 1, 100, 0, 0, 0, 0, 0.18, '231030061001.png', 'a'),
(6, 5, 'LELEC0', 'LECHE TIGRE', '', 40.00, 0.00, 1, 100, 0, 0, 0, 0, 0.18, '231030061525.jpg', 'a'),
(7, 6, 'ARARR50', 'ARROZ CHAUFA DE POLLO', '', 50.00, 0.00, 0, 100, 0, 0, 0, 0, 0.18, '231030062522.jpg', 'a'),
(8, 7, 'ARARR0', 'ARROZ CHAUFA DE CARNE', '', 50.00, 0.00, 0, 100, 0, 0, 0, 0, 0.18, '231030062848.jpg', 'a'),
(9, 8, 'ARESP0', 'ESPECIAL', '', 70.00, 0.00, 1, 100, 0, 0, 0, 0, 0.18, '231030063053.jpg', 'a');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tm_proveedor`
--

CREATE TABLE `tm_proveedor` (
  `id_prov` int(11) NOT NULL,
  `nit` varchar(13) NOT NULL,
  `razon_social` varchar(100) NOT NULL,
  `direccion` varchar(100) DEFAULT NULL,
  `telefono` int(9) DEFAULT NULL,
  `email` varchar(45) DEFAULT NULL,
  `contacto` varchar(45) DEFAULT NULL,
  `estado` varchar(1) DEFAULT 'a'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tm_repartidor`
--

CREATE TABLE `tm_repartidor` (
  `id_repartidor` int(11) NOT NULL,
  `descripcion` varchar(100) NOT NULL,
  `estado` varchar(5) NOT NULL DEFAULT 'a'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tm_repartidor`
--

INSERT INTO `tm_repartidor` (`id_repartidor`, `descripcion`, `estado`) VALUES
(1, 'INTERNO', 'a'),
(2222, 'RAPPI', 'a'),
(3333, 'UBER', 'a'),
(4444, 'GLOVO', 'a');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tm_rol`
--

CREATE TABLE `tm_rol` (
  `id_rol` int(11) NOT NULL,
  `descripcion` varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `tm_rol`
--

INSERT INTO `tm_rol` (`id_rol`, `descripcion`) VALUES
(1, 'ADMINISTRATOR'),
(2, 'ADMINISTRADOR'),
(3, 'CAJERO'),
(4, 'PRODUCCION'),
(5, 'MESERO'),
(6, 'REPARTIDOR');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tm_salon`
--

CREATE TABLE `tm_salon` (
  `id_salon` int(11) NOT NULL,
  `descripcion` varchar(45) NOT NULL,
  `estado` varchar(5) NOT NULL DEFAULT 'a'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `tm_salon`
--

INSERT INTO `tm_salon` (`id_salon`, `descripcion`, `estado`) VALUES
(1, 'SALA POLLERIA CHURRASQUERIA', 'a'),
(2, 'SALA HELADERIA', 'a'),
(3, 'SALA HAMBURGUSERIA', 'a');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tm_tipo_compra`
--

CREATE TABLE `tm_tipo_compra` (
  `id_tipo_compra` int(11) NOT NULL,
  `descripcion` varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `tm_tipo_compra`
--

INSERT INTO `tm_tipo_compra` (`id_tipo_compra`, `descripcion`) VALUES
(1, 'CONTADO'),
(2, 'CREDITO');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tm_tipo_doc`
--

CREATE TABLE `tm_tipo_doc` (
  `id_tipo_doc` int(11) NOT NULL,
  `descripcion` varchar(45) NOT NULL,
  `serie` char(4) NOT NULL,
  `numero` varchar(8) NOT NULL,
  `estado` varchar(5) NOT NULL DEFAULT 'a'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `tm_tipo_doc`
--

INSERT INTO `tm_tipo_doc` (`id_tipo_doc`, `descripcion`, `serie`, `numero`, `estado`) VALUES
(1, 'BOLETA DE VENTA', 'BA01', '00000001', 'a'),
(2, 'FACTURA', 'FA01', '00000001', 'a'),
(3, 'NOTA DE VENTA', 'NV01', '00000001', 'a');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tm_tipo_gasto`
--

CREATE TABLE `tm_tipo_gasto` (
  `id_tipo_gasto` int(11) NOT NULL,
  `descripcion` varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `tm_tipo_gasto`
--

INSERT INTO `tm_tipo_gasto` (`id_tipo_gasto`, `descripcion`) VALUES
(1, 'POR COMPRAS'),
(2, 'POR SREVICIOS'),
(3, 'POR REMUNERACION'),
(4, 'POR CREDITO DE COMPRAS');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tm_tipo_medida`
--

CREATE TABLE `tm_tipo_medida` (
  `id_med` int(11) NOT NULL,
  `descripcion` varchar(45) NOT NULL,
  `grupo` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `tm_tipo_medida`
--

INSERT INTO `tm_tipo_medida` (`id_med`, `descripcion`, `grupo`) VALUES
(1, 'UNIDAD', 1),
(2, 'KILOS', 2),
(3, 'GRAMOS', 2),
(4, 'MILIGRAMOS', 2),
(5, 'LITRO', 3),
(6, 'MILILITRO', 3),
(7, 'LIBRAS', 2),
(8, 'ONZAS', 4);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tm_tipo_pago`
--

CREATE TABLE `tm_tipo_pago` (
  `id_tipo_pago` int(11) NOT NULL,
  `id_pago` int(11) NOT NULL,
  `descripcion` varchar(45) NOT NULL,
  `estado` varchar(5) NOT NULL DEFAULT 'a'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `tm_tipo_pago`
--

INSERT INTO `tm_tipo_pago` (`id_tipo_pago`, `id_pago`, `descripcion`, `estado`) VALUES
(1, 1, 'EFECTIVO', 'a'),
(2, 2, 'TARJETA', 'a'),
(3, 3, 'PAGO MIXTO', 'a'),
(4, 4, 'CULQI', 'i'),
(5, 5, 'YAPE', 'a'),
(6, 5, 'LUKITA', 'i'),
(7, 5, 'TRANSFERENCIA', 'i'),
(8, 5, 'PLIN', 'i'),
(9, 5, 'TUNKI', 'i'),
(15, 2, 'QR', 'a');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tm_tipo_pedido`
--

CREATE TABLE `tm_tipo_pedido` (
  `id_tipo_pedido` int(11) NOT NULL,
  `descripcion` varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `tm_tipo_pedido`
--

INSERT INTO `tm_tipo_pedido` (`id_tipo_pedido`, `descripcion`) VALUES
(1, 'MESA'),
(2, 'LLEVAR'),
(3, 'DELIVERY');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tm_tipo_venta`
--

CREATE TABLE `tm_tipo_venta` (
  `id_tipo_venta` int(11) NOT NULL,
  `descripcion` varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `tm_tipo_venta`
--

INSERT INTO `tm_tipo_venta` (`id_tipo_venta`, `descripcion`) VALUES
(1, 'CONTADO'),
(2, 'CREDITO');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tm_turno`
--

CREATE TABLE `tm_turno` (
  `id_turno` int(11) NOT NULL,
  `descripcion` varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `tm_turno`
--

INSERT INTO `tm_turno` (`id_turno`, `descripcion`) VALUES
(1, 'PRIMER TURNO'),
(2, 'SEGUNDO TURNO');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tm_usuario`
--

CREATE TABLE `tm_usuario` (
  `id_usu` int(11) NOT NULL,
  `id_rol` int(11) NOT NULL,
  `id_areap` int(11) NOT NULL,
  `ci` varchar(10) NOT NULL,
  `ape_paterno` varchar(45) DEFAULT NULL,
  `ape_materno` varchar(45) DEFAULT NULL,
  `nombres` varchar(45) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `usuario` varchar(45) DEFAULT NULL,
  `contrasena` varchar(45) DEFAULT 'cmVzdHBl',
  `estado` varchar(5) DEFAULT 'a',
  `imagen` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `tm_usuario`
--

INSERT INTO `tm_usuario` (`id_usu`, `id_rol`, `id_areap`, `ci`, `ape_paterno`, `ape_materno`, `nombres`, `email`, `usuario`, `contrasena`, `estado`, `imagen`) VALUES
(1, 1, 0, '44827499', 'ADMINISTRADOR', 'ADMINISTRADOR', 'ADMINISTRADOR', 'administrador@gmail.com', 'administrador', 'c29wb3J0ZTI=', 'a', '161117020710-avatar5.png'),
(49, 2, 0, '05705618', 'GONZALES', 'ROCHA', 'EDWIN', 'edwingonzales.1993@gmail.com', 'Egonzales', 'MTIzNDU2', 'a', '231021025200.jpg'),
(51, 5, 0, '4567678', 'CHAO', 'PAZ', 'JOEL', 'joel@gmail.com', 'Cjoel', 'MTIzNDU2', 'a', '231022101011.png'),
(52, 3, 0, '23457885', 'COELHO', 'PEREIRA', 'RODRIGO', 'rodrigo@gmail.com', 'Crodrigo', 'MTIzNDU2', 'a', '231021034300.jpg'),
(53, 4, 1, '23456785', 'GUZMAN', 'MONTE', 'ALISON', 'alison@gmail.com', 'Alison', 'MTIzNDU2', 'a', '231021041633.jpg'),
(55, 2, 0, '2345678', 'RAMOS', 'CHAVARRIA', 'NELLY', 'nelly@gmail.com', 'Rnelly', 'MTIzNDU2', 'a', '231022063215.png'),
(56, 3, 0, '4585853', 'RIVERA', 'DURAN', 'FELIZ', 'feliz@gmail.com', 'Rfeliz', 'MTIzNDU2', 'a', '231022102327.png'),
(58, 3, 0, '5555556', 'HURTADO', 'CARDENAS', 'MARCELA', 'marcela@gmail.com', 'Mhurtada', 'MTIzNDU2', 'a', '231024054816.png'),
(59, 5, 0, '1234567', 'MESA', 'MESA', 'MESA', 'mesa@mail.com', 'Mmesa', 'MTIzNDU2', 'a', '231030054744.jpg'),
(60, 3, 0, '2345678', 'ROJAS', 'TORREZ', 'BRANDON', 'brandon@gmail.com', 'Brojas', 'MTIzNDU2', 'a', '231025031336.jpg'),
(62, 3, 0, '8765435', 'MESIAS', 'HURTADO', 'JONAS', 'mesias@gmail.com', 'Jmesias', 'MTIzNDU2', 'a', '231025083643.jpg'),
(63, 3, 0, '9876567', 'ALIPAZ', 'IDAGUA', 'WALTER', 'walter@gmail.com', 'Walipaz', 'MTIzNDU2', 'a', 'default-avatar.png');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tm_venta`
--

CREATE TABLE `tm_venta` (
  `id_venta` int(11) NOT NULL,
  `id_pedido` int(11) NOT NULL,
  `id_tipo_pedido` int(11) NOT NULL,
  `id_cliente` int(11) NOT NULL,
  `id_tipo_doc` int(11) NOT NULL,
  `id_tipo_pago` int(11) NOT NULL,
  `id_usu` int(11) NOT NULL,
  `id_apc` int(11) NOT NULL,
  `serie_doc` char(4) NOT NULL,
  `nro_doc` varchar(8) NOT NULL,
  `pago_efe` decimal(10,2) DEFAULT 0.00,
  `pago_efe_none` decimal(10,2) DEFAULT 0.00,
  `pago_tar` decimal(10,2) DEFAULT 0.00,
  `descuento_tipo` char(1) NOT NULL DEFAULT '1',
  `descuento_personal` int(11) DEFAULT NULL,
  `descuento_monto` decimal(10,2) DEFAULT 0.00,
  `descuento_motivo` varchar(200) DEFAULT NULL,
  `comision_tarjeta` decimal(10,2) DEFAULT 0.00,
  `comision_delivery` decimal(10,2) DEFAULT 0.00,
  `iva` decimal(10,2) DEFAULT 0.00,
  `total` decimal(10,2) DEFAULT 0.00,
  `codigo_operacion` varchar(20) DEFAULT NULL,
  `fecha_venta` datetime DEFAULT NULL,
  `estado` varchar(15) DEFAULT 'a',
  `enviado_impuestos` char(1) DEFAULT NULL,
  `code_respuesta_impuestos` varchar(5) NOT NULL,
  `descripcion_impuestos_cdr` varchar(300) NOT NULL,
  `name_file_impuestos` varchar(80) NOT NULL,
  `hash_cdr` varchar(200) NOT NULL,
  `hash_cpe` varchar(200) NOT NULL,
  `fecha_vencimiento` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `tm_venta`
--

INSERT INTO `tm_venta` (`id_venta`, `id_pedido`, `id_tipo_pedido`, `id_cliente`, `id_tipo_doc`, `id_tipo_pago`, `id_usu`, `id_apc`, `serie_doc`, `nro_doc`, `pago_efe`, `pago_efe_none`, `pago_tar`, `descuento_tipo`, `descuento_personal`, `descuento_monto`, `descuento_motivo`, `comision_tarjeta`, `comision_delivery`, `iva`, `total`, `codigo_operacion`, `fecha_venta`, `estado`, `enviado_impuestos`, `code_respuesta_impuestos`, `descripcion_impuestos_cdr`, `name_file_impuestos`, `hash_cdr`, `hash_cpe`, `fecha_vencimiento`) VALUES
(1, 1, 1, 1, 1, 1, 49, 1, 'BA01', '00000001', 30.00, 30.00, 0.00, '2', 0, 0.00, '', 0.00, 0.00, 0.18, 30.00, '', '2023-10-25 02:28:35', 'a', NULL, '', '', '', '', '', '0000-00-00'),
(2, 2, 1, 1, 1, 1, 49, 1, 'BA01', '00000002', 15.00, 15.00, 0.00, '2', 0, 0.00, '', 0.00, 0.00, 0.18, 15.00, '', '2023-10-25 03:08:02', 'a', NULL, '', '', '', '', '', '0000-00-00'),
(3, 4, 1, 1, 1, 1, 60, 2, 'BA01', '00000003', 30.00, 30.00, 0.00, '2', 0, 0.00, '', 0.00, 0.00, 0.18, 30.00, '', '2023-10-25 08:19:02', 'a', NULL, '', '', '', '', '', '0000-00-00'),
(4, 5, 1, 6, 1, 1, 60, 2, 'BA01', '00000004', 40.00, 40.00, 0.00, '2', 0, 0.00, '', 0.00, 0.00, 0.18, 40.00, '', '2023-10-25 08:23:24', 'a', NULL, '', '', '', '', '', '0000-00-00'),
(5, 6, 1, 6, 1, 1, 49, 3, 'BA01', '00000005', 55.00, 55.00, 0.00, '2', 0, 0.00, '', 0.00, 0.00, 0.18, 55.00, '', '2023-10-25 10:07:59', 'a', NULL, '', '', '', '', '', '0000-00-00'),
(6, 10, 2, 1, 1, 1, 60, 5, 'BA01', '00000006', 50.00, 50.00, 0.00, '2', 0, 0.00, '', 0.00, 0.00, 0.18, 50.00, '', '2023-10-30 01:51:33', 'a', NULL, '', '', '', '', '', '0000-00-00'),
(7, 11, 1, 1, 1, 1, 60, 5, 'BA01', '00000007', 100.00, 100.00, 0.00, '2', 0, 0.00, '', 0.00, 0.00, 0.18, 100.00, '', '2023-10-30 01:56:28', 'a', NULL, '', '', '', '', '', '0000-00-00'),
(8, 12, 1, 1, 1, 1, 60, 5, 'BA01', '00000008', 70.00, 70.00, 0.00, '2', 0, 0.00, '', 0.00, 0.00, 0.18, 70.00, '', '2023-10-30 02:01:32', 'a', NULL, '', '', '', '', '', '0000-00-00'),
(9, 13, 2, 1, 1, 1, 60, 5, 'BA01', '00000009', 15.00, 15.00, 0.00, '2', 0, 0.00, '', 0.00, 0.00, 0.18, 15.00, '', '2023-10-30 02:07:14', 'a', NULL, '', '', '', '', '', '0000-00-00'),
(10, 9, 2, 1, 1, 1, 60, 5, 'BA01', '00000010', 40.00, 40.00, 0.00, '2', 0, 0.00, '', 0.00, 0.00, 0.18, 40.00, '', '2023-10-30 02:08:17', 'a', NULL, '', '', '', '', '', '0000-00-00');

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_caja_aper`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_caja_aper` (
`id_apc` int(11)
,`id_usu` int(11)
,`id_caja` int(11)
,`id_turno` int(11)
,`fecha_aper` datetime
,`monto_aper` decimal(10,2)
,`fecha_cierre` datetime
,`monto_cierre` decimal(10,2)
,`monto_sistema` decimal(10,2)
,`stock_pollo` varchar(11)
,`estado` varchar(5)
,`desc_per` varchar(137)
,`desc_caja` varchar(45)
,`desc_turno` varchar(45)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_clientes`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_clientes` (
`id_cliente` int(11)
,`tipo_cliente` int(11)
,`ci` varchar(10)
,`nit` varchar(13)
,`nombre` varchar(200)
,`telefono` int(11)
,`fecha_nac` date
,`direccion` varchar(100)
,`referencia` varchar(100)
,`estado` varchar(5)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_cocina_de`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_cocina_de` (
`id_pedido` int(11)
,`id_areap` int(11)
,`id_tipo` int(11)
,`id_pres` int(11)
,`cantidad` int(11)
,`comentario` varchar(100)
,`fecha_pedido` datetime
,`fecha_envio` datetime
,`estado` varchar(5)
,`nro_pedido` varchar(10)
,`id_usu` int(11)
,`nombre_prod` varchar(45)
,`pres_prod` varchar(45)
,`ape_paterno` varchar(45)
,`ape_materno` varchar(45)
,`nombres` varchar(45)
,`estado_pedido` varchar(5)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_cocina_me`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_cocina_me` (
`id_pedido` int(11)
,`id_areap` int(11)
,`id_tipo` int(11)
,`id_pres` int(11)
,`cantidad` int(11)
,`comentario` varchar(100)
,`fecha_pedido` datetime
,`fecha_envio` datetime
,`estado` varchar(5)
,`id_mesa` int(11)
,`id_mozo` int(11)
,`nombre_prod` varchar(45)
,`pres_prod` varchar(45)
,`nro_mesa` varchar(5)
,`desc_salon` varchar(45)
,`ape_paterno` varchar(45)
,`ape_materno` varchar(45)
,`nombres` varchar(45)
,`estado_pedido` varchar(5)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_cocina_mo`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_cocina_mo` (
`id_pedido` int(11)
,`id_areap` int(11)
,`id_tipo` int(11)
,`id_pres` int(11)
,`cantidad` int(11)
,`comentario` varchar(100)
,`fecha_pedido` datetime
,`fecha_envio` datetime
,`estado` varchar(5)
,`nro_pedido` varchar(10)
,`id_usu` int(11)
,`nombre_prod` varchar(45)
,`pres_prod` varchar(45)
,`ape_paterno` varchar(45)
,`ape_materno` varchar(45)
,`nombres` varchar(45)
,`estado_pedido` varchar(5)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_compras`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_compras` (
`id_compra` int(11)
,`id_prov` int(11)
,`id_tipo_compra` int(11)
,`id_tipo_doc` int(11)
,`fecha_c` date
,`fecha_r` datetime
,`hora_c` varchar(45)
,`serie_doc` varchar(45)
,`num_doc` varchar(45)
,`iva` decimal(10,2)
,`total` decimal(10,2)
,`estado` varchar(1)
,`desc_tc` varchar(45)
,`desc_td` varchar(45)
,`desc_prov` varchar(100)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_det_delivery`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_det_delivery` (
`id_pedido` int(11)
,`id_pres` int(11)
,`cantidad` int(11)
,`precio` decimal(10,2)
,`estado` varchar(5)
,`nombre_prod` varchar(45)
,`pres_prod` varchar(45)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_det_llevar`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_det_llevar` (
`id_pedido` int(11)
,`id_pres` int(11)
,`cantidad` int(11)
,`precio` decimal(10,2)
,`estado` varchar(5)
,`nombre_prod` varchar(45)
,`pres_prod` varchar(45)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_gastosadm`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_gastosadm` (
`id_ga` int(11)
,`id_tg` int(11)
,`id_per` int(11)
,`id_usu` int(11)
,`id_apc` int(11)
,`importe` decimal(10,2)
,`responsable` varchar(100)
,`motivo` varchar(100)
,`fecha_re` datetime
,`estado` varchar(5)
,`des_tg` varchar(45)
,`desc_usu` varchar(137)
,`desc_per` varchar(137)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_insprod`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_insprod` (
`id_tipo_ins` int(1)
,`id_ins` int(11)
,`id_med` varchar(11)
,`id_gru` varchar(11)
,`ins_cod` varchar(45)
,`ins_nom` varchar(91)
,`ins_cat` varchar(45)
,`ins_med` varchar(45)
,`ins_rec` int(11)
,`ins_cos` decimal(10,2)
,`ins_sto` int(11)
,`est_a` varchar(5)
,`est_b` varchar(1)
,`est_c` varchar(1)
,`crt_stock` varchar(11)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_insumos`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_insumos` (
`id_ins` int(11)
,`id_catg` int(11)
,`id_med` int(11)
,`id_gru` int(11)
,`ins_cod` varchar(10)
,`ins_nom` varchar(45)
,`ins_sto` int(11)
,`ins_cos` decimal(10,2)
,`ins_est` varchar(5)
,`ins_cat` varchar(45)
,`ins_med` varchar(45)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_inventario`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_inventario` (
`id_tipo_ins` int(11)
,`id_ins` int(11)
,`ent` double
,`sal` varchar(1)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_inventario_ent`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_inventario_ent` (
`id_tipo_ins` int(11)
,`id_ins` int(11)
,`total` double
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_inventario_sal`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_inventario_sal` (
`id_tipo_ins` int(11)
,`id_ins` int(11)
,`total` double
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_listar_mesas`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_listar_mesas` (
`id_mesa` int(11)
,`id_salon` int(11)
,`nro_mesa` varchar(5)
,`estado` varchar(45)
,`desc_salon` varchar(45)
,`id_pedido` int(11)
,`fecha_pedido` datetime
,`nro_personas` int(11)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_mesas`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_mesas` (
`id_mesa` int(11)
,`id_salon` int(11)
,`nro_mesa` varchar(5)
,`estado` varchar(45)
,`desc_salon` varchar(45)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_pedidos_agrupados`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_pedidos_agrupados` (
`tipo_atencion` int(1)
,`id_pedido` int(11)
,`id_areap` int(11)
,`id_tipo` int(11)
,`id_pres` int(11)
,`cantidad` decimal(32,0)
,`comentario` varchar(100)
,`fecha_pedido` datetime
,`fecha_envio` datetime
,`estado` varchar(5)
,`nombre_prod` varchar(45)
,`pres_prod` varchar(45)
,`nro_mesa` varchar(5)
,`desc_salon` varchar(45)
,`ape_paterno` varchar(45)
,`ape_materno` varchar(45)
,`nombres` varchar(45)
,`estado_pedido` varchar(5)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_pedido_delivery`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_pedido_delivery` (
`id_pedido` int(11)
,`id_tipo_pedido` int(11)
,`id_usu` int(11)
,`id_repartidor` int(11)
,`fecha_pedido` datetime
,`estado_pedido` varchar(5)
,`tipo_entrega` int(11)
,`pedido_programado` int(11)
,`hora_entrega` time
,`amortizacion` decimal(10,2)
,`tipo_pago` int(11)
,`paga_con` decimal(10,2)
,`comision_delivery` decimal(10,2)
,`nro_pedido` varchar(10)
,`id_cliente` int(11)
,`tipo_cliente` int(11)
,`ci_cliente` varchar(10)
,`nit_cliente` varchar(13)
,`nombre_cliente` varchar(100)
,`telefono_cliente` varchar(20)
,`direccion_cliente` varchar(100)
,`referencia_cliente` varchar(100)
,`email_cliente` varchar(200)
,`desc_repartidor` varchar(137)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_pedido_llevar`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_pedido_llevar` (
`id_pedido` int(11)
,`id_tipo_pedido` int(11)
,`id_usu` int(11)
,`fecha_pedido` datetime
,`estado_pedido` varchar(5)
,`nro_pedido` varchar(10)
,`nombre_cliente` varchar(100)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_pedido_mesa`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_pedido_mesa` (
`id_pedido` int(11)
,`id_tipo_pedido` int(11)
,`id_usu` int(11)
,`id_mesa` int(11)
,`fecha_pedido` datetime
,`estado_pedido` varchar(5)
,`nombre_cliente` varchar(45)
,`nro_personas` int(11)
,`nro_mesa` varchar(5)
,`desc_salon` varchar(45)
,`estado_mesa` varchar(45)
,`nombre_mozo` varchar(91)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_productos`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_productos` (
`id_pres` int(11)
,`id_prod` int(11)
,`id_tipo` int(11)
,`id_catg` int(11)
,`id_areap` int(11)
,`pro_cat` varchar(45)
,`pro_cod` varchar(45)
,`pro_nom` varchar(45)
,`pro_pre` varchar(45)
,`pro_des` varchar(200)
,`pro_cos` decimal(10,2)
,`pro_cos_del` decimal(10,2)
,`pro_rec` int(1)
,`pro_sto` int(11)
,`pro_imp` int(1)
,`pro_mar` int(11)
,`pro_iva` decimal(10,2)
,`pro_img` varchar(200)
,`del_a` int(1)
,`del_b` int(1)
,`del_c` int(1)
,`est_a` varchar(1)
,`est_b` varchar(1)
,`est_c` varchar(1)
,`crt_stock` int(1)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_repartidores`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_repartidores` (
`id_repartidor` int(11)
,`desc_repartidor` varchar(137)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_stock`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_stock` (
`id_tipo_ins` int(11)
,`id_ins` int(11)
,`ent` double
,`sal` double
,`est_a` varchar(5)
,`est_b` varchar(1)
,`debajo_stock` int(1)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_stock_pedido`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_stock_pedido` (
`id_ins` int(11)
,`ent` double
,`sal` decimal(32,0)
,`control` int(11)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_usuarios`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_usuarios` (
`id_usu` int(11)
,`id_rol` int(11)
,`id_areap` int(11)
,`ci` varchar(10)
,`ape_paterno` varchar(45)
,`ape_materno` varchar(45)
,`nombres` varchar(45)
,`email` varchar(100)
,`usuario` varchar(45)
,`contrasena` varchar(45)
,`estado` varchar(5)
,`imagen` varchar(45)
,`desc_r` varchar(45)
,`desc_ap` varchar(45)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_ventas_con`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_ventas_con` (
`id_ven` int(11)
,`id_ped` int(11)
,`id_tped` int(11)
,`id_cli` int(11)
,`id_tdoc` int(11)
,`id_tpag` int(11)
,`id_usu` int(11)
,`id_apc` int(11)
,`ser_doc` char(4)
,`nro_doc` varchar(8)
,`pago_efe` decimal(10,2)
,`pago_efe_none` decimal(10,2)
,`pago_tar` decimal(10,2)
,`desc_monto` decimal(10,2)
,`desc_tipo` char(1)
,`desc_personal` int(11)
,`desc_motivo` varchar(200)
,`comis_tar` decimal(10,2)
,`comis_del` decimal(10,2)
,`iva` decimal(10,2)
,`total` decimal(10,2)
,`codigo_operacion` varchar(20)
,`fec_ven` datetime
,`estado` varchar(15)
,`enviado_impuestos` char(1)
,`code_respuesta_impuestos` varchar(5)
,`descripcion_impuestos_cdr` varchar(300)
,`name_file_impuestos` varchar(80)
,`hash_cdr` varchar(200)
,`hash_cpe` varchar(200)
,`fecha_vencimiento` date
,`desc_td` varchar(45)
,`desc_tp` varchar(45)
,`desc_usu` varchar(137)
);

-- --------------------------------------------------------

--
-- Estructura para la vista `v_caja_aper`
--
DROP TABLE IF EXISTS `v_caja_aper`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_caja_aper`  AS SELECT `apc`.`id_apc` AS `id_apc`, `apc`.`id_usu` AS `id_usu`, `apc`.`id_caja` AS `id_caja`, `apc`.`id_turno` AS `id_turno`, `apc`.`fecha_aper` AS `fecha_aper`, `apc`.`monto_aper` AS `monto_aper`, `apc`.`fecha_cierre` AS `fecha_cierre`, `apc`.`monto_cierre` AS `monto_cierre`, `apc`.`monto_sistema` AS `monto_sistema`, `apc`.`stock_pollo` AS `stock_pollo`, `apc`.`estado` AS `estado`, concat(`tp`.`nombres`,' ',`tp`.`ape_paterno`,' ',`tp`.`ape_materno`) AS `desc_per`, `tc`.`descripcion` AS `desc_caja`, `tt`.`descripcion` AS `desc_turno` FROM (((`tm_aper_cierre` `apc` join `tm_usuario` `tp` on(`apc`.`id_usu` = `tp`.`id_usu`)) join `tm_caja` `tc` on(`apc`.`id_caja` = `tc`.`id_caja`)) join `tm_turno` `tt` on(`apc`.`id_turno` = `tt`.`id_turno`)) ORDER BY `apc`.`id_apc` DESC ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_clientes`
--
DROP TABLE IF EXISTS `v_clientes`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_clientes`  AS SELECT `tm_cliente`.`id_cliente` AS `id_cliente`, `tm_cliente`.`tipo_cliente` AS `tipo_cliente`, `tm_cliente`.`ci` AS `ci`, `tm_cliente`.`nit` AS `nit`, concat(ifnull(`tm_cliente`.`razon_social`,''),'',`tm_cliente`.`nombres`) AS `nombre`, `tm_cliente`.`telefono` AS `telefono`, `tm_cliente`.`fecha_nac` AS `fecha_nac`, `tm_cliente`.`direccion` AS `direccion`, `tm_cliente`.`referencia` AS `referencia`, `tm_cliente`.`estado` AS `estado` FROM `tm_cliente` ORDER BY `tm_cliente`.`id_cliente` DESC ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_cocina_de`
--
DROP TABLE IF EXISTS `v_cocina_de`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_cocina_de`  AS SELECT `dp`.`id_pedido` AS `id_pedido`, `vp`.`id_areap` AS `id_areap`, `vp`.`id_tipo` AS `id_tipo`, `dp`.`id_pres` AS `id_pres`, if(`dp`.`cantidad` < `dp`.`cant`,`dp`.`cant`,`dp`.`cantidad`) AS `cantidad`, `dp`.`comentario` AS `comentario`, `dp`.`fecha_pedido` AS `fecha_pedido`, `dp`.`fecha_envio` AS `fecha_envio`, `dp`.`estado` AS `estado`, `pd`.`nro_pedido` AS `nro_pedido`, `tp`.`id_usu` AS `id_usu`, `vp`.`pro_nom` AS `nombre_prod`, `vp`.`pro_pre` AS `pres_prod`, `vu`.`ape_paterno` AS `ape_paterno`, `vu`.`ape_materno` AS `ape_materno`, `vu`.`nombres` AS `nombres`, `tp`.`estado` AS `estado_pedido` FROM ((((`tm_detalle_pedido` `dp` join `tm_pedido_delivery` `pd` on(`dp`.`id_pedido` = `pd`.`id_pedido`)) join `tm_pedido` `tp` on(`dp`.`id_pedido` = `tp`.`id_pedido`)) join `v_productos` `vp` on(`dp`.`id_pres` = `vp`.`id_pres`)) join `v_usuarios` `vu` on(`tp`.`id_usu` = `vu`.`id_usu`)) ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_cocina_me`
--
DROP TABLE IF EXISTS `v_cocina_me`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_cocina_me`  AS SELECT `dp`.`id_pedido` AS `id_pedido`, `vp`.`id_areap` AS `id_areap`, `vp`.`id_tipo` AS `id_tipo`, `dp`.`id_pres` AS `id_pres`, `dp`.`cantidad` AS `cantidad`, `dp`.`comentario` AS `comentario`, `dp`.`fecha_pedido` AS `fecha_pedido`, `dp`.`fecha_envio` AS `fecha_envio`, `dp`.`estado` AS `estado`, `pm`.`id_mesa` AS `id_mesa`, `pm`.`id_mozo` AS `id_mozo`, `vp`.`pro_nom` AS `nombre_prod`, `vp`.`pro_pre` AS `pres_prod`, `vm`.`nro_mesa` AS `nro_mesa`, `vm`.`desc_salon` AS `desc_salon`, `vu`.`ape_paterno` AS `ape_paterno`, `vu`.`ape_materno` AS `ape_materno`, `vu`.`nombres` AS `nombres`, `tp`.`estado` AS `estado_pedido` FROM (((((`tm_detalle_pedido` `dp` join `tm_pedido_mesa` `pm` on(`dp`.`id_pedido` = `pm`.`id_pedido`)) join `tm_pedido` `tp` on(`dp`.`id_pedido` = `tp`.`id_pedido`)) join `v_productos` `vp` on(`dp`.`id_pres` = `vp`.`id_pres`)) join `v_mesas` `vm` on(`pm`.`id_mesa` = `vm`.`id_mesa`)) join `v_usuarios` `vu` on(`pm`.`id_mozo` = `vu`.`id_usu`)) ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_cocina_mo`
--
DROP TABLE IF EXISTS `v_cocina_mo`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_cocina_mo`  AS SELECT `dp`.`id_pedido` AS `id_pedido`, `vp`.`id_areap` AS `id_areap`, `vp`.`id_tipo` AS `id_tipo`, `dp`.`id_pres` AS `id_pres`, if(`dp`.`cantidad` < `dp`.`cant`,`dp`.`cant`,`dp`.`cantidad`) AS `cantidad`, `dp`.`comentario` AS `comentario`, `dp`.`fecha_pedido` AS `fecha_pedido`, `dp`.`fecha_envio` AS `fecha_envio`, `dp`.`estado` AS `estado`, `pm`.`nro_pedido` AS `nro_pedido`, `tp`.`id_usu` AS `id_usu`, `vp`.`pro_nom` AS `nombre_prod`, `vp`.`pro_pre` AS `pres_prod`, `vu`.`ape_paterno` AS `ape_paterno`, `vu`.`ape_materno` AS `ape_materno`, `vu`.`nombres` AS `nombres`, `tp`.`estado` AS `estado_pedido` FROM ((((`tm_detalle_pedido` `dp` join `tm_pedido_llevar` `pm` on(`dp`.`id_pedido` = `pm`.`id_pedido`)) join `tm_pedido` `tp` on(`dp`.`id_pedido` = `tp`.`id_pedido`)) join `v_productos` `vp` on(`dp`.`id_pres` = `vp`.`id_pres`)) join `v_usuarios` `vu` on(`tp`.`id_usu` = `vu`.`id_usu`)) ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_compras`
--
DROP TABLE IF EXISTS `v_compras`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_compras`  AS SELECT `c`.`id_compra` AS `id_compra`, `c`.`id_prov` AS `id_prov`, `c`.`id_tipo_compra` AS `id_tipo_compra`, `c`.`id_tipo_doc` AS `id_tipo_doc`, `c`.`fecha_c` AS `fecha_c`, `c`.`fecha_reg` AS `fecha_r`, `c`.`hora_c` AS `hora_c`, `c`.`serie_doc` AS `serie_doc`, `c`.`num_doc` AS `num_doc`, `c`.`iva` AS `iva`, `c`.`total` AS `total`, `c`.`estado` AS `estado`, `tc`.`descripcion` AS `desc_tc`, `td`.`descripcion` AS `desc_td`, `tp`.`razon_social` AS `desc_prov` FROM (((`tm_compra` `c` join `tm_tipo_compra` `tc` on(`c`.`id_tipo_compra` = `tc`.`id_tipo_compra`)) join `tm_tipo_doc` `td` on(`c`.`id_tipo_doc` = `td`.`id_tipo_doc`)) join `tm_proveedor` `tp` on(`c`.`id_prov` = `tp`.`id_prov`)) WHERE `c`.`id_compra` <> 0 ORDER BY `c`.`id_compra` DESC ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_det_delivery`
--
DROP TABLE IF EXISTS `v_det_delivery`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_det_delivery`  AS SELECT `dp`.`id_pedido` AS `id_pedido`, `dp`.`id_pres` AS `id_pres`, if(`dp`.`cantidad` < `dp`.`cant`,`dp`.`cant`,`dp`.`cantidad`) AS `cantidad`, `dp`.`precio` AS `precio`, `dp`.`estado` AS `estado`, `vp`.`pro_nom` AS `nombre_prod`, `vp`.`pro_pre` AS `pres_prod` FROM (((`tm_detalle_pedido` `dp` join `tm_pedido_delivery` `pd` on(`dp`.`id_pedido` = `pd`.`id_pedido`)) join `tm_pedido` `tp` on(`dp`.`id_pedido` = `tp`.`id_pedido`)) join `v_productos` `vp` on(`dp`.`id_pres` = `vp`.`id_pres`)) WHERE `dp`.`estado` <> 'z' ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_det_llevar`
--
DROP TABLE IF EXISTS `v_det_llevar`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_det_llevar`  AS SELECT `dp`.`id_pedido` AS `id_pedido`, `dp`.`id_pres` AS `id_pres`, if(`dp`.`cantidad` < `dp`.`cant`,`dp`.`cant`,`dp`.`cantidad`) AS `cantidad`, `dp`.`precio` AS `precio`, `dp`.`estado` AS `estado`, `vp`.`pro_nom` AS `nombre_prod`, `vp`.`pro_pre` AS `pres_prod` FROM (((`tm_detalle_pedido` `dp` join `tm_pedido_llevar` `pm` on(`dp`.`id_pedido` = `pm`.`id_pedido`)) join `tm_pedido` `tp` on(`dp`.`id_pedido` = `tp`.`id_pedido`)) join `v_productos` `vp` on(`dp`.`id_pres` = `vp`.`id_pres`)) WHERE `dp`.`estado` <> 'z' ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_gastosadm`
--
DROP TABLE IF EXISTS `v_gastosadm`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_gastosadm`  AS SELECT `ga`.`id_ga` AS `id_ga`, `ga`.`id_tipo_gasto` AS `id_tg`, `ga`.`id_per` AS `id_per`, `ga`.`id_usu` AS `id_usu`, `ga`.`id_apc` AS `id_apc`, `ga`.`importe` AS `importe`, `ga`.`responsable` AS `responsable`, `ga`.`motivo` AS `motivo`, `ga`.`fecha_registro` AS `fecha_re`, `ga`.`estado` AS `estado`, `tg`.`descripcion` AS `des_tg`, concat(`tu`.`nombres`,' ',`tu`.`ape_paterno`,' ',`tu`.`ape_materno`) AS `desc_usu`, if(`ga`.`id_per` = '0','',concat(`tus`.`nombres`,' ',`tus`.`ape_paterno`,' ',`tus`.`ape_materno`)) AS `desc_per` FROM (((`tm_gastos_adm` `ga` join `tm_tipo_gasto` `tg` on(`ga`.`id_tipo_gasto` = `tg`.`id_tipo_gasto`)) join `tm_usuario` `tu` on(`ga`.`id_usu` = `tu`.`id_usu`)) left join `tm_usuario` `tus` on(`ga`.`id_per` = `tus`.`id_usu`)) WHERE `ga`.`id_ga` <> 0 ORDER BY `ga`.`id_ga` DESC ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_insprod`
--
DROP TABLE IF EXISTS `v_insprod`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_insprod`  AS SELECT 1 AS `id_tipo_ins`, `i`.`id_ins` AS `id_ins`, `i`.`id_med` AS `id_med`, `i`.`id_gru` AS `id_gru`, `i`.`ins_cod` AS `ins_cod`, `i`.`ins_nom` AS `ins_nom`, `i`.`ins_cat` AS `ins_cat`, `i`.`ins_med` AS `ins_med`, 1 AS `ins_rec`, `i`.`ins_cos` AS `ins_cos`, `i`.`ins_sto` AS `ins_sto`, `i`.`ins_est` AS `est_a`, 'a' AS `est_b`, 'a' AS `est_c`, '' AS `crt_stock` FROM `v_insumos` AS `i`union select 2 AS `id_tipo_ins`,`p`.`id_pres` AS `id_pres`,'1' AS `1`,'1' AS `1`,`p`.`pro_cod` AS `pro_cod`,concat(`p`.`pro_nom`,' ',`p`.`pro_pre`) AS `pro_nom`,`p`.`pro_cat` AS `pro_cat`,'UNIDAD' AS `UNIDAD`,`p`.`pro_rec` AS `pro_rec`,`p`.`pro_cos` AS `pro_cos`,`p`.`pro_sto` AS `pro_sto`,`p`.`est_a` AS `est_a`,`p`.`est_b` AS `est_b`,`p`.`est_c` AS `est_c`,`p`.`crt_stock` AS `crt_stock` from `v_productos` `p` where `p`.`id_tipo` = 2 and `p`.`id_catg` <> 1 union select 3 AS `id_tipo_ins`,`p`.`id_pres` AS `id_pres`,'1' AS `1`,'1' AS `1`,`p`.`pro_cod` AS `pro_cod`,concat(`p`.`pro_nom`,' ',`p`.`pro_pre`) AS `pro_nom`,`p`.`pro_cat` AS `pro_cat`,'UNIDAD' AS `UNIDAD`,`p`.`pro_rec` AS `pro_rec`,`p`.`pro_cos` AS `pro_cos`,`p`.`pro_sto` AS `pro_sto`,`p`.`est_a` AS `est_a`,`p`.`est_b` AS `est_b`,`p`.`est_c` AS `est_c`,`p`.`crt_stock` AS `crt_stock` from `v_productos` `p` where `p`.`id_tipo` = 1 and `p`.`id_catg` <> 1  ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_insumos`
--
DROP TABLE IF EXISTS `v_insumos`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_insumos`  AS SELECT `i`.`id_ins` AS `id_ins`, `i`.`id_catg` AS `id_catg`, `i`.`id_med` AS `id_med`, `m`.`grupo` AS `id_gru`, `i`.`cod_ins` AS `ins_cod`, `i`.`nomb_ins` AS `ins_nom`, `i`.`stock_min` AS `ins_sto`, `i`.`cos_uni` AS `ins_cos`, `i`.`estado` AS `ins_est`, `ic`.`descripcion` AS `ins_cat`, `m`.`descripcion` AS `ins_med` FROM ((`tm_insumo` `i` join `tm_insumo_catg` `ic` on(`i`.`id_catg` = `ic`.`id_catg`)) join `tm_tipo_medida` `m` on(`i`.`id_med` = `m`.`id_med`)) ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_inventario`
--
DROP TABLE IF EXISTS `v_inventario`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_inventario`  AS SELECT `e`.`id_tipo_ins` AS `id_tipo_ins`, `e`.`id_ins` AS `id_ins`, ifnull(`e`.`total`,0) AS `ent`, '0' AS `sal` FROM `v_inventario_ent` AS `e` ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_inventario_ent`
--
DROP TABLE IF EXISTS `v_inventario_ent`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_inventario_ent`  AS SELECT `tm_inventario`.`id_tipo_ins` AS `id_tipo_ins`, `tm_inventario`.`id_ins` AS `id_ins`, if(`tm_inventario`.`id_tipo_ope` = 1 or `tm_inventario`.`id_tipo_ope` = 3,sum(`tm_inventario`.`cant`),0) AS `total` FROM `tm_inventario` WHERE `tm_inventario`.`id_tipo_ope` <> 2 AND `tm_inventario`.`id_tipo_ope` <> 4 AND `tm_inventario`.`estado` <> 'i' GROUP BY `tm_inventario`.`id_tipo_ins`, `tm_inventario`.`id_ins` ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_inventario_sal`
--
DROP TABLE IF EXISTS `v_inventario_sal`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_inventario_sal`  AS SELECT `tm_inventario`.`id_tipo_ins` AS `id_tipo_ins`, `tm_inventario`.`id_ins` AS `id_ins`, if(`tm_inventario`.`id_tipo_ope` = 2 or `tm_inventario`.`id_tipo_ope` = 4,sum(`tm_inventario`.`cant`),0) AS `total` FROM `tm_inventario` WHERE `tm_inventario`.`id_tipo_ope` <> 1 AND `tm_inventario`.`id_tipo_ope` <> 3 AND `tm_inventario`.`estado` <> 'i' GROUP BY `tm_inventario`.`id_tipo_ins`, `tm_inventario`.`id_ins` ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_listar_mesas`
--
DROP TABLE IF EXISTS `v_listar_mesas`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_listar_mesas`  AS SELECT `vm`.`id_mesa` AS `id_mesa`, `vm`.`id_salon` AS `id_salon`, `vm`.`nro_mesa` AS `nro_mesa`, `vm`.`estado` AS `estado`, `vm`.`desc_salon` AS `desc_salon`, `vo`.`id_pedido` AS `id_pedido`, `vo`.`fecha_pedido` AS `fecha_pedido`, `vo`.`nro_personas` AS `nro_personas` FROM (`v_mesas` `vm` left join `v_pedido_mesa` `vo` on(`vm`.`id_mesa` = `vo`.`id_mesa`)) ORDER BY `vm`.`nro_mesa` ASC ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_mesas`
--
DROP TABLE IF EXISTS `v_mesas`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_mesas`  AS SELECT `m`.`id_mesa` AS `id_mesa`, `m`.`id_salon` AS `id_salon`, `m`.`nro_mesa` AS `nro_mesa`, `m`.`estado` AS `estado`, `cm`.`descripcion` AS `desc_salon` FROM (`tm_mesa` `m` join `tm_salon` `cm` on(`m`.`id_salon` = `cm`.`id_salon`)) WHERE `m`.`id_mesa` <> 0 AND `cm`.`estado` <> 'i' ORDER BY `m`.`id_mesa` ASC ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_pedidos_agrupados`
--
DROP TABLE IF EXISTS `v_pedidos_agrupados`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_pedidos_agrupados`  AS SELECT 1 AS `tipo_atencion`, `v_cocina_me`.`id_pedido` AS `id_pedido`, `v_cocina_me`.`id_areap` AS `id_areap`, `v_cocina_me`.`id_tipo` AS `id_tipo`, `v_cocina_me`.`id_pres` AS `id_pres`, sum(`v_cocina_me`.`cantidad`) AS `cantidad`, `v_cocina_me`.`comentario` AS `comentario`, `v_cocina_me`.`fecha_pedido` AS `fecha_pedido`, `v_cocina_me`.`fecha_envio` AS `fecha_envio`, `v_cocina_me`.`estado` AS `estado`, `v_cocina_me`.`nombre_prod` AS `nombre_prod`, `v_cocina_me`.`pres_prod` AS `pres_prod`, `v_cocina_me`.`nro_mesa` AS `nro_mesa`, `v_cocina_me`.`desc_salon` AS `desc_salon`, `v_cocina_me`.`ape_paterno` AS `ape_paterno`, `v_cocina_me`.`ape_materno` AS `ape_materno`, `v_cocina_me`.`nombres` AS `nombres`, `v_cocina_me`.`estado_pedido` AS `estado_pedido` FROM `v_cocina_me` GROUP BY `v_cocina_me`.`id_pedido`, `v_cocina_me`.`id_pres`, `v_cocina_me`.`fecha_pedido`, `v_cocina_me`.`comentario` ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_pedido_delivery`
--
DROP TABLE IF EXISTS `v_pedido_delivery`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_pedido_delivery`  AS SELECT `p`.`id_pedido` AS `id_pedido`, `p`.`id_tipo_pedido` AS `id_tipo_pedido`, `p`.`id_usu` AS `id_usu`, `pd`.`id_repartidor` AS `id_repartidor`, `p`.`fecha_pedido` AS `fecha_pedido`, `p`.`estado` AS `estado_pedido`, `pd`.`tipo_entrega` AS `tipo_entrega`, `pd`.`pedido_programado` AS `pedido_programado`, `pd`.`hora_entrega` AS `hora_entrega`, `pd`.`amortizacion` AS `amortizacion`, `pd`.`tipo_pago` AS `tipo_pago`, `pd`.`paga_con` AS `paga_con`, `pd`.`comision_delivery` AS `comision_delivery`, `pd`.`nro_pedido` AS `nro_pedido`, `pd`.`id_cliente` AS `id_cliente`, `c`.`tipo_cliente` AS `tipo_cliente`, `c`.`ci` AS `ci_cliente`, `c`.`nit` AS `nit_cliente`, `pd`.`nombre_cliente` AS `nombre_cliente`, `pd`.`telefono_cliente` AS `telefono_cliente`, `pd`.`direccion_cliente` AS `direccion_cliente`, `pd`.`referencia_cliente` AS `referencia_cliente`, `pd`.`email_cliente` AS `email_cliente`, `r`.`desc_repartidor` AS `desc_repartidor` FROM (((`tm_pedido` `p` join `tm_pedido_delivery` `pd` on(`p`.`id_pedido` = `pd`.`id_pedido`)) join `v_repartidores` `r` on(`pd`.`id_repartidor` = `r`.`id_repartidor`)) join `tm_cliente` `c` on(`pd`.`id_cliente` = `c`.`id_cliente`)) WHERE `p`.`id_pedido` <> 0 ORDER BY `p`.`id_pedido` DESC ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_pedido_llevar`
--
DROP TABLE IF EXISTS `v_pedido_llevar`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_pedido_llevar`  AS SELECT `p`.`id_pedido` AS `id_pedido`, `p`.`id_tipo_pedido` AS `id_tipo_pedido`, `p`.`id_usu` AS `id_usu`, `p`.`fecha_pedido` AS `fecha_pedido`, `p`.`estado` AS `estado_pedido`, `pl`.`nro_pedido` AS `nro_pedido`, `pl`.`nomb_cliente` AS `nombre_cliente` FROM (`tm_pedido` `p` join `tm_pedido_llevar` `pl` on(`p`.`id_pedido` = `pl`.`id_pedido`)) WHERE `p`.`id_pedido` <> 0 ORDER BY `p`.`id_pedido` DESC ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_pedido_mesa`
--
DROP TABLE IF EXISTS `v_pedido_mesa`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_pedido_mesa`  AS SELECT `p`.`id_pedido` AS `id_pedido`, `p`.`id_tipo_pedido` AS `id_tipo_pedido`, `p`.`id_usu` AS `id_usu`, `pm`.`id_mesa` AS `id_mesa`, `p`.`fecha_pedido` AS `fecha_pedido`, `p`.`estado` AS `estado_pedido`, `pm`.`nomb_cliente` AS `nombre_cliente`, `pm`.`nro_personas` AS `nro_personas`, `vm`.`nro_mesa` AS `nro_mesa`, `vm`.`desc_salon` AS `desc_salon`, `vm`.`estado` AS `estado_mesa`, concat(`u`.`nombres`,' ',`u`.`ape_paterno`) AS `nombre_mozo` FROM (((`tm_pedido` `p` join `tm_pedido_mesa` `pm` on(`p`.`id_pedido` = `pm`.`id_pedido`)) join `v_mesas` `vm` on(`pm`.`id_mesa` = `vm`.`id_mesa`)) join `tm_usuario` `u` on(`pm`.`id_mozo` = `u`.`id_usu`)) WHERE `p`.`id_pedido` <> 0 AND `p`.`estado` = 'a' ORDER BY `p`.`id_pedido` DESC ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_productos`
--
DROP TABLE IF EXISTS `v_productos`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_productos`  AS SELECT `pp`.`id_pres` AS `id_pres`, `pp`.`id_prod` AS `id_prod`, `p`.`id_tipo` AS `id_tipo`, `p`.`id_catg` AS `id_catg`, `p`.`id_areap` AS `id_areap`, `cp`.`descripcion` AS `pro_cat`, `pp`.`cod_prod` AS `pro_cod`, `p`.`nombre` AS `pro_nom`, `pp`.`presentacion` AS `pro_pre`, ifnull(`pp`.`descripcion`,'') AS `pro_des`, `pp`.`precio` AS `pro_cos`, `pp`.`precio_delivery` AS `pro_cos_del`, `pp`.`receta` AS `pro_rec`, `pp`.`stock_min` AS `pro_sto`, `pp`.`impuesto` AS `pro_imp`, `pp`.`margen` AS `pro_mar`, `pp`.`iva` AS `pro_iva`, `pp`.`imagen` AS `pro_img`, `cp`.`delivery` AS `del_a`, `p`.`delivery` AS `del_b`, `pp`.`delivery` AS `del_c`, `cp`.`estado` AS `est_a`, `p`.`estado` AS `est_b`, `pp`.`estado` AS `est_c`, `pp`.`crt_stock` AS `crt_stock` FROM ((`tm_producto_pres` `pp` join `tm_producto` `p` on(`pp`.`id_prod` = `p`.`id_prod`)) join `tm_producto_catg` `cp` on(`p`.`id_catg` = `cp`.`id_catg`)) WHERE `pp`.`id_pres` <> 0 ORDER BY `pp`.`id_pres` DESC ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_repartidores`
--
DROP TABLE IF EXISTS `v_repartidores`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_repartidores`  AS SELECT `tm_usuario`.`id_usu` AS `id_repartidor`, concat(`tm_usuario`.`nombres`,' ',`tm_usuario`.`ape_paterno`,' ',`tm_usuario`.`ape_materno`) AS `desc_repartidor` FROM `tm_usuario` WHERE `tm_usuario`.`id_rol` = 6 ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_stock`
--
DROP TABLE IF EXISTS `v_stock`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_stock`  AS   (select `a`.`id_tipo_ins` AS `id_tipo_ins`,`a`.`id_ins` AS `id_ins`,sum(`a`.`ent`) AS `ent`,sum(`a`.`sal`) AS `sal`,`b`.`est_a` AS `est_a`,`b`.`est_b` AS `est_b`,if(`a`.`ent` - `a`.`sal` > `b`.`ins_sto`,1,0) AS `debajo_stock` from (`v_inventario` `a` join `v_insprod` `b` on(`a`.`id_tipo_ins` = `b`.`id_tipo_ins` and `a`.`id_ins` = `b`.`id_ins`)) where `b`.`est_a` = 'a' and `b`.`est_b` = 'a' and `b`.`ins_rec` = 1 group by `a`.`id_tipo_ins`,`a`.`id_ins`)  ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_stock_pedido`
--
DROP TABLE IF EXISTS `v_stock_pedido`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_stock_pedido`  AS   (select `c`.`id_pres` AS `id_ins`,ifnull((select sum(`b`.`ent`) from `v_inventario` `b` where `b`.`id_ins` = `c`.`id_pres` group by `c`.`id_pres`),0) AS `ent`,ifnull((select sum(`a`.`cant`) from `tm_detalle_pedido` `a` where `a`.`id_pres` = `c`.`id_pres` and `a`.`estado` = 'a' group by `c`.`id_pres`),0) AS `sal`,(select `d`.`crt_stock` from `tm_producto_pres` `d` where `d`.`id_pres` = `c`.`id_pres` group by `c`.`id_pres`) AS `control` from `tm_producto_pres` `c` where `c`.`crt_stock` = 1 group by `c`.`id_pres`)  ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_usuarios`
--
DROP TABLE IF EXISTS `v_usuarios`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_usuarios`  AS SELECT `u`.`id_usu` AS `id_usu`, `u`.`id_rol` AS `id_rol`, `u`.`id_areap` AS `id_areap`, `u`.`ci` AS `ci`, `u`.`ape_paterno` AS `ape_paterno`, `u`.`ape_materno` AS `ape_materno`, `u`.`nombres` AS `nombres`, `u`.`email` AS `email`, `u`.`usuario` AS `usuario`, `u`.`contrasena` AS `contrasena`, `u`.`estado` AS `estado`, `u`.`imagen` AS `imagen`, `r`.`descripcion` AS `desc_r`, `p`.`nombre` AS `desc_ap` FROM ((`tm_usuario` `u` join `tm_rol` `r` on(`u`.`id_rol` = `r`.`id_rol`)) left join `tm_area_prod` `p` on(`u`.`id_areap` = `p`.`id_areap`)) WHERE `u`.`id_usu` <> 0 ORDER BY `u`.`id_usu` DESC ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_ventas_con`
--
DROP TABLE IF EXISTS `v_ventas_con`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_ventas_con`  AS SELECT `v`.`id_venta` AS `id_ven`, `v`.`id_pedido` AS `id_ped`, `v`.`id_tipo_pedido` AS `id_tped`, `v`.`id_cliente` AS `id_cli`, `v`.`id_tipo_doc` AS `id_tdoc`, `v`.`id_tipo_pago` AS `id_tpag`, `v`.`id_usu` AS `id_usu`, `v`.`id_apc` AS `id_apc`, `v`.`serie_doc` AS `ser_doc`, `v`.`nro_doc` AS `nro_doc`, `v`.`pago_efe` AS `pago_efe`, `v`.`pago_efe_none` AS `pago_efe_none`, `v`.`pago_tar` AS `pago_tar`, `v`.`descuento_monto` AS `desc_monto`, `v`.`descuento_tipo` AS `desc_tipo`, `v`.`descuento_personal` AS `desc_personal`, `v`.`descuento_motivo` AS `desc_motivo`, `v`.`comision_tarjeta` AS `comis_tar`, `v`.`comision_delivery` AS `comis_del`, `v`.`iva` AS `iva`, `v`.`total` AS `total`, `v`.`codigo_operacion` AS `codigo_operacion`, `v`.`fecha_venta` AS `fec_ven`, `v`.`estado` AS `estado`, `v`.`enviado_impuestos` AS `enviado_impuestos`, `v`.`code_respuesta_impuestos` AS `code_respuesta_impuestos`, `v`.`descripcion_impuestos_cdr` AS `descripcion_impuestos_cdr`, `v`.`name_file_impuestos` AS `name_file_impuestos`, `v`.`hash_cdr` AS `hash_cdr`, `v`.`hash_cpe` AS `hash_cpe`, `v`.`fecha_vencimiento` AS `fecha_vencimiento`, `td`.`descripcion` AS `desc_td`, `tp`.`descripcion` AS `desc_tp`, concat(`tu`.`ape_paterno`,' ',`tu`.`ape_materno`,' ',`tu`.`nombres`) AS `desc_usu` FROM (((`tm_venta` `v` join `tm_tipo_doc` `td` on(`v`.`id_tipo_doc` = `td`.`id_tipo_doc`)) join `tm_tipo_pago` `tp` on(`v`.`id_tipo_pago` = `tp`.`id_tipo_pago`)) join `tm_usuario` `tu` on(`v`.`id_usu` = `tu`.`id_usu`)) WHERE `v`.`id_venta` <> 0 ORDER BY `v`.`id_venta` DESC ;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `comunicacion_baja`
--
ALTER TABLE `comunicacion_baja`
  ADD PRIMARY KEY (`id_comunicacion`);

--
-- Indices de la tabla `resumen_diario`
--
ALTER TABLE `resumen_diario`
  ADD PRIMARY KEY (`id_resumen`);

--
-- Indices de la tabla `resumen_diario_detalle`
--
ALTER TABLE `resumen_diario_detalle`
  ADD PRIMARY KEY (`id_detalle`),
  ADD KEY `FK_RDD_RES` (`id_resumen`),
  ADD KEY `FK_RDD_VEN` (`id_venta`);

--
-- Indices de la tabla `tm_almacen`
--
ALTER TABLE `tm_almacen`
  ADD PRIMARY KEY (`id_alm`);

--
-- Indices de la tabla `tm_aper_cierre`
--
ALTER TABLE `tm_aper_cierre`
  ADD PRIMARY KEY (`id_apc`),
  ADD KEY `FK_ac_caja` (`id_caja`),
  ADD KEY `FK_ac_turno` (`id_turno`),
  ADD KEY `FK_ac_usu` (`id_usu`);

--
-- Indices de la tabla `tm_area_prod`
--
ALTER TABLE `tm_area_prod`
  ADD PRIMARY KEY (`id_areap`),
  ADD KEY `FK_ap_alm` (`id_imp`);

--
-- Indices de la tabla `tm_caja`
--
ALTER TABLE `tm_caja`
  ADD PRIMARY KEY (`id_caja`);

--
-- Indices de la tabla `tm_cliente`
--
ALTER TABLE `tm_cliente`
  ADD PRIMARY KEY (`id_cliente`);

--
-- Indices de la tabla `tm_compra`
--
ALTER TABLE `tm_compra`
  ADD PRIMARY KEY (`id_compra`),
  ADD KEY `FK_comp_prov` (`id_prov`),
  ADD KEY `FK_comp_tipoc` (`id_tipo_compra`),
  ADD KEY `FK_comp_tipod` (`id_tipo_doc`),
  ADD KEY `FK_comp_usu` (`id_usu`);

--
-- Indices de la tabla `tm_compra_credito`
--
ALTER TABLE `tm_compra_credito`
  ADD PRIMARY KEY (`id_credito`),
  ADD KEY `FK_CC_ID_COMPRA_idx` (`id_compra`);

--
-- Indices de la tabla `tm_compra_detalle`
--
ALTER TABLE `tm_compra_detalle`
  ADD KEY `FK_CDET_COM` (`id_compra`);

--
-- Indices de la tabla `tm_configuracion`
--
ALTER TABLE `tm_configuracion`
  ADD PRIMARY KEY (`id_cfg`);

--
-- Indices de la tabla `tm_credito_detalle`
--
ALTER TABLE `tm_credito_detalle`
  ADD KEY `FK_cred_usu` (`id_usu`),
  ADD KEY `FK_CRED_CRED` (`id_credito`);

--
-- Indices de la tabla `tm_detalle_pedido`
--
ALTER TABLE `tm_detalle_pedido`
  ADD KEY `FK_DPED_PRES` (`id_pres`),
  ADD KEY `FK_DPED_PED` (`id_pedido`),
  ADD KEY `FK_DPED_USU` (`id_usu`);

--
-- Indices de la tabla `tm_detalle_venta`
--
ALTER TABLE `tm_detalle_venta`
  ADD KEY `FK_DVEN_VEN` (`id_venta`),
  ADD KEY `FK_DVEN_PRES` (`id_prod`);

--
-- Indices de la tabla `tm_empresa`
--
ALTER TABLE `tm_empresa`
  ADD PRIMARY KEY (`id_de`);

--
-- Indices de la tabla `tm_gastos_adm`
--
ALTER TABLE `tm_gastos_adm`
  ADD PRIMARY KEY (`id_ga`),
  ADD KEY `FK_gasto_tg` (`id_tipo_gasto`),
  ADD KEY `FK_EADM_APC` (`id_apc`),
  ADD KEY `FK_EADM_USU` (`id_usu`);

--
-- Indices de la tabla `tm_impresora`
--
ALTER TABLE `tm_impresora`
  ADD PRIMARY KEY (`id_imp`);

--
-- Indices de la tabla `tm_ingresos_adm`
--
ALTER TABLE `tm_ingresos_adm`
  ADD PRIMARY KEY (`id_ing`),
  ADD KEY `FK_IADM_USU` (`id_usu`),
  ADD KEY `FK_IADM_APC` (`id_apc`);

--
-- Indices de la tabla `tm_insumo`
--
ALTER TABLE `tm_insumo`
  ADD PRIMARY KEY (`id_ins`),
  ADD KEY `FK_ins_catg` (`id_catg`),
  ADD KEY `FK_ins_med` (`id_med`);

--
-- Indices de la tabla `tm_insumo_catg`
--
ALTER TABLE `tm_insumo_catg`
  ADD PRIMARY KEY (`id_catg`);

--
-- Indices de la tabla `tm_inventario`
--
ALTER TABLE `tm_inventario`
  ADD PRIMARY KEY (`id_inv`);

--
-- Indices de la tabla `tm_inventario_entsal`
--
ALTER TABLE `tm_inventario_entsal`
  ADD PRIMARY KEY (`id_es`),
  ADD KEY `FK_INVES_USU` (`id_usu`),
  ADD KEY `FK_INVES_RESP` (`id_responsable`);

--
-- Indices de la tabla `tm_margen_venta`
--
ALTER TABLE `tm_margen_venta`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `tm_mesa`
--
ALTER TABLE `tm_mesa`
  ADD PRIMARY KEY (`id_mesa`),
  ADD KEY `FKM_IDCATG_idx` (`id_salon`);

--
-- Indices de la tabla `tm_pago`
--
ALTER TABLE `tm_pago`
  ADD PRIMARY KEY (`id_pago`);

--
-- Indices de la tabla `tm_pedido`
--
ALTER TABLE `tm_pedido`
  ADD PRIMARY KEY (`id_pedido`),
  ADD KEY `FK_ped_tp` (`id_tipo_pedido`),
  ADD KEY `FK_ped_usu` (`id_usu`),
  ADD KEY `FK_ped_apc` (`id_apc`);

--
-- Indices de la tabla `tm_pedido_delivery`
--
ALTER TABLE `tm_pedido_delivery`
  ADD KEY `FK_peddel_ped` (`id_pedido`),
  ADD KEY `FK_peddel_cli` (`id_cliente`);

--
-- Indices de la tabla `tm_pedido_llevar`
--
ALTER TABLE `tm_pedido_llevar`
  ADD KEY `FK_pedlle_ped` (`id_pedido`);

--
-- Indices de la tabla `tm_pedido_mesa`
--
ALTER TABLE `tm_pedido_mesa`
  ADD KEY `FK_pedme_ped` (`id_pedido`),
  ADD KEY `FK_pedme_mesa` (`id_mesa`),
  ADD KEY `FK_pedme_mozo` (`id_mozo`);

--
-- Indices de la tabla `tm_producto`
--
ALTER TABLE `tm_producto`
  ADD PRIMARY KEY (`id_prod`),
  ADD KEY `FK_prod_catg` (`id_catg`),
  ADD KEY `FK_prod_area` (`id_areap`);

--
-- Indices de la tabla `tm_producto_catg`
--
ALTER TABLE `tm_producto_catg`
  ADD PRIMARY KEY (`id_catg`);

--
-- Indices de la tabla `tm_producto_ingr`
--
ALTER TABLE `tm_producto_ingr`
  ADD PRIMARY KEY (`id_pi`),
  ADD KEY `FK_PING_PRES` (`id_pres`),
  ADD KEY `FK_PING_INS` (`id_ins`),
  ADD KEY `FK_PING_MED` (`id_med`);

--
-- Indices de la tabla `tm_producto_pres`
--
ALTER TABLE `tm_producto_pres`
  ADD PRIMARY KEY (`id_pres`),
  ADD KEY `FK_PROP_PROD` (`id_prod`);

--
-- Indices de la tabla `tm_proveedor`
--
ALTER TABLE `tm_proveedor`
  ADD PRIMARY KEY (`id_prov`);

--
-- Indices de la tabla `tm_repartidor`
--
ALTER TABLE `tm_repartidor`
  ADD PRIMARY KEY (`id_repartidor`);

--
-- Indices de la tabla `tm_rol`
--
ALTER TABLE `tm_rol`
  ADD PRIMARY KEY (`id_rol`);

--
-- Indices de la tabla `tm_salon`
--
ALTER TABLE `tm_salon`
  ADD PRIMARY KEY (`id_salon`);

--
-- Indices de la tabla `tm_tipo_compra`
--
ALTER TABLE `tm_tipo_compra`
  ADD PRIMARY KEY (`id_tipo_compra`);

--
-- Indices de la tabla `tm_tipo_doc`
--
ALTER TABLE `tm_tipo_doc`
  ADD PRIMARY KEY (`id_tipo_doc`);

--
-- Indices de la tabla `tm_tipo_gasto`
--
ALTER TABLE `tm_tipo_gasto`
  ADD PRIMARY KEY (`id_tipo_gasto`);

--
-- Indices de la tabla `tm_tipo_medida`
--
ALTER TABLE `tm_tipo_medida`
  ADD PRIMARY KEY (`id_med`);

--
-- Indices de la tabla `tm_tipo_pago`
--
ALTER TABLE `tm_tipo_pago`
  ADD PRIMARY KEY (`id_tipo_pago`),
  ADD KEY `FK_TIPODEPAGO` (`id_pago`);

--
-- Indices de la tabla `tm_tipo_pedido`
--
ALTER TABLE `tm_tipo_pedido`
  ADD PRIMARY KEY (`id_tipo_pedido`);

--
-- Indices de la tabla `tm_tipo_venta`
--
ALTER TABLE `tm_tipo_venta`
  ADD PRIMARY KEY (`id_tipo_venta`);

--
-- Indices de la tabla `tm_turno`
--
ALTER TABLE `tm_turno`
  ADD PRIMARY KEY (`id_turno`);

--
-- Indices de la tabla `tm_usuario`
--
ALTER TABLE `tm_usuario`
  ADD PRIMARY KEY (`id_usu`),
  ADD KEY `FKU_IDROL_idx` (`id_rol`);

--
-- Indices de la tabla `tm_venta`
--
ALTER TABLE `tm_venta`
  ADD PRIMARY KEY (`id_venta`),
  ADD KEY `FK_venta_cli` (`id_cliente`),
  ADD KEY `FK_venta_td` (`id_tipo_doc`),
  ADD KEY `FK_venta_tp` (`id_tipo_pago`),
  ADD KEY `FK_venta_usu` (`id_usu`),
  ADD KEY `FK_venta_apc` (`id_apc`),
  ADD KEY `FK_venta_tpe` (`id_tipo_pedido`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `comunicacion_baja`
--
ALTER TABLE `comunicacion_baja`
  MODIFY `id_comunicacion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `resumen_diario`
--
ALTER TABLE `resumen_diario`
  MODIFY `id_resumen` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `resumen_diario_detalle`
--
ALTER TABLE `resumen_diario_detalle`
  MODIFY `id_detalle` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tm_almacen`
--
ALTER TABLE `tm_almacen`
  MODIFY `id_alm` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `tm_aper_cierre`
--
ALTER TABLE `tm_aper_cierre`
  MODIFY `id_apc` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `tm_area_prod`
--
ALTER TABLE `tm_area_prod`
  MODIFY `id_areap` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `tm_caja`
--
ALTER TABLE `tm_caja`
  MODIFY `id_caja` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `tm_cliente`
--
ALTER TABLE `tm_cliente`
  MODIFY `id_cliente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `tm_compra`
--
ALTER TABLE `tm_compra`
  MODIFY `id_compra` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tm_compra_credito`
--
ALTER TABLE `tm_compra_credito`
  MODIFY `id_credito` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tm_configuracion`
--
ALTER TABLE `tm_configuracion`
  MODIFY `id_cfg` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `tm_empresa`
--
ALTER TABLE `tm_empresa`
  MODIFY `id_de` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `tm_gastos_adm`
--
ALTER TABLE `tm_gastos_adm`
  MODIFY `id_ga` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tm_impresora`
--
ALTER TABLE `tm_impresora`
  MODIFY `id_imp` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `tm_ingresos_adm`
--
ALTER TABLE `tm_ingresos_adm`
  MODIFY `id_ing` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tm_insumo`
--
ALTER TABLE `tm_insumo`
  MODIFY `id_ins` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tm_insumo_catg`
--
ALTER TABLE `tm_insumo_catg`
  MODIFY `id_catg` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tm_inventario`
--
ALTER TABLE `tm_inventario`
  MODIFY `id_inv` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tm_inventario_entsal`
--
ALTER TABLE `tm_inventario_entsal`
  MODIFY `id_es` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tm_margen_venta`
--
ALTER TABLE `tm_margen_venta`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `tm_mesa`
--
ALTER TABLE `tm_mesa`
  MODIFY `id_mesa` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `tm_pago`
--
ALTER TABLE `tm_pago`
  MODIFY `id_pago` int(2) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `tm_pedido`
--
ALTER TABLE `tm_pedido`
  MODIFY `id_pedido` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `tm_producto`
--
ALTER TABLE `tm_producto`
  MODIFY `id_prod` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `tm_producto_catg`
--
ALTER TABLE `tm_producto_catg`
  MODIFY `id_catg` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `tm_producto_ingr`
--
ALTER TABLE `tm_producto_ingr`
  MODIFY `id_pi` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tm_producto_pres`
--
ALTER TABLE `tm_producto_pres`
  MODIFY `id_pres` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `tm_proveedor`
--
ALTER TABLE `tm_proveedor`
  MODIFY `id_prov` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tm_repartidor`
--
ALTER TABLE `tm_repartidor`
  MODIFY `id_repartidor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4446;

--
-- AUTO_INCREMENT de la tabla `tm_rol`
--
ALTER TABLE `tm_rol`
  MODIFY `id_rol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `tm_salon`
--
ALTER TABLE `tm_salon`
  MODIFY `id_salon` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `tm_tipo_compra`
--
ALTER TABLE `tm_tipo_compra`
  MODIFY `id_tipo_compra` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `tm_tipo_doc`
--
ALTER TABLE `tm_tipo_doc`
  MODIFY `id_tipo_doc` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `tm_tipo_gasto`
--
ALTER TABLE `tm_tipo_gasto`
  MODIFY `id_tipo_gasto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `tm_tipo_medida`
--
ALTER TABLE `tm_tipo_medida`
  MODIFY `id_med` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `tm_tipo_pago`
--
ALTER TABLE `tm_tipo_pago`
  MODIFY `id_tipo_pago` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `tm_tipo_pedido`
--
ALTER TABLE `tm_tipo_pedido`
  MODIFY `id_tipo_pedido` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `tm_tipo_venta`
--
ALTER TABLE `tm_tipo_venta`
  MODIFY `id_tipo_venta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `tm_turno`
--
ALTER TABLE `tm_turno`
  MODIFY `id_turno` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `tm_usuario`
--
ALTER TABLE `tm_usuario`
  MODIFY `id_usu` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT de la tabla `tm_venta`
--
ALTER TABLE `tm_venta`
  MODIFY `id_venta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `resumen_diario_detalle`
--
ALTER TABLE `resumen_diario_detalle`
  ADD CONSTRAINT `FK_RDD_RES` FOREIGN KEY (`id_resumen`) REFERENCES `resumen_diario` (`id_resumen`),
  ADD CONSTRAINT `FK_RDD_VEN` FOREIGN KEY (`id_venta`) REFERENCES `tm_venta` (`id_venta`);

--
-- Filtros para la tabla `tm_aper_cierre`
--
ALTER TABLE `tm_aper_cierre`
  ADD CONSTRAINT `FK_ac_caja` FOREIGN KEY (`id_caja`) REFERENCES `tm_caja` (`id_caja`),
  ADD CONSTRAINT `FK_ac_turno` FOREIGN KEY (`id_turno`) REFERENCES `tm_turno` (`id_turno`),
  ADD CONSTRAINT `FK_ac_usu` FOREIGN KEY (`id_usu`) REFERENCES `tm_usuario` (`id_usu`);

--
-- Filtros para la tabla `tm_area_prod`
--
ALTER TABLE `tm_area_prod`
  ADD CONSTRAINT `FK_AP_IMP` FOREIGN KEY (`id_imp`) REFERENCES `tm_impresora` (`id_imp`);

--
-- Filtros para la tabla `tm_compra`
--
ALTER TABLE `tm_compra`
  ADD CONSTRAINT `FK_comp_prov` FOREIGN KEY (`id_prov`) REFERENCES `tm_proveedor` (`id_prov`),
  ADD CONSTRAINT `FK_comp_tipoc` FOREIGN KEY (`id_tipo_compra`) REFERENCES `tm_tipo_compra` (`id_tipo_compra`),
  ADD CONSTRAINT `FK_comp_tipod` FOREIGN KEY (`id_tipo_doc`) REFERENCES `tm_tipo_doc` (`id_tipo_doc`),
  ADD CONSTRAINT `FK_comp_usu` FOREIGN KEY (`id_usu`) REFERENCES `tm_usuario` (`id_usu`);

--
-- Filtros para la tabla `tm_compra_credito`
--
ALTER TABLE `tm_compra_credito`
  ADD CONSTRAINT `FK_compcre_idcomp` FOREIGN KEY (`id_compra`) REFERENCES `tm_compra` (`id_compra`);

--
-- Filtros para la tabla `tm_compra_detalle`
--
ALTER TABLE `tm_compra_detalle`
  ADD CONSTRAINT `FK_CDET_COM` FOREIGN KEY (`id_compra`) REFERENCES `tm_compra` (`id_compra`);

--
-- Filtros para la tabla `tm_credito_detalle`
--
ALTER TABLE `tm_credito_detalle`
  ADD CONSTRAINT `FK_CRED_CRED` FOREIGN KEY (`id_credito`) REFERENCES `tm_compra_credito` (`id_credito`),
  ADD CONSTRAINT `FK_cred_usu` FOREIGN KEY (`id_usu`) REFERENCES `tm_usuario` (`id_usu`);

--
-- Filtros para la tabla `tm_detalle_pedido`
--
ALTER TABLE `tm_detalle_pedido`
  ADD CONSTRAINT `FK_DPED_PED` FOREIGN KEY (`id_pedido`) REFERENCES `tm_pedido` (`id_pedido`),
  ADD CONSTRAINT `FK_DPED_PRES` FOREIGN KEY (`id_pres`) REFERENCES `tm_producto_pres` (`id_pres`),
  ADD CONSTRAINT `FK_DPED_USU` FOREIGN KEY (`id_usu`) REFERENCES `tm_usuario` (`id_usu`);

--
-- Filtros para la tabla `tm_detalle_venta`
--
ALTER TABLE `tm_detalle_venta`
  ADD CONSTRAINT `FK_DVEN_VEN` FOREIGN KEY (`id_venta`) REFERENCES `tm_venta` (`id_venta`);

--
-- Filtros para la tabla `tm_gastos_adm`
--
ALTER TABLE `tm_gastos_adm`
  ADD CONSTRAINT `FK_EADM_APC` FOREIGN KEY (`id_apc`) REFERENCES `tm_aper_cierre` (`id_apc`),
  ADD CONSTRAINT `FK_EADM_TGAS` FOREIGN KEY (`id_tipo_gasto`) REFERENCES `tm_tipo_gasto` (`id_tipo_gasto`),
  ADD CONSTRAINT `FK_EADM_USU` FOREIGN KEY (`id_usu`) REFERENCES `tm_usuario` (`id_usu`);

--
-- Filtros para la tabla `tm_ingresos_adm`
--
ALTER TABLE `tm_ingresos_adm`
  ADD CONSTRAINT `FK_IADM_APC` FOREIGN KEY (`id_apc`) REFERENCES `tm_aper_cierre` (`id_apc`),
  ADD CONSTRAINT `FK_IADM_USU` FOREIGN KEY (`id_usu`) REFERENCES `tm_usuario` (`id_usu`);

--
-- Filtros para la tabla `tm_insumo`
--
ALTER TABLE `tm_insumo`
  ADD CONSTRAINT `FK_ins_catg` FOREIGN KEY (`id_catg`) REFERENCES `tm_insumo_catg` (`id_catg`),
  ADD CONSTRAINT `FK_ins_med` FOREIGN KEY (`id_med`) REFERENCES `tm_tipo_medida` (`id_med`);

--
-- Filtros para la tabla `tm_inventario_entsal`
--
ALTER TABLE `tm_inventario_entsal`
  ADD CONSTRAINT `FK_INVES_RESP` FOREIGN KEY (`id_responsable`) REFERENCES `tm_usuario` (`id_usu`),
  ADD CONSTRAINT `FK_INVES_USU` FOREIGN KEY (`id_usu`) REFERENCES `tm_usuario` (`id_usu`);

--
-- Filtros para la tabla `tm_mesa`
--
ALTER TABLE `tm_mesa`
  ADD CONSTRAINT `fk_mesa_salon` FOREIGN KEY (`id_salon`) REFERENCES `tm_salon` (`id_salon`);

--
-- Filtros para la tabla `tm_pedido`
--
ALTER TABLE `tm_pedido`
  ADD CONSTRAINT `FK_ped_tp` FOREIGN KEY (`id_tipo_pedido`) REFERENCES `tm_tipo_pedido` (`id_tipo_pedido`),
  ADD CONSTRAINT `FK_ped_usu` FOREIGN KEY (`id_usu`) REFERENCES `tm_usuario` (`id_usu`);

--
-- Filtros para la tabla `tm_pedido_delivery`
--
ALTER TABLE `tm_pedido_delivery`
  ADD CONSTRAINT `FK_peddel_cli` FOREIGN KEY (`id_cliente`) REFERENCES `tm_cliente` (`id_cliente`),
  ADD CONSTRAINT `FK_peddel_ped` FOREIGN KEY (`id_pedido`) REFERENCES `tm_pedido` (`id_pedido`);

--
-- Filtros para la tabla `tm_pedido_llevar`
--
ALTER TABLE `tm_pedido_llevar`
  ADD CONSTRAINT `FK_pedlle_ped` FOREIGN KEY (`id_pedido`) REFERENCES `tm_pedido` (`id_pedido`);

--
-- Filtros para la tabla `tm_pedido_mesa`
--
ALTER TABLE `tm_pedido_mesa`
  ADD CONSTRAINT `FK_pedme_mesa` FOREIGN KEY (`id_mesa`) REFERENCES `tm_mesa` (`id_mesa`),
  ADD CONSTRAINT `FK_pedme_mozo` FOREIGN KEY (`id_mozo`) REFERENCES `tm_usuario` (`id_usu`),
  ADD CONSTRAINT `FK_pedme_ped` FOREIGN KEY (`id_pedido`) REFERENCES `tm_pedido` (`id_pedido`);

--
-- Filtros para la tabla `tm_producto`
--
ALTER TABLE `tm_producto`
  ADD CONSTRAINT `FK_prod_area` FOREIGN KEY (`id_areap`) REFERENCES `tm_area_prod` (`id_areap`),
  ADD CONSTRAINT `FK_prod_catg` FOREIGN KEY (`id_catg`) REFERENCES `tm_producto_catg` (`id_catg`);

--
-- Filtros para la tabla `tm_producto_ingr`
--
ALTER TABLE `tm_producto_ingr`
  ADD CONSTRAINT `FK_PING_MED` FOREIGN KEY (`id_med`) REFERENCES `tm_tipo_medida` (`id_med`),
  ADD CONSTRAINT `FK_PING_PRES` FOREIGN KEY (`id_pres`) REFERENCES `tm_producto_pres` (`id_pres`);

--
-- Filtros para la tabla `tm_producto_pres`
--
ALTER TABLE `tm_producto_pres`
  ADD CONSTRAINT `FK_PROP_PROD` FOREIGN KEY (`id_prod`) REFERENCES `tm_producto` (`id_prod`);

--
-- Filtros para la tabla `tm_tipo_pago`
--
ALTER TABLE `tm_tipo_pago`
  ADD CONSTRAINT `FK_TIPODEPAGO` FOREIGN KEY (`id_pago`) REFERENCES `tm_pago` (`id_pago`);

--
-- Filtros para la tabla `tm_usuario`
--
ALTER TABLE `tm_usuario`
  ADD CONSTRAINT `FK_usu_rol` FOREIGN KEY (`id_rol`) REFERENCES `tm_rol` (`id_rol`);

--
-- Filtros para la tabla `tm_venta`
--
ALTER TABLE `tm_venta`
  ADD CONSTRAINT `FK_venta_apc` FOREIGN KEY (`id_apc`) REFERENCES `tm_aper_cierre` (`id_apc`),
  ADD CONSTRAINT `FK_venta_cli` FOREIGN KEY (`id_cliente`) REFERENCES `tm_cliente` (`id_cliente`),
  ADD CONSTRAINT `FK_venta_td` FOREIGN KEY (`id_tipo_doc`) REFERENCES `tm_tipo_doc` (`id_tipo_doc`),
  ADD CONSTRAINT `FK_venta_tp` FOREIGN KEY (`id_tipo_pago`) REFERENCES `tm_tipo_pago` (`id_tipo_pago`),
  ADD CONSTRAINT `FK_venta_tpe` FOREIGN KEY (`id_tipo_pedido`) REFERENCES `tm_tipo_pedido` (`id_tipo_pedido`),
  ADD CONSTRAINT `FK_venta_usu` FOREIGN KEY (`id_usu`) REFERENCES `tm_usuario` (`id_usu`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
