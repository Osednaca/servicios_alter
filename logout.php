<?php
// Initialize the session.
session_start();
include("includes/utils.php");

$fechasalida= date("Y-m-d H:i:s");

// Unset all of the session variables.
//$_SESSION = array();

// If it's desired to kill the session, also delete the session cookie.
// Note: This will destroy the session, and not just the session data!
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finally, destroy the session.
session_destroy();

//Guardar hora de cierre de sesion
$stmt = $con->prepare("UPDATE usuariologin SET fechasalida=? WHERE idusuario=? AND accesstoken=?");
/* bind parameters for markers */
$stmt->bind_param("sis", $fechasalida,$idusuario,$accesstoken);
/* execute query */
$stmt->execute();

if($stmt->error == ""){
	echo json_encode(array("respuesta"=>true));
}else{
	echo json_encode(array("respuesta"=>false,"mensaje"=>$stmt->error));
}

?>