<?php
header("Access-Control-Allow-Origin: *");
include_once("config.php");
include(PATH_CLASES . "dbmysql.class.php");
include(PATH_CLASES . "formValidacion.class.php");
include_once(PATH_HELPERS . "helpers.php");


$aDatos = $_POST;
$tabla = "problemas";
$iIdEquino = $aDatos["inptHideIE"];
$sRespuesta ="";
$bGuardar = TRUE;

$oDb = new dbItHealth(DB_NAME);

$iIdEstado = $aDatos["id_estado"];
$iIdCodProblema = $aDatos["id_dolencia"];

$aValores = array(
	"idCodProblema"=>$iIdCodProblema,
	"idEstado"=>$iIdEstado,
	"idEquino"=>$iIdEquino,
	"fechaSistema"=>"NOW()",
);

$bFecha = FormValidacion::validarVacios($aDatos['fecha']);
$bIdLugar = FormValidacion::validarVacios($aDatos['id_lugar']);
$bIdDolencia = FormValidacion::validarVacios($aValores['idCodProblema']);
$bIdEstado = FormValidacion::validarVacios($aValores['idEstado']);

if(!$bFecha){
	$sRespuesta = "ERROR:Complete Fecha";
	$bRespuesta = $bFecha;
	$bGuardar = FALSE;
}else if(!$bIdLugar){
	$sRespuesta = "ERROR:Complete Lugar";
	$bRespuesta = $bIdLugar;
	$bGuardar = FALSE;
}else if(!$bIdDolencia){
	$sRespuesta = "ERROR:Complete Dolencia";
	$bRespuesta = $bIdDolencia;
	$bGuardar = FALSE;
}else if(!$bIdEstado){
	$sRespuesta = "ERROR:Complete Resultado";
	$bRespuesta = $bIdEstado;
	$bGuardar = FALSE;
}

if($bGuardar){
	$oDb->insert($tabla, $aValores);
	$iIdProblema = $oDb->insert_id();
	
	if(empty($iIdProblema)){
		$sRespuesta = "ERROR:No pudo insertar el problema";
		$bRespuesta = FALSE;
	}else{//Problema generado

        if($iIdEstado==3){
            obitoEquino($iIdEquino);
        }

        //Crear Evolucion
        crearEvolucion($iIdProblema,$aDatos);

		$sRespuesta = "Se inserto correctamente el problema";
		$bRespuesta = TRUE;
	}	
}

$aRespuesta = array(
	"sr"=>$sRespuesta
	,"br"=>$bRespuesta
);

echo json_encode((object)$aRespuesta);


function crearEvolucion($iIdProblema,$aDatos)
{
    $oDb = new dbItHealth(DB_NAME);
    $tabla = "evoluciones";
    $iIdLugar = $aDatos['id_lugar'];
    $sFecha = $aDatos['fecha'];
    $sObservacion = $aDatos['obs'];

    $aValores = array(
        'idProblema'=>$iIdProblema,
        'idLugar'=>$iIdLugar,
        'fecha'=>$sFecha,
        'fechaSistema'=>'NOW()',
        'observacion'=>$sObservacion,
    );

    $oDb->insert($tabla, $aValores);
    $iIdEvolucion = $oDb->insert_id();

    if(isset($aDatos['id_estudio_tipo']) && !empty($aDatos['id_estudio_tipo'])) {
        crearEstudio($iIdEvolucion,$aDatos['id_estudio_tipo']);
    }

    if(isset($aDatos['id_medicacion']) && !empty($aDatos['id_medicacion'])){
        crearMedicacion($iIdEvolucion,$aDatos['id_medicacion']);
    }

    if(isset($aDatos['id_practica']) && !empty($aDatos['id_practica'])){
        crearPratica($iIdEvolucion,$aDatos['id_practica']);
    }

    return $iIdEvolucion;

}

?>

