<?php

include("includes.php");

$post_date  	  = file_get_contents("php://input");
$data 			  = json_decode($post_date);
$token 			  = Auth::GetData(
    			  	$data->token
				    );
$idusuario  	  = $token->id;
$idservicio		  = $data->idservicio;
$fechaculminacion = date("Y-m-d H:i:s");
$estatus 		  = 5;
	

$stmt = $con->prepare("UPDATE servicio SET estatus=?,fechaculminacion=? WHERE idservicio=?");
$stmt->bind_param("isi", $estatus, $fechaculminacion, $idservicio);
$stmt->execute();

if($stmt->error == ""){
	if($stmt->affected_rows > 0){
		$stmt->free_result();
		if($stmt->error == ""){			
			echo json_encode(array('respuesta' => true));
		}else{
			echo json_encode(array('respuesta' => false, 'mensaje' => 'Hubo un error. Por favor contacte con un administrador.', 'error' => $stmt->error));
		}
	}else{
		echo json_encode(array('respuesta' => false, 'mensaje' => 'El servicio no existe.','codigoerror'=>2));
	}
}else{
	echo json_encode(array('respuesta' => false, 'error' => $stmt->error,'codigoerror'=>1));
}

?>