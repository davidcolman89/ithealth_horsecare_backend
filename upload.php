<?php
header("Access-Control-Allow-Origin: *");
include_once("config.php");
include_once(PATH_CLASES . "dbmysql.class.php");
include_once(PATH_CLASES . "formValidacion.class.php");
include_once(PATH_HELPERS . "helpers.php");

$datos = $_FILES;
$datos2 = $_REQUEST;
$sLogFile = PATH_LOG . basename($_SERVER['PHP_SELF']) . '.log';
$string = print_r($datos,true);
$string.= print_r($datos2,true);

$log = new Log($sLogFile);
$log->show($string , false);