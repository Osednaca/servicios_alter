<?php
set_time_limit (1500);
include("includes.php");
include("includes/nequiapi/nequiAPI.php");
/* Tipos de Pago
1 = Nequi
2 = Tarjeta e Credito
3 = Saldo Alter
4 = Efectivo
*/

$post_date  	= file_get_contents("php://input");
$data 			= json_decode($post_date);
$idservicio		= $data->idservicio;
$stmt = $con->prepare("SELECT idcliente,cuenta_nequi.telefono,idtipopago,valor,referencianequi FROM servicio LEFT JOIN cuenta_nequi ON(idusuario = idcliente) WHERE idservicio = ?");
$stmt->bind_param("i", $idservicio);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($idcliente,$telefono,$tipopago,$valor,$messageid);
$stmt->fetch();
//echo $tipopago; die();
$poraportegrupo				= select_config_alter("aportegrupo");
$porservicioalter			= select_config_alter("servicioalter");
if($tipopago == 4){
	$porgastostransaccion	= select_config_alter("comisionnequi");
}else{
	$porgastostransaccion	= select_config_alter("comisionnequiproveedor");
}
$poriva						= select_config_alter("iva");
//Valor = Valor Base
$valorgrupo            		= $valor * $poraportegrupo;
$servicioalter         		= $valor * $porservicioalter;
$servicioalter 				= $servicioalter + ($servicioalter*$poriva);
$gastostransaccionales 		= ($valor+$valorgrupo+$servicioalter) * $porgastostransaccion;
$valorcliente   		    = round($valor + $valorgrupo + $servicioalter + $gastostransaccionales);	

if($tipopago == 1){
	$pagocompletado 		   = false;
	//echo "IDCliente $idcliente Telefono: ".$telefono." Valor: ".$valorcliente." IDReferencia: ".$messageid; die();
	$cancelarpagonequiResponse = cancelarTransaccion($idcliente,$telefono,(string)$valorcliente,$messageid);
	if($cancelarpagonequiResponse->ResponseMessage->ResponseHeader->Status->StatusCode == 0){
		$pagocompletado = true;
	}
	else{
		//11-9L		El phoneNumber, code o transactionId no existen
		//11-17L	Error de formato/parseo en alguno de los atributos del request
		//11-18L	Timeout en el componente de logica de negocio
		//11-37L	La cuenta de un usuario no existe
		//20-05A	Cuando se hace una petición pero en el body vienen parametros incorrectos
		//20-07A	Error técnico 
		if($cancelarpagonequiResponse->ResponseMessage->ResponseHeader->Status->StatusCode == "20-07A"){
			echo json_encode(array('respuesta' => false, 'mensaje' => 'Oops hubo un error al devolver la plata.','nequiresponse' => $cancelarpagonequiResponse));
			die();
		}else{
			echo json_encode(array('respuesta' => false, 'mensaje' => 'Oops hubo un error, vuelve a intentarlo mas tarde.', 'nequiResponse' => $cancelarpagonequiResponse));			
			die();			
		}
	}		
}elseif($tipopago == 3){
	//Valor = Valor Cliente
	$stmt = $con->prepare("UPDATE usuario SET saldoalter=saldoalter+$valorcliente WHERE idusuario=?");
	$stmt->bind_param("i", $idcliente);
	$stmt->execute();
	$stmt->free_result();
	$pagocompletado = true;
}elseif($tipopago == 4){
	$valor 	   					= round(($valorgrupo + $servicioalter + $gastostransaccionales)*2);
	$stmt = $con->prepare("UPDATE usuario SET saldoalter=saldoalter+$valor WHERE idusuario=?");
	$stmt->bind_param("i", $idcliente);
	$stmt->execute();
	$stmt->free_result();
	$pagocompletado = true;
}


if($pagocompletado){
	echo json_encode(array('respuesta' => true));
	die();
}else{
	echo json_encode(array('respuesta' => false, 'mensaje'=>''));
}
?>