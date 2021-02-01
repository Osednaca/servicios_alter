<?php

include("includes/utils.php");

$post_date  = file_get_contents("php://input");

$data 		= json_decode($post_date);

$iditem     = $data->iditem;

$extras 	= array();

$stmt = $con->prepare("SELECT iditem, iditemextra, nombre FROM items_extras WHERE iditem = ? AND aprobado = 1");

$stmt->bind_param("i",$iditem);

$stmt->execute();

if($stmt->error == ""){

	/* bind result variables */

	$stmt->bind_result($iditem, $iditemextra, $nombre);

	$stmt->store_result();

	while ($stmt->fetch()) {
		$adiciones  = array();
		//$stmt->free_result()
		$stmt2 = $con->prepare("SELECT iditemadicion,iditemextra, titulo, precio, tipo FROM items_adicion WHERE iditemextra = ? AND aprobado = 1");
		$stmt2->bind_param("i",$iditemextra);
		$stmt2->execute();
		$stmt2->bind_result($iditemadicion,$iditemextra, $titulo, $precio, $tipo);
		$stmt2->store_result();		
		while ($stmt2->fetch()) {
			$adiciones[] = array('iditemextra'=>$iditemextra, 'iditemadicion' =>$iditemadicion,'titulo'=>utf8_encode($titulo), 'precio'=>number_format($precio,0,'',''), 'tipo' =>$tipo);
		}

		$extras[] = array('iditem' => $iditem,'iditemextra' => $iditemextra, 'nombre' => utf8_encode($nombre), 'adiciones' => $adiciones);

	}

	//var_dump($preguntas); die();

	if(count($extras) > 0){

		echo json_encode(array('respuesta' => true, 'extras' => $extras));

	}else{

		echo json_encode(array('respuesta' => false, 'msg' => 'No se encontraron items.', 'extras' => $extras));

	}

}else{

	echo json_encode(array('respuesta' => false, 'msg' => $stmt->error));

}



?>