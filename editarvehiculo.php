<?php



include("includes/utils.php");



$post_date  	 		= file_get_contents("php://input");

$data 			 		= json_decode($post_date);



$idvehiculo				= 	$data->idvehiculo;

$idtipovehiculo			= 	$data->idtipovehiculo;

$placa					= 	strtoupper($data->placa);

$modelo					= 	utf8_decode($data->modelo);

$marca					= 	utf8_decode($data->marca);

$fechaeditar			=   date("Y-m-d H:i:s");


if ((string)$idtipovehiculo!='5'){

	//Validar que el vehiculo no este utilizado por otro usuario

	$stmt = $con->prepare("SELECT idvehiculo FROM vehiculo WHERE placa = ? AND idusuario<>?");

	/* bind parameters for markers */

	$stmt->bind_param("si", $placa,$idusuario);



	/* execute query */

	$stmt->execute();



	/* bind result variables */

	$stmt->bind_result($validavehiculo);



	/* fetch value */

	$stmt->fetch();



	$stmt->free_result();

	//Si no existe ninguno guarda el registro en BD

	if($validavehiculo==""){

		$stmt1 = $con->prepare("UPDATE vehiculo SET idtipovehiculo=?, placa=?, modelo=?, marca=?, fechaeditvehiculo=? WHERE idvehiculo=?");

		/* bind parameters for markers */

		$stmt1->bind_param("issssi", $idtipovehiculo,$placa,$modelo,$marca,$fechaeditar,$idvehiculo);



		/* execute query */

		$stmt1->execute();

		if($stmt1->error == ""){

			echo json_encode(array('respuesta' => true));

		}else{

			echo json_encode(array('respuesta' => false, 'error' => $stmt1->error));		

		}

	}else{

		// Sino muestra un error

		echo json_encode(array('respuesta' => false,'mensaje'=>'La placa ya se encuentra registrado en la plataforma'));

	}

}else{


	$stmt1 = $con->prepare("UPDATE vehiculo SET idtipovehiculo=?, fechaeditvehiculo=? WHERE idvehiculo=?");
	/* bind parameters for markers */
	$stmt1->bind_param("isi", $idtipovehiculo,$fechaeditar,$idvehiculo);
	/* execute query */
	$stmt1->execute();

	if($stmt1->error == ""){

		echo json_encode(array('respuesta' => true));

	}else{

		echo json_encode(array('respuesta' => false, 'error' => $stmt1->error));		

	}

}
?>