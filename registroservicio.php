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
$tiempoestimadototal	= $data->servicio->tiempoestimadototal;
$direccionpartida		= $data->servicio->direccionpartida;
if(!empty($data->servicio->indicacion_partida)){
$indicacionpartida		= $data->servicio->indicacion_partida;
}else{
	$indicacionpartida = "";
}
$direcciondestino		= $data->servicio->direcciondestino;
if(!empty($data->servicio->indicacion_destino)){
$indicaciondestino		= $data->servicio->indicacion_destino;
}else{
	$indicaciondestino = "";
}
$total      			= $data->servicio->costototal;
$tipopago				= $data->servicio->tipopago;
$fecharegistro   		= date("Y-m-d H:i:s");

$stmt = $con->prepare("INSERT INTO servicio(idtiposervicio, idtipovehiculo, idcliente, estatus, fecharegistro, tiempoestimadototal, valor,idtipopago,total) VALUES (?,?,?,?,?,?,?,?,?)");

$con->set_charset("utf8");

$stmt->bind_param("iisisssis", $idtiposervicio,$idtipovehiculo,$idcliente,$estatus,$fecharegistro,$tiempoestimadototal,$total,$tipopago,$total);

$stmt->execute();

if($stmt->error!=""){
	echo json_encode(array('respuesta' => false,'error'=>$stmt->error));
	reporte_error($idcliente,"test",$stmt->error,"registroservicio.php","");	
	die();
}else{
	// Registrar Direccion de partida y de destino
	$idservicio = $stmt->insert_id;
	$tipodireccion1 = 1;
	$tipodireccion2 = 2;
	$orden1			= 1;
	$orden2			= 2;

	$stmt2 = $con->prepare("INSERT INTO direccion(idservicio, direccion, indicaciones, tiempoestimado, tipodireccion, orden, fecharegistrodir) VALUES(?,?,?,?,?,?,?),(?,?,?,?,?,?,?)");

	/* bind parameters for markers */

	$stmt2->bind_param("isssiisisssiis", $idservicio, $direccionpartida, $indicacionpartida, $tiempoestimado, $tipodireccion1, $orden1, $fecharegistro, $idservicio, $direcciondestino, $indicaciondestino, $tiempoestimado, $tipodireccion2, $orden2, $fecharegistro);
	/* execute query */
	$stmt2->execute();
	// Registrar direcciones extra
	//var_dump($data->direcciones); die();
	$orden = 3;
	foreach ($data->direcciones as $key => $value) {
		$direccion 		= $value->direccionextra;
		$tiempoestimado = $value->tiempoestimado;
		$idciudad 		= $value->idciudad;
		if(!empty($value->indicaciones)){
			$indicaciones   = $value->indicaciones;
		}else{
			$indicaciones  = "";
		}
		$tipodireccion 	= 3; //Extra
		$stmt3 = $con->prepare("INSERT INTO direccion(idservicio, idciudad, direccion, indicaciones, tiempoestimado, tipodireccion, orden ,fecharegistrodir) VALUES 	(?,?,?,?,?,?,?,?)");
		/* bind parameters for markers */
		$stmt3->bind_param("iisssiis", $idservicio, $idciudad, $direccion, $indicaciones, $tiempoestimado, $tipodireccion, $orden, $fecharegistro);
		/* execute query */
		//echo "idservicio: $idservicio, idciudad: $idciudad, direccion: $direccion, indicaciones: $indicaciones, tiempoestimado: $tiempoestimado, tipodireccion: $tipodireccion, orden: $orden, fecharegistro: $fecharegistro"; die();
		$stmt3->execute();
		if($stmt3->error != ""){
			echo json_encode(array('respuesta' => false,'error'=>$stmt3->error));
			reporte_error($idcliente,"test",$stmt3->error,"registroservicio.php","");
			die();
		}
		$orden++;
	}

	//validar que todo salga bien con $stmt->error
	if($stmt2->error==""){
		echo json_encode(array('respuesta' => true,'idservicio' => $idservicio));			
	}else{
		echo json_encode(array('respuesta' => false,'error'=>$stmt2));
		reporte_error($idcliente,"test",$stmt2->error,"registroservicio.php","");
	}
}

?>