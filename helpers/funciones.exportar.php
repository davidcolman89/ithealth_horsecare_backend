<?php

function insertarOfflineHistorico($parametros){
    $oDb = new dbItHealth(DB_NAME);
    $oDb->insert('offline_historico',$parametros);
    return $oDb->insert_id();
}

function insertarDuenios($clientes){

    $array = array();

    foreach($clientes as $key=>$json){

        foreach(json_decode($json) as $key2=>$cliente){

            $id = insertarDuenio(array(
                'fechaSistema'=>'NOW()',
                'nombre'=>$cliente->nombre,
                'idLugar'=>$cliente->id_lugar,
                'offline'=>1
            ));

            $array[$cliente->id] = $id;

        }

    }

    return $array;

}

function insertarDuenio($parametros){
    $oDb = new dbItHealth(DB_NAME);
    $oDb->insert('duenios',$parametros);
    return $oDb->insert_id();
}

function insertarEquinos($equinos,$relaciones){

    $array = array();

    foreach($equinos as $key=>$json){

        foreach(json_decode($json) as $key2=>$equino){

            //Chequea si es un nuevo cliente el duenio
            if(array_key_exists($equino->id_duenio, $relaciones)){
                $idDuenio = $relaciones[$equino->id_duenio];
            }else{
                $idDuenio = $equino->id_duenio;
            }

            $id = insertarEquino(array(
                'fechaSistema'=>'NOW()',
                'nombre'=>$equino->nombre,
                'idDuenio'=>$idDuenio,
                'nacimiento'=>$equino->nacimiento,
                'observacion'=>$equino->obs,
                'offline'=>1
            ));

            $array[$equino->id] = $id;

        }

    }

    return $array;

}

function insertarEquino($parametros){
    $oDb = new dbItHealth(DB_NAME);
    $oDb->insert('equinos',$parametros);
    return $oDb->insert_id();
}


function insertarProblemas($problemas,$relaciones){
    $array = array();

    foreach($problemas as $key=>$json){

        foreach(json_decode($json) as $key2=>$problema){

            //Chequea si es un nuevo equino
            if(array_key_exists($problema->id_equino, $relaciones['equinos'])){
                $idEquino = $relaciones['equinos'][$problema->id_equino];
            }else{
                $idEquino = $problema->id_equino;
            }

            $id = insertarProblema(array(
                'fechaSistema'=>'NOW()',
                'idEquino'=>$idEquino,
                'idCodProblema'=>$problema->id_dolencia,
                'idEstado'=>$problema->id_estado,
                'offline'=>1
            ));

            $array[$problema->id] = $id;

        }

    }

    return $array;
}

function insertarProblema($parametros){

    $oDb = new dbItHealth(DB_NAME);
    $oDb->insert('problemas',$parametros);
    return $oDb->insert_id();
}


function insertarEvoluciones($evoluciones,$relaciones){
    $array = array();

    foreach($evoluciones as $key=>$json){

        foreach(json_decode($json) as $key2=>$evolucion){

            $idProblema = $relaciones['problemas'][$evolucion->id_problema];

            $id = insertarEvolucion(array(
                'idProblema'=>$idProblema,
                'idLugar'=>$evolucion->id_lugar,
                'fecha'=>$evolucion->fecha,
                'fechaSistema'=>'NOW()',
                'observacion'=>$evolucion->obs,
                'offline'=>1
            ));

            $array[$evolucion->id] = $id;

        }

    }

    return $array;
}

function insertarEvolucion($parametros){
    $oDb = new dbItHealth(DB_NAME);
    $oDb->insert('evoluciones',$parametros);
    return $oDb->insert_id();
}

function insertarEstudios($estudios,$relacionesEvoluciones){
    $array = array();

    foreach($estudios as $key=>$json){

        foreach(json_decode($json) as $key2=>$estudio){

            $id = insertarEstudio(array(
                'idEvolucion'=>$relacionesEvoluciones[$estudio->id_evolucion],
                'idCodEstudio'=>$estudio->id_estudio_tipo,
                'fechaSistema'=>'NOW()',
            ));

            $array[$estudio->id] = $id;

        }

    }

    return $array;
}

function insertarEstudio($parametros){
    $oDb = new dbItHealth(DB_NAME);
    $oDb->insert('estudios',$parametros);
    return $oDb->insert_id();
}

function insertarMedicaciones($medicaciones,$relacionesEvoluciones){
    $array = array();

    foreach($medicaciones as $key=>$json){

        foreach(json_decode($json) as $key2=>$medicacion){

            $id = insertarMedicacion(array(
                'idEvolucion'=>$relacionesEvoluciones[$medicacion->id_evolucion],
                'idCodMedicacion'=>$medicacion->id_medicacion_tipo,
                'fechaSistema'=>'NOW()',
            ));

            $array[$medicacion->id] = $id;

        }

    }

    return $array;
}

function insertarMedicacion($parametros){
    $oDb = new dbItHealth(DB_NAME);
    $oDb->insert('medicaciones',$parametros);
    return $oDb->insert_id();
}

function insertarPracticas($practicas,$relacionesEvoluciones){
    $array = array();

    foreach($practicas as $key=>$json){

        foreach(json_decode($json) as $key2=>$practica){

            $id = insertarPractica(array(
                'idEvolucion'=>$relacionesEvoluciones[$practica->id_evolucion],
                'idCodPractica'=>$practica->id_practica_tipo,
                'fechaSistema'=>'NOW()',
            ));

            $array[$practica->id] = $id;

        }

    }

    return $array;
}

function insertarPractica($parametros){
    $oDb = new dbItHealth(DB_NAME);
    $oDb->insert('practicas',$parametros);
    return $oDb->insert_id();
}