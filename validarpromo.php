<?php
	include("includes.php");
	
	$post_date  	 = file_get_contents("php://input");
	$data 			 = json_decode($post_date);
	$token 			 = 	Auth::GetData(
     					$data->token
 						);
	$idusuario 		 = 	$token->id;
	
	//Validar que el correo no lo tenga registrado otro usuario
	$stmt = $con->prepare("SELECT promo FROM usuario WHERE idusuario = ?"); //AND estatus=1
	/* bind parameters for markers */
	$stmt->bind_param("s", $idusuario);
	
	/* execute query */
	$stmt->execute();
	
	/* bind result variables */
	$stmt->bind_result($promo);
	
	/* fetch value */
	$stmt->fetch();

	$stmt->free_result();
	if($promo == 0){
		echo json_encode(array('respuesta' => true));
	}elseif($promo == 1 OR $promo == 2){
		echo json_encode(array('respuesta' => false));
	}
?>