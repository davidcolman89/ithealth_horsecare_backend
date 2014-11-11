<?php
header("Access-Control-Allow-Origin: *");
include_once("config.php");
include(PATH_CLASES . "dbmysql.class.php");
include(PATH_CLASES . "formValidacion.class.php");
include_once(PATH_HELPERS . "helpers.php");

$aDatos = $_POST;
$iIdEquino = '';
$iIdProblema = $aDatos["inptHideIP"];
$sRespuesta ="";
$bGuardar = TRUE;
$oDb = new dbItHealth(DB_NAME);


$iIdEstado = $aDatos["id_estado"];

$aValores = array(
	"fecha"=>$aDatos["fecha"]
	,"idLugar"=>$aDatos["id_lugar"]
	,"observacion"=>$aDatos["obs"]
	,"fechaSistema"=>"NOW()"
	,"idProblema"=>$iIdProblema
);

$bFecha = FormValidacion::validarVacios($aValores['fecha']);
$bIdLugar = FormValidacion::validarVacios($aValores['idLugar']);
$bIdEstado = FormValidacion::validarVacios($aDatos['id_estado']);

if(!$bFecha){
	$sRespuesta = "ERROR:Complete Fecha";
	$bRespuesta = $bFecha;
	$bGuardar = FALSE;
}else if(!$bIdLugar){
	$sRespuesta = "ERROR:Complete Lugar";
	$bRespuesta = $bIdLugar;
	$bGuardar = FALSE;
}else if(!$bIdEstado){
	$sRespuesta = "ERROR:Complete Resultado";
	$bRespuesta = $bIdEstado;
	$bGuardar = FALSE;
}

if($bGuardar){
	$oDb->insert("evoluciones", $aValores);
	$iIdEvolucion = $oDb->insert_id();
	
	if(empty($iIdEvolucion)){
		$sRespuesta = "ERROR:No pudo insertar la evolución";
		$bRespuesta = FALSE;
	}else{

        if(isset($aDatos['id_estudio_tipo']) && !empty($aDatos['id_estudio_tipo'])) {
            crearEstudio($iIdEvolucion,$aDatos['id_estudio_tipo']);
        }

        if(isset($aDatos['id_medicacion']) && !empty($aDatos['id_medicacion'])){
            crearMedicacion($iIdEvolucion,$aDatos['id_medicacion']);
        }

        if(isset($aDatos['id_practica']) && !empty($aDatos['id_practica'])){
            crearPratica($iIdEvolucion,$aDatos['id_practica']);
        }


        $tabla = "problemas";
		$aValores = array("idEstado"=>$iIdEstado);
		$oDb->update($tabla, $aValores," idProblema = {$iIdProblema} ");

        if($iIdEstado==3){
            obitoEquino($iIdEquino);
        }
		
		$sRespuesta = "Se inserto correctamente la evolución";
		$bRespuesta = TRUE;
        $iIdEquino = getIdEquinoByProblema($iIdProblema);

	}
}

$aRespuesta = array(
	"sr"=>$sRespuesta
	,"br"=>$bRespuesta
        ,"id_equino"=>$iIdEquino
);

echo json_encode((object)$aRespuesta);

?>