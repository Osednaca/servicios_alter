<?php
include("includes.php");

$post_date  	= file_get_contents("php://input");
$data 			= json_decode($post_date);
$token 			= Auth::GetData(
    				$data->token
				  );
$idusuario  	= $token->id;
$idfavorito 	= $data->idfavorito;
$favorito 	= array();

$stmt = $con->prepare("SELECT * FROM direcciones_fav WHERE iddireccionfav=? ORDER BY titulo");

$stmt->bind_param("i",$idfavorito);

$stmt->execute();

if($stmt->error == ""){
	/* bind result variables */
	$stmt->bind_result($idfavorito,$idusuario,$titulo,$direccionfav,$indicaciones,$ciudad,$lat,$lng,$fecharegistro);
	$stmt->store_result();
	while ($stmt->fetch()) {
		$favorito[] = array('idfavorito'=>$idfavorito,'nombre'=>$titulo,'direccion'=>$direccionfav,'lat'=>$lat,'lng'=>$lng,'ciudad'=>$ciudad,'indicaciones'=>$indicaciones);
	}
	if(!empty($favorito)){
		echo json_encode(array('respuesta' => true, 'favorito'=>$favorito));
	}else{
		echo json_encode(array('respuesta' => false));
	}
}else{
	echo json_encode(array('respuesta' => false, 'mensaje' => $stmt->error));
}

?>