<?php
	include("includes.php");

	$post_date  	= file_get_contents("php://input");
	$data 			= json_decode($post_date);
	$token 			= Auth::GetData(
        				$data->token
    				  );
	$idusuario  	= $token->id;
	$tickets 		= array();

$stmt = $con->prepare("SELECT idticketsoporte,descripcion,estatus,fechaticket FROM ticketsoporte WHERE idusuario=?");

$stmt->bind_param("i",$idusuario);

$stmt->execute();

if($stmt->error == ""){
	/* bind result variables */
	$stmt->bind_result($idticketsoporte,$descripcion,$estatus,$fechaticket);
	$stmt->store_result();
	while ($stmt->fetch()) {
		$tickets[] = array('idticketsoporte'=>$idticketsoporte,'descripcion'=>$descripcion,'estatus'=>$estatus,'fechaticket'=>$fechaticket);
	}

	if(!empty($tickets)){
		echo json_encode(array('respuesta' => true, 'tickets'=>$tickets));
	}else{
		echo json_encode(array('respuesta' => false));
	}
}else{
	echo json_encode(array('respuesta' => false, 'mensaje' => $stmt->error));
}

?>