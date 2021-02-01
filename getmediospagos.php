<?php
include("includes/utils.php");
session_start();
$post_date  = file_get_contents("php://input");
$data 		= json_decode($post_date);
$idusuario  = $_SESSION["idusuario"];
$tarjetas 	= array();
$stmt = $con->prepare("SELECT idtarjetausuario,franquicia,ultimoscuatro,estatus
						FROM tarjetasusuario
						WHERE idusuario = ?");

$stmt->bind_param("i",$idusuario);

$stmt->execute();

if($stmt->error == ""){
	/* bind result variables */
	$stmt->bind_result($idtarjetausuario,$franquicia,$ultimoscuatro,$estatus);
	$stmt->store_result();
	while ($stmt->fetch()) {
		//$stmt->free_result()
		$tarjetas[] = array('idtarjetausuario'=>$idtarjetausuario,'franquicia'=>$franquicia,'ultimoscuatro'=>$ultimoscuatro,'estatus'=>$estatus);
	}
	$stmt1 = $con->prepare("SELECT AVG(calificacion),nombre,apellido FROM calificacion INNER JOIN usuario ON usuario.idusuario=idusuariocalificado WHERE idusuariocalificado = ?");
	$stmt1->bind_param("i",$idusuario);
	$stmt1->execute();
	$stmt1->bind_result($calificacion,$nombre,$apellido);
	$stmt1->fetch();
	$usuariocalificacion = array("calificacion"=>$calificacion,"nombre"=>utf8_encode($nombre),"apellido" => utf8_encode($apellido));

	
	if(empty($stmt1->error)){
		echo json_encode(array('respuesta' => true, 'tarjetas'=>$tarjetas,'usuariocalificacion'=>$usuariocalificacion));
	}else{
		echo json_encode(array('respuesta' => false, 'error' => $stmt1->error));
	}
}else{
	echo json_encode(array('respuesta' => false, 'mensaje' => $stmt->error));
}

?>