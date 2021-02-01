<?php

include("includes/utils.php");

$username = trim($_REQUEST["username"]);
$password = trim($_REQUEST["password"]);

$stmt = $con->prepare("SELECT idusuario,nombre,correo,password,salt FROM usuario WHERE correo = ?");
/* bind parameters for markers */
$stmt->bind_param("s", $username);

/* execute query */
$stmt->execute();

/* bind result variables */
$stmt->bind_result($id,$name,$email,$password_db,$salt);

/* fetch value */
$stmt->fetch();

$stmt->free_result();

if($id != ""){
    $encrypted_password = $password_db;
    $hash = checkhashSSHA($salt, $password);

    if($encrypted_password == $hash){
        echo json_encode(array('respuesta' => true,'id' => $id, 'name' => utf8_encode($name),'email' => $email));
    }else{
        echo json_encode(array('respuesta' => false));
    }
}else{
        echo json_encode(array('respuesta' => false));
}

?>