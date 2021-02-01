<?php

include("includes.php");
$post_date  	= file_get_contents("php://input");
$data 			= json_decode($post_date);
$token 			= Auth::GetData(
    				$data->token
				);
$idusuario  	= $token->id;
$tieneservicio  = false;
$esproveedor 	= false;
$now 			= date("Y-m-d H:i:s");

//Buscar el ID del ultimo servicio

$stmt2 = $con->prepare("SELECT servicio.idservicio,idcliente,idproveedor,servicio.fecharegistro,valor,placa,cliente.nombre,cliente.apellido,proveedor.nombre,proveedor.apellido,calificacion FROM servicio LEFT JOIN calificacion ON(servicio.idservicio = calificacion.idservicio AND idusuario!=servicio.idproveedor) INNER JOIN vehiculo ON(servicio.idvehiculo = vehiculo.idvehiculo) INNER JOIN usuario as cliente ON cliente.idusuario = servicio.idcliente INNER JOIN usuario as proveedor ON proveedor.idusuario = servicio.idproveedor WHERE idcliente = ? AND servicio.estatus = 5 AND calificacion IS NULL ORDER BY fecharegistro DESC LIMIT 1");
$stmt2->bind_param("i", $idusuario);
$stmt2->execute();
$stmt2->bind_result($idservicio,$idcliente,$idproveedor,$fecharegistro,$valor,$placa,$nombrecliente,$apellidocliente,$nombreproveedor,$apellidoproveedor,$calificacion);
$stmt2->fetch();
$stmt2->free_result();

//Checkear si tiene servicios en proceso 
$stmt3 = $con->prepare("SELECT idservicio,idproveedor,idtiposervicio FROM servicio WHERE (servicio.idproveedor = ? OR servicio.idcliente = ?) AND servicio.estatus IN(1,2,3,4)");
$stmt3->bind_param("ii", $idusuario,$idusuario);
$stmt3->execute();
$stmt3->store_result();
$nservicios = $stmt3->num_rows;
$stmt3->bind_result($idservicio2,$idproveedor2,$idtiposervicio);
$stmt3->fetch();

	if($idservicio2 != ""){
		$tieneservicio = true;
	}else{
		$tieneservicio = false;
	}
	if($idproveedor2 == $idusuario){
		$esproveedor = true;
	}else{
		$esproveedor = false;
	}

//Validar que tenga cuenta nequi
$stmt4 = $con->prepare("SELECT idcuentanequi FROM cuenta_nequi INNER JOIN usuario USING(idusuario) WHERE idusuario = ?");
$stmt4->bind_param("i", $idusuario);
$stmt4->execute();
$stmt4->bind_result($idcuenta);
$stmt4->fetch();
$stmt4->free_result();

if($idcuenta != ""){
	$cuenta_nequi = 1;
}else{
	$cuenta_nequi = 0;
}

if($idservicio==""){
	echo json_encode(array('respuesta'=> true,'tieneservicio'=>$tieneservicio,'nservicios' => $nservicios,'idservicio'=>$idservicio2,'esproveedor'=>$esproveedor,'calificacionpendiente'=>false, 'cuenta_nequi' => $cuenta_nequi, 'idtiposervicio' => $idtiposervicio));
}else{
	if($idusuario==$idcliente){
		$tipopersona = 1; //Cliente
		$idusuariocalificacion = $idproveedor;
	}else{
		$tipopersona = 2; //Proveedor
		$idusuariocalificacion = $idcliente;		
	}

	if($calificacion==""){
		$servicio = array("placaproveedor"=>$placa,"idservicio"=>$idservicio,"clientenombre"=>$nombrecliente,"clientenapellido"=>$apellidocliente,"proveedornombre"=>$nombreproveedor,"proveedorapellido"=>$apellidoproveedor,"valor"=>$valor);
		echo json_encode(array('respuesta' => true,'servicio' => $servicio,'tipopersona'=>$tipopersona,'idusuariocalificacion'=>$idusuariocalificacion,'tieneservicio'=>$tieneservicio,'nservicios' => $nservicios,'esproveedor'=>$esproveedor,'calificacionpendiente'=>true, 'cuenta_nequi' => $cuenta_nequi, 'idtiposervicio' => $idtiposervicio));
	}else{
		echo json_encode(array('respuesta' => true,'tieneservicio'=>$tieneservicio,'nservicios' => $nservicios,'idservicio'=>$idservicio2,'esproveedor'=>$esproveedor,'calificacionpendiente'=>false, 'cuenta_nequi' => $cuenta_nequi, 'idtiposervicio' => $idtiposervicio));
	}
}
?>