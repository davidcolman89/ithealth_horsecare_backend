<?php

header("Access-Control-Allow-Origin: *");
include_once ("config.php");
include_once (PATH_CLASES . "dbmysql.class.php");

$aDatos = $_POST;
$sRespuesta = "";
$oDb = new dbItHealth(DB_NAME);

//Clientes
$sQuery = <<<QUERY
SELECT duenios.idDuenio AS 'id'
, duenios.nombre
,duenios.idLugar AS 'id_lugar'
, 0 AS 'offline'
FROM duenios
WHERE 1=1
ORDER BY duenios.idDuenio ASC
QUERY;

$oDb -> query($sQuery);

while ($aDatosDb = $oDb -> fetch_array_assoc()) {
    $aDatosResp["clientes"][] = $aDatosDb;
}

//Equinos
$sQuery = <<<QUERY
SELECT equinos.idEquino AS 'id'
, equinos.nombre
, equinos.nacimiento
, equinos.observacion AS 'obs'
, equinos.idDuenio AS 'id_duenio'
, 0 AS 'offline'
FROM equinos
WHERE 1=1
ORDER BY equinos.idEquino ASC
QUERY;

$oDb -> query($sQuery);

while ($aDatosDb = $oDb -> fetch_array_assoc()) {
    $aDatosResp["equinos"][] = $aDatosDb;
}

echo json_encode((object)$aDatosResp);