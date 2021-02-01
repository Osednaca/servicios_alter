<?php
	include("includes/utils.php");
	$stmt = $con->prepare("SELECT idlugarcategoria,categoria FROM lugar_tipo WHERE aprobado = 1");
	/* execute query */
	$stmt->execute();
    /* bind result variables */
    $stmt->bind_result($idlugarcategoria, $categoria);

    $data = array();
    /* fetch values */
    while ($stmt->fetch()) {
         $data[] = array('idlugarcategoria'=>$idlugarcategoria,'categoria'=>$categoria);
    }

	$stmt = $con->prepare("SELECT idcategoria,categoria FROM lugar_categoria WHERE aprobado = 1");
	/* execute query */
	$stmt->execute();
    /* bind result variables */
    $stmt->bind_result($idlugarcategoria, $categoria);

    $lugarcategoria = array();
    /* fetch values */
    while ($stmt->fetch()) {
         $lugarcategoria[] = array('idlugarcategoria'=>$idlugarcategoria,'categoria'=>$categoria);
    }    

	if (!empty($data)) {
		echo json_encode(array("respuesta"=>true,"lugartipo"=>$data,"lugarcategoria"=>$lugarcategoria));
	}else{
		echo json_encode(array("respuesta"=>false));
	}
?>