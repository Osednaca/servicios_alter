<?php

error_reporting(E_ERROR | E_WARNING | E_PARSE);

	include("includes.php");

	$post_date  	= file_get_contents("php://input");
	$data 			= json_decode($post_date);
	$token 			= Auth::GetData(
        				$data->token
    				  );
	$idusuario  	= $token->id;
	$filtros 		= "";
	$a_params 		= array();
	$negocios 		= array();
	$categorias 	= array();
	$a_params[]     = & $idusuario;
	$tipostring 	= "s";
	$tipousuario 	= $data->tipousuario;

array_unshift($a_params,$tipostring);

if($tipousuario == 3){
	$stmt = $con->prepare("SELECT idlugar,nombrelugar,idlugarcategoria
	FROM lugar
	WHERE lugar.idusuario=?");
}else{
	$stmt = $con->prepare("SELECT idlugar,nombrelugar,idlugarcategoria
	FROM lugar
	WHERE lugar.idusuario=? AND padre = 1");
}

call_user_func_array(array($stmt, 'bind_param'), $a_params);

$stmt->execute();
$stmt->bind_result($idlugar,$nombrelugar,$idlugarcategoria);
if($stmt->error == ""){
	$stmt->store_result();	
	while ($stmt->fetch()) {
		$categorias = array();
		if($idlugarcategoria == 13){
			$sql  	 	  = "SELECT idcategoria,categoria FROM licores_categoria WHERE idusuario=? AND idlugar = ?";
			$sqlcategoria = "idlicorescategoria = ?";
			$tipocategoria= 1;
		}elseif($idlugarcategoria == 18){
			$sql 		  = "SELECT idcategoria,categoria FROM mercado_categoria WHERE idusuario=? AND idlugar = ?";
			$sqlcategoria = "idmercadocategoria = ?";
			$tipocategoria= 0;
		}else{
			$sql 		  = "SELECT idcategoria,categoria FROM comida_categoria WHERE idusuario=? AND idlugar = ?";
			$sqlcategoria = "idcomidacategoria = ?";
			$tipocategoria= 2;
		}
		$stmt1 = $con->prepare($sql);
		$stmt1->bind_param("ii",$idusuario,$idlugar);
		$stmt1->execute();
		$stmt1->store_result();
		$stmt1->bind_result($idcategoria,$categoria);
		while ($stmt1->fetch()) {
			$productos 		= array();		
			$stmt2 = $con->prepare("SELECT iditem,titulo FROM lugar_items WHERE $sqlcategoria AND idusuario=? AND idlugar=?");
			$stmt2->bind_param("iii",$idcategoria,$idusuario,$idlugar);
			$stmt2->execute();	
			$stmt2->bind_result($iditem,$titulo);
			while ($stmt2->fetch()) {
				$productos[] = array("iditem"=>$iditem,"titulo"=>utf8_encode($titulo));
			}
			$categorias[] = array("idcategoria" => $idcategoria,"categoria" => utf8_encode($categoria),'tipo'=>$tipocategoria,'productoxcategoria'=>$productos);
		}
		$negocios[] = array('idlugar'=>$idlugar,'nombrelugar'=>utf8_encode($nombrelugar),'idcategoria'=>$idlugarcategoria,'categorias'=>$categorias);
	}

	if(!empty($negocios)){
		echo json_encode(array('respuesta' => true, 'negocios'=>$negocios));
	}else{
		echo json_encode(array('respuesta' => false, 'mensaje' => 'No se encontraron negocios'));
	}
}else{
	echo json_encode(array('respuesta' => false, 'mensaje' => $stmt->error));
}
?>