<?php
	include("includes/utils.php");
	session_start();
	$post_date  	= file_get_contents("php://input");
	$data 			= json_decode($post_date);
	$idusuario  	= $_SESSION["idusuario"];

$stmt = $con->prepare("SELECT idcuenta,numerocuenta,idbanco,nombrebanco,tipocuenta.tipocuenta,nombretitular,cedula
						FROM cuentabancaria
						INNER JOIN banco USING(idbanco)
						INNER JOIN tipocuenta ON tipocuenta.idtipocuenta = cuentabancaria.tipocuenta
						WHERE cuentabancaria.idusuario=?");

$stmt->bind_param("i", $idusuario);

$stmt->execute();

if($stmt->error == ""){
	/* bind result variables */
	$stmt->bind_result($idcuenta,$numerocuenta,$idbanco,$nombrebanco,$tipocuenta,$nombretitular,$cedula);
	while ($stmt->fetch()) {
		$cuentas_bancarias[] = array('idcuenta' => $idcuenta,'numerocuenta' => $numerocuenta,'idbanco' => $idbanco,'nombrebanco' => utf8_encode($nombrebanco),'tipocuenta' => $tipocuenta,'nombretitular' => utf8_encode($nombretitular),'cedula' => $cedula);
	}
	
	if(!empty($cuentas_bancarias)){
		echo json_encode(array('respuesta' => true, 'cuentas_bancarias'=>$cuentas_bancarias));
	}else{
		echo json_encode(array('respuesta' => false, 'mensaje' => 'No se encontraron cuentas bancarias'));
	}
}else{
	echo json_encode(array('respuesta' => false, 'mensaje' => $stmt->error));
}

?>