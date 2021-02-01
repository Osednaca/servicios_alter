<?php
	include("includes/utils.php");
	$stmt 		= $con->prepare("SELECT idbanco,nombrebanco FROM banco");
	/* execute query */
	$stmt->execute();
    /* bind result variables */
    $stmt->bind_result($idbanco, $nombrebanco);

    $data = array();
    /* fetch values */
    while ($stmt->fetch()) {
         $data[] = array('idbanco'=>$idbanco,'nombrebanco'=>utf8_encode($nombrebanco));
    }
	if (!empty($data)) {
		echo json_encode(array("respuesta"=>true,"data"=>$data));
	}else{
		echo json_encode(array("respuesta"=>false));
	}
?>