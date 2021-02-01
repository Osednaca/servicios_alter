<?php

include("includes.php");

$post_date  	= file_get_contents("php://input");
$data 			= json_decode($post_date);
$token 			= Auth::GetData(
    				$data->token
				  );
$idusuario  	= $token->id;
$direccion		= $data->direccion;

$stmt = $con->prepare("SELECT iddireccionfav FROM direcciones_fav WHERE direccion = ? AND idusuario = ?");
/* bind parameters for markers */
$stmt->bind_param("si", $direccion,$idusuario);

/* execute query */
$stmt->execute();

/* bind result variables */
$stmt->bind_result($iddireccionfav);

$stmt->fetch();

$stmt->free_result();

if($iddireccionfav != ""){
	echo json_encode(array('respuesta' => true,'iddireccionfav'=>$iddireccionfav));
}else{
	echo json_encode(array('respuesta' => false));
}

?>