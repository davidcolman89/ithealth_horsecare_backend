<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/*
 * log.class.php
 *
 *
 * Rutinas para escribir en el archivo log y opcionalmente mostrar
 * progreso por pantalla.
 *
 */

class Log
{
    public $verbose;

    public $logfile;
    public $logarray;

    public $pid = 0;
    public $separator = ' - ';

    public $from = '';
    public $to = '';


    public function __construct($logfile = null, $verbose = false, $pid = false)
    {
        $this->logfile = ($logfile ? $logfile : basename($_SERVER['PHP_SELF']) . '.log');
        $this->verbose = $verbose;
        $this->pid = ($pid ? $pid : getmypid());
        $this->reset();
    }


    public function show($msg, $add_array = false, $verbose = false, $die = false)
    {
        $msg = date('Y/m/d H:i:s') . $this->separator .
               $this->pid . $this->separator .
               trim($msg);

        if ($add_array)
            $this->add($msg);

        $msg .= "\n";

        if (($fd = fopen($this->logfile, 'a'))) {
            fwrite($fd, html_entity_decode($msg));
            fclose($fd);
        }

        if ($die)
            die($msg);

        if ($verbose || $this->verbose)
            print $msg;
    }


    public function reset()
    {
        $this->logarray = array();
    }


    public function add($msg)
    {
        $this->logarray[] = $msg;
    }


    public function size()
    {
        return sizeof($this->logarray);
    }


    public function truncate($msg = false)
    {
        if (($fd = fopen($this->logfile, 'w'))) {
            fclose($fd);
            if ($msg) {
                $this->show($msg);
            }
            return true;
        }
        return false;
    }


    public function mail($subject = null, $to = null, $from = null)
    {
        if (sizeof($this->logarray)) {
            if (!$to) $to = $this->to;
            if (!$subject) $subject = 'Log ' . $this->logfile;

            $header = 'From: ' . ($from ? $from : $this->from) . "\r\n" .
                  'X-Mailer: PHP/' . phpversion() . "\r\n" .
                  'Content-Type: text/html';

            $body = "<PRE>\r\n";
            foreach ($this->logarray as $msg) $body .= $msg . "\r\n";
            $body .= "</PRE>\r\n";

            return mail($to, $subject, $body, $header);
        }

        return true;
    }
}

?>
