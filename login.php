<?php

header('Access-Control-Allow-Origin: *');  

include("includes/utils.php");
require_once 'lib/vendor/autoload.php';
require_once 'lib/auth.php';

use Firebase\JWT\JWT;



$post_date = file_get_contents("php://input");

$data = json_decode($post_date);

if(!empty($data))

{

	$username = trim($data->cedula);

	$password = trim($data->password);



	$stmt = $con->prepare("SELECT usuario.idusuario,correo,password,salt,imgusuario,imgcedula,imgrut,usuario.idpais,usuario.nombre,usuario.apellido,usuario.cedula,usuario.estatus,limiterut, tipousuario, cuenta_nequi.idcuentanequi,direccion,ciudad,idciudad,telefonocelular FROM usuario LEFT JOIN ciudad USING(idciudad) LEFT JOIN cuenta_nequi ON usuario.idusuario = cuenta_nequi.idusuario WHERE usuario.cedula = ? OR telefonocelular = ?");
	/* bind parameters for markers */

    $stmt->bind_param("ss", $username, $username);



    /* execute query */

    $stmt->execute();



    /* bind result variables */

    $stmt->bind_result($idusuario,$email,$usuariopassword,$usuariosalt,$imgusuario,$imgcedula,$imgrut,$idpais,$nombre,$apellido,$cedula,$estatus,$limiterut,$tipousuario,$idcuentanequi,$direccion,$ciudad,$idciudad,$telefono);



    /* fetch value */

    $stmt->fetch();



    $stmt->free_result();



	if($idusuario != ""){

	    $encrypted_password = $usuariopassword;

	    $hash = checkhashSSHA($usuariosalt, $password);



		if($encrypted_password == $hash){

			$SKey = uniqid(mt_rand(), true);

			$timestamp = date("Y-m-d H:i:s");

			$stmt2 = $con->prepare("INSERT INTO usuariologin(idusuario,accesstoken,direccionip,fechalogueo) VALUES(?,?,?,?)");

   			$stmt2->bind_param("isss", $idusuario,$token,$_SERVER["REMOTE_ADDR"],$timestamp);

   			$stmt2->execute();
			
			$token = Auth::SignIn([
        		'id' => $idusuario
    		]);		

			echo json_encode(array('respuesta' => true,'usuario' => array('idpais' => $idpais, 'nombre' => utf8_encode($nombre), 'apellido' => utf8_encode($apellido), 'imgusuario' => $imgusuario,'imgcedula' => $imgcedula,'imgrut'=>$imgrut,'estatus'=>$estatus,'limiterut'=>$limiterut,'tipousuario'=>$tipousuario,'idcuentanequi'=>$idcuentanequi, 'token' => $token,'cedula' => $cedula,'direccion'=>$direccion,'ciudad'=>utf8_encode($ciudad),'idciudad'=>$idciudad,'telefono'=>$telefono)));

		}else{

			echo json_encode(array('respuesta' => false,'mensaje' => "Usuario o password incorrecta"));

		}

	}else{

			echo json_encode(array('respuesta' => false,'mensaje' => "Usuario o password incorrecta"));

	}

}

?>