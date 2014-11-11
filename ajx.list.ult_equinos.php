<?php
header("Access-Control-Allow-Origin: *");
include_once ("config.php");
include_once (PATH_CLASES . "dbmysql.class.php");
include_once (PATH_HELPERS . "helpers.php");

$aDatos = $_POST;
$sRespuesta = "";
$aDatosResp = array();

$oDb = new dbItHealth(DB_NAME);
$equinos = getIdUltEquinos();
$sQuery = getQueryListarUltEquinos($equinos);

$oDb -> query($sQuery);

while ($aDatosDb = $oDb -> fetch_array_assoc()) {
    $aDatosDb['equino_obs'] = truncate($aDatosDb['equino_obs'],20);
    $iIdEquino = $aDatosDb['equino_id'];
    $aDatosDb['bEstadoObito'] = chequearEstadoObito($iIdEquino);
	$aDatosResp[] = $aDatosDb;
}

echo json_encode((object)$aDatosResp);

?>


