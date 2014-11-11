<?php
header("Access-Control-Allow-Origin: *");
include_once ("config.php");
include_once (PATH_CLASES . "dbmysql.class.php");

$aDatos = $_POST;
$iIdCliente = $aDatos["ic"];
$sRespuesta = "";
$sWhereCli = (empty($iIdCliente)) ? "" : " AND equinos.idDuenio = {$iIdCliente} ";
$oDb = new dbItHealth(DB_NAME);

$sQuery = <<<QUERY
SELECT equinos.idEquino AS 'v',equinos.nombre AS 't'
FROM equinos 
WHERE 1=1 {$sWhereCli}
ORDER BY equinos.nombre ASC
QUERY;

$oDb -> query($sQuery);

while ($aDatosDb = $oDb -> fetch_array_assoc()) {
	$aDatosResp[] = $aDatosDb;
}

echo json_encode((object)$aDatosResp);
?>