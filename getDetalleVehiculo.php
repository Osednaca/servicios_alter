<?php

	include("includes/utils.php");

	$post_date  	= file_get_contents("php://input");

	$data 			= json_decode($post_date);

	$idvehiculo  	= $data->idvehiculo;

	$Direcciones 	= array();



$stmt = $con->prepare("SELECT idvehiculo,idtipovehiculo,placa,modelo,marca FROM vehiculo WHERE idvehiculo=?");

/* bind parameters for markers */

$stmt->bind_param("i", $idvehiculo);



/* execute query */

$stmt->execute();



if($stmt->error == ""){

	/* bind result variables */

	$stmt->bind_result($idvehiculo,$idtipovehiculo,$placa,$modelo,$marca);



	$stmt->fetch();

	

	$vehiculo = array('idvehiculo'=>$idvehiculo,'idtipovehiculo'=>$idtipovehiculo,'placa'=>$placa,'modelo'=>utf8_encode($modelo),'marca'=>utf8_encode($marca));



	$stmt->free_result();

	

	if(!empty($vehiculo)){

		echo json_encode(array('respuesta' => true, 'vehiculo'=>$vehiculo));

	}else{

		echo json_encode(array('respuesta' => false));

	}

}else{

	echo json_encode(array('respuesta' => false, 'mensaje' => $stmt->error));

}



?>