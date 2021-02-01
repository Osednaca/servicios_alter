<?php

include("includes/utils.php");
include_once("includes/class.phpmailer.php");

$post_date  	 = file_get_contents("php://input");
$data 			 = json_decode($post_date);
$correo  		 = $data->email;
$hash			 = hashSSHA($data->password);
$password   	 = $hash["encrypted"];
$salt	         = $hash["salt"];
$cedula  		 = $data->cedula;
$nombre  		 = utf8_decode($data->nombre);
if(!empty($data->nombre2)){
	$nombre2  	 = utf8_decode($data->nombre2);
}else{
	$nombre2  	 = "";
}
$apellido  		 = utf8_decode($data->apellido);
if(!empty($data->apellido2)){
	$apellido2   = utf8_decode($data->apellido2);
}else{
	$apellido2   = "";
}
$estatus  		 = 1;
$disponibilidad  = 0;
$fecharegistro   = date("Y-m-d H:i:s");

$stmt1 = $con->prepare("SELECT tiempolimiterut FROM configuracionalter WHERE idconfiguracion = 1");
$stmt1->execute();
$stmt1->bind_result($tiempolimiterut);
$stmt1->fetch();
$stmt1->free_result();

$fechaaux   = date('Y-m-d');
$fechalimiterut = strtotime ( $tiempolimiterut , strtotime ( $fechaaux ) ) ;
$fechalimiterut = date ( 'Y-m-d' , $fechalimiterut );

//Validar que el email no este utilizado por otra persona (Deberia validar tambien la Cedula ???)
$stmt1 = $con->prepare("SELECT idusuario FROM usuario WHERE correo = ?");
$stmt1->bind_param("s", $correo);
$stmt1->execute();
$stmt1->bind_result($idusuario);
$stmt1->fetch();
$stmt1->free_result();
//Si no existe ninguno guarda el registro en BD
if($idusuario==""){
	//Validar que la cedula no este registrada
	$stmt1 = $con->prepare("SELECT idusuario FROM usuario WHERE cedula = ?");
	$stmt1->bind_param("s", $cedula);
	$stmt1->execute();
	$stmt1->bind_result($idusuario2);
	$stmt1->fetch();
	$stmt1->free_result();
	if($idusuario2==""){
		$stmt2 = $con->prepare("INSERT INTO usuario(idusuario,correo, password, salt, cedula, nombre, nombre2, apellido, apellido2,estatus, disponibilidad, fecharegistro,tipousuario,limiterut,metaquincenal,saldoalter,cumplemeta) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,1,?,0,0,2)");
	
		$stmt2->bind_param("sssssssssiiss",$cedula,$correo,$password,$salt,$cedula,$nombre,$nombre2,$apellido,$apellido2,$estatus,$disponibilidad,$fecharegistro,$fechalimiterut);
		
		$stmt2->execute();
		//validar que todo salga bien con $stmt->error	
		if($stmt2->error ==""){
			//Cambiar estatus de recomendacion a Registrada (1)
			$stmt3 = $con->prepare("UPDATE recomendaciones SET estatus = 1 WHERE cedula = ?");
			$stmt3->bind_param("s", $cedula);
			$stmt3->execute();
			if($stmt3->error ==""){
					//Enviar Correo de Bienvenida
				$mail = new PHPMailer;
				$mail->CharSet = "UTF-8";
				$mail->From = "alter@finespublicidad.com";
				$mail->FromName = "Alter";
				
				$mail->addAddress($correo);
					
				$mail->Subject = "Bienvenido a Alter";
				$mail->Body = html_entity_decode("<div style='width: 100%; height: auto;'><div style='width: 100%; float: left; background: #0b2d3f; height: auto;'><div style='width: 200px; float: left; padding: 10px; padding-left: 40px;'><img src='http://finespublicidad.com/serviciosalter/media/img/logo_mailing_alter_w.png' width='100%'; height='auto;' /></div><div style='float: right; text-align: right; padding: 10px; padding-top: 60px; padding-right: 40px;'><a href='#' style='text-decoration: none; font-size: 12px; color: white; font-family: arial;'>info@alterclub.com</a></div></div><div style='width: 100%; height: 10px; background: #36a1db; float: left;'></div><div style='width: 90%; height: auto; float: left; padding: 5%; font-family: arial; color: grey; text-align: justify;'><p><h1>Hola,</h1></p><p>Ya te encuentras registrado en la Plataforma ALTER. A partir de este momento eres un empresario ALTER que puede generar grandes ingresos si te lo propones.</p><p>Invita a tus amigos CLAVE para que hagan parte del club.</p><p>Bienvenido</p><p><h3>Cordialmente,</h3></p><p><h4>El Equipo Alter</h4></p></div><div style='width: 100%; height: auto; background: #36a1db; float: left;' ><p style='color: white; font-family: arial; padding-left: 40px; padding-right: 40px; padding-bottom: 20px; padding-top: 10px;'><a href='https://itunes.apple.com/WebObjects/MZStore.woa/wa/viewSoftware?id=1267852082&mt=8'><img src='http://finespublicidad.com/serviciosalter/media/img/app_store.png' width='150px' height='auto' style='float: right; padding-bottom: 10px;'/></a><a href='https://play.google.com/store/apps/details?id=com.alter.alterclub'><img src='http://finespublicidad.com/serviciosalter/media/img/android.png' width='150px' height='auto' style='float: right; margin-right: 10px; padding-bottom: 10px;' /></a>Alter 2016 - Todos los derechos reservados</br>Descarga nuestra APP, &uacute;nete al Club</p></div></div>\r\n.");
				$mail->IsHTML(true);
				if(!$mail->send()) 
				{
					echo json_encode(array('respuesta' => true, 'mensaje' => 'Hubo un error enviando el mensaje.'));
					die();
				}
				echo json_encode(array('respuesta' => true));
			}else{
				echo json_encode(array('respuesta' => false,'mensaje'=> $stmt3->error));
			}
		}else{
			echo json_encode(array('respuesta' => false,'mensaje'=> 'Error en el sistema. Un administrador se pondra en contacto con usted.'));
			$sql = "INSERT INTO usuario(idusuario,correo, password, salt, cedula, nombre, nombre2, apellido, apellido2,estatus, disponibilidad, fecharegistro,tipousuario,limiterut,metaquincenal,saldoalter,cumplemeta) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,1,?,0,0,2)";
			reporte_error("",$correo,$stmt2->error,"registrousuariosimple.php",$sql);
		}
	}else{
		// Sino muestra un error
		echo json_encode(array('respuesta' => false,'mensaje'=>'La cedula ya se encuentra registrada en la plataforma'));
	}			
}else{
	// Sino muestra un error
	echo json_encode(array('respuesta' => false,'mensaje'=>'Error: El correo ya se encuentra registrado en la plataforma'));
}
?>