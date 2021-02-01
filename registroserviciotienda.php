<?php

include("includes.php");
$post_date  			= file_get_contents("php://input");
$data 					= json_decode($post_date);
$token 					= Auth::GetData(
     						$data->token
 						  );
$idcliente  			= $token->id;
$idproveedor            = $data->servicio->idproveedor;
$idtiposervicio			= 9;
$idtipovehiculo			= 7;
$estatus				= 1;
$idlugar				= $data->servicio->idlugar;
$puntoentrega   		= $data->servicio->puntoentrega;
$puntoentregalat 		= $data->servicio->lat;
$puntoentregalng 		= $data->servicio->lng;
if(!empty($data->servicio->indicacionpe)){
	$indicacionpe		= $data->servicio->indicacionpe;
}else{
	$indicacionpe 		= "";
}
$tipopago				= $data->servicio->tipopago;
$fecharegistro   		= date("Y-m-d H:i:s");
$valor 					= 0;

$stmt = $con->prepare("INSERT INTO servicio(idtiposervicio, idtipovehiculo, idcliente, idproveedor, estatus, fecharegistro,valor,idtipopago,total,totalaproximado) VALUES (?,?,?,?,?,?,?,?,?,?)");

$con->set_charset("utf8");

$stmt->bind_param("iiiiississ", $idtiposervicio,$idtipovehiculo,$idcliente,$idproveedor,$estatus,$fecharegistro,$valor,$tipopago,$valor,$valor);

$stmt->execute();

if($stmt->error!=""){
	echo json_encode(array('respuesta' => false,'error'=>$stmt->error));
	reporte_error($idcliente,"test",$stmt->error,"registroserviciotienda.php","");
	die();
}else{
	$idservicio = $stmt->insert_id;
	$stmt5 = $con->prepare("INSERT INTO servicio_lugar(idservicio, idlugar) VALUES (?,?)");

	$stmt5->bind_param("ii", $idservicio,$idlugar);

	$stmt5->execute();

	// Registrar direccion de entrega
	$orden 		= 0;
	$stmt2 		= $con->prepare("INSERT INTO direccion(idservicio, direccion, indicaciones, orden ,fecharegistrodir, lat, lng) VALUES 	(?,?,?,?,?,?,?)");
	$con->set_charset("utf8");
	$stmt2->bind_param("ississs", $idservicio, $puntoentrega, $indicacionpe, $orden, $fecharegistro,$puntoentregalat,$puntoentregalng);
	$stmt2->execute();
	// Registrar items
	$i = 0;
	foreach ($data->items as $key => $value) {
		$item 	= $value->item;
		$stmt3 = $con->prepare("INSERT INTO domicilio_tienda_items(idservicio, item) VALUES (? , ?)");
		/* bind parameters for markers */
		$stmt3->bind_param("is", $idservicio, $item);
		/* execute query */
		$stmt3->execute();
		if($stmt3->error != ""){
			echo json_encode(array('respuesta' => false,'error'=>$stmt3->error));
			die();
		}
		$orden++;
		$i++;	
	}


	if($stmt2->error==""){
		echo json_encode(array('respuesta' => true,'idservicio' => $idservicio));			
	}else{
		echo json_encode(array('respuesta' => false,'error'=>$stmt2));
	}
}

?>