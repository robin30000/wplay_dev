<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token , Authorization');

$data = json_decode(file_get_contents("php://input"));

if (isset($data->metodo)) {
    switch ($data->metodo) {
        case 'cargaRanking':
            require_once '../class/olimpiadas.php';
            $user = new olimpiadas;
            $user->cargaRanking();
            break;
        /**
             * controlador para el login del usuario
             */
        case 'rankingUsuario':
            require_once '../class/olimpiadas.php';
            $user = new olimpiadas;
            $user->rankingUsuario($data->user);
            break;

        case 'ganadoresxdia':
            require_once '../class/olimpiadas.php';
            $user = new olimpiadas;
            $user->ganadoresxdia($data->dia);
            break;

        case 'rankingHoy':
            require_once '../class/olimpiadas.php';
            $user = new olimpiadas;
            $user->rankingHoy($data->user);
            break;

        default:
            echo 'ninguna opción valida.';
            break;
    }
} else {
    echo 'ninguna opción valida.';
}
