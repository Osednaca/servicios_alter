<?php

include("includes.php");

$post_date  	 		= file_get_contents("php://input");
$data 			 		= json_decode($post_date);

$token 					= 	Auth::GetData(
     							$data->token
 						  	);
$idusuario 				= 	$token->id;
$nombre					= 	utf8_decode($data->producto->nombre);
$idlugar				= 	$data->producto->idlugar;
if(!empty($data->producto->idmercadocategoria)){
	$idmercadocategoria	= 	$data->producto->idmercadocategoria;
}else{
	$idmercadocategoria =   NULL;
}
if(!empty($data->producto->idlicorescategoria)){
	$idlicorescategoria	= 	$data->producto->idlicorescategoria;
}else{
	$idlicorescategoria =   NULL;
}
if(!empty($data->producto->idcomidacategoria)){
	$idcomidacategoria	= 	$data->producto->idcomidacategoria;
}else{
	$idcomidacategoria =   NULL;
}
if(!empty($data->producto->descripcion)){
	$descripcion 		= 	utf8_decode($data->producto->descripcion);
}else{
	$descripcion 		=   "";
}
$precio 				= 	$data->producto->precio;
$imagen					=   $data->imagenproducto.".png";
$fecharegistro 			=   date("Y-m-d H:i:s");
$aprobado				= 	0;

if(!empty($data->producto->nextras)){
	$nextras 			= 	$data->producto->nextras;
}else{
	$nextras 			=   0;
}

if ($nombre !=''){
		if($nextras > 0){
			$extras = 1;
		}else{
			$extras = 0;
		}
		//var_dump($idlicorescategoria); die();
		$stmt = $con->prepare("INSERT INTO lugar_items(idlugar, titulo, descripcion, imagen, precio, fecharegistro, idusuario, aprobado, idmercadocategoria,idlicorescategoria,idcomidacategoria,extras) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
		/* bind parameters for markers */
		$stmt->bind_param("isssssiisssi", $idlugar,$nombre,$descripcion,$imagen,$precio,$fecharegistro,$idusuario,$aprobado,$idmercadocategoria,$idlicorescategoria,$idcomidacategoria,$extras);

		/* execute query */
		$stmt->execute();
		$iditem = $stmt->insert_id;
		for ($i=1; $i <= $nextras; $i++) {
			$nombre = $data->producto->{"extra$i"};
			if($data->producto->{"multiple$i"}){
				$tipo = 1;
			}else{
				$tipo = 2;
			}
			$stmt2 = $con->prepare("INSERT INTO items_extras(iditem, nombre, fecharegistro, idusuario, aprobado) VALUES (?,?,?,?,?)");
			$stmt2->bind_param("issii", $iditem,$nombre,$fecharegistro,$idusuario,$aprobado);
			$stmt2->execute();
			$iditemextra = $stmt2->insert_id;
			$nopciones = $data->producto->{"nopciones$i"};
			for ($j=1; $j <= $nopciones; $j++){
				$titulo  = utf8_decode($data->producto->{"o_".$i."_".$j});
				$precio2 = 0;
				if(!empty($data->producto->{"conprecio$i"})){
					if($data->producto->{"conprecio$i"}){
						$precio2 = $data->producto->{"precio_op_".$i."_".$j};
					}
				}
				$stmt = $con->prepare("INSERT INTO items_adicion(iditemextra, tipo, titulo, precio, idusuario, fecharegistro, aprobado) VALUES (?,?,?,?,?,?,?)");
				$stmt->bind_param("iissisi", $iditemextra,$tipo,$titulo,$precio2,$idusuario,$fecharegistro,$aprobado);
				$stmt->execute();
			}
		}

		if($stmt->error == ""){
			echo json_encode(array('respuesta' => true));
		}else{
			echo json_encode(array('respuesta' => false,'mensaje'=>'Error en el sistema. Contacte con un administrador.', 'error'=>$stmt->error));
			//reporte_error($idusuario,"",$stmt->error,"registronegocio.php",$sql);
		}
}
?>