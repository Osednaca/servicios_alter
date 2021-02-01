<?php
	include("includes/utils.php");
	
	$post_date  	 	= file_get_contents("php://input");
	$data 			 	= json_decode($post_date);
	$cedula			 	= $data->cedula;
	$codigoactivacion 	= $data->codigo;

	//Validar que el codigo de activacion sea correcto
	$stmt = $con->prepare("SELECT codigoactivacion FROM activacionregistro WHERE cedula = ? ORDER BY fechaenvio DESC LIMIT 1");
	/* bind parameters for markers */
	$stmt->bind_param("s", $cedula);
	
	/* execute query */
	$stmt->execute();
	
	/* bind result variables */
	$stmt->bind_result($codigoenviado);
	
	/* fetch value */
	$stmt->fetch();

	$stmt->free_result();

	if($codigoactivacion == $codigoenviado){
		echo json_encode(array('respuesta' => true));
	}else{
		echo json_encode(array('respuesta' => false, 'mensaje'=>'El codigo de activacion es incorrecto.'));
	}
?>