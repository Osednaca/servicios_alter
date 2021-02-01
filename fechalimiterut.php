<?php
    include("includes.php");

    $post_date   = file_get_contents("php://input");
    $data        = json_decode($post_date);
    $token       = Auth::GetData(
                     $data->token
                   );
    $idusuario   = $token->id;
    $start       = new DateTime(date("Y-m-d"), new DateTimeZone("UTC"));
    $time        = strtotime("now");
    $tiemporut   = select_config_alter("tiempolimiterut");
    $fechalimite = date("Y-m-d", strtotime("+$tiemporut", $time));
    //echo $fechalimite; die();
    $stmt       = $con->prepare("UPDATE usuario SET limiterut=? WHERE idusuario = ?");
    $stmt->bind_param("si", $fechalimite,$idusuario);
    $stmt->execute();

    if($stmt->error == ""){
        echo json_encode(array("respuesta" => true));
    }else{
        echo json_encode(array("respuesta" => false, "error" => $stmt->error));
    }
?>