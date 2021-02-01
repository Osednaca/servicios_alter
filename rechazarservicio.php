<?php

include("includes.php");

$post_date  	= file_get_contents("php://input");
$data 			= json_decode($post_date);
$token 			= Auth::GetData(
    				$data->token
				  );
$idusuario  	= $token->id;
$idservicio		= $data->idservicio;

$stmt = $con->prepare("UPDATE notificacion SET estatus=0 WHERE idusuario=? AND idservicio=?");
/* bind parameters for markers */
$stmt->bind_param("ii", $idusuario,$idservicio);

/* execute query */
$stmt->execute();

if($stmt->error==""){
	echo json_encode(array('respuesta' => true));
}else{
	echo json_encode(array('respuesta' => false, 'mensaje' => 'Error en la base de datos.'));
}

?>