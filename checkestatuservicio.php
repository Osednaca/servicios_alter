<?php

include("includes/utils.php");

$post_date  	= file_get_contents("php://input");
$data 			= json_decode($post_date);
$idservicio		= $data->idservicio;

$stmt = $con->prepare("SELECT estatus FROM servicio WHERE idservicio = ?");
/* bind parameters for markers */
$stmt->bind_param("i", $idservicio);

/* execute query */
$stmt->execute();

/* bind result variables */
$stmt->bind_result($estatus);

$stmt->fetch();

$stmt->free_result();

if($estatus != ""){
	echo json_encode(array('respuesta' => true,'estatus'=>$estatus));
}else{
	echo json_encode(array('respuesta' => false, 'error'=>''));
}

?>