<?php
header('Access-Control-Allow-Origin: *');
    header("Access-Control-Allow-Credentials: true");
    header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
    header('Access-Control-Max-Age: 1000');
    header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token , Authorization');
		
	$data = json_decode(file_get_contents("php://input"));

	switch ($data->metodo) {
		case 'bancos':
			require_once '../class/banco.php';
			$consulta = new Banco;
			$consulta->bancos();
			break;
		case 'bankAccount':
			require_once '../class/banco.php';
			$consulta = new Banco;
			$consulta->bankAccount($data->user);
			break;
        case 'getBankAccount':
            require_once '../class/banco.php';
            $user = new Banco;
            $user->getBankAccount($data->user_id, $data->token);
            break;
        case 'preguntas':
            require_once '../class/usuario.php';
            $consulta = new Usuario();
            $consulta->preguntas_seguridad();
            break;
        case 'cliente_cuenta':
            require_once '../class/banco.php';
            $consulta = new Banco();
            $consulta->cliente_cuenta();
            break;
        case 'deleteBank':
            require_once '../class/banco.php';
            $consulta = new Banco();
            $consulta->deleteBank($data->user_id, $data->token, $data->bank_id);
	}
