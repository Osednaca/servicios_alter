<?php
	include("includes.php");
	include("funcionessaldo.php");

	$post_date  	= file_get_contents("php://input");
	$data 			= json_decode($post_date);
	$token 			= Auth::GetData(
        				$data->token
    				  );
	$idusuario  	= $token->id;
	$user 			= array();

	$stmt1 = $con->prepare("SELECT  fechacorperiodicidad,fechacordiasemana,fechainicor,fechafincor,fechacordiames
						FROM configuracionalter");
	$stmt1->execute();
	if($stmt1->error == ""){
		$stmt1->bind_result($fechacorperiodicidad,$fechacordiasemana,$fechainicor,$fechafincor,$fechacordiames);
		$stmt1->fetch();
		$stmt1->free_result();
	}else{
		echo json_encode(array('respuesta' => false, 'mensaje' => $stmt1->error));
	}

	switch ($fechacorperiodicidad) {
		case '0':
			if($fechacordiasemana == 0){ $dia = "Domingo"; $day = "sunday"; }
			elseif($fechacordiasemana == 1){ $dia = "Lunes"; $day = "monday"; }
			elseif($fechacordiasemana == 2){ $dia = "Martes"; $day = "tuesday"; }
			elseif($fechacordiasemana == 3){ $dia = "Miercoles"; $day = "wednesday"; }
			elseif($fechacordiasemana == 4){ $dia = "Jueves"; $day = "thursday"; }
			elseif($fechacordiasemana == 5){ $dia = "Viernes"; $day = "friday"; }
			elseif($fechacordiasemana == 6){ $dia = "Sabado"; $day = "saturday"; }
			$fechacortetxt = "Semanal: El dia: ".$dia;

			$lastday 	 = strtotime("last $day");
			$fechaultimocorte = date("Y-m-d",$lastday);

			if(date("w") == $fechacordiasemana){
				$fechacorte  = date("Y-m-d");
			}else{
				$nextday 	 = strtotime("next $day");
				$fechacorte  = date("Y-m-d",$nextday);
			}
			break;
		case '1':
			$fechainicor = $fechainicor < 10 ? '0'.$fechainicor : $fechainicor;
			$fechafincor = $fechafincor < 10 ? '0'.$fechafincor : $fechafincor;
			$fechacortetxt = "Desde $fechainicor/".date("m/Y")." Hasta $fechafincor/".date("m/Y");
			if(date("Y-m-d") == date("Y-m-").$fechainicor){
				$fechacorte    = date("Y-m-").$fechainicor;
			}elseif(date("Y-m-d") == date("Y-m-").$fechafincor){
				$fechacorte    = date("Y-m-").$fechafincor;
			}elseif(date("Y-m-d") < date("Y-m-").$fechainicor){
				echo "test";
				$fechacorte    = date("Y-m-").$fechainicor;
			}else{
				$fechacorte    = date("Y-m-").$fechafincor;
			}
			break;
		case '2':
			$fechacortetxt 	  = "Desde $fechacordiames/".date("m/Y", strtotime('-1 months'))." Hasta $fechacordiames/".date("m/Y");
			$fechaultimocorte = date("Y-m-", strtotime("-1 months")).$fechacordiames;
			if(date("Y-m-d") == date("Y-m-").$fechacordiames){
				$fechacorte 	  = date("Y-m-d");
			}elseif(date("Y-m-d") < date("Y-m-").$fechacordiames){
				$fechacorte = date("Y-m-").$fechacordiames;
			}else{
				$fechacorte = date("Y-m-", strtotime('+1 months')).$fechacordiames;
			}
			break;
	}
	
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
		if(date('Y-m-d') == $fechacorte){ // date('Y-m-d') == $fechacorte
			$saldo = calcularsaldodisponible($idusuario,true,$fechaultimocorte);
		}else{
			$saldo = calcularsaldodisponible($idusuario,false,$fechaultimocorte);
		}

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
		if($saldo["egresos"] == null){
			$saldo["egresos"] = 0;
		}		

		$estadocuenta = array("nombre"=>$user["nombre"],"apellido"=>$user["apellido"],"idusuario"=>$user["idusuario"],"ingresosxgrupo"=>round($saldo["ingresoxgrupo"]),"ingresonormal"=>round($saldo["ingresonormal"]),"meta"=>$saldo["meta"],"saldoalter"=>round($user["saldoalter"]), "fechacorte"=>$fechacorte,"ultimocorte" => $saldo["ultimocorte"], "ingresostotales" => round($saldo["ingresototales"]),'conmeta' => $saldo["conmeta"],'aporte2' => $saldo["aporte2"],"egresos" => round($saldo["egresos"]),'fechacortetxt' => $fechacortetxt);
	
		if(!empty($estadocuenta)){
			echo json_encode(array('respuesta' => true, 'estadocuenta'=>$estadocuenta));
		}else{
			echo json_encode(array('respuesta' => false));
		}
	}else{
		echo json_encode(array('respuesta' => false, 'mensaje' => $stmt->error));
	}
	
?>