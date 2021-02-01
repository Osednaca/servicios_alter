<?php
include("includes.php");
require '../vendor/autoload.php';
use Location\Coordinate;
use Location\Polygon;
use Location\Distance\Haversine;

$post_date  	= file_get_contents("php://input");
$data 			= json_decode($post_date);
$lat 	  		= $data->latitud;
$lng 	  		= $data->longitud;
$idtiposervicio = $data->idtiposervicio;
$categorias 	= array();
$dia 			= date("N");

if($dia >= 1 OR $dia <= 5){
	$tipohorario = 1;
}elseif($dia == 6){
	$tipohorario = 2;
}elseif($dia == 7) {
	$tipohorario = 3;
}

if($idtiposervicio == 5){
	$sqlcategoria = "AND idlugarcategoria NOT IN(18,13)";
}else{
	$sqlcategoria = "AND idlugarcategoria = 13";
}

$stmt = $con->prepare("SELECT lugar.idlugar,(SELECT COUNT(iditem) FROM lugar_items WHERE idlugar = lugar.idlugar OR idlugar IN(SELECT l.idlugar FROM lugar l WHERE l.nombrelugar = lugar.nombrelugar AND padre = 1) AND aprobado = 1), nombrelugar, descripcion, imagen,lat,lng, desde, hasta,idusuario,tiporegistro FROM lugar LEFT JOIN lugar_horario ON lugar.idlugar = lugar_horario.idlugar AND lugar_horario.tipohorario = $tipohorario WHERE lugar.aprobado = 1 AND estatus = 1 $sqlcategoria");
$stmt->execute();

if($stmt->error == ""){
	/* bind result variables */
	$stmt->bind_result($idlugar, $nitems, $nombrelugar, $descripcion, $imagen, $latlugar, $lnglugar,$desde,$hasta,$idusuario,$tiporegistro);
	$stmt->store_result();
	while ($stmt->fetch()) {
		if($nitems > 0){
			if($desde != ""){
				if(date("H:i") >= date("H:i", strtotime("$desde")) AND date("H:i") < date("H:i", strtotime("$hasta"))){
					$cerrado = false;
				}else{
					$cerrado = true;
				}
				if($hasta < "12:00"){
					if(date("H:i") >= date("H:i", strtotime("$desde")) OR date("H:i") < date("H:i", strtotime("$hasta"))){
						$cerrado = false;
					}
				}
			}else{
				$cerrado = false;
			}

			$stmt3 = $con->prepare("SELECT foto_producto FROM lugar WHERE idlugar = $idlugar");
			$stmt3->execute();
			
			if($stmt3->error == ""){
				/* bind result variables */
				$stmt3->bind_result($imagenplato);
				$stmt3->store_result();
				$stmt3->fetch();
			}
			if(empty($imagenplato)){
				$stmt2 = $con->prepare("SELECT imagen FROM lugar_items WHERE idlugar = $idlugar  AND imagen <> 'default-store.jpg' ORDER BY fecharegistro LIMIT 1");
				$stmt2->execute();
				
				if($stmt2->error == ""){
					/* bind result variables */
					$stmt2->bind_result($imagenplato);
					$stmt2->store_result();
					$stmt2->fetch();
				}
			}

			$coordinate1 = new Coordinate($lat, $lng); // Users Location
			$coordinate2 = new Coordinate($latlugar, $lnglugar); // Place Location
			$dist =  $coordinate1->getDistance($coordinate2, new Haversine());
			//echo $nombrelugar.": ".$dist."<br>";
			if($dist <= 5000){
				$lugares[] = array("idlugar" => $idlugar,"nombrelugar" => utf8_encode($nombrelugar),"descripcion" => utf8_encode($descripcion),"lat" => $latlugar,"lng" => $lnglugar,"imagen" => utf8_encode($imagen),"cerrado"=>$cerrado,"desde"=>$desde,"hasta"=>$hasta,"distancia"=>$dist,'tiporegistro'=>$tiporegistro,'imagenplato'=>$imagenplato);
			}
		}
	}
	
	$stmt2 = $con->prepare("SELECT idcategoria,categoria FROM lugar_categoria WHERE aprobado = 1 ORDER BY categoria ASC");
	$stmt2->execute();
	
	if($stmt2->error == ""){
		/* bind result variables */
		$stmt2->bind_result($idcategoria, $categoria);
		$stmt2->store_result();
		while ($stmt2->fetch()) {
			$categorias[] = array('idcategoria'=>$idcategoria,'categoria'=>$categoria); 
		}
	}

	if(!empty($lugares)){
		echo json_encode(array("respuesta" => true, "lugares" => $lugares, "categorias" => $categorias));
	}else{
		echo json_encode(array("respuesta" => false, "msg" => "No hay lugares cercanos o disponibles.","categorias" => $categorias));
	}
}else{
	echo json_encode(array("respuesta"=>false,"msg"=>"Error en la consulta."));
}