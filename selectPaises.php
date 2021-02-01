<?php
	include("includes/utils.php");
	$stmt = $con->prepare("SELECT idpais,pais FROM pais");
	/* execute query */
	$stmt->execute();
    /* bind result variables */
    $stmt->bind_result($idpais, $pais);

    $data = array();
    /* fetch values */
    while ($stmt->fetch()) {
         $data[] = array('idpais'=>$idpais,'pais'=>$pais);
    }
	if (!empty($data)) {
		echo json_encode(array("respuesta"=>true,"data"=>$data));
	}else{
		echo json_encode(array("respuesta"=>false));
	}
?>