<?php
include("includes/utils.php");
session_start();
$post_date  		= file_get_contents("php://input");
$data 				= json_decode($post_date);
$idtipovehiculo 	= $data->idtipovehiculo;
$idtiposervicio 	= $data->idtiposervicio;
$idservicio 		= $data->idservicio;
$valor    			= $data->costototal;

$stmt1 = $con->prepare("UPDATE servicio SET idtipovehiculo = ?, idtiposervicio = ?,valor=?,total = ? WHERE idservicio = ?");
$stmt1->bind_param("iissi",$idtipovehiculo,$idtiposervicio,$valor,$valor,$idservicio);
$stmt1->execute();

if(empty($stmt1->error)){
	echo json_encode(array('respuesta' => true));
}else{
	echo json_encode(array('respuesta' => false, 'error' => $stmt1->error));
}

?>