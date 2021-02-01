<?php
include("includes/utils.php");

$idusuario 			= $_REQUEST["idusuario"];
$token 				= $_REQUEST["token"];

$stmt = $con->prepare("SELECT token FROM cambiodocumentos WHERE idusuariosolicitud=? AND estatus=1");
/* bind parameters for markers */
$stmt->bind_param("i", $idusuario);

/* execute query */
$stmt->execute();

/* bind result variables */
$stmt->bind_result($tokendocumentos);

/* fetch value */
$stmt->fetch();

if($tokendocumentos==$token){
	echo json_encode(array('respuesta' => true));
}else{
	echo json_encode(array('respuesta' => false));
}

?>