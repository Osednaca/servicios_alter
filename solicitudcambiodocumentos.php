<?php
session_start();
include("includes/utils.php");

$post_date  		=  file_get_contents("php://input");
$data 				=  json_decode($post_date);
$idusuario 			=  $_SESSION["idusuario"];
$fecharegistro 		=  date("Y-m-d H:m:i");
$estatus			=  2; //Enviada

// Validar que no tenga ya una solicitud
$stmt = $con->prepare("SELECT idsolicitud FROM cambiodocumentos WHERE idusuariosolicitud = ? AND estatus=2");

$stmt->bind_param("i",$idusuario);

$stmt->execute();

$stmt->bind_result($idsolicitud);

$stmt->fetch();

$stmt->free_result();
	
if($idsolicitud == ""){
	$stmt2 = $con->prepare("INSERT INTO cambiodocumentos(idusuariosolicitud, fechasolicitud, estatus) VALUES (?,?,?)");
	
	$stmt2->bind_param("isi",$idusuario,$fecharegistro,$estatus);
	
	$stmt2->execute();
	//validar que todo salga bien con $stmt->error
	if($stmt2->error == ""){
		echo json_encode(array('respuesta' => true));    
	}else{
		echo json_encode(array('respuesta' => false, 'mensaje' => $stmt2->error));    
	}
}else{
	echo json_encode(array('respuesta' => false, 'mensaje' => 'Ya tienes una solicitud de Cambio de documentos. Espere un correo con su aprobacion.'));
}
?>