<?php
include("includes.php");
$post_date  	= file_get_contents("php://input");
$data 			= json_decode($post_date);
$token 			= Auth::GetData(
    				$data->token
				);
$idusuario  	= $token->id;
$dia 			= date("N");
$lugares 		= "";

if($dia >= 1 OR $dia <= 5){
	$tipohorario = 1;
}elseif($dia == 6){
	$tipohorario = 2;
}elseif($dia == 7) {
	$tipohorario = 3;
}
//echo $idusuario; die();
$sql = "SELECT lugar.idlugar, desde, tipodesde, hasta, tipohasta FROM lugar LEFT JOIN lugar_horario ON lugar.idlugar = lugar_horario.idlugar AND lugar_horario.tipohorario = $tipohorario WHERE lugar.aprobado = 1 AND lugar.idusuario = $idusuario";
$stmt = $con->prepare($sql);
$stmt->execute();

if($stmt->error == ""){
	/* bind result variables */
	$stmt->bind_result($idlugar,$desde,$tipodesde,$hasta,$tipohasta);
	$stmt->store_result();
	while ($stmt->fetch()) {
		if(date("H:i") >= date("H:i", strtotime("$desde")) AND date("H:i") < date("H:i", strtotime("$hasta")) OR $hasta < "12:00"){
			if($lugares == ""){
				$lugares = $idlugar;
			}else{
				$lugares .= ",".$idlugar;
			}
		}
	}
	echo json_encode(array("respuesta"=>true,"negocios"=>$lugares));
}else{
	echo json_encode(array("respuesta"=>false,"msg"=>"Error en la consulta."));
}
?>