<?php
// Include the main TCPDF library (search for installation path).
require_once('tcpdf_include.php');
require_once('include/utils.php');
$idservicio = $_REQUEST["idservicio"];

$stmt = $con->prepare("SELECT idtransaccion,DATE(fechatransaccion),tiposervicio,valor,servicio.fecharegistro,servicio.estatus,cliente.cedula,cliente.nombre,cliente.apellido,proveedor.cedula,proveedor.nombre,proveedor.apellido FROM servicio INNER JOIN tiposervicio USING(idtiposervicio) INNER JOIN usuario as cliente ON servicio.idcliente = cliente.idusuario INNER JOIN usuario as proveedor ON servicio.idproveedor = proveedor.idusuario INNER JOIN transacciones USING(idservicio) WHERE idservicio=?");
$stmt->bind_param("i",$idservicio);
$stmt->execute();
$stmt->bind_result($idtransaccion,$fechatransaccion,$servicio,$valor,$fechaservicio,$estatus,$cedulacliente,$nombrecliente,$apellidocliente,$cedulaproveedor,$nombreproveedor,$apellidoproveedor);
$stmt->fetch();
$stmt->free_result();

// create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Alter');
$pdf->SetTitle('Factura #');
$pdf->SetSubject('TCPDF Tutorial');
$pdf->SetKeywords('TCPDF, PDF, example, test, guide');

// set default header data
$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' '.$idtransaccion, PDF_HEADER_STRING);

// set header and footer fonts
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// set some language-dependent strings (optional)
if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
    require_once(dirname(__FILE__).'/lang/eng.php');
    $pdf->setLanguageArray($l);
}

// ---------------------------------------------------------

// set font
$pdf->SetFont('dejavusans', '', 10);

// add a page
$pdf->AddPage();

// writeHTML($html, $ln=true, $fill=false, $reseth=false, $cell=false, $align='')
// writeHTMLCell($w, $h, $x, $y, $html='', $border=0, $ln=0, $fill=0, $reseth=true, $align='', $autopadding=true)

// create some HTML content
$html = '<h1>Cuenta de Cobro</h1>';

// output the HTML content
$pdf->writeHTML($html, true, false, true, false, '');

// Escribir HTML de la factura
$html = "Fecha: $fechaservicio. <br>".utf8_encode($nombrecliente." ".$apellidocliente).".<br>CC: $cedulacliente.<br>Debe a:<br>".utf8_encode($nombreproveedor." ".$apellidoproveedor)."<br>CC: $cedulaproveedor.<br>La suma de: ".number_format($valor,2,",",".").".<br>Concepto: ".utf8_encode($servicio);

if($estatus == 6){
	$html .= "<br><br>Cuenta Pagada: $fechatransaccion";
}

$pdf->writeHTML($html, true, false, true, false, '');

// ---------------------------------------------------------

//Close and output PDF document
$pdf->Output('factura1.pdf', 'I');

//============================================================+
// END OF FILE
//============================================================+
?>