<?php

include("includes.php");

$post_date  	 		= file_get_contents("php://input");
$data 			 		= json_decode($post_date);

$token 					= 	Auth::GetData(
     							$data->token
 						  	);
$idusuario 				= 	$token->id;

$registrocompleto = registroCompleto($idusuario);

if($registrocompleto){
	echo json_encode(array('respuesta' => true));
}else{
	echo json_encode(array('respuesta' => false));
}
?>