<?php
header("Access-Control-Allow-Origin: *");
include_once ("config.php");
include_once (PATH_CLASES . "dbmysql.class.php");

$aDatos = $_POST;
$sRespuesta = "";
$oDb = new dbItHealth(DB_NAME);

$sQuery = <<<QUERY
SELECT  parientes.idPariente AS 'v',
        parientes.nombre AS 't' 
FROM    parientes 
WHERE   1=1 
ORDER BY parientes.nombre ASC
QUERY;

$oDb -> query($sQuery);

while ($aDatosDb = $oDb -> fetch_array_assoc()) {
	$aDatosResp[] = $aDatosDb;
}

echo json_encode((object)$aDatosResp);
?>