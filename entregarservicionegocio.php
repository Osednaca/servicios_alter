<?php
include("includes.php");

$post_date  	= file_get_contents("php://input");
$data 			= json_decode($post_date);
$idservicio 	= $data->idservicio;
$estatus 		= 4;


$stmt = $con->prepare("SELECT idproveedor FROM servicio WHERE idservicio=?");
/* bind parameters for markers */
$stmt->bind_param("i", $idservicio);
/* execute query */
$stmt->execute();

$stmt->bind_result($idproveedor);

$stmt->fetch();

$stmt->free_result();

$stmt = $con->prepare("UPDATE servicio SET estatus=? WHERE idservicio=?");
$stmt->bind_param("ii", $estatus, $idservicio);
$stmt->execute();

if($stmt->error == ""){
	$stmt->free_result();		
	echo json_encode(array('respuesta' => true,'idproveedor' => $idproveedor));
}else{
	echo json_encode(array('respuesta' => false, 'error' => $stmt->error));
}

?>