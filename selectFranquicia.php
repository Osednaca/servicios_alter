<?php
	include("includes/utils.php");
	$stmt = $con->prepare("SELECT idfranquicia,franquicia FROM franquicia");
	/* execute query */
	$stmt->execute();
    /* bind result variables */
    $stmt->bind_result($idfranquicia, $franquicia);

    $data = array();
    /* fetch values */
    while ($stmt->fetch()) {
         $data[] = array('idfranquicia'=>$idfranquicia,'franquicia'=>$franquicia);
    }

	if (!empty($data)) {
		echo json_encode(array("respuesta"=>true,"franquicia"=>$data));
	}else{
		echo json_encode(array("respuesta"=>false));
	}
?>