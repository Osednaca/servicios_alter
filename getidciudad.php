<?php
	include("includes/utils.php");
	$post_date = file_get_contents("php://input");
	$data 	   = json_decode($post_date);
	$ciudad    = $data->ciudad;
	//echo $ciudad; die;
	//$ciudad    = replaceAccents($ciudad);
	$stmt 	   = $con->prepare("SELECT idciudad FROM ciudad WHERE LOCATE('".utf8_decode($ciudad)."',ciudad)"); //En un futuro filtrar por idpais
	$stmt->execute();
    /* Store the result (to get properties) */
    $stmt->store_result();
    $num_rows = $stmt->num_rows;
    /* Bind the result to variables */
    $stmt->bind_result($idciudad);
	$stmt->fetch();
	$stmt->free_result();

	$stmt1 	   = $con->prepare("SELECT densidadtraficodivididopor FROM configuracionalter");
	$stmt1->execute();
	$stmt1->bind_result($ddtdp);
	$stmt1->fetch();		
	if($num_rows > 0){
		echo json_encode(array('respuesta' => true,'idciudad' => $idciudad, 'ddtdp' => $ddtdp));
	die();
	}else{
		echo json_encode(array('respuesta' => false,'mensaje' => 'No se encontro la ciudad'));
	}
?>