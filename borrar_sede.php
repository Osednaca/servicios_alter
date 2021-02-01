<?php

include("includes/utils.php");
//Para mayor seguridad validar origen (desde donde se hace el request)

$post_date  	 = file_get_contents("php://input");
$data 			 = json_decode($post_date);

$idlugar 		 = $data->idlugar;

$stmt = $con->prepare("DELETE FROM lugar WHERE idlugar=?");
/* bind parameters for markers */
$stmt->bind_param("i", $idlugar);
/* execute query */
$stmt->execute();

$stmt = $con->prepare("DELETE FROM usuario WHERE idsede=?");
/* bind parameters for markers */
$stmt->bind_param("i", $idlugar);
/* execute query */
$stmt->execute();

//validar que todo salga bien con $stmt->error
if($stmt->error==""){
	echo json_encode(array('respuesta' => true));
}else{
	echo json_encode(array('respuesta' => false,'error'=>$stmt->error));
}

?>