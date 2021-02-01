<?php
	include("includes/utils.php");
	$post_date  	= file_get_contents("php://input");
	$data 			= json_decode($post_date);
	$fields  		= $data->config;
	$config  		= array();

	$options 		= explode(",", $fields);

$sql = "SELECT $fields FROM configuracionalter WHERE idconfiguracion=1";

//$stmt->bind_param("i",$idservicio);
$result = $con->query($sql);
$row = $result->fetch_array(MYSQLI_ASSOC);

foreach ($options as $key => $value) {
	$config[] = array($value => $row[$value]);
}

if(!empty($config)){
	echo json_encode(array('respuesta' => true, 'config'=>$config));
}else{
	echo json_encode(array('respuesta' => false, 'mensaje' => $stmt->error));
}

?>