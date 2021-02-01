<?php
include("includes/utils.php");

$idusuario 	= $_REQUEST["idusuario"];
$token 		= $_REQUEST["token"];

//Validar que el correo este registrado en la base de datos.
$stmt = $con->prepare("SELECT tokenpassword FROM usuario WHERE idusuario=?");
/* bind parameters for markers */
$stmt->bind_param("i", $idusuario);

/* execute query */
$stmt->execute();

/* bind result variables */
$stmt->bind_result($tokenpassword);

/* fetch value */
$stmt->fetch();

if($tokenpassword==$token){
	echo json_encode(array('respuesta' => true));
}else{
	echo json_encode(array('respuesta' => false));
}

?>