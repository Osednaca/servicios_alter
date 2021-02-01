<?php

$con = new mysqli("localhost", "alterclu_root", "@J0dzsqwlDsL", "alterclu_pruebas");
//

//die();
// Check connection
if (mysqli_connect_errno())
{
	echo "Error conectando a MySQL: " . mysqli_connect_error();
}
?>