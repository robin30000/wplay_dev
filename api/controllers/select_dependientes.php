<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token , Authorization');

$data = json_decode(file_get_contents("php://input"));

if (isset($data->metodo)) {

    switch ($data->metodo) {

        case 'departamentos':
            require_once '../class/consultasGenerales.php';
            $consulta = new ConsultasGenerales;
            $consulta->departamentos($data->pais_id);
            break;
        case 'ciudad':
            require_once '../class/consultasGenerales.php';
            $consulta = new ConsultasGenerales;
            $consulta->ciudad($data->idDepartamento);
            break;
        case 'mostrarCiudadesCodigos':
            require_once '../class/consultasGenerales.php';
            $consulta = new ConsultasGenerales;
            $consulta->ciudadesCodigos($data->idDepartamento);
            break;
        default:
            echo "<script>alert('Ninguna opción es valida')</script>";
            break;
    }
}
