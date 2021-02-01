<?php
include("includes.php");
require '../vendor/autoload.php';
use Location\Coordinate;
use Location\Polygon;

$post_date  	= file_get_contents("php://input");
$data 			= json_decode($post_date);
$idcategoria 	= $data->idcategoria;
$idlugar 		= $data->idlugar;
$idtiposervicio = $data->idtiposervicio;
$items 	= array();

//echo $idtiposervicio."//".$idcategoria;

if($idtiposervicio == 8){
	$stmt = $con->prepare("SELECT iditem, titulo, descripcion, imagen, precio, extras, personalizable, fecharegistro, idusuario FROM lugar_items WHERE idmercadocategoria = ? AND idlugar = ? AND aprobado = 1");
	$stmt->bind_param("ii",$idcategoria,$idlugar);
}elseif ($idtiposervicio == 7) {
	$stmt = $con->prepare("SELECT iditem, titulo, descripcion, imagen, precio, extras, personalizable, fecharegistro, idusuario FROM lugar_items WHERE idlicorescategoria = ? AND idlugar = ? AND aprobado = 1");
	$stmt->bind_param("ii",$idcategoria,$idlugar);
}elseif ($idtiposervicio == 5) {
	$stmt = $con->prepare("SELECT iditem, titulo, descripcion, imagen, precio, extras, personalizable, fecharegistro, idusuario FROM lugar_items WHERE idcomidacategoria = ? AND idlugar = ? AND aprobado = 1");
	$stmt->bind_param("ii",$idcategoria,$idlugar);
}
if($idcategoria == 0){
	$stmt = $con->prepare("SELECT iditem, titulo, descripcion, imagen, precio, extras, personalizable, fecharegistro, idusuario FROM lugar_items WHERE idlugar = ? AND aprobado = 1");
	$stmt->bind_param("i",$idlugar);
}
if($idcategoria == 0 AND $idtiposervicio == 8){
	$stmt = $con->prepare("SELECT iditem, titulo, descripcion, imagen, precio, extras, personalizable, fecharegistro, idusuario FROM lugar_items WHERE idlugar = ? AND aprobado = 1");
	$stmt->bind_param("i",$idlugar);
}

$stmt->execute();

if($stmt->error == ""){
	/* bind result variables */
	$stmt->bind_result($iditem, $titulo, $descripcion, $imagen, $precio, $extras, $personalizable, $fecharegistro, $idusuario);
	$stmt->store_result();
	while ($stmt->fetch()) {
		$items[] = array("iditem" =>$iditem, "titulo" => utf8_encode($titulo), "descripcion" => utf8_encode($descripcion), "imagen" => utf8_encode($imagen), "precio" => $precio, "extras" => $extras, "personalizable" => $personalizable, "fecharegistro" => $fecharegistro, "idusuario" => $idusuario, 'cantidad'=>0);
	}

	if(!empty($items)){
		echo json_encode(array("respuesta" => true, "items" => $items));
	}else{
		echo json_encode(array("respuesta" => false, "msg" => "No hay items disponibles."));
	}
}else{
	echo json_encode(array("respuesta"=>false,"msg"=>"Error en la consulta."));
}