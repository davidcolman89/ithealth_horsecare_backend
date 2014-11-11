<?php
header("Access-Control-Allow-Origin: *");
include_once("config.php");
include(PATH_CLASES . "dbmysql.class.php");
include(PATH_CLASES . "formValidacion.class.php");
include_once(PATH_HELPERS . "helpers.php");

$aDatos = $_POST;
$tabla = "parientes";
$sRespuesta ="";
$bGuardar = TRUE;
$oDb = new dbItHealth(DB_NAME);

$aValores = array(
	"fechaSistema"=>"NOW()"
	,"nombre"=>$aDatos["nombre"]
	,"descripcion"=>$aDatos["obs"]
);

$bNombre = FormValidacion::validarVacios($aValores['nombre']);

if(!$bNombre){
	$sRespuesta = "ERROR:Complete Nombre";
	$bRespuesta = $bNombre;
	$bGuardar = FALSE;
}


if($bGuardar){
	$oDb->insert($tabla, $aValores);
	$iIdGenetica = $oDb->insert_id();
	
	if(empty($iIdGenetica)){
		$sRespuesta = "ERROR:No pudo insertar el pariente";
		$bRespuesta = FALSE;
	}else{
		$sRespuesta = "Se inserto correctamente el pariente";
		$bRespuesta = TRUE;
	}	
}

$aRespuesta = array(
	"sr"=>$sRespuesta
	,"br"=>$bRespuesta
);

echo json_encode((object)$aRespuesta);


?>