<?php

include("includes.php");

$post_date  	= file_get_contents("php://input");
$data 			= json_decode($post_date);
$token 			= Auth::GetData(
    $data->token
  );
$idusuario  	= $token->id;

$stmt = $con->prepare("SELECT idlugar FROM lugar WHERE idusuario = ? AND idlugarcategoria = 19");
/* bind parameters for markers */
$stmt->bind_param("i", $idusuario);

/* execute query */
$stmt->execute();

/* bind result variables */
$stmt->bind_result($idlugar);

$stmt->fetch();

$stmt->free_result();

if($idlugar != ""){
	echo json_encode(array('respuesta' => true,'idlugar'=>$idlugar));
}else{
	echo json_encode(array('respuesta' => false, 'error'=>''));
}

?>