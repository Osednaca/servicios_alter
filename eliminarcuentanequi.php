<?php
include("includes.php");

$post_date  	= file_get_contents("php://input");
$data 			= json_decode($post_date);
$token 			= Auth::GetData(
     				$data->token
 				  );
$idusuario  	= $token->id;
$idcuentanequi  = $data->idcuenta;

$stmt1 = $con->prepare("DELETE FROM cuenta_nequi WHERE idcuentanequi = ?");
$stmt1->bind_param("i",$idcuentanequi);
$stmt1->execute();

if($stmt1->error == ""){
	echo json_encode(array('respuesta' => true));
}else{
	echo json_encode(array('respuesta' => false, 'mensaje' => $stmt1->error));
}

?>