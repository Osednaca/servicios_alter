<?php

	include("includes.php");

$post_date  	= file_get_contents("php://input");
$data 			= json_decode($post_date);
$token 			= Auth::GetData(
     				$data->token
 				  );
$idusuario  	= $token->id;
$calificacion	= $data->calificacion->estrellas;

if(!empty($data->calificacion->comentario)){
	$comentarios		= 	$data->calificacion->comentario;
}else{
	$comentarios 		=   "";
}

$idusuariocalificado	= 	$data->idusuariocalificado;
$idservicio 			= 	$data->idservicio;
$fecharegistro 			=   date("Y-m-d H:i:s");
$tipousuariocalificado  =   $data->tipousuariocalificado;

//Validar que el servicio no haya sido calificado
$stmt = $con->prepare("SELECT idcalificacion FROM calificacion WHERE idusuario = ? AND idservicio = ?");
/* bind parameters for markers */
$stmt->bind_param("ii", $idusuario,$idservicio);

/* execute query */
$stmt->execute();

/* bind respuesta variables */
$stmt->bind_result($idcalificacion);

/* fetch value */
$stmt->fetch();

$stmt->free_result();
//var_dump($idcalificacion); die();
//Si no existe ninguno guarda el registro en BD
if($idcalificacion==""){
	$stmt = $con->prepare("INSERT INTO calificacion(calificacion, comentarios, idservicio,idusuario,idusuariocalificado, fechacalificacion,tipousuariocalificado) VALUES (?,?,?,?,?,?,?)");
	/* bind parameters for markers */
	$stmt->bind_param("isiiisi", $calificacion, $comentarios, $idservicio,$idusuario,$idusuariocalificado,$fecharegistro,$tipousuariocalificado);

	/* execute query */
	$stmt->execute();

	if($stmt->error == ""){
		echo json_encode(array('respuesta' => true));
	}else{
		echo json_encode(array('respuesta' => false, 'error' => $stmt->error));
	}
}else{
	// Sino muestra un error
	echo json_encode(array('respuesta' => false,'msg'=>'Este servicio ya fue calificado anteriormente.'));
}

?>