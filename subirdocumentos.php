<?php
session_start();
include("includes.php");
$token     = Auth::GetData(
             $_POST["token"]
           );
$idusuario = $token->id;
$cedula    = $_POST["cedula"];

$documento  = $_POST["nombredocumento"];
$target_dir = "media/$documento/";
$fechamodificacion = date("Y-m-d H:i:s");

if($documento == "cedula"){
    $nombredocumentosql = "imgcedula";
}elseif ($documento == "cedula1") {
    $nombredocumentosql = "imgcedula1";
}elseif ($documento == "rut") {
    $nombredocumentosql = "imgrut";
}elseif ($documento == "foto") {
    $nombredocumentosql = "imgusuario";
}elseif ($documento == "matricula") {
    $nombredocumentosql = "imgmatricula";
}elseif ($documento == "matricula1") {
    $nombredocumentosql = "imgmatricula1";
}

$target_file = $target_dir . $cedula;
$uploadOk = 1;
$imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);

// Check if image file is a actual image or fake image
$check = getimagesize($_FILES[$documento]["tmp_name"]);
$maxsize    = 10000000;
$acceptable = array(
    'application/pdf',
    'image/jpeg',
    'image/jpg',
    'image/gif',
    'image/png'
);

if(isset($_POST["modificar"])){
    $idsolicitud = $_POST["idsolicitud"];
    $stmt = $con->prepare("UPDATE cambiodocumentos SET estatus WHERE idsolicitud = ?");
    $stmt->bind_param("i", $idsolicitud);
    $stmt->execute();
}

if(($_FILES[$documento]['size'] >= $maxsize) OR ($_FILES[$documento]["size"] == 0)) {
    $mensaje = 'El archivo es muy grande debe ser menor de 10MB.'; 
    $uploadOk = 0;
}

if(!in_array($_FILES[$documento]['type'], $acceptable) AND (!empty($_FILES[$documento]["type"]))) {
    $mensaje = 'Tipo de archivo no valido.'; 
    $uploadOk = 0;
}

// Check if $uploadOk is set to 0 by an error
if ($uploadOk == 0) {
    echo json_encode(array('respuesta' => false, 'mensaje' => "Error: ".$mensaje));
// if everything is ok, try to upload file
} else {
    //
    if($documento == "foto"){
        if($_POST["sexo"] != "" AND $_POST["sexo"] != "null"){
            $sexo           = $_POST["sexo"];
        }else{
            $sexo           = NULL;
        }
        if($_POST["fechanacimiento"] != "" AND $_POST["fechanacimiento"] != "null"){
            $fechanacimiento    = $_POST["fechanacimiento"];
            $fechanacimiento    = explode("/", $_POST["fechanacimiento"]);
            $fechanacimiento    = $fechanacimiento[2]."-".$fechanacimiento[1]."-".$fechanacimiento[0];
        }else{
            $fechanacimiento    = NULL;
        }
        if($_POST["telefonocelular"] != "" AND $_POST["telefonocelular"] != "null"){
            $telefonocelular    = $_POST["telefonocelular"];
        }else{
            $telefonocelular    = NULL;
        }
        if($_POST["direccion"] != "" AND $_POST["direccion"] != "null"){
            $direccion          = $_POST["direccion"];
        }else{
            $direccion          = NULL;
        }
        if($_POST["idpais"] != "" AND $_POST["idpais"] != "null"){
            $idpais             = $_POST["idpais"];
        }else{
            $idpais             = NULL;
        }
        if($_POST["idciudad"] != "" AND $_POST["idciudad"] != "null"){
            $idciudad           = $_POST["idciudad"];
        }else{
            $idciudad           = NULL;
        }
        $stmt = $con->prepare("UPDATE usuario SET sexo = ?,fechanacimiento = ?,telefonocelular = ?,direccion = ?,idpais = ?,idciudad = ? WHERE idusuario = ?");
        $stmt->bind_param("isssiii", $sexo,$fechanacimiento,$telefonocelular,$direccion,$idpais,$idciudad,$idusuario);
        $stmt->execute();
        if($stmt->error != ""){
            echo json_encode(array('respuesta' => false, 'error' => $stmt->error));
        }
    }
    if($_FILES[$documento]['type'] != "application/pdf"){
        if (compress($_FILES[$documento]["tmp_name"], $target_file.".jpg", 90)) {
            //Crear unos thumb nails si tipodocumento es foto
            if($documento == "foto"){
                makeThumbnails($target_dir, $cedula.".jpg", 1);
            }
            //Guardar nombre de archivo en base de datos
            $target_file = $cedula.".jpg";
            $stmt = $con->prepare("UPDATE usuario SET $nombredocumentosql = ?, fechamodificacion = ? WHERE idusuario = ?"); //,imgusuario = ?,imgrut = ?
            $stmt->bind_param("ssi", $target_file,$fechamodificacion,$idusuario);
            $stmt->execute();
            //validar si tiene todos los datos de registro
            if(registroCompleto($idusuario)){
                $registrocompleto = true;
            }else{
                $registrocompleto = false;
            }
            echo json_encode(array('respuesta' => true,"imgusuario" => $target_file,"registrocompleto" => $registrocompleto));
        } else {
            echo json_encode(array('respuesta' => false, 'mensaje' => "Error comprimiendo la imagen. Contacte con un administrador."));
        }
    }else{
            $tmp_name = $_FILES[$documento]["tmp_name"];
            move_uploaded_file($tmp_name, $target_file.".pdf");        
            $target_file = $cedula.".pdf";
            $stmt = $con->prepare("UPDATE usuario SET $nombredocumentosql = ?, fechamodificacion = ? WHERE idusuario = ?"); //,imgusuario = ?,imgrut = ?
            $stmt->bind_param("ssi", $target_file,$fechamodificacion,$idusuario);
            $stmt->execute();
            echo json_encode(array('respuesta' => true,"imgusuario" => $target_file));
    }
}

?>