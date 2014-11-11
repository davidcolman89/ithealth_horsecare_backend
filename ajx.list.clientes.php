<?php
header("Access-Control-Allow-Origin: *");
include_once ("config.php");
include_once (PATH_CLASES . "dbmysql.class.php");

$aDatos = $_POST;
$sRespuesta = "";
$oDb = new dbItHealth(DB_NAME);

$sQuery = <<<QUERY
SELECT  duenios.*,
        duenios.idDuenio AS 'id',
        provincias.nombre AS 'provincia',
        localidades.nombre AS 'localidad'
FROM duenios
LEFT JOIN provincias ON provincias.idProvincia = duenios.idProvincia
LEFT JOIN localidades ON localidades.idLocalidad = duenios.idLocalidad
WHERE 1=1 
ORDER BY duenios.nombre ASC
QUERY;

$oDb -> query($sQuery);

while ($aDatosDb = $oDb -> fetch_array_assoc()) {
	$aDatosResp[] = $aDatosDb;
}

echo json_encode((object)$aDatosResp);
?>