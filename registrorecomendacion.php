<?php

include_once("includes/class.phpmailer.php");
include("includes.php");

$post_date  	= file_get_contents("php://input");
$data 			= json_decode($post_date);
$token 			= Auth::GetData(
    				$data->token
				  );
$idusuario  	= $token->id;
$cedula			= $data->recomendado->cedula;
$nombre			= utf8_decode($data->recomendado->nombre);
$telefono		= $data->recomendado->telefono;
$correo 		= $data->recomendado->email;
$estatus 		= 0;
$fecharegistro 	=   date("Y-m-d H:i:s");

//validar que el usuario solo tenga 4 recomendados
$stmt = $con->prepare("SELECT count(*) FROM recomendaciones WHERE idusuario = ? AND estatus IN(0,1)");

$stmt->bind_param("i", $idusuario);

$stmt->execute();

$stmt->bind_result($cantidadrecomendaciones);

$stmt->fetch();

$stmt->free_result();

if($cantidadrecomendaciones < 4) {
	// Validar que el mismo usuario u otro usuario no recomiende 2 veces la misma cedula
	$stmt1 = $con->prepare("SELECT idrecomendaciones FROM recomendaciones WHERE cedula=?");
	
	$stmt1->bind_param("s", $cedula);
	
	$stmt1->execute();
	
	$stmt1->bind_result($idrecomendaciones);
	
	$stmt1->fetch();

	$stmt1->free_result();

	if($idrecomendaciones == ""){
		// Registrar Recomendacion
		$stmt2 = $con->prepare("INSERT INTO recomendaciones(idusuario, cedula, nombre, telefono, correo, estatus, fecharecomendacion) VALUES (?,?,?,?,?,?,?)");
		/* bind parameters for markers */
		$stmt2->bind_param("issssis", $idusuario, $cedula, $nombre, $telefono, $correo, $estatus, $fecharegistro);
		/* execute query */
		$stmt2->execute();	
	
		//validar que todo salga bien con $stmt->error
		if($stmt2->error==""){
			//Enviar mensaje al correo
			$mail = new PHPMailer;
			$mail->CharSet = "UTF-8";
			$mail->From = "alter@finespublicidad.com";
			$mail->FromName = "Alter";
			
			$mail->addAddress($correo);
				
			$mail->isHTML(true);
			
			$mail->Subject = "Recomendado - Alter";
			$mail->Body = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html xmlns="http://www.w3.org/1999/xhtml"><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8" /><title>ALTER</title></head><body style="margin: 0px; padding: 0px;"><div style="width: 100%; height: auto;"><div style="width: 100%; float: left; background: #0b2d3f; height: auto;"><div style="width: 200px; float: left; padding: 10px; padding-left: 40px;"><img src="http://finespublicidad.com/serviciosalter/media/img/logo_mailing_alter_w.png" width="100%"; height="auto;" /></div><div style="float: right; text-align: right; padding: 10px; padding-top: 60px; padding-right: 40px;"><a href="#" style="text-decoration: none; font-size: 12px; color: white; font-family: arial;">info@alterclub.com</a></div></div><div style="width: 100%; height: 10px; background: #36a1db; float: left;"></div><div style="width: 90%; height: auto; float: left; padding: 5%; font-family: arial; color: grey; text-align: justify;"><p><h1>Hola, '.$nombre.'</h1></p><p>'.$data->recomendado->referente.' te ha referido como una de las piezas clave de su equipo ALTER, a partir de este momento tienes 8 días para realizar tu registro. ES MUY FACIL.</p><p>Solo debes descargar la APP de ALTER Club y hacer clic en REGISTRAME, llena la información y podrás empezar a utilizar la plataforma.</p><p>Es el momento de que empieces a ser un empresario ALTER y generar grandes ingresos.</p><p><h3>Cordialmente,</h3></p><p><h4>El Equipo Alter</h4></p></div><div style="width: 100%; height: auto; background: #36a1db; float: left;" ><p style="color: white; font-family: arial; padding-left: 40px; padding-right: 40px; padding-bottom: 20px; padding-top: 10px;"><a href="https://itunes.apple.com/WebObjects/MZStore.woa/wa/viewSoftware?id=1267852082&mt=8"><img src="http://finespublicidad.com/serviciosalter/media/img/app_store.png" width="150px" height="auto" style="float: right; padding-bottom: 10px;"/></a><a href="https://play.google.com/store/apps/details?id=com.alter.alterclub"><img src="http://finespublicidad.com/serviciosalter/media/img/android.png" width="150px" height="auto" style="float: right; margin-right: 10px; padding-bottom: 10px;" /></a>Alter 2016 - Todos los derechos reservados</br> Descarga nuestra APP, únete al Club</p></div></div></body></html>';
	
			if(!$mail->send()) 
			{
			    echo json_encode(array('respuesta' => false, 'mensaje' => 'Hubo un error.'));
			    die;
			}
			echo json_encode(array('respuesta' => true));
		}else{
			echo json_encode(array('respuesta' => false,'error'=>$stmt2->error,'mensaje'=>'Error en el sistema. Un administrador se pondra en contacto con usted.'));
			reporte_error($idusuario,"",$stmt2->error,"registrorecomendacion.php",$sql);
		}
	}else{
		echo json_encode(array('respuesta' => false, 'mensaje'=>'La cedula ya fue recomendada anteriormente.'));
	}
}else{
	echo json_encode(array('respuesta' => false, 'mensaje' => 'No puedes recomendar mas de 4 personas.'));
}

?>