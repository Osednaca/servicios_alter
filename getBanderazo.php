<?php
include("includes/utils.php");
$post_date  		= file_get_contents("php://input");
$data 				= json_decode($post_date);
$idtiposervicio     = $data->idtiposervicio;
$idtipovehiculo     = $data->idtipovehiculo;
$idciudad     		= $data->idciudad;

$stmt = $con->prepare("SELECT valor FROM banderazo WHERE idtiposervicio = ? AND idtipovehiculo = ? AND idciudad = ?");
/* bind parameters for markers */
$stmt->bind_param("iii", $idtiposervicio,$idtipovehiculo,$idciudad);

/* execute query */
$stmt->execute();

/* bind result variables */
$stmt->bind_result($valor);

/* fetch value */
$stmt->fetch();

$stmt->free_result();

if($valor==""){
	echo json_encode(array('respuesta' => false,'mensaje' => 'Error'));
}else{
	echo json_encode(array('respuesta' => true, 'banderazo'=> $valor));
}

?>