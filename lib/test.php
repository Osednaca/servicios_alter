<?php
include("includes/utils.php");

$stmt1 = $con->prepare("SELECT tiempolimiterut FROM configuracionalter WHERE idconfiguracion = 1");
$stmt1->execute();
$stmt1->bind_result($tiempolimiterut);
$stmt1->fetch();

$fechalimiterut  = datesc('Y-m-d');
$nuevafecha 	 = strtotime ( $tiempolimiterut , strtotime ( $fechalimiterut ) ) ;
$nuevafecha 	 = date ( 'Y-m-d' , $nuevafecha );

echo $nuevafecha;

?>
