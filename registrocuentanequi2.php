<?php
set_time_limit (900);
header('Access-Control-Allow-Origin: *');  
include("includes/nequiapi/nequiAPI.php");
include("includes.php");

$post_date  	= file_get_contents("php://input");
$data 			= json_decode($post_date);
$usuario 		= Auth::GetData(
    				$data->token
				  );
$idusuario  	= $usuario->id;
$cedula			= $data->cuenta->cedula;
$telefono		= $data->cuenta->telefono;
$nombre			= $data->cuenta->nombre;
$token			= $data->tokennequi;
$principal		= true;
$fecharegistro  = date("Y-m-d H:i:s");
$estatus 		= 2;

$validarSuscripcionResponse = validarSuscripcion($cedula,$telefono,$token);
if($validarSuscripcionResponse->ResponseMessage->ResponseHeader->Status->StatusCode == "0"){
	if($validarSuscripcionResponse->ResponseMessage->ResponseBody->any->getSubscriptionRS->subscription->status == "1"){
		//echo $token; die();
		$stmt1 = $con->prepare("INSERT INTO cuenta_nequi(idusuario, cedula, nombre, telefono, estatus, fecharegistro,tokendebautomatico) VALUES (?,?,?,?,?,?,?)");
		$stmt1->bind_param("isssiss", $idusuario,$cedula, $nombre, $telefono, $estatus, $fecharegistro,$token);
		$stmt1->execute();
		if($stmt1->error!=""){
			echo json_encode(array('respuesta' => false,'error'=>$stmt1->error,'mensaje'=>'Error en el sistema. Un administrador se pondra en contacto con usted.'));
			reporte_error($idusuario,"",$stmt1->error,"registrocuentanequi2.php",$sql);
		}
		echo json_encode(array('respuesta' => true));	
		die();	
	}elseif($validarSuscripcionResponse->ResponseMessage->ResponseBody->any->getSubscriptionRS->subscription->status == "2"){echo json_encode(array('respuesta' => false,'cancelado' => true, 'mensaje'=>'Ha cancelado la peticion.'));
		die();
	}elseif($validarSuscripcionResponse->ResponseMessage->ResponseBody->any->getSubscriptionRS->subscription->status == "0"){
		//No ha aceptado todavia meh
		die();
	}
}elseif($validarSuscripcionResponse->ResponseMessage->ResponseHeader->Status->StatusCode == "3-451"){
	echo json_encode(array('respuesta' => false, 'mensaje' => 'Usuario no existe en Nequi'));
	die();				
}//elseif($validarSuscripcionResponse->ResponseMessage->ResponseHeader->Status->StatusCode == "10-454"){
	//echo json_encode(array("respuesta" => false, "tiempo" => true, "mensaje" => "Su solicitud ha caducado."));
	//break;
//}
else{
	//11-9L	El phoneNumber, code o transactionId no existen
	//11-17L	Error de formato/parseo en alguno de los atributos del request
	//11-18L	Timeout en el componente de logica de negocio
	//11-37L	La cuenta de un usuario no existe
	//20-05A	Parametros incorrectos							
	echo json_encode(array('respuesta' => false, 'mensaje' => 'Hubo un error, intenta de nuevo mas tarde.', "validarSuscripcionResponse" => $validarSuscripcionResponse));
	//reporte_error_nequi($idusuario,$validarSuscripcionResponse->ResponseMessage->ResponseHeader->Status->StatusCode,"registrocuentanequi.php");
	die();
}

?>