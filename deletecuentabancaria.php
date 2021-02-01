<?php

include("includes/utils.php");
//Para mayor seguridad validar origen (desde donde se hace el request)

$post_date  	 = file_get_contents("php://input");
$data 			 = json_decode($post_date);

$idcuenta  		 = $data->idcuenta;

$stmt = $con->prepare("DELETE FROM cuentabancaria WHERE idcuenta=?");
/* bind parameters for markers */
$stmt->bind_param("i", $idcuenta);
/* execute query */
$stmt->execute();
//validar que todo salga bien con $stmt->error
if($stmt->error==""){
	echo json_encode(array('respuesta' => true));			
}else{
	echo json_encode(array('respuesta' => false,'error'=>$stmt->error));
}

?>