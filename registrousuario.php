<?php

include("includes/utils.php");
include_once("includes/class.phpmailer.php");

$post_date  	 = file_get_contents("php://input");
$data 			 = json_decode($post_date);
$correo  		 = $data->registro->correo;
if(!empty($data->registro->codigo)){
	$codigo  	 = $data->registro->codigo;
}else{
	$codigo 	 = "";
}
$hash			 = hashSSHA($data->registro->password);
$password   	 = $hash["encrypted"];
$salt	         = $hash["salt"];
$cedula  		 = $data->registro->cedula;
$imgusuario		 = $cedula.".jpg";
$nombre  		 = utf8_decode($data->registro->nombres);
$apellido  		 = utf8_decode($data->registro->apellidos);
if(!empty($data->registro->telefonofijo)){
	$telefonofijo  	 = $data->registro->telefonofijo;
}else{
	$telefonofijo  	 = "";
}	
//$aux 			 = explode("/", $data->registro->fechanacimiento);
//$fechanacimiento = $aux[2].'-'.$aux[1].'-'.$aux[0];
$telefonocelular = $data->registro->telefonocelular;
$idpais  		 = $data->registro->idpais;
$estatus  		 = 1;
$disponibilidad  = 0;
$fecharegistro   = date("Y-m-d H:i:s");

// Si viene recomendado por alguien guardar la recomendacion
if($codigo != ""){
	//Validar que esa cedula no tenga sus 4 recomendados.
	$stmt4 = $con->prepare("SELECT count(idrecomendaciones) FROM recomendaciones WHERE idusuario = ?");
	$stmt4->bind_param("s", $codigo);
	$stmt4->execute();
	$stmt4->bind_result($nrecomendados);
	$stmt4->fetch();
	$stmt4->free_result();

	if($nrecomendados >= 4){
		echo json_encode(array('respuesta' => false, 'mensaje' => 'El usuario que te recomendo ya tiene sus 4 recomendados llenos.'));
		die();
	}else{
		$nombrecompleto = $apellido." ".$nombre;
		$stmt3 = $con->prepare("INSERT INTO recomendaciones(idusuario, cedula, nombre, telefono, correo, estatus, fecharecomendacion) VALUES (?,?,?,?,?,1,?)");
		$stmt3->bind_param("ssssss",$codigo, $cedula,$nombrecompleto,$telefonocelular,$correo,$fecharegistro);
		
		$stmt3->execute();
		//validar que todo salga bien con $stmt->error	
		if($stmt3->error != ""){
			echo json_encode(array('respuesta' => false,'mensaje'=> 'Error en el sistema. Un administrador se pondra en contacto con usted.'));
			reporte_error("","",$stmt3->error,"registrousuario.php","");		
			die();
		}
	}
}

//$stmt1 = $con->prepare("SELECT tiempolimiterut FROM configuracionalter WHERE idconfiguracion = 1");
//$stmt1->execute();
//$stmt1->bind_result($tiempolimiterut);
//$stmt1->fetch();
//$stmt1->free_result();

//$fechaaux   = date('Y-m-d');
//$fechalimiterut = strtotime ( $tiempolimiterut , strtotime ( $fechaaux ) ) ;
//$fechalimiterut = date ( 'Y-m-d' , $fechalimiterut );

		// SI todo esta bien registra la cuenta bancaria.
		//if(!empty($data->numerocuenta)){
		//	$numerocuenta 	= $data->numerocuenta;
		//	$idbanco	  	= $data->idbanco;
		//	$tipocuenta	  	= $data->idtipocuenta;
		//	$nombretitular	= $data->nombretitular;
		//	$cedulatitular 	= $data->cedulatitular;
		//	$estatus	  	= 1;
		//	$stmt = $con->prepare("INSERT INTO cuentabancaria(numerocuenta, idbanco, tipocuenta, nombretitular, cedula, estatus, //fecharegistrocuenta) VALUES (?,?,?,?,?,?,now())");
		//		/* bind parameters for markers */
		//		$stmt->bind_param("siissi", $numerocuenta, $idbanco, $tipocuenta, $nombretitular, $cedulatitular, $estatus);
		//		/* execute query */
		//		$stmt->execute();
		//
		//		$idcuentabancaria = $stmt->insert_id;
		//}else{
		//	$idcuentabancaria = 0;
		//}

$stmt2 = $con->prepare("INSERT INTO usuario(idusuario,correo, password, salt, cedula, nombre, apellido, telefonocelular, idpais,estatus, disponibilidad, fecharegistro,tipousuario,metaquincenal,saldoalter,cumplemeta,imgusuario) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,1,0,0,2,?)");
	
$stmt2->bind_param("isssssssiiiss",$cedula,$correo,$password,$salt,$cedula,$nombre,$apellido,$telefonocelular,$idpais,$estatus,$disponibilidad,$fecharegistro,$imgusuario);
		
$stmt2->execute();
//validar que todo salga bien con $stmt->error	
if($stmt2->error ==""){
	//Enviar Correo de Bienvenida
	$mail = new PHPMailer;
	$mail->CharSet = "UTF-8";
	$mail->From = "alter@finespublicidad.com";
	$mail->FromName = "Alter";
	
	$mail->addAddress($correo);
		
	$mail->Subject = "Bienvenido a Alter";
	$mail->Body = html_entity_decode("<div style='width: 100%; height: auto;'><div style='width: 100%; float: left; background: #0b2d3f; height: auto;'><div style='width: 200px; float: left; padding: 10px; padding-left: 40px;'><img src='http://finespublicidad.com/serviciosalter/media/img/logo_mailing_alter_w.png' width='100%'; height='auto;' /></div><div style='float: right; text-align: right; padding: 10px; padding-top: 60px; padding-right: 40px;'><a href='#' style='text-decoration: none; font-size: 12px; color: white; font-family: arial;'>info@alterclub.com</a></div></div><div style='width: 100%; height: 10px; background: #36a1db; float: left;'></div><div style='width: 90%; height: auto; float: left; padding: 5%; font-family: arial; color: grey; text-align: justify;'><p><h1>Hola,</h1></p><p>Ya te encuentras registrado en la Plataforma ALTER. A partir de este momento eres un empresario ALTER que puede generar grandes ingresos si te lo propones.</p><p>Invita a tus amigos CLAVE para que hagan parte del club.</p><p>Bienvenido</p><p><h3>Cordialmente,</h3></p><p><h4>El Equipo Alter</h4></p></div><div style='width: 100%; height: auto; background: #36a1db; float: left;' ><p style='color: white; font-family: arial; padding-left: 40px; padding-right: 40px; padding-bottom: 20px; padding-top: 10px;'><a href='https://itunes.apple.com/WebObjects/MZStore.woa/wa/viewSoftware?id=1267852082&mt=8'><img src='http://finespublicidad.com/serviciosalter/media/img/app_store.png' width='150px' height='auto' style='float: right; padding-bottom: 10px;'/></a><a href='https://play.google.com/store/apps/details?id=com.alter.alterclub'><img src='http://finespublicidad.com/serviciosalter/media/img/android.png' width='150px' height='auto' style='float: right; margin-right: 10px; padding-bottom: 10px;' /></a>Alter 2016 - Todos los derechos reservados</br>Descarga nuestra APP, &uacute;nete al Club</p></div></div>\r\n.");
	$mail->IsHTML(true);
	//if(!$mail->send()) 
	//{
	//	echo json_encode(array('respuesta' => false, 'mensaje' => 'Hubo un error.'));
	//}
	echo json_encode(array('respuesta' => true));
}else{
	echo json_encode(array('respuesta' => false,'mensaje'=> 'Error en el sistema. Un administrador se pondra en contacto con usted.'));
	reporte_error("","",$stmt2->error,"registrousuario.php","");
}
?>