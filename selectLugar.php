<?php
include("includes.php");
	$post_date  	 		= file_get_contents("php://input");
	$data 			 		= json_decode($post_date);
	$token 					= Auth::GetData(
	     							$data->token
	 						  	);
	$idusuario 				= $token->id;
    $idlugar2                = $data->idlugar;

	$stmt = $con->prepare("SELECT idlugar,nombrelugar,idlugarcategoria FROM lugar WHERE idusuario = ?");
	$stmt->bind_param("i",$idusuario);
	/* execute query */
	$stmt->execute();
    /* bind result variables */
    $stmt->bind_result($idlugar, $nombrelugar,$idcategoria);

    $data = array();
    /* fetch values */
    while ($stmt->fetch()) {
         $data[] = array('idlugar'=>$idlugar,'lugar'=>utf8_encode($nombrelugar),'idcategoria'=>$idcategoria);
    }

	$stmt = $con->prepare("SELECT idcategoria,categoria FROM mercado_categoria WHERE idusuario = $idusuario AND idlugar = $idlugar2");
	$stmt->execute();
    /* bind result variables */
    $stmt->bind_result($idcategoria, $categoria);

    $mcategorias = array();
    /* fetch values */
    while ($stmt->fetch()) {
         $mcategorias[] = array('idcategoria'=>$idcategoria,'categoria'=>utf8_encode($categoria));
    } 

	$stmt = $con->prepare("SELECT idcategoria,categoria FROM licores_categoria WHERE idusuario = $idusuario AND idlugar = $idlugar2");
	$stmt->execute();
    /* bind result variables */
    $stmt->bind_result($idcategoria, $categoria);

    $lcategorias = array();
    /* fetch values */
    while ($stmt->fetch()) {
         $lcategorias[] = array('idcategoria'=>$idcategoria,'categoria'=>utf8_encode($categoria));
    }

    $stmt = $con->prepare("SELECT idcategoria,categoria FROM comida_categoria WHERE idusuario = $idusuario AND idlugar = $idlugar2");
    $stmt->execute();
    /* bind result variables */
    $stmt->bind_result($idcategoria, $categoria);

    $ccategorias = array();
    /* fetch values */
    while ($stmt->fetch()) {
         $ccategorias[] = array('idcategoria'=>$idcategoria,'categoria'=>utf8_encode($categoria));
    }    

	if (!empty($data)) {
		echo json_encode(array("respuesta"=>true,"lugar"=>$data,"mcategorias"=>$mcategorias,"lcategorias"=>$lcategorias,"ccategorias"=>$ccategorias));
	}else{
		echo json_encode(array("respuesta"=>false));
	}
?>