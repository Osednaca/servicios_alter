<?php
include("includes.php");
	$post_date  	 		= file_get_contents("php://input");
	$data 			 		= json_decode($post_date);
	$token 					= Auth::GetData(
	     							$data->token
	 						  	);
	$idusuario 				= 	$token->id;

	$stmt = $con->prepare("SELECT iditem,titulo FROM lugar_items WHERE idusuario = ? AND idmercadocategoria IS NULL");
	$stmt->bind_param("i",$idusuario);
	/* execute query */
	$stmt->execute();
    /* bind result variables */
    $stmt->bind_result($iditem, $titulo);

    $data = array();
    /* fetch values */
    while ($stmt->fetch()) {
         $data[] = array('iditem'=>$iditem,'item'=>utf8_encode($titulo));
    }

	if (!empty($data)) {
		echo json_encode(array("respuesta"=>true,"item"=>$data));
	}else{
		echo json_encode(array("respuesta"=>false));
	}
?>