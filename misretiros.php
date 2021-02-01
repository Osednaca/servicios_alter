<?php
	include("includes.php");

	$post_date  	= file_get_contents("php://input");
	$data 			= json_decode($post_date);
	$token 			= Auth::GetData(
        				$data->token
    				  );
	$idusuario  	= $token->id;
	$retiros 	= array();

	$stmt = $con->prepare("SELECT idretirodinero, valor, estatus, DATE(fechasolicitud) FROM retirodedinero WHERE idusuario=? AND estatus = 1");
	
	$stmt->bind_param("i",$idusuario);
	
	$stmt->execute();
	
	if($stmt->error == ""){
		/* bind result variables */
		$stmt->bind_result($idretirodinero, $valor, $estatus, $fechasolicitud);
		$stmt->store_result();
		while ($stmt->fetch()) {
			$retiros[] = array('idretirodinero'=>$idretirodinero, 'valor' => $valor, 'estatus' => $estatus, 'fechasolicitud' => $fechasolicitud);
		}
		
		if(!empty($retiros)){
			echo json_encode(array('respuesta' => true, 'retiros'=>$retiros));
		}else{
			echo json_encode(array('respuesta' => false, 'mensaje' => 'No se encontraron retiros'));
		}
	}else{
		echo json_encode(array('respuesta' => false, 'mensaje' => $stmt->error));
	}

?>