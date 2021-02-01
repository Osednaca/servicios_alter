<?php

include("includes.php");

$post_date  	 		= file_get_contents("php://input");
$data 			 		= json_decode($post_date);

$token 					= 	Auth::GetData(
     							$data->token
 						  	);
$idusuario 				= 	$token->id;
$nombre					= 	utf8_decode($data->nombre);
$iditem					= 	$data->iditem;
if(!empty($data->descripcion)){
	$descripcion 		= 	utf8_decode($data->descripcion);
}else{
	$descripcion 		=   "";
}
$precio 				= 	$data->precio;
$imagen					=   $nombre.".png";
$fecharegistro 			= 	date("Y-m-d");
$aprobado 				= 	0;

//Validar que el negocio no este utilizado por otro usuario
if ($nombre !=''){
		$stmt = $con->prepare("INSERT INTO items_extras(iditem, nombre, descripcion, imagen, costo, idusuario, fecharegistro, aprobado) VALUES (?,?,?,?,?,?,?,?)");
		/* bind parameters for markers */
		$stmt->bind_param("issssisi", $iditem,$nombre,$descripcion,$imagen,$precio,$idusuario,$fecharegistro,$aprobado);

		/* execute query */
		$stmt->execute();
		if($stmt->error == ""){
			$stmt = $con->prepare("UPDATE lugar_items SET extras = 1 WHERE iditem = ?");
			$stmt->bind_param("i", $iditem);
			$stmt->execute();			
			echo json_encode(array('respuesta' => true));
		}else{
			echo json_encode(array('respuesta' => false,'mensaje'=>'Error en el sistema. Contacte con un administrador.', 'error'=>$stmt->error));
			reporte_error($idusuario,"",$stmt->error,"registronegocio.php",$sql);
		}
}
?>