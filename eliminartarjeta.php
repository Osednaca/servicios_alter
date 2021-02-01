<?php
include("includes/utils.php");
include("lib/nusoap.php");
session_start();
$post_date  = file_get_contents("php://input");
$data 		= json_decode($post_date);
$idusuario  = $_SESSION["idusuario"];
$idtarjeta  = $data->idtarjeta;

$stmt = $con->prepare("SELECT idalmacenelp FROM tarjetasusuario WHERE idtarjetausuario=?");
$stmt->bind_param("i",$idtarjeta);
$stmt->execute();
$stmt->bind_result($idalmacenelp);
$stmt->fetch();

//Eliminar_Almacenamiento
$wsdl	= "https://www.enlineapagos.com/secure/webservices/Almacenamiento.do?wsdl"; 
$client = new nusoap_client($wsdl, true);
$Params = array('usuario'=>'FINESXPR','clave'=>'124830','llavemd5'=>'f0e377101fb213fa60f0ea081383ab48','id_almacenamiento'=>$idalmacenelp);

$response=$client->call('Eliminar_Almacenamiento', $Params); 
$stmt->free_result();

if($response["respuesta"]=="ok"){
	$stmt1 = $con->prepare("DELETE FROM tarjetasusuario WHERE idtarjetausuario = ?");
	$stmt1->bind_param("i",$idtarjeta);
	$stmt1->execute();
	
	if($stmt->error == ""){
		echo json_encode(array('respuesta' => true));
	}else{
		echo json_encode(array('respuesta' => false, 'mensaje' => $stmt->error));
	}
}elseif($response["respuesta"]=="error"){
	echo json_encode(array('respuesta' => false, 'mensaje' => utf8_encode($response["errorsms"])));
}
?>