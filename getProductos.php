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

	$productos 		= array();

	$a_params[]    = & $idusuario;

	$tipostring 	= "s";



array_unshift($a_params,$tipostring);



$stmt = $con->prepare("SELECT iditem,titulo,nombrelugar
						FROM lugar_items
						INNER JOIN lugar USING(idlugar)
						WHERE lugar_items.idusuario=?");



call_user_func_array(array($stmt, 'bind_param'), $a_params);



$stmt->execute();



if($stmt->error == ""){

	/* bind result variables */

	$stmt->bind_result($iditem,$titulo,$nombrelugar);

	while ($stmt->fetch()) {

		$productos[] = array('iditem'=>$iditem,'titulo'=>utf8_encode($titulo),'nombrelugar'=>utf8_encode($nombrelugar));

	}

	if(!empty($productos)){

		echo json_encode(array('respuesta' => true, 'productos'=>$productos));

	}else{

		echo json_encode(array('respuesta' => false, 'mensaje' => 'No se encontraron productos'));

	}

}else{

	echo json_encode(array('respuesta' => false, 'mensaje' => $stmt->error));

}



?>