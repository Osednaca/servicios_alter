<?php

include("includes.php");

$post_date  		= file_get_contents("php://input");
$data 				= json_decode($post_date);
$token 				= Auth::GetData(
     					$data->token
 					  );
$idremitente  		= $token->id;
$iddestinatario 	= $data->iddestinatario;
$idservicio  		= $data->idservicio;
$fechaenvio 		= $data->fechaenvio;
$mensajes 			= htmlentities($data->mensaje);
$estatus			= 1;

$stmt = $con->prepare("INSERT INTO mensajes(idservicio, idremitente, iddestinatario, mensaje, estatus, fechaenvio) VALUES (?,?,?,?,?,?)");
/* bind parameters for markers */
$stmt->bind_param("iiisis", $idservicio,$idremitente,$iddestinatario,$mensajes,$estatus,$fechaenvio);

/* execute query */
$stmt->execute();

$idmensaje = $stmt->insert_id;

//validar que todo salga bien con $stmt->error
if($stmt->error ==""){
	echo json_encode(array('respuesta' => true, 'idmensaje' => $idmensaje, 'fechaenvio' => $fechaenvio));
}else{
	echo json_encode(array('respuesta' => false,'msg' => $stmt->error));
}


?>