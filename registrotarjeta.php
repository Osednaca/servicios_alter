<?php
header('Access-Control-Allow-Origin: *');  
include("includes/utils.php");
include("lib/nusoap.php");
session_start();

$post_date  	 		= file_get_contents("php://input");
$data 			 		= json_decode($post_date);

$idusuario				= $_SESSION["idusuario"];
$numerotarjeta   		= $data->numerotarjeta;
$ultimoscuatro			= substr($numerotarjeta, strlen($numerotarjeta)-4, 4);
$franquicia				= $data->franquicia;
$tcmesexpiracion 		= $data->mes;
$tcanoexpiracion 		= (int)$data->anio;
$cvv 					= (int)$data->cvv;
$principal				= $data->principal;
$fecharegistro   		= date("Y-m-d H:i:s");
if($principal){
	$estatus			= 2;
	$stmt1 = $con->prepare("UPDATE tarjetasusuario SET estatus = 1 WHERE idusuario = ?");
	$stmt1->bind_param("i",$idusuario);
	$stmt1->execute();
}else{
	$estatus 			= 1;
	//Validar si no tiene tarjeta principal, hacer de esta la principal
	$stmt = $con->prepare("SELECT idtarjetausuario FROM tarjetasusuario WHERE estatus = 2 AND idusuario = ?");
	$stmt->bind_param("s", $idusuario);
	$stmt->execute();
	$stmt->bind_result($validar);
	$stmt->fetch();
	$stmt->free_result();
	if($validar == ""){
		$estatus = 2;
	}
}

//Validar que la tarjeta no este registrada ya
$stmt = $con->prepare("SELECT idtarjetausuario FROM tarjetasusuario WHERE ultimoscuatro = ?");
$stmt->bind_param("s", $ultimoscuatro);
$stmt->execute();
$stmt->bind_result($idtarjetausuario);
$stmt->fetch();
$stmt->free_result();

if(empty($idtarjetausuario)){
	$wsdl	= "https://www.enlineapagos.com/secure/webservices/Almacenamiento.do?wsdl"; 
	$client = new nusoap_client($wsdl, true);
	$Params = array('usuario'=>'FINESXPR','clave'=>'124830','llavemd5'=>'f0e377101fb213fa60f0ea081383ab48','tctipo'=>$franquicia,'tarjetanumero'=>(int)	$numerotarjeta,'cvv2'=>$cvv,'tcmesexpiracion'=>$tcmesexpiracion,'tcanoexpiracion'=>$tcanoexpiracion);
	//var_dump($Params); die();
	$response=$client->call('Crear_Almacenamiento', $Params); 
	if($response["respuesta"] == "ok"){
		$idalmacenelp = $response["id_almacenamiento"];
		$tokenelp 	  = $response["token"];
		$stmt1 		  = $con->prepare("INSERT INTO tarjetasusuario(idusuario, tokenelp, idalmacenelp, ultimoscuatro, franquicia, fecharegistrotarjeta, 	estatus) VALUES 	(?,?,?,?,?,?,?)");
		
		$stmt1->bind_param("isssssi", $idusuario,$tokenelp,$idalmacenelp,$ultimoscuatro,$franquicia,$fecharegistro,$estatus);
		
		$stmt1->execute();
		
		if($stmt1->error==""){
			echo json_encode(array('respuesta' => true));			
		}else{
			echo json_encode(array('respuesta' => false,'error'=>$stmt->error));
		}
	}else{
		echo json_encode(array('respuesta' => false,'error'=>"Hubo un error registrando la tarjeta."));
	}
}else{
	echo json_encode(array('respuesta' => false,'error'=>"La tarjeta ya se encuentra registrada"));
}

?>