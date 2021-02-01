<?php
include("includes.php");
$post_date  	= file_get_contents("php://input");
$data 			= json_decode($post_date);
$token 			= Auth::GetData(
    				$data->token
				);
$idusuario  	= $token->id;
$lugares 		= false;

//echo $idusuario; die();
$sql = "SELECT COUNT(idlugar) FROM lugar WHERE lugar.aprobado = 0 AND lugar.idusuario = $idusuario";
$stmt = $con->prepare($sql);
$stmt->execute();

if($stmt->error == ""){
	/* bind result variables */
	$stmt->bind_result($nlugares);
	$stmt->store_result();
	while ($stmt->fetch()) {
		if($nlugares > 0){
			$lugares = true;
		}else{
			$lugares = false;
		}
	}
	echo json_encode(array("respuesta"=>true,"negocios"=>$lugares));
}else{
	echo json_encode(array("respuesta"=>false,"msg"=>"Error en la consulta."));
}
?>