<?php

error_reporting(E_ERROR | E_WARNING | E_PARSE);

	include("includes.php");



	$post_date  	= file_get_contents("php://input");

	$data 			= json_decode($post_date);

	$token 			= Auth::GetData(

        				$data->token

    				  );

	$idusuario  	= $token->id;

	$filtros 		= "";

	$a_params 		= array();

	$extras 		= array();

	$a_params[]    = & $idusuario;

	$tipostring 	= "s";



array_unshift($a_params,$tipostring);



$stmt = $con->prepare("SELECT iditemextra,titulo,nombre
						FROM items_extras
						INNER JOIN lugar_items USING(iditem)
						WHERE items_extras.idusuario=?");



call_user_func_array(array($stmt, 'bind_param'), $a_params);



$stmt->execute();



if($stmt->error == ""){

	/* bind result variables */

	$stmt->bind_result($iditemextra,$titulo,$nombre);

	while ($stmt->fetch()) {

		$extras[] = array('iditemextra'=>$iditemextra,'titulo'=>utf8_encode($titulo),'nombre'=>utf8_encode($nombre));

	}

	if(!empty($extras)){

		echo json_encode(array('respuesta' => true, 'extras'=>$extras));

	}else{

		echo json_encode(array('respuesta' => false, 'mensaje' => 'No se encontraron extras'));

	}

}else{

	echo json_encode(array('respuesta' => false, 'mensaje' => $stmt->error));

}



?>