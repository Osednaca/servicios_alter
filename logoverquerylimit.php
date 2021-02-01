<?php
include("includes.php");

//$post_date      = file_get_contents("php://input");
//$data           = json_decode($post_date);
//$token          = Auth::GetData(
                 //  $data->token
                 //);
$idusuario      = 32;
$fecha       = date("Y-m-d H:i:s");
$txt               = "";

$txt .= "\n$idusuario $fecha";

$myfile = "log_overquery.txt";
file_put_contents($myfile, $txt, FILE_APPEND | LOCK_EX);

?>