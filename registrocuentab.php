<?php
	include("includes/utils.php");
	
	session_start();
	$post_date  	 = file_get_contents("php://input");
	$data 			 = json_decode($post_date);
	$idusuario	     = $_SESSION["idusuario"];
	//Guardar datos Bancarios
	$numerocuenta 	= $data->cuenta->numerocuenta;
	$idbanco	  	= $data->cuenta->idbanco;
	$tipocuenta	  	= $data->cuenta->idtipocuenta;
	$nombretitular	= $data->cuenta->nombretitular;
	$cedula		  	= $data->cuenta->cedulatitular;
	$estatus	  	= 1;
	$fecharegistro 	= date("Y-m-d H:i:s");

	//Validar que el numero de cuenta no se encuentre registrado
	$stmt = $con->prepare("SELECT idcuenta FROM cuentabancaria WHERE numerocuenta = ?");
	/* bind parameters for markers */
	$stmt->bind_param("s", $numerocuenta);
	
	/* execute query */
	$stmt->execute();
	
	/* bind result variables */
	$stmt->bind_result($idcuenta);
	
	/* fetch value */
	$stmt->fetch();
	
	$stmt->free_result();

	if($idcuenta == ""){
		$stmt = $con->prepare("INSERT INTO cuentabancaria(idusuario,numerocuenta, idbanco, tipocuenta, nombretitular, cedula, estatus, fecharegistrocuenta) VALUES (?,?,?,?,?,?,?,?)");
		/* bind parameters for markers */
		$stmt->bind_param("isiissis", $idusuario, $numerocuenta, $idbanco, $tipocuenta, $nombretitular, $cedula, $estatus, $fecharegistro);
		/* execute query */
		$stmt->execute();
		//validar que todo salga bien con $stmt->error
		if($stmt->error==""){
			$idcuenta = $stmt->insert_id;
			echo json_encode(array('respuesta' => true,'idcuenta'=>$idcuenta));
		}else{
			echo json_encode(array('respuesta' => false,'error'=>$stmt->error));
		}
	}else{
		echo json_encode(array('respuesta' => false, 'mensaje'=>'El numero de cuenta ya esta registrado en el sistema.'));
	}
?>