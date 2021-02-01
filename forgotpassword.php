<?php

header('Access-Control-Allow-Origin: *');  

include("includes/utils.php");

include_once("includes/class.phpmailer.php");



$post_date  		= file_get_contents("php://input");

$data 				= json_decode($post_date);

$usuario 				= $data->cedula;



//Validar que el correo este registrado en la base de datos.

$stmt = $con->prepare("SELECT idusuario,nombre,apellido,correo FROM usuario WHERE cedula = ?");

/* bind parameters for markers */

$stmt->bind_param("s", $usuario);



/* execute query */

$stmt->execute();



/* bind result variables */

$stmt->bind_result($idusuario,$nombre,$apellido,$email);



/* fetch value */

$stmt->fetch();



$stmt->free_result();


if($idusuario!=""){

	//Validar si ya se le ha enviado un token en 24 horas y preguntar si quiere enviar otro.

	

	//Token para poder cambiar la password

	$encrypt = md5(1290*3+$idusuario);



	//Se registra en la BD y se envia por correo al usuario

	$stmt = $con->prepare("UPDATE usuario SET tokenpassword=? WHERE idusuario=?");

	/* bind parameters for markers */

	$stmt->bind_param("si", $encrypt, $idusuario);

	/* execute query */

	$stmt->execute();

	$stmt->free_result();

	

	$mail = new PHPMailer;

	$mail->CharSet = "UTF-8";

	$mail->From 		= "alter@finespublicidad.com";

	$mail->FromName 	= "Alter";

	$mail->addReplyTo('no-reply@alter.com', 'No Reply');

	$mail->SMTPSecure 	= 'ssl';

	$mail->Port 		= 465;

	

	$mail->addAddress($email);

		

	$mail->isHTML(true);

	$mail->Subject = utf8_decode(html_entity_decode("Alter | Olvid&oacute; su contrase&ntilde;a"));



	$mail->Body = html_entity_decode("<div style='width: 100%; height: auto;'><div style='width: 100%; float: left; background: #0b2d3f; height: auto;'><div style='width: 200px; float: left; padding: 10px; padding-left: 40px;'><img src='http://finespublicidad.com/serviciosalter/media/img/logo_mailing_alter_w.png' width='100%'; height='auto;' /></div><div style='float: right; text-align: right; padding: 10px; padding-top: 60px; padding-right: 40px;'><a href='#' style='text-decoration: none; font-size: 12px; color: white; font-family: arial;'>info@alterclub.com</a></div></div><div style='width: 100%; height: 10px; background: #36a1db; float: left;'></div><div style='width: 90%; height: auto; float: left; padding: 5%; font-family: arial; color: grey; text-align: justify;'><p><h1>Hola $nombre $apellido,</h1></p><p>Haz click aqu&iacute; para reiniciar tu contrase&ntilde;a: </p><p><a href='https://alterclub.com/openapp.php?idusuario=$idusuario&token=$encrypt'><img src='https://alterclub.com/clave-app.png' width='100' /></a>&nbsp;&nbsp;<a href='https://alterclub.com/cambiarclave.php?idusuario=$idusuario&token=$encrypt'><img src='https://alterclub.com/clave-web.png' width='100' /></a></p><p>Si no solicitaste un cambio de contrase&ntilde;a haz caso omiso de este correo.</p><p><h3>Cordialmente,</h3></p><p><h4>El Equipo Alter</h4></p></div><div style='width: 100%; height: auto; background: #36a1db; float: left;' ><p style='color: white; font-family: arial; padding-left: 40px; padding-right: 40px; padding-bottom: 20px; padding-top: 10px;'><img src='http://finespublicidad.com/serviciosalter/media/img/app_store.png' width='150px' height='auto' style='float: right; padding-bottom: 10px;' /><img src='http://finespublicidad.com/serviciosalter/media/img/android.png' width='150px' height='auto' style='float: right; margin-right: 10px; padding-bottom: 10px;' />Alter 2016 - Todos los derechos reservados</br>Descarga nuestra APP, &uacute;nete al Club</p></div></div>\r\n.");



	$mail->IsHTML(true);



	if(!$mail->send()) 

	{

		echo json_encode(array('respuesta' => false));

	}else {

		echo json_encode(array('respuesta' => true, 'email'=> filterEmail($email)));

	}

}else{

	echo json_encode(array('respuesta' => false, 'mensaje' => 'El correo no esta registrado en nuestra plataforma.'));

}



?>