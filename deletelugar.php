<?php

include("includes/utils.php");
//Para mayor seguridad validar origen (desde donde se hace el request)

$post_date  	 = file_get_contents("php://input");
$data 			 = json_decode($post_date);

$nombre  	 	 = utf8_decode($data->nombre);
$idlugar 		 = $data->idlugar;

$stmt = $con->prepare("DELETE FROM usuario WHERE idsede IN(SELECT idlugar FROM lugar WHERE nombrelugar = ?)");
/* bind parameters for markers */
$stmt->bind_param("s", $nombre);
/* execute query */
$stmt->execute();

$stmt = $con->prepare("DELETE FROM lugar WHERE nombrelugar=?");
/* bind parameters for markers */
$stmt->bind_param("s", $nombre);
/* execute query */
$stmt->execute();

$stmt = $con->prepare("DELETE FROM items_extras WHERE iditem=(SELECT iditem FROM lugar_items WHERE idlugar = ?)");
/* bind parameters for markers */
$stmt->bind_param("i", $idlugar);
/* execute query */
$stmt->execute();

$stmt = $con->prepare("DELETE FROM lugar_items WHERE idlugar=?");
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