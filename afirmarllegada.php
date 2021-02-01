<?php
set_time_limit (1500);
include("includes.php");
include("includes/nequiapi/nequiAPI.php");

$post_date  	   = file_get_contents("php://input");
$data 			   = json_decode($post_date);
$token 			   = Auth::GetData(
    				$data->token
				  );
$idusuario  	   = $token->id;
$idservicio		   = $data->idservicio;
$fechallego 	   = date("Y-m-d H:i:s");
$valor 			   = round($data->valor);
if(!empty($data->tiporegistrolugar)){
	$tiporegistrolugar = $data->tiporegistrolugar;
	if($tiporegistrolugar == 1){
		$costoproducto     = round($data->costoproducto);
	}	
}
$idcliente		   = $data->idcliente;
$estatus 		   = $data->estatus;
$tipopago 		   = $data->tipopago;

if($tipopago == 1){
	$pagocompletado = false;
	if(!empty($tiporegistrolugar)){
		if($tiporegistrolugar == 1){
			$stmt = $con->prepare("SELECT idusuario,porcobroproducto FROM servicio 
											INNER JOIN servicio_lugar USING(idservicio)
											INNER JOIN lugar USING(idlugar)
											INNER JOIN usuario USING(idusuario) WHERE idservicio = ?");
			$stmt->bind_param("i", $idservicio);
			$stmt->execute();
			$stmt->store_result();
			$stmt->bind_result($idnegocioproveedor,$porcobroproducto);
			$stmt->fetch();
			$valor = $valor + $costoproducto + ($costoproducto * $porcobroproducto);
		}	
	}
	//Buscar si el usuario tiene activada una promocion
	$stmt = $con->prepare("SELECT promo FROM usuario WHERE idusuario = ?");
	$stmt->bind_param("i", $idcliente);
	$stmt->execute();
	$stmt->store_result();
	$stmt->bind_result($promo);
	$stmt->fetch();
	if($promo == 0)	{
		//Buscar cuenta nequi de Oscar
		$stmt = $con->prepare("SELECT idcuentanequi,telefono,tokendebautomatico FROM cuenta_nequi WHERE idusuario = 32 AND estatus=2");
		$stmt->execute();
		$stmt->store_result();
		$stmt->bind_result($idcuentanequi,$telefono,$tokendebautomatico);
		$stmt->fetch();			
		$pagonequiResponse = pagoNequiAutomatico("12345",$telefono,"$valor",$tokendebautomatico);
		if($pagonequiResponse->ResponseMessage->ResponseHeader->Status->StatusCode == 0){
			$pagocompletado = true;
			//var_dump($pagonequiResponse); die();
			$referencia = $pagonequiResponse->ResponseMessage->ResponseHeader->MessageID;
			$nuevotoken = $pagonequiResponse->ResponseMessage->ResponseBody->any->automaticPaymentRS->token;
			$stmt = $con->prepare("UPDATE cuenta_nequi SET tokendebautomatico=? WHERE idcuentanequi=?");
			$stmt->bind_param("si",$nuevotoken , $idcuentanequi);	
			$stmt->execute();

			
			$stmt->free_result();
			//Marcar como comsumida la promocion
			$stmt = $con->prepare("UPDATE usuario SET promo=1 WHERE idusuario=?");
			$stmt->bind_param("i",$idcliente);	
			$stmt->execute();
			$stmt->free_result();				
			//Guardar la referencia de la transaccion
			$stmt = $con->prepare("UPDATE servicio SET referencianequi = ? WHERE idservicio=?");
			$stmt->bind_param("si",$referencia,$idservicio);	
			$stmt->execute();
			$stmt->free_result();			
		}
		else{
			//11-9L		El phoneNumber, code o transactionId no existen
			//11-17L	Error de formato/parseo en alguno de los atributos del request
			//11-18L	Timeout en el componente de logica de negocio
			//11-37L	La cuenta de un usuario no existe
			//20-05A	Cuando se hace una petición pero en el body vienen parametros incorrectos
			//20-07A	Error técnico 
			if($pagonequiResponse->ResponseMessage->ResponseHeader->Status->StatusCode == "20-07A"){
				echo json_encode(array('respuesta' => false, 'mensaje' => 'Oops hubo un inconveniente el servicio se va a cancelar', 'nosaldo' => true));			
				die();
			}else{
				echo json_encode(array('respuesta' => false, 'mensaje' => 'Oops hubo un error, vuelve a intentarlo mas tarde.', 'nequiResponse' => $pagonequiResponse));			
				die();			
			}
		}		
	}elseif($promo == 1 OR $promo == 2){
		//Buscar cuenta nequi asociada al usuario
		$stmt = $con->prepare("SELECT idcuentanequi,telefono,tokendebautomatico FROM cuenta_nequi WHERE idusuario = ? AND estatus=2");
		$stmt->bind_param("i", $idcliente);
		$stmt->execute();
		$stmt->store_result();
		$stmt->bind_result($idcuentanequi,$telefono,$tokendebautomatico);
		$stmt->fetch();	
		//echo $telefono." || ".$valor." || ".$tokendebautomatico; die();
		$pagonequiResponse = pagoNequiAutomatico("12345",$telefono,"$valor",$tokendebautomatico);
		//var_dump($pagonequiResponse); die();
		if($pagonequiResponse->ResponseMessage->ResponseHeader->Status->StatusCode == 0){
			$pagocompletado = true;
			$referencia = $pagonequiResponse->ResponseMessage->ResponseHeader->MessageID;
			$nuevotoken = $pagonequiResponse->ResponseMessage->ResponseBody->any->automaticPaymentRS->token;
			$stmt = $con->prepare("UPDATE cuenta_nequi SET tokendebautomatico=? WHERE idcuentanequi=?");
			$stmt->bind_param("si",$nuevotoken , $idcuentanequi);	
			$stmt->execute();
			$stmt->free_result();	
			//Guardar la referencia de la transaccion
			$stmt = $con->prepare("UPDATE servicio SET referencianequi = ? WHERE idservicio=?");
			$stmt->bind_param("si",$referencia,$idservicio);	
			$stmt->execute();
			$stmt->free_result();
		}
		else{
			//11-9L		El phoneNumber, code o transactionId no existen
			//11-17L	Error de formato/parseo en alguno de los atributos del request
			//11-18L	Timeout en el componente de logica de negocio
			//11-37L	La cuenta de un usuario no existe
			//20-05A	Cuando se hace una petición pero en el body vienen parametros incorrectos
			//20-07A	Error técnico 
			if($pagonequiResponse->ResponseMessage->ResponseHeader->Status->StatusCode == "20-07A"){
				echo json_encode(array('respuesta' => false, 'mensaje' => 'Oops hubo un inconveniente el servicio se va a cancelar', 'nosaldo' => true));			
				die();
			}else{
				echo json_encode(array('respuesta' => false, 'mensaje' => 'Oops hubo un error, vuelve a intentarlo mas tarde.', 'nequiResponse' => $pagonequiResponse));			
				die();			
			}
		}
	}
}elseif($tipopago == 3){
	$poraportegrupo				= select_config_alter("aportegrupo");
	$porservicioalter			= select_config_alter("servicioalter");
	$porgastostransaccionales	= select_config_alter("comisionnequi");	
	$poriva						= select_config_alter("iva");
	$valorgrupo            		= $valor * $poraportegrupo;
	$servicioalter         		= $valor * $porservicioalter;
	$servicioalter 				= $servicioalter + ($servicioalter*$poriva);
	$gastostransaccionales 		= ($valor+$valorgrupo+$servicioalter) * $porgastostransaccionales;
	$valor    					= round($valor+$valorgrupo + $servicioalter + $gastostransaccionales);
	if(!empty($tiporegistrolugar)){
		if($tiporegistrolugar == 1){
			$stmt = $con->prepare("SELECT idusuario,porcobroproducto FROM servicio 
											INNER JOIN servicio_lugar USING(idservicio)
											INNER JOIN lugar USING(idlugar)
											INNER JOIN usuario USING(idusuario) WHERE idservicio = ?");
			$stmt->bind_param("i", $idservicio);
			$stmt->execute();
			$stmt->store_result();
			$stmt->bind_result($idnegocioproveedor,$porcobroproducto);
			$stmt->fetch();
			$valor = $valor + $costoproducto + ($costoproducto * $porcobroproducto);
			$stmt  = $con->prepare("UPDATE usuario SET saldoalter=saldoalter+$costoproducto WHERE idusuario=?");
			$stmt->bind_param("i", $idnegocioproveedor);
			$stmt->execute();
			$stmt->free_result();
		}
	}
	//Valor = Valor Cliente
	//echo "Valor: ".$valor." || Costo Producto: ".$costoproducto;
	$stmt = $con->prepare("UPDATE usuario SET saldoalter=saldoalter-$valor WHERE idusuario=?");
	$stmt->bind_param("i", $idcliente);
	$stmt->execute();
	$stmt->free_result();
	$pagocompletado = true;
}elseif($tipopago == 4){
	//Valor = Valor Base
	$poraportegrupo				= select_config_alter("aportegrupo");
	$porservicioalter			= select_config_alter("servicioalter");
	$porgastostransaccionales	= select_config_alter("comisionnequi");	
	$poriva						= select_config_alter("iva");
	$valorgrupo            		= $valor * $poraportegrupo;
	$servicioalter         		= $valor * $porservicioalter;
	$servicioalter 				= $servicioalter + ($servicioalter*$poriva);
	$gastostransaccionales 		= ($valor+$valorgrupo+$servicioalter) * $porgastostransaccionales;
	$valor 	   					= round(($valorgrupo + $servicioalter + $gastostransaccionales)*2);
	$stmt = $con->prepare("UPDATE usuario SET saldoalter=saldoalter-$valor WHERE idusuario=?");
	$stmt->bind_param("i", $idusuario);
	$stmt->execute();
	$stmt->free_result();
	$pagocompletado = true;
}


if($pagocompletado){
	//Cambiar estatus del servicio y guardar la fecha de llegada
	if($estatus != 4){
		$estatus = 3;
	}
	$stmt = $con->prepare("UPDATE servicio SET estatus=?,fechallego=? WHERE idservicio=?");
	$stmt->bind_param("isi", $estatus, $fechallego, $idservicio);
	$stmt->execute();
	if($stmt->error == ""){
		if($stmt->affected_rows > 0){
			echo json_encode(array('respuesta' => true));
			die();
		}else{
			echo json_encode(array('respuesta' => false, 'mensaje' => 'El servicio no existe.'));
			die();
		}
	}else{
		echo json_encode(array('respuesta' => false, 'error' => $stmt2->error, 'mensaje'=>''));
		die();
	}
}else{
	echo json_encode(array('respuesta' => false, 'mensaje'=>''));
}
?>