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
$busqueda   = $data->busqueda;
$latusuario = $data->lat;
$lngusuario = $data->lng;
$resultado    = array();

if($dia >= 1 OR $dia <= 5){
	$tipohorario = 1;
}elseif($dia == 6){
	$tipohorario = 2;
}elseif($dia == 7) {
	$tipohorario = 3;
}

$sql = "SELECT DISTINCT l.idlugar, idlugarcategoria, nombrelugar, imagen, lat,lng, desde, hasta,tiporegistro,l.idusuario FROM lugar l LEFT JOIN lugar_horario ON l.idlugar = lugar_horario.idlugar AND lugar_horario.tipohorario = $tipohorario WHERE l.aprobado = 1 AND estatus = 1 AND nombrelugar LIKE '%$busqueda%'";
//echo $sql; die;
$stmt = $con->prepare($sql); //idciudad=? AND 
//$stmt->bind_param("i",$idciudad);
$stmt->execute();

if($stmt->error == ""){
	/* bind result variables */
	$stmt->bind_result($idlugar, $idlugarcategoria, $nombrelugar, $imagen, $latlugar, $lnglugar,$desde,$hasta,$tiporegistro,$idproveedor);
	$stmt->store_result();
	while ($stmt->fetch()) {
		//echo $idlugar." => ".$desde."\n";
		$coordinate1 = new Coordinate($latlugar, $lnglugar);
		$coordinate2 = new Coordinate($latusuario, $lngusuario);
		$dist =  $coordinate1->getDistance($coordinate2, new Haversine());
		if($dist <= 3000){
			if($idlugarcategoria == 13){
				$idcategoria = 7;
			}elseif($idlugarcategoria == 18){
				$idcategoria = 8;
			}else{
				$idcategoria = 5;
			}
			$resultado[] = array("idlugar" => $idlugar,"nombre" => utf8_encode($nombrelugar),"lat" => $latlugar,"lng" => $lnglugar,"imagen" => $imagen,'dist' => $dist,'tiporegistro' => $tiporegistro,'idproveedor'=>$idproveedor,'idcategoria'=>$idcategoria);
		}
	}

	$group = array();

	foreach ( $resultado as $value ) {
    	$group[$value['nombre']][] = $value;
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
		    if(!in_array($item['nombre'], $taken)) {
    	    	$taken[] = $item['nombre'];
    		} else {
		        unset($value[$key]);
		    }
		}
		$finalarray[] = $value;
	}

	$sql2 = "SELECT lugar.idlugar,idlugarcategoria, lat, lng, tiporegistro, iditem, titulo, lugar_items.descripcion, lugar_items.imagen, precio,lugar_items.idusuario FROM lugar_items INNER JOIN lugar USING(idlugar) WHERE titulo LIKE '%$busqueda%' AND lugar_items.aprobado = 1";
	//echo $sql2; die;
	$stmt2 = $con->prepare($sql2);
	//$stmt->bind_param("i",$idciudad);
	$stmt2->execute();
	
	if($stmt2->error == ""){
		/* bind result variables */
		$stmt2->bind_result($idlugar,$idlugarcategoria,$lat,$lng,$tiporegistro,$iditem, $titulo, $descripcion, $imagen, $precio,$idusuario);
		$stmt2->store_result();
		while ($stmt2->fetch()) {
			if($idlugarcategoria == 13){
				$idcategoria = 7;
			}elseif($idlugarcategoria == 18){
				$idcategoria = 8;
			}else{
				$idcategoria = 5;
			}			
			$resultado[] = array("idlugar"=>$idlugar,"lat"=>$lat,"lng"=>$lng,"tiporegistro"=>$tiporegistro,"iditem"=>$iditem,"nombre"=>utf8_encode($titulo),"descripcion"=>utf8_encode($descripcion),"imagen"=>$imagen,"precio"=>$precio,"idusuario"=>$idusuario,"idcategoria"=>$idcategoria);
		}
	}	

	//var_dump($resultado); die();

	if(!empty($resultado)){
		echo json_encode(array("respuesta" => true, "resultados" => $resultado));
	}else{
		echo json_encode(array("respuesta" => false, "msg" => "Lo sentimos. No encontramos lo que buscaba."));
	}
}else{
	echo json_encode(array("respuesta"=>false,"msg"=>"Error en la consulta."));
}

?>