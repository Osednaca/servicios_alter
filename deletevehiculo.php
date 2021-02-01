<?php

include("includes/utils.php");
//Para mayor seguridad validar origen (desde donde se hace el request)

$post_date  	 = file_get_contents("php://input");
$data 			 = json_decode($post_date);

$idvehiculo  	 = $data->idvehiculo;

$stmt = $con->prepare("DELETE FROM vehiculo WHERE idvehiculo=?");
/* bind parameters for markers */
$stmt->bind_param("i", $idvehiculo);
/* execute query */
$stmt->execute();
//validar que todo salga bien con $stmt->error
if($stmt->error==""){
	if($stmt->affected_rows > 0){
		echo json_encode(array('respuesta' => true));
	}else{
		echo json_encode(array('respuesta' => false));
	}
}else{
	echo json_encode(array('respuesta' => false,'error'=>$stmt->error));
}

?>