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
		//Items del servicio
		$sql = "SELECT iditem,titulo,imagen,cantidad,instrucciones FROM domicilio_items
							INNER JOIN lugar_items USING(iditem)
							WHERE idservicio=? AND extra = 0";
		$stmt4 = $con->prepare($sql);
		$stmt4->bind_param("i", $idservicio);
		$stmt4->execute();
		$stmt4->bind_result($iditem,$titulo,$imagen,$cantidad,$instrucciones);	
		
		while($stmt4->fetch()){
			$items[] = array('iditem'=>$iditem,'titulo'=>utf8_encode($titulo),'imagen'=>$imagen,'cantidad'=>$cantidad,'instrucciones'=>$instrucciones);
		}
	
		$descripcion = "Pedido:";
	
		foreach ($items as &$i) {
			$descripcion .= $i["cantidad"]." ".$i["titulo"]."\n";
			$descripcion .= "Instrucciones: ".$i["instrucciones"]."\n";
			//Extras del servicio
			$sql2 = "SELECT titulo FROM domicilio_items
									INNER JOIN items_adicion USING(iditemadicion)
									WHERE idservicio=? AND iditem = ? AND extra = 1";
			
			$stmt5 = $con->prepare($sql2);
			$stmt5->bind_param("ii", $idservicio,$i["iditem"]);
			$stmt5->execute();
			$stmt5->bind_result($titulo2);
			while($stmt5->fetch()){
				$extras[] = array('titulo'=>utf8_encode($titulo2));
				$descripcion .= "Extra: \n ".$extras[0]["titulo"];
			}
			if(!empty($extras)){
				$i["extras"]  = $extras;
			}
		}		
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

		$servicio = array('idservicio'=>$idservicio,'incluyetramite'=>$incluyetramite,'estatus'=>$estatus,'fecharegistro'=>$fecharegistro,'fechaculminacion'=>$fechaculminacion,'tiempoestimadototal'=>$tiempoestimadototal,'total'=>$valor,'idproveedor'=>$idproveedor,'cedulaproveedor'=>$cedulaproveedor,'nombre'=>utf8_encode($nombreproveedor),'apellido'=>utf8_encode($apellidoproveedor),'telefonocelular'=>$telefono,'imgusuario'=>$imgusuario,'placa' => $placa,'tipopago' => $tipopagotxt,'descripcion'=>$descripcion);
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