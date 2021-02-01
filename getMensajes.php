<?php
	include("includes.php");

	$post_date  	= file_get_contents("php://input");
	$data 			= json_decode($post_date);
	$token 			= Auth::GetData(
        				$data->token
    				  );
	$idusuario  	= $token->id;
	$idservicio  	= $data->idservicio;
	$servicios 		= array();

	$stmt = $con->prepare("SELECT idmensaje,idservicio,idremitente,iddestinatario,mensaje,mensajes.estatus,fechaenvio,idcliente
							FROM mensajes 
							INNER JOIN servicio USING(idservicio)
							WHERE idservicio=?");

	$stmt->bind_param("i",$idservicio);

	$stmt->execute();

if($stmt->error == ""){
	/* bind result variables */
	$stmt->bind_result($idmensaje,$idservicio,$idremitente,$iddestinatario,$mensaje,$estatus,$fechaenvio,$idcliente);
	$stmt->store_result();
	while ($stmt->fetch()) {
		if($idremitente==$idusuario){
			$esremitente = true;
		}else{
			$esremitente = false;
		}
		$mensajes[] = array('idmensaje'=>$idmensaje,'idservicio'=>$idservicio,'idremitente'=>$idremitente,'iddestinatario'=>$iddestinatario,'mensaje'=>html_entity_decode($mensaje),'estatus'=>$estatus,'fechaenvio'=>$fechaenvio,'esremitente'=>$esremitente);
	}

	
	if(!empty($mensajes)){
		//Marcar mensajes como leidos
		$stmt1 = $con->prepare("UPDATE mensajes SET estatus = 3 WHERE idservicio=? AND estatus IN(1,2)");
		$stmt1->bind_param("i",$idservicio);
		$stmt1->execute();

		echo json_encode(array('respuesta' => true, 'mensajes'=>$mensajes));
	}else{
		echo json_encode(array('respuesta' => false, 'mensaje' => 'No se encontraron mensajes'));
	}
}else{
	echo json_encode(array('respuesta' => false, 'mensaje' => $stmt->error));
}

?>