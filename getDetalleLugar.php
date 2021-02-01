<?php

	include("includes/utils.php");

	$post_date  	= file_get_contents("php://input");

	$data 			= json_decode($post_date);

	$idlugar  		= $data->idlugar;

	$lugar_geofence 	= array();
	$categorias  		= array();
	$categoriascomida 	= array();
	$arrhorarios		= array();
	$sedes 				= array();

$stmt2 = $con->prepare("SELECT idcategoria,categoria FROM lugar_categoria");
//$stmt->bind_param("i",$idciudad);
$stmt2->execute();

if($stmt2->error == ""){
	/* bind result variables */
	$stmt2->bind_result($idcategoria, $categoria);
	$stmt2->store_result();
	while ($stmt2->fetch()) {
		$categorias[] = array('idcategoria'=>$idcategoria,'categoria'=>$categoria); 
	}
}

$stmt2 = $con->prepare("SELECT idlugarcategoria,categoria FROM lugar_tipo");
//$stmt->bind_param("i",$idciudad);
$stmt2->execute();

if($stmt2->error == ""){
	/* bind result variables */
	$stmt2->bind_result($idcategoria, $categoria);
	$stmt2->store_result();
	while ($stmt2->fetch()) {
		$lugartipo[] = array('idcategoria'=>$idcategoria,'categoria'=>$categoria); 
	}
}


$stmt = $con->prepare("SELECT idlugar, idlugarcategoria, nombrelugar, descripcion, imagen, foto_producto, direccion, ciudad, lat, lng, nombrepropietario FROM lugar WHERE idlugar = ?");

/* bind parameters for markers */

$stmt->bind_param("i", $idlugar);

/* execute query */

$stmt->execute();

if($stmt->error == ""){

	/* bind result variables */

	$stmt->bind_result($idlugar, $idlugarcategoria, $nombrelugar, $descripcion, $imagen, $foto_producto, $direccion, $ciudad, $latlugar, $lnglugar,$nombrepropietario);

	$stmt->fetch();

	$stmt->free_result();

	$stmt1 = $con->prepare("SELECT lat, lng, radio FROM lugar_geofence WHERE idlugar = ?");

	$stmt1->bind_param("i", $idlugar);

	$stmt1->execute();

	$stmt1->bind_result($lat,$lng,$radio);
	$stmt1->store_result();
	while($stmt1->fetch()){
		$lugar_geofence[] = array($lat,$lng,$radio);
	}


	$stmt1 = $con->prepare("SELECT idcategoria FROM lugarcategoria WHERE idlugar = ?");

	$stmt1->bind_param("i", $idlugar);

	$stmt1->execute();

	$stmt1->bind_result($idcategoria);
	$stmt1->store_result();
	while($stmt1->fetch()){
		$categoriascomida[] = $idcategoria;
	}	

    //Horarios                           
    $stmt2 = $con->prepare("SELECT tipohorario,desde,tipodesde,hasta,tipohasta FROM lugar_horario WHERE idlugar = $idlugar");
    $stmt2->execute();
    $stmt2->bind_result($tipohorario,$desde,$tipodesde,$hasta,$tipohasta);
    $stmt2->store_result();
    while($stmt2->fetch()){
      $arrhorarios[] = array("tipohorario" => $tipohorario,"desde" => $desde,"tipodesde" => $tipodesde,"hasta" => $hasta,"tipohasta" => $tipohasta);
    }

    //Si tiene sedes
    $stmt3 = $con->prepare("SELECT idlugar, direccion, lat, lng FROM lugar WHERE nombrelugar = '$nombrelugar' AND padre = 0");
    $stmt3->execute();
    $stmt3->bind_result($idlugar2, $direccion1, $lat, $lng);
    $stmt3->store_result();
    while($stmt3->fetch()){
    	$stmt4 = $con->prepare("SELECT telefonocelular FROM usuario WHERE idsede = '$idlugar2'");
    	$stmt4->execute();
    	$stmt4->bind_result($telefonocelular);
    	$stmt4->store_result();
    	$stmt4->fetch();
      	$sedes[] = array("idlugar" => $idlugar2,"telefono" => $telefonocelular,"direccion" => $direccion1,"lat" => $lat,"lng" => $lng);
    }

	$lugar = array('idlugar'=>$idlugar, 'tipolugar'=>$idlugarcategoria, 'nombrelugar'=>utf8_encode($nombrelugar), 'descripcion'=>utf8_encode($descripcion), 'logo'=>utf8_encode($imagen), 'foto_producto' => $foto_producto, 'direccion'=>utf8_encode($direccion), 'ciudad'=>$ciudad, 'lat'=>$latlugar, 'lng'=>$lnglugar, 'lugar_geofence'=>$lugar_geofence,'arrhorarios'=>$arrhorarios,'sedes' => $sedes,'categoriascomida' => $categoriascomida,'nombrepropietario'=>utf8_encode($nombrepropietario));

	$stmt->free_result();

	if(!empty($lugar)){

		echo json_encode(array('respuesta' => true, 'lugar'=>$lugar, 'categorias' => $categorias,'lugartipo' => $lugartipo));

	}else{

		echo json_encode(array('respuesta' => false));

	}

}else{

	echo json_encode(array('respuesta' => false, 'mensaje' => $stmt->error));

}



?>