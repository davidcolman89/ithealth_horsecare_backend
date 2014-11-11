<?php
header("Access-Control-Allow-Origin: http://brasil.ithealth.com.ar/dcolman/*");
include_once ("config.php");
include_once (PATH_CLASES . "dbmysql.class.php");

$aDatos = $_POST;
$sUsuario = $aDatos["u"];
$sPassword = $aDatos["p"];
$oDb = new dbItHealth(DB_NAME);

$sQuery = <<<QUERY
SELECT usuarios.idUsuario AS 'id'
FROM usuarios 
WHERE usuarios.usuario = '{$sUsuario}'
AND usuarios.password = MD5('{$sPassword}')
LIMIT 1
QUERY;

$oDb->query($sQuery);
$iTotal = $oDb->num_rows();

if($iTotal>0){
	
	$aDatosQuery = $oDb->fetch_array_assoc();
	$iIdUsuario = $aDatosQuery["id"];
	
	$aValores = array(
		"usuario"=>$iIdUsuario
		,"fecha"=>"NOW()"
		,"ip"=>$_SERVER['REMOTE_ADDR']
	);
	
	$oDb->insert("logUsuarios", $aValores);
	$iIdLogIngreso = $oDb->insert_id();

	$sRespuesta = "Bienvenido/a";
	$bRespuesta = true;
		
}else{
	$sRespuesta = "Usuario o Password incorrectos";
	$bRespuesta = false;
}

$aRespuesta = array(
	"sr"=>$sRespuesta
	,"br"=>$bRespuesta
);

echo json_encode((object)$aRespuesta);
?>