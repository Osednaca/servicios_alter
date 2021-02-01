<?php
include("includes.php");

$post_date  	  = file_get_contents("php://input");
$data 			  = json_decode($post_date);
$token 			  = Auth::GetData(
    				$data->token
				  );
$idusuario  	  = $token->id;
$idservicio		  = $data->idservicio;
$idvehiculo		  = $data->idvehiculo;
$idlugar		  = $data->idlugar;
//$direccionpartida = $data->direccionpartida

//Verificar que el servicio ya no haya sido tomado por otro proveedor

$stmt = $con->prepare("SELECT idproveedor,estatus FROM servicio WHERE idservicio = ?");
/* bind parameters for markers */
$stmt->bind_param("i", $idservicio);
/* execute query */
$stmt->execute();
/* bind result variables */
$stmt->bind_result($idproveedor,$estatus);
$stmt->fetch();
$stmt->free_result();
if($idproveedor == NULL){
	//Si el cliente cancelo la busqueda del servicio
	if($estatus === 0){
		$stmt4 = $con->prepare("UPDATE notificacion SET estatus=0 WHERE idusuario=? AND idservicio=?");
		/* bind parameters for markers */
		$stmt4->bind_param("ii", $idusuario,$idservicio);
		/* execute query */
		$stmt4->execute();
		echo json_encode(array('respuesta' => false, 'mensaje' => 'El servicio fue cancelado por el cliente antes de aceptarlo.'));
		die();	
	}

	//Poner fuera del servicio al Proveedor para que se concentre solo en el servicio que acepto
	$disponibilidad = 0;

	$stmt1 = $con->prepare("UPDATE usuario SET disponibilidad=? WHERE idusuario=?");
	/* bind parameters for markers */
	$stmt1->bind_param("is", $disponibilidad,$idusuario);
	/* execute query */
	$stmt1->execute();
	if($stmt1->error != ""){
		echo json_encode(array('respuesta' => false, 'error' => $stmt1->error));
		die();
	}
	$stmt3 = $con->prepare("UPDATE notificacion SET estatus=1 WHERE idusuario=? AND idservicio=?");
	/* bind parameters for markers */
	$stmt3->bind_param("ii", $idusuario,$idservicio);
	/* execute query */
	$stmt3->execute();
	if($stmt3->error != ""){
		echo json_encode(array('respuesta' => false, 'error' => $stmt3->error));
		die();
	}
	$stmt5 = $con->prepare("UPDATE notificacionlugar SET estatus=1 WHERE idservicio=? AND idlugar = ?");
	/* bind parameters for markers */
	$stmt5->bind_param("ii", $idservicio,$idlugar);
	/* execute query */
	$stmt5->execute();

	//Actualizar Servicio con los datos del proveedor
	$stmt2 = $con->prepare("UPDATE servicio SET idproveedor=?, estatus=2, idvehiculo = ? WHERE idservicio=?");

	/* bind parameters for markers */

	$stmt2->bind_param("iii", $idusuario,$idvehiculo,$idservicio);

	

	/* execute query */

	$stmt2->execute();

	if($stmt2->error == ""){
		if($stmt2->affected_rows > 0){
			echo json_encode(array('respuesta' => true));
		}else{
			echo json_encode(array('respuesta' => false, 'mensaje' => 'El servicio no existe.'));
		}
	}else{
		echo json_encode(array('respuesta' => false, 'error' => $stmt2->error));
	}

}else{
	$stmt4 = $con->prepare("UPDATE notificacion SET estatus=0 WHERE idusuario=? AND idservicio=?");

	$stmt4->bind_param("ii", $idusuario,$idservicio);

	$stmt4->execute();

	echo json_encode(array('respuesta' => false, 'mensaje' => 'El servicio ya fue tomado por otro proveedor.'));
	die();
}

?>