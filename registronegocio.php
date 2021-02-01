<?php

include("includes.php");

//$con->autocommit(false);
$post_date              = file_get_contents("php://input");
$data                   = json_decode($post_date);

$token                  =   Auth::GetData(
                                $data->token
                            );
$idusuario              =   $token->id;
$nombre                 =   utf8_decode($data->negocio->nombre);
$categorias             =   $data->negocio->categoria;
$categoria              =   $data->negocio->tipolugar;
$ciudad                 =   $data->negocio->ciudad;
if(!empty($data->negocio->descripcion)){
    $descripcion        =   utf8_decode($data->negocio->descripcion);
}else{
    $descripcion        =   "";
}

//Horarios
if(!empty($data->negocio->desdelunes)){
$desdelunes             =   $data->negocio->desdelunes;
$hastalunes             =   $data->negocio->hastalunes;
}
//Sabado
if(!empty($data->negocio->desdesabado)){
$desdesabado            =   $data->negocio->desdesabado;
$hastasabado            =   $data->negocio->hastasabado;
}
//Domingo
if(!empty($data->negocio->desdedomingo)){
$desdedomingo           =   $data->negocio->desdedomingo;
$hastadomingo           =   $data->negocio->hastadomingo;
}

$estatus                =   0;
$aprobado               =   0;
$imagen                 =   $data->nombreimagen.".png";
$foto_producto          =   $data->foto_producto.".png";
$fecharegistro          =   date("Y-m-d H:i:s");
$porcobroproducto       =   0.15;

//var_dump($ciudad); die();
$estatus                =   1;
    //Validar que el negocio no este utilizado por otro usuario
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
        if(!empty($data->negocio->nsedes)){
            $nsedes  = $data->negocio->nsedes;
        }else{
            $nsedes  = 1;
        }
        for ($i=1; $i <= $nsedes; $i++) {                   
            if($i == 1){
                $padre = 1;
            }else{
                $padre = 0;
                $telefonocelular = $data->negocio->{"telefono$i"};
                $stmt = $con->prepare("SELECT idusuario FROM usuario WHERE idusuario = ?");
                $stmt->bind_param("s", $telefonocelular);
                $stmt->execute();
                $stmt->bind_result($idusuario2);
                $stmt->fetch();
                $stmt->free_result();
                if($idusuario2 != ""){
                    $stmt = $con->prepare("DELETE FROM lugar WHERE idlugar = ?");
                    $stmt->bind_param("i", $idlugar);
                    $stmt->execute();

                    $stmt = $con->prepare("DELETE FROM usuario WHERE idsede = ?");
                    $stmt->bind_param("i", $idlugar);
                    $stmt->execute();
                    echo json_encode(array('respuesta' => false,'mensaje'=>'El telefono ya se encuentra registrado.'));
                    die();
                }
            }
            $direccion= $data->negocio->{"direccion$i"};
            $latlug   = $data->negocio->{"lat$i"};
            $lnglug   = $data->negocio->{"lng$i"}; 
            //Para poligono ???
            $stmt2 = $con->prepare("INSERT INTO lugar(idlugarcategoria, nombrelugar, descripcion, imagen, foto_producto, estatus, aprobado, direccion, ciudad, fecharegistro, idusuario,lat,lng,padre,tiporegistro,porcobroproducto) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,1,?)");

                /* bind parameters for markers */
            $stmt2->bind_param("issssiisssissid", $categoria,$nombre,$descripcion,$imagen,$foto_producto,$estatus,$aprobado,$direccion,$ciudad,$fecharegistro,$idusuario,$latlug,$lnglug,$padre,$porcobroproducto);
                    /* execute query */
            $stmt2->execute();
            if($i == 1){
                $idlugar = $stmt2->insert_id;
            }
            if($i != 1){
                $idlugar2        = $stmt2->insert_id;
                $correo          = "cuentasede@alterclub.com";
                $hash            = hashSSHA($nombre);
                $password        = $hash["encrypted"];
                $salt            = $hash["salt"];
                $nombreusuario   = $nombre;
                $apellido        = $nombre;
                $stmt   = $con->prepare("INSERT INTO usuario(idusuario,correo, password, salt, cedula, nombre, apellido, telefonocelular, estatus, disponibilidad, fecharegistro, tipousuario,  promo, idsede) VALUES (?,?,?,?,?,?,?,?,1,0,?,3,2,?)");
                $stmt->bind_param("sssssssssi", $telefonocelular,$correo,$password,$salt,$telefonocelular,$nombreusuario,$apellido,$telefonocelular,$fecharegistro,$idlugar2);
                $stmt->execute();
            }
        }
        //Guardar categorias
        foreach ($categorias as $key => $value) {
            $stmt = $con->prepare("INSERT INTO lugarcategoria(idlugar, idcategoria) VALUES (?,?)");
            /* bind parameters for markers */
            $stmt->bind_param("ii", $idlugar,$value);
            /* execute query */
            $stmt->execute();
        }
        //Insert horarios if exist
        if(!empty($desdelunes)){
            $stmt2 = $con->prepare("INSERT INTO lugar_horario(idlugar, tipohorario, desde, hasta) VALUES (?,1,?,?)");
            $stmt2->bind_param("iss",$idlugar,$desdelunes,$hastalunes);
            $stmt2->execute();
            if($stmt2->error != ""){
                echo "Error: ".$stmt2->error; die();
            }
        }    
        if(!empty($desdesabado)){
            $stmt3 = $con->prepare("INSERT INTO lugar_horario(idlugar, tipohorario, desde, hasta) VALUES (?,2,?,?)");
            $stmt3->bind_param("iss",$idlugar,$desdesabado,$hastasabado);
            $stmt3->execute();
            if($stmt3->error != ""){
                echo "Error: ".$stmt3->error; die();
            }
        }
        if(!empty($desdedomingo)){            
            $stmt4 = $con->prepare("INSERT INTO lugar_horario(idlugar, tipohorario, desde, hasta) VALUES (?,3,?,?)");
            $stmt4->bind_param("iss",$idlugar,$desdedomingo,$hastadomingo);
            $stmt4->execute();
            if($stmt4->error != ""){
                echo "Error: ".$stmt4->error; die();
            }                   
        }
        if($stmt->error == ""){
            echo json_encode(array('respuesta' => true));
        }else{
            echo json_encode(array('respuesta' => false,'mensaje'=>'Error en el sistema. Contacte con un administrador.', 'error'=>$stmt->error));
            //reporte_error($idusuario,"",$stmt->error,"registronegocio.php",$sql);
        }            
    }else{
    // Sino muestra un error
    echo json_encode(array('respuesta' => false,'mensaje'=>'El nombre de tu negocio ya se encuentra registrado.'));
    }
?>