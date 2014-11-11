<?php
header("Access-Control-Allow-Origin: *");
include_once ("config.php");
include_once (PATH_CLASES . "dbmysql.class.php");

$aDatos = $_POST;
$iEstadoObito = 3;
$iIdEquino = $aDatos["ie"];
$aDatosResp["br"] = FALSE;//br = bool respuesta

$oDb = new dbItHealth(DB_NAME);

$qSelect = <<<QUERY
SELECT  COUNT(problemas.id) AS 'cant'
FROM    problemas
WHERE   problemas.id_equino = {$iIdEquino}
        AND problemas.id_estado = {$iEstadoObito}
QUERY;
$oDb->query($qSelect);
$iTotal = $oDb->num_rows();

if($iTotal>0){
    $aDatos=$oDb->fetch_array_assoc();
    if($aDatos["cant"]>0){
        $aDatosResp["beo"] = TRUE;    
    }    
}

echo json_encode((object)$aDatosResp);
?>