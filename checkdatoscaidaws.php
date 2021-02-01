<?php
	include("includes.php");

	$post_date  	= file_get_contents("php://input");
	$data 			= json_decode($post_date);
	$token 			= Auth::GetData(
        				$data->token
    				  );
	$idusuario  	= $token->id;
	$idservicio  	= $data->idservicio;
	$mensajes 		= array();

//Checkear estatus del servicio
$stmt = $con->prepare("SELECT estatus FROM servicio WHERE idservicio=?");

$stmt->bind_param("i",$idservicio);

$stmt->execute();

if($stmt->error == ""){
	$stmt->bind_result($estatusservicio);
	$stmt->fetch();
	$stmt->free_result();

	$stmt1 = $con->prepare("SELECT idmensaje,idservicio,idremitente,iddestinatario,mensaje,mensajes.estatus,fechaenvio,idcliente FROM mensajes INNER JOIN servicio USING(idservicio) WHERE mensajes.idservicio=? AND mensajes.estatus IN(1,2) AND idremitente<>?");
	$stmt1->bind_param("ii",$idservicio,$idusuario);
	$stmt1->execute();
	$stmt1->bind_result($idmensaje,$idservicio,$idremitente,$iddestinatario,$mensaje,$estatus,$fechaenvio,$idcliente);

	while ($stmt1->fetch()) {
		if($idremitente==$idusuario){
			$esremitente = true;
		}else{
			$esremitente = false;
		}
		$mensajes[] = array('idmensaje'=>$idmensaje,'idservicio'=>$idservicio,'idremitente'=>$idremitente,'iddestinatario'=>$iddestinatario,'mensaje'=>$mensaje,'estatus'=>$estatus,'fechaenvio'=>$fechaenvio,'esremitente'=>$esremitente);
	}

	echo json_encode(array('respuesta' => true, 'estatus'=>$estatusservicio, 'mensajes' => $mensajes));
}else{
	echo json_encode(array('respuesta' => false, 'mensaje' => $stmt->error));
}

?>