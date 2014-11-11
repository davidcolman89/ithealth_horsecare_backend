<?php
header("Access-Control-Allow-Origin: *");
include_once ("config.php");
include_once (PATH_CLASES . "dbmysql.class.php");
include_once(PATH_HELPERS . "helpers.php");

$aDatos = $_POST;
$iIdEquino = $aDatos["ie"];
$aDatosResp["il"] = "";
$oDb = new dbItHealth(DB_NAME);

$qSelect = <<<QUERY
SELECT  duenios.idLugar
FROM    equinos
        INNER JOIN duenios ON equinos.idDuenio = duenios.idDuenio
WHERE   equinos.idEquino = {$iIdEquino}
LIMIT 1
QUERY;
$oDb->query($qSelect);
$iTotal = $oDb->num_rows();

if($iTotal>0){
    $aDatos=$oDb->fetch_array_assoc();
    $aDatosResp["il"] = $aDatos["idLugar"];
}

echo json_encode((object)$aDatosResp);
?>