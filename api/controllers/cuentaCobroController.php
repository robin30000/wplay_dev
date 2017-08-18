<?php

header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token , Authorization');

$data = json_decode(file_get_contents("php://input"));
switch ($data->opcion) {
    case 'cuentaCobro':
        require_once '../class/cuentaCobro.php';
        $movimiento = new cuentaCobro();
        $movimiento->cuenta($data->user, $data->token);
        break;
    case 'cuentaCobroAll':
        require_once '../class/cuentaCobro.php';
        $movimiento = new cuentaCobro();
        $movimiento->accountAll($data->user, $data->token);
        break;
    case 'cancelDocCuentaCobro':
        require_once '../class/cuentaCobro.php';
        $movimiento = new cuentaCobro();
        $movimiento->cancelDocCuentaCobro($data->user, $data->token, $data->cuenta_id);
        break;
}

