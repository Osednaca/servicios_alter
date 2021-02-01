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

	$vehiculos 		= array();

	$a_params[]    = & $idusuario;

	$tipostring 	= "s";



	//Filtros

	//if(!empty($data->mes)){

	//	$filtros 	   	= 	"AND MONTH(fecharegistro) = ?";

	//	$a_params[] 	= & $data->mes;

	//	$tipostring		.= 	"i";

	//}

	//if(!empty($data->dia)){

	//	$filtros 	   .= " AND DAY(fecharegistro) = ?";

	//	$a_params[] 	= & $data->dia;

	//	$tipostring	   .= 	"s";

	//}



array_unshift($a_params,$tipostring);



$stmt = $con->prepare("SELECT idvehiculo,placa,tipovehiculo,vehiculo.estatus

						FROM vehiculo

						INNER JOIN tipovehiculo USING(idtipovehiculo) 

						WHERE vehiculo.idusuario=? $filtros");



call_user_func_array(array($stmt, 'bind_param'), $a_params);



$stmt->execute();



if($stmt->error == ""){

	/* bind result variables */

	$stmt->bind_result($idvehiculo,$placa,$tipovehiculo,$estatus);

	while ($stmt->fetch()) {

		$vehiculos[] = array('idvehiculo'=>$idvehiculo,'placa'=>$placa,'tipovehiculo'=>utf8_encode($tipovehiculo),'estatus' => $estatus);

	}

	

	if(!empty($vehiculos)){

		echo json_encode(array('respuesta' => true, 'vehiculos'=>$vehiculos));

	}else{

		echo json_encode(array('respuesta' => false, 'mensaje' => 'No se encontraron vehiculos'));

	}

}else{

	echo json_encode(array('respuesta' => false, 'mensaje' => $stmt->error));

}



?>