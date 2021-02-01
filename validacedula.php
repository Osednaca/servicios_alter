<?php
include("includes/utils.php");
$post_date  = file_get_contents("php://input");
$data 		= json_decode($post_date);
$cedula     = $data->cedula;

//Validar que la cedula no este registrada
$stmt = $con->prepare("SELECT idusuario FROM recomendaciones WHERE cedula = ? AND estatus = 1");
$stmt->bind_param("s", $cedula);
$stmt->execute();
$stmt->bind_result($idusuario);
$stmt->fetch();

$stmt->free_result();

if($idusuario!=""){
	echo json_encode(array('respuesta' => false, "registrado" => true,'mensaje' => 'Su cedula ya se encuentra registrada.'));
}else{
	$stmt = $con->prepare("SELECT idusuario FROM recomendaciones WHERE cedula = ?");
	$stmt->bind_param("s", $cedula);
	$stmt->execute();
	$stmt->bind_result($idusuario2);
	$stmt->fetch();
	$stmt->free_result();	
	if($idusuario2 ==""){
		echo json_encode(array('respuesta' => false));
	}else{
		echo json_encode(array('respuesta' => true, 'mensaje'=>''));
	}	
}
?>