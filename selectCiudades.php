<?php
	include("includes/utils.php");
	$post_date  = file_get_contents("php://input");
	$datarequest= json_decode($post_date);
	$idpais  	= $datarequest->idpais;
	$stmt 		= $con->prepare("SELECT idciudad,ciudad FROM ciudad WHERE idpais=?");
	/* bind parameters for markers */
	$stmt->bind_param("i", $idpais);
	/* execute query */
	$stmt->execute();
    /* bind result variables */
    $stmt->bind_result($idciudad, $ciudad);

    $data = array();
    /* fetch values */
    while ($stmt->fetch()) {
         $data[] = array('idciudad'=>$idciudad,'ciudad'=>utf8_encode($ciudad));
    }

	if (!empty($data)) {
		echo json_encode(array("respuesta"=>true,"data"=>$data));
	}else{
		echo json_encode(array("respuesta"=>false));
	}
?>