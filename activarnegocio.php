<?php

include("includes.php");

$post_date  	= file_get_contents("php://input");
$data 			= json_decode($post_date);
$idlugar		= $data->idlugar;

$stmt = $con->prepare("UPDATE lugar SET estatus=1 WHERE idlugar=?");
/* bind parameters for markers */
$stmt->bind_param("i", $idlugar);

/* execute query */
$stmt->execute();

if($stmt->error==""){
	echo json_encode(array('respuesta' => true));
}else{
	echo json_encode(array('respuesta' => false, 'mensaje' => 'Error en la base de datos.'));
}

?>