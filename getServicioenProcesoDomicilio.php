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
	$items 			= array();
	$extras 		= array();

	$poraportegrupo				= select_config_alter("aportegrupo");
	$poraportehijos 			= select_config_alter("aportehijos");
	$poraportenietos			= select_config_alter("aportenietos");
	$poraportebisnietos			= select_config_alter("aportebisnietos");
	$porservicioalter			= select_config_alter("servicioalter");
	$porgastostransaccionales	= select_config_alter("comisionnequi");	
	$poriva						= select_config_alter("iva");

	$direccionlugar 			= "";

$stmt = $con->prepare("SELECT idservicio,incluyetramite,servicio.estatus,DATE(servicio.fecharegistro),fechaculminacion,tiempoestimadototal,valor,tiposervicio.idtiposervicio,tiposervicio,usuario.idvehiculoactivo,tipovehiculo.idtipovehiculo,tipovehiculo,tipopago,idproveedor,idcliente,usuario.cedula,usuario.nombre,usuario.apellido,usuario.telefonocelular,proveedor.cedula,proveedor.nombre,proveedor.apellido,vehiculo.placa,usuario.imgusuario,proveedor.imgusuario,proveedor.telefonocelular, (SELECT idciudad FROM direccion WHERE idservicio = servicio.idservicio AND orden = 1) as idciudadpartida, (SELECT ciudad FROM direccion INNER JOIN ciudad USING(idciudad) WHERE idservicio = servicio.idservicio AND orden = 1) as ciudadpartida,(SELECT direccion FROM direccion WHERE idservicio = servicio.idservicio AND orden = 0) as direccionpartida,servicio.idtiposervicio,servicio.idtipopago,transactionid,totalaproximado,idlugar,fechallego,tiempopreparacion
						FROM servicio
						INNER JOIN servicio_lugar USING(idservicio) 
						INNER JOIN usuario ON idcliente=usuario.idusuario 
						LEFT JOIN usuario as proveedor ON idproveedor=proveedor.idusuario 
						LEFT JOIN tiposervicio USING(idtiposervicio) 
						INNER JOIN tipovehiculo USING(idtipovehiculo) 
						LEFT JOIN tipopago USING(idtipopago) 
						LEFT JOIN vehiculo ON servicio.idvehiculo = vehiculo.idvehiculo
						WHERE idservicio=? AND servicio.estatus IN(1,2,3,4,9,10)");

/* bind parameters for markers */

$stmt->bind_param("i", $idservicio);

$stmt->execute();

if($stmt->error == ""){

	/* bind result variables */

	$stmt->bind_result($idservicio,$incluyetramite,$estatus,$fecharegistro,$fechaculminacion,$tiempoestimadototal,$valor,$idtiposervicio,$tiposervicio,$idvehiculo,$idtipovehiculo,$tipovehiculo,$tipopago,$idproveedor,$idcliente,$cedulacliente,$nombrecliente,$apellidocliente,$clientetelefono,$cedulaproveedor,$nombreproveedor,$apellidoproveedor,$placaproveedor,$imgcliente,$imgproveedor,$proveedortelefono,$idciudadpartida,$ciudadpartida,$direccionpartida,$idtiposervicio,$idtipopago,$transactionid,$totalaproximado,$idlugar,$fechallego,$tiempopreparacion);

	$stmt->fetch();

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

		$costodomicilio  = $valor;
		if($idproveedor == $idusuario){
			$escliente = false;
		}else{
			$escliente = true;
		}
		$stmt->free_result();

		if($estatus == 10){
			$sql = "SELECT imagen,nombrelugar FROM lugar WHERE idlugar=?";
		
			$stmt2 = $con->prepare($sql);
			$stmt2->bind_param("i", $idlugar);
			$stmt2->execute();
			$stmt2->bind_result($imagen,$nombrelugar);
			$stmt2->fetch();
			$cedulaproveedor   = $idlugar;
			$nombreproveedor   = $nombrelugar;
			$apellidoproveedor = "";
			$imgproveedor      = $imagen;
			$placaproveedor    = "N/A";
			$stmt2->free_result();
		}

		//Direccion del Restaurant
		$sql = "SELECT direccion FROM lugar WHERE idlugar=?";
	
		$stmt2 = $con->prepare($sql);
		$stmt2->bind_param("i", $idlugar);
		$stmt2->execute();
		$stmt2->bind_result($direccion);	
		
		while($stmt2->fetch()){
			$direccionlugar = utf8_encode($direccion);
		}
		//Items del servicio
		if($idtiposervicio == 9){
			$sql = "SELECT item FROM domicilio_tienda_items
						WHERE idservicio=?";
	
			$stmt4 = $con->prepare($sql);
			$stmt4->bind_param("i", $idservicio);
			$stmt4->execute();
			$stmt4->bind_result($item);
			while($stmt4->fetch()){
				$items[] = array('item'=>utf8_encode($item));
			}
		}else{
			$sql = "SELECT iditem,titulo,imagen,cantidad FROM domicilio_items
						INNER JOIN lugar_items USING(iditem)
						WHERE idservicio=? AND extra = 0";
	
			$stmt4 = $con->prepare($sql);
			$stmt4->bind_param("i", $idservicio);
			$stmt4->execute();
			$stmt4->bind_result($iditem,$titulo,$imagen,$cantidad);
			while($stmt4->fetch()){
				$items[] = array('iditem'=>$iditem,'cantidad'=>$cantidad,'titulo'=>utf8_encode($titulo),'imagen'=>utf8_encode($imagen));
			}			
		}
		if($idtiposervicio != 9){
			foreach ($items as &$i) {
				//Extras del servicio
				$sql2 = "SELECT titulo,precio FROM domicilio_items
								INNER JOIN items_adicion ON domicilio_items.iditemadicion = items_adicion.iditemadicion
								WHERE idservicio=? AND domicilio_items.iditem = ? AND extra = 1";
			
				$stmt5 = $con->prepare($sql2);
				$stmt5->bind_param("ii", $idservicio,$i["iditem"]);
				$stmt5->execute();
				$stmt5->bind_result($titulo2,$precio2);
				while($stmt5->fetch()){
					$extras[] = array('titulo'=>utf8_encode($titulo2),'precio'=>$precio2);
				}
				if(!empty($extras)){
					$i["extras"] = $extras;
				}
			}
		}

		$servicio = array('idservicio'=>$idservicio,'incluyetramite'=>$incluyetramite,'estatus'=>$estatus,'fecharegistro'=>$fecharegistro,'fechaculminacion'=>$fechaculminacion,'tiempoestimadototal'=>$tiempoestimadototal,'valor'=>$valor,'tiposervicio'=>utf8_encode($tiposervicio),'tipovehiculo'=>utf8_encode($tipovehiculo),'tipopago'=>$tipopago,'idcliente'=>$idcliente,'clientecedula'=>$cedulacliente,'idproveedor'=>$idproveedor,'clientenombre'=>utf8_encode($nombrecliente),'clienteapellido'=>	utf8_encode($apellidocliente),'clientetelefono'=>$clientetelefono,'cedulaproveedor'=>$cedulaproveedor,'nombreproveedor'	=>utf8_encode($nombreproveedor),'apellidoproveedor'=>utf8_encode($apellidoproveedor),'placaproveedor'=>$placaproveedor,	'escliente'=>$escliente,'placaproveedor'=>$placaproveedor,'imgcliente'=>$imgcliente,'imgproveedor'=>$imgproveedor,'direccionpartida'=>utf8_encode($direccionpartida),'idtiposervicio'=>$idtiposervicio,'idtipovehiculo'=>$idtipovehiculo,'ciudadpartida'=>$idciudadpartida,'ciudadpartidatxt'=>utf8_encode($ciudadpartida),'idvehiculo'=>$idvehiculo,'telefono'=>$proveedortelefono,'idtiposervicio'=>$idtiposervicio,'idtipopago'=>$idtipopago,'tipopago'=>$tipopago,'costodomicilio'=>$costodomicilio,'transactionid'=>$transactionid,'totalaproximado'=>$totalaproximado,'direccionlugar'=>$direccionlugar,'items'=>$items,'fechallego'=>$fechallego,'tiempopreparacion'=>$tiempopreparacion,'idlugar'=>$idlugar);

		$sql = "SELECT ciudad,direccion,indicaciones,tiempoestimado,orden FROM direccion LEFT JOIN ciudad USING(idciudad) WHERE idservicio=? AND orden = 0";

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