<?php

	include("includes/utils.php");

	$post_date  	= file_get_contents("php://input");

	$data 			= json_decode($post_date);

	$iditem  		= $data->iditem;

	$producto 		= array();

	$items_extras 	= array();


$stmt = $con->prepare("SELECT iditem, idlugar, tipo, titulo, descripcion, imagen, precio, idmercadocategoria,idlicorescategoria,idcomidacategoria FROM lugar_items WHERE iditem = ?");

/* bind parameters for markers */

$stmt->bind_param("i", $iditem);



/* execute query */

$stmt->execute();



if($stmt->error == ""){

	/* bind result variables */

	$stmt->bind_result($iditem, $idlugar, $tipo, $titulo, $descripcion, $imagen, $precio, $idmercadocategoria, $idlicorescategoria, $idcomidacategoria);

	$stmt->store_result();

	$stmt->fetch();

	$stmt1 = $con->prepare("SELECT iditemextra, iditem, nombre FROM items_extras WHERE iditem = ?");
	$stmt1->bind_param("i", $iditem);
	$stmt1->execute();
	$stmt1->bind_result($iditemextra, $iditem2, $nombre);
	$stmt1->store_result();
	while ($stmt1->fetch()) {
		$adiciones = array();
		$stmt2 = $con->prepare("SELECT iditemadicion, titulo, precio, tipo FROM items_adicion WHERE iditemextra = ?");
		$stmt2->bind_param("i", $iditemextra);
		$stmt2->execute();
		$stmt2->bind_result($iditemadicion, $tituloadicion, $precio2,$tipo2);
		$stmt2->store_result();
		while ($stmt2->fetch()) {
			$adiciones[] = array('iditemadicion'=>$iditemadicion, 'titulo'=>utf8_encode($tituloadicion), 'precio'=>$precio2, 'tipo'=>$tipo2);	
		}	
		$items_extras[] = array('iditemextra' => $iditemextra, 'iditem' => $iditem2, 'nombre' => utf8_encode($nombre),'adiciones' => $adiciones);
	}

	$producto = array('iditem'=>$iditem, 'idlugar'=>$idlugar, 'tipo'=>$tipo, 'nombre'=>utf8_encode($titulo), 'logo'=>utf8_encode($imagen), 'descripcion'=>utf8_encode($descripcion), 'precio'=>$precio, 'idmercadocategoria' =>$idmercadocategoria, 'idlicorescategoria' =>$idlicorescategoria, 'idcomidacategoria' =>$idcomidacategoria, 'items_extras' =>$items_extras);



	$stmt->free_result();

	

	if(!empty($producto)){

		echo json_encode(array('respuesta' => true, 'producto'=>$producto));

	}else{

		echo json_encode(array('respuesta' => false));

	}

}else{

	echo json_encode(array('respuesta' => false, 'mensaje' => $stmt->error));

}



?>