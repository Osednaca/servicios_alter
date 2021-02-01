<?php
include("includes.php");

$post_date  	= file_get_contents("php://input");
$data 			= json_decode($post_date);
$token 			= Auth::GetData(
    				$data->token
				  );
$idusuario  	= $token->id;
$correo  		 = $data->user->correo;
$nombre  		 = utf8_decode($data->user->nombres);
$apellido  		 = utf8_decode($data->user->apellidos);
if(!empty($data->user->telefonofijo)){
	$telefonofijo  	 = $data->user->telefonofijo;
}else{
	$telefonofijo  	 = "";
}
if(!empty($data->user->fechanacimiento)){
	$aux 			 = explode("/", $data->user->fechanacimiento);
	$fechanacimiento = $aux[2]."-".$aux[1]."-".$aux[0];
}else{
	$fechanacimiento = null;
}
if(!empty($data->user->sexo)){
	$sexo		  	 = $data->user->sexo;
}else{
	$sexo = null;
}
$telefonocelular = $data->user->telefonocelular;
if(!empty($data->user->idpais)){
	$idpais  		 = $data->user->idpais;
}else{
	$idpais = null;
}
if(!empty($data->user->idciudad)){
	$idciudad  		 = $data->user->idciudad;
}else{
	$idciudad = null;
}
$estatus  		 = 1;
$disponibilidad  = 1;
$fecharegistro   = date("Y-m-d H:i:s");

//Validar que el email no este utilizado por otra persona
$stmt1 = $con->prepare("SELECT idusuario FROM usuario WHERE correo = ? AND idusuario<>?");

$stmt1->bind_param("si", $correo,$validausuario);

$stmt1->execute();

$stmt1->bind_result($validausuario);

$stmt1->fetch();

$stmt1->free_result();

$stmt3 = $con->prepare("SELECT imgusuario FROM usuario WHERE idusuario = ?");

$stmt3->bind_param("i",$idusuario);

$stmt3->execute();

$stmt3->bind_result($imgusuario);

$stmt3->fetch();

$stmt3->free_result();

if(!empty($correo) AND !empty($nombre) AND !empty($apellido) AND !empty($telefonocelular) AND !empty($fechanacimiento) AND !empty($sexo) AND !empty($idpais) AND !empty($idciudad) AND !empty($imgusuario)){
	$registrocompletado = true;
}else{
	$registrocompletado = false;
}

//Si no existe ninguno guarda el registro en BD
if($validausuario==""){
		$stmt2 = $con->prepare("UPDATE usuario SET correo=?, nombre=?, apellido=?, fechanacimiento=?, sexo=?, telefonofijo=?, telefonocelular=?, idpais=?, idciudad=?,estatus=?, disponibilidad=?, fechamodificacion=? WHERE idusuario=?");
		
		$stmt2->bind_param("sssssssiiiiss", $correo,$nombre,$apellido,$fechanacimiento,$sexo,$telefonofijo,$telefonocelular,$idpais,$idciudad,$estatus,$disponibilidad,$fecharegistro,$idusuario);
		
		$stmt2->execute();
	
		if($stmt2->error == ""){
			echo json_encode(array('respuesta' => true, 'registrocompleto' => $registrocompletado));
		}else{
			echo json_encode(array('respuesta' => false,'error' => $stmt2->error,'mensaje'=>'Error en el sistema. Un administrador se pondra en contacto con usted.'));
			reporte_error($idusuario,"",$stmt2->error,"modificarusuario.php",$sql);
		}
}else{
	// Sino muestra un error
	echo json_encode(array('respuesta' => false,'mensaje'=>'Error: El correo ya se encuentra registrado en la plataforma'));
}
?>