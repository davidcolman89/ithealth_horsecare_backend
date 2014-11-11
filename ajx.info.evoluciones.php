<?php
header("Access-Control-Allow-Origin: *");
include_once ("config.php");
include_once (PATH_CLASES . "dbmysql.class.php");
include_once (PATH_HELPERS . "helpers.php");

$aDatos = $_POST;

$iIdEquino = $aDatos["ie"];
$iIdProblema = $aDatos["ip"];
$oDb = new dbItHealth(DB_NAME);


/*
 * Selecciona la informacion de los problemas del equino enviado
 */
$sQuery = <<<QUERY
SELECT
evoluciones.idEvolucion,
evoluciones.idProblema
FROM	evoluciones
WHERE evoluciones.idProblema = {$iIdProblema}
ORDER BY evoluciones.fecha DESC, evoluciones.idEvolucion DESC
QUERY;

$oDb -> query($sQuery);
$iTotal = $oDb->num_rows();
$aDatosResp["bInfoEvol"] = ($iTotal>0) ? TRUE: FALSE;

if($iTotal>0){
	while($aDatosQuery = $oDb->fetch_array_assoc()){
        $infoEvolucion = obtenerInfoEvolucion($aDatosQuery['idEvolucion']);
		$aDatosResp["id_prob"] =  $iIdProblema ;
		$aDatosResp["infoEvol"][] =  $infoEvolucion ;
	}
}else{
	$aDatosResp["infoEvol"] = "";
	$aDatosResp["sError"] = "No existen evoluciones para este problema";
}

echo json_encode((object)$aDatosResp);


function obtenerInfoEvolucion($iIdEvolucion)
{
    $oDb = new dbItHealth(DB_NAME);

    $sQuery = <<<QUERY
SELECT
evoluciones.idEvolucion AS 'evol_id',
evoluciones.idProblema AS 'evol_prob_id',
DATE_FORMAT(evoluciones.fecha,"%d/%m/%Y") AS 'evol_fecha',
evoluciones.observacion AS 'evol_obs',
lugares.nombre AS 'evol_lugar'
FROM	evoluciones
        LEFT JOIN lugares ON lugares.idLugar = evoluciones.idLugar

WHERE evoluciones.idEvolucion = {$iIdEvolucion}
ORDER BY evoluciones.fecha DESC
QUERY;
    $oDb->query($sQuery);

    $iTotal = $oDb->num_rows();

    if($iTotal>0){

        while($datos = $oDb->fetch_array_assoc()){
            $iIdEvolucion = $datos['evol_id'];
            $datos['practicas'] = obtenerPracticas($iIdEvolucion);
            $datos['estudios'] = obtenerEstudios($iIdEvolucion);
            $datos['medicaciones'] = obtenerMedicaciones($iIdEvolucion);
            $evolucion[] = $datos;
        }

    }else {
        $evolucion = array();
    }

    return $evolucion;

}


function obtenerPracticas($iIdEvolucion)
{

    $sQuery = <<<QUERY
SELECT  codPracticas.descripcion
FROM    practicas
        LEFT JOIN codPracticas ON codPracticas.idCodPractica = practicas.idCodPractica
WHERE   practicas.idEvolucion = {$iIdEvolucion}
QUERY;

    return obtenerRegistros($sQuery);

}

function obtenerEstudios($iIdEvolucion)
{

    $sQuery = <<<QUERY
SELECT  estudios.idEstudio,
        estudios.activo,
        estudios.doctor,
        estudios.observacion,
        codEstudios.descripcion
FROM    estudios
        LEFT JOIN codEstudios ON codEstudios.idCodEstudio = estudios.idCodEstudio
WHERE   estudios.idEvolucion = {$iIdEvolucion}
QUERY;

    $registros = obtenerRegistros($sQuery);

    foreach($registros as $key=>$registro){
        $iIdEstudio = $registros[$key]['idEstudio'];
        $registros[$key]['archivos'] = obtenerArchivos($iIdEstudio);
    }

    return $registros;

}

function obtenerMedicaciones($iIdEvolucion)
{

    $sQuery = <<<QUERY
SELECT  codMedicaciones.nombrecom AS 'descripcion'
FROM    medicaciones
        LEFT JOIN codMedicaciones ON codMedicaciones.idCodMedicacion = medicaciones.idCodMedicacion
WHERE   medicaciones.idEvolucion = {$iIdEvolucion}
QUERY;

    return obtenerRegistros($sQuery);

}

function obtenerArchivos($iIdEstudio)
{

    $sQuery = <<<QUERY
SELECT  archivos.nombre
FROM    archivos
WHERE   archivos.idEstudio = {$iIdEstudio}
QUERY;

    return obtenerRegistros($sQuery);

}

function obtenerRegistros($sQuery)
{

    $registros = array();
    $oDb = new dbItHealth(DB_NAME);

    $oDb->query($sQuery);
    $iTotal = $oDb->num_rows();

    if($iTotal>0)
    {
        while($datos = $oDb->fetch_array_assoc())
        {
            $registros[] = $datos;
        }
    }

    return $registros;

}

function obtenerRegistro($sQuery)
{

    $registros = array();
    $oDb = new dbItHealth(DB_NAME);

    $oDb->query($sQuery);
    $iTotal = $oDb->num_rows();

    if($iTotal>0)
    {
        $registros = $oDb->fetch_array_assoc();

    }

    return $registros;

}

?>