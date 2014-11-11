<?php
header("Access-Control-Allow-Origin: *");
include_once ("config.php");

$aDatosResp = array("b"=>true);
echo json_encode((object)$aDatosResp);