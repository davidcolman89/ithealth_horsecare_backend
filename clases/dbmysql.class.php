<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

include_once(PATH_CLASES . 'mysql.class.php');
include_once(PATH_CLASES . 'log.class.php');


class dbItHealth extends MySQL
{
	public $LOG;
    public $log_prefix;


    public function __construct($db = '', $host = '', $user = '', $password = '')
    {
        if (defined('PATH_LOG')){
			$sLogFile = PATH_LOG . basename($_SERVER['PHP_SELF']) . '.log';
			$this->log_prefix =  basename($_SERVER['PHP_SELF']) . ' - ';
			$this->LOG = new Log($sLogFile);	
		} else {
			$this->log_prefix = basename($_SERVER['PHP_SELF']) . ' - ';
			$this->LOG = new Log();        	
        }		

        parent::__construct($db, $host, $user, $password);
    }


    public function show_error($errno, $errmsg, $query)
    {
        $this->LOG->show($this->log_prefix . 'SQL: ERROR: ' . $errno . ' - ' . $errmsg . ' - sql: ' . $query, false);
    }


    public function show_query($query)
    {
        $this->LOG->show($this->log_prefix . 'SQL: ' . $query, false);
    }
}

?>
