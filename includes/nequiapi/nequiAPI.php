<?php
/*
 * @description Cliente con las funciones para consumir el API
 * @author michel.lugo@pragma.com.co, jomgarci@bancolombia.com.co
 */
include 'awsSigner.php';

$host    = "kksofdo2af.execute-api.us-east-1.amazonaws.com";
$channel = 'PDA05-C001';

$host2   = "tte1048ge4.execute-api.us-east-1.amazonaws.com";

/**
 * Encapsula el consumo del servicio de validacion de cliente del API y retorna la respuesta del servicio
 */
function pagoNequi($clientId,$telefono,$value) {
  $servicePath = "/prod/-services-paymentservice-unregisteredpayment";
  $body = getBodyPagoNequi($GLOBALS['channel'], $clientId, $telefono, $value);
  $response = makeSignedRequest($GLOBALS['host'], $servicePath, 'POST', $body);  
  if(json_decode($response) == null){
    return $response;
  }else{
    return json_decode($response);
  }
}
/**
 * Forma el cuerpo para consumir el servicio de validación de cliente del API
 */
function getBodyPagoNequi($channel, $clientId, $phoneNumber, $value){
  $messageId =  substr(strval((new DateTime())->getTimestamp()), 0, 9);
  return array(
    "RequestMessage"  => array(
      "RequestHeader"  => array (
        "Channel" => $channel,
        "RequestDate" => gmdate("Y-m-d\TH:i:s\\Z"),
        "MessageID" => $messageId,
        "ClientID" => $clientId,
        "Destination"=> array(
        "ServiceName"=> "PaymentsService", //Servicio que se quiere consumir
        "ServiceOperation"=> "unregisteredPayment", //Operación del servicio a consumir
        "ServiceRegion"=> "C001", //Región a la que pertenece el servicio, para panamá P001
        "ServiceVersion"=> "1.0.0" //Versión del servicio
        )
        ),
      "RequestBody"  => array (
        "any" => array (
          "unregisteredPaymentRQ" => array (
            "phoneNumber" => $phoneNumber,
            "code" =>  "1", //NIT del comercio
            "value" => $value
            )
        )
      )
    )
  );
}

/**
 * Encapsula el consumo del servicio de validacion de cliente del API y retorna la respuesta del servicio
 */
function validarpagoNequi($clientId,$transactionId) {
  $servicePath = "/prod/-services-paymentservice-getstatuspayment";
  $body2 = getBodyvalidarPagoNequi($GLOBALS['channel'], $clientId, $transactionId);
  $response2 = makeSignedRequest($GLOBALS['host'], $servicePath, 'POST', $body2);  
  if(json_decode($response2) == null){
    return $response2;
  }else{
    return json_decode($response2);
  }
}
/**
 * Forma el cuerpo para consumir el servicio de validación de cliente del API
 */
function getBodyvalidarPagoNequi($channel, $clientId, $transactionId){
  $messageId =  substr(strval((new DateTime())->getTimestamp()), 0, 9);
  return array(
    "RequestMessage"  => array(
      "RequestHeader"  => array (
        "Channel" => $channel,
        "RequestDate" => gmdate("Y-m-d\TH:i:s\\Z"),
        "MessageID" => $messageId,
        "ClientID" => $clientId,
        "Destination"=> array(
        "ServiceName"=> "PaymentsService", //Servicio que se quiere consumir
        "ServiceOperation"=> "getStatusPayment", //Operación del servicio a consumir
        "ServiceRegion"=> "C001", //Región a la que pertenece el servicio, para panamá P001
        "ServiceVersion"=> "1.0.0" //Versión del servicio
        )
        ),
      "RequestBody"  => array (
        "any" => array (
        "getStatusPaymentRQ" => array(
        "codeQR" => $transactionId //Código del pago sea QR o transactionId
        )
        )
      )
    )
  );
}

/**
 * Encapsula el consumo del servicio de validacion de cliente del API y retorna la respuesta del servicio
 */
function validateClient($clientId, $phoneNumber, $value) {
  $servicePath = "/prod/-services-clientservice-validateclient";
  $body = getBodyValidateClient($GLOBALS['channel'], $clientId, $phoneNumber, $value);
  $response = makeSignedRequest($GLOBALS['host'], $servicePath, 'POST', $body);  
  if(json_decode($response) == null){
    return $response;
  }else{
    return json_decode($response);
  }
}
/**
 * Forma el cuerpo para consumir el servicio de validación de cliente del API
 */
function getBodyValidateClient($channel, $clientId, $phoneNumber, $value){
  $messageId =  substr(strval((new DateTime())->getTimestamp()), 0, 9);
  return array(
    "RequestMessage"  => array(
      "RequestHeader"  => array (
        "Channel" => $channel,
        "RequestDate" => gmdate("Y-m-d\TH:i:s\\Z"),
        "MessageID" => $messageId,
        "ClientID" => $clientId),
      "RequestBody"  => array (
        "any" => array (
          "validateClientRQ" => array (
            "phoneNumber" => $phoneNumber,
            "value" => $value
            )
        )
      )
    )
  );
}

/**
 * Encapsula el consumo del servicio de nuevaSuscripcion del API y retorna la respuesta del servicio
 */
function nuevaSuscripcion($clientId,$phoneNumber) {
  $servicePath = "/pdn/-services-subscriptionpaymentservice-newsubscription";
  $body2 = getBodynuevaSuscripcion($GLOBALS['channel'], $clientId, $phoneNumber);
  $response2 = makeSignedRequest($GLOBALS['host2'], $servicePath, 'POST', $body2);  
  if(json_decode($response2) == null){
    return $response2;
  }else{
    return json_decode($response2);
  }
}
/**
 * Forma el cuerpo para consumir el servicio de nuevaSuscripcion del API
 */
function getBodynuevaSuscripcion($channel, $clientId, $phoneNumber){
  $messageId =  substr(strval((new DateTime())->getTimestamp()), 0, 9);
  return array(
  "RequestMessage" => array(
    "RequestHeader" => array(
      "Channel" => $channel,
      "RequestDate" => gmdate("Y-m-d\TH:i:s\\Z"),
        "MessageID" => $messageId,
        "ClientID" => $clientId,
      "Destination" => array(
        "ServiceName" => "SubscriptionPaymentService",
        "ServiceOperation" => "newSubscription",
        "ServiceRegion" => "C001",
        "ServiceVersion" => "1.0.0"
      ),
    ),
    "RequestBody" => array(
      "any" => array(
        "newSubscriptionRQ" => array(
          "phoneNumber" => $phoneNumber,
          "code" => "900053280",
          "name" => "alter"
        )
      )
    )
  )
);

}

/**
 * Encapsula el consumo del servicio de validacion de cliente del API y retorna la respuesta del servicio
 */
function pagoNequiAutomatico($clientId,$telefono,$value,$token) {
  $servicePath = "/pdn/-services-subscriptionpaymentservice-automaticpayment";
  $body = getBodyPagoNequiAutomatico($GLOBALS['channel'], $clientId, $telefono, $value,$token);
  $response = makeSignedRequest($GLOBALS['host2'], $servicePath, 'POST', $body);  
  if(json_decode($response) == null){
    return $response;
  }else{
    return json_decode($response);
  }
}
/**
 * Forma el cuerpo para consumir el servicio de validación de cliente del API
 */
function getBodyPagoNequiAutomatico($channel, $clientId, $phoneNumber, $value,$token){
  $messageId =  substr(strval((new DateTime())->getTimestamp()), 0, 9);
return array(
  "RequestMessage" => array(
    "RequestHeader"=> array(
      "Channel" => $channel,
      "RequestDate"=> gmdate("Y-m-d\TH:i:s\\Z"),
        "MessageID" => $messageId,
        "ClientID" => $clientId,
      "Destination"=> array(
        "ServiceName"=> "SubscriptionPaymentService",
        "ServiceOperation" => "automaticPayment",
        "ServiceRegion" => "C001",
        "ServiceVersion" => "1.0.0"
      ),
    ),
    "RequestBody"=> array(
      "any"=> array(
        "automaticPaymentRQ"=> array(
          "phoneNumber"=> $phoneNumber,
          "code"=> "900053280",
          "value"=> $value,
          "token"=> $token
            )
          )
        )
      )
    );
}

  /**
 * Encapsula el consumo del servicio de validacion de cliente del API y retorna la respuesta del servicio
 */

function validarSuscripcion($clientId,$telefono,$token){
  $servicePath = "/pdn/-services-subscriptionpaymentservice-getsubscription";
  $body = getBodyvalidarSuscripcion($GLOBALS['channel'], $clientId, $telefono,$token);
  $response = makeSignedRequest($GLOBALS['host2'], $servicePath, 'POST', $body);  
  if(json_decode($response) == null){
    return $response;
  }else{
    return json_decode($response);
  }
}
/**
 * Forma el cuerpo para consumir el servicio de validación de cliente del API
 */
function getBodyvalidarSuscripcion($channel, $clientId, $phoneNumber,$token){
  $messageId =  substr(strval((new DateTime())->getTimestamp()), 0, 9);
  return array(
    "RequestMessage" => array(
      "RequestHeader" => array(
        "Channel" => "PDA05-C001",
        "RequestDate" => "2017-06-21T20:26:12.654Z",
        "MessageID" => "1234567890",
        "ClientID" => "12345",
        "Destination"  => array(
          "ServiceName" => "SubscriptionPaymentService",
          "ServiceOperation" => "getubscription",
          "ServiceRegion" => "C001",
          "ServiceVersion" => "1.0.0"
        )
      ),
      "RequestBody" => array(
        "any" => array(
          "getSubscriptionRQ" => array(
            "phoneNumber" => $phoneNumber,
            "code" => "900053280",
            "token" => $token
          )
        )
      )
    )
  );

}

  /**
 * Encapsula el consumo del servicio de validacion de cliente del API y retorna la respuesta del servicio
 */

function cancelarTransaccion($clientId,$telefono,$valor,$messageid){
  //echo "IDCliente: $clientId Telefono: ".$telefono." Valor: ".$valor." IDReferencia: ".$messageid; die();
  $servicePath = "/pdn/-services-reverseservices-reversetransaction";
  $body = getBodycancelarTransaccion($GLOBALS['channel'], $clientId, $telefono,$valor,$messageid);
  //var_dump($body); die();
  $response = makeSignedRequest($GLOBALS['host'], $servicePath, 'POST', $body);  
  if(json_decode($response) == null){
    return $response;
  }else{
    return json_decode($response);
  }
}
/**
 * Forma el cuerpo para consumir el servicio de cancelar una transaccion
 */
function getBodycancelarTransaccion($channel, $clientId, $phoneNumber,$valor,$messageid2){
  $messageId =  substr(strval((new DateTime())->getTimestamp()), 0, 10);
  return   array(
  "RequestMessage" => array(
    "RequestHeader" => array(
      "Channel" => $channel,
      "RequestDate"=> gmdate("Y-m-d\TH:i:s\\Z"),
        "MessageID" => $messageId,
        "ClientID" => $clientId,
      "Destination" =>  array(
        "ServiceName" => "ReverseServices",
        "ServiceOperation" => "reverseTransaction",
        "ServiceRegion" => "C001",
        "ServiceVersion" => "1.0.0"
      )
    ),
    "RequestBody"  => array(
      "any"  => array(
        "reversionRQ"  => array(
          "phoneNumber" => $phoneNumber,
          "value" => $valor,
          "code" => "1",
          "messageId" => $messageid2,
          "type" => "automaticPayment"
        )
      )
    )
  )
);

}

?>