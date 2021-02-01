<?php

	include("includes/utils.php");

	session_start();

	$post_date  	= file_get_contents("php://input");

	$data 			= json_decode($post_date);

	$idusuario  	= $data->idproveedor;

	$disponibilidad = 0;

	$fechanow 		= date("Y-m-d H:i:s");

	$Proveedor    = array();

	//echo "idservicio : $idservicio <br> idusuario : $idusuario <br> idciudad : $idciudad <br> tipovehiculo : $tipovehiculo";

//Validar que la fecha de la ubicacion sea reciente  Probar con este filtro: AND fechaubicacion >= NOW() - INTERVAL 5 MINUTE

$stmt = $con->prepare("SELECT idusuario as idusuario1, latitud, longitud FROM usuarioubicacion WHERE idusuario=? AND usuarioubicacion.tipousuario=2 ORDER BY fechaubicacion DESC LIMIT 1"); // 

/* bind parameters for markers */

$stmt->bind_param("i",$idusuario);

/* execute query */

$stmt->execute();

/* bind result variables */

$stmt->bind_result($idusuario, $latitud, $longitud);

$stmt->fetch();
$stmt->free_result();

if($latitud != ""){
	$prov_position = $latitud.", ".$longitud;
	echo json_encode(array('respuesta' => true, 'prov_position' => $prov_position));
}else{

	echo json_encode(array('respuesta' => false,'msj'=>'No se pudo obtener la ubicacion del proveedor.'));

}

?>