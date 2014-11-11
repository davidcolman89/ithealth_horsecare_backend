<?php
header("Access-Control-Allow-Origin: *");
include_once ("config.php");
include_once (PATH_CLASES . "dbmysql.class.php");

$aDatos = $_POST;
$iIdEquino = $aDatos["ie"];
$oDb = new dbItHealth(DB_NAME);


/*
 * Selecciona la informacion del equino que se le envie
 */
$sQuery = <<<QUERY
SELECT equinos.idEquino AS 'id'
, equinos.nombre
, equinos.idDuenio AS 'id_duenio'
, equinos.idSexo AS 'id_sexo'
, equinos.nacimiento
, equinos.observacion AS 'obs'
, equinos.idPadrillo AS 'id_padrillo'
, equinos.idMadre AS 'id_madre'
, equinos.idAbuelo AS 'id_abuelo'
FROM equinos
WHERE equinos.idEquino = {$iIdEquino}
ORDER BY equinos.nombre ASC
LIMIT 1
QUERY;

$oDb -> query($sQuery);
$iTotal = $oDb->num_rows();
$aDatosResp["bInfoEq"] = ($iTotal>0) ? TRUE: FALSE;
$aDatosResp["infoEq"] = ($iTotal>0) ? $oDb -> fetch_array_assoc() : ""; 

echo json_encode((object)$aDatosResp);
?>