<?php
	/*
	Descripcion: Se ejecuta con un CRON Job todos los 15 y ultimo de cada mes (Fechas de corte)
	*/
	include_once("includes/utils.php");
	include("includes/class.phpmailer.php");
	include("lib/nusoap.php");

	function enviacorreo($correo,$total){
		$mail = new PHPMailer;
		$mail->From = "alter@finespublicidad.com";
		$mail->FromName = "Alter";
		$mail->addAddress($correo);
		$mail->Subject = "Alter | Problema con pago";
		$mail->Body = html_entity_decode("<div style='width: 100%; height: auto;'><div style='width: 100%; float: left; background: #0b2d3f; height: auto;'><div style='width: 200px; float: left; padding: 10px; padding-left: 40px;'><img src='http://finespublicidad.com/serviciosalter/media/img/logo_mailing_alter_w.png' width='100%'; height='auto;' /></div><div style='float: right; text-align: right; padding: 10px; padding-top: 60px; padding-right: 40px;'><a href='#' style='text-decoration: none; font-size: 12px; color: white; font-family: arial;'>info@alterclub.com</a></div></div><div style='width: 100%; height: 10px; background: #36a1db; float: left;'></div><div style='width: 90%; height: auto; float: left; padding: 5%; font-family: arial; color: grey; text-align: justify;'><p><h1>Hola,</h1></p><p>Lamentamos informarte que a la fecha tienes un saldo pendiente de pago de $ $total (COL), este pago fue tratado de debitar de tu cuenta y no fue posible, te invitamos a que revises tus medios de pago o a&ntilde;adas saldo ALTER para poder cubrir esta mora, recuerda que este valor corresponde a Proveedores que ya facilitaron sus servicios y necesitan recibir su dinero.</p><p>Esperamos que puedas poner al d&iacute;a prontamente esta deuda.</p><p>Esta notificación consta de 15 d&iacute;as de mora, si sobrepasas los 30 d&iacute;as ser&aacute;s dado de baja en la plataforma y tus cuentas pendientes ser&aacute;n entregadas a una entidad externa de cobranza para que sean conciliadas con los diferentes acreedores directamente.</p><p><h3>Cordialmente,</h3></p><p><h4>El Equipo Alter</h4></p></div><div style='width: 100%; height: auto; background: #36a1db; float: left;' ><p style='color: white; font-family: arial; padding-left: 40px; padding-right: 40px; padding-bottom: 20px; padding-top: 10px;'><img src='http://finespublicidad.com/serviciosalter/media/img/app_store.png' width='150px' height='auto' style='float: right; padding-bottom: 10px;' /><img src='http://finespublicidad.com/serviciosalter/media/img/android.png' width='150px' height='auto' style='float: right; margin-right: 10px; padding-bottom: 10px;' />Alter 2016 - Todos los derechos reservados</br>Descarga nuestra APP, &uacute;nete al Club</p></div></div>\r\n.");

		$mail->IsHTML(true);

		if(!$mail->send()) 
		{
			return true;
		}
	}

	$fechanow   = date("Y-m-d H:i:s");
	if(date('d') <= 15){
		$m = date('m')-1;
	    $fechaultimocorte = date("Y-".$m."-t");
	}
	if(date('d') <= date('t') && date('d') > 15){
	    $fechaultimocorte = date("Y-m-15");
	}
	//Seleccionar todos los usuarios de la base de datos
	$stmt = $con->prepare("SELECT idusuario FROM usuario WHERE estatus = 4");
	$stmt->execute();
	$stmt->bind_result($idusuario);
	while($stmt->fetch()){
		$stmt->store_result();
		//Servicios Como Cliente
		$stmt1 = $con->prepare("SELECT SUM(valor) FROM servicio WHERE idcliente = ? AND estatus=5 AND fecharegistro>=? AND fecharegistro<=?");
		$stmt1->bind_param("iss", $idusuario,$fechaultimocorte,$fechanow);
		$stmt1->execute();
		$stmt1->bind_result($servicioshechos);
		$stmt1->fetch();
		$stmt1->free_result();
		if($servicioshechos > 0){
			//Buscar datos del usuario y de la TC
			$stmt4 = $con->prepare("SELECT correo,cedula,apellido,apellido2,nombre,nombre2,direccion,telefonocelular,ciudad,tokenelp,idalmacenelp FROM usuario INNER JOIN tarjetasusuario USING(idusuario) INNER JOIN ciudad USING(idciudad) WHERE usuario.idusuario = ? AND tarjetasusuario.estatus = 2");
			$stmt4->bind_param("i", $idusuario);
			$stmt4->execute();
			$stmt4->bind_result($correo,$cedula,$apellido,$apellido2,$nombre,$nombre2,$direccion,$telefono,$ciudad,$tokenelp,$idalmacenelp);
			$stmt4->fetch();
			$stmt4->free_result();
			//echo "$correo,$cedula,$apellido,$apellido2,$nombre,$nombre2,$direccion,$telefono,$ciudad,$tokenelp,".$_SERVER['REMOTE_ADDR'].",$idalmacenelp"; die();		

			//Consultar Saldo Alter
			$stmt2 = $con->prepare("SELECT saldoalter FROM usuario WHERE idusuario = ?");
			$stmt2->bind_param("i", $idusuario);
			$stmt2->execute();
			$stmt2->bind_result($saldoalter);
			$stmt2->fetch();
			$stmt2->free_result();
			//Validar que tenga suficiente saldo
			if($saldoalter > 0){
				if($saldoalter >= $servicioshechos){
					//Pagar con saldo alter
					$stmt3 = $con->prepare("UPDATE usuario SET saldoalter = saldoalter - ?, estatus = 1 WHERE idusuario = ?");
					$stmt3->bind_param("ii", $servicioshechos,$idusuario);
					$stmt3->execute();
					$stmt3->free_result();
				}else{
					//Se cobra parte con el saldo alter y lo demas con la tarjeta
					$servicioshechos = $servicioshechos - $saldoalter;
					$stmt3 = $con->prepare("UPDATE usuario SET saldoalter = 0 WHERE idusuario = ?");
					$stmt3->bind_param("i", $idusuario);
					$stmt3->execute();
					$stmt3->free_result();

					if(!empty($tokenelp)){
						//IVA y Total
						$iva   = 0; //$servicioshechos*0.19;
						$total = $servicioshechos+$iva;
						$pais  = "COL"; //Por los momentos Colombia
						$moneda= "COP"; //Por los momentos Pesos Colombianos
						//echo "$servicioshechos"; die();
						$wsdl	= "https://www.enlineapagos.com/secure/webservices/Almacenamiento.do?wsdl"; 
						$client = new nusoap_client($wsdl, true);
						// Parámetros de la transacción
						$Params=array('usuario'=>'FINESXPR','clave'=>'124830','llavemd5'=>'f0e377101fb213fa60f0ea081383ab48','id_almacenamiento'=>$idalmacenelp,'token'=>$tokenelp,'nombres'=>$nombre.' '.$nombre2,'apellidos'=>$apellido.' '.$apellido2,'tipodedocumentoidentidad'=>'cc','numerodedocumentoidentidad'=>$cedula,'cuotas'=>"01",'direccion'=>$direccion,'codigopostal'=>'0005','pais'=>$pais,'ciudad'=>$ciudad,'telefono'	=>$telefono,'email'=>$correo,'ip'=>$_SERVER['REMOTE_ADDR'],'moneda'=>$moneda,'valor'=>(string)$total,'iva'=>(string)$iva,'baseiva'=>$servicioshechos,'descripcionpago'=>'Pago de Servicios Corte '.$fechaultimocorte,"extra1" => "1234","extra2" => "","extra3" => ""); 
						// Llamamos el Método
						$response=$client->call('Procesar_Almacenamiento', $Params); 

						if($response["respuesta"] == "aprobada"){
							echo "Cobro Realizado con exito.";
							$stmt3 = $con->prepare("UPDATE usuario SET estatus = 1 WHERE idusuario = ?");
							$stmt3->bind_param("i", $idusuario);
							$stmt3->execute();
							$stmt3->free_result();
						}elseif($response["respuesta"] == "rechazada" OR $response["respuesta"] == "error"){
							//Enviar Correo y Bloquear Temporalmente al usuario 
							enviacorreo($correo,$total);
							$stmt5 = $con->prepare("UPDATE usuario SET estatus = 2 WHERE idusuario = ?");
							$stmt5->bind_param("i", $idusuario);
							$stmt5->execute();
							$stmt5->free_result();
						}

					}else{
						//No tiene tarjeta registrada
						//Enviar Correo y Bloquear Temporalmente al usuario
						enviacorreo($correo,$total);
						$stmt5 = $con->prepare("UPDATE usuario SET estatus = 2 WHERE idusuario = ?");
						$stmt5->bind_param("i", $idusuario);
						$stmt5->execute();
						$stmt5->free_result();			
					}
				}
			}else{
				// SI NO TIENE SALDO SE PAGA CON Tarjeta de Credito
				if(!empty($tokenelp)){
					//IVA y Total
					$iva   = 0; //$servicioshechos*0.19;
					$total = $servicioshechos+$iva;
					$pais  = "COL"; //Por los momentos Colombia
					$moneda= "COP"; //Por los momentos Pesos Colombianos
					//echo "$servicioshechos"; die();
					$wsdl	= "https://www.enlineapagos.com/secure/webservices/Almacenamiento.do?wsdl"; 
					$client = new nusoap_client($wsdl, true);
					// Parámetros de la transacción
					$Params=array('usuario'=>'FINESXPR','clave'=>'124830','llavemd5'=>'f0e377101fb213fa60f0ea081383ab48','id_almacenamiento'=>$idalmacenelp,'token'=>$tokenelp,'nombres'=>$nombre.' '.$nombre2,'apellidos'=>$apellido.' '.$apellido2,'tipodedocumentoidentidad'=>'cc','numerodedocumentoidentidad'=>$cedula,'cuotas'=>"01",'direccion'=>$direccion,'codigopostal'=>'0005','pais'=>$pais,'ciudad'=>$ciudad,'telefono'	=>$telefono,'email'=>$correo,'ip'=>$_SERVER['REMOTE_ADDR'],'moneda'=>$moneda,'valor'=>(string)$total,'iva'=>(string)$iva,'baseiva'=>$servicioshechos,'descripcionpago'=>'Pago de Servicios Corte '.$fechaultimocorte,"extra1" => "1234","extra2" => "","extra3" => ""); 
					//var_dump($Params); die();
					// Llamamos el Método
					$response=$client->call('Procesar_Almacenamiento', $Params); 

					if($response["respuesta"] == "aprobada"){
						echo "Cobro Realizado con exito.";
						$stmt3 = $con->prepare("UPDATE usuario SET estatus = 1 WHERE idusuario = ?");
						$stmt3->bind_param("i", $idusuario);
						$stmt3->execute();
						$stmt3->free_result();
					}elseif($response["respuesta"] == "rechazada" OR $response["respuesta"] == "error"){
						//Sin saldo Rechazo la tarjeta.
						//Se le manda un correo al dia siguiente 16 diciendole que solucione. Se bloquea temporalmente (no puede pedir ni prestar servicios) y al pagar (solucionar) se desbloquea automaticamente.
						enviacorreo($correo,$total);
						$stmt5 = $con->prepare("UPDATE usuario SET estatus = 2 WHERE idusuario = ?");
						$stmt5->bind_param("i", $idusuario);
						$stmt5->execute();
						$stmt5->free_result();
					}
				}else{
					//No tiene saldo en la cuenta ni tiene tarjeta registrada
					//Se le manda un correo al dia siguiente 16 diciendole que solucione. Se bloquea temporalmente (no puede pedir ni prestar servicios) y al pagar (solucionar) se desbloquea automaticamente.
					enviacorreo($correo,$total);
					$stmt5 = $con->prepare("UPDATE usuario SET estatus = 2 WHERE idusuario = ?");
					$stmt5->bind_param("i", $idusuario);
					$stmt5->execute();
					$stmt5->free_result();		
				}
			}
		}else{
			echo "Cobro Realizado con exito.";
			$stmt3 = $con->prepare("UPDATE usuario SET estatus = 1 WHERE idusuario = ?");
			$stmt3->bind_param("i", $idusuario);
			$stmt3->execute();
			$stmt3->free_result();		
		}
	}
?>