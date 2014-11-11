<?php
header("Access-Control-Allow-Origin: *");
include_once ("config.php");
include_once (PATH_CLASES . "dbmysql.class.php");
include_once (PATH_HELPERS . "helpers.php");

$aDatos = $_POST;
$iIdEquino = $aDatos["ie"];
$bLimiteProb = ($aDatos["lp"]==='true')?TRUE:FALSE;

$aDatosResp = array();

$oDb = new dbItHealth(DB_NAME);


/*
 * Selecciona la informacion del equino que se le envie
 */
$sQuery = <<<QUERY
SELECT  equinos.idEquino AS 'equino_id',
        equinos.nombre AS 'equino_nombre',
        DATE_FORMAT(equinos.nacimiento,"%d/%m/%Y") AS 'equino_nac',
        equinos.observacion AS 'equino_obs',
        duenios.nombre AS 'duenio_nombre',
        eq_gen_1.nombre AS 'equino_padrillo',
        eq_gen_2.nombre AS 'equino_madre',
        eq_gen_3.nombre AS 'equino_abuelo',
        sexos.nombre AS 'equino_sexo',
        equinos.activo AS 'equino_activo'
FROM equinos
INNER JOIN duenios ON duenios.idDuenio = equinos.idDuenio
LEFT JOIN parientes AS eq_gen_1 ON eq_gen_1.idPariente = equinos.idPadrillo
LEFT JOIN parientes AS eq_gen_2 ON eq_gen_2.idPariente = equinos.idMadre
LEFT JOIN parientes AS eq_gen_3 ON eq_gen_3.idPariente = equinos.idAbuelo
LEFT JOIN sexos ON sexos.idSexo = equinos.idSexo

WHERE equinos.idEquino = {$iIdEquino}

ORDER BY equinos.nombre ASC

LIMIT 1
QUERY;

$oDb ->  query($sQuery);
$iTotal = $oDb->num_rows();

$aDatosResp["bInfoEq"] = ($iTotal>0) ? TRUE: FALSE;
$aDatosResp["infoEq"] = ($iTotal>0) ? $oDb -> fetch_array_assoc() : ""; 


$problemas = getProblemasByIdEquino($iIdEquino,$bLimiteProb);


if(!empty($problemas)){

    $aDatosResp["bInfoEqProb"] = true;

    foreach($problemas as $problema){
        $aDatosResp["infoEqProb"][] = $problema;
    }

}else{
    $aDatosResp["bInfoEqProb"] = false;
	$aDatosResp["infoEqProb"] = "";
}

$aDatosResp["bEstadoObito"] = chequearEstadoObito($iIdEquino);

echo json_encode((object)$aDatosResp);

?>