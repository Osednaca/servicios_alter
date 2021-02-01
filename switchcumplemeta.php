<?php
	/*
	Descripcion: Este servicio se ejecuta con CRON JOB todos los 15 y ultimo de cada mes (Fechas de corte) antes de ejecutar el updatesaldoalter.php
	Funcion: Recorrer cada uno de los usuarios registrados en la base de datos, verificar si cumplieron la meta y marcar en la base de datos si cumplio o no.
	*/
	include("includes/utils.php");
	if(date('d') == 16){
	    $fechaultimocorte = date("Y-m-01");
	}
	if(date('d') == 1){
		$m = date('m')-1;
	    $fechaultimocorte = date("Y-".$m."-16");
	}
	$fechanow = date("Y-m-d H:i:s");

	$stmt = $con->prepare("SELECT idusuario,nombre,apellido FROM usuario");
	$stmt->execute();
	$stmt->bind_result($idusuario,$nombre,$apellido);
	$stmt->store_result();
	while($stmt->fetch()){

		//Saldo que ha ganado por servicios como proveedor y como cliente
		$stmt1 = $con->prepare("SELECT SUM(valor) FROM servicio WHERE (idproveedor = ? OR idcliente = ?) AND estatus=5 AND fecharegistro>=? AND fecharegistro<=?");
		$stmt1->bind_param("iiss", $idusuario,$idusuario,$fechaultimocorte,$fechanow);
		$stmt1->execute();
		$stmt1->bind_result($servicioshechos);
		$stmt1->fetch();
		$stmt1->free_result();

		//Get Meta Mensual
		$stmt2 = $con->prepare("SELECT metaquincenal FROM usuario WHERE idusuario = ?");
		$stmt2->bind_param("i", $idusuario);
		$stmt2->execute();
		$stmt2->bind_result($meta);
		$stmt2->fetch();
		$stmt2->free_result();
	
		if($servicioshechos >= $meta){
 			$cumplemeta = 1;
		}else{
			$cumplemeta = 0;
		}

		//echo "Nombre: $nombre $apellido Servicios: $servicioshechos Meta: $meta | Desde: $fechaultimocorte Hasta: $fechanow<br>";
		
		$stmt3 = $con->prepare("UPDATE usuario SET cumplemeta = ? WHERE idusuario = ?");
		$stmt3->bind_param("ii", $cumplemeta,$idusuario);
		$stmt3->execute();
	}
?>