<?php

include("includes/utils.php");
//Para mayor seguridad validar origen (desde donde se hace el request)

$post_date  	 = file_get_contents("php://input");
$data 			 = json_decode($post_date);

$idretirodinero  	 = $data->idretirodinero;

$stmt = $con->prepare("UPDATE retirodedinero SET estatus=0 WHERE idretirodinero=?");
$stmt->bind_param("i", $idretirodinero);
$stmt->execute();
//Reponer dinero
$stmt = $con->prepare("UPDATE usuario SET saldoalter=saldoalter+(SELECT valor FROM retirodedinero WHERE idretirodinero=?) WHERE idusuario=(SELECT idusuario FROM retirodedinero WHERE idretirodinero=?)");
$stmt->bind_param("ii", $idretirodinero, $idretirodinero);
$stmt->execute();
//validar que todo salga bien con $stmt->error
if($stmt->error==""){
	if($stmt->affected_rows > 0){
		$stmt1 = $con->prepare("SELECT saldoalter FROM usuario WHERE idusuario=(SELECT idusuario FROM retirodedinero WHERE idretirodinero=?)");
		$stmt1->bind_param("i", $idretirodinero);
		$stmt1->execute();
		$stmt1->bind_result($saldo);
		$stmt1->fetch();		
		echo json_encode(array('respuesta' => true, 'saldo' => $saldo));
	}else{
		echo json_encode(array('respuesta' => false));
	}
}else{
	echo json_encode(array('respuesta' => false,'error'=>$stmt->error));
}

?>