<?php
	include("includes/utils.php");
	$post_date  	= file_get_contents("php://input");
	$data 			= json_decode($post_date);
	$tipousuario	= $data->tipousuario;
	$mensajes 		= array();

	$stmt = $con->prepare("SELECT mensaje FROM mensajespredeterminados WHERE tipousuario=?");
	$stmt->bind_param("i",$tipousuario);
	$stmt->execute();
    $stmt->bind_result($mensaje);
    
    /* fetch values */
    while ($stmt->fetch()) {
        $mensajes[] = array('mensaje'=>html_entity_decode(utf8_encode($mensaje)));
    }
    
	if (!empty($mensajes)) {
		echo json_encode(array('respuesta'=>true, 'mensajes' => $mensajes));
	}else{
		echo json_encode(array('respuesta'=>false));
	}
?>