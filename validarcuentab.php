<?php
	include("includes/utils.php");
	
	$post_date  	 = file_get_contents("php://input");
	$data 			 = json_decode($post_date);
	//Guardar datos Bancarios
	$numerocuenta 	= $data->numerocuenta;

	//Validar que el numero de cuenta no se encuentre registrado
	$stmt = $con->prepare("SELECT idcuenta FROM cuentabancaria WHERE numerocuenta = ?");
	/* bind parameters for markers */
	$stmt->bind_param("s", $numerocuenta);
	
	/* execute query */
	$stmt->execute();
	
	/* bind result variables */
	$stmt->bind_result($idcuenta);
	
	/* fetch value */
	$stmt->fetch();

	$stmt->free_result();

	if($idcuenta == ""){
		echo json_encode(array('respuesta' => true));
	}else{
		echo json_encode(array('respuesta' => false, 'mensaje'=>'El numero de cuenta ya esta registrado en el sistema.'));
	}
?>