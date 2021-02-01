<?php
include("includes/utils.php");
$post_date  = file_get_contents("php://input");
$data 		= json_decode($post_date);
$banners    = array();    

$stmt = $con->prepare("SELECT imagen,url FROM banner");

/* execute query */
$stmt->execute();

/* bind result variables */
$stmt->bind_result($imagen,$url);

while($stmt->fetch()){
    $banners[] = array("imagen"=>$imagen,"url"=>utf8_encode($url));
}
$stmt->free_result();

if(empty($banners)){
	echo json_encode(array('respuesta' => false,'mensaje' => 'Error'));
}else{
	echo json_encode(array('respuesta' => true, 'banners'=> $banners));
}

?>