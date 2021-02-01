<?php
	include("includes.php");

	$post_date  	= file_get_contents("php://input");
	$data 			= json_decode($post_date);
	$token 			= Auth::GetData(
        				$data->token
    				  );
	$idusuario  	= $token->id;
	$idservicio   	= $data->idservicio;
	$idciudad   	= $data->idciudad;
	$tipovehiculo   = $data->tipovehiculo;
	$tiposervicio   = $data->tiposervicio;
	$valor 			= $data->valor;
	$disponibilidad = 1;
	$start  		= $data->start; // punto de patida del servicio
	$fechanow 		= date("Y-m-d H:i:s");
	$Proveedores    = array();

$stmt = $con->prepare("SELECT estatus FROM servicio WHERE idservicio=? "); // 
$stmt->bind_param("i",$idservicio);
$stmt->execute();
$stmt->bind_result($estatus);
$stmt->fetch();
$stmt->store_result();
if($estatus != 0){
	$stmt->free_result();
	if($tipovehiculo == 7){
		$sqltipovehiculo = "AND tipovehiculoactivo IN(2,5)";
	}else{
		$sqltipovehiculo = "AND tipovehiculoactivo = $tipovehiculo";
	}
//var_dump($fechanow); die;
	//echo "idservicio : $idservicio <br> idusuario : $idusuario <br> idciudad : $idciudad <br> tipovehiculo : $tipovehiculo // tiposervicio : $tiposervicio // valor : $valor";
if(!empty($data->tiempopreparacion)){
	$stmt2 = $con->prepare("UPDATE servicio SET tiempopreparacion = ? WHERE idservicio = ?");
	$stmt2->bind_param("ii", $data->tiempopreparacion,$idservicio);
	$stmt2->execute();
	$stmt2->free_result();
}

//Validar que la fecha de la ubicacion sea reciente  Probar con este filtro: AND fechaubicacion >= NOW() - INTERVAL 5 MINUTE
$stmt3 = $con->prepare("SELECT DISTINCT idusuario as idusuario1, (SELECT latitud FROM usuarioubicacion WHERE idusuario = idusuario1 ORDER BY fechaubicacion DESC LIMIT 1), (SELECT longitud FROM usuarioubicacion WHERE idusuario = idusuario1 ORDER BY fechaubicacion DESC LIMIT 1),(SELECT fechaubicacion FROM usuarioubicacion WHERE idusuario = idusuario1 ORDER BY fechaubicacion DESC LIMIT 1)
							FROM usuarioubicacion 
							INNER JOIN usuario USING(idusuario) 
						WHERE disponibilidad=? AND idusuario<>? AND usuarioubicacion.tipousuario=2 $sqltipovehiculo AND saldoalter >= ? AND fechaubicacion >= ? - INTERVAL 5 MINUTE AND activopara LIKE '%$tiposervicio%'"); // 

/* bind parameters for markers */
//echo "$sqltipovehiculo";
$stmt3->bind_param("iiis",$disponibilidad,$idusuario,$valor,$fechanow);

/* execute query */
$stmt3->execute();

/* bind result variables */
$stmt3->bind_result($idproveedor, $latitud, $longitud, $fechaubicacion);

$stmt3->store_result();
while($stmt3->fetch()){
	//echo $idproveedor. $idservicio."\n";
	//Validar que el proveedor no haya sido notificado de este servicio
	$stmt4 = $con->prepare("SELECT idnotificacion FROM notificacion WHERE idusuario=? AND idservicio=? AND estatus IN(2,0)");
	/* bind parameters for markers */
	$stmt4->bind_param("ii", $idproveedor, $idservicio);
	/* execute query */
	$stmt4->execute();
	/* bind result variables */
	$stmt4->bind_result($idnotificacion);

	$stmt4->fetch();
	
	if($idnotificacion == ""){
		$end = $latitud.", ".$longitud;
		$agent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.0.3705; .NET CLR 1.1.4322)';
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_USERAGENT, $agent);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_BINARYTRANSFER, TRUE);
		$url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=".urlencode($start)."&destinations=".urlencode($end)."&mode=driving&key=AIzaSyDpE4hgWe71uVid7ZN2oGdHUTSy4jjky3A";
		curl_setopt($ch, CURLOPT_URL,$url);
		$result=curl_exec($ch);
		$array = json_decode($result, true);
		curl_exec($ch);
		//var_dump($array["rows"][0]["elements"]); die;
		if($array["status"] != "OVER_QUERY_LIMIT"){
			if(!array_key_exists ('status', $array["rows"][0]["elements"])){
				if($array["rows"][0]["elements"][0]["status"] == 'OK'){				
					$Proveedores[] = array('idusuario' => $idproveedor,'distancia' => $array["rows"][0]["elements"][0]["distance"]["value"],'tiempo' =>	$array["rows"][0]["elements"][0]["duration"]["value"]);
				}
			}
		}
	}else{
		//Ya tiene una notificacion enviada o rechazada
	}
	$idnotificacion = "";
	$stmt4->free_result();
}

if(!empty($Proveedores)){
	echo json_encode(array('respuesta' => true, 'proveedores'=> $Proveedores));
}else{
	echo json_encode(array('respuesta' => false,'msj'=>'No hay proveedores cercanos.'));
}
}else{
	echo json_encode(array('respuesta' => false, 'msj'=>'Cancelado'));
}
?>