<?php

include("includes.php");

$post_date  	 		= file_get_contents("php://input");
$data 			 		= json_decode($post_date);

$token 					= 	Auth::GetData(
     							$data->token
 						  	);
$idusuario 				= 	$token->id;
$idlugar          =   $data->negocio->idlugar;
$nombre			      = 	utf8_decode($data->negocio->nombrelugar);
$categorias       =   $data->negocio->categoria;
$categoria        =   $data->negocio->tipolugar;
$ciudad					  = 	$data->negocio->ciudad;
$sqlimagen        =   "";
$sqlimagen2       =   "";

if(!empty($data->negocio->descripcion)){
	$descripcion 		= 	utf8_decode($data->negocio->descripcion);
}else{
	$descripcion 		=   "";
}
$direccion				= 	utf8_decode($data->negocio->direccion);
$fecharegistro 		=   date("Y-m-d H:i:s");
//Horarios
if(!empty($data->negocio->desdelunes)){
$desdelunes                   =     $data->negocio->desdelunes;
$hastalunes                   =     $data->negocio->hastalunes;
}
//Sabado
if(!empty($data->negocio->desdesabado)){
$desdesabado                  =     $data->negocio->desdesabado;
$hastasabado                  =     $data->negocio->hastasabado;
}
//Domingo
if(!empty($data->negocio->desdedomingo)){
$desdedomingo                 =     $data->negocio->desdedomingo;
$hastadomingo                 =     $data->negocio->hastadomingo;
}

if(!empty($data->negocio->editarlogo)){
  $imagen     = $data->nombreimagen.".png";
  $sqlimagen  = ",imagen='$imagen'";
}else{
  $imagen     = $data->nombreimagen.".png";
}

if(!empty($data->negocio->editarfotoproducto)){
  $imagen2     = $data->foto_producto.".png";
  $sqlimagen2  = ",foto_producto='$imagen2'";
}else{
  $imagen2     = $data->foto_producto.".png";
}

$lat = $data->negocio->lat1;
$lng = $data->negocio->lng1;

//echo $lat." ".$lng; die;

//Validar que el negocio no este utilizado por otro usuario
if ($nombre !=''){

	$stmt = $con->prepare("UPDATE lugar SET idlugarcategoria=?,nombrelugar=?,descripcion=?,direccion=?,ciudad=?,fechamodificacion=?,lat=?,lng=? $sqlimagen $sqlimagen2 WHERE idlugar= ?");
	/* bind parameters for markers */
	$stmt->bind_param("ssssssssi", $categoria,$nombre,$descripcion,$direccion,$ciudad,$fecharegistro,$lat,$lng,$idlugar);
  $stmt->execute();
  if($stmt->error == ""){
    $nsedes        = $data->negocio->nsedes;
    $nsedesnuevas  = $data->negocio->nsedesnuevas;
    for ($i=2; $i <= $nsedes; $i++) {
      $telefonocelular = $data->negocio->{"telefono$i"};
      //$stmt = $con->prepare("SELECT idusuario FROM usuario WHERE telefonocelular = ?");
      //$stmt->bind_param("s", $telefonocelular);
      //$stmt->execute();
      //$stmt->bind_result($idusuario2);
      //$stmt->fetch();
      //if($idusuario2 != ""){
      //    echo json_encode(array('respuesta' => false,'mensaje'=>'El telefono ya se encuentra registrado.'));
      //    $con->rollback();
      //    die();
      //}
      $direccion= $data->negocio->{"direccion$i"};
      $latlug   = $data->negocio->{"lat$i"};
      $lnglug   = $data->negocio->{"lng$i"}; 
      $idlugar2 = $data->negocio->{"id$i"}; 
      //Para poligono ???
      $stmt = $con->prepare("UPDATE lugar SET idlugarcategoria = ?, nombrelugar = ?, descripcion = ?, direccion = ?, idciudad = ?, fechamodificacion = ?  $sqlimagen WHERE idlugar = ?");
      /* bind parameters for markers */
      $stmt->bind_param("isssisi", $categoria,$nombre,$descripcion,$direccion,$ciudad,$fecharegistro,$idlugar2);
      $stmt->execute();

      $correo          = "cuentasede@alterclub.com";
      $hash            = hashSSHA($nombre);
      $password        = $hash["encrypted"];
      $salt            = $hash["salt"];
      $nombreusuario   = $nombre;
      $apellido        = $nombre;
      $stmt   = $con->prepare("UPDATE usuario SET idusuario = ?, password = ?, salt = ?, cedula = ?, nombre = ?, apellido = ?, telefonocelular = ?, fechamodificacion = ? WHERE idsede = ?");
      $stmt->bind_param("ssssssssi", $telefonocelular,$password,$salt,$telefonocelular,$nombreusuario,$apellido,$telefonocelular,$fecharegistro,$idlugar2);
      $stmt->execute();
    }
    for ($i=2; $i <= $nsedesnuevas; $i++) {                   
        $telefonocelular = $data->negocio->{"telnuevo$i"};
        $stmt = $con->prepare("SELECT idusuario FROM usuario WHERE telefonocelular = ?");
        $stmt->bind_param("s", $telefonocelular);
        $stmt->execute();
        $stmt->bind_result($idusuario2);
        $stmt->fetch();
        if($idusuario2 != ""){
            echo json_encode(array('respuesta' => false,'mensaje'=>'El telefono ya se encuentra registrado.'));
            $con->rollback();
            die();
        }

        $direccion= $data->negocio->{"dirnuevo$i"};
        $latlug   = $data->negocio->{"latnuevo$i"};
        $lnglug   = $data->negocio->{"lngnuevo$i"};
        $padre    = 0;
        $aprobado = 0;
        $estatus  = 0;
        //Para poligono ???
        $stmt = $con->prepare("INSERT INTO lugar(idlugarcategoria, nombrelugar, descripcion, imagen, estatus, aprobado, direccion, ciudad, fecharegistro, idusuario,lat,lng,padre,tiporegistro) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,1)");
        /* bind parameters for markers */
        $stmt->bind_param("isssiisssissi", $categoria,$nombre,$descripcion,$imagen,$estatus,$aprobado,$direccion,$ciudad,$fecharegistro,$idusuario,$latlug,$lnglug,$padre);
        /* execute query */
        $stmt->execute();
        if($stmt->error != ""){
          echo json_encode(array("respuesta"=>false,"error"=>$stmt->error));
          die();
        }
        if($i != 1){
            $idlugar2        = $stmt->insert_id;
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

      $stmt = $con->prepare("DELETE FROM lugarcategoria WHERE idlugar = ?");
      $stmt->bind_param("i", $idlugar);
      $stmt->execute();
      //Guardar categorias
      foreach ($categorias as $key => $value) {
          $stmt = $con->prepare("INSERT INTO lugarcategoria(idlugar, idcategoria) VALUES (?,?)");
          /* bind parameters for markers */
          $stmt->bind_param("ii", $idlugar,$value);
          /* execute query */
          $stmt->execute();
      }

      //Insert horarios if exist
      $stmt2 = $con->prepare("DELETE FROM lugar_horario WHERE idlugar = ?");
      $stmt2->bind_param("i",$idlugar);
      $stmt2->execute();
      if($stmt2->error != ""){
          echo "Error: ".$stmt2->error; die();
      }
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
		echo json_encode(array('respuesta' => true));
	}else{
		echo json_encode(array('respuesta' => false,'mensaje'=>'Error en el sistema. Contacte con un administrador.', 'error'=>$stmt->error));
		//reporte_error($idusuario,"",$stmt->error,"registronegocio.php",$sql);
	}
}
?>