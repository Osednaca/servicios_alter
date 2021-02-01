<?php
	include("includes/utils.php");
	$post_date  	= file_get_contents("php://input");
	$data 			= json_decode($post_date);
	$idservicio  	= $data->idservicio;
	$idusuario 		= $data->idusuario;
	$now 			= date("Y-m-d H:i:s");
	$idnotificacion = $data->idnotificacion;
	$Direcciones 	= array();

	$stmt = $con->prepare("SELECT estatus FROM notificacion WHERE idservicio=? AND idusuario = ?");
	/* bind parameters for markers */
	$stmt->bind_param("ii", $idservicio,$idusuario);
	
	/* execute query */
	$stmt->execute();
	$stmt->bind_result($estatus);
	$stmt->fetch();
	
	$stmt->free_result();
	
	if($estatus == 1){
		$stmt1 = $con->prepare("SELECT (SELECT direccion FROM direccion WHERE idservicio = servicio.idservicio AND orden=1),idservicio,incluyetramite,servicio.estatus,servicio.fecharegistro,fechaculminacion,tiempoestimadototal,valor,proveedor.idusuario,proveedor.cedula,proveedor.nombre,proveedor.apellido,proveedor.imgusuario,vehiculo.placa, proveedor.telefonocelular,servicio.idtipopago
							FROM servicio 
							LEFT JOIN usuario as proveedor ON servicio.idproveedor=proveedor.idusuario
							INNER JOIN vehiculo ON vehiculo.idvehiculo = proveedor.idvehiculoactivo
							WHERE idservicio=?");
		/* bind parameters for markers */
		$stmt1->bind_param("i", $idservicio);
		
		/* execute query */
		$stmt1->execute();
		
		if($stmt1->error == ""){
			/* bind result variables */
			$stmt1->bind_result($direccionpartida,$idservicio,$incluyetramite,$estatus,$fecharegistro,$fechaculminacion,$tiempoestimadototal,$valor,$idproveedor,$cedulaproveedor,$nombreproveedor,$apellidoproveedor,$imgusuario,$placa,$telefono,$idtipopago);
	
		$stmt1->fetch();
		switch ($idtipopago) {
			case 1:
				$tipopagotxt = "Nequi";
				break;
			case 3:
				$tipopagotxt = "Saldo Alter";
				break;
			case 4:
				$tipopagotxt = "Efectivo";
				break;								
		}

		$servicio = array('idservicio'=>$idservicio,'incluyetramite'=>$incluyetramite,'estatus'=>$estatus,'fecharegistro'=>$fecharegistro,'fechaculminacion'=>$fechaculminacion,'tiempoestimadototal'=>$tiempoestimadototal,'valor'=>$valor,'idproveedor'=>$idproveedor,'cedulaproveedor'=>$cedulaproveedor,'nombreproveedor'=>utf8_encode($nombreproveedor),'apellidoproveedor'=>utf8_encode($apellidoproveedor),'telefono'=>$telefono,'imgusuario'=>$imgusuario,'placa' => $placa,'tipopago' => $tipopagotxt);
		$stmt1->free_result();

		if($idproveedor!=""){

			echo json_encode(array('respuesta' => true, 'servicio'=>$servicio));	
		}else{
			echo json_encode(array('respuesta' => false));
		}
	}else{
		echo json_encode(array('respuesta' => false, 'mensaje' => $stmt1->error));
	}
	}elseif($estatus == 0){
		//Rechazada
		echo json_encode(array('respuesta' => true));
	}elseif($estatus == 2){
		//No respondida
		echo json_encode(array('respuesta' => false));
	}