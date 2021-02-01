<?php
set_time_limit (1500);
include("includes/nequiapi/nequiAPI.php");
include("includes.php");

$post_date  	= file_get_contents("php://input");
$data 			= json_decode($post_date);
$idservicio 	= $data->servicio->idservicio;
if(isset($data->servicio->total)){
	$valor_producto  = round($data->servicio->total);
}else{
	$valor_producto  = round((int)str_replace(".", "", $data->servicio->totalaproximado));
}
if(isset($data->servicio->costodomicilio)){
	$valor_domicilio = (int)str_replace(".", "", $data->servicio->costodomicilio); 
}
$idcliente					= $data->servicio->idcliente;
$tipopago 					= $data->servicio->idtipopago;
$fechanow   				= date("Y-m-d H:i:s");
$estatus 					= 5;
$valor 						= $valor_producto;
$poraportegrupo				= select_config_alter("aportegrupo");
$porservicioalter			= select_config_alter("servicioalter");
$poriva						= select_config_alter("iva");
$poraporteprimernivel  		= select_config_alter("aportehijos");
$poraportesegundonivel 		= select_config_alter("aportenietos");
$poraportetercernivel  		= select_config_alter("aportebisnietos");
$porcomnequi 		   		= select_config_alter("comisionnequi");
$porcomnequi2 		   		= select_config_alter("comisionnequiproveedor");

//Traer dueño del negocio
$stmt = $con->prepare("SELECT idusuario,porcobroproducto,tiporegistro FROM servicio 
										INNER JOIN servicio_lugar USING(idservicio)
										INNER JOIN lugar USING(idlugar)
										INNER JOIN usuario USING(idusuario) WHERE idservicio = ?");
$stmt->bind_param("i", $idservicio);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($idnegocioproveedor,$porcobroproducto,$tiporegistrolugar);
$stmt->fetch();

if($tipopago == 1){
	$pagocompletado = false;
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
	//Valor = Valor Cliente
	$valorcliente = round($valor_producto + $valor_domicilio + ($valor_producto * $porcobroproducto));	
	$stmt = $con->prepare("UPDATE usuario SET saldoalter=saldoalter-$valorcliente WHERE idusuario=?");
	$stmt->bind_param("i", $idcliente);
	$stmt->execute();
	$stmt->free_result();

	$stmt1 		= $con->prepare("UPDATE usuario SET saldoalter=saldoalter+$valor_producto WHERE idusuario=?");
	$stmt1->bind_param("i", $idnegocioproveedor);
	$stmt1->execute();
	$stmt1->free_result();	
	$pagocompletado = true;
}elseif($tipopago == 4){
	//Valor = Valor Base
	$valorgrupo            		= $valor_domicilio * $poraportegrupo;
	$servicioalter         		= $valor_domicilio * $porservicioalter;
	$servicioalter 				= $servicioalter + ($servicioalter*$poriva);
	$gastostransaccionales 		= ($valor_domicilio+$valorgrupo+$servicioalter) * $porcomnequi;
	$valoragregadoproducto 		= $valor_producto*$porcobroproducto;
	$valorefectivo				= round(($valorgrupo + $servicioalter + $gastostransaccionales+$valoragregadoproducto)*2);
	$stmt = $con->prepare("UPDATE usuario SET saldoalter=saldoalter-$valorefectivo WHERE idusuario=?");
	$stmt->bind_param("i", $idusuario);
	$stmt->execute();
	$stmt->free_result();
	$pagocompletado = true;
}

$stmt = $con->prepare("SELECT idproveedor FROM servicio WHERE idservicio=?");
/* bind parameters for markers */
$stmt->bind_param("i", $idservicio);
/* execute query */
$stmt->execute();

$stmt->bind_result($idproveedor);

$stmt->fetch();

$stmt->free_result();

if($pagocompletado){
	$stmt = $con->prepare("UPDATE servicio SET estatus=?,fechaculminacion=? WHERE idservicio=?");
	$stmt->bind_param("isi", $estatus, $fechanow, $idservicio);
	$stmt->execute();

	//guardar la transaccion 
	$comalter        	   = $valor_domicilio*$porservicioalter;
	$ivacomalter     	   = $comalter*$poriva;
	$comalter 			   = $comalter + $ivacomalter;
	$comprimernivel  	   = $valor_domicilio*$poraporteprimernivel; 
	$comsegundonivel 	   = $valor_domicilio*$poraportesegundonivel;
	$comtercernivel  	   = $valor_domicilio*$poraportetercernivel;
	$valoragregadoproducto = $valor_producto*$porcobroproducto;
	$valoragregadoproducto = $valoragregadoproducto*0.5;

	$cidprimernivel  = cedulaNivel1($idcliente);
	$cidsegundonivel = cedulaNivel2($idcliente);
	$cidtercernivel  = cedulaNivel3($idcliente);	

	if($tiporegistrolugar == 1){
		$pidprimernivel  = cedulaNivel1($idproveedor);
		$pidsegundonivel = cedulaNivel2($idproveedor);
		$pidtercernivel  = cedulaNivel3($idproveedor);

		if($tipopago == 1){
			//Nequi
			$vrnequi  		   = ($valor_domicilio+$comalter+$comprimernivel+$comsegundonivel+$comtercernivel)*$porcomnequi;
			$totalcliente      = ($valor_domicilio+$comalter+$comprimernivel+$comsegundonivel+$comtercernivel+$vrnequi+$valoragregadoproducto);
			$comnequi 		   = $totalcliente*$porcomnequi2;
			$totalproveedor    = ($valor_domicilio-$comalter-$comprimernivel-$comsegundonivel-$comtercernivel+$valoragregadoproducto);
		}elseif($tipopago == 2 OR $tipopago == 3 OR $tipopago == 4){
			$vrnequi  		   = 0;
			$totalcliente      = ($valor_domicilio+$comalter+$comprimernivel+$comsegundonivel+$comtercernivel+$vrnequi+$valoragregadoproducto);
			$comnequi 		   = 0;
			$totalproveedor    = ($valor_domicilio-$comalter-$comprimernivel-$comsegundonivel-$comtercernivel+$valoragregadoproducto);
		}

		$stmt = $con->prepare("INSERT INTO transacciones(idservicio, comalter, ivacomalter, ccomprimernivel, cidprimernivel, ccomsegundonivel, cidsegundonivel, ccomtercernivel, cidtercernivel, pcomalter, pivacomalter, pcomprimernivel, pidprimernivel, pcomsegundonivel, pidsegundonivel, pcomtercernivel, pidtercernivel, vrnequi, fechatransaccion,comnequi,totalcliente,totalproveedor) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
		$stmt->bind_param("isssssssssssssssssssss", $idservicio, $comalter, $ivacomalter, $comprimernivel, $cidprimernivel, $comsegundonivel, $cidsegundonivel, $comtercernivel, $cidtercernivel, $comalter, $ivacomalter, $comprimernivel, $pidprimernivel, $comsegundonivel, $pidsegundonivel, $comtercernivel, $pidtercernivel, $vrnequi, $fechanow,$comnequi,$totalcliente,$totalproveedor);		
	}elseif ($tiporegistrolugar == 2) {
		$pidprimernivel  = NULL;
		$pidsegundonivel = NULL;
		$pidtercernivel  = NULL;
		//$pcomalter 	     = 0;
		//$pivacomalter    = 0;
		$pcomprimernivel = 0;
		$pcomsegundonivel= 0;
		$pcomtercernivel = 0;

		if($tipopago == 1){
			//Nequi
			$vrnequi  		   = ($valor_domicilio+$comalter+$comprimernivel+$comsegundonivel+$comtercernivel)*$porcomnequi;
			$totalcliente      = ($valor_domicilio+$comalter+$comprimernivel+$comsegundonivel+$comtercernivel+$vrnequi+$valoragregadoproducto);
			$comnequi 		   = $totalcliente*$porcomnequi2;
			$totalproveedor    = ($valor_domicilio-$comalter-$comprimernivel-$comsegundonivel-$comtercernivel+$valoragregadoproducto);
		}elseif($tipopago == 2 OR $tipopago == 3 OR $tipopago == 4){
			$vrnequi  		   = 0;
			$totalcliente      = ($valor_domicilio+$comalter+$comprimernivel+$comsegundonivel+$comtercernivel+$vrnequi+$valoragregadoproducto);
			$comnequi 		   = 0;
			$totalproveedor    = ($valor_domicilio-$comalter-$comprimernivel-$comsegundonivel-$comtercernivel+$valoragregadoproducto);
		}

		$stmt = $con->prepare("INSERT INTO transacciones(idservicio, comalter, ivacomalter, ccomprimernivel, cidprimernivel, ccomsegundonivel, cidsegundonivel, ccomtercernivel, cidtercernivel, pcomalter, pivacomalter, pcomprimernivel, pidprimernivel, pcomsegundonivel, pidsegundonivel, pcomtercernivel, pidtercernivel, vrnequi, fechatransaccion,comnequi,totalcliente,totalproveedor) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
		$stmt->bind_param("isssssssssssssssssssss", $idservicio, $comalter, $ivacomalter, $comprimernivel, $cidprimernivel, $comsegundonivel, $cidsegundonivel, $comtercernivel, $cidtercernivel, $comalter, $ivacomalter, $pcomprimernivel, $pidprimernivel, $pcomsegundonivel, $pidsegundonivel, $pcomtercernivel, $pidtercernivel, $vrnequi, $fechanow,$comnequi,$totalcliente,$totalproveedor);
	}

	$stmt->execute();	

	if($stmt->error == ""){
		$stmt->free_result();		
		echo json_encode(array('respuesta' => true,'idproveedor' => $idproveedor));
	}else{
		echo json_encode(array('respuesta' => false, 'error' => $stmt->error));
	}
}	

?>