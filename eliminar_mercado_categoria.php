<?php

include("includes/utils.php");
//Para mayor seguridad validar origen (desde donde se hace el request)

$post_date  	 = file_get_contents("php://input");
$data 			 = json_decode($post_date);

$idcategoria  	 = $data->idcategoria;
$tipo 			 = $data->tipo;

if($tipo == 0){
	$tabla = "mercado_categoria";
	$campo = "idmercadocategoria = ?";
}elseif ($tipo == 1) {
	$tabla = "licores_categoria";
	$campo = "idlicorescategoria = ?";
}elseif ($tipo == 2) {
	$tabla = "comida_categoria";
	$campo = "idcomidacategoria = ?";
}


$stmt = $con->prepare("DELETE FROM $tabla WHERE idcategoria=?");
/* bind parameters for markers */
$stmt->bind_param("i", $idcategoria);
/* execute query */
$stmt->execute();

$stmt->free_result();

$sql = "DELETE FROM lugar_items WHERE $campo";

$stm1 = $con->prepare($sql);
/* b1nd parameters for markers */
$stm1->bind_param("i", $idcategoria);
/* e1ecute query */
$stm1->execute();

$stm1->free_result();

$stmt2 = $con->prepare("SELECT iditem FROM lugar_items WHERE idmercadocategoria=?");
$stmt2->bind_param("i", $idcategoria);
$stmt2->execute();
if($stmt2->error == ""){
    $stmt2->bind_result($iditem);
    //var_dump($stmt); die();
    $stmt2->store_result();
    while($stmt2->fetch()){
		$stmt3 = $con->prepare("DELETE FROM items_extras WHERE iditem= ?");
		/* bind parameters for markers */
		$stmt3->bind_param("i", $iditem);
		/* execute query */
		$stmt3->execute();
		$stmt3->free_result();
	}
}
//validar que todo salga bien con $stmt->error
if($stmt3->error==""){
	echo json_encode(array('respuesta' => true));
}else{
	echo json_encode(array('respuesta' => false,'error'=>$stmt3->error));
}

?>