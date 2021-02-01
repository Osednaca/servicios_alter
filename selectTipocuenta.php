<?php
	include("includes/utils.php");
	$stmt 		= $con->prepare("SELECT idtipocuenta,tipocuenta FROM tipocuenta");
	/* execute query */
	$stmt->execute();
    /* bind result variables */
    $stmt->bind_result($idtipocuenta, $tipocuenta);

    $data = array();
    /* fetch values */
    while ($stmt->fetch()) {
         $data[] = array('idtipocuenta'=>$idtipocuenta,'tipocuenta'=>$tipocuenta);
    }
	if (!empty($data)) {
		echo json_encode(array("respuesta"=>true,"data"=>$data));
	}else{
		echo json_encode(array("respuesta"=>false));
	}
?>