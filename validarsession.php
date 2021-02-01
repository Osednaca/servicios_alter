<?php
include("includes/utils.php");
$post_date  = file_get_contents("php://input");
$data 		= json_decode($post_date);
$session_key    = $data->sessionkey;

$stmt = $con->prepare("SELECT idlogin,cedula,nombre,apellido,imgusuario,idpais,imgrut,limiterut FROM usuariologin INNER JOIN usuario USING(idusuario) WHERE accesstoken = ?");
$stmt2 = $con->prepare("SELECT count(idcuentanequi) FROM cuenta_nequi INNER JOIN usuario USING(idusuario) INNER JOIN usuariologin USING(idusuario) WHERE accesstoken = ?");

$stmt->bind_param("s",$session_key);

$stmt->execute();

if($stmt->error == ""){
	/* bind result variables */
	$stmt->store_result();
	$stmt->bind_result($idlogin,$cedula,$nombre,$apellido,$imgusuario,$idpais,$imgrut,$limiterut);
	$stmt->fetch();
	//var_dump($preguntas); die();
	if($stmt->num_rows > 0){
		if($stmt->error == ""){
			$stmt2->bind_param("s",$session_key);
			$stmt2->execute();
			$stmt2->bind_result($ncuentanequi);
			$stmt2->fetch();			
			if($stmt2->error == ""){
				if($ncuentanequi > 0){
					$cuentanequi = true;
				}else{
					$cuentanequi = false;
				}
			}else{
				echo json_encode(array('respuesta' => false, 'mensaje' => $stmt2->error));
			}

			echo json_encode(array('respuesta' => true, 'cedula'=>$cedula,'nombre'=>$nombre,'apellido'=>$apellido,'imgusuario'=>$imgusuario,'cuentanequi' => $cuentanequi,'ncuentanequi' => $ncuentanequi,'idpais'=>$idpais,'imgrut'=>$imgrut,'limiterut'=>$limiterut));
		}else{
			echo json_encode(array('respuesta' => false, 'mensaje' => $stmt->error));
		}
	}else{
		echo json_encode(array('respuesta' => false, 'mensaje' => 'Sesion no encontrada.'));
	}
}else{
	echo json_encode(array('respuesta' => false, 'mensaje' => $stmt->error));
}	

?>