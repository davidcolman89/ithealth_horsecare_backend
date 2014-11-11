<?php
header("Access-Control-Allow-Origin: *");
include_once ("config.php");
include_once (PATH_CLASES . "dbmysql.class.php");
include_once (PATH_HELPERS . "helpers.php");

$aRespuesta = array();
$aDatos = $_POST;
$iIdProblema = $aDatos['ip'];
$oDb = new dbItHealth(DB_NAME);

$sQuery = <<<QUERY
SELECT *
FROM problemas
WHERE problemas.idProblema = {$iIdProblema}
QUERY;

$oDb->query($sQuery);
$oDb->fetch_all($aRespuesta);

echo json_encode($aRespuesta);
