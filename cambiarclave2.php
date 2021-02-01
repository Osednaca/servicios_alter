<?php

include("includes/utils.php");

$post_date  		=	file_get_contents("php://input");
$data 				=	json_decode($post_date);
$idusuario 			=	$data->user->idusuario;
$nuevacontrasena 	=	$data->user->contrasena;
$newcontrasena 		= 	hashSSHA($nuevacontrasena);
$fechamodificacion  =   date("Y-m-d H:i:s");

$stmt = $con->prepare("UPDATE usuario SET password = ?, salt = ?, fechamodificacion = ?, tokenpassword='' WHERE idusuario = ?");
/* bind parameters for markers */
$stmt->bind_param("sssi", $newcontrasena["encrypted"], $newcontrasena["salt"], $fechamodificacion, $idusuario);

/* execute query */
$stmt->execute();

if($stmt->error == ""){
	echo json_encode(array('respuesta' => true));
}else{
	echo json_encode(array('respuesta' => false, 'error' => $stmt->error));
}

?>