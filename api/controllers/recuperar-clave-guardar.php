<?php

require_once '../class/conexion.php';
require_once '../requires/global.php';
require_once '../requires/funciones.php';

$data = json_decode(file_get_contents("php://input"));

$_DB = new Conection;

$privatekey = "6LfcFQYTAAAAAOMpUzfTlwEi9ILaEEGTTc1kXmEw";

//Genera la clave para la activacion
$clave = strtoupper(GenerarClaveTicket(5));
$clave_activa = GenerarClaveTicket(15);

//Valida que el usuario se haya registrado previamente
$validaSql = $_DB->prepare("SELECT a.usuario_id, b.estado, b.intentos
									FROM
										registro a
									INNER JOIN usuario b ON (a.mandante = b.mandante AND a.usuario_id = b.usuario_id)
									WHERE a.mandante =:mandante
									AND ucase(a.email) =:email
									AND b.estado_esp =:estado_esp");

$validaSql->execute(array(':mandante' => $mandante, ':estado_esp' => 'A', ':email' => strtoupper($data->email)));

if ($validaSql->rowCount() > 0) {
    $valida_RS = $validaSql->fetchAll(PDO::FETCH_ASSOC);
    $strSql = $_DB->prepare("UPDATE usuario SET clave=aes_encrypt(:Clave, :clave_encrypt),estado='A',intentos=0 WHERE mandante=:mandante AND usuario_id=:usuario_id");

    $strSql->execute(array('clave' => $clave, ':clave_encrypt' => $clave_encrypt, ':mandante' => $mandante, ':usuario_id' => $valida[0]['usuario_id']));

    if ($strSql->rowCount() > 0) {

        //Arma el mensaje completo
        $mensaje_txt = "A continuacion encontrara sus nuevas credenciales de acceso al sistema:" . "\r\n\r\n";
        $mensaje_txt = $mensaje_txt . "Usuario: " . $email . "\r\n";
        $mensaje_txt = $mensaje_txt . "Clave: " . $clave . "\r\n\r\n";
        $mensaje_txt = $mensaje_txt . "Recuerde usar las mismas minusculas o mayusculas al ingresar la nueva clave que le informamos en este mensaje. Lo invitamos ademas a cambiar su clave una vez ingrese nuevamente, y tenga presente que por su seguridad, es importante cambiarla con regularidad." . "\r\n\r\n";
        $mensaje_txt = $mensaje_txt . "http://www.winplay.co" . "\r\n\r\n";
        $mensaje_txt = $mensaje_txt . "Atentamente," . "\r\n\r\n";
        $mensaje_txt = $mensaje_txt . "Servicio al Cliente Winplay";

        //Arma la canecera del mensaje de correo
        $cabeceras .= 'From: Winplay <online@winplay.co>' . "\r\n" . 'X-Mailer: PHP/' . phpversion();

        //Destinatarios
        $destinatarios = $email;

        //Envia el mensaje de correo
        mail($destinatarios, 'Informacion recuperacion clave Winplay', CambioCaracter($mensaje_txt), $cabeceras);

        echo 'Se ha enviado a su correo las indicaciones para reestablecer su contrase�a.';
    }
} else {
    echo "El usuario no se encuentra regsitrado.";
}


?>

