<?php
	include("includes/utils.php");
	$post_date  	= file_get_contents("php://input");
	$data 			= json_decode($post_date);
	$idservicio  	= $data->idservicio;

	$direcciones 	= array();

$stmt = $con->prepare("SELECT estatus FROM servicio WHERE idservicio=?");

$stmt->bind_param("i",$idservicio);

$stmt->execute();

if($stmt->error == ""){
	/* bind result variables */
	$stmt->bind_result($estatus);
	$stmt->fetch();

	echo json_encode(array('respuesta' => true, 'estatus'=>$estatus));
}else{
	echo json_encode(array('respuesta' => false, 'mensaje' => $stmt->error));
}

?>