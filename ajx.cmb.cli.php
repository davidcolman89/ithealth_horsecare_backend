<?php
header("Access-Control-Allow-Origin: *");
include_once ("config.php");
include_once (PATH_CLASES . "dbmysql.class.php");

$aDatos = $_POST;
$sRespuesta = "";
$oDb = new dbItHealth(DB_NAME);

$sQuery = <<<QUERY
SELECT  duenios.idDuenio AS 'v',
        duenios.nombre AS 't'
FROM duenios
WHERE 1=1
ORDER BY duenios.nombre ASC
QUERY;

$oDb -> query($sQuery);

while ($aDatosDb = $oDb -> fetch_array_assoc()) {
	$aDatosResp[] = $aDatosDb;
}

//var_dump($aDatosResp);die();

echo json_encode((object)$aDatosResp);
?>