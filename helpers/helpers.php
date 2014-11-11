<?php
header("Access-Control-Allow-Origin: *");
@include_once ("config.php");
@include_once (PATH_CLASES . "dbmysql.class.php");

function truncate($string,$length=100,$append="&hellip;") {
  $string = trim($string);

  if(strlen($string) > $length) {
    $string = substr($string,0,$length);
    $string = $string . $append;
  }
    
  return $string;
}


/*
 * Chequea el estado obito del equino
 */
function chequearEstadoObito($iIdEquino){
        
    $iEstadoObito = 3;
    $bRespuesta = FALSE;
    
    $oDb = new dbItHealth(DB_NAME);    
    $qSelect = <<<QUERY
SELECT  COUNT(problemas.idProblema) AS 'cant'
FROM    problemas
WHERE   problemas.idEquino = {$iIdEquino}
        AND problemas.idEstado = {$iEstadoObito}
LIMIT 1
QUERY;
    $oDb->query($qSelect);
    $iTotal = $oDb->num_rows();
    
    if($iTotal>0){
        $aDatos=$oDb->fetch_array_assoc();
        if($aDatos["cant"]>0){
            $bRespuesta = TRUE;    
        }    
    }
    
    return $bRespuesta;
        
}

function armarNombreArchivo($sAux="default"){

    $iRandom = rand(99,999);
    $sFechaHoy = date('Ymdhis');
    $sSeparador = "_";
    $sNombreArchivo = join($sSeparador,array(
        $sAux,
        $sFechaHoy,
        $iRandom
    ));

    return $sNombreArchivo;

}

function getQueryListarEquinos()
{
    $sQuery = <<<QUERY
SELECT
			equinos.idEquino AS 'equino_id'
		,	equinos.nombre AS 'equino_nombre'
		,	equinos.observacion AS 'equino_obs'
		, equinos.idDuenio as 'id_duenio'
		, duenios.nombre AS 'duenio_nombre'
		, '' AS 'estado'
		, '' AS 'acciones'
FROM equinos
INNER JOIN duenios ON duenios.idDuenio = equinos.idDuenio
WHERE 1=1
ORDER BY equinos.idEquino DESC
QUERY;

    return $sQuery;

}

function getQueryListarUltEquinos($equinos)
{
    $aux = join(',',$equinos);

    $sQuery = <<<QUERY
SELECT
			equinos.idEquino AS 'equino_id'
		,	equinos.nombre AS 'equino_nombre'
		,	equinos.observacion AS 'equino_obs'
		, equinos.idDuenio as 'id_duenio'
		, duenios.nombre AS 'duenio_nombre'
		, '' AS 'estado'
		, '' AS 'acciones'
FROM equinos
INNER JOIN duenios ON duenios.idDuenio = equinos.idDuenio
WHERE equinos.idEquino in ({$aux})
ORDER BY equinos.idEquino DESC
QUERY;

    return $sQuery;

}

function getProblemasByIdEquino($iIdEquino,$bQueryLimit)
{
    $oDb = new dbItHealth(DB_NAME);
    $problemas = array();
    $iQueryLimitCant = 2;
    $sQueryLimit = ($bQueryLimit) ? "LIMIT {$iQueryLimitCant}" : "";

    $sQuery = <<<QUERY
SELECT  problemas.idEquino AS 'equino_id',
        problemas.idProblema AS 'prob_id',
        codProblemas.descripcion AS 'prob_dolencia',
        estados.idEstado AS 'prob_id_estado',
        estados.nombre AS 'prob_estado'
FROM problemas
INNER JOIN codProblemas ON codProblemas.idCodProblema = problemas.idCodProblema
INNER JOIN estados ON estados.idEstado = problemas.idEstado
WHERE problemas.idEquino = {$iIdEquino}
ORDER BY problemas.idProblema DESC
{$sQueryLimit}
QUERY;

    $oDb -> query($sQuery);
    $iTotal = $oDb->num_rows();

    if($iTotal>0){
        while($aDatosQuery = $oDb->fetch_array_assoc()){
            $iIdProblema = $aDatosQuery['prob_id'];
            $aDatosQuery['prob_fecha'] = getFechaProblema($iIdProblema);
            $problemas[] =  $aDatosQuery;
        }
    }

    return $problemas;

}


function crearEstudio($iIdEvolucion,$iIdCodEstudio)
{

    $oDb = new dbItHealth(DB_NAME);
    $tabla = "estudios";
    $aValores = array(
        "fechaSistema"=>'NOW()',
        "idCodEstudio" =>  $iIdCodEstudio,
        "idEvolucion" =>  $iIdEvolucion,
        "activo"=>0
    );

    $oDb->insert($tabla, $aValores);
    return $oDb->insert_id();

}

function crearMedicacion($iIdEvolucion,$iIdCodMedicacion)
{

    $oDb = new dbItHealth(DB_NAME);
    $tabla = "medicaciones";
    $aValores = array(
        "fechaSistema"=>'NOW()',
        "idCodMedicacion" =>  $iIdCodMedicacion,
        "idEvolucion" =>  $iIdEvolucion
    );

    $oDb->insert($tabla, $aValores);
    return $oDb->insert_id();

}

function crearPratica($iIdEvolucion,$iIdCodPractica)
{

    $oDb = new dbItHealth(DB_NAME);
    $tabla = "practicas";
    $aValores = array(
        "fechaSistema"=>'NOW()',
        "idCodPractica" =>  $iIdCodPractica,
        "idEvolucion" =>  $iIdEvolucion
    );

    $oDb->insert($tabla, $aValores);
    return $oDb->insert_id();

}

function obitoEquino($iIdEquino)
{
    $oDb = new dbItHealth(DB_NAME);
    $oDb->update("equinos",array('activo'=>0)," idEquino={$iIdEquino}");
}


function getIdUltEquinos()
{
    $equinos = array();
    $bLimit = true;
    $iNumLimit = 5;
    $sLimit = ($bLimit===true) ? " LIMIT {$iNumLimit} " : '';

    $oDb = new dbItHealth(DB_NAME);

    $query = <<<QUERY
SELECT problemas.idEquino
FROM  problemas
INNER JOIN evoluciones ON evoluciones.idProblema = problemas.idProblema
GROUP BY evoluciones.idProblema
ORDER BY evoluciones.idProblema DESC
{$sLimit}
QUERY;

    $oDb->query($query);
    $iTotal = $oDb->num_rows();

    if($iTotal>0){

        while($datos=$oDb->fetch_array_assoc()){
            $equinos[] = $datos['idEquino'];
        }

    }


    return $equinos;

}


function getFechaProblema($iIdProblema)
{
    $oDb = new dbItHealth(DB_NAME);

    $query = <<<QUERY
SELECT
DATE_FORMAT(evoluciones.fecha,"%d/%m/%Y") AS 'fecha'
FROM evoluciones
WHERE evoluciones.idProblema = {$iIdProblema}
ORDER BY evoluciones.idEvolucion ASC
QUERY;

    $oDb->query($query);
    $datos = $oDb->fetch_array_assoc();
    $fecha = $datos['fecha'];

    return $fecha;

}

function getIdEquinoByProblema($iIdProblema)
{
    $oDb = new dbItHealth(DB_NAME);

    $query = <<<QUERY
SELECT
problemas.idEquino
FROM problemas
WHERE problemas.idProblema = {$iIdProblema}
LIMIT 1
QUERY;

    $oDb->query($query);
    $datos = $oDb->fetch_array_assoc();
    $iIdEquino = $datos['idEquino'];

    return $iIdEquino;
}