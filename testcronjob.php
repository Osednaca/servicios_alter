<?php
function write_log($msg,$logfile){
    $myfile = fopen($logfile, "w") or die("Unable to open file!");
    fwrite($myfile, $msg);
}

$log = "Funciono!";
write_log($log,"cronjoblog/log_".date("Ymd").".txt");

include("includes/utils.php");
$stmt1 = $con->prepare("INSERT INTO ciudad(idciudad,idpais,ciudad,idusuario) VALUES (?,?,?,?)");
$stmt1->bind_param("isssis", 1,1,"Larracay",32);
$stmt1->execute();

?>