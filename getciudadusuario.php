<?php
	include("includes.php");

	$post_date  	= file_get_contents("php://input");
	$data 			= json_decode($post_date);
	$token 			= Auth::GetData(
        				$data->token
    				  );
	$idusuario  	= $token->id;

	$stmt = $con->prepare("SELECT ciudad.idciudad,ciudad FROM usuario INNER JOIN ciudad USING(idciudad) WHERE usuario.idusuario = ?"); //En un futuro filtrar por idpais
	$stmt->bind_param("i", $idusuario);
	$stmt->execute();
	$stmt->bind_result($idciudad,$ciudadbd);
	while($stmt->fetch()){
		$ciudadbd 		  = explode("-", $ciudadbd);
		$solociudad 	  = trim(utf8_encode($ciudadbd[0]));
		$solodepartamento = trim(utf8_encode($ciudadbd[1]));
		echo json_encode(array('respuesta' => true,'ciudad' => $solociudad));
		die();
	}
	echo json_encode(array('respuesta' => false,'mensaje' => 'No se encontro la ciudad'));
?>