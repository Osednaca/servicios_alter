<?php
	include("includes/utils.php");

    $stmt       = $con->prepare("SELECT tos FROM configuracionalter WHERE idconfiguracion=1");
    $stmt->execute();
    $stmt->bind_result($tos);
    $stmt->fetch();

if(!empty($tos)){
	echo json_encode(array('respuesta' => true, 'tos'=>utf8_encode($tos)));
}else{
	echo json_encode(array('respuesta' => false, 'mensaje' => 'Error'));
}

?>