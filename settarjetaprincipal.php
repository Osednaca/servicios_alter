<?php
include("includes/utils.php");
session_start();
$post_date  = file_get_contents("php://input");
$data 		= json_decode($post_date);
$idusuario 	= $_SESSION["idusuario"];
$idtarjeta  = $data->idtarjeta;


$stmt = $con->prepare("UPDATE tarjetasusuario SET estatus = 1 WHERE idusuario = ?");
$stmt->bind_param("i",$idusuario);
$stmt->execute();


$stmt1 = $con->prepare("UPDATE tarjetasusuario SET estatus = 2 WHERE idtarjetausuario = ?");
$stmt1->bind_param("i",$idtarjeta);
$stmt1->execute();

if(empty($stmt1->error)){
	echo json_encode(array('respuesta' => true));
}else{
	echo json_encode(array('respuesta' => false, 'error' => $stmt1->error));
}

?>