<?php
include("includes.php");
session_start();
$notificaciones = array();
$post_date  	= file_get_contents("php://input");
$data 			= json_decode($post_date);
$token 			= Auth::GetData(
    				$data->token
				);
$idusuario  	= $token->id;
$idlugar 		= $data->idlugar;
$items 			= array();

//Buscar
$stmt1 = $con->prepare("SELECT idservicio,incluyetramite, servicio.fecharegistro,tiempoestimadototal,valor,totalaproximado,tiposervicio,tipovehiculo, servicio.idcliente, nombre, apellido,telefonocelular, direccion.lat,direccion.lng,
							(SELECT direccion FROM direccion WHERE orden=0 AND direccion.idservicio=notificacionlugar.idservicio), 
							(SELECT indicaciones FROM direccion WHERE orden=0 AND direccion.idservicio=notificacionlugar.idservicio),servicio.idtiposervicio,servicio.idtipopago,servicio.idtipovehiculo,lugar.lat,lugar.lng
							FROM notificacionlugar 
							INNER JOIN lugar USING(idlugar)
							INNER JOIN servicio USING(idservicio) 
							INNER JOIN direccion USING(idservicio)
							INNER JOIN usuario ON servicio.idcliente = usuario.idusuario 
							LEFT JOIN tiposervicio USING(idtiposervicio) 
							INNER JOIN tipovehiculo USING(idtipovehiculo) 
						WHERE notificacionlugar.idlugar IN($idlugar) AND notificacionlugar.estatus=2 ORDER BY fechanotificacion");

$stmt1->execute();

$stmt1->bind_result($idservicio,$incluyetramite, $fecharegistro,$tiempoestimadototal,$valor,$totalaproximado,$tiposervicio,$tipovehiculo,$idcliente,$nombre,$apellido,$telefonocelular,$dirlat,$dirlng,$direcciondestino,$indicacion_destino,$idtiposervicio,$idtipopago,$idtipovehiculo,$lat,$lng);
$stmt1->store_result();
$direccionpartida = "$dirlat,$dirlng";
while($stmt1->fetch()){
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
	if($idtiposervicio == 9){
		$sql = "SELECT item FROM domicilio_tienda_items
							WHERE idservicio=?";
		$stmt4 = $con->prepare($sql);
		$stmt4->bind_param("i", $idservicio);
		$stmt4->execute();
		$stmt4->bind_result($item);
		
		while($stmt4->fetch()){
			$items[] = array('item'=>utf8_encode($item));
		}
	}else{
		$sql = "SELECT iditem,titulo,imagen,cantidad,instrucciones FROM domicilio_items
		INNER JOIN lugar_items USING(iditem)
		WHERE idservicio=? AND extra = 0";
		$stmt4 = $con->prepare($sql);
		$stmt4->bind_param("i", $idservicio);
		$stmt4->execute();
		$stmt4->bind_result($iditem,$titulo,$imagen,$cantidad,$instrucciones);	

		while($stmt4->fetch()){
		$items[] = array('iditem'=>$iditem,'titulo'=>utf8_encode($titulo),'imagen'=>utf8_encode($imagen),'cantidad'=>$cantidad,'instrucciones'=>$instrucciones);
		}
	}

	$descripcion = "Pedido:";

	if($idtiposervicio == 9){
		foreach ($items as &$i) {
			$descripcion .= utf8_encode($i["item"])."<br>";
		}		
	}else{
		foreach ($items as &$i) {
			$descripcion .= $i["cantidad"]." ".utf8_encode($i["titulo"])."<br>";
			$descripcion .= "<b>Instrucciones:</b> ".$i["instrucciones"]."<br>";
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
				$descripcion .= "<b>Extra:</b> <br> ".utf8_encode($extras[0]["titulo"]);
			}
			if(!empty($extras)){
				$i["extras"]  = $extras;
			}
		}
	}

	$notificaciones[] = array('idservicio'=>$idservicio,'incluyetramite'=>$incluyetramite, 'fecharegistro'=>$fecharegistro,'tiempoestimadototal'=>$tiempoestimadototal,'valor'=>$valor,'totalaproximado'=>$totalaproximado,'tiposervicio'=>utf8_encode($tiposervicio),'tipovehiculo'=>utf8_encode($tipovehiculo),'iddestinatario'=>$idcliente, 'idcliente'=>$idcliente,'clientenombre'=>utf8_encode($nombre), 'clienteapellido'=>utf8_encode($apellido),'clientetelefono'=>$telefonocelular,'direccionpartida'=>utf8_encode($direccionpartida),'direcciondestino'=>utf8_encode($direcciondestino),'indicacion_destino'=>utf8_encode($indicacion_destino),'idtiposervicio'=>$idtiposervicio,'idtipopago'=>$idtipopago,'idtipovehiculo'=>$idtipovehiculo,'latlugar'=>$lat,'lnglugar'=>$lng,'direccionesextras'=>$direccionesextras,'items'=>$items,'descripcion'=>utf8_encode($descripcion));
}
//var_dump($notificaciones); die;
$stmt1 = $con->prepare("UPDATE notificacionlugar SET notificacionlugar.estatus=3 WHERE notificacionlugar.idlugar=? AND notificacionlugar.estatus=2");

$stmt1->bind_param("i", $idlugar);

$stmt1->execute();
//var_dump($notificaciones); die('hola');
if(empty($notificaciones)){
	echo json_encode(array('respuesta' => false));
}else{
	echo json_encode(array('respuesta' => true, 'notificaciones'=> $notificaciones));
}

?>