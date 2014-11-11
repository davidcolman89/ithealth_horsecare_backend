<?php
header("Access-Control-Allow-Origin: *");
include_once ("config.php");
include_once (PATH_CLASES . "dbmysql.class.php");

$aDatosResp = array();
$aDatosResp["sfa"] = date('Y-m-d');//string Fecha Actual

echo json_encode((object)$aDatosResp);
?>