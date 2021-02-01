<?php
include("includes.php");

$post_date  	= file_get_contents("php://input");
$data 			= json_decode($post_date);
$token 			= Auth::GetData(
    				$data->token
				  );
$idusuario  	= $token->id;
$cuentas 	= array();
$stmt = $con->prepare("SELECT idcuentanequi, nombre, telefono, estatus, fecharegistro FROM cuenta_nequi
						WHERE idusuario = ?");

$stmt->bind_param("i",$idusuario);

$stmt->execute();

if($stmt->error == ""){
	/* bind result variables */
	$stmt->bind_result($idcuentanequi,$nombre,$telefono,$estatus,$fecharegistro);
	$stmt->store_result();
	while ($stmt->fetch()) {
		//$stmt->free_result()
		$cuentas[] = array('idcuentanequi'=>$idcuentanequi,'nombre'=>$nombre,'telefono'=>$telefono,'estatus'=>$estatus);
	}
	$stmt1 = $con->prepare("SELECT AVG(calificacion),(SELECT nombre FROM usuario WHERE usuario.idusuario= ?),(SELECT apellido FROM usuario WHERE usuario.idusuario= ?) FROM calificacion WHERE idusuariocalificado = ?");
	$stmt1->bind_param("iii",$idusuario,$idusuario,$idusuario);
	$stmt1->execute();
	$stmt1->bind_result($calificacion,$nombre,$apellido);
	$stmt1->fetch();
	$usuariocalificacion = array("calificacion"=>$calificacion,"nombre"=>utf8_encode($nombre),"apellido" => utf8_encode($apellido));

	
	if(empty($stmt1->error)){
		echo json_encode(array('respuesta' => true, 'cuentas'=>$cuentas,'usuariocalificacion'=>$usuariocalificacion));
	}else{
		echo json_encode(array('respuesta' => false, 'error' => $stmt1->error));
	}
}else{
	echo json_encode(array('respuesta' => false, 'mensaje' => $stmt->error));
}

?>