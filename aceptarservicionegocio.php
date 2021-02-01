<?php
include("includes.php");

$post_date  	   = file_get_contents("php://input");
$data 			   = json_decode($post_date);
$token 			   = Auth::GetData(
    				    $data->token
				    );
$idusuario  	   = $token->id;
$idservicio 	   = $data->idservicio;
$tiempopreparacion = $data->tiempopreparacion;
//$direccionpartida = $data->direccionpartida

$stmt = $con->prepare("UPDATE servicio SET tiempopreparacion = ?, estatus = 10 WHERE idservicio = ?");
$stmt->bind_param("ii", $tiempopreparacion,$idservicio);
$stmt->execute();

$stmt = $con->prepare("SELECT servicio.fecharegistro,valor,totalaproximado, servicio.idcliente, nombre, apellido,telefonocelular,idtipopago FROM servicio 
                        INNER JOIN servicio_lugar USING(idservicio)
                        INNER JOIN lugar USING(idlugar)
                        INNER JOIN usuario ON servicio.idcliente = usuario.idusuario WHERE idservicio = ?");
$stmt->bind_param("i", $idservicio);
$stmt->execute();
$stmt->bind_result($fecharegistro,$costodomicilio,$total,$idcliente,$nombre,$apellido,$telefonocelular,$idtipopago);
$stmt->store_result();
$stmt->fetch();

//Items del servicio
$sql = "SELECT iditem,titulo,imagen,cantidad,instrucciones FROM domicilio_items
					INNER JOIN lugar_items USING(iditem)
					WHERE idservicio=? AND extra = 0";
$stmt4 = $con->prepare($sql);
$stmt4->bind_param("i", $idservicio);
$stmt4->execute();
$stmt4->bind_result($iditem,$titulo,$imagen,$cantidad,$instrucciones);	

while($stmt4->fetch()){
	$items[] = array('iditem'=>$iditem,'cantidad'=>$cantidad,'titulo'=>utf8_encode($titulo),'imagen'=>utf8_encode($imagen),'instrucciones'=>$instrucciones);
}
foreach ($items as &$i) {
	//Extras del servicio
	$iditemsql = $i["iditem"];
	$extras = array();
	$sql2 = "SELECT titulo,precio FROM domicilio_items
							INNER JOIN items_adicion ON domicilio_items.iditemadicion = items_adicion.iditemadicion
							WHERE idservicio=? AND domicilio_items.iditem = ? AND extra = 1";
	
	$stmt5 = $con->prepare($sql2);
	$stmt5->bind_param("ii", $idservicio, $iditemsql);
	$stmt5->execute();
	$stmt5->bind_result($titulo2,$precio);
	while($stmt5->fetch()){
		$extras[] = array('titulo'=>utf8_encode($titulo2),'precio'=>$precio);
	}
	if(!empty($extras)){
		$i["extras"] = $extras;
	}
}

$servicio[] = array('idservicio'=>$idservicio,'nombre'=>utf8_encode($nombre),'apellido'=>utf8_encode($apellido),'fecharegistro'=>$fecharegistro,'telefonocelular'=>$telefonocelular,'items'=>$items,'costodomicilio'=>$costodomicilio,'total'=>$total,'idtipopago'=>$idtipopago,'idcliente'=>$idcliente);

$stmt1 = $con->prepare("UPDATE notificacionlugar SET estatus=1 WHERE idservicio=?");
/* bind parameters for markers */
$stmt1->bind_param("i", $idservicio);
/* execute query */
$stmt1->execute();
if($stmt1->error != ""){
    //echo "TEST"; die();
	echo json_encode(array('respuesta' => false, 'error' => $stmt1->error));
    reporte_error("$idusuario","",$stmt1->error,"aceptarservicionegocio.php","");    
	die();
}else{
    //var_dump($servicio); die();
	echo json_encode(array('respuesta' => true,'servicio'=>$servicio));
}

?>