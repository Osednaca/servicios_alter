<?php
	include("includes/utils.php");
	session_start();
	$post_date  	= file_get_contents("php://input");
	$data 			= json_decode($post_date);
	$idusuario		= $data->idusuario;
	$Direcciones 	= array();

$stmt = $con->prepare("SELECT longitud,latitud,fechaubicacion FROM usuarioubicacion WHERE idusuario=? ORDER BY fechaubicacion DESC LIMIT 1");
/* bind parameters for markers */
$stmt->bind_param("i", $idusuario);

/* execute query */
$stmt->execute();

if($stmt->error == ""){
	$stmt->bind_result($longitud,$latitud,$fechaubicacion);
	$stmt->fetch();
	echo json_encode(array('respuesta' => true, 'latitud' => $latitud, 'longitud' => $longitud, 'fechaubicacion' => $fechaubicacion));
}else{
	echo json_encode(array('respuesta' => false, 'mensaje' => $stmt->error));
}
?>