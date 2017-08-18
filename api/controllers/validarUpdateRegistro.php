<?php

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

    if (strlen($data->pNom) == 0) {
        $m = 'El primer nombre del usuario es obligatorio.';
        $s = 1;
        jsonReturn($s, $m);
        return false;
    }

    if (strlen($data->pApe) == 0) {
        $m = 'El primer apellido del usuario es obligatorio.';
        $s = 2;
        jsonReturn($s, $m);
        return false;
    }

    if (!preg_match("/^[a-zA-Z áéíóúAÉÍÓÚÑñ]+$/", $data->pNom) || !preg_match("/^[a-zA-Z áéíóúAÉÍÓÚÑñ]+$/", $data->pApe)) {
        $m = 'En los campos de nombre y apellido solo pueden ingresarse letras.';
        $s = 3;
        jsonReturn($s, $m);
        return false;
    }

    if (empty($data->tDoc)) {
        $m = 'El tipo de documento es invalido.';
        $s = 4;
        jsonReturn($s, $m);
        return false;
    }

    if (strlen($data->numDoc) < 3) {
        $m = 'El numero de Identificación es invalido.';
        $s = 5;
        jsonReturn($s, $m);
        return false;
    }

    if (strlen($data->numDoc) > 15) {
        $m = 'El numero de Identificación es invalido.';
        $s = 5;
        jsonReturn($s, $m);
        return false;
    }

    if (!is_numeric($data->numDoc)) {
        $m = 'El numero de Identificación solo son permitidos caracteres numéricos.';
        $s = 6;
        jsonReturn($s, $m);
        return false;
    }

    if (strlen($data->fExp) != 10) {
        $m = 'El formato de la fecha de expedición del documento de identidad no es valido.';
        $s = 7;
        jsonReturn($s, $m);
        return false;
    }

    $fecha = explode("-", $data->fExp);
    $anio = strlen($fecha[0]);
    $mes = strlen($fecha[1]);
    $dia = strlen($fecha[2]);

    if ($anio != 4 || $mes != 2 || $dia != 2) {
        $m = 'El formato de la fecha de expedición del documento de identidad no es valido.';
        $s = 8;
        jsonReturn($s, $m);
        return false;
    }

    if (!(preg_match('/^([0-9]*)$/', $data->ciud_exp->ciudad_id))) {
        $m = 'La ciudad de expedición es invalida.';
        $s = 9;
        jsonReturn($s, $m);
        return false;
    }

    if ($data->tDoc == 2) {
        if ($data->fVence < $data->fExp) {
            $m = 'La fecha de vencimiento del documento de extranjera no puede ser anterior a la fecha de expedición';
            $s = 10;
            jsonReturn($s, $m);
            return false;
        }
    }

    if (!isset($data->fnace)) {
        $m = 'La fecha de naciemiento es obligatoria';
        $s = 11;
        jsonReturn($s, $m);
        return false;
    }

    if (!isset($data->fExp)) {
        $m = 'La fecha de expedicion es obligatoria';
        $s = 12;
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

    if ($start_ts < strtotime($data->fExp)) {
        $m = 'La fecha de expedicion del documento no puede ser mayor que la actual.';
        $s = 14;
        jsonReturn($s, $m);
        return false;
    }

    if (strtotime($data->fnace) > strtotime($data->fExp)) {
        $m = 'La fecha de expedición del documento de identidad no puede ser anterior a la fecha de nacimiento.';
        $s = 15;
        jsonReturn($s, $m);
        return false;
    }

    if (strlen($data->clave_reg) < 6) {
        $m = 'La contraseña debe tener al menos 6 caracteres.';
        $s = 19;
        jsonReturn($s, $m);
        return false;
    }
    if ($data->clave_reg != $data->confirma_clave_reg) {
        $m = 'La contraseña no coincide.';
        $s = 20;
        jsonReturn($s, $m);
        return false;
    }

    if (!(isset($data->movil))) {
        $m = 'El teléfono móvil es obligatorio.';
        $s = 21;
        jsonReturn($s, $m);
        return false;
    }

    if (!is_numeric($data->movil)) {
        $m = 'El teléfono móvil tiene campos invadidos.';
        $s = 21;
        jsonReturn($s, $m);
        return false;
    }

    if ($data->fijo != '') {
        if (!is_numeric($data->fijo)) {
            $m = 'El teléfono tiene campos invadidos.';
            $s = 21;
            jsonReturn($s, $m);
            return false;
        }
    }

    if (!$data->t_and_c) {
        $m = 'Debes aceptar los términos y condiciones para poder registrarse.';
        $s = 22;
        jsonReturn($s, $m);
        return false;
    }
    return true;
}

if (valida_preregistro($data)) {
    require_once("../class/consultasGenerales.php");
    $pre = new ConsultasGenerales();
    $pre->updateregistro($datos->usuario_id, $datos->token, $data);
}