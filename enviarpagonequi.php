<?php
set_time_limit (1500);
include("includes/utils.php");
include("includes/nequiapi/nequiAPI.php");

$post_date  	= file_get_contents("php://input");
$data 			= json_decode($post_date);
$idservicio		= $data->idservicio;
$idcliente		= $data->idcliente;

$stmt = $con->prepare("SELECT valor,estatus,transactionid FROM servicio WHERE idservicio = ?");
$stmt->bind_param("i", $idservicio);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($valor,$estatus,$transactionid);
$stmt->fetch();

if($estatus == 2){
	$poraportegrupo				= select_config_alter("aportegrupo");
	$poraportehijos				= select_config_alter("aportehijos");
	$poraportenietos			= select_config_alter("aportenietos");
	$poraportebisnietos			= select_config_alter("aportebisnietos");
	$porservicioalter			= select_config_alter("servicioalter");
	$porgastostransaccionales	= select_config_alter("comisionnequi");	
	$poriva						= select_config_alter("iva");
	$valorgrupo            	= round($valor * $poraportegrupo);
	$servicioalter         	= $valor * $porservicioalter;
	$servicioalter 			= round($servicioalter + ($servicioalter*$poriva));
	$gastostransaccionales 	= round(($valor+$valorgrupo+$servicioalter) * $porgastostransaccionales);
	$valor 	   				= round($valor + $valorgrupo + $servicioalter + $gastostransaccionales);
	
	//Pago directo por la API
	//Buscar cuenta nequi asociada al usuario
	$stmt = $con->prepare("SELECT telefono,tokendebautomatico FROM cuenta_nequi WHERE idusuario = ? AND estatus=2");
	$stmt->bind_param("i", $idcliente);
	$stmt->execute();
	$stmt->store_result();
	$stmt->bind_result($telefono,$tokendebautomatico);
	$stmt->fetch();

	if($telefono != ""){
		//echo "Telf: $telefono || Token: $tokendebautomatico || Valor: $valor"; die();		
		$enviarPagoResponse = pagoNequiAutomatico("12345",$telefono,$valor,$tokendebautomatico);
		//var_dump($enviarPagoResponse); die();
		if($enviarPagoResponse->ResponseMessage->ResponseHeader->Status->StatusCode != 0){
			//Hubo un error con el pago de NEQUI o con la API
			echo json_encode(array('respuesta' => false, 'mensaje' => 'Error con la API de Nequi. Codigo: '.$enviarPagoResponse->ResponseMessage->ResponseHeader->Status->StatusCode));
			die();
		}else{
			$transactionid  = $enviarPagoResponse->ResponseMessage->ResponseBody->any->unregisteredPaymentRS->transactionId;
			//Cambiar token
			//$stmt = $con->prepare("UPDATE servicio SET estatus=9,transactionid='$transactionid' WHERE idservicio=?");
			///* bind parameters for markers */
			//$stmt->bind_param("i", $idservicio);
			//
			///* execute query */
			//$stmt->execute();		
			echo json_encode(array("respuesta" => true, "transactionid" => $transactionid));
			die();
		}
	}else{
		echo json_encode(array('respuesta' => false, 'mensaje' => 'El cliente no tiene cuenta nequi activa.'));
		die();
	}
}//elseif($estatus == 9){
//	echo json_encode(array("respuesta" => true, "transactionid" => $transactionid));
//	die();
//}
?>