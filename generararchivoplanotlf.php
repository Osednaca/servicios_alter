<?php
include("includes/conexion.php");
$txt               = "";

$stmt = $con->prepare("SELECT nombre,apellido,telefonocelular FROM usuario WHERE estatus IN(1,2,3) ORDER BY nombre ASC");
//var_dump($con);
$stmt->execute();      
$stmt->bind_result($nombre,$apellido,$telefono);
while ($stmt->fetch()){
    $txt .= $nombre." ".$apellido.",".$telefono."\r\n";
}

$myfile = fopen("usuariosalter.txt", "w") or die("Unable to open file!");
fwrite($myfile, $txt);
fclose($myfile);

?>