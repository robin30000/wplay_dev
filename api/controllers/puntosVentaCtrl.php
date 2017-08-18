<?php

/**
 * Created by PhpStorm.
 * User: robin
 * Date: 7/06/17
 * Time: 09:54 AM
 */

header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token , Authorization');

$data = json_decode(file_get_contents("php://input"));

if (isset($data->metodo)) {
    switch ($data->metodo) {
        case 'getPoints':
            require_once '../class/consultasGenerales.php';
            $user = new ConsultasGenerales();
            $user->getPoints();
            break;
        default:
            echo 'ninguna opción valida.';
            break;
    }
} else {
    echo 'ninguna opción valida.';
    exit();
}
