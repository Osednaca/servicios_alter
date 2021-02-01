<?php
	include("includes.php");

	$post_date  		= file_get_contents("php://input");
	$data 				= json_decode($post_date);
	$token 				= Auth::GetData(
        					$data->token
    				  	);
	$idusuario  		= $token->id;
	$misenvios 			= array();
	$enviosrecibidos	= array();
	$serviciosefectivo 	= array();
	$filtros 			= "";
	$filtros2 			= "";
	$tipostring     	= "";
	if(!empty($data->desde)){
		$filtros 	   	.= 	" AND DATE(fechaenvio) >= ?";
		$filtros2 	   	.= 	" AND DATE(fecharegistro) >= ?";
		$aux 			= explode("/",$data->desde);
		$desde 			= $aux[2]."-".$aux[1]."-".$aux[0];
		$a_params[] 	= & $desde;
		$tipostring		.= 	"s";
	}

	if(!empty($data->hasta)){
		$filtros 	   	.= 	" AND DATE(fechaenvio) <= ?";
		$filtros2 	   	.= 	" AND DATE(fecharegistro) <= ?";
		$aux 			= explode("/",$data->hasta);
		$hasta 			= $aux[2]."-".$aux[1]."-".$aux[0];
		$a_params[] 	= & $hasta;
		$tipostring		.= 	"s";
	}
	array_unshift($a_params,$tipostring);

	//Buscar cedula
	$stmt = $con->prepare("SELECT cedula FROM usuario WHERE idusuario=?");
	$stmt->bind_param("i",$idusuario);
	$stmt->execute();
	/* bind result variables */
	$stmt->bind_result($cedulau);
	$stmt->fetch();
	$stmt->free_result();
    $stmt->close();
	//Mis Envios 
	$stmt2 = $con->prepare("SELECT cedula, valor, DATE(fechaenvio) FROM enviodedinero WHERE idusuario=$idusuario AND estatus = 1 $filtros");
	call_user_func_array(array($stmt2, 'bind_param'), $a_params);
	$stmt2->execute();
	if($stmt2->error == ""){
		/* bind result variables */
		$stmt2->bind_result($cedula, $valor, $fechaenvio);
		while ($stmt2->fetch()) {
			$misenvios[] = array('cedula'=>$cedula, 'valor' => $valor, 'fechaenvio' => $fechaenvio);
		}
    	$stmt2->close();
	}else{
		echo json_encode(array('respuesta' => false, 'mensaje' => $stmt2->error));
		die;
	}

	//Envios Recibidos
	$sql = "SELECT cedula, valor, DATE(fechaenvio) FROM enviodedinero WHERE cedula=$cedulau AND estatus = 1 $filtros";
	$stmt3 = $con->prepare($sql);
	call_user_func_array(array($stmt3, 'bind_param'), $a_params);
	$stmt3->execute();
	if($stmt3->error == ""){
		/* bind result variables */
		$stmt3->bind_result($cedula, $valor, $fechaenvio);
		while ($stmt3->fetch()) {
			$enviosrecibidos[] = array('cedula'=>$cedula, 'valor' => $valor, 'fechaenvio' => $fechaenvio);
		}
	}else{
		echo json_encode(array('respuesta' => false, 'mensaje' => $stmt3->error));
		die;
	}

	//Servicios en Efectivo
	$sql = "SELECT (SELECT cedula FROM usuario WHERE idusuario = $idusuario),(SELECT cedula FROM usuario WHERE idusuario = idcliente),(SELECT cedula FROM usuario WHERE idusuario = idproveedor),valor, DATE(fecharegistro) FROM servicio WHERE (idcliente = $idusuario OR idproveedor = $idusuario) AND idtipopago=4 AND estatus IN(6,9) AND valor > 0 $filtros2";
	$stmt4 = $con->prepare($sql);
	call_user_func_array(array($stmt4, 'bind_param'), $a_params);
	$stmt4->execute();
	if($stmt4->error == ""){
		$stmt4->store_result();
		/* bind result variables */
		$stmt4->bind_result($cedula, $cedulacliente, $cedulaproveedor, $valor, $fechaenvio);
		while ($stmt4->fetch()) {
			if($cedula == $cedulacliente){
				$cedula2 = $cedulaproveedor;
			}elseif($cedula == $cedulaproveedor){
				$cedula2 = $cedulacliente;
			}
			$poraportegrupo				= select_config_alter("aportegrupo");
			$porservicioalter			= select_config_alter("servicioalter");
			$porgastostransaccionales	= select_config_alter("comisionnequi");	
			$poriva						= select_config_alter("iva");
			$valorgrupo            		= $valor * $poraportegrupo;
			$servicioalter         		= $valor * $porservicioalter;
			$servicioalter 				= $servicioalter + ($servicioalter*$poriva);
			$gastostransaccionales 		= ($valor+$valorgrupo+$servicioalter) * $porgastostransaccionales;
			$valor2 	   					= round(($valorgrupo + $servicioalter + $gastostransaccionales)*2);					
			$serviciosefectivo[] = array('cedula'=>$cedula2, 'valor' => $valor2, 'fechaenvio' => $fechaenvio);
		}
	}else{
		echo json_encode(array('respuesta' => false, 'mensaje' => $stmt3->error));
		die;
	}	

	echo json_encode(array('respuesta' => true, 'misenvios'=>$misenvios,'enviosrecibidos'=>$enviosrecibidos,'serviciosefectivo'=>$serviciosefectivo));
?>