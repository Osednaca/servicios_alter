<?php
	include("includes.php");

	$post_date  				= file_get_contents("php://input");
	$data 						= json_decode($post_date);
	$token 						= Auth::GetData(
        							$data->token
    							  );
	$idusuario  				= $token->id;
	$filtros 					= "";
	$a_params 					= array();
	$servicios 					= array();
	$a_params[]    				= & $idusuario;
	$a_params[]    				= & $idusuario;
	$tipostring 				= "ss";
	$poraportegrupo				= select_config_alter("aportegrupo");
	$porservicioalter			= select_config_alter("servicioalter");
	$porgastostransaccionales	= select_config_alter("comisionnequi");
	$poriva						= select_config_alter("iva");

	//Filtros
	if(!empty($data->anio)){
		$filtros 	   	= 	"AND YEAR(fecharegistro) = ?";
		$a_params[] 	= & $data->anio;
		$tipostring		.= 	"i";
	}
	if(!empty($data->mes)){
		$filtros 	   	= 	"AND MONTH(fecharegistro) = ?";
		$a_params[] 	= & $data->mes;
		$tipostring		.= 	"i";
	}
	if(!empty($data->dia)){
		$filtros 	   .= " AND DAY(fecharegistro) = ?";
		$a_params[] 	= & $data->dia;
		$tipostring	   .= 	"s";
	}
	if(!empty($data->estatus)){
		if($data->estatus=="enproceso"){
			$filtros   .= " AND servicio.estatus IN(1,2,3,4,10)"; //2 = En Proceso, 3 = Pagado
		}
	}

array_unshift($a_params,$tipostring);

$stmt = $con->prepare("SELECT idservicio,incluyetramite,servicio.estatus,servicio.fecharegistro,fechaculminacion,tiempoestimadototal,valor,idproveedor,usuario.cedula,usuario.nombre,usuario.apellido,proveedor.cedula,proveedor.nombre,proveedor.apellido,servicio.idtipopago,servicio.idtiposervicio,totalaproximado
						FROM servicio 
						INNER JOIN usuario ON idcliente=usuario.idusuario 
						LEFT JOIN usuario as proveedor ON idproveedor=proveedor.idusuario 
						LEFT JOIN tiposervicio USING(idtiposervicio) 
						INNER JOIN tipovehiculo USING(idtipovehiculo) 
						WHERE (idcliente=? OR idproveedor=?) $filtros");

call_user_func_array(array($stmt, 'bind_param'), $a_params);

$stmt->execute();

if($stmt->error == ""){
	/* bind result variables */
	$stmt->bind_result($idservicio,$incluyetramite,$estatus,$fecharegistro,$fechaculminacion,$tiempoestimadototal,$valor,$idproveedor,$cedulacliente,$nombrecliente,$apellidocliente,$cedulaproveedor,$nombreproveedor,$apellidoproveedor,$idtipopago,$idtiposervicio,$totalaproximado);
	$stmt->store_result();
	while ($stmt->fetch()) {
    	$valorgrupo            		= $valor * $poraportegrupo;
    	$servicioalter         		= $valor * $porservicioalter;
    	$servicioalter         		= $servicioalter + ($servicioalter * $poriva);
    	$gastostransaccionales 		= ($valor + $servicioalter + $valorgrupo)* $porgastostransaccionales;

		if($idproveedor == $idusuario){
			$escliente   = false;
			$nombre    	 = $nombrecliente;
			$apellido  	 = $apellidocliente;
			if($idtipopago == 4){
				$valortotal  = round($valor + $valorgrupo + $servicioalter + $gastostransaccionales);
			}else{
				$valortotal  = round($valor - $valorgrupo - $servicioalter - $gastostransaccionales);
			}
			if($idtiposervicio == 5){
				$valortotal  = round($valor + $totalaproximado);
			}
		}else{
			$escliente   = true;
			$nombre    	 = $nombreproveedor;
			$apellido  	 = $apellidoproveedor;
			$valortotal  = round($valor + $valorgrupo + $servicioalter + $gastostransaccionales);
			if($idtiposervicio == 5){
				$valortotal  = round($valor + $totalaproximado);
			}
		}

		$direcciones = array();
		//$stmt->free_result();
		$stmt1 = $con->prepare("SELECT ciudad,direccion FROM direccion INNER JOIN ciudad USING(idciudad) WHERE idservicio = ? ORDER BY orden");
		$stmt1->bind_param("i",$idservicio);
		$stmt1->execute();
		$stmt1->bind_result($ciudad,$direccion);
		while ($stmt1->fetch()) {
			$direcciones[] = array("ciudad"=>utf8_encode($ciudad),"direccion"=>utf8_encode($direccion));
		}
		$servicios[] = array('idservicio'=>$idservicio,'incluyetramite'=>$incluyetramite,'estatus'=>$estatus,'fecharegistro'=>$fecharegistro,'fechaculminacion'=>$fechaculminacion,'tiempoestimadototal'=>$tiempoestimadototal,'valor'=>$valor,'valortotal'=>$valortotal,'idproveedor'=>$idproveedor,'escliente'=>$escliente,'nombre'=>utf8_encode($nombre.' '.$apellido),'direcciones'=>$direcciones,'idtiposervicio'=>$idtiposervicio);
	}
	
	if(!empty($servicios)){
		echo json_encode(array('respuesta' => true, 'servicios'=>$servicios));
	}else{
		echo json_encode(array('respuesta' => false, 'mensaje' => 'No se encontraron servicios'));
	}
}else{
	echo json_encode(array('respuesta' => false, 'mensaje' => $stmt->error));
}

?>