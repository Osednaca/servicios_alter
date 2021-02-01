<?php
include("includes/utils.php");
$post_date  		= file_get_contents("php://input");
$data 				= json_decode($post_date);
$idtiposervicio     = (int)$data->idtiposervicio;
$idtipovehiculo     = (int)$data->idtipovehiculo;
$idciudad     		= $data->idciudad;
$ciudad 			= $data->ciudad;
$valores 			= array();

//var_dump($data); die();

//Si tipo de servicio es mensajeria ignorar idciudad = 0 (todas las ciudades)
if($idtiposervicio==1 || $idtiposervicio == 4 || $idtiposervicio == 6){
	$idciudad = 0;
}
//Si tipo de Servicio es igual a Taxi de lujo se buscan las tarifas de taxi normal
if($idtiposervicio==3){
	$idtiposervicio = 2;
}

//Si tipo de servicio es taxi y ciudad diferente de medellin, bogota y cali entonces idciudad = 0
if ($idtiposervicio==2) {
	if($idciudad != 1 AND $idciudad != 2 AND $idciudad != 3){
		$idciudad = 0;
	}
}

if($idtipovehiculo == 7){
	$idtipovehiculo = 2;
}

//echo $ciudad; die();
//if($idciudad != "" OR $idciudad === 0){
//	//echo "Debug";
//	$sqltarifa   		= "SELECT valor,tipocalculo FROM tarifas WHERE idtiposervicio = ? AND idtipovehiculo = ? AND idciudad = ? ORDER BY tipocalculo";
//	$sqlbanderazo 		= "SELECT valor FROM banderazo WHERE idtiposervicio = ? AND idtipovehiculo = ? AND idciudad = ?";
//	$sqltarifaminima 	= "SELECT valor FROM tarifa_minima WHERE idtiposervicio = ? AND idtipovehiculo = ? AND idciudad = ?";
//	$ciudad = $idciudad;
//}elseif($ciudad != ""){
	$sqltarifa = "SELECT valor,tipocalculo FROM tarifas WHERE idtiposervicio = ? AND idtipovehiculo = ? AND ciudad LIKE CONCAT('%',?,'%') ORDER BY tipocalculo";
	$sqlbanderazo = "SELECT valor FROM banderazo WHERE idtiposervicio = ? AND idtipovehiculo = ? AND ciudad LIKE CONCAT('%',$ciudad,'%')";
	$sqltarifaminima 	= "SELECT valor FROM tarifa_minima WHERE idtiposervicio = ? AND idtipovehiculo = ? AND ciudad LIKE CONCAT('%',$ciudad,'%')";
//}
//echo "$ciudad // $idtipovehiculo // $idtiposervicio"; die();
//echo $sqltarifa; die();
$con->set_charset("utf8");
$stmt = $con->prepare($sqltarifa);
$stmt->bind_param("iis", $idtiposervicio,$idtipovehiculo,$ciudad);
$stmt->execute();
$stmt->bind_result($valor,$tipocalculo);

if($valor != ""){
	/* fetch value */
	while ($stmt->fetch()) {
		$valores[] = array('valor'=>$valor,'tipocalculo'=>$tipocalculo);
	}
}else{
	$stmt->free_result();
	$sqltarifa = "SELECT valor,tipocalculo FROM tarifas WHERE idtiposervicio = ? AND idtipovehiculo = ? AND ciudad = 0 ORDER BY tipocalculo";
	$sqlbanderazo = "SELECT valor FROM banderazo WHERE idtiposervicio = ? AND idtipovehiculo = ? AND ciudad = 0";
	$sqltarifaminima 	= "SELECT valor FROM tarifa_minima WHERE idtiposervicio = ? AND idtipovehiculo = ? AND ciudad = 0";

	$con->set_charset("utf8");
	$stmt = $con->prepare($sqltarifa);
	$stmt->bind_param("ii", $idtiposervicio,$idtipovehiculo);
	$stmt->execute();
	$stmt->bind_result($valor,$tipocalculo);	

	while ($stmt->fetch()) {
		$valores[] = array('valor'=>$valor,'tipocalculo'=>$tipocalculo);
	}	
}


$stmt->free_result();

//traer banderazo
$stmt = $con->prepare($sqlbanderazo);
$stmt->bind_param("ii", $idtiposervicio,$idtipovehiculo);
$stmt->execute();
$stmt->bind_result($banderazo);
$stmt->fetch();
$stmt->free_result();

//traer cobro metros
$stmt = $con->prepare("SELECT metroscobro,densidadtraficodivididopor FROM configuracionalter");
$stmt->execute();
$stmt->bind_result($metroscobros,$densidadtraficodivididopor);
$stmt->fetch();
$stmt->free_result();

//traer tarifa minima
$stmt = $con->prepare($sqltarifaminima);
$stmt->bind_param("ii", $idtiposervicio,$idtipovehiculo);
$stmt->execute();
$stmt->bind_result($tarifaminima);
$stmt->fetch();
$stmt->free_result();

if(empty($valores)){
	echo json_encode(array('respuesta' => false,'mensaje' => 'Error'));
}else{
	echo json_encode(array('respuesta' => true, 'valores'=> $valores, 'banderazo' => $banderazo,'metroscobros' => $metroscobros,'tarifaminima' => $tarifaminima,'ddtdp' => $densidadtraficodivididopor));
}

?>