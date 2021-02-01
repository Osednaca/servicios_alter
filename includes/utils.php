<?php



include("includes/conexion.php");

include_once("includes/class.phpmailer.php");

if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');    // cache for 1 day
}

// Access-Control headers are received during OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");         

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

    exit(0);
}

date_default_timezone_set("America/Bogota");



// Definir $fields de la siguiente manera {idtabla,nombreoption}



function select_config_alter($config){

    global $con;

    $sql = "SELECT $config FROM configuracionalter WHERE idconfiguracion = 1";

    $stmt = $con->prepare($sql);

    $stmt->execute();

    if($stmt->error == ""){

        $stmt->bind_result($valor);

        $stmt->store_result();

        $stmt->fetch();

        $stmt->free_result();

        return $valor;

    }else{

        return false;

    }

}



function select_from_database($id,$function,$att,$table,$condition,$fields){

	global $db;

	$options = explode(",", $fields);

	$cant = count($options);

	//var_dump($options);

	?>

	<select class="form-control" <?= $att; ?> id="<?= $id ?>" name="<?= $id ?>">

		<option></option>

		<?php

		$sql = "SELECT $fields FROM $table $condition";

		foreach ($db->query($sql) as $row) {

		?>

			<option value="<?= $row[$options[0]] ?>"><?= $row[$options[1]] ?><?php if($cant>2): echo " ".$row[$options[2]]; endif; ?></option>

		<?php

		}

		?>

	</select>

	<?php

}



/**

 * Encrypting password

 * @param password

 * returns salt and encrypted password

 */

function hashSSHA($password) {



    $salt = sha1(rand());

    $salt = substr($salt, 0, 10);

    $encrypted = base64_encode(sha1($password . $salt, true) . $salt);

    $hash = array("salt" => $salt, "encrypted" => $encrypted);

    return $hash;

}



/**

 * Decrypting password

 * @param salt, password

 * returns hash string

 */

function checkhashSSHA($salt, $password) {



    $hash = base64_encode(sha1($password . $salt, true) . $salt);



    return $hash;

}



function calcular_edad($fecha){

	list($Y,$m,$d) = explode("-",$fecha);

    return( date("md") < $m.$d ? date("Y")-$Y-1 : date("Y")-$Y );

}



function dar_formato_monto($format, $number){

	setlocale(LC_MONETARY, 'es_VE');

	$regex  = '/%((?:[\^!\-]|\+|\(|\=.)*)([0-9]+)?'. 

              '(?:#([0-9]+))?(?:\.([0-9]+))?([in%])/'; 



    $locale = localeconv();

    preg_match_all($regex, $format, $matches, PREG_SET_ORDER); 

    foreach ($matches as $fmatch) { 

        $value = floatval($number); 

        $flags = array( 

            'fillchar'  => preg_match('/\=(.)/', $fmatch[1], $match) ? 

                           $match[1] : ' ', 

            'nogroup'   => preg_match('/\^/', $fmatch[1]) > 0, 

            'usesignal' => preg_match('/\+|\(/', $fmatch[1], $match) ? 

                           $match[0] : '+', 

            'nosimbol'  => preg_match('/\!/', $fmatch[1]) > 0, 

            'isleft'    => preg_match('/\-/', $fmatch[1]) > 0 

        ); 

        $width      = trim($fmatch[2]) ? (int)$fmatch[2] : 0; 

        $left       = trim($fmatch[3]) ? (int)$fmatch[3] : 0; 

        $right      = trim($fmatch[4]) ? (int)$fmatch[4] : $locale['int_frac_digits']; 

        $conversion = $fmatch[5]; 



        $positive = true; 

        if ($value < 0) { 

            $positive = false; 

            $value  *= -1; 

        } 

        $letter = $positive ? 'p' : 'n'; 



        $prefix = $suffix = $cprefix = $csuffix = $signal = ''; 



        $signal = $positive ? $locale['positive_sign'] : $locale['negative_sign']; 

        switch (true) { 

            case $locale["{$letter}_sign_posn"] == 1 && $flags['usesignal'] == '+': 

                $prefix = $signal; 

                break; 

            case $locale["{$letter}_sign_posn"] == 2 && $flags['usesignal'] == '+': 

                $suffix = $signal; 

                break; 

            case $locale["{$letter}_sign_posn"] == 3 && $flags['usesignal'] == '+': 

                $cprefix = $signal; 

                break; 

            case $locale["{$letter}_sign_posn"] == 4 && $flags['usesignal'] == '+': 

                $csuffix = $signal; 

                break; 

            case $flags['usesignal'] == '(': 

            case $locale["{$letter}_sign_posn"] == 0: 

                $prefix = '('; 

                $suffix = ')'; 

                break; 

        } 

        if (!$flags['nosimbol']) { 

            $currency = $cprefix . 

                        ($conversion == 'i' ? $locale['int_curr_symbol'] : $locale['currency_symbol']) . 

                        $csuffix; 

        } else { 

            $currency = ''; 

        } 

        $space  = $locale["{$letter}_sep_by_space"] ? ' ' : ''; 



        $value = number_format($value, $right, $locale['mon_decimal_point'], 

                 $flags['nogroup'] ? '' : $locale['mon_thousands_sep']); 

        $value = @explode($locale['mon_decimal_point'], $value); 



        $n = strlen($prefix) + strlen($currency) + strlen($value[0]); 

        if ($left > 0 && $left > $n) { 

            $value[0] = str_repeat($flags['fillchar'], $left - $n) . $value[0]; 

        } 

        $value = implode($locale['mon_decimal_point'], $value); 

        if ($locale["{$letter}_cs_precedes"]) { 

            $value = $prefix . $currency . $space . $value . $suffix; 

        } else { 

            $value = $prefix . $value . $space . $currency . $suffix; 

        } 

        if ($width > 0) { 

            $value = str_pad($value, $width, $flags['fillchar'], $flags['isleft'] ? 

                     STR_PAD_RIGHT : STR_PAD_LEFT); 

        } 



        $format = str_replace($fmatch[0], $value, $format); 

    } 

    return $format; $regex  = '/%((?:[\^!\-]|\+|\(|\=.)*)([0-9]+)?'. 

              '(?:#([0-9]+))?(?:\.([0-9]+))?([in%])/'; 

    if (setlocale(LC_MONETARY, 0) == 'C') { 

        setlocale(LC_MONETARY, ''); 

    } 

    $locale = localeconv(); 

    preg_match_all($regex, $format, $matches, PREG_SET_ORDER); 

    foreach ($matches as $fmatch) { 

        $value = floatval($number); 

        $flags = array( 

            'fillchar'  => preg_match('/\=(.)/', $fmatch[1], $match) ? 

                           $match[1] : ' ', 

            'nogroup'   => preg_match('/\^/', $fmatch[1]) > 0, 

            'usesignal' => preg_match('/\+|\(/', $fmatch[1], $match) ? 

                           $match[0] : '+', 

            'nosimbol'  => preg_match('/\!/', $fmatch[1]) > 0, 

            'isleft'    => preg_match('/\-/', $fmatch[1]) > 0 

        ); 

        $width      = trim($fmatch[2]) ? (int)$fmatch[2] : 0; 

        $left       = trim($fmatch[3]) ? (int)$fmatch[3] : 0; 

        $right      = trim($fmatch[4]) ? (int)$fmatch[4] : $locale['int_frac_digits']; 

        $conversion = $fmatch[5]; 



        $positive = true; 

        if ($value < 0) { 

            $positive = false; 

            $value  *= -1; 

        } 

        $letter = $positive ? 'p' : 'n'; 



        $prefix = $suffix = $cprefix = $csuffix = $signal = ''; 



        $signal = $positive ? $locale['positive_sign'] : $locale['negative_sign']; 

        switch (true) { 

            case $locale["{$letter}_sign_posn"] == 1 && $flags['usesignal'] == '+': 

                $prefix = $signal; 

                break; 

            case $locale["{$letter}_sign_posn"] == 2 && $flags['usesignal'] == '+': 

                $suffix = $signal; 

                break; 

            case $locale["{$letter}_sign_posn"] == 3 && $flags['usesignal'] == '+': 

                $cprefix = $signal; 

                break; 

            case $locale["{$letter}_sign_posn"] == 4 && $flags['usesignal'] == '+': 

                $csuffix = $signal; 

                break; 

            case $flags['usesignal'] == '(': 

            case $locale["{$letter}_sign_posn"] == 0: 

                $prefix = '('; 

                $suffix = ')'; 

                break; 

        } 

        if (!$flags['nosimbol']) { 

            $currency = $cprefix . 

                        ($conversion == 'i' ? $locale['int_curr_symbol'] : $locale['currency_symbol']) . 

                        $csuffix; 

        } else { 

            $currency = ''; 

        } 

        $space  = $locale["{$letter}_sep_by_space"] ? ' ' : ''; 



        $value = number_format($value, $right, $locale['mon_decimal_point'], 

                 $flags['nogroup'] ? '' : $locale['mon_thousands_sep']); 

        $value = @explode($locale['mon_decimal_point'], $value); 



        $n = strlen($prefix) + strlen($currency) + strlen($value[0]); 

        if ($left > 0 && $left > $n) { 

            $value[0] = str_repeat($flags['fillchar'], $left - $n) . $value[0]; 

        } 

        $value = implode($locale['mon_decimal_point'], $value); 

        if ($locale["{$letter}_cs_precedes"]) { 

            $value = $prefix . $currency . $space . $value . $suffix; 

        } else { 

            $value = $prefix . $value . $space . $currency . $suffix; 

        } 

        if ($width > 0) { 

            $value = str_pad($value, $width, $flags['fillchar'], $flags['isleft'] ? 

                     STR_PAD_RIGHT : STR_PAD_LEFT); 

        } 



        $format = str_replace($fmatch[0], $value, $format); 

    } 

    return $format; 

}



function replaceAccents($str) {



  $search = explode(",","ç,æ,œ,á,é,í,ó,ú,à,è,ì,ò,ù,ä,ë,ï,ö,ü,ÿ,â,ê,î,ô,û,å,ø,Ø,Å,Á,À,Â,Ä,È,É,Ê,Ë,Í,Î,Ï,Ì,Ò,Ó,Ô,Ö,Ú,Ù,Û,Ü,Ÿ,Ç,Æ,Œ");



  $replace = explode(",","c,ae,oe,a,e,i,o,u,a,e,i,o,u,a,e,i,o,u,y,a,e,i,o,u,a,o,O,A,A,A,A,A,E,E,E,E,I,I,I,I,O,O,O,O,U,U,U,U,Y,C,AE,OE");



  return str_replace($search, $replace, $str);

}



// Función que convierte los parámetros en UTF8 para evitar errores de serialización



function utf8_converter($array)

{

    array_walk_recursive($array, function(&$item, $key){

        if(!mb_detect_encoding($item, 'utf-8', true)){

                $item = utf8_encode($item);

        }

    });

 

    return $array;

}



function compress($source, $destination, $quality) {

    ini_set('memory_limit', '-1');

    $info = getimagesize($source);



    if ($info['mime'] == 'image/jpeg') 

        $image = imagecreatefromjpeg($source);



    elseif ($info['mime'] == 'image/gif') 

        $image = imagecreatefromgif($source);



    elseif ($info['mime'] == 'image/png') 

        $image = imagecreatefrompng($source);



    imagejpeg($image, $destination, $quality);



    return $destination;

}

function makeThumbnails( $updir, $img, $id){
    $thumbnail_width  = 200;
    $thumbnail_height = 180;    
    list($original_width, $original_height, $original_type) = getimagesize($updir."/".$img);

    $new_width = $thumbnail_width;
    $new_height = $thumbnail_height;

    if ($original_type === 1) {
        $imgt = "ImageGIF";
        $imgcreatefrom = "ImageCreateFromGIF";
    } else if ($original_type === 2) {
        $imgt = "ImageJPEG";
        $imgcreatefrom = "ImageCreateFromJPEG";
    } else if ($original_type === 3) {
        $imgt = "ImagePNG";
        $imgcreatefrom = "ImageCreateFromPNG";
    } else {
        return false;
    }

    $old_image = $imgcreatefrom($updir."/".$img);
    $new_image = imagecreatetruecolor($thumbnail_width, $thumbnail_height); // creates new image, but with a black background

    imagesavealpha($new_image, true);

    $trans_colour = imagecolorallocatealpha($new_image, 0, 0, 0, 127);
    imagefill($new_image, 0, 0, $trans_colour);
    
    $red = imagecolorallocate($new_image, 255, 0, 0);
    imagefilledellipse($new_image, 400, 300, 400, 300, $red);
    
    imagecopyresampled($new_image, $old_image, 0, 0, 0, 0, $new_width, $new_height, $original_width, $original_height);
    $imgt($new_image, "media/thumb/$img");
}



function reporte_error($idusuario,$email,$error,$archivo,$sql){

    if($idusuario != ""){

        global $con;

        $stmt = $con->prepare("SELECT nombre,apellido,correo FROM usuario WHERE idusuario = ?");

        $stmt->bind_param("s", $idusuario);

        $stmt->execute();

        $stmt->bind_result($nombre,$apellido,$email);

        $stmt->fetch();

        $stmt->free_result();    

        $body = "<p><b>Archivo:</b> $archivo</p><p><b>SQL: </b></p><p><b>Error:</b> ".$error."</p><p><b>IDUsuario: </b> $idusuario</p><p><b>Nombre: $nombre $apellido</b></p><p><b>Email: </b>$email</p>";

    }else{

        $body = "<p><b>Archivo:</b> $archivo</p><p><b>SQL: </b></p><p><b>Error:</b> ".$error."</p><p><b>IDUsuario: </b> Usuario no registrado</p><p><b>Email: $email</b></p>";

    }

    $mail = new PHPMailer;

    $mail->CharSet = "UTF-8";

    $mail->From = "alter@finespublicidad.com";

    $mail->FromName = "Alter";

    

    $mail->addAddress("on.navas@gmail.com"); //Oscar Navas

    //$mail->addAddress('cristianparada@zeroazul.com');  //Cristian Parada

        

    $mail->Subject = "Error en base de datos";

    $mail->Body = html_entity_decode("$body\r\n.");

    $mail->IsHTML(true);

    $mail->send();

}



function reporte_error_nequi($idusuario,$error,$archivo){

    if($idusuario != ""){

        global $con;

        $stmt = $con->prepare("SELECT nombre,apellido,correo FROM usuario WHERE idusuario = ?");

        $stmt->bind_param("s", $idusuario);

        $stmt->execute();

        $stmt->bind_result($nombre,$apellido,$email);

        $stmt->fetch();

        $stmt->free_result();    

        $body = "<p><b>Archivo:</b> $archivo</p><p><b>Cod. Error:</b> ".$error."</p><p><b>IDUsuario: </b> $idusuario</p><p><b>Nombre: $nombre $apellido</b></p><p><b>Email: </b>$email</p>";

    }   

    $mail = new PHPMailer;

    $mail->CharSet = "UTF-8";

    $mail->From = "alter@finespublicidad.com";

    $mail->FromName = "Alter";

    

    $mail->addAddress("on.navas@gmail.com"); //Oscar Navas

    $mail->addAddress('cristianparada@zeroazul.com');  //Cristian Parada

        

    $mail->Subject = "Error con Nequi";

    $mail->Body = html_entity_decode("$body\r\n.");

    $mail->IsHTML(true);

    $mail->send();    

}



function write_log($msg,$logfile){

    $myfile = fopen($logfile, "w") or die("Unable to open file!");

    fwrite($myfile, $msg);

}



/**

 *  An example CORS-compliant method.  It will allow any GET, POST, or OPTIONS requests from any

 *  origin.

 *

 *  In a production environment, you probably want to be more restrictive, but this gives you

 *  the general idea of what is involved.  For the nitty-gritty low-down, read:

 *

 *  - https://developer.mozilla.org/en/HTTP_access_control

 *  - http://www.w3.org/TR/cors/

 *

 */

function cors() {



    // Allow from any origin

    if (isset($_SERVER['HTTP_ORIGIN'])) {

        // Decide if the origin in $_SERVER['HTTP_ORIGIN'] is one

        // you want to allow, and if so:

        header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");

        header('Access-Control-Allow-Credentials: true');

        header('Access-Control-Max-Age: 86400');    // cache for 1 day

    }



    // Access-Control headers are received during OPTIONS requests

    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {



        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))

            // may also be using PUT, PATCH, HEAD etc

            header("Access-Control-Allow-Methods: GET, POST, OPTIONS");         



        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))

            header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");



        exit(0);

    }

}


function filterEmail($email) {
    $emailSplit = explode('@', $email);
    $email = $emailSplit[0];
    $email2 = $emailSplit[1];
    $len = strlen($email);
    $len2 = strlen($email2)-1;
    for($i = 2; $i < $len; $i++) {
        $email[$i] = '*';
    }

    for($i = 1; $i < ($len2-3); $i++) {
        $email2[$i] = '*';
    }
    return $email . '@' . $email2;
}

function registroCompleto($idusuario){

    global $con;

    $sql = "SELECT sexo,fechanacimiento,telefonocelular,direccion,idpais,idciudad,imgusuario FROM usuario WHERE idusuario = $idusuario";

    $stmt = $con->prepare($sql);

    $stmt->execute();

    if($stmt->error == ""){

        $stmt->bind_result($sexo,$fechanacimiento,$telefonocelular,$direccion,$idpais,$idciudad,$imgusuario);

        $stmt->store_result();

        $stmt->fetch();

        $stmt->free_result();
    }    
    if($sexo == "" || $fechanacimiento == "" || $telefonocelular == "" || $direccion == "" || $idpais == "" || $idciudad == "" || $imgusuario == ""){
        return false;
    }else{
        return true;
    }
}

function cedulaNivel1($idusuario){
    global $con;
    $stmt = $con->prepare("SELECT usuario.cedula FROM recomendaciones INNER JOIN usuario USING(idusuario) WHERE recomendaciones.cedula = (SELECT cedula FROM usuario WHERE idusuario = ?)");
    $stmt->bind_param("i", $idusuario);
    $stmt->execute();
    if($stmt->error != ""){
        echo json_encode(array("respuesta"=>"false", "error" => $stmt->error));
        die();
    }           
    $stmt->store_result();          
    $stmt->bind_result($cedula);
    $stmt->fetch();
    return $cedula;
}            

function cedulaNivel2($idusuario){
    global $con;
    $stmt = $con->prepare("SELECT usuario.cedula FROM recomendaciones INNER JOIN usuario USING(idusuario) WHERE recomendaciones.cedula = (SELECT cedula FROM usuario WHERE idusuario = (SELECT usuario.idusuario FROM recomendaciones INNER JOIN usuario USING(idusuario) WHERE recomendaciones.cedula = (SELECT cedula FROM usuario WHERE idusuario = ?)))");
    $stmt->bind_param("i", $idusuario);
    $stmt->execute();
    if($stmt->error != ""){
        echo json_encode(array("respuesta"=>"false", "error" => $stmt->error));
        die();
    }           
    $stmt->store_result();          
    $stmt->bind_result($cedula);
    $stmt->fetch();
    return $cedula;
}

function cedulaNivel3($idusuario){
    global $con;
    $stmt = $con->prepare("SELECT usuario.cedula FROM recomendaciones INNER JOIN usuario USING(idusuario) WHERE recomendaciones.cedula = (SELECT cedula FROM usuario WHERE idusuario = (SELECT usuario.idusuario FROM recomendaciones INNER JOIN usuario USING(idusuario) WHERE recomendaciones.cedula = (SELECT cedula FROM usuario WHERE idusuario = (SELECT usuario.idusuario FROM recomendaciones INNER JOIN usuario USING(idusuario) WHERE recomendaciones.cedula = (SELECT cedula FROM usuario WHERE idusuario = ?)))))");
    $stmt->bind_param("i", $idusuario);
    $stmt->execute();
    if($stmt->error != ""){
        echo json_encode(array("respuesta"=>"false", "error" => $stmt->error));
        die();
    }
    $stmt->store_result();          
    $stmt->bind_result($cedula);
    $stmt->fetch();
    return $cedula;
}

?>