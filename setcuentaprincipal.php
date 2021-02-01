<?php
include("includes.php");

$post_date  	  = file_get_contents("php://input");
$data 			  = json_decode($post_date);
$token 			  = Auth::GetData(
    				$data->token
				  );
$idusuario  	  = $token->id;
$idcuenta  = $data->idcuenta;


$stmt = $con->prepare("UPDATE cuenta_nequi SET estatus = 1 WHERE idusuario = ?");
$stmt->bind_param("i",$idusuario);
$stmt->execute();


$stmt1 = $con->prepare("UPDATE cuenta_nequi SET estatus = 2 WHERE idcuentanequi = ?");
$stmt1->bind_param("i",$idcuenta);
$stmt1->execute();

if(empty($stmt1->error)){
	echo json_encode(array('respuesta' => true));
}else{
	echo json_encode(array('respuesta' => false, 'error' => $stmt1->error));
}

?>