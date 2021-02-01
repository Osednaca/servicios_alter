<?php

include_once("includes/class.phpmailer.php");
include("includes.php");

$post_date  	= file_get_contents("php://input");
$data 			= json_decode($post_date);
$token 			= Auth::GetData(
    				$data->token
				  );
$idusuario  	= $token->id;
$cedula			= $data->cedula;
$estatus		= 1;		

//Validar que la cedula sea un usuario activo
$stmt = $con->prepare("SELECT idusuario,nombre,apellido FROM usuario WHERE cedula = ?");
$stmt->bind_param("i", $cedula);
$stmt->execute();
$stmt->bind_result($existeusuario,$nombre,$apellido);
$stmt->fetch();
$stmt->free_result();

if($existeusuario != ""){

	//Consultar saldo y validar que el monto que quiere retirar es menor o igual a su saldo disponible
	$stmt = $con->prepare("SELECT saldoalter,correo FROM usuario WHERE idusuario = ?");
	$stmt->bind_param("i", $idusuario);
	$stmt->execute();
	$stmt->bind_result($saldodisponible,$correo);
	$stmt->fetch();
	$stmt->free_result();
//
	////echo "idusuario: $idusuario // Saldo: $saldodisponible";
//
	//if($valor <= $saldodisponible){
		$codigo	=  rand(1000,5000);
		//Descontar temporalmente el dinero de su saldo
		$stmt2 = $con->prepare("UPDATE usuario SET pinenviodinero = ? WHERE idusuario = ?");
		$stmt2->bind_param("si", $codigo, $idusuario);
		//echo $codigo."//".$idusuario; die();
		$stmt2->execute();
		if($stmt2->error == ""){
			//Enviar correo con el PIN al usuario
			$mail 	= new PHPMailer;
			$mail->CharSet = "UTF-8";
			$mail->From = "alter@finespublicidad.com";
			$mail->FromName = "Alter";
			$mail->addReplyTo('no-reply@alter.com', 'No Reply');
			
			$mail->addAddress($correo);
				
			$mail->Subject = "Alter | Envio de Dinero";

			$mail->Body = html_entity_decode("<div style='width: 100%; height: auto;'><div style='width: 100%; float: left; background: #0b2d3f; height: auto;'><div style='width: 200px; float: left; padding: 10px; padding-left: 40px;'><img src='http://finespublicidad.com/serviciosalter/media/img/logo_mailing_alter_w.png' width='100%'; height='auto;' /></div><div style='float: right; text-align: right; padding: 10px; padding-top: 60px; padding-right: 40px;'><a href='#' style='text-decoration: none; font-size: 12px; color: white; font-family: arial;'>info@alterclub.com</a></div></div><div style='width: 100%; height: 10px; background: #36a1db; float: left;'></div><div style='width: 90%; height: auto; float: left; padding: 5%; font-family: arial; color: grey; text-align: justify;'><p><h1>Hola,</h1></p><p>Hemos recibido una solicitud para enviar dinero de tu cuenta ALTER, a la siguiente otra cuenta:</p><p><b>Cedula:</b> $cedula</p><p><b>Nombre:</b> $nombre $apellido</p><p>Para poder completar el proceso utilice el siguiente PIN: <b>$codigo</b></p><p><h3>Cordialmente,</h3></p><p><h4>El Equipo Alter</h4></p></div><div style='width: 100%; height: auto; background: #36a1db; float: left;' ><p style='color: white; font-family: arial; padding-left: 40px; padding-right: 40px; padding-bottom: 20px; padding-top: 10px;'><a href='https://itunes.apple.com/WebObjects/MZStore.woa/wa/viewSoftware?id=1267852082&mt=8'><img src='http://finespublicidad.com/serviciosalter/media/img/app_store.png' width='150px' height='auto' style='float: right; padding-bottom: 10px;'/></a><a href='https://play.google.com/store/apps/details?id=com.alter.alterclub'><img src='http://finespublicidad.com/serviciosalter/media/img/android.png' width='150px' height='auto' style='float: right; margin-right: 10px; padding-bottom: 10px;' /></a>Alter 2016 - Todos los derechos reservados</br>. Descarga nuestra APP, &uacute;nete al Club</p></div></div>\r\n.");
	
			$mail->IsHTML(true);
	
			if(!$mail->send()) 
			{
			    echo json_encode(array('respuesta' => false, 'mensaje' => 'Hubo un error.'));
			    die();
			}
			echo json_encode(array('respuesta' => true));			
		}else{
			echo json_encode(array('respuesta' => false,'error'=>$stmt2->error));
		}
	//}else{
	//	echo json_encode(array('respuesta' => false,'mensaje' => "Buen intento."));
	//}
}else{
	echo json_encode(array('respuesta' => false, 'mensaje' => 'El usuario no existe'));
}
?>