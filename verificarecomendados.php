<?php

	include("includes/utils.php");
	$fechanow = date("Y-m-d");
	$stmt 		= $con->prepare("SELECT idrecomendaciones,fecharecomendacion FROM recomendaciones WHERE estatus = 0");
	$stmt->execute();
    $stmt->bind_result($idrecomendado, $fecharecomendacion);
    $stmt->store_result();

    $data = array();
    /* fetch values */
    while ($stmt->fetch()) {
		$date1 = date_create($fecharecomendacion);
		$date2 = date_create($fechanow);
		
    	$interval = date_diff($date1,$date2);
    	echo $interval->days."<br>";
    	if($interval->days >= 8){
    		$stmt1 = $con->prepare("UPDATE recomendaciones SET estatus=2 WHERE idrecomendaciones = ?");
    		$stmt1->bind_param("i",$idrecomendado);
			$stmt1->execute();
    	}
	}

?>