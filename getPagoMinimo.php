<?php

include("includes/utils.php");

$post_date  	= file_get_contents("php://input");
$data 			= json_decode($post_date);
$idtiposervicio	= $data->idtiposervicio;
$idtipovehiculo	= $data->idtipovehiculo;
$idciudad		= 0; //Todas las ciudades por los momentos

//Verificar que el servicio ya no haya sido tomado por otro proveedor

$stmt = $con->prepare("SELECT valor FROM tarifa_minima WHERE idtiposervicio = ? AND idtipovehiculo=? AND idciudad=?");
/* bind parameters for markers */
$stmt->bind_param("iii", $idtiposervicio,$idtipovehiculo,$idciudad);

/* execute query */
$stmt->execute();

/* bind result variables */
$stmt->bind_result($valor);

$stmt->fetch();

if($valor != ""){
	echo json_encode(array('respuesta' => true,'valor'=>$valor));
}else{
	echo json_encode(array('respuesta' => false, 'mensaje' => 'Error del Sistema.'));
}

?>