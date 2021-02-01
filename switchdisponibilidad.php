<?php
include("includes.php");

$post_date  	  = file_get_contents("php://input");
$data 			  = json_decode($post_date);
$token 			  = Auth::GetData(
    				$data->token
				  );
$idusuario  	  = $token->id;

//Ver disponibilidad del usuario para prenderlo o apagarlo dependiendo del caso.
$stmt = $con->prepare("SELECT disponibilidad FROM usuario WHERE idusuario = ?");
/* bind parameters for markers */
$stmt->bind_param("i", $idusuario);

/* execute query */
$stmt->execute();

/* bind result variables */
$stmt->bind_result($disponibilidad);

/* fetch value */
$stmt->fetch();

$stmt->free_result();

if($disponibilidad == 1){
	$disponibilidad = 0;
}else{
	$disponibilidad = 1;
}

$stmt1 = $con->prepare("UPDATE usuario SET disponibilidad=? WHERE idusuario=?");
/* bind parameters for markers */
$stmt1->bind_param("is", $disponibilidad,$idusuario);

/* execute query */
$stmt1->execute();

if($stmt1->error == ""){
	if($stmt1->affected_rows > 0){
		echo json_encode(array('respuesta' => true));
	}else{
		echo json_encode(array('respuesta' => false, 'mensaje' => 'El usuario no existe.'));
	}
}else{
	echo json_encode(array('respuesta' => false, 'error' => $stmt->error));
}

?>