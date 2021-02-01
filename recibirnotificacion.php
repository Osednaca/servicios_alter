<?php
include("includes.php");
$post_date  	= file_get_contents("php://input");
$data 			= json_decode($post_date);
$token 			= Auth::GetData(
       				$data->token
   				  );
$idusuario  	= $token->id;
$notificaciones = array();
$items 			= array();
$extras 		= array();

//Buscar
$stmt1 = $con->prepare("SELECT idservicio,incluyetramite, servicio.fecharegistro,tiempoestimadototal,valor,totalaproximado,tiposervicio,tipovehiculo, servicio.idcliente, nombre, apellido,telefonocelular, 
							(SELECT direccion FROM direccion WHERE orden=1 AND direccion.idservicio=notificacion.idservicio), 
							(SELECT indicaciones FROM direccion WHERE orden=1 AND direccion.idservicio=notificacion.idservicio),
							(SELECT direccion FROM direccion WHERE orden=2 AND direccion.idservicio=notificacion.idservicio), 
							(SELECT indicaciones FROM direccion WHERE orden=2 AND direccion.idservicio=notificacion.idservicio),servicio.idtiposervicio,servicio.idtipopago,tiempopreparacion,tiporegistro,idlugar
							FROM notificacion 
							INNER JOIN servicio USING(idservicio)
							LEFT JOIN servicio_lugar USING(idservicio)
							INNER JOIN lugar USING(idlugar)
							INNER JOIN usuario ON servicio.idcliente = usuario.idusuario 
							INNER JOIN tiposervicio USING(idtiposervicio) 
							INNER JOIN tipovehiculo USING(idtipovehiculo) 
						WHERE notificacion.idusuario=? AND notificacion.estatus=2");

$stmt1->bind_param("i", $idusuario);

$stmt1->execute();

$stmt1->bind_result($idservicio,$incluyetramite, $fecharegistro,$tiempoestimadototal,$valor,$totalaproximado,$tiposervicio,$tipovehiculo,$idcliente,$nombre,$apellido,$telefonocelular,$direccionpartida,$indicacion_partida,$direcciondestino,$indicacion_destino,$idtiposervicio,$idtipopago,$tiempopreparacion,$tipolugar,$idlugar);
$stmt1->store_result();
while($stmt1->fetch()){
	//Buscar datos de direcciones
	$direccionesextras = array();
	if($idtiposervicio == 1 OR $idtiposervicio == 2 OR $idtiposervicio == 3){
		$sql = "SELECT direccion,indicaciones,tiempoestimado,orden,ciudad 
								FROM direccion 
								LEFT JOIN ciudad USING(idciudad) 
							WHERE idservicio=? AND orden NOT IN(1,2)";
	}elseif($idtiposervicio == 4 OR $idtiposervicio == 5 OR $idtiposervicio == 6 OR $idtiposervicio == 7 OR $idtiposervicio == 8){
		$sql = "SELECT direccion,indicaciones,tiempoestimado,orden,ciudad 
								FROM direccion 
								LEFT JOIN ciudad USING(idciudad) 
							WHERE idservicio=?";					
	}
//echo $idtiposervicio;
	$stmt3 = $con->prepare("SELECT MAX(orden) FROM direccion WHERE idservicio = ?");
	$stmt3->bind_param("i", $idservicio);
	$stmt3->execute();
	$stmt3->bind_result($ultimadireccion);
	$stmt3->store_result();
	$stmt3->fetch();

	$stmt2 = $con->prepare($sql);
	$stmt2->bind_param("i", $idservicio);
	$stmt2->execute();
	$stmt2->bind_result($direccion,$indicaciones,$tiempoestimado,$orden,$ciudad);	
	
	while($stmt2->fetch()){
		if($orden == $ultimadireccion){
			$direccionfinal = true;
		}else{
			$direccionfinal = false;
		}	
		$direccionesextras[] = array('direccion'=>utf8_encode($direccion),'indicaciones'=>utf8_encode($indicaciones),'tiempoestimado'=>$tiempoestimado,'orden'=>$orden,'ciudad'=>utf8_encode($ciudad),'direccionfinal' => $direccionfinal,'ultimadireccion'=>$ultimadireccion);
	}

	$porservicioalter  		= select_config_alter("servicioalter");
	$porgrupo  				= select_config_alter("aportegrupo");
	$porhijos  				= select_config_alter("aportehijos");
	$pornietos 				= select_config_alter("aportenietos");
	$porbisnietos			= select_config_alter("aportebisnietos");
	$porgastostransaccion	= select_config_alter("comisionnequi");

	$poriva					= select_config_alter("iva");

	$servicioalter			= $valor*$porservicioalter;
	$servicioalter		    = $servicioalter +($servicioalter*$poriva);
	//$valorhijos				= $valor*$porhijos;
	//$valornietos			= $valor*$pornietos;
	//$valorbisnietos			= $valor*$porbisnietos;
	$valorgrupo				= $valor*$porgrupo;
	$gastostransaccionales	= ($valor+$servicioalter+$valorgrupo)*$porgastostransaccion;
	$costototal 			= $valor;
	$costocliente 			= $valor+$servicioalter+$valorgrupo+$gastostransaccionales;
	if($idtipopago == 4){
		$costototal 		= $costocliente;
	}

	if($idtiposervicio == 5 OR $idtiposervicio == 8){
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
	}

	$notificaciones[] = array('idservicio'=>$idservicio,'incluyetramite'=>$incluyetramite, 'fecharegistro'=>$fecharegistro,'tiempoestimadototal'=>$tiempoestimadototal,'valor'=>$valor,'costototal'=>$costototal,'totalaproximado'=>$totalaproximado,'tiposervicio'=>utf8_encode($tiposervicio),'tipovehiculo'=>utf8_encode($tipovehiculo),'iddestinatario'=>$idcliente, 'idcliente'=>$idcliente,'clientenombre'=>utf8_encode($nombre), 'clienteapellido'=>utf8_encode($apellido),'clientetelefono'=>$telefonocelular,'direccionpartida'=>utf8_encode($direccionpartida),'direcciondestino'=>utf8_encode($direcciondestino),'indicacion_partida'=>utf8_encode($indicacion_partida),'indicacion_destino'=>utf8_encode($indicacion_destino),'direccionesextras'=>$direccionesextras,'idtiposervicio'=>$idtiposervicio,'idtipopago'=>$idtipopago,'costocliente'=>$costocliente,'tiempopreparacion'=>$tiempopreparacion,'items'=>$items,'idlugar'=>$idlugar,'tiporegistrolugar'=>$tipolugar);
}

if(empty($notificaciones)){
	echo json_encode(array('respuesta' => false));
}else{
	echo json_encode(array('respuesta' => true, 'notificaciones'=> $notificaciones));
}

?>