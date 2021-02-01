<?php
include("includes.php");
require '../vendor/autoload.php';
use Location\Coordinate;
use Location\Polygon;
use Location\Distance\Haversine;

$post_date  = file_get_contents("php://input");
$data 		= json_decode($post_date);
$categorias = array();
$dia 		= date("N");
$latusuario = $data->lat;
$lngusuario = $data->lng;
$cerrado 	= false;
$lugares    = array();

if($dia >= 1 OR $dia <= 5){
	$tipohorario = 1;
}elseif($dia == 6){
	$tipohorario = 2;
}elseif($dia == 7) {
	$tipohorario = 3;
}

$stmt = $con->prepare("SELECT l.idlugar,(SELECT COUNT(iditem) FROM lugar_items WHERE idlugar = (SELECT idlugar FROM lugar WHERE nombrelugar = l.nombrelugar AND padre = 1) AND aprobado = 1), categoria, nombrelugar, descripcion, imagen, lat,lng, desde, hasta,tiporegistro FROM lugar l INNER JOIN lugar_tipo USING(idlugarcategoria) LEFT JOIN lugar_horario ON l.idlugar = lugar_horario.idlugar AND lugar_horario.tipohorario = $tipohorario WHERE l.aprobado = 1 AND estatus = 1 AND idlugarcategoria = 18"); //idciudad=? AND 
//$stmt->bind_param("i",$idciudad);
$stmt->execute();

if($stmt->error == ""){
	/* bind result variables */
	$stmt->bind_result($idlugar, $nitems, $tipolugar, $nombrelugar, $descripcion, $imagen, $latlugar, $lnglugar,$desde,$hasta,$tiporegistro);
	$stmt->store_result();
	while ($stmt->fetch()) {
		//echo $idlugar." => ".$desde."\n";
		$coordinate1 = new Coordinate($latlugar, $lnglugar);
		$coordinate2 = new Coordinate($latusuario, $lngusuario);
		$dist =  $coordinate1->getDistance($coordinate2, new Haversine());
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
			}			
			$lugares[] = array("idlugar" => $idlugar,"nombrelugar" => utf8_encode($nombrelugar),"tipolugar" => $tipolugar,"descripcion" => $descripcion,"lat" => $latlugar,"lng" => $lnglugar,"imagen" => $imagen,"cerrado" => $cerrado,'dist' => $dist,'tiporegistro' => $tiporegistro);
		}
	}

	$group = array();

	foreach ( $lugares as $value ) {
    	$group[$value['nombrelugar']][] = $value;
	}

	$orderbydist = array();
	foreach ($group as $key => $value) {
		usort($value, function($a, $b) {
	    	return $a['dist'] <=> $b['dist'];
		});
		$orderbydist[] = $value;
	}
	//var_dump($orderbydist); die();
	$taken 		= array();
	$finalarray = array();
	foreach ($orderbydist as $key => $value) {
		foreach($value as $key => $item) {
		    if(!in_array($item['nombrelugar'], $taken)) {
    	    	$taken[] = $item['nombrelugar'];
    		} else {
		        unset($value[$key]);
		    }
		}
		$finalarray[] = $value;
	}

	//var_dump($finalarray); die();

	if(!empty($lugares)){
		echo json_encode(array("respuesta" => true, "lugares" => $finalarray, "categorias" => $categorias));
	}else{
		echo json_encode(array("respuesta" => false, "msg" => "No hay lugares cercanos o disponibles.","categorias" => $categorias));
	}
}else{
	echo json_encode(array("respuesta"=>false,"msg"=>"Error en la consulta."));
}