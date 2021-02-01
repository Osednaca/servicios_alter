<?php

include("includes.php");
$post_date  	= file_get_contents("php://input");
$data 			= json_decode($post_date);
$token 			= Auth::GetData(
    				$data->token
				);
$idusuario  	= $token->id;
$idfavorito 	= $data->favorito->idfavorito;
$direccion 		= $data->favorito->direccion;
$titulo 		= $data->favorito->nombre;
if(!empty($data->favorito->indicaciones)){
	$indicaciones 	= $data->favorito->indicaciones;
}else{
	$indicaciones 	= "";
}
$lat			= $data->favorito->lat;
$lng			= $data->favorito->lng;
if(!empty($data->favorito->ciudad)){
	$ciudad 		= $data->favorito->ciudad;
	//echo $ciudad; die;
	$sqlciudad 		= ",ciudad='$ciudad'";
}else{
	$sqlciudad 		= "";
}
$fecharegistro  = date("Y-m-d H:i:s");

$stmt = $con->prepare("UPDATE direcciones_fav SET idusuario=?,titulo=?,direccion=?,indicaciones=? $sqlciudad,lat=?,lng=? WHERE iddireccionfav=?");

$stmt->bind_param("isssssi", $idusuario,$titulo,$direccion,$indicaciones,$lat,$lng,$idfavorito);

$stmt->execute();

//validar que todo salga bien con $stmt->error
if($stmt->error==""){
	echo json_encode(array('respuesta' => true));			
}else{
	echo json_encode(array('respuesta' => false,'error'=>$stmt->error));
}

?>