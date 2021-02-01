<?php

include("includes.php");

$post_date  			= file_get_contents("php://input");
$data 					= json_decode($post_date);
$token 					= Auth::GetData(
     						$data->token
 						  );
$idcliente  			= $token->id;
$idtiposervicio			= $data->servicio->idtiposervicio;
$idtipovehiculo			= $data->servicio->idtipovehiculo;
$estatus				= 1;
$puntoentrega   		= $data->servicio->puntoentrega;
$puntoentregalat 		= $data->servicio->pelat;
$puntoentregalng 		= $data->servicio->pelng;
if(!empty($data->servicio->indicacionpe)){
	$indicacionpe		= $data->servicio->indicacionpe;
}else{
	$indicacionpe 		= "";
}
$total      			= $data->servicio->costototal;
$costoproductos			= $data->servicio->totalaproximado;
$tipopago				= $data->servicio->tipopago;
$fecharegistro   		= date("Y-m-d H:i:s");

$stmt = $con->prepare("INSERT INTO servicio(idtiposervicio, idtipovehiculo, idcliente, estatus, fecharegistro,  valor,idtipopago,total,totalaproximado) VALUES (?,?,?,?,?,?,?,?,?)");

$con->set_charset("utf8");

$stmt->bind_param("iiiississ", $idtiposervicio,$idtipovehiculo,$idcliente,$estatus,$fecharegistro,$total,$tipopago,$total,$costoproductos);

$stmt->execute();

if($stmt->error!=""){
	echo json_encode(array('respuesta' => false,'error'=>$stmt->error));
	reporte_error($idcliente,"test",$stmt->error,"registroserviciocompras.php","");
	die();
}else{
	// Registrar items
	// es necesario idciudad?
	$idservicio 	= $stmt->insert_id;
	$orden = 0;
	foreach ($data->items as $key => $value) {
		$direccion 		= $value->lugar;
		if(!empty($value->indicaciones)){
			$lista 			= $value->item." $ ".$value->valor." Indicaciones: ".$value->indicaciones;
		}else{
			$lista 			= $value->item." $ ".$value->valor;
		}
		$valor   		= $value->valor;
		$latitud   		= $value->lat;
		$longitud  		= $value->lng;
		$tipodireccion 	= 3;
		$stmt3 = $con->prepare("INSERT INTO direccion(idservicio, direccion, indicaciones, orden ,fecharegistrodir, lat, lng) VALUES 	(?,?,?,?,?,?,?)");
		/* bind parameters for markers */
		$stmt3->bind_param("ississs", $idservicio, $direccion, $lista, $orden, $fecharegistro, $latitud, $longitud);
		/* execute query */
		//echo "idservicio: $idservicio, idciudad: $idciudad, direccion: $direccion, indicaciones: $indicaciones, tiempoestimado: $tiempoestimado, tipodireccion: $tipodireccion, orden: $orden, fecharegistro: $fecharegistro"; die();
		$stmt3->execute();
		if($stmt3->error != ""){
			echo json_encode(array('respuesta' => false,'error'=>$stmt3->error));
			die();
		}
		$orden++;
	}
	// Registrar punto de destino
	// es necesario idciudad?
	$stmt2 = $con->prepare("INSERT INTO direccion(idservicio, direccion, indicaciones, orden, fecharegistrodir,lat,lng) VALUES(?,?,?,?,?,?,?)");

	/* bind parameters for markers */

	$stmt2->bind_param("ississs", $idservicio, $puntoentrega, $indicacionpe, $orden, $fecharegistro,$puntoentregalat,$puntoentregalng);
	/* execute query */
	$stmt2->execute();	

	//validar que todo salga bien con $stmt->error
	if($stmt2->error==""){
		echo json_encode(array('respuesta' => true,'idservicio' => $idservicio));			
	}else{
		echo json_encode(array('respuesta' => false,'error'=>$stmt2));
	}
}

?>