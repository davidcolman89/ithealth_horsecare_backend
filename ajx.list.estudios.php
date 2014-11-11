<?php
header("Access-Control-Allow-Origin: *");
include_once ("config.php");
include_once (PATH_CLASES . "dbmysql.class.php");
include_once (PATH_HELPERS . "helpers.php");

$aDatos = $_POST;
$sRespuesta = "";
$aEstudiosEvol = array();
$array = array();

$oDb = new dbItHealth(DB_NAME);

$sQuery = <<<QUERY
SELECT  DATE_FORMAT(estudios.fechaSistema,"%d/%m/%Y") AS 'fecha_creacion',
        estudios.idEstudio AS 'id_estudio',
        estudios.activo AS 'estudio_activo',
        codEstudios.descripcion AS 'estudio_tipo',
        equinos.nombre AS 'equino_nombre',
        equinos.idEquino AS 'id_equino'
FROM    estudios
        INNER JOIN codEstudios ON codEstudios.idCodEstudio = estudios.idCodEstudio
        INNER JOIN evoluciones ON evoluciones.idEvolucion = estudios.idEvolucion
        INNER JOIN problemas ON problemas.idProblema = evoluciones.idProblema
        INNER JOIN equinos ON equinos.idEquino = problemas.idEquino
        LEFT JOIN archivos ON archivos.idEstudio = estudios.idEstudio
WHERE   estudios.activo = 0
        AND equinos.activo = 1
QUERY;

$oDb -> query($sQuery);
$oDb->fetch_all($aEstudiosEvol);

echo json_encode((object)array(
    'estudios_evol'=>$aEstudiosEvol
));