<?php

include("includes.php");

$post_date  	 		= file_get_contents("php://input");
$data 			 		= json_decode($post_date);

$token 					= 	Auth::GetData(
     							$data->token
 						  	);
$idusuario 				= 	$token->id;
$idlugar 				=   $data->mercadocategoria->idlugar;
$nombre					= 	$data->mercadocategoria->nombre;
$imagen					=   $nombre.".jpg";

//Validar que el negocio no este utilizado por otro usuario
if ($nombre !=''){
		$stmt = $con->prepare("INSERT INTO mercado_categoria(idlugar,categoria,idusuario) VALUES (?,?,?)");
		/* bind parameters for markers */
		$stmt->bind_param("isi", $idlugar,$nombre,$idusuario);

		/* execute query */
		$stmt->execute();
		if($stmt->error == ""){
			$id = $stmt->insert_id;
			echo json_encode(array('respuesta' => true,'id' => $id));
		}else{
			echo json_encode(array('respuesta' => false,'mensaje'=>'Error en el sistema. Contacte con un administrador.', 'error'=>$stmt->error));
			reporte_error($idusuario,"",$stmt->error,"registro_mercado_categoria.php",$sql);
		}
}
?>