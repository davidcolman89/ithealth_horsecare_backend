<?php
header("Access-Control-Allow-Origin: *");
include_once("config.php");
include(PATH_CLASES . "formValidacion.class.php");
include(PATH_CLASES . "dbmysql.class.php");
include_once(PATH_HELPERS . "helpers.php");


$aDatos = $_POST;
$tabla = "duenios";
$sRespuesta ="";
$bGuardar = TRUE;
$update = false;

if(isset($aDatos["inptModHideIC"]) && !empty($aDatos["inptModHideIC"])){
    $iIdCliente = $aDatos["inptModHideIC"];
    $sWhereCli = " idDuenio = {$iIdCliente} ";
    $update = true;
}


$oDb = new dbItHealth(DB_NAME);

$aValores = array(
	"fechaSistema"=>"NOW()"
	,"nombre"=>$aDatos["nombre"]
	,"direccion"=>$aDatos["direccion"]
	,"idProvincia"=>$aDatos["id_provincia"]
	,"idLocalidad"=>$aDatos["id_localidad"]
	,"telefono"=>$aDatos["telefono"]
	,"celular"=>$aDatos["celular"]
	,"email"=>$aDatos["email"]
	,"idLugar"=>$aDatos["id_lugar"]
);

$bNombre = FormValidacion::validarVacios($aValores['nombre']);
$bDir = true;
#$bDir = FormValidacion::validarVacios($aValores['direccion']);
$bProv = true;
#$bProv = FormValidacion::validarVacios($aValores['idProvincia']);
$bLocal = true;
#$bLocal = FormValidacion::validarVacios($aValores['idLocalidad']);
$bEmail = true;
#$bEmail = FormValidacion::validarCorreo($aValores['email']);

if(!$bNombre){
	$sRespuesta = "ERROR:Complete Nombre";
	$bRespuesta = $bNombre;
	$bGuardar = FALSE;
}else if(!$bDir){
	$sRespuesta = "ERROR:Complete Dirección";
	$bRespuesta = $bDir;
	$bGuardar = FALSE;
}else if(!$bProv){
	$sRespuesta = "ERROR:Complete Provincia";
	$bRespuesta = $bProv;
	$bGuardar = FALSE;
}else if(!$bLocal){
    $sRespuesta = "ERROR:Complete Localidad";
    $bRespuesta = $bLocal;
    $bGuardar = FALSE;
}else if(!$bEmail){
	$sRespuesta = "ERROR:Complete Email";
	$bRespuesta = $bEmail;
	$bGuardar = FALSE;
}


if($bGuardar){

    if($update===false){
        $oDb->insert($tabla, $aValores);
        $iIdCliente = $oDb->insert_id();

        if(empty($iIdCliente)){
            $sRespuesta = "ERROR:No pudo insertar el cliente";
            $bRespuesta = FALSE;
        }else{
            $sRespuesta = "Se inserto correctamente el cliente";
            $bRespuesta = TRUE;
        }
    }else{
        $bQuery = $oDb->update($tabla, $aValores,$sWhereCli);

        if($bQuery){
            $sRespuesta = "Se actualizo correctamente el cliente";
            $bRespuesta = TRUE;
        }else{
            $sRespuesta = "ERROR:No pudo actualizar el cliente";
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