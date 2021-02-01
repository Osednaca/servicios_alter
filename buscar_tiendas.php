<?php
include("includes.php");
require '../vendor/autoload.php';
use Location\Coordinate;
use Location\Polygon;
use Location\Distance\Haversine;

$post_date  = file_get_contents("php://input");
$data 		= json_decode($post_date);
$token 		= Auth::GetData(
	$data->token
);
$idusuario  = $token->id;
$dia 		= date("N");
$latusuario = $data->lat;
$lngusuario = $data->lng;
$lugares    = array();


$stmt = $con->prepare("SELECT DISTINCT l.idlugar, categoria, nombrelugar, nombrepropietario, imagen, lat,lng, desde, hasta,tiporegistro,l.idusuario FROM lugar l INNER JOIN lugar_tipo USING(idlugarcategoria) LEFT JOIN lugar_horario ON l.idlugar = lugar_horario.idlugar WHERE l.aprobado = 1 AND estatus = 1 AND idlugarcategoria = 19 AND idusuario <> $idusuario"); //idciudad=? AND 
//$stmt->bind_param("i",$idciudad);
$stmt->execute();

if($stmt->error == ""){
	/* bind result variables */
	$stmt->bind_result($idlugar, $tipolugar, $nombrelugar, $nombrepropietario, $imagen, $latlugar, $lnglugar,$desde,$hasta,$tiporegistro,$idproveedor);
	$stmt->store_result();
	while ($stmt->fetch()) {
		//echo $idlugar." => ".$desde."\n";
		$coordinate1 = new Coordinate($latlugar, $lnglugar);
		$coordinate2 = new Coordinate($latusuario, $lngusuario);
		$dist =  $coordinate1->getDistance($coordinate2, new Haversine());
		if($dist <= 3000){
			$lugares[] = array("idlugar" => $idlugar,"nombrelugar" => utf8_encode($nombrelugar),"tipolugar" => $tipolugar,"nombrepropietario" => utf8_encode($nombrepropietario),"lat" => $latlugar,"lng" => $lnglugar,"imagen" => $imagen,'dist' => $dist,'tiporegistro' => $tiporegistro,'idproveedor'=>$idproveedor);
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

	//var_dump($lugares); die();

	if(!empty($lugares)){
		echo json_encode(array("respuesta" => true, "lugares" => $lugares));
	}else{
		echo json_encode(array("respuesta" => false, "msg" => "No hay tiendas cercanas o disponibles."));
	}
}else{
	echo json_encode(array("respuesta"=>false,"msg"=>"Error en la consulta."));
}

?>