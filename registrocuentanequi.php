<?php
header('Access-Control-Allow-Origin: *');  
include("includes/nequiapi/nequiAPI.php");
include("includes.php");

$post_date  	= file_get_contents("php://input");
$data 			= json_decode($post_date);
$token 			= Auth::GetData(
    				$data->token
				  );
$idusuario  	= $token->id;
$telefono		= $data->cuenta->telefono;
$cedula			= $data->cuenta->cedula;
$nombre			= $data->cuenta->nombre;
$principal		= true;
$fecharegistro  = date("Y-m-d H:i:s");

//Validar que el usuario ya no tenga cuenta registra por ahora una sola cuenta nequi por usuario
$stmt = $con->prepare("SELECT count(idcuentanequi) FROM cuenta_nequi WHERE idusuario = ?");
$stmt->bind_param("i", $idusuario);
$stmt->execute();
$stmt->bind_result($ncuentanequi);
$stmt->fetch();
$stmt->free_result();

if($ncuentanequi == 0){
	//Validar que la cuenta no este registrada ya
	$stmt = $con->prepare("SELECT idcuentanequi FROM cuenta_nequi WHERE telefono = ?");
	$stmt->bind_param("s", $telefono);
	$stmt->execute();
	$stmt->bind_result($idcuentanequi);
	$stmt->fetch();
	$stmt->free_result();
	
	if(empty($idcuentanequi)){
		if($principal){
			$estatus			= 2;
			$stmt1 = $con->prepare("UPDATE cuenta_nequi SET estatus = 1 WHERE idusuario = ?");
			$stmt1->bind_param("i",$idusuario);
			$stmt1->execute();
		}else{
			$estatus 			= 1;
			//Validar si no tiene tarjeta principal, hacer de esta la principal
			$stmt = $con->prepare("SELECT idcuentanequi FROM cuenta_nequi WHERE estatus = 2 AND idusuario = ?");
			$stmt->bind_param("s", $idusuario);
			$stmt->execute();
			$stmt->bind_result($validar);
			$stmt->fetch();
			$stmt->free_result();
			if($validar == ""){
				$estatus = 2;
			}
		}
	
		$nuevasuscripcionResponse = nuevaSuscripcion($cedula,$telefono);
		if($nuevasuscripcionResponse->ResponseMessage->ResponseHeader->Status->StatusCode == "0"){
			$token = $nuevasuscripcionResponse->ResponseMessage->ResponseBody->any->newSubscriptionRS
->token;
			echo json_encode(array("respuesta" => true, "token" => $token));
		}elseif($nuevasuscripcionResponse->ResponseMessage->ResponseHeader->Status->StatusCode == "3-451"){
			echo json_encode(array('respuesta' => false, 'mensaje' => 'Usuario no existe en Nequi'));
			die();
		}else{
			//	Cliente o usuario no encontrado en base de datos
			//11-9L	El phoneNumber, code o transactionId no existen
			//11-17L	Error de formato/parseo en alguno de los atributos del request
			//11-18L	Timeout en el componente de logica de negocio
			//11-37L	La cuenta de un usuario no existe
			//20-05A	Parametros incorrectos			
			echo json_encode(array('respuesta' => false, 'mensaje' => 'Hubo un error, intenta de nuevo mas tarde.', "nuevasuscripcionResponse" => $nuevasuscripcionResponse));
			//reporte_error_nequi($idusuario,$nuevasuscripcionResponse->ResponseMessage->ResponseHeader->Status->StatusCode,"registrocuentanequi.php");
		}
	}else{
		echo json_encode(array('respuesta' => false,'mensaje'=>"La cuenta ya se encuentra registrada"));
	}
}else{
	echo json_encode(array('respuesta' => false,'mensaje'=>"Ya tiene una cuenta nequi registrada."));
}

?>