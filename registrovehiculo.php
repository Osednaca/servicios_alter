<?php

include("includes.php");

$post_date  	 		= file_get_contents("php://input");
$data 			 		= json_decode($post_date);

$token 					= 	Auth::GetData(
     							$data->token
 						  	);
$idusuario 				= 	$token->id;
$idtipovehiculo			= 	$data->vehiculo->idtipovehiculo;
if ((string)$idtipovehiculo!='5'){
	$placa					= 	strtoupper($data->vehiculo->placa);
	$modelo					= 	utf8_decode($data->vehiculo->modelo);
	$marca					= 	utf8_decode($data->vehiculo->marca);
}
/*$nomotor				= 	$data->nomotor;
$nochasis				= 	$data->nochasis;
$vencimientosoat		= 	$data->vencimientosoat;
$vencimientotecnico		= 	$data->vencimientotecnico;*/
$fecharegistro 			=   date("Y-m-d H:i:s");

//Formatear fecha para base de datos
//$aux 			 	= explode("/", $vencimientosoat);
//$vencimientosoat 	= $aux[2]."-".$aux[1]."-".$aux[0];
//$aux1			 	= explode("/", $vencimientotecnico);
//$vencimientotecnico = $aux1[2]."-".$aux1[1]."-".$aux1[0];

//if(!$data->espropietario){
//	$cedulapropietario		= 	$data->cedulapropietario;
//	$nombrepropietario		= 	utf8_decode($data->nombrepropietario);
//}else{
//	$cedulapropietario		=	"";
//	$nombrepropietario		=	"";
//}

$estatus				= 	0;

//Validar que el vehiculo no este utilizado por otro usuario
if ((string)$idtipovehiculo!='5'){
	$stmt = $con->prepare("SELECT idvehiculo FROM vehiculo WHERE placa = ?");
	/* bind parameters for markers */
	$stmt->bind_param("s", $placa);

	/* execute query */
	$stmt->execute();

	/* bind result variables */
	$stmt->bind_result($idvehiculo);

	/* fetch value */
	$stmt->fetch();

	$stmt->free_result();
	//Si no existe ninguno guarda el registro en BD
	if($idvehiculo==""){
		$stmt = $con->prepare("INSERT INTO vehiculo(idusuario, idtipovehiculo, placa, modelo, marca,estatus, fecharegistrovehiculo) VALUES (?,?,?,?,?,?,?)");
		/* bind parameters for markers */
		$stmt->bind_param("iisssis", $idusuario,$idtipovehiculo,$placa,$modelo,$marca,$estatus,$fecharegistro);

		/* execute query */
		$stmt->execute();
		if($stmt->error == ""){
			$idvehiculo = $stmt->insert_id;
			echo json_encode(array('respuesta' => true,'idvehiculo' => $idvehiculo,'placa' => $placa));
			//Enviar Correo
			$mail = new PHPMailer;
			$mail->CharSet = "UTF-8";
			$mail->From = "alter@finespublicidad.com";
			$mail->FromName = "Alter";
			
			$mail->addAddress("finesxpress@gmail.com");
				
			$mail->Subject = "Nuevo vehiculo";
			$mail->Body = html_entity_decode("Han agregado un nuevo vehiculo. <a href='https://alterclub.com/administrador'>Ir al administrador.</a>");
			$mail->IsHTML(true);
			$mail->send();			
		}else{
			echo json_encode(array('respuesta' => false,'mensaje'=>'Error en el sistema. Contacte con un administrador.', 'error'=>$stmt->error));
			reporte_error($idusuario,"",$stmt->error,"registrovehiculo.php",$sql);
		}
	}else{
		// Sino muestra un error
		echo json_encode(array('respuesta' => false,'mensaje'=>'Tu placa ya se encuentra registrada'));
	}
}else{
	$stmt = $con->prepare("SELECT idvehiculo FROM vehiculo WHERE idtipovehiculo = 5 AND idusuario = ?");
	$stmt->bind_param("s", $idusuario);
	$stmt->execute();
	$stmt->bind_result($idvehiculo);
	$stmt->fetch();
	$stmt->free_result();
	//Si no existe ninguno guarda el registro en BD
	if($idvehiculo==""){
	
		$stmt = $con->prepare("INSERT INTO vehiculo(idusuario, idtipovehiculo, estatus, fecharegistrovehiculo) VALUES (?,?,?,?)");
		$stmt->bind_param("iiis", $idusuario,$idtipovehiculo,$estatus,$fecharegistro);
		$stmt->execute();
	
		if($stmt->error == ""){
			$idvehiculo = $stmt->insert_id;
			echo json_encode(array('respuesta' => true,'idvehiculo' => $idvehiculo));
			//Enviar Correo
			$mail = new PHPMailer;
			$mail->CharSet = "UTF-8";
			$mail->From = "finesxpress@finespublicidad.com";
			$mail->FromName = "Alter";
			
			$mail->addAddress("on.navas@gmail.com");
				
			$mail->Subject = "Nuevo vehiculo";
			$mail->Body = html_entity_decode("Han agregado un nueva bicicleta. <a href='https://alterclub.com/administrador'>Ir al administrador.</a>");
			$mail->IsHTML(true);
			$mail->send();
		}else{
			echo json_encode(array('respuesta' => false,'mensaje'=>'Error en el sistema. Contacte con un administrador.', 'error'=>$stmt->error));
			reporte_error($idusuario,"",$stmt->error,"registrovehiculo.php",$sql);
		}
	}else{
		echo json_encode(array('respuesta' => false,'mensaje'=>'Solo puede registrar una bicicleta.'));
	}
}
?>