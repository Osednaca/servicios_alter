<?php
include_once("includes/utils.php");
$fechanow   = date("Y-m-d 23:59:59");

function aporteRecomendado($idusuario,$nivel,$fechaultimocorte){
	global $con;
	global $fechanow;
	//Seleccionar el % de aporte por nivel
	$nivel = "aporte".$nivel;
	$aportenivel = select_config_alter($nivel);
	
	//Calcular lo que han hecho
	$stmt2 = $con->prepare("SELECT SUM(valor) FROM servicio WHERE idproveedor = ? AND fecharegistro>=? AND fecharegistro<=? AND estatus = 5");
	$stmt2->bind_param("iss", $idusuario,$fechaultimocorte,$fechanow);
	$stmt2->execute();
	$stmt2->bind_result($valor1);
	$stmt2->fetch();
	$aporterecomendado = ($valor1*0.06)*$aportenivel;
	
	return $aporterecomendado;

}

function aporteGrupo($idusuario,$fechaultimocorte){
	global $con;
	global $fechanow;
	$aportehijos  	 = 0;
	$aportenietos 	 = 0;
	$aportebisnietos = 0;

	$pornietos 		= select_config_alter("aportenietos");
	$porhijos 		= select_config_alter("aportehijos");
	$porbisnietos 	= select_config_alter("aportebisnietos");

	//Calcular lo que han hechos sus recomendados hijos
	$stmt = $con->prepare("SELECT valor FROM servicio WHERE (idproveedor IN(SELECT usuario.idusuario FROM recomendaciones INNER JOIN 	usuario USING(cedula) WHERE recomendaciones.idusuario = ? AND recomendaciones.estatus=1) OR idcliente IN(SELECT usuario.idusuario FROM recomendaciones INNER JOIN 	usuario USING(cedula) WHERE recomendaciones.idusuario = ? AND recomendaciones.estatus=1)) AND fecharegistro>=? AND fecharegistro<=? AND estatus 	= 5 ORDER BY idproveedor");
	// ???
	$stmt->bind_param("iiss", $idusuario,$idusuario,$fechaultimocorte,$fechanow);
	$stmt->execute();
	$stmt->bind_result($valor1);
	
	while($stmt->fetch()){
		$aportehijos += $valor1*$porhijos;
	}
	//Calcular lo que han hecho sus recomendados nietos
	$stmt2 = $con->prepare("SELECT valor FROM servicio INNER JOIN usuario ON idproveedor=idusuario WHERE (idproveedor IN(SELECT usuario.idusuario FROM recomendaciones INNER JOIN usuario USING(cedula) WHERE recomendaciones.idusuario IN( SELECT usuario.idusuario FROM recomendaciones INNER JOIN usuario USING(cedula) WHERE recomendaciones.idusuario = ? AND recomendaciones.estatus=1)) OR idcliente IN(SELECT usuario.idusuario FROM recomendaciones INNER JOIN usuario USING(cedula) WHERE recomendaciones.idusuario IN( SELECT usuario.idusuario FROM recomendaciones INNER JOIN usuario USING(cedula) WHERE recomendaciones.idusuario = ? AND recomendaciones.estatus=1))) AND servicio.fecharegistro>=? AND servicio.fecharegistro<=? AND servicio.estatus = 5");
	$stmt2->bind_param("iiss", $idusuario,$idusuario,$fechaultimocorte,$fechanow);
	$stmt2->execute();
	$stmt2->bind_result($valor2);
	while($stmt2->fetch()){
		$aportenietos += $valor2*$pornietos;
	}
	//Calcular lo que han hecho sus recomendados bisnietos
	$stmt3 = $con->prepare("SELECT valor FROM servicio INNER JOIN usuario ON idproveedor=idusuario WHERE (idproveedor IN(SELECT usuario.idusuario FROM recomendaciones INNER JOIN usuario USING(cedula) WHERE recomendaciones.idusuario IN( SELECT usuario.idusuario FROM recomendaciones INNER JOIN usuario USING(cedula) WHERE recomendaciones.idusuario IN( SELECT usuario.idusuario FROM recomendaciones INNER JOIN usuario USING(cedula) WHERE recomendaciones.idusuario = ? AND recomendaciones.estatus=1)) AND recomendaciones.estatus=1) OR idcliente IN(SELECT usuario.idusuario FROM recomendaciones INNER JOIN usuario USING(cedula) WHERE recomendaciones.idusuario IN( SELECT usuario.idusuario FROM recomendaciones INNER JOIN usuario USING(cedula) WHERE recomendaciones.idusuario IN( SELECT usuario.idusuario FROM recomendaciones INNER JOIN usuario USING(cedula) WHERE recomendaciones.idusuario = ? AND recomendaciones.estatus=1)) AND recomendaciones.estatus=1)) AND servicio.fecharegistro>=? AND servicio.fecharegistro<=? AND servicio.estatus = 5");
	$stmt3->bind_param("iiss", $idusuario,$idusuario,$fechaultimocorte,$fechanow);
	$stmt3->execute();
	$stmt3->bind_result($valor3);
	while($stmt3->fetch()){
		$aportebisnietos += $valor3*$porbisnietos;
	}
	
	return $aportehijos+$aportenietos+$aportebisnietos;
}

function aporteGrupoFechas($idusuario,$fechaini,$fechafin,$fechaultimocorte){
	global $con;
	global $fechanow;
	$aportehijos  	 = 0;
	$aportenietos 	 = 0;
	$aportebisnietos = 0;

	$pornietos 		= select_config_alter("aportenietos");
	$porhijos 		= select_config_alter("aportehijos");
	$porbisnietos 	= select_config_alter("aportebisnietos");

	//Calcular lo que han hechos sus recomendados hijos
	$stmt = $con->prepare("SELECT valor FROM servicio WHERE (idproveedor IN(SELECT usuario.idusuario FROM recomendaciones INNER JOIN 	usuario USING(cedula) WHERE recomendaciones.idusuario = ? AND recomendaciones.estatus=1) OR idcliente IN(SELECT usuario.idusuario FROM recomendaciones INNER JOIN 	usuario USING(cedula) WHERE recomendaciones.idusuario = ? AND recomendaciones.estatus=1)) AND fecharegistro>=? AND fecharegistro<=? AND estatus 	= 5 ORDER BY idproveedor");
	// ???
	$stmt->bind_param("iiss", $idusuario, $idusuario,$fechaini,$fechafin);
	$stmt->execute();
	$stmt->bind_result($valor1);
	
	while($stmt->fetch()){
		$aportehijos += $valor1*$porhijos;
	}
	//Calcular lo que han hecho sus recomendados nietos
	$stmt2 = $con->prepare("SELECT valor FROM servicio INNER JOIN usuario ON idproveedor=idusuario WHERE (idproveedor IN(SELECT usuario.idusuario FROM recomendaciones INNER JOIN usuario USING(cedula) WHERE recomendaciones.idusuario IN( SELECT usuario.idusuario FROM recomendaciones INNER JOIN usuario USING(cedula) WHERE recomendaciones.idusuario = ? AND recomendaciones.estatus=1)) OR idcliente IN(SELECT usuario.idusuario FROM recomendaciones INNER JOIN usuario USING(cedula) WHERE recomendaciones.idusuario IN( SELECT usuario.idusuario FROM recomendaciones INNER JOIN usuario USING(cedula) WHERE recomendaciones.idusuario = ? AND recomendaciones.estatus=1))) AND servicio.fecharegistro>=? AND servicio.fecharegistro<=? AND servicio.estatus = 5");
	$stmt2->bind_param("iiss", $idusuario,$idusuario,$fechaini,$fechafin);
	$stmt2->execute();
	$stmt2->bind_result($valor2);
	while($stmt2->fetch()){
		$aportenietos += $valor2*$pornietos;
	}
	//Calcular lo que han hecho sus recomendados bisnietos
	$stmt3 = $con->prepare("SELECT valor FROM servicio INNER JOIN usuario ON idproveedor=idusuario WHERE (idproveedor IN(SELECT usuario.idusuario FROM recomendaciones INNER JOIN usuario USING(cedula) WHERE recomendaciones.idusuario IN( SELECT usuario.idusuario FROM recomendaciones INNER JOIN usuario USING(cedula) WHERE recomendaciones.idusuario IN( SELECT usuario.idusuario FROM recomendaciones INNER JOIN usuario USING(cedula) WHERE recomendaciones.idusuario = ? AND recomendaciones.estatus=1)) AND recomendaciones.estatus=1) OR idcliente IN(SELECT usuario.idusuario FROM recomendaciones INNER JOIN usuario USING(cedula) WHERE recomendaciones.idusuario IN( SELECT usuario.idusuario FROM recomendaciones INNER JOIN usuario USING(cedula) WHERE recomendaciones.idusuario IN( SELECT usuario.idusuario FROM recomendaciones INNER JOIN usuario USING(cedula) WHERE recomendaciones.idusuario = ? AND recomendaciones.estatus=1)) AND recomendaciones.estatus=1)) AND servicio.fecharegistro>=? AND servicio.fecharegistro<=? AND servicio.estatus = 5");
	$stmt3->bind_param("iiss", $idusuario,$idusuario,$fechaini,$fechafin);
	$stmt3->execute();
	$stmt3->bind_result($valor3);
	while($stmt3->fetch()){
		$aportebisnietos += $valor3*$porbisnietos;
	}
	return $aportehijos+$aportenietos+$aportebisnietos;
}

/*
* Aporte por no cumplir meta
*/
function aportecumplemeta($idusuario,$fechaultimocorte){
	global $con;
	global $fechanow;
	$aportehijos  	  = 0;
	$aportenietos 	  = 0;
	$aportebisnietos  = 0;
	$aportehijos2  	  = 0;
	$aportenietos2 	  = 0;
	$aportebisnietos2 = 0;
	//Hijos
	$stmt = $con->prepare("SELECT idusuario,cumplemeta FROM usuario WHERE idusuario IN(SELECT usuario.idusuario FROM recomendaciones INNER JOIN 	usuario USING(cedula) WHERE recomendaciones.idusuario = ? AND recomendaciones.estatus=1)");
	$stmt->bind_param("i", $idusuario);
	$stmt->execute();
	$stmt->bind_result($idhijo,$cumplemetahijo);
	$stmt->store_result();

	while($stmt->fetch()){
		if(!$cumplemetahijo){
			$valor1 	= aporteGrupo($idhijo,$fechaultimocorte);
			$otroaporte = aportecumplemeta($idhijo,$fechaultimocorte);
			$aportehijos2 += $otroaporte * 0.5;
			$aportehijos  += $valor1*0.5;
		}
	}

	//Nietos
	$stmt1 = $con->prepare("SELECT idusuario,cumplemeta FROM usuario WHERE idusuario IN(SELECT usuario.idusuario FROM recomendaciones INNER JOIN usuario USING(cedula) WHERE recomendaciones.idusuario IN( SELECT usuario.idusuario FROM recomendaciones INNER JOIN usuario USING(cedula) WHERE recomendaciones.idusuario = ? AND recomendaciones.estatus=1))");
	$stmt1->bind_param("i", $idusuario);
	$stmt1->execute();
	$stmt1->bind_result($idnieto,$cumplemetanieto);
	$stmt1->store_result();

	while($stmt1->fetch()){
		if(!$cumplemetanieto){
			$valor2 	  = aporteGrupo($idnieto,$fechaultimocorte);
			$otroaporte   = aportecumplemeta($idhijo,$fechaultimocorte);
			$aportenietos2+= $otroaporte * 0.3;
			$aportenietos += $valor2*0.3;
		}
	}
	$stmt1->free_result();

	//Bisnietos
	$stmt2 = $con->prepare("SELECT idusuario,cumplemeta FROM usuario WHERE idusuario IN(SELECT usuario.idusuario FROM recomendaciones INNER JOIN usuario USING(cedula) WHERE recomendaciones.idusuario IN( SELECT usuario.idusuario FROM recomendaciones INNER JOIN usuario USING(cedula) WHERE recomendaciones.idusuario IN( SELECT usuario.idusuario FROM recomendaciones INNER JOIN usuario USING(cedula) WHERE recomendaciones.idusuario = ? AND recomendaciones.estatus=1)) AND recomendaciones.estatus=1)");
	$stmt2->bind_param("i", $idusuario);
	$stmt2->execute();
	$stmt2->bind_result($idbisnietos,$cumplemetabisnieto);
	$stmt2->store_result();

	while($stmt2->fetch()){
		if(!$cumplemetabisnieto){
			$valor3		  	 = aporteGrupo($idbisnietos,$fechaultimocorte);
			$otroaporte 	 = aportecumplemeta($idhijo,$fechaultimocorte);
			$aportebisnietos2+= $otroaporte * 0.2;
			$aportebisnietos += $valor3*0.2;
		}
	}
	$stmt2->free_result();
	return $aportehijos+$aportehijos2+$aportenietos+$aportenietos2+$aportebisnietos+$aportebisnietos2;
}

/*
	Calcular Saldo Disponible
	Saldo disponible = Todos los servicios que no hayan sido cobrados y que se hallan finalizados antes de la ultima fecha de corte. Una anterior
*/
function calcularsaldodisponible($idusuario,$conmeta,$fechaultimocorte){
	global $con;
	global $fechanow;
	$serviciospagonequi   = 0;
	$aportegrupo 		   = 0;
	$idsaportes  		   = "";
	
	//Saldo que ha ganado por servicios como proveedor
	//echo "idusuario:$idusuario // fechaultimocorte:$fechaultimocorte // fechanow:$fechanow"; die();
	$stmt = $con->prepare("SELECT SUM(totalproveedor) FROM servicio INNER JOIN transacciones USING(idservicio) WHERE idproveedor = ? AND estatus=5 AND fecharegistro>=? AND fecharegistro<=?");
	$stmt->bind_param("iss", $idusuario,$fechaultimocorte,$fechanow);
	$stmt->execute();
	$stmt->bind_result($servicioshechos);
	$stmt->fetch();
	$stmt->free_result();
	//Servicios como Cliente
	$stmt = $con->prepare("SELECT SUM(totalcliente) FROM servicio INNER JOIN transacciones USING(idservicio) WHERE idcliente = ? AND estatus=5 AND fecharegistro>=? AND fecharegistro<=?");
	$stmt->bind_param("iss", $idusuario,$fechaultimocorte,$fechanow);
	$stmt->execute();
	$stmt->bind_result($serviciospedidos);
	$stmt->fetch();
	$stmt->free_result();
	//Saldo que ha ganado por servicios como proveedor con estatus 7 Pago minimo
	$stmt1 = $con->prepare("SELECT valor FROM servicio WHERE idproveedor = ? AND estatus=7 AND fecharegistro>=? AND fecharegistro<=?");
	$stmt1->bind_param("iss", $idusuario,$fechaultimocorte,$fechanow);
	$stmt1->execute();
	$stmt1->bind_result($valor);
	while ($stmt1->fetch()){
		$serviciospagonequi += $valor;
	};
	$servicioshechos = $servicioshechos + $serviciospagonequi;
	$stmt1->free_result();
	//Get Meta Mensual
	$stmt2 = $con->prepare("SELECT metaquincenal FROM usuario WHERE idusuario = ?");
	$stmt2->bind_param("i", $idusuario);
	$stmt2->execute();
	$stmt2->bind_result($meta);
	$stmt2->fetch();
	$stmt2->free_result();

	if($meta == NULL){
		$meta = 0;
	}
	
	$aportegrupo = aporteGrupo($idusuario,$fechaultimocorte);

	if($conmeta){
	//Aporte de los de su grupo que no hayan cumplido la meta
	$aporte2     = aportecumplemeta($idusuario,$fechaultimocorte);
}else{
	$aporte2 = 0;
}
	//Sumar servicios hechos + servicios pedidos
	$serviciostotal = $servicioshechos + $serviciospedidos;
	if($serviciostotal >= $meta){
		$ingresototales = $servicioshechos + $aportegrupo + $aporte2;
	}else{
		$ingresototales = $servicioshechos;
	}
	
	if($conmeta){
		$aportegrupo = $aportegrupo+$aporte2;
	}
	
	return array('ingresototales'=>$ingresototales,'ingresoxgrupo' => $aportegrupo, 'aporte2' => $aporte2, 'meta' => $meta,'ingresonormal' => $servicioshechos,'ultimocorte'=>$fechaultimocorte,'conmeta'=>$conmeta,"egresos"=>$serviciospedidos);
}

function calcularsaldodisponibleFechas($idusuario,$conmeta,$fechaini,$fechafin){
	global $con;
	global $fechanow;
	global $fechaultimocorte;
	$serviciospagonequi   = 0;
	$aportegrupo 		   = 0;
	$idsaportes  		   = "";
	
	//Saldo que ha ganado por servicios como proveedor
	//echo "idusuario:$idusuario // fechaultimocorte:$fechaultimocorte // fechanow:$fechanow"; die();
	$stmt = $con->prepare("SELECT SUM(totalproveedor) FROM servicio INNER JOIN transacciones USING(idservicio) WHERE idproveedor = ? AND estatus=5 AND fecharegistro>=? AND fecharegistro<=?");
	$stmt->bind_param("iss", $idusuario,$fechaini,$fechafin);
	$stmt->execute();
	$stmt->bind_result($servicioshechos);
	$stmt->fetch();
	$stmt->free_result();
	//Servicios como Cliente
	$stmt = $con->prepare("SELECT SUM(totalcliente) FROM servicio INNER JOIN transacciones USING(idservicio) WHERE idcliente = ? AND estatus=5 AND fecharegistro>=? AND fecharegistro<=?");
	$stmt->bind_param("iss", $idusuario,$fechaini,$fechafin);
	$stmt->execute();
	$stmt->bind_result($serviciospedidos);
	$stmt->fetch();
	$stmt->free_result();
	//Saldo que ha ganado por servicios como proveedor con estatus 7 Pago minimo
	$stmt1 = $con->prepare("SELECT valor FROM servicio WHERE idproveedor = ? AND estatus=7 AND fecharegistro>=? AND fecharegistro<=?");
	$stmt1->bind_param("iss", $idusuario,$fechaini,$fechafin);
	$stmt1->execute();
	$stmt1->bind_result($valor);
	while ($stmt1->fetch()){
		$serviciospagonequi += $valor; //Seleccionar desde la tabla configuracion alter
	};
	$servicioshechos = $servicioshechos + $serviciospagonequi;
	$stmt1->free_result();
	//Get Meta Mensual
	$stmt2 = $con->prepare("SELECT metaquincenal FROM usuario WHERE idusuario = ?");
	$stmt2->bind_param("i", $idusuario);
	$stmt2->execute();
	$stmt2->bind_result($meta);
	$stmt2->fetch();
	$stmt2->free_result();

	if($meta == NULL){
		$meta = 0;
	}
	
	$aportegrupo = aporteGrupoFechas($idusuario,$fechaini,$fechafin);

	if($conmeta){
	//Aporte de los de su grupo que no hayan cumplido la meta
	$aporte2     = aportecumplemeta($idusuario);
}else{
	$aporte2 = 0;
}
	//Sumar servicios hechos + servicios pedidos
	$serviciostotal = $servicioshechos + $serviciospedidos;
	if($serviciostotal >= $meta){
		$ingresototales = $servicioshechos + $aportegrupo + $aporte2;
	}else{
		$ingresototales = $servicioshechos;
	}
	
	if($conmeta){
		$aportegrupo = $aportegrupo+$aporte2;
	}
	
	return array('ingresototales'=>$ingresototales,'ingresoxgrupo' => $aportegrupo, 'aporte2' => $aporte2, 'meta' => $meta,'ingresonormal' => $servicioshechos,'ultimocorte'=>$fechaultimocorte,'conmeta'=>$conmeta,"egresos"=>$serviciospedidos);
}

?>