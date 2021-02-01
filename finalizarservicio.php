<?php

include("includes.php");

$post_date  	  = file_get_contents("php://input");
$data 			  = json_decode($post_date);
$token 			  = Auth::GetData(
    			  	$data->token
				    );
$idusuario  	  = $token->id;
$idservicio		  = $data->idservicio;
$fechaculminacion = date("Y-m-d H:i:s");
	
//Comprobar que el servicio esta en estatus 3
$stmt = $con->prepare("SELECT idcliente,idproveedor,idservicio,valor,idtipopago FROM servicio WHERE idservicio=? AND estatus IN(3,4)");
$stmt->bind_param("i", $idservicio);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($idcliente,$idproveedor,$idservicio,$valor,$tipopago);
$stmt->fetch();
if($stmt->num_rows > 0){
	if($tipopago == 4){
		$estatus = 9;
	}else{
		$estatus = 5;
	}

	$stmt = $con->prepare("UPDATE servicio SET estatus=?,fechaculminacion=? WHERE idservicio=?");
	$stmt->bind_param("isi", $estatus, $fechaculminacion, $idservicio);
	$stmt->execute();
	
	if($stmt->error == ""){
		if($stmt->affected_rows > 0){
			$stmt->free_result();
			//Actualizar disponibilidad del proveedor
			$stmt = $con->prepare("UPDATE usuario SET disponibilidad=1 WHERE idusuario=?");
			/* bind parameters for markers */
			$stmt->bind_param("i", $idusuario);
			/* execute query */
			$stmt->execute();
			//Contabilidad
			$porservicioalter      = select_config_alter("servicioalter");
			$poraporteprimernivel  = select_config_alter("aportehijos");
			$poraportesegundonivel = select_config_alter("aportenietos");
			$poraportetercernivel  = select_config_alter("aportebisnietos");
			$poriva 			   = select_config_alter("iva");
			$porcomnequi 		   = select_config_alter("comisionnequi");
			$porcomnequi2 		   = select_config_alter("comisionnequiproveedor");

			$comalter        	   = $valor*$porservicioalter;
			$ivacomalter     	   = $comalter*$poriva;
			$comalter 			   = $comalter + $ivacomalter;
			$comprimernivel  	   = $valor*$poraporteprimernivel; 
			$comsegundonivel 	   = $valor*$poraportesegundonivel;
			$comtercernivel  	   = $valor*$poraportetercernivel;

			if($tipopago == 1){
				//Nequi
				$vrnequi  		   = ($valor+$comalter+$comprimernivel+$comsegundonivel+$comtercernivel)*$porcomnequi;
				$totalcliente      = ($valor+$comalter+$comprimernivel+$comsegundonivel+$comtercernivel+$vrnequi);
				$comnequi 		   = $totalcliente*$porcomnequi2;
				$totalproveedor    = ($valor-$comalter-$comprimernivel-$comsegundonivel-$comtercernivel);
			}elseif($tipopago == 2 OR $tipopago == 3 OR $tipopago == 4){
				//Tarjeta de Credito
				$vrnequi  		   = 0;
				$totalcliente      = ($valor+$comalter+$comprimernivel+$comsegundonivel+$comtercernivel+$vrnequi);
				$comnequi 		   = 0;
				$totalproveedor    = ($valor-$comalter-$comprimernivel-$comsegundonivel-$comtercernivel);
			}

			//Buscar Cedulas del grupo tanto del cliente como del proveedor
			//Cliente
			//Padre
			$stmt = $con->prepare("SELECT usuario.cedula FROM recomendaciones INNER JOIN usuario USING(idusuario) WHERE recomendaciones.cedula = (SELECT cedula FROM usuario WHERE idusuario = ?)");
			$stmt->bind_param("i", $idcliente);
			$stmt->execute();
			if($stmt->error != ""){
				echo json_encode(array("respuesta"=>"false", "error" => $stmt->error));
				die();
			}			
			$stmt->store_result();
			$stmt->bind_result($cidprimernivel);
			$stmt->fetch(); 

			//Abuelo
			$stmt = $con->prepare("SELECT usuario.cedula FROM recomendaciones INNER JOIN usuario USING(idusuario) WHERE recomendaciones.cedula = (SELECT cedula FROM usuario WHERE idusuario = (SELECT usuario.idusuario FROM recomendaciones INNER JOIN usuario USING(idusuario) WHERE recomendaciones.cedula = (SELECT cedula FROM usuario WHERE idusuario = ?)))");
			$stmt->bind_param("i", $idcliente);
			$stmt->execute();
			if($stmt->error != ""){
				echo json_encode(array("respuesta"=>"false", "error" => $stmt->error));
				die();
			}			
			$stmt->store_result();
			$stmt->bind_result($cidsegundonivel);
			$stmt->fetch();

			//Bisabuelo
			$stmt = $con->prepare("SELECT usuario.cedula FROM recomendaciones INNER JOIN usuario USING(idusuario) WHERE recomendaciones.cedula = (SELECT cedula FROM usuario WHERE idusuario = (SELECT usuario.idusuario FROM recomendaciones INNER JOIN usuario USING(idusuario) WHERE recomendaciones.cedula = (SELECT cedula FROM usuario WHERE idusuario = (SELECT usuario.idusuario FROM recomendaciones INNER JOIN usuario USING(idusuario) WHERE recomendaciones.cedula = (SELECT cedula FROM usuario WHERE idusuario = ?)))))");
			$stmt->bind_param("i", $idcliente);
			$stmt->execute();
			if($stmt->error != ""){
				echo json_encode(array("respuesta"=>"false", "error" => $stmt->error));
				die();
			}			
			$stmt->store_result();			
			$stmt->bind_result($cidtercernivel);
			$stmt->fetch();

			//Proveedor
			//Padre
			$stmt = $con->prepare("SELECT usuario.cedula FROM recomendaciones INNER JOIN usuario USING(idusuario) WHERE recomendaciones.cedula = (SELECT cedula FROM usuario WHERE idusuario = ?)");
			$stmt->bind_param("i", $idproveedor);
			$stmt->execute();
			if($stmt->error != ""){
				echo json_encode(array("respuesta"=>"false", "error" => $stmt->error));
				die();
			}			
			$stmt->store_result();			
			$stmt->bind_result($pidprimernivel);
			$stmt->fetch();

			//Abuelo
			$stmt = $con->prepare("SELECT usuario.cedula FROM recomendaciones INNER JOIN usuario USING(idusuario) WHERE recomendaciones.cedula = (SELECT cedula FROM usuario WHERE idusuario = (SELECT usuario.idusuario FROM recomendaciones INNER JOIN usuario USING(idusuario) WHERE recomendaciones.cedula = (SELECT cedula FROM usuario WHERE idusuario = ?)))");
			$stmt->bind_param("i", $idproveedor);
			$stmt->execute();
			if($stmt->error != ""){
				echo json_encode(array("respuesta"=>"false", "error" => $stmt->error));
				die();
			}			
			$stmt->store_result();			
			$stmt->bind_result($pidsegundonivel);
			$stmt->fetch();

			//Bisabuelo
			$stmt = $con->prepare("SELECT usuario.cedula FROM recomendaciones INNER JOIN usuario USING(idusuario) WHERE recomendaciones.cedula = (SELECT cedula FROM usuario WHERE idusuario = (SELECT usuario.idusuario FROM recomendaciones INNER JOIN usuario USING(idusuario) WHERE recomendaciones.cedula = (SELECT cedula FROM usuario WHERE idusuario = (SELECT usuario.idusuario FROM recomendaciones INNER JOIN usuario USING(idusuario) WHERE recomendaciones.cedula = (SELECT cedula FROM usuario WHERE idusuario = ?)))))");
			$stmt->bind_param("i", $idproveedor);
			$stmt->execute();
			if($stmt->error != ""){
				echo json_encode(array("respuesta"=>"false", "error" => $stmt->error));
				die();
			}
			$stmt->store_result();			
			$stmt->bind_result($pidtercernivel);
			$stmt->fetch();			

			//guardar la transaccion 
			$stmt = $con->prepare("INSERT INTO transacciones(idservicio, comalter, ivacomalter, ccomprimernivel, cidprimernivel, ccomsegundonivel, cidsegundonivel, ccomtercernivel, cidtercernivel, pcomalter, pivacomalter, pcomprimernivel, pidprimernivel, pcomsegundonivel, pidsegundonivel, pcomtercernivel, pidtercernivel, vrnequi, fechatransaccion,comnequi,totalcliente,totalproveedor) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
			$stmt->bind_param("isssssssssssssssssssss", $idservicio, $comalter, $ivacomalter, $comprimernivel, $cidprimernivel, $comsegundonivel, $cidsegundonivel, $comtercernivel, $cidtercernivel, $comalter, $ivacomalter, $comprimernivel, $pidprimernivel, $comsegundonivel, $pidsegundonivel, $comtercernivel, $pidtercernivel, $vrnequi, $fechaculminacion,$comnequi,$totalcliente,$totalproveedor);
			$stmt->execute();
			if($stmt->error == ""){			
				echo json_encode(array('respuesta' => true));
			}else{
				echo json_encode(array('respuesta' => false, 'mensaje' => 'Hubo un error. Por favor contacte con un administrador.', 'error' => $stmt->error));
			}
		}else{
			echo json_encode(array('respuesta' => false, 'mensaje' => 'El servicio no existe.','codigoerror'=>2));
		}
	}else{
		echo json_encode(array('respuesta' => false, 'error' => $stmt2->error,'codigoerror'=>1));
	}
}else{
	echo json_encode(array('respuesta' => false, 'mensaje' => 'El servicio fue cancelado o finalizado.','codigoerror'=>2));
}

?>