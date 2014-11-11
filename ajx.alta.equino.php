<?php
header("Access-Control-Allow-Origin: *");
include_once("config.php");
include(PATH_CLASES . "dbmysql.class.php");
include(PATH_CLASES . "formValidacion.class.php");
include_once(PATH_HELPERS . "helpers.php");


$aDatos = $_POST;
$tabla = "equinos";
$sRespuesta ="";
$bRespuesta = TRUE;
$bGuardar = TRUE;
$update = false;

if(isset($aDatos["inptModHideIE"])&&!empty($aDatos["inptModHideIE"])){
    $update = true;
    $iIdEquino = $aDatos["inptModHideIE"];
    $sWhereEq = " idEquino = {$iIdEquino} ";
}

$oDb = new dbItHealth(DB_NAME);

$aValores = array(
	"fechaSistema"=>"NOW()"
	,"nombre"=>$aDatos["nombre"]
	,"idDuenio"=>$aDatos["id_duenio"]
	,"idSexo"=>$aDatos["id_sexo"]
	,"nacimiento"=>$aDatos["nacimiento"]
	,"observacion"=>$aDatos["obs"]
	,"idPadrillo"=>$aDatos["id_padrillo"]
	,"idMadre"=>$aDatos["id_madre"]
	,"idAbuelo"=>$aDatos["id_abuelo"]
);

$bNombre = FormValidacion::validarVacios($aValores['nombre']);
$bDuenio = FormValidacion::validarVacios($aValores['idDuenio']);
$bSexo = FormValidacion::validarVacios($aValores['idSexo']);
$bNac = FormValidacion::validarVacios($aValores['nacimiento']);

if(!$bNombre){
	$sRespuesta = "ERROR:Complete Nombre";
	$bRespuesta = $bNombre;
	$bGuardar = FALSE;
}else if(!$bDuenio){
	$sRespuesta = "ERROR:Complete Dueño";
	$bRespuesta = $bDuenio;
	$bGuardar = FALSE;
}else if(!$bSexo){
	$sRespuesta = "ERROR:Complete Sexo";
	$bRespuesta = $bSexo;
	$bGuardar = FALSE;
}else if(!$bNac){
	$sRespuesta = "ERROR:Complete Nacimiento";
	$bRespuesta = $bNac;
	$bGuardar = FALSE;
}


if($bGuardar){

    if($update===false){
        $oDb->insert($tabla, $aValores);
        $iIdEquino = $oDb->insert_id();

        if(empty($iIdEquino)){
            $sRespuesta = "ERROR:No se pudo insertar el equino";
            $bRespuesta = FALSE;
        }else{
            $sRespuesta = "Se inserto correctamente el equino";
            $bRespuesta = TRUE;
        }
    }else{
        $bQuery = $oDb->update($tabla, $aValores,$sWhereEq);

        if($bQuery){
            $sRespuesta = "Se actualizo correctamente el equino";
            $bRespuesta = TRUE;
        }else{
            $sRespuesta = "ERROR:No pudo actualizar el equino";
            $bRespuesta = FALSE;
        }
    }


}

$aRespuesta = array(
	"sr"=>$sRespuesta
	,"br"=>$bRespuesta
);

echo json_encode((object)$aRespuesta);


?>