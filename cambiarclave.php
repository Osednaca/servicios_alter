<?php

include("includes.php");

$post_date  			= 	file_get_contents("php://input");
$data 					= 	json_decode($post_date);
$token 					= 	Auth::GetData(
    							$data->token
						  	);
$idusuario  			= 	$token->id;
$contrasena				= 	$data->user->contrasena;
$nuevacontrasena 		= 	$data->user->nuevacontrasena;
$fechamodificacion      = 	date("Y-m-d H:i:s");

//Validar la contrasena
$stmt = $con->prepare("SELECT password,salt FROM usuario WHERE idusuario = ?");
/* bind parameters for markers */
$stmt->bind_param("i", $idusuario);
$stmt->execute();
$stmt->bind_result($password,$salt);
$stmt->fetch();

$hash = checkhashSSHA($salt, $contrasena);

$stmt->free_result();
//Si es correcta modificar el registro en BD
if($hash==$password){
	$newcontrasena = hashSSHA($nuevacontrasena);
	$stmt = $con->prepare("UPDATE usuario SET password = ?, salt = ?, fechamodificacion = ? WHERE idusuario = ?");
	/* bind parameters for markers */
	$stmt->bind_param("sssi", $newcontrasena["encrypted"], $newcontrasena["salt"], $fechamodificacion, $idusuario);

	/* execute query */
	$stmt->execute();

	if($stmt->error == ""){
		echo json_encode(array('respuesta' => true));
	}else{
		echo json_encode(array('respuesta' => false, 'error' => $stmt->error));
	}
}else{
	// Sino muestra un error
	echo json_encode(array('respuesta' => false,'mensaje'=>'Contraseña incorrecta.'));
}

?>