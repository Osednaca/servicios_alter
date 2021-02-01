<?php

include("includes.php");

$post_date  	 		= file_get_contents("php://input");
$data 			 		= json_decode($post_date);

$token 					= 	Auth::GetData(
     							$data->token
 						  	);
$idusuario 				= 	$token->id;
$iditemextra                 =   $data->extra->iditemextra;
$iditem                 =   $data->extra->iditem;
$nombre			      	= 	utf8_decode($data->extra->nombre);
$precio					= 	$data->extra->precio;
$sqlimagen 				= 	"";

if(!empty($data->extra->descripcion)){
	$descripcion 		= 	utf8_decode($data->extra->descripcion);
}else{
	$descripcion 		=   "";
}

if(!empty($data->extra->editarlogo)){
  $imagen   = $nombre.".png";
  $sqlimagen  = ",imagen='$imagen'";
}

$stmt = $con->prepare("UPDATE items_extras SET iditem=?,nombre=?,descripcion=?,costo=? $sqlimagen WHERE iditemextra=?");
/* bind parameters for markers */
$stmt->bind_param("isssi", $iditem,$nombre,$descripcion,$precio,$iditemextra);
/* execute query */
$stmt->execute();
if($stmt->error == ""){
	echo json_encode(array('respuesta' => true));
}else{
	echo json_encode(array('respuesta' => false,'mensaje'=>'Error en el sistema. Contacte con un administrador.', 'error'=>$stmt->error));
	//reporte_error($idusuario,"",$stmt->error,"registronegocio.php",$sql);
}
?>