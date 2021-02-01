<?php
header('Access-Control-Allow-Origin: *');  
include("includes.php");

$post_date  	  = file_get_contents("php://input");
$data 			  = json_decode($post_date);
$token 			  = Auth::GetData(
    			  	$data->token
				    );
$idusuario  	  = $token->id;
$idticketsoporte  = $data->idticket;
$texto        	  = $data->texto;
$fecharespuesta   = date("Y-m-d H:i:s");

$stmt = $con->prepare("UPDATE ticketsoporte SET estatus = 1  WHERE idticketsoporte=?");
$stmt->bind_param("i", $idticketsoporte);
$stmt->execute();
if($stmt->error != ""){

}
$stmt->free_result();

$stmt = $con->prepare("INSERT INTO respuestasoporte(idticketsoporte, idoperador, respuesta, estatus, fecharespuesta) VALUES (?,null,?,1,?)");
$stmt->bind_param("iss", $idticketsoporte, $texto, $fecharespuesta);
$stmt->execute();
if($stmt->error != ""){
	echo json_encode(array('respuesta' => false,'error' => $stmt->error));
	die();
}else{
	echo json_encode(array('respuesta' => true));
}
$stmt->free_result();

?>