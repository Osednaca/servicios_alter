<?php
	/* 
		Se ejecuta con un CRON JOB todos los dias ???
		Descripcion: Actualiza el estatus de una recomendacion si esta ha superado los 8 dias y todavia el usuario no se ha registrado.
	*/
	include("includes/utils.php");
	$stmt = $con->prepare("UPDATE recomendaciones SET estatus=2 WHERE estatus=0 AND DATEDIFF(CURDATE(),DATE(fecharecomendacion) > 8");
	$stmt->execute();
	if($stmt->error == ""){
		echo "Ok";
	}else{
		echo "Error: ".$stmt->error;
	}
?>