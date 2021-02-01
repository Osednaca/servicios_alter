<?php
include("includes.php");
$post_date  	= file_get_contents("php://input");
$data 			= json_decode($post_date);
$token 			= Auth::GetData(
       				$data->token
   				  );
$idusuario  	= $token->id;
$notificaciones = array();

//Buscar
$stmt1 = $con->prepare("SELECT idservicio, servicio.fecharegistro,tiempoestimadototal,valor,totalaproximado,tiposervicio,tipovehiculo, servicio.idcliente, nombre, apellido,telefonocelular,idlugar,idtiposervicio,idtipopago,tiporegistro,tiempopreparacion
							FROM notificacion 
							INNER JOIN servicio USING(idservicio) 
							INNER JOIN servicio_lugar USING(idservicio)
							INNER JOIN lugar USING(idlugar)
							INNER JOIN usuario ON servicio.idcliente = usuario.idusuario 
							INNER JOIN tiposervicio USING(idtiposervicio) 
							INNER JOIN tipovehiculo USING(idtipovehiculo) 
						WHERE notificacion.idusuario=? AND notificacion.estatus=2");

$stmt1->bind_param("i", $idusuario);

$stmt1->execute();

$stmt1->bind_result($idservicio, $fecharegistro,$tiempoestimadototal,$valor,$totalaproximado,$tiposervicio,$tipovehiculo,$idcliente,$nombre,$apellido,$telefonocelular,$idlugar,$idtiposervicio,$idtipopago,$tiporegistro,$tiempopreparacion);
$stmt1->store_result();
while($stmt1->fetch()){
	//Buscar datos de direcciones
	$direccionesextras = array();
	//Direccion del Restaurant
	$sql = "SELECT direccion FROM lugar WHERE idlugar=?";

	$stmt2 = $con->prepare($sql);
	$stmt2->bind_param("i", $idlugar);
	$stmt2->execute();
	$stmt2->bind_result($direccion);	
	
	while($stmt2->fetch()){
		$direccionlugar = utf8_encode($direccion);
	}

	//Direccion de Entrega
	$sql = "SELECT direccion 
					FROM direccion
						WHERE idservicio=? AND orden = 0";

	$stmt3 = $con->prepare($sql);
	$stmt3->bind_param("i", $idservicio);
	$stmt3->execute();
	$stmt3->bind_result($direccion);	
	
	while($stmt3->fetch()){
		$direccionesextras[] = array('direccion'=>utf8_encode($direccion));
	}

	//Items del servicio
	$sql = "SELECT iditem,titulo,imagen,cantidad,instrucciones FROM domicilio_items
						INNER JOIN lugar_items USING(iditem)
						WHERE idservicio=? AND extra = 0";

	$stmt4 = $con->prepare($sql);
	$stmt4->bind_param("i", $idservicio);
	$stmt4->execute();
	$stmt4->bind_result($iditem,$titulo,$imagen,$cantidad,$instrucciones);	
	
	while($stmt4->fetch()){
		$items[] = array('iditem'=>$iditem,'cantidad'=>$cantidad,'titulo'=>utf8_encode($titulo),'imagen'=>utf8_encode($imagen),'instrucciones'=>$instrucciones);
	}

	foreach ($items as &$i) {
		//Extras del servicio
		$iditemsql = $i["iditem"];
		$extras = array();
		$sql2 = "SELECT titulo,precio FROM domicilio_items
								INNER JOIN items_adicion ON domicilio_items.iditemadicion = items_adicion.iditemadicion
								WHERE idservicio=? AND domicilio_items.iditem = ? AND extra = 1";
		
		$stmt5 = $con->prepare($sql2);
		$stmt5->bind_param("ii", $idservicio, $iditemsql);
		$stmt5->execute();
		$stmt5->bind_result($titulo2,$precio);
		while($stmt5->fetch()){
			$extras[] = array('titulo'=>utf8_encode($titulo2),'precio'=>$precio);
		}
		if(!empty($extras)){
			$i["extras"] = $extras;
		}
	}

	$notificaciones[] = array('idservicio'=>$idservicio, 'fecharegistro'=>$fecharegistro,'tiempoestimadototal'=>$tiempoestimadototal,'valor'=>$valor,'costototal'=>$valor,'totalaproximado'=>$totalaproximado,'tiposervicio'=>utf8_encode($tiposervicio),'tipovehiculo'=>utf8_encode($tipovehiculo),'iddestinatario'=>$idcliente, 'idcliente'=>$idcliente,'clientenombre'=>utf8_encode($nombre), 'clienteapellido'=>utf8_encode($apellido),'clientetelefono'=>$telefonocelular,'direccionlugar'=>$direccionlugar,'direccionesextras'=>$direccionesextras,'items'=>$items,'idtiposervicio'=>$idtiposervicio,'idtipopago'=>$idtipopago,'tiporegistrolugar'=>$tiporegistro,'idlugar'=>$idlugar,'tiempopreparacion'=>$tiempopreparacion);
}

if(empty($notificaciones)){
	echo json_encode(array('respuesta' => false));
}else{
	echo json_encode(array('respuesta' => true, 'notificaciones'=> $notificaciones));
}

?>