<?php

include("includes/utils.php");

$post_date  	= file_get_contents("php://input");
$data 			= json_decode($post_date);
$idnotificacion = $data->idnotificacion;

//Verificar si el servicio se encuentra en estatus 3 si se encuentra cobrale el minimo al cliente y poner un nuevo estatus que diga Cancelado Pago Minimo
//$stmt = $con->prepare("SELECT estatus FROM servicio WHERE idservicio=?");
//$stmt->bind_param("i", $idservicio);
//$stmt->execute();
//$stmt->bind_result($status);
//$stmt->fetch();

//$stmt->free_result();
$estatus = 0;
$stmt2 = $con->prepare("UPDATE notificacion SET estatus=? WHERE idnotificacion=?");
/* bind parameters for markers */
$stmt2->bind_param("ii", $estatus,$idnotificacion);
/* execute query */
$stmt2->execute();

if($stmt2->error == ""){
	echo json_encode(array('respuesta' => true));
}else{
	echo json_encode(array('respuesta' => false, 'error' => $stmt2->error));
}

?>