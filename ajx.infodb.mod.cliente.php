<?php
header("Access-Control-Allow-Origin: *");
include_once ("config.php");
include_once (PATH_CLASES . "dbmysql.class.php");

$aDatos = $_POST;
$iIdDuenio = $aDatos["ic"];
$oDb = new dbItHealth(DB_NAME);


/*
 * Selecciona la informacion del equino que se le envie
 */
$sQuery = <<<QUERY
SELECT 
duenios.idDuenio AS 'id'
,duenios.nombre
,duenios.direccion
,duenios.idProvincia AS 'id_provincia'
,duenios.idLocalidad AS 'id_localidad'
,duenios.telefono
,duenios.celular
,duenios.email
,duenios.idLugar AS 'id_lugar'
FROM duenios
WHERE duenios.idDuenio = {$iIdDuenio}
ORDER BY duenios.nombre ASC
LIMIT 1
QUERY;

$oDb -> query($sQuery);
$iTotal = $oDb->num_rows();
$aDatosResp["bInfoCli"] = ($iTotal>0) ? TRUE: FALSE;
$aDatosResp["infoCli"] = ($iTotal>0) ? $oDb -> fetch_array_assoc() : ""; 

echo json_encode((object)$aDatosResp);
?>