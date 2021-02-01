<?php

include("includes/utils.php");

session_start();
$post_date  	   = file_get_contents("php://input");
$data 			   = json_decode($post_date);
$idusuario	       = $_SESSION["idusuario"];
//Guardar datos Bancarios
$idcuenta	       = $data->cuenta->idcuenta;
$numerocuenta 	   = $data->cuenta->numerocuenta;
$idbanco	  	   = $data->cuenta->idbanco;
$tipocuenta	  	   = $data->cuenta->idtipocuenta;
$nombretitular	   = $data->cuenta->nombretitular;
$cedula		  	   = $data->cuenta->cedulatitular;
$fechamodificacion = date("Y-m-d H:i:s");

	//Validar que el numero de cuenta no se encuentre registrado
	$stmt = $con->prepare("SELECT idcuenta FROM cuentabancaria WHERE numerocuenta = ? AND idcuenta<>?");
	/* bind parameters for markers */
	$stmt->bind_param("si", $numerocuenta,$idcuenta);
	
	/* execute query */
	$stmt->execute();
	
	/* bind result variables */
	$stmt->bind_result($validacuenta);
	
	/* fetch value */
	$stmt->fetch();
	
	$stmt->free_result();

if($validacuenta == ""){
	$stmt2 = $con->prepare("UPDATE cuentabancaria SET numerocuenta=?,idbanco=?,tipocuenta=?,nombretitular=?,cedula=?,estatus=?,fechamodificacioncuenta=? WHERE idcuenta=?");
	
	$stmt2->bind_param("siissisi", $numerocuenta,$idbanco,$tipocuenta,$nombretitular,$cedula,$estatus,$fechamodificacion,$idcuenta);
	
	$stmt2->execute();
	if($stmt2->error==""){
		echo json_encode(array('respuesta' => true));
	}else{
		echo json_encode(array('respuesta' => false,'error' => $stmt2->error));
	}
}else{
	// Sino muestra un error
	echo json_encode(array('respuesta' => false,'mensaje'=>'El numero de cuenta ya se encuentra registrado.'));
}
?>