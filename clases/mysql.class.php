<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/*
 * mysql.class.php
 *
 * Rutinas de acceso a la bases de datos (MySQL).
 *
 */

class MySQL
{
    public $devel = null;
    public $debug = false;

    public $html_output = true;

    public $host = null;
    public $database = null;
    public $connection = null;
    public $result = null;
    public $result_mode = MYSQLI_STORE_RESULT;      // puede ser MYSQLI_USE_RESULT

    protected $savepoint = null;

    public $replicate_count = 0;
    public $replicate_to_table = '';
    protected $replicate_to_cols = array();
    protected $replicate_from_table = '';
    protected $replicate_from_cols = array();

    private $_autocommit = true;


    public function __construct($db = '', $host = '', $user = '', $password = '')
    {
        if (defined('DEVEL'))
            $this->devel = DEVEL;

        if (is_null($this->devel))
            $this->devel = (stristr(__FILE__, '_des/') || stristr(__FILE__, '_desa/'));

        if ($db || $host)
            $this->connect($db, $host, $user, $password);
			$this->query("SET NAMES 'utf8'");
    }


    public function __destruct()
    {
        $this->close();
    }


    public function show_error($errno, $errmsg, $query)
    {
        if ($this->html_output)
            die('<font color="#000000"><b>' . $errno . ' - ' . $errmsg . '<br><br>' . $this->output($query) .
                '<br><br><small><font color="#ff0000">[STOP]</font></small><br><br></b></font>');
        else
            die("\n$errno - $errmsg\n\n" . $this->output($query) . "\n\n[STOP]\n\n");
    }


    public function show_query($query)
    {
        echo $query . "<br>\n";
    }



    public function connect($db, $host = '', $user = '', $password = '')
    {
        global $__MySQL_server_array, $__MySQL_db_array;

        $this->close();

        // si no se indico el host, buscar la base en la lista
        if ($host == '') {
            // ubicar la base
            $d = @$__MySQL_db_array[$db];

            // obtener datos de la base o dar error
            if (is_array($d)) {
                if ($this->devel && array_key_exists('server_devel', $d))
                    $h = $d['server_devel'];
                else
                    $h = $d['server'];
            } else {
                $this->show_error(-1, 'No se encuentra la base de datos', $db);
                return false;
            }

            // si es desarrollo cambiar a base de desarrollo
            if ($this->devel)
                $db = $d['db_devel'];
            else
                $db = $d['db'];

            // primero ver si el usuario/password se definio en la base
            if (array_key_exists('host', $d)) $host = $d['host'];
            if (array_key_exists('user', $d)) $user = $d['user'];
            if (array_key_exists('password', $d)) $password = $d['password'];

            // ubicar el server si no esta definido en la base
            if ($host == '') {
                $d = @$__MySQL_server_array[$h];

                // obtener datos del server o dar error
                if (is_array($d)) {
                    $host = $d['host'];
                    if ($user == '') $user = $d['user'];
                    if ($password == '') $password = $d['password'];
                } else {
                    $this->show_error(-1, 'No se encuentra el servidor de la base de datos', $h);
                    return false;
                }
            }
        }
        // el host esta en la tabla de servers
        else if (substr($host, 0, 1) == '=') {
            $d = @$__MySQL_server_array[ltrim(substr($host, 1))];

            // obtener datos del server o dar error
            if (is_array($d)) {
                $host = $d['host'];
                if ($user == '') $user = $d['user'];
                if ($password == '') $password = $d['password'];
            } else {
                $this->show_error(-1, 'No se encuentra el servidor de la base de datos', $host);
                return false;
            }
        }


        $this->host = $host;
        $this->connection = @mysqli_connect($host, $user, $password);

        if (!$this->connection) {
            $this->show_error(-1, 'No fue posible conectarse al host ' . $host, @mysqli_connect_error());
            return false;
        }

        if (!$this->select_db($db)) {
            $this->show_error(-1, 'No fue posible seleccionar la base de datos', $db . ' en ' . $host);
            return false;
        }

        if ($this->debug)
            $this->show_query("Conectado a la base de datos '$db' en '$host'");

        return $this->connection;
    }


    public function connect_if_not($db, $host = '', $user = '', $password = '')
    {
        if ($this->connection)
            return $this->connection;
        else
            return $this->connect($db, $host, $user, $password);
    }


    public function select_db($db)
    {
        if (@mysqli_select_db($this->connection, $db)) {
            $this->database = $db;
            return true;
        }

        return false;
    }


    public function selected_db()
    {
        return $this->database;
    }


    public function is_connected()
    {
        return $this->connection ? true : false;
    }


    public function close()
    {
        if ($this->connection) {
            @mysqli_close($this->connection);
            $this->connection = null;
            $this->result = null;
        }

        return true;
    }


    public function error()
    {
        return @mysqli_errno($this->connection);
    }


    public function errormsg()
    {
        return @mysqli_error($this->connection);
    }


    public function exec($query, $show_errors = true)
    {
        return $this->query($query, $show_errors);
    }


    public function query($query, $show_errors = true)
    {
        if ($this->debug)
            $this->show_query($query);

        if ($this->result && $this->result_mode == MYSQLI_USE_RESULT)
            $this->free_result();

        $this->result = @mysqli_query($this->connection, str_replace("\t", ' ', $query), $this->result_mode);

        if ($this->result)
            return $this->result;

        if ($show_errors)
            $this->show_error($this->error(), $this->errormsg(), $query);

        return false;
    }


    function delete($table, $where = '')
    {
        if ($where && strtoupper(substr($where, 0, 6)) != "WHERE ") $where = "WHERE $where";

        return $this->query("DELETE FROM $table $where");
    }


    function insert($table, $values, $parameters = '', $show_errors = true)  // $parameters= 'ignore' o array de valores para ON DUPLICATE KEY
    {
        return $this->perform($table, $values, 'insert', $parameters, $show_errors);
    }


    function update($table, $values, $where, $show_errors = true)
    {
        return $this->perform($table, $values, 'update', $where, $show_errors);
    }


    function replace($table, $values, $show_errors = true)
    {
        return $this->perform($table, $values, 'replace', '', $show_errors);
    }


    function perform($table, $data, $action = 'insert', $parameters = '', $show_errors = true)
    {
        reset($data);

        $action = strtolower($action);

        if ($action == 'insert' || substr($action, 0, 7) == 'replace') {
            $query = ' INTO ' . $table . ' (';
            while (list($column, ) = each($data)) {
                $query .= $column . ', ';
            }
            $query = substr($query, 0, -2) . ') VALUES (';
            reset($data);
            while (list(, $value) = each($data)) {
                if (is_null($value)) {
                    $query .= 'NULL, ';
                } else {
                    switch (strtolower((string)$value)) {
                        case 'now()':
                        case 'null':
                            $query .= $value . ', ';
                            break;
                        default:
                            $query .= '\'' . $this->input($value) . '\', ';
                            break;
                    }
                }
            }
            $query = substr($query, 0, -2) . ')';

            if (!empty($parameters)) {
                if (is_array($parameters)) {        // solo valido para INSERT
                    $query .= ' ON DUPLICATE KEY UPDATE ';
                    reset($parameters);
                    while (list($column, $value) = each($parameters)) {
                        if (is_null($value)) {
                            $query .= $column . ' = NULL, ';
                        } else {
                            switch ((string)$value) {
                                case 'now()':
                                case 'null':
                                case 'NULL':
                                    $query .= $column . ' = ' . $value . ', ';
                                    break;
                                default:
                                    $query .= $column . ' = \'' . $this->input($value) . '\', ';
                                    break;
                            }
                        }
                    }
                    $query = substr($query, 0, -2);
                } else {
                    $query = ' ' . $parameters . $query;
                }
            }

            $query = strtoupper($action) . $query;
        }
        elseif ($action == 'update')
        {
            $query = strtoupper($action) . ' ' . $table . ' SET ';
            while (list($column, $value) = each($data)) {
                if (is_null($value)) {
                        $query .= $column . ' = NULL, ';
                } else {
                    switch ((string)$value) {
                        case 'now()':
                        case 'null':
                        case 'NULL':
                            $query .= $column .= ' = ' . $value . ', ';
                            break;
                        default:
                            $query .= $column . ' = \'' . $this->input($value) . '\', ';
                            break;
                    }
                }
            }
            $query = substr($query, 0, -2) . ' WHERE ' . $parameters;
        }
        return $this->query($query, $this->connection, $show_errors);
    }


    // replica los registros indicados en $where de $from_table a $to_table. Se copian todos los
    // campos de $from_table que existan (con el mismo nombre) en $to_table. Ademas copia los campos/valores
    // indicados en $data (estos tienen prioridad). Si se llama varias veces al metodo con las mismas tablas
    // los datos de las mismas se inicializan una unica vez. El parametro $parameters puede ser IGNORE, REPLACE
    // o ON DUPLICATE KEY UPDATE...
    // db2 permite copiar los registros en una tabla de otra base, incluso de otro servidor. db2 debe ser un objeto
    // ya inicializado.
    function replicate($from_table, $to_table = null, $where = null, $data = null, $parameters = null, $db2 = null, $default_columns = true, $show_errors = true)
    {
        $this->replicate_count = 0;

        if (is_null($to_table)) $to_table = $from_table;
        if (is_null($data)) $default_columns = true;

        // si la tabla from es distinta de la ultima usada, obtener los campos
        $tbl = "{$this->database}.$from_table";
        if ($default_columns) {
            if ($this->replicate_from_table != $tbl) {
                if (!$this->columns($from_table))
                    return false;

                $this->replicate_from_table = $tbl;
                $this->replicate_from_cols = array();

                while ($row = $this->fetch_array(null, MYSQLI_NUM)) {
                    $this->replicate_from_cols[] = $row[0];
                }
            }
        } else {
            $this->replicate_from_table = '';
            $this->replicate_from_cols = array();
        }

        if (is_null($db2)) {
            $db2 =& $this;
            $tbl = "{$this->database}.$to_table";
        } else {
            $tbl = "{$db2->database}.$to_table:2";
        }

        // si la tabla to es distinta de la ultima usada, obtener los campos
        if ($default_columns) {
            if ($db2->replicate_to_table != $tbl) {
                if (!$db2->columns($to_table))
                    return false;

                $this->replicate_to_table = $tbl;
                $this->replicate_to_cols = array();

                while ($row = $db2->fetch_array(null, MYSQLI_NUM)) {
                    $this->replicate_to_cols[] = $row[0];
                }
            }
        } else {
            $this->replicate_to_table = '';
            $this->replicate_to_cols = array();
        }

        if ($db2 == $this) {

            // copia de tablas en en el mismo servidor

            $from_cols = $to_cols = '';

            // buscar columnas que esten en las dos tablas y que no esten en $data
            foreach ($this->replicate_from_cols as $column) {
                if (array_search($column, $this->replicate_to_cols) !== false &&
                            (is_null($data) || !array_key_exists($column, $data))) {
                    $from_cols .= ", $column";
                    $to_cols .= ", $column";
                }
            }

            // agregar las columnas/valores indicadas en $data (no agrego '' para permitir usar nombre de campos)
            if (is_array($data)) {
                reset($data);
                while (list($column, $value) = each($data)) {
                    $from_cols .= ", $value";
                    $to_cols .= ", $column";
                }
            }

            // armar query
            $query = '';

            if (is_array($parameters)) {
                $query = "INSERT INTO ";
            } else if (strtolower(substr($parameters, 0, 7)) != 'replace') {
                $query = "INSERT " . ($parameters ? $parameters . " " : "") . "INTO ";
            } else {
                $query = strtoupper($parameters) . " INTO ";
            }

            $query .= $to_table . " (" . substr($to_cols, 2) . ") SELECT " . substr($from_cols, 2) . " FROM $from_table";
            if (!is_null($where)) {
                if (strtoupper(substr($where, 0, 6)) == 'WHERE ') $where = substr($where, 6);
                $query .= " WHERE $where";
            }

            if (is_array($parameters)) {
                $query .= ' ON DUPLICATE KEY UPDATE ';
                reset($parameters);
                while (list($column, $value) = each($parameters)) {
                    if (is_null($value)) {
                            $query .= $column . ' = NULL, ';
                    } else {
                        switch ((string)$value) {
                            case 'now()':
                            case 'null':
                            case 'NULL':
                                $query .= $column .= ' = ' . $value . ', ';
                                break;
                            default:
                                $query .= $column . ' = \'' . $this->input($value) . '\', ';
                                break;
                        }
                    }
                }
                $query = substr($query, 0, -2);
            }

            $success = $this->query($query, $this->connection, $show_errors);

            $this->replicate_count = $this->affected_rows();

            return $success;

        } else {

            // copia de tablas en distintos servidores (db2 != $this)
            $fields = array();

            // armar query con columnas (from) que esten en las dos tablas y que no existan en $data
            $query = '';
            foreach ($this->replicate_from_cols as $column) {
                if (array_search($column, $this->replicate_to_cols) !== false &&
                            (is_null($data) || !array_key_exists($column, $data))) {
                    $fields[] = $column;
                    $query .= ", $column";
                }
            }
            // agregar valores de $data
            if (is_array($data)) {
                reset($data);
                while (list($column, $value) = each($data)) {
                    $fields[] = $column;
                    $query .= ", $value";
                }
            }

            $query = "SELECT " . substr($query, 2) . " FROM $from_table";
            if (!is_null($where)) {
                if (strtoupper(substr($where, 0, 6)) == 'WHERE ') $where = substr($where, 6);
                $query .= " WHERE $where";
            }

            // ejecutar select (from)
            $success = $this->query($query);

            // el query tiene que tener la misma cantidad de columnas a insertar...
            while ($success && ($row = $this->fetch_array(null, MYSQLI_NUM))) {
                // armar lista de valores a insertar
                $values = array();
                for ($i = 0; $i < sizeof($fields); $i++) {
                    if (is_null($row[$i])) {
                        $values["{$fields[$i]}"] = 'NULL';
                    } else {
                        $values["{$fields[$i]}"] = $row[$i];
                    }
                }
                if (empty($values)) {
                    $this->show_error(-1, 'No hay datos que insertar', "$from_table a $to_table");
                    $success = false;
                    break;
                }

                // ejecutar insercion en tabla to
                if (strtolower(substr($parameters, 0, 7)) == 'replace') {
                    if (!$db2->perform($to_table, $values, $parameters, '', $show_errors)) {
                        $success = false;
                        break;
                    }
                } else {
                    if (!$db2->perform($to_table, $values, 'insert', $parameters, $show_errors)) {
                        $success = false;
                        break;
                    }
                }

                ++$this->replicate_count;
            }

            return $success;
        }
    }


    // devuelve un result set con el nombre de las tablas de la actual base de datos seleccionada
    function tables()
    {
        return $this->query("SHOW TABLES");
    }


    // devuelve un result set las columnas de la tabla indicada  de la actual base de datos seleccionada
    function columns($table)
    {
        return $this->query("SHOW COLUMNS FROM $table");
    }


    public function seek($rownum = 0, $db_query = null)
    {
        return @mysqli_data_seek(($db_query ? $db_query : $this->result), $rownum);
    }


    // $resulttype = MYSQLI_ASSOC, MYSQLI_NUM, MYSQLI_BOTH
    public function fetch_array($db_query = null, $resulttype = MYSQLI_BOTH, $rownum = false)
    {
        if ($rownum !== false && !$this->seek($rownum))
            return false;

        return @mysqli_fetch_array(($db_query ? $db_query : $this->result), $resulttype);
    }


    function fetch_array_assoc($db_query = NULL, $rownum = false)
    {
        if ($rownum !== false && !$this->seek($rownum))
            return false;

    	return @mysqli_fetch_assoc($db_query ? $db_query : $this->result);
    }


    public function fetch($db_query = null, $rownum = false)
    {
        if ($rownum !== false && !$this->seek($rownum))
            return false;

        return @mysqli_fetch_object($db_query ? $db_query : $this->result);
    }


    public function fetch_all_array(&$row_array, $db_query = null, $resulttype = MYSQLI_BOTH, $column = false)
    {
        $row_array = array();
        while ($row = @mysqli_fetch_array(($db_query ? $db_query : $this->result), $resulttype)) {
            $row_array[] = ($column === false ? $row : $row[$column]);
        }
        return sizeof($row_array);
    }


    public function fetch_all(&$row_array, $db_query = null)
    {
        $row_array = array();
        while ($row = @mysqli_fetch_object($db_query ? $db_query : $this->result)) {
            $row_array[] = $row;
        }
        return sizeof($row_array);
    }



    public function free_result($db_query = null)
    {
        $success = @mysqli_free_result($db_query ? $db_query : $this->result);
        if (!$db_query || $db_query == $this->result) $this->result = null;
        return $success;
    }


    public function num_fields($db_query = null)
    {
        return @mysqli_num_fields($db_query ? $db_query : $this->result);
    }


    public function num_rows($db_query = null)
    {
        return @mysqli_num_rows($db_query ? $db_query : $this->result);
    }


    public function affected_rows($db_query = null)
    {
        return @mysqli_affected_rows($this->connection);
    }


    public function insert_id()
    {
        return @mysqli_insert_id($this->connection);
    }


    public function set_autocommit($mode = true)
    {
        if ($this->debug)
            $this->show_query('SET AUTOCOMMIT = ' . ($mode ? '1' : '0'));

        $this->savepoint = null;
        $this->_autocommit = $mode;

        return @mysqli_autocommit($this->connection, $mode);
    }


    // ojo, el begin transaction siempre hace un COMMIT de la anterior si la hubiera
    public function begin_transaction()
    {
        $this->savepoint = null;

        if ($this->_autocommit && !@mysqli_autocommit($this->connection, false))
            return false;

        return $this->query("START TRANSACTION");
    }


    public function commit()
    {
        if ($this->debug)
            $this->show_query('COMMIT');

        $this->savepoint = null;

        if (!@mysqli_commit($this->connection))
            return false;

        if ($this->_autocommit)
            return @mysqli_autocommit($this->connection, true);

        return true;
    }


    // $id = null   =>  rollback
    // $id = false  =>  rollback
    // $id = true   =>  rollback to savepoint $this->savepoint
    // $id = "id"   =>  rollback to $id
    public function rollback($id = null)
    {
        if ($id) {
            if ($id === true) $id = $this->savepoint;
            return $this->query("ROLLBACK TO SAVEPOINT $id");
        }

        if ($this->debug)
            $this->show_query('ROLLBACK');

        $this->savepoint = null;

        if (!@mysqli_rollback($this->connection))
            return false;

        if ($this->_autocommit)
            return @mysqli_autocommit($this->connection, true);

        return true;
    }


    public function savepoint($id = null)
    {
        if (is_null($id)) {
            static $savepoint_count = 0;
            $id = 'savepoint_' . ++$savepoint_count;
        }

        $this->savepoint = $id;

        return ($this->query("SAVEPOINT $id") ? $id : false);
    }


    public function get_lock($id, $timeout=1)
    {
        if ($this->query("SELECT GET_LOCK('$id', $timeout)") &&
                ($row = $this->fetch_array(null, MYSQLI_NUM)))
            return ($row[0] == '1');

        return null;
    }

    public function release_lock($id, $blind=true)
    {
        if ($blind) {
            $this->query("DO RELEASE_LOCK('$id')");
            return true;
        }

        if ($this->query("SELECT RELEASE_LOCK('$id')") &&
                ($row = $this->fetch_array(null, MYSQLI_NUM)))
            return ($row[0] == '1');

        return null;
    }


//
// Functiones estaticas de acceso publico, no se necesita instanciar la clase
//

    public static function output($string)
    {
        return htmlspecialchars($string);
    }


    public static function input($string)
    {
        return addslashes($string);
    }


    public static function prepare_input($string)
    {
        if (is_string($string)) {
            return trim(stripslashes($string));
        } elseif (is_array($string)) {
            reset($string);
            while (list($key, $value) = each($string)) {
                $string[$key] = static::prepare_input($value);
            }
            return $string;
        } else {
            return $string;
        }
    }


    // instancia un objeto de la clase y lo conecta a la base, si ya se habia instalaciado/conectado devuelve dicho objeto
    // (si $db === null reinicializa el array de conexiones ya hechas)
    public static function get($db = '', $host = '', $user = '', $password = '')
    {
        static $shared_connections = array();

        if (is_null($db)) {
            $shared_connections = array();
            return;
        }

        $key = "$db:$host:$user";

        if (isset($shared_connections["$key"])) {
            if (!$shared_connections["$key"]->connect_if_not($db, $host, $user, $password))
                return false;
        } else {
            $class = __CLASS__;
            $obj = new $class();
            if ($obj->connect($db, $host, $user, $password))
                $shared_connections["$key"] = $obj;
            else
                return false;
        }

        return $shared_connections["$key"];
    }
}


?>

