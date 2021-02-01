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



$stmt = $con->prepare("SELECT idservicio,incluyetramite,servicio.estatus,DATE(servicio.fecharegistro),fechaculminacion,tiempoestimadototal,valor,idtiposervicio,tiposervicio,tipovehiculo,idtipopago,idproveedor,usuario.cedula,usuario.nombre,usuario.apellido,proveedor.cedula,proveedor.nombre,proveedor.apellido,vehiculo.placa,usuario.imgusuario,proveedor.imgusuario,proveedor.telefonocelular,totalaproximado
						FROM servicio 
						INNER JOIN usuario ON idcliente=usuario.idusuario 
						LEFT JOIN usuario as proveedor ON idproveedor=proveedor.idusuario 
						INNER JOIN tiposervicio USING(idtiposervicio) 
						INNER JOIN tipovehiculo USING(idtipovehiculo) 
						LEFT JOIN tipopago USING(idtipopago) 
						LEFT JOIN vehiculo ON servicio.idvehiculo = vehiculo.idvehiculo
						WHERE idservicio=?");

/* bind parameters for markers */

$stmt->bind_param("i", $idservicio);



/* execute query */

$stmt->execute();



if($stmt->error == ""){

	/* bind result variables */

	$stmt->bind_result($idservicio,$incluyetramite,$estatus,$fecharegistro,$fechaculminacion,$tiempoestimadototal,$valor,$idtiposervicio,$tiposervicio,$tipovehiculo,$tipopago,$idproveedor,$cedulacliente,$nombrecliente,$apellidocliente,$cedulaproveedor,$nombreproveedor,$apellidoproveedor,$placaproveedor,$imgcliente,$imgproveedor,$telefonoproveedor,$totalaproximado);



	$stmt->fetch();

	

	//0 = Cancelado, 1 = Activo, 2 = En proceso, 3 = Proveedor Llego, 4 = Proveedor Finalizo, 5 = Finalizado, 6 = Cobrado, 7 = Cancelado Pago Minimo

	if($estatus==0){
		$estatustxt = "Cancelado";
		$color 		= "color: red;";
	}elseif ($estatus==1) {
		$estatustxt = "Activo";
		$color 		= "color: green;";
	}elseif ($estatus==2) {
		$estatustxt = "En proceso";
		$color 		= "color: orange;";
	}elseif ($estatus==3) {
		$estatustxt = "Proveedor Llego";
		$color 		= "color: orange;";
	}elseif ($estatus==4) {
		$estatustxt = "Proveedor Finalizó";
		$color 		= "color: red;";
	}elseif ($estatus==5) {
		$estatustxt = "Finalizó";
		$color 		= "color: red;";
	}elseif ($estatus==6) {
		$estatustxt = "Cobrado";
		$color 		= "color: #c3c302;";
	}elseif ($estatus==7) {
		$estatustxt = "Cancelado Pago Minimo";
		$color 		= "color: blue;";
	}elseif ($estatus==9) {
		$estatustxt = "Finalizó";
		$color 		= "color: #c3c302;";
	}

	if($tipopago == 1){
		$tipopagotxt = "Nequi";
	}elseif ($tipopago == 3) {
		$tipopagotxt = "Saldo Alter";
	}elseif ($tipopago == 4) {
		$tipopagotxt = "Efectivo";
	}


	if($idproveedor == $idusuario){
		$escliente = false;
	}else{
		$escliente = true;
	}

	$stmt->free_result();

	if($idtiposervicio == 5){
		$stmt2 = $con->prepare("SELECT direccion FROM lugar INNER JOIN servicio_lugar USING(idlugar) WHERE idservicio=?");
		$stmt2->bind_param("i", $idservicio);
		$stmt2->execute();
		$stmt2->bind_result($direccionlugar);
		$stmt2->fetch();
		$stmt2->free_result();
	}else{
		$direccionlugar = "";
	}

	$servicio = array('idservicio'=>$idservicio,'incluyetramite'=>$incluyetramite,'estatus'=>$estatus,'fecharegistro'=>$fecharegistro,'fechaculminacion'=>$fechaculminacion,'tiempoestimadototal'=>$tiempoestimadototal,'valor'=>$valor,'idtiposervicio'=>$idtiposervicio,'tiposervicio'=>utf8_encode($tiposervicio),'tipovehiculo'=>utf8_encode($tipovehiculo),'tipopago'=>$tipopago,'clientecedula'=>$cedulacliente,'idproveedor'=>$idproveedor,'clientenombre'=>utf8_encode($nombrecliente),'clienteapellido'=>utf8_encode($apellidocliente),'cedulaproveedor'=>$cedulaproveedor,'nombreproveedor'=>utf8_encode($nombreproveedor),'apellidoproveedor'=>utf8_encode($apellidoproveedor),'placaproveedor'=>$placaproveedor,'color'=>$color,'estatustxt'=>$estatustxt,'escliente'=>$escliente,'placaproveedor'=>$placaproveedor,'imgcliente'=>$imgcliente,'imgproveedor'=>$imgproveedor,'telefonoproveedor'=>$telefonoproveedor,'totalaproximado'=>$totalaproximado,'direccionlugar'=>utf8_encode($direccionlugar),'tipopagotxt'=>$tipopagotxt);

	

	$stmt1 = $con->prepare("SELECT ciudad,direccion,indicaciones,tiempoestimado,orden FROM direccion LEFT JOIN ciudad USING(idciudad) WHERE idservicio=? ORDER BY orden");

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

	echo json_encode(array('respuesta' => false, 'mensaje' => $stmt->error));

}



?>