<?php
	include("includes.php");

	$post_date  = file_get_contents("php://input");
	$data 		= json_decode($post_date);
	$token 		= Auth::GetData(
        			$data->token
    			  );
	$idusuario  = $token->id;
	$valor 		= $data->valor;

	$stmt = $con->prepare("SELECT saldoalter FROM usuario WHERE idusuario=?");

	$stmt->bind_param("i", $idusuario);

	$stmt->execute();
	
	if($stmt->error == ""){
		$stmt->bind_result($saldoalter);
		$stmt->fetch();
		if($saldoalter >= $valor){
			echo json_encode(array('respuesta' => true, 'saldoalter' => $saldoalter));
		}else{
			echo json_encode(array('respuesta' => false));
		}
	}else{
		echo json_encode(array('respuesta' => false, 'mensaje' => $stmt->error));
	}
	
?>