<?php
	include("includes.php");

	$post_date  	= file_get_contents("php://input");
	$data 			= json_decode($post_date);
	$token 			= Auth::GetData(
        				$data->token
    				  );
	$idusuario  	= $token->id;
	$user 			= array();

	//echo $idusuario; die();

$stmt = $con->prepare("SELECT  correo,usuario.cedula,nombre,nombre2,apellido,apellido2,telefonofijo,telefonocelular,direccion,idpais,idciudad,fechanacimiento,sexo,imgusuario 
						FROM usuario 
						WHERE idusuario=?");

$stmt->bind_param("i", $idusuario);

$stmt->execute();

if($stmt->error == ""){

	$stmt->bind_result($correo,$cedula,$nombre,$nombre2,$apellido,$apellido2,$telefonofijo,$telefonocelular,$direccion,$idpais,$idciudad,$fechanacimiento,$sexo,$imgusuario);

	$stmt->fetch();
	
	$user = array('correo'=>$correo,'cedula'=>$cedula,'nombres'=>utf8_encode($nombre),'apellidos'=>utf8_encode($apellido),'telefonofijo'=>utf8_encode($telefonofijo),'telefonocelular'=>$telefonocelular,'direccion'=>utf8_encode($direccion),'idpais'=>$idpais,'idciudad'=>$idciudad,'fechanacimiento'=>$fechanacimiento,'sexo'=>$sexo,'imgusuario'=>$imgusuario);

	$stmt->free_result();
	
	if(!empty($user)){
		echo json_encode(array('respuesta' => true, 'user'=>$user));
	}else{
		echo json_encode(array('respuesta' => false));
	}
}else{
	echo json_encode(array('respuesta' => false, 'mensaje' => $stmt->error));
}

?>