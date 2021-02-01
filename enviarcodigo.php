<?php
include("includes/utils.php");
include("includes/class.phpmailer.php");
header('Access-Control-Allow-Origin: *');

$post_date  		=  file_get_contents("php://input");
$data 				=  json_decode($post_date);
$email 				=  $data->mail;
$cedula 			=  $data->cedula;
$codigoactivacion	=  rand(1000,5000);
$fecharegistro 		=  date("Y-m-d H:i:s");

// Validar que el correo no este siendo utilizado por otro usuario
$stmt = $con->prepare("SELECT idusuario FROM usuario WHERE correo = ? AND estatus=1");

$stmt->bind_param("s",$email);

$stmt->execute();

$stmt->bind_result($idusuario);

$stmt->fetch();

$stmt->free_result();

if($idusuario ==""){
	// Validar un maximo de envios por dia (3 es un buen numero)
	$stmt1 = $con->prepare("SELECT count(*) FROM activacionregistro WHERE cedula = ? AND estatus=0 AND date(fechaenvio)='".date('Y-m-d')."'");
	
	$stmt1->bind_param("s",$cedula);
	
	$stmt1->execute();
	
	$stmt1->bind_result($numenvios);
	
	$stmt1->fetch();
	
	$stmt1->free_result();
	
	if($numenvios < 3){
		$mail = new PHPMailer;
		$mail->CharSet = "UTF-8";
		$mail->From = "alter@finespublicidad.com";
		$mail->FromName = "Alter";
		
		$mail->addAddress($email);
			
		$mail->Subject = "Registro | Verificacion";

		$mail->Body = html_entity_decode("<div style='width: 100%; height: auto;'><div style='width: 100%; float: left; background: #0b2d3f; height: auto;'><div style='width: 200px; float: left; padding: 10px; padding-left: 40px;'><img src='http://finespublicidad.com/serviciosalter/media/img/logo_mailing_alter_w.png' width='100%'; height='auto;' /></div><div style='float: right; text-align: right; padding: 10px; padding-top: 60px; padding-right: 40px;'><a href='#' style='text-decoration: none; font-size: 12px; color: white; font-family: arial;'>info@alterclub.com</a></div></div><div style='width: 100%; height: 10px; background: #36a1db; float: left;'></div><div style='width: 90%; height: auto; float: left; padding: 5%; font-family: arial; color: grey; text-align: justify;'><p><h1>Hola,</h1></p><p>T&uacute; c&oacute;digo de acceso para registrarte en la plataforma ALTER es <b>$codigoactivacion</b>, c&oacute;pialo y p&eacute;galo para que puedas terminar tu proceso.</p><p><h3>Cordialmente,</h3></p><p><h4>El Equipo Alter</h4></p></div><div style='width: 100%; height: auto; background: #36a1db; float: left;' ><p style='color: white; font-family: arial; padding-left: 40px; padding-right: 40px; padding-bottom: 20px; padding-top: 10px;'><img src='http://finespublicidad.com/serviciosalter/media/img/app_store.png' width='150px' height='auto' style='float: right; padding-bottom: 10px;' /><img src='http://finespublicidad.com/serviciosalter/media/img/android.png' width='150px' height='auto' style='float: right; margin-right: 10px; padding-bottom: 10px;' />Alter 2016 - Todos los derechos reservados</br>Descarga nuestra APP, &uacute;nete al Club</p></div></div>\r\n.");

		$mail->IsHTML(true);

		if(!$mail->send()) 
		{
		    echo json_encode(array('respuesta' => false, 'mensaje' => 'Hubo un error.'));
		} 
		else 
		{		
			$stmt2 = $con->prepare("INSERT INTO activacionregistro(cedula, codigoactivacion, fechaenvio,estatus) VALUES (?,?,?,0)");
		
			$stmt2->bind_param("sss",$cedula,$codigoactivacion,$fecharegistro);
		
			$stmt2->execute();
			//validar que todo salga bien con $stmt->error
			if($stmt2->error == ""){
				echo json_encode(array('respuesta' => true,'mensaje' => 'Código enviado.'));    
			}else{
				echo json_encode(array('respuesta' => false, 'mensaje' => $stmt->error));    
			}
		}
	}else{
		echo json_encode(array('respuesta' => false, 'mensaje' => 'Has enviado demasiadas solicitudes.'));
	}
}else{
	echo json_encode(array('respuesta' => false, 'mensaje' => 'El correo que introdujo ya esta utilizado por otro usuario.'));
}
?>