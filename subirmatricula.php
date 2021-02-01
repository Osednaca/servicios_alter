<?php
include("includes.php");

$post_date  = file_get_contents("php://input");
$data       = json_decode($post_date);
$token      = Auth::GetData(
              $data->token
            );
$idusuario  = $token->id;

$idvehiculo = $_POST["idvehiculo"];
$placa      = $_POST["placa"];
$documento  = $_POST["nombredocumento"];
$target_dir = "media/$documento/";
$fechamodificacion = date("Y-m-d H:i:s");
$random     = rand(1000,5000);

if ($documento == "matricula") {
    $nombredocumentosql = "imgmatricula";
}elseif ($documento == "matricula1") {
    $nombredocumentosql = "imgmatricula1";
}

$target_file = $target_dir . $placa ."_".$random;

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
    if($_FILES[$documento]['type'] != "application/pdf"){
        if (compress($_FILES[$documento]["tmp_name"], $target_file.".jpg", 90)) {
            //Guardar nombre de archivo en base de datos
            $target_file = $placa."_".$random.".jpg";
            $stmt = $con->prepare("UPDATE vehiculo SET $nombredocumentosql = ? WHERE idvehiculo = ?"); //,imgusuario = ?,imgrut = ?
            $stmt->bind_param("si", $target_file,$idvehiculo);
            $stmt->execute();
            //if($documento == "foto"){
            //    $ultimo = true;
            //}else{
            //    $ultimo = false;
            //}
            echo json_encode(array('respuesta' => true,"imgusuario" => $target_file));
        } else {
            echo json_encode(array('respuesta' => false, 'mensaje' => "Error comprimiendo la imagen. Contacte con un administrador."));
        }
    }else{
            $tmp_name = $_FILES[$documento]["tmp_name"];
            move_uploaded_file($tmp_name, $target_file.".pdf");        
            $target_file = $placa.".pdf";
            $stmt = $con->prepare("UPDATE vehiculo SET $nombredocumentosql = ? WHERE idvehiculo= ?"); //,imgusuario = ?,imgrut = ?
            $stmt->bind_param("si", $target_file,$idvehiculo);
            $stmt->execute();
            echo json_encode(array('respuesta' => true,"imgusuario" => $target_file));
    }
}

?>