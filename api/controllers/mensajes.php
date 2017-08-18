<?php
header('Access-Control-Allow-Origin: *');
    header("Access-Control-Allow-Credentials: true");
    header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
    header('Access-Control-Max-Age: 1000');
    header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token , Authorization');

$data = json_decode(file_get_contents("php://input"));
if (isset($data->metodo)) {
    switch ($data->metodo) {

        case 'traer_mensajes':
          require_once '../class/usuario.php';
          $datos = new Usuario();
          $datos->cargaMensajes($data->user_id, $data->token);
          break;

        case 'mensajes_nuevos':
            require_once '../class/usuario.php';
            $datos = new Usuario();
            $datos->messages($data->user_id, $data->token);
            break;

        case 'leer_mensaje':
            require_once '../class/usuario.php';
            $datos = new Usuario();
            $datos->Leer_mensajes($data->user_id, $data->token, $data->message_id);
            break;

        default:
          echo 'ninguna opción valida.';
          break;
    }

} else {
    echo 'ninguna opción valida.';
}
