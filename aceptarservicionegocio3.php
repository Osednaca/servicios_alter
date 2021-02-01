<?php
include("includes.php");

$post_date  	   = file_get_contents("php://input");
$data 			   = json_decode($post_date);
$token 			   = Auth::GetData(
    				    $data->token
				    );
$idusuario  	   = $token->id;
$idservicio 	   = $data->idservicio;

$stmt1 = $con->prepare("SELECT estatus FROM notificacionlugar WHERE idservicio=?");
/* bind parameters for markers */
$stmt1->bind_param("i", $idservicio);
/* execute query */
$stmt1->execute();

$stmt1->bind_result($estatus);

$stmt1->fetch();

$stmt1->free_result();

if($estatus == 2){

$stmt3 = $con->prepare("UPDATE notificacionlugar SET estatus=1 WHERE idservicio=?");
/* bind parameters for markers */
$stmt3->bind_param("i", $idservicio);
/* execute query */
$stmt3->execute();

$stmt2 = $con->prepare("UPDATE servicio SET estatus=2 WHERE idservicio=?");
/* bind parameters for markers */
$stmt2->bind_param("i", $idservicio);
/* execute query */
$stmt2->execute();
if($stmt2->error != ""){
    //echo "TEST"; die();
	echo json_encode(array('respuesta' => false, 'error' => $stmt2->error, 'mensaje' => 'Hubo un error. Consulte con un administrador.'));
    reporte_error("$idusuario","",$stmt2->error,"aceptarservicionegocio.php","");    
	die();
}else{
    //var_dump($servicio); die();
	echo json_encode(array('respuesta' => true));
	die();
}
}elseif($estatus == 0){
	echo json_encode(array('respuesta' => false, 'mensaje' => 'El servicio ha sido cancelado'));
}

?>