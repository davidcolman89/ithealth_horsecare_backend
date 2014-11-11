<?php
header("Access-Control-Allow-Origin: *");
include_once("config.php");
include_once(PATH_CLASES . "dbmysql.class.php");
include_once(PATH_CLASES . "formValidacion.class.php");
include_once(PATH_HELPERS . "helpers.php");

$aDatos = $_POST;
$iIdEstudio = $aDatos["id_estudio"];
$sRespuesta = "";
$bGuardar = TRUE;
$oDb = new dbItHealth(DB_NAME);

$aValores = array(
    "doctor" => $aDatos["doctor_nombre"],
    "observacion" => $aDatos["obs"],
    "activo" => 1
);

//$bDoctorNombre = FormValidacion::validarVacios($aValores['doctor']);
$bDoctorNombre = true;

if (!$bDoctorNombre) {
    $sRespuesta = "ERROR:Complete El nombre del doctor";
    $bRespuesta = $bDoctorNombre;
    $bGuardar = FALSE;
}else if (isset($_FILES) && !empty($_FILES)) {//Upload Archivo

    foreach ($_FILES as $file) {
        $origen = $file['tmp_name'];
        $sFileName = $file['name'];
        //$bFileImage = FormValidacion::validarFileImage($file['name']);
        $bFileImage = true;

        if($bFileImage){

            $sAux = "estudio_{$iIdEstudio}";
            $sArchivoNombre = armarNombreArchivo($sAux);
            $destino = PATH_MEDIA . basename($sArchivoNombre);

            if ( move_uploaded_file( $origen, $destino) ) {

                $sExtension = pathinfo($sFileName, PATHINFO_EXTENSION);
                $sTipo = $file['type'];

                $bResult = $oDb->insert("archivos", array(
                    "idEstudio"=>$iIdEstudio,
                    "fechaSistema"=>"NOW()",
                    "nombre"=>$sArchivoNombre,
                    "nombre_original"=>$sFileName,
                    "tipo"=>$sTipo,
                    "extension"=>$sExtension
                ));

                if(!$bResult){
                    $sRespuesta = "ERROR: No se pudo copia en el servidor el archivo";
                    $bRespuesta = FALSE;
                    $bGuardar = FALSE;
                }else{
                    $iIdArchivo = $oDb->insert_id();
                }

            } else {
                $sRespuesta = "ERROR: No se pudo copia en el servidor el archivo";
                $bRespuesta = FALSE;
                $bGuardar = FALSE;
            }

        }else{
            $sRespuesta = "ERROR: El archivo no es una imagen.";
            $bRespuesta = FALSE;
            $bGuardar = FALSE;
        }

    }

} else {
    $sRespuesta = "ERROR: No se recibio el archivo";
    $bRespuesta = FALSE;
    $bGuardar = FALSE;
}



if ($bGuardar) {

    $tabla = "estudios";
    $bResult = $oDb->update($tabla, $aValores, " idEstudio = {$iIdEstudio} ");

    if (!$bResult) {
        $sRespuesta = "ERROR:No pudo insertar el archivo";
        $bRespuesta = FALSE;
    } else {
        $sRespuesta = "Se inserto correctamente el archivo";
        $bRespuesta = TRUE;
    }

}

$aRespuesta = array(
    "sr" => $sRespuesta,
    "br" => $bRespuesta
);

echo json_encode((object)$aRespuesta);
?>