<?php
	include("includes/utils.php");
	$stmt = $con->prepare("SELECT idtemasoporte,tema FROM temassoporte");
	/* execute query */
	$stmt->execute();
    /* bind result variables */
    $stmt->bind_result($idtemasoporte, $tema);

    $data = array();
    /* fetch values */
    while ($stmt->fetch()) {
         $data[] = array('idtemasoporte'=>$idtemasoporte,'tema'=>utf8_encode($tema));
    }
	if (!empty($data)) {
		echo json_encode(array("respuesta"=>true,"data"=>$data));
	}else{
		echo json_encode(array("respuesta"=>false));
	}
?>