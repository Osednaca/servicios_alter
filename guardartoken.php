<?php
include("includes.php");

$post_date  	  = file_get_contents("php://input");
$data 			  = json_decode($post_date);
$token 			  = Auth::GetData(
    				$data->tokenid
				  );
$idusuario  	  = $token->id;
$tokefcm		  = $data->token;


$stmt = $con->prepare("UPDATE usuario SET tokenfcm=? WHERE idusuario=?");
/* bind parameters for markers */
$stmt->bind_param("si", $tokefcm,$idusuario);
//var_dump($tokefcm);
/* execute query */
$stmt->execute();
echo json_encode(array('respuesta' => true));


?>