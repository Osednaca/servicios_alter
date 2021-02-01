<?php

include("includes/utils.php");

$post_date  	= file_get_contents("php://input");
$data 			= json_decode($post_date);
$idservicio		= $data->idservicio;
$canceladopor	= $data->canceladopor;
$estatus		= 0;

$stmt = $con->prepare("SELECT estatus,idtipopago,idtiposervicio FROM servicio WHERE idservicio=?");
$stmt->bind_param("i", $idservicio);
$stmt->execute();
$stmt->bind_result($estatus1,$idtipopago,$idtiposervicio);
$stmt->fetch();

$stmt->free_result();

if($idtiposervicio != 4 AND $idtiposervicio != 6){
	if($estatus1 == 3 AND $idtipopago == 1){
		$estatus = 7;
	}
}

$stmt2 = $con->prepare("UPDATE servicio SET estatus=?,canceladopor=? WHERE idservicio=?");
/* bind parameters for markers */
$stmt2->bind_param("iii", $estatus,$canceladopor,$idservicio);
/* execute query */
$stmt2->execute();

if($stmt2->error == ""){
	echo json_encode(array('respuesta' => true));
}else{
	echo json_encode(array('respuesta' => false, 'error' => $stmt2->error));
}

?>