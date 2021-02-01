<?php

include("includes.php");

$post_date  	 		= file_get_contents("php://input");
$data 			 		= json_decode($post_date);

$token 					= 	Auth::GetData(
     							$data->token
 						  	);
$idusuario 				= 	$token->id;
$iditem                 =   $data->producto->iditem;
$idlugar                =   $data->producto->idlugar;
$nombre					= 	utf8_decode($data->producto->nombre);
$precio					= 	$data->producto->precio;
$sqlimagen 				=   "";

if(!empty($data->producto->tipo)){
	$tipo				= 	$data->producto->tipo;
}else{
	$tipo 				=   NULL;
}
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
if(!empty($data->producto->descripcion)){
	$descripcion 		= 	utf8_decode($data->producto->descripcion);
}else{
	$descripcion 		=   "";
}

if(!empty($data->producto->editarlogo)){
	$imagen 	= $data->imagenproducto.".png";
	$sqlimagen	= ",imagen='$imagen'";
}

$nitemsextras 			= $data->producto->nextras;
$nitemsextrasnuevos 	= $data->producto->nextrasnuevos;
$fecharegistro 			= date("Y-m-d H:i:s");
$aprobado 				= 1;

if($nitemsextrasnuevos > 0){
	$extras = 1;
}

$stmt = $con->prepare("UPDATE lugar_items SET idlugar=?,tipo=?,titulo=?,descripcion=?,precio=?,fechamodificacion=?,idmercadocategoria=?,idlicorescategoria=?,extras=? $sqlimagen WHERE iditem= ?");
/* bind parameters for markers */
$stmt->bind_param("iissssiiii", $idlugar,$tipo,$nombre,$descripcion,$precio,$fecharegistro,$idmercadocategoria,$idlicorescategoria,$extras,$iditem);
/* execute query */
$stmt->execute();
if($stmt->error != ""){
	echo json_encode(array('respuesta' => false,'mensaje'=>'Error en el sistema. Contacte con un administrador.', 'error'=>$stmt->error));
}
for ($i=0; $i < $nitemsextras; $i++) {
	$iditemextra = $data->producto->{"iditemextra$i"};
	$nombre      = utf8_decode($data->producto->{"extra$iditemextra"});
	$nopciones   = $data->producto->{"nopciones$iditemextra"};
	$nopcionesnuevas   = $data->producto->{"nopcionesnuevas$iditemextra"};
	if($data->producto->{"multiple$iditemextra"}){
		$tipo = 1;
	}else{
		$tipo = 2;
	}
	$stmt = $con->prepare("UPDATE items_extras SET nombre=? WHERE iditemextra= ?");
	$stmt->bind_param("si", $nombre,$iditemextra);
	$stmt->execute();
	for ($j=0; $j < $nopciones; $j++) {
		$iditemadicion = $data->producto->{"iditemadicion_".$iditemextra."_$j"};
		$titulo 	   = utf8_decode($data->producto->{"o_".$iditemextra."_".$iditemadicion});
		$precio		   = $data->producto->{"precio_op_".$iditemextra."_".$iditemadicion};
		$stmt = $con->prepare("UPDATE items_adicion SET titulo=?,precio=?,tipo=? WHERE iditemadicion=?");
		$stmt->bind_param("ssii", $titulo,$precio,$tipo,$iditemadicion);
		$stmt->execute();
	}
	for ($k=1; $k <= $nopcionesnuevas; $k++) {
		$titulo 	   = utf8_decode($data->producto->{"onueva_".$iditemextra."_".$k});
		$precio		   = $data->producto->{"precio_nuevo_op_".$iditemextra."_".$k};
		$stmt = $con->prepare("INSERT INTO items_adicion(iditemextra,tipo, titulo, precio, idusuario, fecharegistro, aprobado) VALUES (?,?,?,?,?,?,?)");
		$stmt->bind_param("iissisi", $iditemextra, $tipo, $titulo, $precio, $idusuario, $fecharegistro, $aprobado);
		$stmt->execute();
		if($stmt->error != ""){
			echo json_encode(array('respuesta' => false,'mensaje'=>'Error en el sistema. Contacte con un administrador.', 'error'=>$stmt->error));
		}
	}
}

for ($i=1; $i <= $nitemsextrasnuevos; $i++) {
	$nombre = utf8_decode($data->producto->{"extranuevo$i"});
	$nopcionesnuevas = $data->producto->{"nopcionesnuevas$i"};
	if($data->producto->{"multiple2$i"}){
		$tipo = 1;
	}else{
		$tipo = 2;
	}	
	$stmt = $con->prepare("INSERT INTO items_extras(iditem, nombre, fecharegistro, idusuario, aprobado) VALUES (?,?,?,?,?)");
	$stmt->bind_param("issii", $iditem, $nombre, $fecharegistro, $idusuario, $aprobado);
	$stmt->execute();
	if($stmt->error != ""){
		echo json_encode(array('respuesta' => false,'mensaje'=>'Error en el sistema. Contacte con un administrador.', 'error'=>$stmt->error));
	}
	$iditemextra = $stmt->insert_id;
	for ($j=1; $j <= $nopcionesnuevas; $j++) {
		$titulo = utf8_decode($data->producto->{"onueva_".$i."_$j"});
		if(isset($data->producto->{"precio_nuevo_op_".$i."_$j"})){
			$precio = $data->producto->{"precio_nuevo_op_".$i."_$j"};
		}else{
			$precio = 0;
		}
		$stmt   = $con->prepare("INSERT INTO items_adicion(iditemextra, tipo, titulo, precio, idusuario, fecharegistro, aprobado) VALUES (?,?,?,?,?,?,?)");
		$stmt->bind_param("iissisi", $iditemextra, $tipo, $titulo, $precio, $idusuario, $fecharegistro, $aprobado);
		$stmt->execute();
		if($stmt->error != ""){
			echo json_encode(array('respuesta' => false,'mensaje'=>'Error en el sistema. Contacte con un administrador.', 'error'=>$stmt->error));
		}		
	}
}

if($stmt->error == ""){
	echo json_encode(array('respuesta' => true));
}else{
	echo json_encode(array('respuesta' => false,'mensaje'=>'Error en el sistema. Contacte con un administrador.', 'error'=>$stmt->error));
	//reporte_error($idusuario,"",$stmt->error,"registronegocio.php",$sql);
}
?>