<?php
include("includes.php");

$post_date  	= file_get_contents("php://input");
$data 			= json_decode($post_date);
$token 			= Auth::GetData(
    				$data->token
				  );
$idusuario  	= $token->id;
$direcciones 	= array();

$stmt = $con->prepare("SELECT * FROM direcciones_fav WHERE idusuario=? ORDER BY titulo");

$stmt->bind_param("i",$idusuario);

$stmt->execute();

if($stmt->error == ""){
	/* bind result variables */
	$stmt->bind_result($iddireccionfav,$idusuario,$titulo,$direccionfav,$indicaciones,$ciudad,$lat,$lng,$fecharegistro);
	$stmt->store_result();
	while ($stmt->fetch()) {
		$direcciones[] = array('titulo'=>$titulo,'formatted_address'=>$direccionfav,'lat'=>$lat,'lng'=>$lng,'ciudad'=>$ciudad,'indicaciones'=>$indicaciones,'iddireccionfav'=>$iddireccionfav);
	}
	if(!empty($direcciones)){
		echo json_encode(array('respuesta' => true, 'direcciones'=>$direcciones));
	}else{
		echo json_encode(array('respuesta' => false));
	}
}else{
	echo json_encode(array('respuesta' => false, 'mensaje' => $stmt->error));
}

?>