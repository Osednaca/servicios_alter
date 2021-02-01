<?php

include("includes/utils.php");

$post_date  = file_get_contents("php://input");

$data 		= json_decode($post_date);

$idtema    = $data->idtema;

$preguntas 	= array();





$stmt = $con->prepare("SELECT idpreguntafrecuente,pregunta,respuesta,palabrasclaves

						FROM preguntasfrecuentes

						WHERE idtemasoporte = ?");



$stmt->bind_param("i",$idtema);



$stmt->execute();



if($stmt->error == ""){

	/* bind result variables */

	$stmt->bind_result($idpreguntafrecuente,$pregunta,$respuesta,$palabrasclaves);

	$stmt->store_result();

	while ($stmt->fetch()) {

		//$stmt->free_result()

		$preguntas[] = array('idpreguntafrecuente'=>$idpreguntafrecuente,'pregunta'=>utf8_encode($pregunta),'respuesta'=>utf8_encode($respuesta),'palabrasclaves'=>utf8_encode($palabrasclaves));

	}

	//var_dump($preguntas); die();

	if(count($preguntas) > 0){

		echo json_encode(array('respuesta' => true, 'preguntas' => $preguntas));

	}else{

		echo json_encode(array('respuesta' => false, 'mensaje' => 'No se encontro pregunta relacionada.'));

	}

}else{

	echo json_encode(array('respuesta' => false, 'mensaje' => $stmt->error));

}



?>