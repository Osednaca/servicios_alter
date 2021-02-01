<?php

include("includes.php");

$post_date  	 	  = file_get_contents("php://input");
$data 			 	  = json_decode($post_date);

$token 				  = 	Auth::GetData(
     							$data->token
 						  	);
$idusuario 			  =   $token->id;
$idlugar              =   $data->tienda->idlugar;
$nombre			      =   utf8_decode($data->tienda->nombretienda);
$nombrepropietario    =   utf8_decode($data->tienda->nombrepropietario);
$ciudad				  =   $data->tienda->ciudad;
$sqlimagen            =   "";
$latlug               =   $data->tienda->lat;
$lnglug               =   $data->tienda->lng;
$direccion			  =   utf8_decode($data->tienda->direccion);
$fecharegistro 		  =   date("Y-m-d H:i:s");

if(!empty($data->tienda->editarlogo)){
  $imagen     = $data->nombreimagen.".png";
  $sqlimagen  = ",imagen='$imagen'";
}else{
  $imagen     = $data->nombreimagen.".png";
}

//Validar que el negocio no este utilizado por otro usuario
if ($nombre !=''){
	$stmt = $con->prepare("UPDATE lugar SET nombrelugar=?,direccion=?,ciudad=?,fechamodificacion=?,lat=?,lng=?,nombrepropietario=? $sqlimagen WHERE idlugar= ?");
	/* bind parameters for markers */
	$stmt->bind_param("sssssssi", $nombre,$direccion,$ciudad,$fecharegistro,$latlug,$lnglug,$nombrepropietario,$idlugar);
      $stmt->execute();
    if($stmt->error == ""){
		echo json_encode(array('respuesta' => true));
	}else{
		echo json_encode(array('respuesta' => false,'mensaje'=>'Error en el sistema. Contacte con un administrador.', 'error'=>$stmt->error));
		//reporte_error($idusuario,"",$stmt->error,"registronegocio.php",$sql);
	}
}
?>