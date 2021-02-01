<?php
include("includes.php");

$post_date  = file_get_contents("php://input");
$data 		= json_decode($post_date);
$idservicio = $data->idservicio;
$estatus    = $data->estatus;

$stmt1 = $con->prepare("UPDATE servicio SET estatus = ? WHERE idservicio = ?");
$stmt1->bind_param("ii",$estatus,$idservicio);
$stmt1->execute();

if(empty($stmt1->error)){
	echo json_encode(array('respuesta' => true));
}else{
	echo json_encode(array('respuesta' => false, 'error' => $stmt1->error));
}

?>