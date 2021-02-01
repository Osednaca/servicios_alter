<?php

include("includes/utils.php");
//Para mayor seguridad validar origen (desde donde se hace el request)

$post_date  	 = file_get_contents("php://input");
$data 			 = json_decode($post_date);

$iddireccionfav  = $data->iddireccionfav;

$stmt = $con->prepare("DELETE FROM direcciones_fav WHERE iddireccionfav=?");
/* bind parameters for markers */
$stmt->bind_param("i", $iddireccionfav);
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