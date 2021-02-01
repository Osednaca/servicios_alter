<?php

include("includes/utils.php");
//Para mayor seguridad validar origen (desde donde se hace el request)

$post_date  	 = file_get_contents("php://input");
$data 			 = json_decode($post_date);

$iditem  	 	 = $data->iditem;

$stmt = $con->prepare("DELETE FROM lugar_items WHERE iditem=?");
/* bind parameters for markers */
$stmt->bind_param("i", $iditem);
/* execute query */
$stmt->execute();

$stmt = $con->prepare("DELETE FROM items_extras WHERE iditem= ?");
/* bind parameters for markers */
$stmt->bind_param("i", $iditem);
/* execute query */
$stmt->execute();
//validar que todo salga bien con $stmt->error
if($stmt->error==""){
	echo json_encode(array('respuesta' => true));
}else{
	echo json_encode(array('respuesta' => false,'error'=>$stmt->error));
}

?>