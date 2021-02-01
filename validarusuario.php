<?php
	include("includes/utils.php");
	
	$post_date  	 = file_get_contents("php://input");
	$data 			 = json_decode($post_date);
	//Guardar datos Bancarios
	$correo 	= $data->correo;
	$cedula 	= $data->cedula;

	//Validar que el correo no lo tenga registrado otro usuario
	$stmt = $con->prepare("SELECT idusuario FROM usuario WHERE correo = ? OR cedula = ?"); //AND estatus=1
	/* bind parameters for markers */
	$stmt->bind_param("ss", $correo, $cedula);
	
	/* execute query */
	$stmt->execute();
	
	/* bind result variables */
	$stmt->bind_result($idusuario);
	
	/* fetch value */
	$stmt->fetch();

	$stmt->free_result();

	if($idusuario == ""){
		echo json_encode(array('respuesta' => true));
	}else{
		echo json_encode(array('respuesta' => false, 'mensaje'=>'El usuario ya esta registrado en el sistema.'));
	}
?>