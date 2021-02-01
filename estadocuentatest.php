<?php
	include("includes/utils.php");
	include("funcionessaldo.php");
	session_start();
	$post_date  		= file_get_contents("php://input");
	$data 				= json_decode($post_date);
	$idusuario  		= 27;
	$user 				= array();
	$fechanow   		= date("Y-m-17 23:59:59");
	$fechaultimocorte 	= date("Y-12-1");


	$stmt = $con->prepare("SELECT  correo,usuario.cedula,nombre,nombre2,apellido,apellido2,telefonofijo,telefonocelular,direccion,idpais,idciudad,saldoalter
						FROM usuario 
						WHERE idusuario=?");

	$stmt->bind_param("i", $idusuario);

	$stmt->execute();
	
	if($stmt->error == ""){
		$stmt->bind_result($correo,$cedula,$nombre,$nombre2,$apellido,$apellido2,$telefonofijo,$telefonocelular,$direccion,$idpais,$idciudad,$saldoalter);
		$stmt->fetch();
		$user = array('idusuario'=>$idusuario,'nombre'=>utf8_encode($nombre),'apellido'=>utf8_encode($apellido),'saldoalter'=>$saldoalter);
	
		$stmt->free_result();

		//Si es fecha de corte se calcula con meta
		$saldo = calcularsaldodisponibleFechas($idusuario,false,$fechaultimocorte,$fechanow);

		if($saldo["ingresototales"] == null){
			$saldo["ingresototales"] = 0;
		}
		if($user["saldoalter"] == null){
			$user["saldoalter"] = 0;
		}
		if($saldo["ingresoxgrupo"] == null){
			$saldo["ingresoxgrupo"] = 0;
		}
		if($saldo["ingresonormal"] == null){
			$saldo["ingresonormal"] = 0;
		}

		$estadocuenta = array("nombre"=>$user["nombre"],"apellido"=>$user["apellido"],"idusuario"=>$user["idusuario"],"ingresosxgrupo"=>round($saldo["ingresoxgrupo"]),"ingresonormal"=>round($saldo["ingresonormal"]),"meta"=>$saldo["meta"],"saldoalter"=>$user["saldoalter"], "fechacorte"=>$saldo["proximocorte"], "ingresostotales" => round($saldo["ingresototales"]),'conmeta' => $saldo["conmeta"],'aporte2' => $saldo["aporte2"],"fechanow"=>$fechanow,"fechaultimocorte" => $fechaultimocorte);
	
		if(!empty($estadocuenta)){
			echo json_encode(array('respuesta' => true, 'estadocuenta'=>$estadocuenta));
		}else{
			echo json_encode(array('respuesta' => false));
		}
	}else{
		echo json_encode(array('respuesta' => false, 'mensaje' => $stmt->error));
	}
	
?>