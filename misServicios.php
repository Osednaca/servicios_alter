<?php

	include("includes.php");

	$post_date  		 = file_get_contents("php://input");

	$data 				 = json_decode($post_date);

	$token 				 = Auth::GetData(

        				 	$data->token

    					   );

	$idusuario  		 = $token->id;

	$filtros 			 = "";

	$tipostring 		 = "";

	$a_params 			 = array();

	$servicios 			 = array();

	$usuariocalificacion = array();

	$poraportegrupo				= select_config_alter("aportegrupo");

	$poraportehijos				= select_config_alter("aportehijos");

	$poraportenietos			= select_config_alter("aportenietos");

	$poraportebisnietos			= select_config_alter("aportebisnietos");

	$porservicioalter			= select_config_alter("servicioalter");

	$porcomisionnequi			= select_config_alter("comisionnequi");

	$porcomisionnequi2			= select_config_alter("comisionnequiproveedor");

	$poriva						= select_config_alter("iva");



	//Filtros

	$filtros = "servicio.estatus IN(5,6,7,9) ";

	if(!empty($data->tipo)){

		if($data->tipo == 1){

			$filtros 	   .= " AND idcliente=?";

			$a_params[]    = & $idusuario;

			$tipostring	   = 	"s";

			$tipocalificacion = "Cliente";				

		}elseif($data->tipo == 2){

			$filtros 	   .= " AND idproveedor=?";

			$a_params[]    = & $idusuario;

			$tipostring	   = 	"s";

			$tipocalificacion = "Proveedor";	

		}

	}else{

		$filtros 	    .= " AND (idcliente=? OR idproveedor=?)";

		$a_params[] 	= & $idusuario;

		$a_params[] 	= & $idusuario;

		$tipostring	    = 	"ii";

	}

	if(!empty($data->desde)){

		$filtros 	   	.= 	" AND DATE(servicio.fecharegistro) >= ?";

		$aux 			= explode("/",$data->desde);

		$desde 			= $aux[2]."-".$aux[1]."-".$aux[0];

		$a_params[] 	= & $desde;

		$tipostring		.= 	"s";

	}

	if(!empty($data->hasta)){

		$filtros 	   	.= 	" AND DATE(servicio.fecharegistro) <= ?";

		$aux 			= explode("/",$data->hasta);

		$hasta 			= $aux[2]."-".$aux[1]."-".$aux[0];

		$a_params[] 	= & $hasta;

		$tipostring		.= 	"s";

	}



array_unshift($a_params,$tipostring);



$stmt = $con->prepare("SELECT idservicio,incluyetramite,servicio.estatus,DATE(servicio.fecharegistro),fechaculminacion,tiempoestimadototal,valor,idproveedor,usuario.cedula,usuario.nombre,usuario.apellido,proveedor.cedula,proveedor.nombre,proveedor.apellido,servicio.idtipopago

						FROM servicio 

						INNER JOIN usuario ON idcliente=usuario.idusuario 

						LEFT JOIN usuario as proveedor ON idproveedor=proveedor.idusuario 

						INNER JOIN tiposervicio USING(idtiposervicio) 

						INNER JOIN tipovehiculo USING(idtipovehiculo) 

						WHERE $filtros");



call_user_func_array(array($stmt, 'bind_param'), $a_params);



$stmt->execute();

if($stmt->error == ""){

	/* bind result variables */

	$stmt->bind_result($idservicio,$incluyetramite,$estatus,$fecharegistro,$fechaculminacion,$tiempoestimadototal,$valor,$idproveedor,$cedulacliente,$nombrecliente,$apellidocliente,$cedulaproveedor,$nombreproveedor,$apellidoproveedor,$idtipopago);

	$stmt->store_result();

	while ($stmt->fetch()) {

		//$stmt->free_result();

		if($idproveedor == $idusuario){

			$escliente = false;

			$nombre    	 = $nombrecliente;

			$apellido  	 = $apellidocliente;

		}else{

			$escliente = true;

			$nombre    	 = $nombreproveedor;

			$apellido  	 = $apellidoproveedor;

		}

		$valorhijos 				= $valor * $poraportehijos;
		$valornietos				= $valor * $poraportenietos;
		$valorbisnietos				= $valor * $poraportebisnietos;
    	$valorgrupo            		= $valorhijos + $valorbisnietos + $valornietos;
    	$valorservicioalter         = $valor * $porservicioalter;
    	$valorservicioalter         = $valorservicioalter + ($valorservicioalter * $poriva);
    	$comisionnequi 	= ($valor+$valorgrupo+$valorservicioalter) * $porcomisionnequi;
    	$comisionnequi2 = ($valor+$valorgrupo+$valorservicioalter) * $porcomisionnequi2;

    	if($escliente){
    		$valor 			= round($valor + $valorgrupo + $valorservicioalter + $comisionnequi);
    	}else{
    		if($idtipopago == 4){
    			$valor 			= round($valor + $valorgrupo + $valorservicioalter + $comisionnequi);
    		}else{
    			$valor 			= round($valor - $valorgrupo - $valorservicioalter - $comisionnequi2);
    		}

    	}

		

		$servicios[] = array('idservicio'=>$idservicio,'incluyetramite'=>$incluyetramite,'estatus'=>$estatus,'fecharegistro'=>$fecharegistro,'fechaculminacion'=>$fechaculminacion,'tiempoestimadototal'=>$tiempoestimadototal,'valor'=>$valor,'escliente'=>$escliente,'nombre'=>utf8_encode($nombre.' '.$apellido));

	}

	$stmt1 = $con->prepare("SELECT AVG(calificacion),(SELECT nombre FROM usuario WHERE idusuario = ?),(SELECT apellido FROM usuario WHERE idusuario = ?) FROM calificacion WHERE idusuariocalificado = ?");

	$stmt1->bind_param("iii",$idusuario,$idusuario,$idusuario);

	$stmt1->execute();

	$stmt1->bind_result($calificacion,$nombre,$apellido);

	$stmt1->fetch();
	//"tipocalificacion"=>$tipocalificacion
	$usuariocalificacion = array("calificacion"=>$calificacion,"nombre"=>utf8_encode($nombre),"apellido" => utf8_encode($apellido));



	

	if(empty($stmt1->error)){

		echo json_encode(array('respuesta' => true, 'servicios'=>$servicios,'usuariocalificacion'=>$usuariocalificacion));

	}else{

		echo json_encode(array('respuesta' => false, 'error' => $stmt1->error));

	}

}else{

	echo json_encode(array('respuesta' => false, 'mensaje' => $stmt->error));

}



?>