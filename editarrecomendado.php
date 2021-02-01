<?php

include("includes/utils.php");

$post_date  	 		= file_get_contents("php://input");
$data 			 		= json_decode($post_date);

$idrecomendaciones 		= $data->recomendaciones->idrecomendaciones;
$nombre 				= $data->recomendaciones->nombre;
$cedula 				= $data->recomendaciones->cedula;
$telefono 				= $data->recomendaciones->telefono;
$correo 				= $data->recomendaciones->email;

$stmt1 = $con->prepare("UPDATE recomendaciones SET cedula=?,nombre=?,telefono=?,correo=? WHERE idrecomendaciones=?");
/* bind parameters for markers */
$stmt1->bind_param("ssssi", $cedula,$nombre,$telefono,$correo,$idrecomendaciones);

/* execute query */
$stmt1->execute();
if($stmt1->error == ""){
	echo json_encode(array('respuesta' => true));
}else{
	echo json_encode(array('respuesta' => false, 'error' => $stmt1->error));		
}

?>