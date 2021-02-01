<?php
	include("includes.php");

	$post_date  	= file_get_contents("php://input");
	$data 			= json_decode($post_date);
	$token 			= Auth::GetData(
        				$data->token
    				  );
	$idusuario  	= $token->id;
	$direccion 		= $data->direccion;
	//$pais 		= $data->pais; Por los momentos Colombia
	$direccionsql  	= "%".$direccion."%";
	$direcciones 	= array();

$stmt = $con->prepare("SELECT * FROM direcciones_fav WHERE direccion LIKE ? AND idusuario=?");

$stmt->bind_param("si",$direccionsql,$idusuario);

$stmt->execute();

if($stmt->error == ""){
	/* bind result variables */
	$stmt->bind_result($iddireccionfav,$idusuario,$titulo,$direccionfav,$indicaciones,$ciudad,$lat,$lng,$fecharegistro);
	$stmt->store_result();
	while ($stmt->fetch()) {
		$direcciones[] = array('direccion'=>$direccionfav,'titulo'=>$titulo, 'indicaciones'=>$indicaciones,'lat'=>$lat,'lng'=>$lng,'ciudad'=>$ciudad,'esfav'=>true);
	}

	echo json_encode(array('respuesta' => true, 'direcciones'=>$direcciones));
}else{
	echo json_encode(array('respuesta' => false, 'mensaje' => $stmt->error));
}

?>