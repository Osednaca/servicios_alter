<?php

include("includes/utils.php");
session_start();
$post_date  		= file_get_contents("php://input");
$data 				= json_decode($post_date);

$idservicio			= $data->idservicio;
$idlugar			= $data->idlugar;
$fecharegistro 		= date("Y-m-d H:i:s");

$stmt1 = $con->prepare("INSERT INTO notificacionlugar(idservicio, idlugar, fechanotificacion) VALUES (?,?,?)");
/* bind parameters for markers */
$stmt1->bind_param("iis", $idservicio,$idlugar,$fecharegistro);

/* execute query */
$stmt1->execute();

$idnotificacion = $stmt1->insert_id;

if($stmt1->error != ""){
	echo json_encode(array('respuesta' => false,'mensaje' => $stmt1->error));
}else{
	echo json_encode(array('respuesta' => true, 'idnotificacion' => $idnotificacion));
}

?>