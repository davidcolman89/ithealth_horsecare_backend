<?php

define("DEVEL", FALSE);
define("DS", "/");
define('PATH_ROOT', __DIR__);

define('PATH_CONTROLLERS', PATH_ROOT . DS . 'controllers' . DS);
define('PATH_MODELS', PATH_ROOT . DS . 'models' . DS);
define('PATH_HELPERS', PATH_ROOT . DS . 'helpers' . DS);
define('PATH_CLASES', PATH_ROOT . DS . 'clases' . DS);
define('PATH_LOG', PATH_ROOT . DS . 'log' . DS);
define('PATH_TEMPLATES', PATH_ROOT . DS . 'template' . DS);
define('PATH_TEMPLATES_CACHE', PATH_TEMPLATES . 'cache');
define('PATH_LIB', PATH_ROOT . DS . 'lib' . DS);
define('PATH_MEDIA', PATH_ROOT . DS . 'media' . DS);

//Config Base de datos

//define('DB_NAME', 'brasil_ithealth_com_ar_1');
//define('DB_NAME_DEVEL', '');
//define('DB_SERVER', 'brasil_ithealth_com_ar_1');

define('DB_NAME', 'brasil_ithealth_com_ar_testAriel');
define('DB_NAME_DEVEL', '');
define('DB_SERVER', 'brasil_ithealth_com_ar_testAriel');

if (DEVEL) {
	define('DB_HOST', 'localhost');
	define('DB_USR', 'root');
	define('DB_PSS', 'minS4NC0CH0seg');
} else {
	#define('DB_HOST', '201.216.232.39');
	define('DB_HOST', 'localhost');
	define('DB_USR', 'ithbr-admin');
	define('DB_PSS', 'zicopeledinho1970');
}

$__MySQL_server_array = array(DB_SERVER => array('host' => DB_HOST, 'user' => DB_USR, 'password' => DB_PSS));

$__MySQL_db_array = array(DB_NAME => array('server' => DB_SERVER, 'db' => DB_NAME, 'db_devel' => ''));