<?php
/*
	Descripcion: Se ejecuta con un CRON JOB todos los dias ???
	Funciones: Busca en la base de datos todas los retiros de dinero pendientes y los procesa en caso de exito le resta el valor del retiro al saldo alter del usuario.
*/

include("includes/utils.php");
$stmt = $con->prepare("SELECT valor FROM retirodedinero WHERE idusuario = ?");
$stmt->bind_param("i", $idusuario);
$stmt->execute();
$stmt->bind_result($valor);

while($stmt->fetch()){
	$stmt1 = $con->prepare("UPDATE usuario SET saldoalter = saldoalter - ?  WHERE idusuario = ?");
	$stmt1->bind_param("i", $idusuario);
	$stmt1->execute();	
}

?>