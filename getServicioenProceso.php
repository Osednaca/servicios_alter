<?php

	include("includes.php");

	$post_date  	= file_get_contents("php://input");
	$data 			= json_decode($post_date);
	$token 			= Auth::GetData(
        				$data->token
    				  );
	$idusuario  	= $token->id;

	$idservicio  	= $data->idservicio;

	$Direcciones 	= array();

	$poraportegrupo				= select_config_alter("aportegrupo");
	$poraportehijos 			= select_config_alter("aportehijos");
	$poraportenietos			= select_config_alter("aportenietos");
	$poraportebisnietos			= select_config_alter("aportebisnietos");
	$porservicioalter			= select_config_alter("servicioalter");
	$porgastostransaccionales	= select_config_alter("comisionnequi");	
	$poriva						= select_config_alter("iva");	


$stmt = $con->prepare("SELECT idtiposervicio FROM servicio WHERE idservicio = ?"); // 

$stmt->bind_param("i",$idservicio);
$stmt->execute();
$stmt->bind_result($tiposervicio);
$stmt->store_result();
$stmt->fetch();	

if($tiposervicio == 4){
	$sqldireccionpartida = "(SELECT direccion FROM direccion WHERE idservicio = servicio.idservicio AND orden = 0)";
}else{
	$sqldireccionpartida = "(SELECT direccion FROM direccion WHERE idservicio = servicio.idservicio AND orden = 1)";
}

$stmt = $con->prepare("SELECT idservicio,incluyetramite,servicio.estatus,DATE(servicio.fecharegistro),fechaculminacion,tiempoestimadototal,valor,tiposervicio.idtiposervicio,tiposervicio,usuario.idvehiculoactivo,tipovehiculo.idtipovehiculo,tipovehiculo,tipopago,idproveedor,idcliente,usuario.cedula,usuario.nombre,usuario.apellido,usuario.telefonocelular,proveedor.cedula,proveedor.nombre,proveedor.apellido,vehiculo.placa,usuario.imgusuario,proveedor.imgusuario,proveedor.telefonocelular, (SELECT idciudad FROM direccion WHERE idservicio = servicio.idservicio AND orden = 1) as idciudadpartida, (SELECT ciudad FROM direccion INNER JOIN ciudad USING(idciudad) WHERE idservicio = servicio.idservicio AND orden = 1) as ciudadpartida,$sqldireccionpartida as direccionpartida, (SELECT direccion FROM direccion WHERE idservicio = servicio.idservicio AND orden = 2) as direcciondestino, (SELECT indicaciones FROM direccion WHERE idservicio = servicio.idservicio AND orden = 1) as indicacionpartida, (SELECT indicaciones FROM direccion WHERE idservicio = servicio.idservicio AND orden = 2) as indicaciondestino,servicio.idtiposervicio,servicio.idtipopago,transactionid,totalaproximado
						FROM servicio 
						INNER JOIN usuario ON idcliente=usuario.idusuario 
						LEFT JOIN usuario as proveedor ON idproveedor=proveedor.idusuario 
						INNER JOIN tiposervicio USING(idtiposervicio) 
						INNER JOIN tipovehiculo USING(idtipovehiculo) 
						LEFT JOIN tipopago USING(idtipopago) 
						LEFT JOIN vehiculo ON servicio.idvehiculo = vehiculo.idvehiculo
						WHERE idservicio=? AND servicio.estatus IN(1,2,3,9)");

/* bind parameters for markers */

$stmt->bind_param("i", $idservicio);

$stmt->execute();

if($stmt->error == ""){

	/* bind result variables */

	$stmt->bind_result($idservicio,$incluyetramite,$estatus,$fecharegistro,$fechaculminacion,$tiempoestimadototal,$valor,$idtiposervicio,$tiposervicio,$idvehiculo,$idtipovehiculo,$tipovehiculo,$tipopago,$idproveedor,$idcliente,$cedulacliente,$nombrecliente,$apellidocliente,$clientetelefono,$cedulaproveedor,$nombreproveedor,$apellidoproveedor,$placaproveedor,$imgcliente,$imgproveedor,$proveedortelefono,$idciudadpartida,$ciudadpartida,$direccionpartida,$direcciondestino,$indicacionpartida,$indicaciondestino,$idtiposervicio,$idtipopago,$transactionid,$totalaproximado);

	$stmt->fetch();

    $valorhijos            	= $valor * $poraportehijos;
    $valornietos           	= $valor * $poraportenietos;
    $valorbisnietos        	= $valor * $poraportebisnietos;
    $valorgrupo            	= $valorhijos+$valornietos+$valorbisnietos;
    $servicioalter         	= $valor * $porservicioalter;
    $servicioalter 			= $servicioalter + ($servicioalter*$poriva);
    $gastostransaccionales 	= ($valor+$valorgrupo+$servicioalter) * $porgastostransaccionales;

	if($idcliente != "" AND $idcliente != NULL){
		switch ($idtipopago) {
				case 1:
					$tipopago = "Nequi";
					break;
				case 3:
					$tipopago = "Saldo Alter";
					break;
				case 4:
					$tipopago = "Efectivo";
					break;
			}	
		$costocliente  = round($valor + $valorgrupo + $servicioalter + $gastostransaccionales);
		if($idproveedor == $idusuario){
			$escliente = false;
			if($idtipopago == 4){
				$total 	   = $costocliente;
			}else{
				$total 	   = round($valor - $valorgrupo - $servicioalter - $gastostransaccionales);
			}
		}else{
			$escliente = true;
			$total 	   = $costocliente;
		}


		$servicio = array('idservicio'=>$idservicio,'incluyetramite'=>$incluyetramite,'estatus'=>$estatus,'fecharegistro'=>$fecharegistro,'fechaculminacion'=>$fechaculminacion,'tiempoestimadototal'=>$tiempoestimadototal,'valor'=>$total,'tiposervicio'=>utf8_encode($tiposervicio),'tipovehiculo'=>utf8_encode($tipovehiculo),'tipopago'=>$tipopago,'idcliente'=>$idcliente,'clientecedula'=>$cedulacliente,'idproveedor'=>$idproveedor,'clientenombre'=>utf8_encode($nombrecliente),'clienteapellido'=>	utf8_encode($apellidocliente),'clientetelefono'=>$clientetelefono,'cedulaproveedor'=>$cedulaproveedor,'nombreproveedor'	=>utf8_encode($nombreproveedor),'apellidoproveedor'=>utf8_encode($apellidoproveedor),'placaproveedor'=>$placaproveedor,	'escliente'=>$escliente,'placaproveedor'=>$placaproveedor,'imgcliente'=>$imgcliente,'imgproveedor'=>$imgproveedor,'direccionpartida'=>utf8_encode($direccionpartida),'direcciondestino'=>utf8_encode($direcciondestino),'idtiposervicio'=>$idtiposervicio,'idtipovehiculo'=>$idtipovehiculo,'ciudadpartida'=>$idciudadpartida,'ciudadpartidatxt'=>utf8_encode($ciudadpartida),'idvehiculo'=>$idvehiculo,'indicacion_partida'=>utf8_encode($indicacionpartida),'indicacion_destino'=>utf8_encode($indicaciondestino),'telefono'=>$proveedortelefono,'idtiposervicio'=>$idtiposervicio,'idtipopago'=>$idtipopago,'tipopago'=>$tipopago,'costocliente'=>$costocliente,'transactionid'=>$transactionid,'totalaproximado'=>$totalaproximado);

		$stmt->free_result();

		if($idtiposervicio == 1 OR $idtiposervicio == 2 OR $idtiposervicio == 3){
			$sql = "SELECT ciudad,direccion,indicaciones,tiempoestimado,orden FROM direccion LEFT JOIN ciudad USING(idciudad) WHERE idservicio=? AND orden NOT IN(1,2) ORDER BY orden";
		}elseif($idtiposervicio == 4 OR $idtiposervicio == 5 OR $idtiposervicio == 6 OR $idtiposervicio == 7 OR $idtiposervicio == 8){
			$sql = "SELECT ciudad,direccion,indicaciones,tiempoestimado,orden FROM direccion LEFT JOIN ciudad USING(idciudad) WHERE idservicio=? ORDER BY orden";
		}

		$stmt1 = $con->prepare($sql);

		$stmt1->bind_param("i", $idservicio);

		$stmt1->execute();

		$stmt1->bind_result($ciudad,$direccion,$indicaciones,$tiempoestimado,$orden);

		while($stmt1->fetch()){

			$Direcciones[] = array('ciudad'=>utf8_encode($ciudad),'direccion'=>utf8_encode($direccion),'indicaciones'=>utf8_encode($indicaciones),'tiempoestimado'=>$tiempoestimado,'orden'=>$orden);

		}

		if(!empty($servicio)){

			echo json_encode(array('respuesta' => true, 'servicio'=>$servicio,'direcciones'=> $Direcciones));

		}else{

			echo json_encode(array('respuesta' => false));

		}

	}else{

		echo json_encode(array('respuesta' => false, 'mensaje' => 'El servicio no fue encontrado o ya no esta en proceso.'));

	}

}else{

	echo json_encode(array('respuesta' => false, 'mensaje' => $stmt->error));

}

?>