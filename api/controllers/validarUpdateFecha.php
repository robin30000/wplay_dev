<?php
/**
 * Created by PhpStorm.
 * User: robin
 * Date: 28/07/17
 * Time: 11:44 AM
 */

header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token , Authorization');

require_once '../requires/funciones.php';

$datos = json_decode(file_get_contents("php://input"));
$data = $datos->data;

function valida_preregistro($data)
{

    if (!isset($data->fnace)) {
        $m = 'La fecha de nacimiento es obligatoria';
        $s = 11;
        jsonReturn($s, $m);
        return false;
    }

    $hoy = getdate();
    $fecha = $hoy['year'] . '-' . $hoy['mon'] . '-' . $hoy['mday'];
    $start_ts = strtotime($fecha);
    $end_ts = strtotime($data->fnace);
    $diff = $start_ts - $end_ts;
    $dias = $diff / 86400;
    $anios = round($dias / 365);

    if ($anios < 18) {
        $m = 'Debe ser mayor de edad para poder registrarse en wplay.co.';
        $s = 13;
        jsonReturn($s, $m);
        return false;
    }
    return true;
}

if (valida_preregistro($data)) {
    require_once("../class/consultasGenerales.php");
    $pre = new ConsultasGenerales();
    $pre->updateFecha($datos->usuario_id, $datos->token, $data);
}