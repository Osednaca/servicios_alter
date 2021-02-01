<?php
	include("includes/utils.php");
	$post_date  	= file_get_contents("php://input");
	$data 			= json_decode($post_date);
	$idservicio  	= $data->idservicio;
	$mensajes 		= array();
	$stmt = $con->prepare("SELECT idmensaje,idservicio,idremitente,iddestinatario,mensaje,mensajes.estatus,fechaenvio,idcliente
							FROM mensajes 
							INNER JOIN servicio USING(idservicio)
							WHERE idservicio=? AND mensajes.estatus=1");

	$stmt->bind_param("i",$idservicio);

	$stmt->execute();

/* bind result variables */
$stmt->bind_result($idmensaje,$idservicio1,$idremitente,$iddestinatario,$mensaje,$estatus,$fechaenvio,$idcliente);
$stmt->store_result();
while ($stmt->fetch()) {
	$mensajes[] = array('idmensaje'=>$idmensaje,'idservicio'=>$idservicio1,'idremitente'=>$idremitente,'iddestinatario'=>$iddestinatario,'mensaje'=>$mensaje,'estatus'=>$estatus,'fechaenvio'=>$fechaenvio);
}

if(!empty($mensajes)){
	$nuevomensaje = true;
}else{
	$nuevomensaje = false;
}

$stmt1 = $con->prepare("SELECT estatus FROM servicio WHERE idservicio=?");
$stmt1->bind_param("i",$idservicio);
$stmt1->execute();
$stmt1->bind_result($estatuserv);
$stmt1->fetch();

echo json_encode(array('estatusservicio'=> $estatuserv,'nuevomensaje'=>$nuevomensaje, 'mensajes' => $mensajes));

?>