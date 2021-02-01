<?php

	include("includes/utils.php");

	$post_date  	= file_get_contents("php://input");

	$data 			= json_decode($post_date);

	$idextra  		= $data->idextra;

	$extra 			= array();



$stmt = $con->prepare("SELECT iditemextra,iditem, nombre, descripcion, imagen, costo FROM items_extras WHERE iditemextra = ?");

/* bind parameters for markers */

$stmt->bind_param("i", $idextra);



/* execute query */

$stmt->execute();



if($stmt->error == ""){

	/* bind result variables */

	$stmt->bind_result($iditemextra,$iditem, $nombre, $descripcion, $imagen, $costo);



	$stmt->fetch();

	

	$extra = array('iditemextra'=>$iditemextra,'iditem'=>$iditem, 'nombre'=>utf8_encode($nombre), 'descripcion'=>utf8_encode($descripcion), 'precio'=>$costo, 'logo'=>utf8_encode($imagen));



	$stmt->free_result();

	

	if(!empty($extra)){

		echo json_encode(array('respuesta' => true, 'extra'=>$extra));

	}else{

		echo json_encode(array('respuesta' => false));

	}

}else{

	echo json_encode(array('respuesta' => false, 'mensaje' => $stmt->error));

}



?>