<?php

include("includes.php");

$post_date  	 		= file_get_contents("php://input");
$data 			 		= json_decode($post_date);

$token 					= 	Auth::GetData(
     							$data->token
							   );
$idusuario 				= $token->id;

$nombre					= 	$data->categoria->nombre;

if ($nombre !=''){
		$stmt = $con->prepare("INSERT INTO lugar_categoria(categoria,aprobado,idusuario) VALUES (?,0,?)");
		/* bind parameters for markers */
		$stmt->bind_param("si", $nombre,$idusuario);

		/* execute query */
		$stmt->execute();
		if($stmt->error == ""){
			$idcategoria = $stmt->insert_id;
			echo json_encode(array('respuesta' => true,'idcategoria' => $idcategoria));
		}else{
			echo json_encode(array('respuesta' => false,'mensaje'=>'Error en el sistema. Contacte con un administrador.', 'error'=>$stmt->error));
			reporte_error($idusuario,"",$stmt->error,"registro_categoria.php",$sql);
		}
}
?>