<?php
session_start();
include("includes.php");

$token      = Auth::GetData(
            $_POST["idusuario"]
          );
$idusuario  = $token->id;
$documento  = $_POST["nombredocumento"];
$placa      = $_POST["placa"];
$target_dir = "media/$documento/";
if($documento != "cedula"){
  $target_file = $target_dir . $placa;
}else{
  $target_file = $target_dir . $idusuario;
}
$uploadOk = 1;
$imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);

// Check if image file is a actual image or fake image
$check = getimagesize($_FILES[$documento]["tmp_name"]);
$maxsize    = 10000000;
$acceptable = array(
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
    if (compress($_FILES[$documento]["tmp_name"], $target_file.".jpg", 90)) {
        //Guardar nombre de archivo en base de datos
        $target_file = $placa.".jpg";
        //$stmt = $con->prepare("UPDATE vehiculo SET $nombredocumentosql = ?, fechamodificacion = ? WHERE idusuario = ?"); ////,imgusuario = ?,imgrut = ?
        //$stmt->bind_param("ssi", $target_file,$fechamodificacion,$idusuario);
        //$stmt->execute();
      echo json_encode(array('respuesta' => true,"imgusuario" => $target_file));
  } else {
      echo json_encode(array('respuesta' => false, 'mensaje' => "Error comprimiendo la imagen. Contacte con un administrador."));
  }       
}

?>