<?php
	include("includes.php");
	//echo "Hola!"; die;
	$post_date  	= file_get_contents("php://input");
	$data 			= json_decode($post_date);
	$token 			= Auth::GetData(
        				$data->token
    				  );
	$idusuario  	= $token->id;
	$idservicio   	= $data->idservicio;
	//$idciudad   	= $data->idciudad;
	$tipovehiculo   = $data->tipovehiculo;
	$tiposervicio   = $data->tiposervicio;
	$disponibilidad = 1;
	$start  		= $data->start; // punto de patida del servicio
	$fechanow 		= date("Y-m-d H:i:s");
	$Proveedores    = array();
	if($tipovehiculo == 7){
		$sqlvehiculo = "tipovehiculoactivo IN(2,5)";
	}else{
		$sqlvehiculo = "tipovehiculoactivo = $tipovehiculo";
	}

if(!empty($data->tiempopreparacion)){
	$stmt = $con->prepare("UPDATE servicio SET tiempopreparacion = ? WHERE idservicio = ?");
	$stmt->bind_param("ii", $data->tiempopreparacion,$idservicio);
	$stmt->execute();
}

$stmt1 = $con->prepare("SELECT estatus,canceladopor FROM servicio WHERE idservicio=? "); // 
$stmt1->bind_param("i",$idservicio);
$stmt1->execute();
$stmt1->bind_result($estatus,$canceladopor);
$stmt1->fetch();
$stmt1->free_result();
if($estatus != 0){

	//echo "idservicio : $idservicio <br> idusuario : $idusuario <br> idciudad : $idciudad <br> tipovehiculo : $tipovehiculo";
//Validar que la fecha de la ubicacion sea reciente  Probar con este filtro: AND fechaubicacion >= NOW() - INTERVAL 5 MINUTE
$stmt = $con->prepare("SELECT DISTINCT idusuario as idusuario1, (SELECT latitud FROM usuarioubicacion WHERE idusuario = idusuario1 ORDER BY fechaubicacion DESC LIMIT 1), (SELECT longitud FROM usuarioubicacion WHERE idusuario = idusuario1 ORDER BY fechaubicacion DESC LIMIT 1),(SELECT fechaubicacion FROM usuarioubicacion WHERE idusuario = idusuario1 ORDER BY fechaubicacion DESC LIMIT 1)
							FROM usuarioubicacion 
							INNER JOIN usuario USING(idusuario) 
						WHERE disponibilidad=? AND idusuario<>? AND usuarioubicacion.tipousuario=2 AND $sqlvehiculo AND fechaubicacion >= ? - INTERVAL 5 MINUTE AND FIND_IN_SET($tiposervicio,activopara) > 0"); // 

/* bind parameters for markers */
$stmt->bind_param("iis",$disponibilidad,$idusuario,$fechanow);

/* execute query */
$stmt->execute();

/* bind result variables */
$stmt->bind_result($idproveedor, $latitud, $longitud, $fechaubicacion);

$stmt->store_result();
while($stmt->fetch()){
	//echo $idproveedor. $idservicio."\n";
	//Validar que el proveedor no haya sido notificado de este servicio
	$stmt1 = $con->prepare("SELECT idnotificacion FROM notificacion WHERE idusuario=? AND idservicio=? AND estatus IN(2,0)");
	/* bind parameters for markers */
	$stmt1->bind_param("ii", $idproveedor, $idservicio);
	
	/* execute query */
	$stmt1->execute();
	/* bind result variables */
	$stmt1->bind_result($idnotificacion);

	$stmt1->fetch();
	
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
		if($array["status"] != "OVER_QUERY_LIMIT"){
			if(!array_key_exists ('status', $array["rows"][0]["elements"])){
				if($array["rows"][0]["elements"][0]["status"] == 'OK'){				
					if($array["rows"][0]["elements"][0]["distance"]["value"] <= 10000){
						$Proveedores[] = array('idusuario' => $idproveedor,'distancia' => $array["rows"][0]["elements"][0]["distance"]["value"],'tiempo' =>	$array["rows"][0]["elements"][0]["duration"]["value"]);
					}
				}
			}
		}
	}else{
		//Ya tiene una notificacion enviada o rechazada
	}
	$idnotificacion = "";
	$stmt1->free_result();
}

if(!empty($Proveedores)){
	echo json_encode(array('respuesta' => true, 'proveedores'=> $Proveedores));
}else{
	echo json_encode(array('respuesta' => false,'msj'=>'No hay proveedores cercanos.'));
}
}else{
	echo json_encode(array('respuesta' => false, 'msj'=>'Cancelado','canceladopor'=>$canceladopor));
}
?>