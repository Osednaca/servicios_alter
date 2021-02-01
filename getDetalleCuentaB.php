<?php
	include("includes/utils.php");
	$post_date  	= file_get_contents("php://input");
	$data 			= json_decode($post_date);
	$idcuenta  		= $data->idcuenta;
	$cuentabancaria 	= array();

$stmt = $con->prepare("SELECT idcuenta,numerocuenta,idbanco,nombrebanco,tipocuenta.idtipocuenta,nombretitular,cedula
						FROM cuentabancaria
						INNER JOIN banco USING(idbanco)
						INNER JOIN tipocuenta ON tipocuenta.idtipocuenta = cuentabancaria.tipocuenta
						WHERE idcuenta=?");
/* bind parameters for markers */
$stmt->bind_param("i", $idcuenta);

/* execute query */
$stmt->execute();

if($stmt->error == ""){
	/* bind result variables */

	$stmt->bind_result($idcuenta,$numerocuenta,$idbanco,$nombrebanco,$tipocuenta,$nombretitular,$cedula);

	$stmt->fetch();
	
	$cuentabancaria = array('idcuenta'=>$idcuenta,'numerocuenta'=>$numerocuenta,'idbanco'=>$idbanco,'nombrebanco'=>utf8_encode($nombrebanco),'idtipocuenta'=>$tipocuenta,'nombretitular'=>utf8_encode($nombretitular),'cedulatitular'=>$cedula);

	$stmt->free_result();
	
	if(!empty($cuentabancaria)){
		echo json_encode(array('respuesta' => true, 'cuentabancaria'=>$cuentabancaria));
	}else{
		echo json_encode(array('respuesta' => false));
	}
}else{
	echo json_encode(array('respuesta' => false, 'mensaje' => $stmt->error));
}

?>