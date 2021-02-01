<?php

include_once("/var/www/html/servicios/includes/class.phpmailer.php");

$mail   = new PHPMailer;
$mail->CharSet = "UTF-8";
$mail->From = "alter@alterclub.com";
$mail->FromName = "Alter";
$mail->addReplyTo('no-reply@alter.com', 'No Reply');
$mail->addAddress("");
$mail->Subject = "Alter | Envio de Dinero";
$mail->Body = html_entity_decode("hola");

$mail->IsHTML(true);
$mail->send();
?>
