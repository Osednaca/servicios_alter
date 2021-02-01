<?php

	include("includes/utils.php");

	$post_date  	= file_get_contents("php://input");

	$data 			= json_decode($post_date);


	$sql = "SELECT idtipovehiculo,tipovehiculo FROM tipovehiculo WHERE tipovehiculo <> 'Domicilio'";

	$stmt = $con->prepare($sql);

	/* execute query */

	$stmt->execute();

    /* bind result variables */

    $stmt->bind_result($idtipovehiculo, $tipovehiculo);



    $data = array();

    /* fetch values */

    while ($stmt->fetch()) {

         $data[] = array('idtipovehiculo'=>$idtipovehiculo,'tipovehiculo'=>utf8_encode($tipovehiculo));

    }

	if (!empty($data)) {

		echo json_encode(array("respuesta"=>true,"data"=>$data));

	}else{

		echo json_encode(array("respuesta"=>false));

	}

?>