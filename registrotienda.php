<?php

include("includes.php");

//$con->autocommit(false);
$post_date              = file_get_contents("php://input");
$data                   = json_decode($post_date);

$token                  =   Auth::GetData(
                                $data->token
                            );
$idusuario              =   $token->id;
$nombre                 =   utf8_decode($data->tienda->nombretienda);
$nombrepropietario      =   utf8_decode($data->tienda->nombrepropietario);
$ciudad                 =   $data->tienda->ciudad;

$estatus                =   0;
$aprobado               =   0;
$imagen                 =   $data->nombreimagen.".png";
$fecharegistro          =   date("Y-m-d H:i:s");
$direccion              = $data->tienda->direccion;
$latlug                 = $data->tienda->lat;
$lnglug                 = $data->tienda->lng; 
$categoria              = 19;
$padre                  = 1;

//var_dump($categoria); die();
$estatus                =   1;
//Validar que el negocio no este utilizado por otro usuario
if ($nombre !=''){
    $stmt = $con->prepare("SELECT idlugar FROM lugar WHERE nombrelugar = ?");
    /* bind parameters for markers */
    $stmt->bind_param("s", $nombre);

    /* execute query */
    $stmt->execute();

    /* bind result variables */
    $stmt->bind_result($idlugar);

    /* fetch value */
    $stmt->fetch();

    $stmt->free_result();
    //Si no existe ninguno guarda el registro en BD
    if($idlugar==""){
        $stmt = $con->prepare("INSERT INTO lugar(idlugarcategoria, nombrelugar, imagen, estatus, aprobado, direccion, ciudad, fecharegistro, idusuario,lat,lng,padre,tiporegistro,nombrepropietario) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,1,?)");
        /* bind parameters for markers */
        $stmt->bind_param("issiisssissis",$categoria,$nombre,$imagen,$estatus,$aprobado,$direccion,$ciudad,$fecharegistro,$idusuario,$latlug,$lnglug,$padre,$nombrepropietario);
        /* execute query */
        $stmt->execute();

        if($stmt->error == ""){
            echo json_encode(array('respuesta' => true));
        }else{
            echo json_encode(array('respuesta' => false,'mensaje'=>'Error en el sistema. Contacte con un administrador.', 'error'=>$stmt->error));
            //reporte_error($idusuario,"",$stmt->error,"registronegocio.php",$sql);
        }            
    }
}else{
    // Sino muestra un error
    echo json_encode(array('respuesta' => false,'mensaje'=>'El nombre de tu negocio ya se encuentra registrado.'));
}
?>