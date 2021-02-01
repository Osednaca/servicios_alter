<?php
	include("includes/utils.php");
	
	$post_date  	 = file_get_contents("php://input");
	$data 			 = json_decode($post_date);
	//Guardar datos Bancarios
	$correo 	= $data->email;

	//Validar que el correo no lo tenga registrado otro usuario
	$stmt = $con->prepare("SELECT idusuario FROM usuario WHERE correo = ?"); //AND estatus=1
	/* bind parameters for markers */
	$stmt->bind_param("s", $correo);
	
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
		echo json_encode(array('respuesta' => false, 'mensaje'=>'El correo ya esta registrado en el sistema.'));
	}
?>