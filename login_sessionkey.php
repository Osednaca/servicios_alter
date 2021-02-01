<?php
header('Access-Control-Allow-Origin: *');  
session_start();
include("includes/utils.php");

$post_date = file_get_contents("php://input");
$data = json_decode($post_date);

if(!empty($data))
{
	$sessionkey = trim($data->sessionkey);

	$stmt = $con->prepare("SELECT idusuario,correo,password,salt,imgusuario,imgcedula,imgrut,idpais,usuario.nombre,usuario.apellido,usuario.cedula,usuario.estatus,limiterut, cuenta_nequi.idcuentanequi FROM usuario INNER JOIN usuariologin USING(idusuario) LEFT JOIN cuenta_nequi USING(idusuario) WHERE accesstoken = ?");
	/* bind parameters for markers */
    $stmt->bind_param("s", $sessionkey);

    /* execute query */
    $stmt->execute();

    /* bind result variables */
    $stmt->bind_result($idusuario,$email,$usuariopassword,$usuariosalt,$imgusuario,$imgcedula,$imgrut,$idpais,$nombre,$apellido,$cedula,$estatus,$limiterut,$idcuentanequi);

    /* fetch value */
    $stmt->fetch();

    $stmt->free_result();

//FIN TIENE SERVICIO
//Tiene servicio en proceso
$stmt = $con->prepare("SELECT idservicio,idproveedor FROM servicio INNER JOIN usuariologin ON usuariologin.idusuario = servicio.idcliente OR usuariologin.idusuario = servicio.idproveedor WHERE usuariologin.accesstoken = ? AND estatus IN(2,3,9)");
$stmt->bind_param("s", $accesstoken);
$stmt->execute();
$stmt->bind_result($idservicio,$idproveedor);
$stmt->fetch();
$stmt->free_result();
	
	if($idservicio != ""){
		$tieneservicio = true;
	}else{
		$tieneservicio = false;
	}
	if($idproveedor == $idusuario){
		$esproveedor = true;
	}else{
		$esproveedor = false;
	}
//fin tiene servicio en proceso
	if($idusuario != ""){	
			$SKey = uniqid(mt_rand(), true);
			$timestamp = date("Y-m-d H:i:s");
   			
	   		$_SESSION["idusuario"]    = $idusuario;
	   		$_SESSION["cedula"]		  = $cedula;
			$_SESSION["email"] 		  = $email;
	       	$_SESSION['userAgent'] 	  = sha1($_SERVER['HTTP_USER_AGENT']);
			$_SESSION['IPaddress'] 	  = $_SERVER["REMOTE_ADDR"];
			$_SESSION['LastActivity'] = $_SERVER['REQUEST_TIME'];			
			echo json_encode(array('respuesta' => true,'usuario' => array('idusuario' => $idusuario, 'idpais' => $idpais, 'nombre' => utf8_encode($nombre), 'apellido' => utf8_encode($apellido), 'imgusuario' => $imgusuario,'imgcedula' => $imgcedula,'imgrut'=>$imgrut,'tieneservicio'=>$tieneservicio,'estatus'=>$estatus,'limiterut'=>$limiterut,'idcuentanequi'=>$idcuentanequi,'esproveedor'=>$esproveedor)));
	}else{
			echo json_encode(array('respuesta' => false,'mensaje' => "Accesstoken incorrecto"));
	}
}
?>