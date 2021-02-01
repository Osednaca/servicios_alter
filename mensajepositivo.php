<?php
include("includes.php");

$stmt = $con->prepare("SELECT mensaje FROM mensaje_positivo ORDER BY RAND() LIMIT 1;");

$stmt->execute();

if($stmt->error == ""){
	/* bind result variables */
	$stmt->bind_result($mensaje);
	$stmt->fetch();
	
	if(!empty($mensaje)){
		echo json_encode(array('respuesta' => true, 'mensajepositivo'=>$mensaje));
	}else{
		echo json_encode(array('respuesta' => false, 'mensaje' => 'No se encontraron recomendaciones'));
	}
}else{
	echo json_encode(array('respuesta' => false, 'mensaje' => $stmt->error));
}

?>