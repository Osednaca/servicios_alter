<?php
	include("includes/utils.php");
	$post_date  	= file_get_contents("php://input");
	$data 			= json_decode($post_date);
	$idservicio  	= $data->idservicio;
	$idlugar 		= $data->idlugar;
	$now 			= date("Y-m-d H:i:s");
	$idnotificacion = $data->idnotificacion;
	$Direcciones 	= array();
	$items 			= array();
	$idusuario  	= "";

	$stmt = $con->prepare("SELECT estatus FROM notificacionlugar WHERE idservicio=? AND idlugar = ?");
	/* bind parameters for markers */
	$stmt->bind_param("ii", $idservicio,$idlugar);
	
	/* execute query */
	$stmt->execute();
	$stmt->bind_result($estatus);
	$stmt->fetch();
	
	$stmt->free_result();
	
	if($estatus == 1){
		$stmt1 = $con->prepare("SELECT (SELECT direccion FROM direccion WHERE idservicio = servicio.idservicio AND orden=0),idservicio,incluyetramite,servicio.estatus,servicio.fecharegistro,fechaculminacion,tiempoestimadototal,valor,proveedor.idusuario,proveedor.cedula,proveedor.nombre,proveedor.apellido,proveedor.imgusuario,vehiculo.placa, proveedor.telefonocelular,servicio.idtipopago,nombrelugar,imagen,tiempopreparacion,idtiposervicio
							FROM servicio 
							INNER JOIN servicio_lugar USING(idservicio)
							INNER JOIN lugar USING(idlugar)
							LEFT JOIN usuario as proveedor ON servicio.idproveedor=proveedor.idusuario
							LEFT JOIN vehiculo ON vehiculo.idvehiculo = proveedor.idvehiculoactivo
							WHERE idservicio=?");
		/* bind parameters for markers */
		$stmt1->bind_param("i", $idservicio);
		
		/* execute query */
		$stmt1->execute();
		
		if($stmt1->error == ""){
			/* bind result variables */
			$stmt1->bind_result($direccionpartida,$idservicio,$incluyetramite,$estatus,$fecharegistro,$fechaculminacion,$tiempoestimadototal,$valor,$idproveedor,$cedulaproveedor,$nombreproveedor,$apellidoproveedor,$imgusuario,$placa,$telefono,$idtipopago,$nombrelugar,$logolugar,$tiempopreparacion,$tiposervicio);
	
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

		if(empty($idproveedor)){
			$stmt1->free_result();
			$sql = "SELECT idusuario FROM lugar WHERE idlugar=?";
			$stmt5 = $con->prepare($sql);
			$stmt5->bind_param("i", $idlugar);
			$stmt5->execute();
			$stmt5->bind_result($idusuario);
			$stmt5->fetch();
			$stmt5->free_result();

			$idproveedor 	 = $idlugar;
			$nombreproveedor = $nombrelugar;
			$imgusuario 	 = $logolugar;
			$placa 			 = "N/A";
			$telefono 		 = "";
		}else{
			$idusuario = $idproveedor;
		}
		$stmt1->free_result();
		if($tiposervicio == 9){
			$sql = "SELECT item FROM domicilio_tienda_items
			WHERE idservicio=?";
			$stmt4 = $con->prepare($sql);
			$stmt4->bind_param("i", $idservicio);
			$stmt4->execute();
			$stmt4->bind_result($item);

			while($stmt4->fetch()){
				$items[] = array('item'=>$item);
			}			
		}		

		$servicio = array('idservicio'=>$idservicio,'incluyetramite'=>$incluyetramite,'estatus'=>$estatus,'fecharegistro'=>$fecharegistro,'fechaculminacion'=>$fechaculminacion,'tiempoestimadototal'=>$tiempoestimadototal,'valor'=>$valor,'idproveedor'=>$idproveedor,'cedulaproveedor'=>$cedulaproveedor,'nombreproveedor'=>utf8_encode($nombreproveedor),'apellidoproveedor'=>utf8_encode($apellidoproveedor),'telefono'=>$telefono,'imgusuario'=>$imgusuario,'placa' => $placa,'tipopago' => $tipopagotxt,'tiempopreparacion' => $tiempopreparacion,'items'=>$items,'direccion'=>utf8_encode($direccionpartida),'idusuario'=>$idusuario);
		
		if(!empty($servicio)){
			echo json_encode(array('respuesta' => true, 'servicio'=>$servicio));	
		}else{
			echo json_encode(array('respuesta' => false, 'mensaje'=>''));
		}
		//Cancelado
		//if($estatus == 0){
		//	echo json_encode(array('respuesta' => false));	
		//}
	}else{
		echo json_encode(array('respuesta' => false, 'mensaje' => $stmt1->error));
	}
	}elseif($estatus == 0){
		//Rechazada
		echo json_encode(array('respuesta' => true));
	}elseif($estatus == 2){
		//No respondida
		echo json_encode(array('respuesta' => false, 'mensaje'=>'No respondida'));
	}elseif($estatus == 3){
		//Aceptada Negocio
		$stmt = $con->prepare("SELECT estatus FROM servicio WHERE idservicio=? "); // 
		$stmt->bind_param("i",$idservicio);
		$stmt->execute();
		$stmt->bind_result($estatus2);
		$stmt->fetch();
		if($estatus2 != 0){
			echo json_encode(array('respuesta' => false));
		}elseif($estatus2 == 0){
			echo json_encode(array('respuesta' => true, 'mensaje'=>'Cancelado'));
		}
	}