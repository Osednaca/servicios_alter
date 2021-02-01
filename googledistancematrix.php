<?php
	$post_date  = file_get_contents("php://input");
	$data 		= json_decode($post_date);
	$start  	= $data->start;
	$end		= $data->end;
	$agent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.0.3705; .NET CLR 1.1.4322)';
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_USERAGENT, $agent);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_BINARYTRANSFER, TRUE);

	$url = "https://maps.googleapis.com/maps/api/directions/json?origin=".urlencode($start)."&destination=".urlencode($end)."&departure_time=now&mode=driving&alternatives=true&key=AIzaSyDpE4hgWe71uVid7ZN2oGdHUTSy4jjky3A";
	//echo $url; die();
	curl_setopt($ch, CURLOPT_URL,$url);
	$result=curl_exec($ch);
	$array = json_decode($result, true);
	curl_exec($ch);
	echo json_encode(array('data' => $array));

?>