<?php

include("includes.php");
$post_date  	= file_get_contents("php://input");
$data 			= json_decode($post_date);
$token 			= Auth::GetData(
    				$data->token
				);
$idusuario  	= $token->id;


//Validar que tenga cuenta nequi
$stmt = $con->prepare("SELECT idcuentanequi FROM cuenta_nequi WHERE idusuario = ? AND estatus = 2");
$stmt->bind_param("i", $idusuario);
$stmt->execute();
$stmt->bind_result($idcuenta);
$stmt->fetch();
$stmt->free_result();

if($idcuenta != ""){
	echo json_encode(array('respuesta'=> true, 'cuenta_nequi' => true));
}else{
	echo json_encode(array('respuesta' => false, 'cuenta_nequi' => false));
}


?>