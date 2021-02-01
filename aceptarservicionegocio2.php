<?php
include("includes.php");

$post_date  	   = file_get_contents("php://input");
$data 			   = json_decode($post_date);
$token 			   = Auth::GetData(
    				    $data->token
				    );
$idusuario  	   = $token->id;
$idservicio 	   = $data->idservicio;

$stmt = $con->prepare("SELECT estatus FROM servicio WHERE idservicio = ?");
/* bind parameters for markers */
$stmt->bind_param("i", $idservicio);
/* execute query */
$stmt->execute();
/* bind result variables */
$stmt->bind_result($estatus);
$stmt->fetch();
$stmt->free_result();

if($estatus === 0){
	$stmt2 = $con->prepare("UPDATE notificacionlugar SET estatus=0 WHERE idservicio=?");
	/* bind parameters for markers */
	$stmt2->bind_param("i", $idservicio);
	/* execute query */
	$stmt2->execute();
	echo json_encode(array('respuesta' => false, 'mensaje' => 'El servicio fue cancelado por el cliente antes de aceptarlo.'));
	die();	
}

$stmt1 = $con->prepare("UPDATE notificacionlugar SET estatus=3 WHERE idservicio=?");
/* bind parameters for markers */
$stmt1->bind_param("i", $idservicio);
/* execute query */
$stmt1->execute();
if($stmt1->error != ""){
    //echo "TEST"; die();
	echo json_encode(array('respuesta' => false, 'error' => $stmt1->error));
    reporte_error("$idusuario","",$stmt1->error,"aceptarservicionegocio.php","");    
	die();
}else{
    //var_dump($servicio); die();
	echo json_encode(array('respuesta' => true));
}

?>