<?php
	include("includes/utils.php");
	$post_date  = file_get_contents("php://input");
	$data 		= json_decode($post_date);
	$idmensaje 	= $data->idmensaje;
	$estatus 	= $data->estatus;
	$stmt = $con->prepare("UPDATE mensajes SET estatus=? WHERE idmensaje=?");
	$stmt->bind_param('ii',$estatus,$idmensaje);
	$stmt->execute();
	
	if($stmt->error == ""){
		$stmt1 = $con->prepare("SELECT idremitente FROM mensajes WHERE idmensaje=?");
		$stmt1->bind_param('i',$idmensaje);
		$stmt1->execute();
		$stmt1->bind_result($idremitente);
		$stmt1->fetch();
		echo json_encode(array('respuesta' => true,'idremitente' => $idremitente,'idmensaje' => $idmensaje));
	}else{
		echo json_encode(array('respuesta' => false, 'error' => $stmt->error));
	}
?>