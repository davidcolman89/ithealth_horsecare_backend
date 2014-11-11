<?php
class FormValidacion{
 
    public function __construct(){

    }

    public static function validarFileImage($fileroute) {

        $ext = pathinfo($fileroute, PATHINFO_EXTENSION);
        if($ext != "jpg" && $ext != "jpeg" && $ext != "png" && $ext != "JPG" && $ext != "PNG" && $ext != "JPEG") {
            $resultado = FALSE;
        } else {
            $resultado = TRUE;
        }

        return  $resultado;
    }
    
    // VALIDANDO CAMPOS VACIOS
    public static function validarVacios($campo){
        if(!empty($campo) || $campo != "" or $campo != NULL){
            $resultado = TRUE;
        }else{
            $resultado = FALSE;
        }
        return $resultado;
    }
    
    // VALIDANDO CORREOS
    public static function validarCorreo($correo){
        filter_var($correo, FILTER_VALIDATE_EMAIL) ? $resultado = TRUE : $resultado = FALSE;
        return $resultado;
    }
		
		// VALIDANDO 2 CORREOS
    public static function validarDosCorreos($correo,$correo2){
        	
        filter_var($correo, FILTER_VALIDATE_EMAIL) ? $resultado = TRUE : $resultado = FALSE;
				if($resultado) filter_var($correo2, FILTER_VALIDATE_EMAIL) ? $resultado = TRUE : $resultado = FALSE;
				if($resultado) ($correo === $correo2) ? $resultado = TRUE : $resultado = FALSE;
				
        return $resultado;
    }
    
    // VALIDANDO ENTEROS
    // CERO Y MENOS CERO NO SON ENTEROS VALIDOS
    public static function validarEnteros($entero, $min, $max){
    
        $rango = array (
            "options" => array
            ( "min_range" => $min, "max_range" => $max)
        );
 
        filter_var($entero, FILTER_VALIDATE_INT, $rango) ? $resultado = TRUE : $resultado = FALSE;
        return $resultado;
    }
 
    // VALIDANDO BOLEANOS
    // RETORNA TRUE PARA 1, TRUE, ON, YES
    // EN OTROS CASOS RETORNARA FALSE
    public static function validarBoleanos($boleano){
        filter_var($boleano, FILTER_VALIDATE_BOOLEAN) ? $resultado = TRUE : $resultado = FALSE;
        return $resultado;
    }
 
    // VALIDANDO NUMEROS DE PUNTO FLOTANTE (DECIMALES)
    public static function validarFlotante($flotante){
    
        $separador = array('options' => array('decimal' => ','));
        
        // si se quiere usar un numero con miles y punto flotante (1.238,32) se usa esta condicion
        // (!filter_var($flotante, FILTER_VALIDATE_FLOAT,  array('options' => array('decimal' => ','), 'flags' => FILTER_FLAG_ALLOW_THOUSAND)))
        filter_var($flotante, FILTER_VALIDATE_FLOAT, $separador) ? $resultado = TRUE : $resultado = FALSE;
        return $resultado;
    }
    
    // VALIDANDO UNA IP
    // en version se le debe pasar como parametro el tipo de ip FILTER_FLAG_IPV4 - FILTER_FLAG_IPV6
    public static function validarIP($ip, $version){
        filter_var($ip, FILTER_VALIDATE_IP, $version) ? $resultado = TRUE : $resultado = FALSE;
        return $resultado;
    }
    
    // VALIDANDO CON EXPRESIONES REGULARES
    // VERIFICA QUE EL CONTENIDO CONCUERDE CON LA EXPRESION REGULAR
    /*
        $expresion = '/^M(.*)/';
        si se le pasa una cadena de texto que comience con M el resultado sera positivo
    */
    public static function ValidarExpRegular($contenido, $expresion){
        
        // para pasarle las opciones al filtro
        $opcion = array("options" => array("regexp" => $expresion));
 
        filter_var($contenido, FILTER_VALIDATE_REGEXP, $opcion) ? $resultado = TRUE : $resultado = FALSE;
        return $resultado;
    }
    
    // VALIDANDO UNA URL
    /* 
        $opcion =
            FILTER_FLAG_SCHEME_REQUIRED  - ejemplo: http://pagina
            FILTER_FLAG_SCHEME_REQUIRED - ejemplo: http://www.pagina.com
            FILTER_FLAG_PATH_REQUIRED - ejemplo: www.example.com/example1/test2/
            FILTER_FLAG_QUERY_REQUIRED  - ejmemplo: pagina.php?nombre=Juan&edad=37
    */
    public static function validarURL($url, $opcion){
        filter_var($url, FILTER_VALIDATE_URL, $opcion) ? $resultado = TRUE : $resultado = FALSE;
        return $resultado;
    }
    
    // VALIDANDO QUE UN CAMPO EXISTA SI OTRO ESTA DEFINIDO Y CON UN VALOR
    /*
        si el campo 1 existe y tiene un valor, validar el numero 2
        $validar tendra el filtro que se quiere aplicar
        $campo2 es el campo que se quiere validar
    */
    public static function validarDosCampos($campo1, $valor, $validar, $campo2){
        if(isset($campo1) && $campo1 == $valor){
            switch($validar){
                case 'FILTER_VALIDATE_BOOLEAN':
                    filter_var($campo2, FILTER_VALIDATE_BOOLEAN) ? $resultado = TRUE : $resultado = FALSE;
                break;
                case 'FILTER_VALIDATE_EMAIL':
                    filter_var($campo2, FILTER_VALIDATE_EMAIL) ? $resultado = TRUE : $resultado = FALSE;
                break;
                case 'FILTER_VALIDATE_FLOAT':
                    $separador = array('options' => array('decimal' => ','));
                    filter_var($campo2, FILTER_VALIDATE_FLOAT, $separador) ? $resultado = TRUE : $resultado = FALSE;
                break;
                case 'FILTER_VALIDATE_INT':
                    filter_var($campo2, FILTER_VALIDATE_INT) ? $resultado = TRUE : $resultado = FALSE;
                break;
                case 'FILTER_VALIDATE_IP':
                    filter_var($campo2, FILTER_VALIDATE_IP) ? $resultado = TRUE : $resultado = FALSE;
                break;
                case 'FILTER_VALIDATE_REGEXP':
                    filter_var($contenido, FILTER_VALIDATE_REGEXP, array("options"=>array("regexp"=>"/^M(.*)/"))) ? $resultado = TRUE : $resultado = FALSE;
                break;
                case 'FILTER_VALIDATE_URL':
                    filter_var($url, FILTER_VALIDATE_URL) ? $resultado = TRUE : $resultado = FALSE;
                break;
                default:
                    $resultado = FALSE;
                break;
            }
        }else{
            $resultado = FALSE;
        }
        return $resultado;
    }   
}
?>