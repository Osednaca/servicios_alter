<?php
	include("includes.php");

	$post_date  	= file_get_contents("php://input");
	$data 			= json_decode($post_date);
	$token 			= Auth::GetData(
        				$data->token
    				  );
	$idusuario  	= $token->id;

$stmt = $con->prepare("SELECT idrecomendaciones,cedula,nombre,telefono,correo,fecharecomendacion,estatus
						FROM recomendaciones
						WHERE idusuario=? AND estatus <> 2");

$stmt->bind_param("i", $idusuario);

$stmt->execute();

if($stmt->error == ""){
	/* bind result variables */
	$stmt->bind_result($idrecomendaciones,$cedula,$nombre,$telefono,$correo,$fecharecomendacion,$estatus);
	while ($stmt->fetch()) {
		$recomendaciones[] = array('idrecomendaciones'=>$idrecomendaciones,'cedula'=>$cedula,'nombre'=>utf8_encode($nombre),'telefono'=>$telefono,'correo'=>$correo,'fecharecomendacion'=>$fecharecomendacion,'estatus'=>$estatus);
	}
	
	//Numero de inscritos de su grupo
	$nhijos    = 0;
	$nnietos   = 0;
	$nbisnietos= 0;
	// HIJOS
    $sqlhijos = "SELECT usuario.idusuario FROM recomendaciones LEFT JOIN usuario USING(cedula) WHERE recomendaciones.idusuario = ?";
    $stmt = $con->prepare($sqlhijos);
    $stmt->bind_param("i",$idusuario);
    $stmt->execute();
    if($stmt->error == ""){
        $stmt->bind_result($idhijo);
        $stmt->store_result();
        while($stmt->fetch()){
            $nhijos += 1;
                // NIETOS
                $sqlnietos = "SELECT usuario.idusuario FROM recomendaciones LEFT JOIN usuario USING(cedula) WHERE recomendaciones.idusuario = ?";
                $stmt2 = $con->prepare($sqlnietos);
                $stmt2->bind_param("i",$idhijo);
                $stmt2->execute();
                $stmt2->store_result();

                if($stmt2->error == ""){
                    $stmt2->bind_result($idnieto);
                    $stmt2->store_result();
                    while($stmt2->fetch()){
						$nnietos += 1;                                         
                        // BISNIETOS
                        $sqlbisnietos = "SELECT usuario.idusuario FROM recomendaciones LEFT JOIN usuario USING(cedula) WHERE recomendaciones.idusuario = ?";
                        $stmt3 = $con->prepare($sqlbisnietos);
                        $stmt3->bind_param("i",$idnieto);
                        $stmt3->execute();
                        $stmt3->store_result();

                        if($stmt3->error == ""){
                            $stmt3->bind_result($idbisnietos);
                            $stmt3->store_result();
                            while($stmt3->fetch()){
								$nbisnietos += 1; 
                            }
                        }else{
							echo json_encode(array('respuesta' => false, 'mensaje' => $stmt3->error));
							die();
						}
                    }
                }else{
					echo json_encode(array('respuesta' => false, 'mensaje' => $stmt2->error));
					die();
				}
        }
   	}else{
		echo json_encode(array('respuesta' => false, 'mensaje' => $stmt1->error));
		die();
	}

	$ninscritostotal = $nhijos+$nnietos+$nbisnietos;

	if(!empty($recomendaciones)){
		echo json_encode(array('respuesta' => true, 'recomendaciones'=>$recomendaciones,'ninscritos' => $ninscritostotal));
	}else{
		echo json_encode(array('respuesta' => false, 'mensaje' => 'No se encontraron recomendaciones'));
	}
}else{
	echo json_encode(array('respuesta' => false, 'mensaje' => $stmt->error));
}

?>