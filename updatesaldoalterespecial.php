<?php
	/*
	Descripcion: Servicio PHP que se ejecutara con un CRON Job todos los 15 y ultimo de cada mes (Fechas de corte)
	Funcion: Calcula el valor de los servicios hechos por el usuario desde la ultima fecha de corte hasta el dia de la fecha de corte actual, este saldo se lo suma al saldo alter que esta en la base de datos y marca los servicios hechos en esa fecha como pagados.
	*/
	include_once("includes/utils.php");
	include_once("funcionessaldo.php");
	$pormeta    = select_config_alter("pormeta");
	//$fechanow   = date("Y-m-16 H:i:s");

	//Fecha Ultimo Corte
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
			//$fechacortetxt = "Semanal: El dia: ".$dia;

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
			//$fechacortetxt = "Desde $fechainicor/".date("m/Y")." Hasta $fechafincor/".date("m/Y");
			if(date("Y-m-d") == date("Y-m-").$fechainicor){
				$fechacorte       = date("Y-m-").$fechainicor;
				$fechaultimocorte = date("Y-m-", strtotime("-1 months")).$fechafincor;
			}elseif(date("Y-m-d") == date("Y-m-").$fechafincor){
				$fechacorte    	  = date("Y-m-").$fechafincor;
				$fechaultimocorte = date("Y-m-").$fechainicor;
			}elseif(date("Y-m-d") < date("Y-m-").$fechainicor){
				$fechacorte    = date("Y-m-").$fechainicor;
				$fechaultimocorte = date("Y-m-", strtotime("-1 months")).$fechafincor;
			}else{
				$fechacorte    = date("Y-m-").$fechafincor;
			}
			break;
		case '2':
			//$fechacortetxt 	  = "Desde $fechacordiames/".date("m/Y", strtotime('-1 months'))." Hasta $fechacordiames/".date("m/Y");
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

	$fechaultimocorte = "2018-04-01";
    $log = "Pago Alter ".date("Y-m-d H:i:s")." \n";

	$stmt = $con->prepare("SELECT idusuario,nombre,apellido FROM usuario WHERE estatus=1");
	$stmt->execute();
	$stmt->bind_result($idusuario,$nombre,$apellido);
	$stmt->store_result();

	while($stmt->fetch()){
		$saldodisponible = calcularsaldodisponible($idusuario,true,$fechaultimocorte);
		////Meta mensual es un porcentaje de lo que haya ganado un usuario por su grupo el mes anterior.
		$ingresosxgrupo = $saldodisponible['ingresoxgrupo'];
		$meta  = $ingresosxgrupo*$pormeta;
//
		$stmt1 = $con->prepare("UPDATE usuario SET saldoalter = saldoalter + ?, metaquincenal=? WHERE idusuario = ?");
		$stmt1->bind_param("iii", $saldodisponible["ingresototales"],$meta,$idusuario);
		$stmt1->execute();		
		$log .= "IDUsuario: ".utf8_encode($nombre)." ".utf8_encode($apellido)." Saldo: ".$saldodisponible["ingresototales"]." Ultimo corte: ".$saldodisponible["ultimocorte"]."\n";
		echo "IDUsuario: ".utf8_encode($nombre)." ".utf8_encode($apellido)." Saldo: ".$saldodisponible["ingresototales"]." Ultimo corte: ".$saldodisponible["ultimocorte"]."<br>";
		//var_dump($saldodisponible); echo "</br>";
		//Marcar que ya fueron sumados al saldo del usuario

		$stmt2 = $con->prepare("UPDATE servicio SET estatus=6 WHERE idproveedor = ? AND fecharegistro>=? AND fecharegistro<=? AND estatus IN(5,7)");
		$stmt2->bind_param("iss", $idusuario,$fechaultimocorte,$fechanow);
		$stmt2->execute();
	}
	write_log($log,"pagos_log/log_".date("Ymd").".txt");
?>