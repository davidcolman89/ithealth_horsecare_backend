<?php
header("Access-Control-Allow-Origin: *");
include_once ("config.php");
include_once (PATH_CLASES . "dbmysql.class.php");
include_once (PATH_HELPERS . "funciones.exportar.php");

$aDatos = $_POST;
$relacionesClientes = array();
$relacionesEquinos = array();
$relacionesEstudios = array();
$relacionesProblemas = array();
$relacionesEvoluciones = array();
$relacionesEstudios = array();
$relacionesMedicaciones = array();
$relacionesPracticas = array();
$duenios = json_decode($aDatos['clientes']);
$equinos = json_decode($aDatos['equinos']);
$problemas = json_decode($aDatos['problemas']);
$evoluciones = json_decode($aDatos['evoluciones']);
$estudios = json_decode($aDatos['estudios']);
$medicaciones = json_decode($aDatos['medicaciones']);
$practicas = json_decode($aDatos['practicas']);

if(!empty($duenios)){
    $relacionesClientes  = insertarDuenios($duenios);
}

if(!empty($equinos)){
    $relacionesEquinos = insertarEquinos($equinos,$relacionesClientes);
}

if(!empty($problemas)){

    $relacionesProblemas = insertarProblemas($problemas,array("equinos"=>$relacionesEquinos));

    if(!empty($evoluciones)){
        $relacionesEvoluciones = insertarEvoluciones($evoluciones,array("problemas"=>$relacionesProblemas));
    }

    if(!empty($estudios)){
        $relacionesEstudios = insertarEstudios($estudios,$relacionesEvoluciones);
    }

    if(!empty($medicaciones)){
        $relacionesMedicaciones = insertarMedicaciones($medicaciones,$relacionesEvoluciones);
    }

    if(!empty($practicas)){
        $relacionesPracticas = insertarPracticas($practicas,$relacionesEvoluciones);
    }

}

$jsonPost = json_encode(array(
    'clientes'=>$duenios,
    'equinos'=>$equinos,
    'problemas'=>$problemas,
    'evoluciones'=>$evoluciones,
    //'estudios'=>$estudios,
));

$jsonRelaciones = json_encode(array(
    'clientes'=>$relacionesClientes,
    'equinos'=>$relacionesEquinos,
    'problemas'=>$relacionesProblemas,
    'evoluciones'=>$relacionesEvoluciones,
    //'estudios'=>$relacionesEstudios,
));
insertarOfflineHistorico(array('fecha_creacion'=>'NOW()','json_post'=>$jsonPost,'json_relaciones'=>$jsonRelaciones));

$bool = true;

if($bool){
    $sRespuesta = 'Exito!';
    $bRespuesta = true;
}else{
    $sRespuesta = 'No se pudo exportar la informacion';
    $bRespuesta = false;
}

$aRespuesta = array(
    "sr"=>$sRespuesta
,"br"=>$bRespuesta
);

echo json_encode((object)$aRespuesta);