<?php
	include("includes.php");
	
	$post_date  	= file_get_contents("php://input");
	$data 			= json_decode($post_date);
	$idticket  		= $data->idticket;	
	$ticketdetalle 	= array();

$stmt = $con->prepare("SELECT idrespuestasoporte,respuesta,respuestasoporte.estatus,fecharespuesta,ticketsoporte.idtemasoporte,idoperador,nombre,apellido FROM respuestasoporte INNER JOIN ticketsoporte USING(idticketsoporte) LEFT JOIN usuario ON usuario.idusuario = idoperador WHERE idticketsoporte=?");

$stmt->bind_param("i",$idticket);

$stmt->execute();

if($stmt->error == ""){
	/* bind result variables */
	$stmt->bind_result($idrespuestasoporte,$respuesta,$estatus,$fecharespuesta,$idtemasoporte,$idoperador,$nombre,$apellido);
	$stmt->store_result();
	while ($stmt->fetch()) {
		if(!empty($idoperador)){
			$color  = "#274b8e";
			$align  = "left";
			$fcolor = "white";
		}else{
			$color  = "#08c6e8";
			$align  = "right";
			$fcolor = "black";
		}
		$ticketdetalle[] = array('idrespuestasoporte'=>$idrespuestasoporte,'respuesta'=>utf8_encode($respuesta),'estatus'=>$estatus,'fecharespuesta'=>$fecharespuesta,'idtemasoporte'=>$idtemasoporte,'idoperador'=>$idoperador,'nombre'=>$nombre,'apellido'=>$apellido,'color'=>$color,'align'=>$align,'fcolor'=>$fcolor);
	}

	if(!empty($ticketdetalle)){
		echo json_encode(array('respuesta' => true, 'ticketdetalles'=>$ticketdetalle));
	}else{
		echo json_encode(array('respuesta' => false));
	}
}else{
	echo json_encode(array('respuesta' => false, 'mensaje' => $stmt->error));
}

?>