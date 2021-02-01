<?php
	include("includes.php");

	$post_date  	= file_get_contents("php://input");
	$data 			= json_decode($post_date);
	$token 			= Auth::GetData(
        				$data->token
    				  );
	$idusuario  	= $token->id;
	$tipousuario	= $data->tipousuario;	
	$idciudad		= $data->idciudad;
	$latitud		= $data->latitud;
	$longitud		= $data->longitud;
	$activopara		= $data->activopara;
	$fechaubicacion = date("Y-m-d H:i:s");

$stmt = $con->prepare("INSERT INTO usuarioubicacion(idusuario, tipousuario, idciudad, latitud, longitud, fechaubicacion, activopara) VALUES (?,?,?,?,?,?,?)");
/* bind parameters for markers */
$stmt->bind_param("iiissss", $idusuario, $tipousuario, $idciudad, $latitud, $longitud,$fechaubicacion,$activopara);

/* execute query */
$stmt->execute();

if($stmt->error == ""){
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
	$activopara2 = $act;	
	echo json_encode(array('respuesta' => true, 'mensaje' => $stmt->error, 'activopara' => $activopara2, 'idtiposervicios'=>$activopara));
}else{
	echo json_encode(array('respuesta' => false, 'mensaje' => $stmt->error));
}
?>