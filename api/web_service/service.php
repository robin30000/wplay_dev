<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token , Authorization');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if ($_POST['authenticity_token'] === 'LlAhHRfyyt5HS4ViwkSWBULAoL3ccgxjXBKR2ux4cu2atS8lL7O2boFyA68oDkP1GulHW0HKjlngVKDRD0epdg==') {
        $name            = explode(' ', $_POST['name']);
        $lastname        = explode(' ', $_POST['lastname']);
        $datos->pNom     = $name[0];
        $datos->sNom     = $name[1];
        $datos->sApe     = $lastname[1];
        $datos->pApe     = $lastname[0];
        $datos->mail     = $_POST['email'];
        $datos->movil    = $_POST['phone'];
        $datos->numDoc   = $_POST['identification'];
        $datos->oculto   = 'I';
        $datos->segmento = $_POST['segmento'];;
        require_once '../class/consultasGenerales.php';
        $insert = new ConsultasGenerales();
        $insert->preRegistroCortoCreativo($datos);
    }else{
        echo 'error';
    }
}
