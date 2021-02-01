<?php

include("includes.php");
$post_date  	= file_get_contents("php://input");
$data 			= json_decode($post_date);
$token 			= Auth::GetData(
    				$data->token
				);
$idusuario  	= $token->id;
$direccion 		= $data->favorito->direccion;
$titulo 		= $data->favorito->nombre;
if(!empty($data->favorito->indicaciones)){
	$indicaciones 	= $data->favorito->indicaciones;
}else{
	$indicaciones 	= "";
}
$lat			= $data->favorito->lat;
$lng			= $data->favorito->lng;
$ciudadtmp 		= $data->favorito->ciudad;
$ciudad 		= explode("-", $ciudadtmp);
$ciudad 		= trim($ciudad[0]);
$fecharegistro  = date("Y-m-d H:i:s");

$stmt = $con->prepare("INSERT INTO direcciones_fav(idusuario, titulo, direccion, indicaciones, ciudad, lat, lng, fecharegistro) VALUES (?,?,?,?,?,?,?,?)");

$stmt->bind_param("isssssss", $idusuario,$titulo,$direccion,$indicaciones,$ciudad,$lat,$lng,$fecharegistro);

$stmt->execute();

$iddireccionfav = $stmt->insert_id;

//validar que todo salga bien con $stmt->error
if($stmt->error==""){
	echo json_encode(array('respuesta' => true,'iddireccionfav' => $iddireccionfav));			
}else{
	echo json_encode(array('respuesta' => false,'error'=>$stmt->error));
}

?>