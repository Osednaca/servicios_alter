<?php
include("includes.php");

$post_date  	= file_get_contents("php://input");
$data 			= json_decode($post_date);
$token 			= Auth::GetData(
    				$data->token
				);
$idusuario  	= $token->id;
$idservicio 	= $data->idservicio;

$stmt = $con->prepare("SELECT idcliente,idproveedor FROM servicio WHERE idservicio=?");
/* bind parameters for markers */
$stmt->bind_param("i", $idservicio);
/* execute query */
$stmt->execute();

$stmt->bind_result($idcliente,$idproveedor);

$stmt->fetch();

$stmt->free_result();

$stmt = $con->prepare("UPDATE notificacionlugar SET estatus=0 WHERE idservicio=?");
/* bind parameters for markers */
$stmt->bind_param("i", $idservicio);

/* execute query */
$stmt->execute();

$stmt->free_result();
$estatus 	  = 0;
$canceladopor = 3;
$stmt2 = $con->prepare("UPDATE servicio SET estatus=?,canceladopor=? WHERE idservicio=?");
/* bind parameters for markers */
$stmt2->bind_param("iii", $estatus,$canceladopor,$idservicio);
/* execute query */
$stmt2->execute();

if($stmt2->error==""){
	echo json_encode(array('respuesta' => true,'idcliente' => $idcliente,'idproveedor' => $idproveedor));
}else{
	echo json_encode(array('respuesta' => false, 'mensaje' => 'Error en la base de datos.'));
}

?>