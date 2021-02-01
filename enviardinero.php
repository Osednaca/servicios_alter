<?php
include_once("includes/class.phpmailer.php");
include("includes.php");

$post_date  	= file_get_contents("php://input");
$data 			= json_decode($post_date);
$token 			= Auth::GetData(
    				$data->token
				  );
$idusuario  	= $token->id;
$cedula 		= $data->cedula;	
$valor			= $data->valor;
$ip				= $data->ip;
$pin			= $data->pin;
$estatus		= 1;		
$fecharegistro  = date("Y-m-d H:i:s");

//Consultar saldo y validar que el monto que quiere retirar es menor o igual a su saldo disponible
$stmt = $con->prepare("SELECT saldoalter,correo,pinenviodinero,cedula FROM usuario WHERE idusuario = ?");
$stmt->bind_param("i", $idusuario);
$stmt->execute();
$stmt->bind_result($saldodisponible,$correo,$pinenviodinero,$cedula_u);
$stmt->fetch();
$stmt->free_result();

//Validar que la cedula exista
$stmt = $con->prepare("SELECT idusuario FROM usuario WHERE cedula = ?");
$stmt->bind_param("i", $cedula);
$stmt->execute();
$stmt->bind_result($idusuario2);
$stmt->fetch();
$stmt->free_result();
if($idusuario2 == ""){
	echo json_encode(array('respuesta' => false,'mensaje' => "La cedula no esta registrada."));
	die();
}
//echo "idusuario: $idusuario // Saldo: $saldodisponible";

if($pinenviodinero != $pin){
	echo json_encode(array('respuesta' => false,'mensaje' => "El pin es incorrecto"));
	die();
}

if($valor <= $saldodisponible  && $cedula!=$cedula_u){
	$stmt1 = $con->prepare("INSERT INTO enviodedinero(idusuario, cedula, valor, estatus, fechaenvio, ip) VALUES (?,?,?,?,?,?)");
	
	$stmt1->bind_param("iisiss", $idusuario,$cedula,$valor,$estatus,$fecharegistro,$ip);
	
	$stmt1->execute();
	
	//$idticket = $stmt->insert_id;
	
	//validar que todo salga bien con $stmt->error
	if($stmt->error==""){
		//Descontar el dinero de su saldo
		$stmt2 = $con->prepare("UPDATE usuario SET saldoalter = saldoalter - ? WHERE idusuario = ?");
		$stmt2->bind_param("si", $valor, $idusuario);
		$stmt2->execute();
		//Sumarselo al otro usuario
		$stmt3 = $con->prepare("UPDATE usuario SET saldoalter = saldoalter + ? WHERE cedula = ?");
		$stmt3->bind_param("si", $valor, $cedula);
		$stmt3->execute();		

		//Enviar Push Notification
    	$stmt = $con->prepare("SELECT tokenfcm FROM usuario WHERE cedula = ?  AND tokenfcm != ''");
    	$stmt->bind_param("s", $cedula);
    	$stmt->execute();      
    	$stmt->bind_result($tokenfcm);
    	$stmt->fetch();

if(!empty($tokenfcm)){
    $json_data = [
        "to" => $tokenfcm,
        "notification" => [
            "body" => "Te han enviado $valor a traves de Alter.",
            "title" => "Alter Envio de dinero.",
            "icon" => "https://alterclub.com/icon.png",
        ],
        "data" => [
            "message"=>"ANYTHING EXTRA HERE",
            "picture"=>"http://36.media.tumblr.com/c066cc2238103856c9ac506faa6f3bc2/tumblr_nmstmqtuo81tssmyno1_1280.jpg",
        ]
    ];
    
    $headers = array(
        'Content-Type:application/json',
        'Authorization:key=AAAAaRYi1uw:APA91bF9PgDmd5bopgxfkEkMJawdN8aERwSlgBuMWNNSgRpiLq0pTLZOJG8NgRjjxOsWLEyL8kqXtQpRnulAlIQjr475iE6UwI80uWFdTOCOOQLKOQ1wuV8fxC91JUZXEmWM51fvlaNFNXrcS09NRuTcEui67DjC0w'
    );

    $data = json_encode($json_data);
    
    $agent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.0.3705; .NET CLR 1.1.4322)';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_USERAGENT, $agent);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_BINARYTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    
    $url = "https://fcm.googleapis.com/fcm/send";
    //echo $url; die();
    curl_setopt($ch, CURLOPT_URL,$url);
    $result = curl_exec($ch);
    //if ($result === FALSE) {
    //    echo curl_error($ch);
    //}
    curl_close($ch);
}
    //////////////////////////

		echo json_encode(array('respuesta' => true,'mensaje' => "Exito.", 'saldo' => $saldodisponible));			
	}else{
		echo json_encode(array('respuesta' => false,'error'=>$stmt->error));
	}
}else{
	//Buen intento kiddo
	if ($cedula==$cedula){
		echo json_encode(array('respuesta' => false,'mensaje' => "No puedes enviar dínero a ti mismo =)"));
	}else{
		echo json_encode(array('respuesta' => false,'mensaje' => "Buen intento kiddo"));

	}
}
?>