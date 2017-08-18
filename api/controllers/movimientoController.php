<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token , Authorization');

$data = json_decode(file_get_contents("php://input"));

if (isset($data->opcion)) {

    require_once '../class/movimientos.php';
    $movimiento = new Movimiento();
    $movimiento->movimientos($data->opcion, $data->user, $data->token);

} else {
    echo 'ninguna opción valida.';
}
