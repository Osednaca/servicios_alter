<?php

include("includes.php");
session_start();
$post_date  	 		= file_get_contents("php://input");
$data 					= json_decode($post_date);
$token 					= Auth::GetData(
    						$data->token
						);
$idusuario  			= $token->id;
$idvehiculo				= $data->idvehiculo;
$tipovehiculo			= $data->tipovehiculo;
$disponibilidad			= 1;

$stmt = $con->prepare("UPDATE usuario SET disponibilidad=?, idvehiculoactivo=?, tipovehiculoactivo=? WHERE idusuario=?");
/* bind parameters for markers */
$stmt->bind_param("iiis", $disponibilidad, $idvehiculo, $tipovehiculo, $idusuario);

/* execute query */
$stmt->execute();


$stmt2 = $con->prepare("SELECT activopara FROM usuarioubicacion WHERE idusuario=? ORDER BY fechaubicacion DESC LIMIT 1");

$stmt2->bind_param("s", $idusuario);

$stmt2->execute();

if($stmt->error == "" and $stmt2->error == ""){
	$stmt2->bind_result($activopara);
	$stmt2->fetch();

	$dat = explode(",", $activopara);
	$counnt = count($dat);
	$act = '';
	sort($dat);
	
	foreach ($dat as $key => $value) {
	    if ($value==1){
	    	$act .= " Envios";
	    }elseif($value==2){
	    	$act .= " Taxi";	        
	    }elseif($value == 4){
	    	$act .= " Compras";
	    }elseif($value == 5){
	    	$act .= " Domicilio";
	    }elseif($value == 6){
	    	$act .= " Farmacia";
	    }
	}

	$activopara = $act;
	echo utf8_encode(json_encode(array('respuesta' => true, 'activopara' => $activopara)));
}else{
	echo json_encode(array('respuesta' => false, 'error' => $stmt->error));
}

?>