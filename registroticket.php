<?php
include("includes.php");

$post_date  	= file_get_contents("php://input");
$data 			= json_decode($post_date);
$token 			= Auth::GetData(
    				$data->token
				  );
$idusuario  	= $token->id;
$idtemasoporte 	= $data->idtemasoporte;
$descripcion	= $data->descripcion;
$descripcion    = trim(preg_replace('/\s+/', ' ', utf8_decode($descripcion)));
$estatus		= 0;
$fecharegistro  = date("Y-m-d H:i:s");

$stmt = $con->prepare("INSERT INTO ticketsoporte(idusuario, idtemasoporte, descripcion, estatus, fechaticket) VALUES (?,?,?,?,?)");

$stmt->bind_param("iisis", $idusuario,$idtemasoporte,$descripcion,$estatus,$fecharegistro);

$stmt->execute();

$idticket = $stmt->insert_id;

//validar que todo salga bien con $stmt->error
if($stmt->error==""){
	echo json_encode(array('respuesta' => true,'mensaje' => "Mensaje Enviado, daremos respuesta a tu correo. Tu ticket es: $idticket. Guarda el numero para hacer seguimiento a tu caso."));			
}else{
	echo json_encode(array('respuesta' => false,'error'=>$stmt->error));
}

?>