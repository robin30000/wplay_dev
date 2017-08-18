<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token , Authorization');

$data = json_decode(file_get_contents("php://input"));

if (isset($data->metodo)) {
    switch ($data->metodo) {
        case 'getSession':
            require_once '../class/usuario.php';
            $user = new Usuario;
            $user->getSession();
            break;

        case 'login':
            require_once '../class/usuario.php';
            $user = new Usuario;
            $user->login($data->user);
            break;

        case 'logout':
            require_once '../class/usuario.php';
            $user = new Usuario;
            $user->logout($data->user_id);
            break;

        case 'perfil':
            require_once '../class/usuario.php';
            $user = new Usuario;
            $user->perfilUser($data->user_id, $data->token);
            break;

        case 'actualiza_perfil_usuario':
            require_once '../class/usuario.php';
            $user = new Usuario;
            $user->actualiza_perfil_usuario($data->data);
            break;

        case 'password':
            require_once '../class/usuario.php';
            $user = new Usuario;
            $user->updatePassUser($data->data);
            break;

        case 'contact':
            require_once '../class/usuario.php';
            $user = new Usuario;
            $user->contact($data->data);
            break;

        case 'recuperarClave':
            require_once '../class/usuario.php';
            $user = new Usuario;
            $user->recuperarClave($data->data);
            break;

        case 'nuevoPass':
            require_once '../class/usuario.php';
            $user = new Usuario;
            $user->nuevoPass($data->user);
            break;

        case 'autoExclusionApuestas':
            require_once '../class/usuario.php';
            $user = new Usuario;
            $user->usuarioAutoExclusionApuestas($data->user);
            break;

        case 'autoExclusionLimiteDeposito':
            require_once '../class/usuario.php';
            $user = new Usuario;
            $user->usuarioAutoExclusionDeposito($data->user);
            break;

        case 'autoExclusionLimiteDepositoPeticion':
            require_once '../class/usuario.php';
            $user = new Usuario;
            $user->autoExclusionLimiteDepositoPeticion($data->user);
            break;

        case 'autoExclusionTiempo':
            require_once '../class/usuario.php';
            $user = new Usuario;
            $user->usuarioAutoExclusionTiempo($data->user);
            break;

        case 'verLimiteApuesta':
            require_once '../class/usuario.php';
            $user = new Usuario;
            $user->verLimiteApuesta($data->user_id, $data->token);
            break;

        case 'verLimiteTiempo':
            require_once '../class/usuario.php';
            $user = new Usuario;
            $user->verLimiteTiempo($data->user_id, $data->token);
            break;

        case 'verLimiteDeposito':
            require_once '../class/usuario.php';
            $user = new Usuario;
            $user->verLimiteDeposito($data->user_id, $data->token);
            break;

        case 'preguntas_seguridad_estado':
            require_once '../class/usuario.php';
            $user = new Usuario;
            $user->preguntas_seguridad_estado($data->user_id, $data->token);
            break;

        case 'retiros_cuentas_bancarias':
            require_once '../class/usuario.php';
            $user = new Usuario;
            $user->consultaSaldoRetiros($data->user_id, $data->token);
            break;

        case 'pregunta_nueva_contrasena':
            require_once '../class/usuario.php';
            $user = new Usuario;
            $user->pregunta_nueva_contrasena($data->user_id);
            break;

        case 'cancelarCuenta':
            require_once '../class/usuario.php';
            $user = new Usuario;
            $user->cancelarCuenta($data->user);
            break;

        case 'recordarContrasena':
            require_once '../class/usuario.php';
            $user = new Usuario;
            $user->recordarContrasena($data->user);
            break;

        case 'saldo':
            require_once '../class/consultasGenerales.php';
            $consulta = new ConsultasGenerales();
            $consulta->saldo_usuario($data->user_id, $data->token);
            break;

        case 'datosUsuarioTuCompra':
            require_once '../class/usuario.php';
            $consulta = new Usuario();
            $consulta->datosUsuarioTuCompra($data->user_id, $data->token);
            break;

        case 'trabajeConNosotros':
            require_once '../class/usuario.php';
            $consulta = new Usuario();
            $consulta->trabajeConNosotros($data->user);
            break;

        case 'guarda_preguntas_seguridad':
            require_once '../class/usuario.php';
            $consulta = new Usuario();
            $consulta->guarda_preguntas_seguridad($data->user);
            break;

        case 'limite_dias':
            require_once '../class/usuario.php';
            $consulta = new Usuario();
            $consulta->limite_dias($data->user_id, $data->token);
            break;

        case 'limite_tiempo':
            require_once '../class/usuario.php';
            $consulta = new Usuario();
            $consulta->limite_tiempo($data->user_id, $data->token);
            break;

        case 'limite_deposito':
            require_once '../class/usuario.php';
            $consulta = new Usuario();
            $consulta->limite_deposito($data->user_id, $data->token);
            break;

        case 'valida_email_preregistro':
            require_once '../class/usuario.php';
            $consulta = new Usuario();
            $consulta->valida_email_preregistro($data->email);
            break;

        case 'pasarelaPago':
            require_once '../class/usuario.php';
            $consulta = new Usuario();
            $consulta->pasarelaPago($data->user_id, $data->token, $data->valor, $data->origen);
            break;

        case 'changeStatusDraft':
            require_once '../class/usuario.php';
            $consulta = new Usuario();
            $consulta->changeStatusDraft($data->user_id, $data->token, $data->id_factura);
            break;

        case 'cancelaPeticionPasarela':
            require_once '../class/usuario.php';
            $consulta = new Usuario();
            $consulta->cancelaPeticionPasarela($data->user_id, $data->token, $data->id_factura);
            break;

        case 'restorePassword':
            require_once '../class/usuario.php';
            $user = new Usuario;
            $user->restorePassword($data->data);
            break;

        case 'updateFecha':
            require_once '../class/consultasGenerales.php';
            $user = new ConsultasGenerales();
            $user->updateFecha($data->data);
            break;

        default:
            echo 'ninguna opción valida.';
            break;
    }
} else {
    echo 'ninguna opción valida.';
}
