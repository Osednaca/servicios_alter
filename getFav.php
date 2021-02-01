<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
	include("includes.php");

	$post_date  	= file_get_contents("php://input");
	$data 			= json_decode($post_date);
	$token 			= Auth::GetData(
        				$data->token
    				  );
	$idusuario  	= $token->id;
	$filtros 		= "";
	$a_params 		= array();
	$favoritos 		= array();
	$a_params[]    = & $idusuario;
	$tipostring 	= "s";

array_unshift($a_params,$tipostring);

$stmt = $con->prepare("SELECT iddireccionfav,titulo,direccion
						FROM direcciones_fav
						WHERE direcciones_fav.idusuario=? AND titulo = 'Fav' ORDER BY fecharegistro DESC LIMIT 1");

call_user_func_array(array($stmt, 'bind_param'), $a_params);

$stmt->execute();

if($stmt->error == ""){
	/* bind result variables */
	$stmt->bind_result($iddireccionfav,$titulo,$direccion);

	$stmt->fetch();

if($iddireccionfav == ""){


$stmt = $con->prepare("SELECT iddireccionfav,titulo,direccion
						FROM direcciones_fav
						WHERE direcciones_fav.idusuario=? ORDER BY fecharegistro LIMIT 1");

call_user_func_array(array($stmt, 'bind_param'), $a_params);

$stmt->execute();

if($stmt->error == ""){
	/* bind result variables */
	$stmt->bind_result($iddireccionfav,$titulo,$direccion);

	while ($stmt->fetch()) {
		$favoritos[] = array('iddireccionfav'=>$iddireccionfav,'titulo'=>utf8_encode($titulo),'direccion'=>$direccion);
	}
}else{
	echo json_encode(array('respuesta' => false, 'mensaje' => $stmt->error));
}
}else{
	$favoritos[] = array('iddireccionfav'=>$iddireccionfav,'titulo'=>utf8_encode($titulo),'direccion'=>$direccion);
}	

	if(!empty($favoritos)){
		echo json_encode(array('respuesta' => true, 'favoritos'=>$favoritos));
	}else{
		echo json_encode(array('respuesta' => false, 'mensaje' => 'No se encontraron favoritos'));
	}
}else{
	echo json_encode(array('respuesta' => false, 'mensaje' => $stmt->error));
}

?>