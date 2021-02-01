<?php

include("includes.php");
$post_date  			= file_get_contents("php://input");
$data 					= json_decode($post_date);
$token 					= Auth::GetData(
     						$data->token
 						  );
$idcliente  			= $token->id;
$idtiposervicio			= $data->servicio->idtiposervicio;
$idtipovehiculo			= $data->servicio->idtipovehiculo;
$estatus				= 1;
$idlugar				= $data->servicio->idlugar;
$puntoentrega   		= $data->servicio->puntoentrega;
$puntoentregalat 		= $data->servicio->lat;
$puntoentregalng 		= $data->servicio->lng;
if(!empty($data->servicio->indicacionpe)){
	$indicacionpe		= $data->servicio->indicacionpe;
}else{
	$indicacionpe 		= "";
}
$costodomicilio      	= $data->servicio->costodomicilio;
$costoproductos			= $data->servicio->totalproductos;
$tipopago				= $data->servicio->tipopago;
$fecharegistro   		= date("Y-m-d H:i:s");

$porservicioalter		= select_config_alter("servicioalter");
$poriva					= select_config_alter("iva");
$poraporteprimernivel  	= select_config_alter("aportehijos");
$poraportesegundonivel 	= select_config_alter("aportenietos");
$poraportetercernivel  	= select_config_alter("aportebisnietos");
$porcomnequi 		   	= select_config_alter("comisionnequi");

$comalter        	   	= $costodomicilio*$porservicioalter;
$ivacomalter     	   	= $comalter*$poriva;
$comalter 			   	= $comalter + $ivacomalter;
$comprimernivel  	   	= $costodomicilio*$poraporteprimernivel; 
$comsegundonivel 	   	= $costodomicilio*$poraportesegundonivel;
$comtercernivel  	   	= $costodomicilio*$poraportetercernivel;
	
$comgrupo 			   	= $comprimernivel + $comsegundonivel + $comtercernivel;

if($tipopago == 1){
	$vrnequi  		    = ($costodomicilio+$comalter+$comprimernivel+$comsegundonivel+$comtercernivel)*$porcomnequi;
}elseif ($tipopago == 2 OR $tipopago == 3 OR $tipopago == 4) {
	$vrnequi  		    = 0;
}

$total 					= $costodomicilio + $comalter + $comgrupo + $vrnequi;

$stmt = $con->prepare("INSERT INTO servicio(idtiposervicio, idtipovehiculo, idcliente, estatus, fecharegistro,valor,idtipopago,total,totalaproximado) VALUES (?,?,?,?,?,?,?,?,?)");

$con->set_charset("utf8");

$stmt->bind_param("iiiississ", $idtiposervicio,$idtipovehiculo,$idcliente,$estatus,$fecharegistro,$costodomicilio,$tipopago,$total,$costoproductos);

$stmt->execute();

if($stmt->error!=""){
	echo json_encode(array('respuesta' => false,'error'=>$stmt->error));
	reporte_error($idcliente,"test",$stmt->error,"registroserviciodomicilio.php","");
	die();
}else{
	$idservicio = $stmt->insert_id;
	$stmt5 = $con->prepare("INSERT INTO servicio_lugar(idservicio, idlugar) VALUES (?,?)");

	$stmt5->bind_param("ii", $idservicio,$idlugar);

	$stmt5->execute();

	// Registrar direccion de entrega
	$orden 		= 0;
	$stmt2 		= $con->prepare("INSERT INTO direccion(idservicio, direccion, indicaciones, orden ,fecharegistrodir, lat, lng) VALUES 	(?,?,?,?,?,?,?)");
	$con->set_charset("utf8");
	$stmt2->bind_param("ississs", $idservicio, $puntoentrega, $indicacionpe, $orden, $fecharegistro,$puntoentregalat,$puntoentregalng);
	$stmt2->execute();
	// Registrar items
	$i = 0;
	foreach ($data->carroitems as $key => $value) {
		$iditem   		= $value->iditem;
		$cantidad 		= $value->cantidad;
		if(!empty($value->instrucciones)){
			$instrucciones 	= $value->instrucciones;
		}
		$stmt3 = $con->prepare("INSERT INTO domicilio_items(idservicio, iditem, extra, cantidad, instrucciones) VALUES (? , ? , 0 , ?, ?)");
		/* bind parameters for markers */
		$stmt3->bind_param("iiis", $idservicio, $iditem, $cantidad,$instrucciones);
		/* execute query */
		$stmt3->execute();
		if($stmt3->error != ""){
			echo json_encode(array('respuesta' => false,'error'=>$stmt3->error));
			die();
		}
		$orden++;
		// Registrar extras
		if(!empty($data->servicio->adiciones)){
			foreach ($data->servicio->adiciones[$i] as $key => $val) {
				$iditemadicion 	= $val->iditemadicion;
				$stmt4 = $con->prepare("INSERT INTO domicilio_items(idservicio, iditem, extra, iditemadicion) VALUES (? , ? , 1, ?)");
				/* bind parameters for markers */
				$stmt4->bind_param("iii", $idservicio, $iditem, $iditemadicion);
				/* execute query */
				$stmt4->execute();
				if($stmt4->error != ""){
					echo json_encode(array('respuesta' => false,'error'=>$stmt4->error));
					die();
				}
			}
		}
		$i++;	
	}

	foreach ($data->servicio->adiciones as $key => $value) {
		if(!empty($value)){

		}
	}	

	if($stmt2->error==""){
		echo json_encode(array('respuesta' => true,'idservicio' => $idservicio));			
	}else{
		echo json_encode(array('respuesta' => false,'error'=>$stmt2));
	}
}

?>