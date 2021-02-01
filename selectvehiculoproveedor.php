<?php
	include("includes.php");
	
	$post_date  	  = file_get_contents("php://input");
	$data 			  = json_decode($post_date);
	$token 			  = Auth::GetData(
	    				$data->token
					  );
	$idusuario  	  = $token->id;
	$stmt = $con->prepare("SELECT idvehiculo,tipovehiculo.idtipovehiculo,tipovehiculo,marca,placa 
								FROM vehiculo 
								INNER JOIN tipovehiculo USING(idtipovehiculo)
							WHERE vehiculo.idusuario=? AND vehiculo.estatus = 1");
	$stmt->bind_param("i",$idusuario);
	$stmt->execute();
    $stmt->bind_result($idvehiculo,$idtipovehiculo,$tipovehiculo,$marca,$placa);

    $vehiculos = array();
    /* fetch values */
    while ($stmt->fetch()) {
        $vehiculos[] = array('idvehiculo'=>$idvehiculo,'idtipovehiculo' => $idtipovehiculo,'tipovehiculo'=>utf8_encode($tipovehiculo),'marca'=>utf8_encode($marca),'placa'=>$placa);
    }
	if (!empty($vehiculos)) {
		echo json_encode(array("respuesta"=>true,"vehiculo"=>$vehiculos));
	}else{
		echo json_encode(array("respuesta"=>false));
	}
?>