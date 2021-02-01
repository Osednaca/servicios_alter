<?php

include("includes/utils.php");

$post_date  	   = file_get_contents("php://input");

$data 			   = json_decode($post_date);

$idlugar    	   = $data->idlugar;

$idtiposervicio    = $data->idtiposervicio;

$items 	= array();
$aprobado = 1;

$categorias = array();

$sql  = "SELECT porcobroproducto FROM lugar WHERE idlugar = ? OR idlugar = (SELECT idlugar FROM lugar WHERE nombrelugar = (SELECT nombrelugar FROM lugar WHERE idlugar = ?) AND padre = 1) AND aprobado = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("iii",$idlugar,$idlugar,$aprobado);
//echo $sql; die();
$stmt->execute();
$stmt->bind_result($porcobroproducto);
$stmt->store_result();
$stmt->fetch();

if($idtiposervicio == 5){
	$tipo = 1;
}elseif ($idtiposervicio == 7) {
	$tipo = 2;
}
//echo $idlugar;

$sql  = "SELECT iditem, titulo, descripcion, imagen, precio,personalizable,extras,idusuario FROM lugar_items WHERE (idlugar = ? OR idlugar IN(SELECT idlugar FROM lugar WHERE nombrelugar = (SELECT nombrelugar FROM lugar WHERE idlugar = ?) AND padre = 1)) AND aprobado = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("iii",$idlugar,$idlugar,$aprobado);
//echo $sql; die();
$stmt->execute();

if($stmt->error == ""){

	/* bind result variables */

	$stmt->bind_result($iditem, $titulo, $descripcion, $imagen, $precio,$personalizable,$extras,$idusuario);

	$stmt->store_result();
	//var_dump($porcobroproducto);
	while ($stmt->fetch()) {

		//$stmt->free_result()

		$porcentaje = $precio * $porcobroproducto;

		$precio = $precio + $porcentaje;

		$items[] = array('iditem'=>$iditem,'titulo'=>utf8_encode($titulo),'descripcion'=>utf8_encode($descripcion),'imagen'=>utf8_encode($imagen),'precio'=>$precio,"personalizable"=>$personalizable,"extras"=>$extras, "cantidad" => 0);

	}

	if($idtiposervicio == 8){
		$stmt = $con->prepare("SELECT idcategoria, categoria
							FROM mercado_categoria WHERE idlugar = $idlugar ORDER BY categoria ASC");
	}elseif($idtiposervicio == 7){
		$stmt = $con->prepare("SELECT idcategoria, categoria
							FROM licores_categoria WHERE idlugar = $idlugar ORDER BY categoria ASC");
	}elseif ($idtiposervicio == 5) {
		$stmt = $con->prepare("SELECT idcategoria, categoria
							FROM comida_categoria WHERE idlugar = $idlugar OR idlugar IN(SELECT idlugar FROM lugar WHERE nombrelugar = (SELECT nombrelugar FROM lugar WHERE idlugar = $idlugar)) ORDER BY categoria ASC");
	}
	$stmt->execute();
	
	if($stmt->error == ""){
		/* bind result variables */
		$stmt->bind_result($idcategoria, $categoria);
	
		$stmt->store_result();
	
		while ($stmt->fetch()) {	
			$categorias[] = array("idcategoria" => $idcategoria,"categoria" => utf8_encode($categoria));
		}
	}
	if(count($items) > 0){

		echo json_encode(array('respuesta' => true, 'items' => $items, 'categorias' => $categorias));

	}else{

		echo json_encode(array('respuesta' => false, 'msg' => 'No se encontraron items.','categorias' => $categorias));

	}

}else{

	echo json_encode(array('respuesta' => false, 'msg' => $stmt->error));

}



?>