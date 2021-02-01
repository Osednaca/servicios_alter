<?php

include("includes/utils.php");

$post_date  	= file_get_contents("php://input");
$data 			= json_decode($post_date);
$idservicio     = $data->idservicio;

$estatus = 0;
$stmt2 = $con->prepare("UPDATE notificacionlugar SET estatus=? WHERE idservicio=?");
/* bind parameters for markers */
$stmt2->bind_param("ii", $estatus,$idservicio);
/* execute query */
$stmt2->execute();

if($stmt2->error == ""){
	echo json_encode(array('respuesta' => true));
}else{
	echo json_encode(array('respuesta' => false, 'error' => $stmt2->error));
}

?>