<?php

require_once 'conexion.php';
date_default_timezone_set('America/Bogota');

class Usuario
{
    private $_DB;

    public function __construct()
    {
        $this->_DB = new Conection;
    }

    /**
     * @param $data
     */
    public function updatePassUser($data)
    {
        require_once '../requires/funciones.php';
        $state = '';
        $valida = $this->valida_token($data->user_id, $data->session_token);
        if (!$valida) {
            $m = 'Alguien ingresó con tu cuenta desde otro equipo vuelve a iniciar session';
            $state = 99;
            jsonReturn($state, $m);
        }

        $this->_DB->beginTransaction();
        require_once "../requires/global.php";

        $query_hash = $this->_DB->prepare("SELECT clave FROM usuario WHERE usuario_id = ?");
        $query_hash->bindParam(1, $data->user_id, PDO::PARAM_INT);
        $query_hash->execute();
        $hash = $query_hash->fetch(PDO::FETCH_OBJ);
        $validar = $this->validar_password($hash->clave, $data->password);

        if (!$validar) {
            $m = 'La contraseña anterior no es correcta';
            $state = 2;
            jsonReturn($state, $m);
        }

        if ($data->confirPass === $data->password) {
            $m = 'La nueva contraseña no puede ser igual a la contraseña actual';
            $state = 3;
            jsonReturn($state, $m);
        }

        $updateUsuario = $this->_DB->prepare("UPDATE usuario SET clave = ?, fecha_clave = NOW() WHERE usuario_id = ?");
        $updateUsuario->bindParam(1, $this->password($data->confirPass), PDO::PARAM_STR);
        $updateUsuario->bindValue(2, $data->user_id, PDO::PARAM_INT);
        $updateUsuario->execute();
        if ($updateUsuario->rowCount() != 1) {
            $m = 'Error actualizando la nueva contraseña';
            $state = 4;
            jsonReturn($state, $m);
        }

        if ($state == '') {
            $this->_DB->commit();
            $result = array('state' => 1, 'msg' => 'La contraseña fue actualizada correctamente.');
        } else {
            $this->_DB->rollBack();
            $result = array('state' => 5, 'msg' => 'Ocurrió un error interno inténtalo nuevamente si el problema persiste reportarlo con un administrador del sistema');
        }
        $this->_DB = null;
        echo json_encode($result);
    }

    /**
     * @param $user_id
     * @param $token
     */
    public function perfilUser($user_id, $token)
    {
        require_once '../requires/funciones.php';
        $valida = $this->valida_token($user_id, $token);
        if (!$valida) {
            $m = 'Alguien ingresó con tu cuenta desde otro equipo vuelve a iniciar session';
            $state = 99;
            jsonReturn($state, $m);
        }
        require_once "../requires/global.php";

        $stmt = $this->_DB->prepare("SELECT
                                                  AES_DECRYPT(p.p_nombre, '" . CLAVE_ENCRYPT . "')   AS pNom,
                                                  AES_DECRYPT(p.s_nombre, '" . CLAVE_ENCRYPT . "')   AS sNom,
                                                  AES_DECRYPT(p.p_apellido, '" . CLAVE_ENCRYPT . "') AS pApe,
                                                  AES_DECRYPT(p.s_apellido, '" . CLAVE_ENCRYPT . "') AS sApe,
                                                  AES_DECRYPT(p.direccion, '" . CLAVE_ENCRYPT . "')  AS dir,
                                                  AES_DECRYPT(r.celular, '" . CLAVE_ENCRYPT . "')    AS movil,
                                                  AES_DECRYPT(p.fijo, '" . CLAVE_ENCRYPT . "')       AS fijo,
                                                  CASE u.referido_id
                                                  WHEN 0
                                                    THEN u.referido_id
                                                  ELSE (SELECT cedula
                                                        FROM registro
                                                        WHERE usuario_id = u.referido_id) END  referido_id,
                                                  pr.tipo_registro,
                                                  r.email,
                                                  p.depart_res,
                                                  p.ciudad_res,
                                                  p.depart_nac,
                                                  p.ciudad_nac,
                                                  p.depart_exp,
                                                  p.ciudad_exp,
                                                  r.cedula AS numDoc,
                                                  r.usuario_id,
                                                  p.pais,
                                                  p.cod_postal,
                                                  p.tipo_doc,
                                                  p.sexo,
                                                  AES_DECRYPT(p.fijo, '" . CLAVE_ENCRYPT . "') AS fijo,
                                                  AES_DECRYPT(r.celular, '" . CLAVE_ENCRYPT . "') AS celular
                                                FROM usuario_otrainfo p
                                                  INNER JOIN registro r ON r.usuario_id = p.usuario_id
                                                  INNER JOIN usuario u ON u.usuario_id = r.usuario_id
                                                  INNER JOIN preregistro pr ON pr.id = r.preregistro_id
                                                WHERE u.usuario_id = :u");
        $stmt->execute(array(':u' => $user_id));
        if ($stmt->rowCount() > 0) {
            $time = $this->_DB->prepare("SELECT tiempo, fecha_fin FROM usuario_log WHERE usuario_id = ? ORDER BY usulog_id DESC LIMIT 2");
            $time->bindParam(1, $user_id, PDO::PARAM_INT);
            $time->execute();
            if ($time->rowCount() <= 1) {
                $timeConection = array('tiempo' => '00:00', 'fecha_fin' => '0000-00-00');
            } else {
                $timeConection = $time->fetchAll(PDO::FETCH_ASSOC);
                $timeConection = $timeConection[1];
            }

            $data = $stmt->fetch(PDO::FETCH_OBJ);
            $result = array('state' => 1, 'dato' => $data, 'time' => $timeConection);
        } else {
            $result = array('state' => 0, 'data' => 'Error no se encontró ningún dato');
        }
        $this->_DB = null;
        echo json_encode($result);
    }

    public function actualiza_perfil_usuario($datos)
    {

        try {
            require_once '../requires/funciones.php';
            $valida = $this->valida_token($datos->usuario_id, $datos->token);
            if (!$valida) {
                $msg = "Alguien ingresó con tu cuenta desde otro equipo vuelve a iniciar session";
                $state = 99;
                jsonReturn($state, $msg);
            }

            require_once '../requires/global.php';
            $this->_DB->beginTransaction();
            $sentencia = $this->_DB->prepare("UPDATE usuario_otrainfo
                                                SET
                                                 sexo = ?,
                                                 depart_res = ?,
                                                 ciudad_res = ?,
                                                 direccion = aes_encrypt(?, '" . CLAVE_ENCRYPT . "'),
                                                 cod_postal = ?,
                                                 fijo = aes_encrypt(?, '" . CLAVE_ENCRYPT . "')
                                                 WHERE usuario_id = ?");
            $sentencia->bindParam(1, $datos->sexo, PDO::PARAM_INT, 1);
            $sentencia->bindParam(2, $datos->depart_res->depto_id, PDO::PARAM_INT, 1);
            $sentencia->bindParam(3, $datos->ciud_res->ciudad_id, PDO::PARAM_INT, 1);
            $sentencia->bindParam(4, $datos->dir, PDO::PARAM_STR);
            $sentencia->bindParam(5, $datos->codP->id_codigo_postal, PDO::PARAM_INT);
            $sentencia->bindParam(6, $datos->fijo, PDO::PARAM_INT);
            $sentencia->bindParam(7, $datos->usuario_id, PDO::PARAM_INT);
            $sentencia->execute();

            $q = $this->_DB->prepare("UPDATE registro
                                                SET
                                                 celular = aes_encrypt(?, '" . CLAVE_ENCRYPT . "')
                                                 WHERE usuario_id = ?");
            $q->bindParam(1, $datos->movil, PDO::PARAM_STR);
            $q->bindParam(2, $datos->usuario_id, PDO::PARAM_INT);
            $q->execute();

            if ($this->_DB->commit()) {
                $res = array('state' => 1, 'msg' => 'Los datos se actualizaron correctamente');
            } else {
                $res = array('state' => 0, 'msg' => 'Ha ocurrido un error interno, intentalo nuevamente');

            }

            echo json_encode($res);
        } catch (PDOException $e) {
            echo 'Exception -> ' . $this->_DB->errorInfo();
        }
    }

    /**
     * @param $user_id
     * @param $token
     * @param $valor
     * @param $origen
     */
    public function pasarelaPago($user_id, $token, $valor, $origen)
    {


        require_once '../requires/funciones.php';

        $valida = $this->valida_token($user_id, $token);
        if (!$valida) {
            $m = 'Alguien ingresó con tu cuenta desde otro equipo vuelve a iniciar session';
            $state = 99;
            jsonReturn($state, $m);
        }

        if (!isset($valor) AND !is_numeric($valor)) {
            $m = "Debe ingresar el valor que desea recargar";
            $state = 2;
            jsonReturn($state, $m);
        }

        if (!isset($origen)) {
            $m = "Debe seleccionar un medio de pago";
            $state = 3;
            jsonReturn($state, $m);
        }

        $valReferido = $this->_DB->prepare("SELECT cedula FROM registro WHERE usuario_id = ?");
        $valReferido->bindParam(1, $_POST['usuario_id'], PDO::PARAM_INT);
        $valReferido->execute();

        $doc = $valReferido->fetch(PDO::FETCH_OBJ);

        $validaExclusion = $this->_DB->prepare("SELECT cc FROM usuario_excluidos WHERE cc = ?");
        $validaExclusion->bindParam(1, $doc->cedula, PDO::PARAM_INT);
        $validaExclusion->execute();

        if ($validaExclusion->rowCount() > 0) {
            $state = 5;
            $m = "Usuario excluido no es posible recargar";
            jsonReturn($state, $m);
        }

        $valor_min = $this->_DB->prepare("SELECT rec_min_pasarela FROM configuracion WHERE config_id = ?");
        $valor_min->bindValue(1, 1, PDO::PARAM_INT);
        $valor_min->execute();
        $val_min = $valor_min->fetch(PDO::FETCH_OBJ);

        if ($val_min->rec_min_pasarela > $valor) {
            $m = "El valor minimo para realizar recagas online es " . number_format($val_min->rec_min_pasarela);
            $state = 4;
            jsonReturn($state, $m);
        }

        ///jsonReturn(13, 'Pronto podras recargas por este medio');

        require_once '../requires/global.php';
        $stmt = $this->_DB->prepare("SELECT
                        CONCAT(AES_DECRYPT(uo.p_nombre, '" . CLAVE_ENCRYPT . "'),' ',AES_DECRYPT(uo.s_nombre, '" . CLAVE_ENCRYPT . "')) nombre,
                        CONCAT(AES_DECRYPT(uo.p_apellido, '" . CLAVE_ENCRYPT . "'),' ',AES_DECRYPT(uo.s_apellido, '" . CLAVE_ENCRYPT . "')) apellido,
                        r.email AS mail,
                        r.cedula AS numDoc,
                        AES_DECRYPT(r.celular, '" . CLAVE_ENCRYPT . "') AS movil,
                        AES_DECRYPT(uo.fijo, '" . CLAVE_ENCRYPT . "') AS fijo,
                        AES_DECRYPT(uo.direccion, '" . CLAVE_ENCRYPT . "') AS dir,
                        c.ciudad_nom,
                        pais.pais_nom
                        FROM usuario_otrainfo uo
                        INNER JOIN registro r ON uo.usuario_id = r.usuario_id
                        INNER JOIN usuario u ON u.usuario_id = r.usuario_id
                        INNER JOIN ciudad c ON c.ciudad_id = uo.ciudad_res
                        INNER JOIN pais ON pais.pais_id = uo.pais
                        WHERE u.usuario_id = :u");
        $stmt->execute(array(':u' => $user_id));

        $datosUsuario = $stmt->fetch(PDO::FETCH_OBJ);
        $configTuCompra = $this->_DB->prepare("SELECT
                                                            url_base_compra,
                                                            llave_terminal,
                                                            usuario,
                                                            cliente
                                                        FROM
                                                            configuracion_pasarela
                                                        WHERE
                                                            id_configuracion = :origen");
        $configTuCompra->execute(array(':origen' => $origen));
        $resConfiguracionTuCompra = $configTuCompra->fetch(PDO::FETCH_OBJ);

        $usuario = new stdClass();
        $usuario->descripcion = 'Deposito para compra de crédito';
        $usuario->configuracionTuCompra = $resConfiguracionTuCompra;
        $usuario->valor = $valor;
        $usuario->iva = 0;

        switch ($origen) {
            case 1:
                $usuario->token_solicitud = md5($resConfiguracionTuCompra->llave_terminal + date('H:i', time()));
                $insert = $this->_DB->prepare("INSERT INTO factura_pasarela(id_usuario, descripcion, token, valor, origen) VALUES (:u, :des, :t, :v, :o)");
                $insert->execute(array(':u' => $user_id, ':des' => 'Recarga de crédito', ':t' => $usuario->token_solicitud, ':v' => $valor, ':o' => $origen));
                $lastId = $this->_DB->lastInsertId();
                $usuario->factura = $lastId;
                break;
            case 2:
                $insert = $this->_DB->prepare("INSERT INTO factura_pasarela(id_usuario, descripcion, valor, origen) VALUES (:u, :des, :v, :o)");
                $insert->execute(array(':u' => $user_id, ':des' => 'Recarga de crédito', ':v' => $valor, ':o' => $origen));
                $lastId = $this->_DB->lastInsertId();

                $usuario->factura = $lastId;
                $usuario->token_solicitud = md5($resConfiguracionTuCompra->llave_terminal . '~' . $resConfiguracionTuCompra->cliente . '~' . $usuario->factura . '~' . $usuario->valor . '~' . 'COP');
                break;

            case 3:
                $insert = $this->_DB->prepare("INSERT INTO factura_pasarela(id_usuario, descripcion, valor, origen) VALUES (:u, :des, :v, :o)");
                $insert->execute(array(':u' => $user_id, ':des' => 'Recarga de crédito', ':v' => $valor, ':o' => $origen));
                $lastId = $this->_DB->lastInsertId();

                $usuario->factura = $lastId;
                $usuario->token_solicitud = md5($resConfiguracionTuCompra->llave_terminal . '~' . $resConfiguracionTuCompra->cliente . '~' . $usuario->factura . '~' . $usuario->valor . '~' . 'COP');
                break;
            case 4:
                $insert = $this->_DB->prepare("INSERT INTO factura_pasarela(id_usuario, descripcion, valor, origen) VALUES (:u, :des, :v, :o)");
                $insert->execute(array(':u' => $user_id, ':des' => 'Recarga de crédito', ':v' => $valor, ':o' => $origen));
                $lastId = $this->_DB->lastInsertId();

                $usuario->factura = $lastId;
                $usuario->token_solicitud = md5($resConfiguracionTuCompra->llave_terminal . '~' . $resConfiguracionTuCompra->cliente . '~' . $usuario->factura . '~' . $usuario->valor . '~' . 'COP');
                break;
        }
        $usuario->datosUsuario = $datosUsuario;
        $result = array('state' => 1, 'usuario' => $usuario);
        $this->_DB = null;
        echo json_encode($result);
    }

    /**
     * @param $user_id
     * @param $user_i
     * @param $token
     * @param $id_factura
     */
    public function changeStatusDraft($user_id, $token, $id_factura)
    {
        require_once '../requires/funciones.php';
        $valida = $this->valida_token($user_id, $token);
        if (!$valida) {
            $m = 'Alguien ingresó con tu cuenta desde otro equipo vuelve a iniciar session';
            $state = 99;
            jsonReturn($state, $m);
        }
        $deposito = explode(" ", $id_factura);
        $stmt = $this->_DB->prepare("UPDATE factura_pasarela SET status = :d WHERE id_usuario = :u AND id = :i");
        $stmt->execute(array(':d' => 2, ':u' => $user_id, ':i' => $deposito[1]));
    }

    /**
     * @param $user_id
     * @param $token
     * @param $id_factura
     */
    public function cancelaPeticionPasarela($user_id, $token, $id_factura)
    {
        require_once '../requires/funciones.php';
        $valida = $this->valida_token($user_id, $token);
        if (!$valida) {
            $m = 'Alguien ingresó con tu cuenta desde otro equipo vuelve a iniciar session';
            $state = 99;
            jsonReturn($state, $m);
        }
        $deposito = explode(" ", $id_factura);
        $stmt = $this->_DB->prepare("UPDATE factura_pasarela SET status = :d WHERE id_usuario = :u AND id = :i");
        $stmt->execute(array(':d' => 3, ':u' => $user_id, ':i' => $deposito[1]));
        if ($stmt->rowCount() > 0) {
            $result = array('state' => 1, 'msg' => 'success');
        } else {
            $result = array('state' => 0, 'msg' => 'false');
        }
        $this->_DB = null;
        echo json_encode($result);
    }

    /**
     * @param $user
     * @return string
     */
    private function logUsuario($user_id)
    {
        require_once '../requires/funciones.php';
        $stmt = $this->_DB->prepare("INSERT INTO usuario_log (usuario_id, fecha_ini,ip) VALUES (?, NOW(), ?)");
        $stmt->bindParam(1, $user_id, PDO::PARAM_INT);
        $stmt->bindParam(2, ObtenerIP(), PDO::PARAM_STR);
        $stmt->execute();
        if ($stmt->rowCount() == 1) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $user
     * @return bool
     */
    private function usuario_log_login($user)
    {
        require_once '../requires/funciones.php';
        $stmt = $this->_DB->prepare("INSERT INTO usuario_log_login (ip, fecha, usuario) VALUES (?, NOW(), ?)");
        $stmt->bindParam(1, ObtenerIP(), PDO::PARAM_STR);
        $stmt->bindParam(2, $user, PDO::PARAM_INT);
        if ($stmt->rowCount() == 1) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $user
     */
    private function usuario_log_error($user)
    {
        $error = '';
        require_once '../requires/funciones.php';
        $this->_DB->beginTransaction();
        $stmt = $this->_DB->prepare('INSERT INTO usuario_log_error (ip, fecha, usuario) VALUES (:ip, :f, :u)');
        if (!$stmt->execute(array(':ip' => ObtenerIP(), ':f' => date('Y-n-d H:r:s'), ':u' => $user))) {
            $error = 'Error en el registro log login' . $this->_DB->errorInfo();
        }

        if ($error == '') {
            $this->_DB->commit();
        } else {
            $this->_DB->rollBack();
        }
    }


    /**
     * @param $user
     */
    public function login($user)
    {
        try {
            require_once '../requires/global.php';
            require_once '../requires/funciones.php';
            $country_code = $_SERVER["HTTP_CF_IPCOUNTRY"];

            $pre = $this->_DB->prepare("SELECT mail FROM preregistro WHERE mail = :m  AND estado = :p");
            $pre->execute(array(':m' => strtoupper($user->usuario), ':p' => 'PENDIENTE'));

            if ($pre->rowCount() == 1) {
                $m = 'El usuario ingresado se encuentra en estado de verificación.';
                $state = 99;
                jsonReturn($state, $m);
            }

            if ($country_code == 'US') {
                $m = 'Su pais no se encuentra autorizado.';
                $state = 98;
                jsonReturn($state, $m);
            }

            $stmt = $this->_DB->prepare("SELECT a.usuario_id, a.intentos, c.contingencia, a.estado, a.estado_esp, a.estado_jugador
                                    FROM usuario a INNER JOIN usuario_perfil b ON ( a.mandante = b.mandante AND a.usuario_id = b.usuario_id)
                                    INNER JOIN perfil c ON (b.perfil_id = c.perfil_id)
                                    WHERE a.mandante = '" . MANDANTE . "' AND a.login = :login");
            $stmt->execute(array(':login' => strtoupper($user->usuario)));
            if ($stmt->rowCount() == 1) {
                $data = $stmt->fetch(PDO::FETCH_OBJ);
                if ($data->estado_esp == 'I') {
                    $result = array('state' => 1, 'msg' => 'El usuario ingresado se encuentra inactivo. Si tiene alguna inquietud, favor escríbanos por el chat en línea o por la página de contacto.');
                } elseif ($data->contingencia == 'S') {
                    $result = array('state' => 2, 'msg' => 'En el momento nos encontramos en proceso de mantenimiento del sitio. Por favor intente nuevamente en unos minutos. Agradecemos su comprensión.');
                } elseif ($data->intentos > 4) {
                    $result = array('state' => 3, 'msg' => 'El usuario ha sido bloqueado por el sistema debido a que excedió el número de intentos permitidos con clave errónea. Por favor use la opción de recuperación de clave que encontrará en la parte superior derecha del sitio << ¿Olvidó su contraseña? >>, o escríbanos por el chat en línea o por la página de contacto, para ayudarle a solucionar su inconveniente.');
                } elseif ($data->estado_jugador == 'CT') {
                    $result = array('state' => 10, 'msg' => 'Esta cuenta a sido cancelada para ingresar nuevamente deberá volver a registrar una nueva cuenta.');
                } else {

                    //valida el hash de la contraseña ingresada
                    $query_hash = $this->_DB->prepare("SELECT clave FROM usuario WHERE usuario_id = ?");
                    $query_hash->bindParam(1, $data->usuario_id, PDO::PARAM_INT);
                    $query_hash->execute();
                    $hash = $query_hash->fetch(PDO::FETCH_OBJ);
                    $validar = $this->validar_password($hash->clave, $user->clave);

                    //trae los datos de la session
                    $userData = $this->_DB->prepare("SELECT
                                                                a.usuario_id,
                                                                aes_decrypt(a.nombre, '" . CLAVE_ENCRYPT . "') AS nombre,
                                                                a.token_itainment,
                                                                r.cedula,
                                                                p.tipo
                                                            FROM
                                                                usuario a
                                                            INNER JOIN usuario_perfil b ON (
                                                                a.mandante = b.mandante
                                                                AND a.usuario_id = b.usuario_id
                                                            )
                                                            INNER JOIN registro r ON r.usuario_id = a.usuario_id
                                                            INNER JOIN perfil p ON (p.perfil_id = b.perfil_id)
                                                            WHERE
                                                                a.mandante = :m
                                                                AND login = :login
                                                                AND a.estado = 'A'
                                                                AND a.estado_jugador = :e");
                    $userData->execute(array(':m' => MANDANTE, ':login' => strtoupper($user->usuario), ':e' => 'AC'));

                    if ($userData->rowCount() == 1 and $validar) {
                        $row = $userData->fetch(PDO::FETCH_OBJ);
                        if ($row->tipo !== 'U') {
                            $m = 'Su perfil no esta autorizado para esta zona';
                            $state = 97;
                            jsonReturn($state, $m);
                        }

                        $q = $this->logUsuario($row->usuario_id);
                        if (!$q) {
                            $m = 'Problema tipo log';
                            $state = 96;
                            jsonReturn($state, $m);
                        }

                        $pregunta = $this->_DB->prepare("SELECT id_pregunta FROM usuario_pregunta_seguridad WHERE id_user = :u");
                        $pregunta->execute(array(':u' => $row->usuario_id));
                        if ($pregunta->rowCount() > 2) {
                            $pregunta = 1;
                        } else {
                            $pregunta = 0;
                        }

                        $tipo_registro = $this->_DB->prepare("SELECT tipo_registro, corrige_fecha FROM preregistro p INNER JOIN registro r ON r.preregistro_id = p.id  AND r.usuario_id = ?");
                        $tipo_registro->bindParam(1, $row->usuario_id, PDO::PARAM_INT);
                        $tipo_registro->execute();
                        $t_registro = $tipo_registro->fetch(PDO::FETCH_OBJ);

                        session_destroy();
                        session_start();

                        $_SESSION["logueado"] = true;
                        $_SESSION['timeOnline'] = time() * 1000;
                        $_SESSION['online'] = date("H:i:s");
                        $_SESSION["pregunta_seguridad"] = $pregunta;
                        $_SESSION["tipo_registro"] = $t_registro->tipo_registro;
                        $_SESSION["c_fecha"] = $t_registro->corrige_fecha;
                        $_SESSION["win_perfil"] = $row->tipo;
                        $_SESSION["usuario"] = $row->usuario_id;
                        $_SESSION["nombre"] = $row->nombre;
                        $_SESSION['numDoc'] = $row->cedula;
                        $_SESSION["dir_ip"] = ObtenerIP();
                        $_SESSION["token"] = tokenItainment($row->usuario_id);
                        $_SESSION['token_session'] = uniqid();


                        //se genera el token de la session Y restaura los intentos fallidos
                        $token = $this->_DB->prepare("UPDATE usuario SET token_session = ?, dir_ip = ?, intentos = ?, token_itainment = ?, estado_token = ?, fecha_ult = NOW() WHERE usuario_id = ? AND mandante = ?");
                        $token->bindParam(1, $_SESSION['token_session'], PDO::PARAM_STR);
                        $token->bindParam(2, ObtenerIP(), PDO::PARAM_STR);
                        $token->bindValue(3, 0, PDO::PARAM_INT);
                        $token->bindParam(4, $_SESSION["token"], PDO::PARAM_INT);
                        $token->bindValue(5, MANDANTE, PDO::PARAM_INT);
                        $token->bindParam(6, $row->usuario_id, PDO::PARAM_INT);
                        $token->bindValue(7, MANDANTE, PDO::PARAM_INT);
                        $token->execute();

                        /*3dementes*/
                        $result = array(
                            'state' => 7,
                            'msg' => 'Bienvenido a ' . EMPRESA,
                            'timeOnline' => time() * 1000,
                            'perfil' => $row->tipo,
                            'id' => $row->usuario_id,
                            'numDoc' => $row->cedula,
                            'nombre' => $row->nombre,
                            'pregunta_seguridad' => $pregunta,
                            'tipo_registro' => $_SESSION["tipo_registro"],
                            'c_fecha' => $_SESSION["c_fecha"],
                            'token_game' => $_SESSION["token"],
                            'token_session' => $_SESSION['token_session'],
                        );
                        /*3dementes fin*/

                    } else {
                        $this->usuario_log_error($data->usuario_id);
                        $state = '';
                        $this->_DB->beginTransaction();

                        //incrementa los intentos del usuario en 1
                        $strSql = $this->_DB->prepare("UPDATE usuario SET intentos=intentos+:v WHERE mandante=:m AND login = :u");
                        if (!$strSql->execute(array(':m' => MANDANTE, ':u' => strtoupper($user->usuario), ':v' => 1))) {
                            $m = 'Error actualizando los intentos del usuario';
                            $state = 7;
                            jsonReturn($state, $m);
                        }

                        //si los intentos llegan a 5 el usuario es bloqueado
                        $estadoSql = $this->_DB->prepare("UPDATE usuario SET estado=CASE WHEN intentos=5 THEN 'I' ELSE estado END,estado_ant=CASE WHEN intentos=5 THEN 'I' ELSE estado_ant END, dir_ip = :ip WHERE mandante= :mandante AND usuario_id=:usuario");
                        if (!$estadoSql->execute(array(':mandante' => MANDANTE, ':usuario' => $data->usuario_id, ':ip' => ObtenerIP()))) {
                            $m = 'Error al cambiar el estado inactivo por intentos del usuario';
                            $state = 8;
                            jsonReturn($state, $m);
                        }

                        if ($state == '') {
                            $this->_DB->commit();
                            $result = array('state' => 8, 'msg' => 'La clave ingresada es errónea. Recuerde que al quinto (5) intento con una clave equivocada, el sistema lo bloqueará automáticamente. Recuerde que puede usar la opción de recuperación de clave que está disponible en la parte superior derecha del sitio y así evitar bloquear su cuenta en WPlay.');
                        } else {
                            $this->_DB->rollBack();
                            $result = array('state' => 13, 'msg' => 'Error');
                        }
                    }
                }
            } else {
                $result = array('state' => 0, 'msg' => 'El usuario ingresado no se encuentra registrado.');
            }
        } catch (PDOException $e) {
            $result = array('state' => 0, 'msg' => 'Error ' . $e->getMessage());
        } finally {
            $this->_DB = null;
            echo json_encode($result);
        }
    }

    /**
     * @param $user_id
     */
    public function logout($user_id)
    {
        //echo $user_id;exit();
        $this->cerrarSession($user_id);

        //remove PHPSESSID from browser
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), "", time() - 3600, "/");
        }

        //clear session from globals
        $_SESSION = array();
        //clear session from disk
        session_destroy();
        $result = array('state' => 1, 'msg' => 'Logout exitoso');
        echo json_encode($result);
    }

    public function getSession()
    {
        $session = $this->getActualSession();
        echo json_encode($session);
    }

    /**
     * @param $variable
     * @param $valor
     */
    private function updateSession($variable, $valor)
    {
        if (!isset($_SESSION)) {
            session_start();
        }
        if (isset($_SESSION[$variable])) {
            $_SESSION[$variable] = $valor;
        }
    }

    private function tokenItainmet()
    {
        if (isset($_SESSION)) {

            require_once '../requires/funciones.php';
            $token = $this->_DB->prepare("UPDATE usuario SET token_itainment = ?, estado_token = ? WHERE usuario_id = ?");
            $token->bindParam(1, tokenItainment($_SESSION['usuario']), PDO::PARAM_INT);
            $token->bindValue(2, 0, PDO::PARAM_INT);
            $token->bindParam(3, $_SESSION['usuario'], PDO::PARAM_INT);
            $token->execute();

            $q = $this->_DB->prepare("SELECT token_itainment FROM usuario WHERE usuario_id = ?");
            $q->bindParam(1, $_SESSION['usuario'], PDO::PARAM_INT);
            $q->execute();
            $token = $q->fetch(PDO::FETCH_OBJ);
        }

        return $token->token_itainment;
    }

    private function getActualSession()
    {
        if (!isset($_SESSION)) {
            session_start();
        }
        $sess = array();
        if (isset($_SESSION['usuario'])) {
            $sess["usuario"] = $_SESSION['usuario'];
            $sess["nombre"] = $_SESSION['nombre'];
            $sess["timeOnline"] = $_SESSION['timeOnline'];
            $sess["numDoc"] = $_SESSION['numDoc'];
            $sess["tokenSession"] = $_SESSION['token_session'];
            $sess["token_game"] = $this->tokenItainmet();
            $sess["pregunta_seguridad"] = $_SESSION["pregunta_seguridad"];
            $sess["tipo_registro"] = $_SESSION["tipo_registro"];
            $sess["c_fecha"] = $_SESSION["c_fecha"];
        } else {
            $sess["usuario"] = '';
            $sess["nombre"] = 'Guest';
            $sess["timeOnline"] = '';
            $sess["tokenSession"] = '';
            $sess["token_game"] = '';
            $sess["pregunta_seguridad"] = '';
            $sess['numDoc'] = '';
            $sess['tipo_registro'] = '';
            $sess['c_fecha'] = '';
        }
        return $sess;
    }

    /**
     * @param $hash
     * @param $pass
     * @return bool
     */
    private function validar_password($hash, $pass)
    {
        $delimitador = strpos($hash, '$');
        if ($delimitador === false) {
            return false;
        }
        $salt = base64_decode(substr($hash, 0, $delimitador));
        $tHash = '';
        for ($i = 0; $i < 5; $i++) {
            $tHash = hash('sha384', $tHash . $salt . $pass);
        }
        return (base64_encode($salt) . '$' . $tHash == $hash);
    }

    /**
     * @return string
     */
    public function pseudoLlaveAleatoria()
    {
        if (function_exists('openssl_random_pseudo_bytes')) {
            $rnd = openssl_random_pseudo_bytes(256, $strong);
            if ($strong == true) {
                return $rnd;
            }
        }
        $sha = '';
        $rnd = '';
        for ($i = 0; $i < 256; $i++) {
            $sha = hash('sha256', $sha . mt_rand());
            $char = mt_rand(0, 62);
            $rnd .= chr(hexdec($sha[$char] . $sha[$char + 1]));
        }
        return $rnd;
    }

    /**
     * @param $pass
     * @return string
     */
    public function password($pass)
    {
        $salt = $this->pseudoLlaveAleatoria();
        $hash = '';
        for ($i = 0; $i < 5; $i++) {
            $hash = hash('sha384', $hash . $salt . $pass);
        }
        return base64_encode($salt) . '$' . $hash;
    }

    /**
     * @param $user
     */
    public function contact($user)
    {
        require_once '../requires/global.php';
        require_once '../requires/funciones.php';
        $this->_DB->beginTransaction();
        $state = '';

        $strSql = $this->_DB->prepare("INSERT INTO contacto (nombre, email, telefono, mensaje, fecha_crea, mandante, tema) VALUES (:nombre, :email, :telefono, :mensaje, :fecha, :mandante, :tema)");
        if (!$strSql->execute(array(':nombre' => $user->name, ':email' => $user->email, ':telefono' => $user->tel, ':mensaje' => $user->msg, ':fecha' => date('Y-m-d H:i:s'), ':mandante' => MANDANTE, ':tema' => $user->tema))) {
            $m = 'No se ingreso el registro del contacto';
            $state = 1;
            jsonReturn($state, $m);
        }

        require "../libs/vendor/phpmailer/phpmailer/PHPMailerAutoload.php";
        $mailer = new PHPMailer();
        $mailer->IsSMTP();
        $mailer->Host = "mail.betstore.co";
        $mailer->Port = 587;
        $mailer->SMTPDebug = 0;
        $mailer->From = "wplay@betstore.co";
        $mailer->FromName = "wplay";
        $mailer->Subject = "inconvenientes de contactos";

        ob_start();
        require_once '../consultas/template_mail/contacto/mailer.php';
        $message = ob_get_contents();
        ob_end_clean();

        $mailer->SetFrom('contacto@intechenter.com', $user->tema);
        $mailer->msgHTML($message, dirname(__FILE__));
        $mailer->AddAddress('contacto@intechenter.com', $user->tema);
        $mailer->isHTML(true);
        $mailer->CharSet = "utf-8";
        $mailer->SMTPAuth = true;
        $mailer->Username = "wplay@betstore.co";
        $mailer->Password = "megaman300";

        if (!$mailer->Send()) {
            $m = "No se ha enviado el email error: " . $mailer->ErrorInfo;
            $state = 2;
            jsonReturn($state, $m);
        }

        if (!$state) {
            $this->_DB->commit();
            $data = array('state' => 1, 'msg' => 'El mensaje a sido enviado. nuestros operadores validaran la información y se contactaran con usted en el menor tiempo posible.');
        } else {
            $this->_DB->rollBack();
            $data = array('state' => 2, 'msg' => 'Error');
        }
        $this->_DB = null;
        echo json_encode($data);
    }

    /**
     * restaura la contraseña si el usuario lo olvida 1°parte cuando el usuario lo solicita
     * @param $user
     **/
    public function recuperarClave($user)
    {
        require_once '../requires/global.php';
        require_once '../requires/funciones.php';

        $postdata = http_build_query(
            array(
                'secret' => '6Le7xQ8UAAAAAHv8ow4yOvdSGU8Xa2Wfx6mZDeri', //secret KEy provided by google
                'response' => $user->captcha,                    // g-captcha-response string sent from client
                'remoteip' => $_SERVER['REMOTE_ADDR']
            )
        );

//Build options for the post request
        $opts = array('http' =>
            array(
                'method' => 'POST',
                'header' => 'Content-type: application/x-www-form-urlencoded',
                'content' => $postdata
            )
        );

        $context = stream_context_create($opts);

        /* Send request to Googles siteVerify API*/
        $response = file_get_contents("https://www.google.com/recaptcha/api/siteverify", false, $context);

        $response = json_decode($response, true);

        if ($response["success"] === false) {
            $state = 65;
            $m = 'Captcha no valida';
            jsonReturn($state, $m);
        }

        //Valida que el usuario se haya registrado previamente
        $validaSql = $this->_DB->prepare("SELECT a.usuario_id, b.login
                                                FROM registro a
                                                INNER JOIN usuario b ON (a.mandante = b.mandante AND a.usuario_id = b.usuario_id)
                                                WHERE a.mandante =:mandante
                                                AND a.email = :email
                                                AND b.estado_jugador =:estado");

        $validaSql->execute(array(':mandante' => MANDANTE, ':estado' => 'AC', ':email' => strtoupper($user->email)));

        if ($validaSql->rowCount() != 1) {
            $state = 2;
            $m = 'El usuario ingresado no se encuentra registrado';
            jsonReturn($state, $m);
        }


        $this->_DB->beginTransaction();
        $user_id = $validaSql->fetch(PDO::FETCH_OBJ);
        $activeLink = $this->_DB->prepare("UPDATE usuario SET link_recupera_contrasena = ?, link_valido_fecha_contrasena = NOW() + INTERVAL 1 DAY WHERE usuario_id = ?");
        $activeLink->bindValue(1, 1, PDO::PARAM_INT);
        $activeLink->bindParam(2, $user_id->usuario_id, PDO::PARAM_INT);
        $activeLink->execute();

        if ($activeLink->rowCount() != 1) {
            $state = 3;
            $m = 'No fue posible realizar la peticion solicitada intentalo nuevamente.';
            jsonReturn($state, $m);
        }

        require_once "../libs/vendor/phpmailer/phpmailer/PHPMailerAutoload.php";

        $mailer = new PHPMailer();
        $mailer->IsSMTP();
        $mailer->Host = "localhost";
        $mailer->CharSet = "UTF-8";
        $mailer->SMTPDebug = 0;
        $mailer->From = "noreply@wplay.co";
        $mailer->FromName = "Info Wplay";
        $mailer->Subject = "Contraseña WPlay.co";
        $mailer->SMTPAuth = false;
        $mailer->isHTML(true);

        $m = base64_encode($user->email);
        ob_start();

        require_once '../template_mail/recuperar_contrasena/mailer.php';
        $message = ob_get_contents();
        ob_end_clean();

        $mailer->msgHTML($message, dirname(__FILE__));
        $mailer->AddAddress($user->email);

        if (!$mailer->Send()) {
            $state = 4;
            $m = $mailer->ErrorInfo;
            jsonReturn($state, $m);
        }
        
        if (!$state) {
            $this->_DB->commit();
            $result = array('state' => 1, 'msg' => 'Se ha enviado a su correo las instrucciones para recuperar su contraseña');
        } else {
            $this->_DB->rollBack();
            $result = array('state' => 0, 'msg' => 'Error');
        }
        echo json_encode($result);
    }

    /**
     * restaura la contraseña si el usuario lo olvida 2°parte cuando el usuario utiliza el link
     * @param $data
     */
    public function restorePassword($data)
    {

        require_once '../requires/global.php';
        require_once '../requires/funciones.php';

        if (!isset($data->captcha)) {
            $state = 59;
            $m = 'Captcha no valida';
        }

        $postdata = http_build_query(
            array(
                'secret' => '6Le7xQ8UAAAAAHv8ow4yOvdSGU8Xa2Wfx6mZDeri', //secret KEy provided by google
                'response' => $data->captcha,                    // g-captcha-response string sent from client
                'remoteip' => $_SERVER['REMOTE_ADDR']
            )
        );

//Build options for the post request
        $opts = array('http' =>
            array(
                'method' => 'POST',
                'header' => 'Content-type: application/x-www-form-urlencoded',
                'content' => $postdata
            )
        );

        $context = stream_context_create($opts);

        /* Send request to Googles siteVerify API*/
        $response = file_get_contents("https://www.google.com/recaptcha/api/siteverify", false, $context);

        $response = json_decode($response, true);

        if ($response["success"] === false) {
            $state = 65;
            $m = 'Captcha no valida';
            jsonReturn($state, $m);
        }

        $state = '';
        if ($data->pass != $data->cPass) {
            $m = 'La contraseña no coincide';
            $state = 1;
            jsonReturn($state, $m);
        }

        $query_hash = $this->_DB->prepare("SELECT clave FROM usuario WHERE login = ?");
        $query_hash->bindParam(1, base64_decode($data->email), PDO::PARAM_STR);
        $query_hash->execute();
        $hash = $query_hash->fetch(PDO::FETCH_OBJ);
        $validar = $this->validar_password($hash->clave, $data->pass);

        if ($validar == 1) {
            $m = 'La contraseña debe ser diferente a la ultima almacenada';
            $state = 2;
            jsonReturn($state, $m);
        }

        $stmt = $this->_DB->prepare("SELECT r.email, u.estado_jugador, u.link_recupera_contrasena, u.link_valido_fecha_contrasena FROM registro r INNER JOIN usuario u ON r.usuario_id = u.usuario_id WHERE r.email = ?");
        $stmt->bindParam(1, base64_decode($data->email), PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() != 1) {
            $m = 'El correo no se encuentra en nuestra base de datos';
            $state = 3;
            jsonReturn($state, $m);
        }

        $estado = $stmt->fetch(PDO::FETCH_OBJ);

        if ($estado->estado_jugador != 'AC') {
            $m = 'Esta cuenta de juego no se encuentra activa';
            $state = 4;
            jsonReturn($state, $m);
        }

        if ($estado->link_recupera_contrasena == 0) {
            $m = 'El link para cambiar contraseña ya se ha utilizado debes volver a solicitar un nuevo link para realizar esta operación';
            $state = 5;
            jsonReturn($state, $m);
        }

        $now = $this->_DB->query("SELECT NOW() fecha");
        $now->execute();
        $res_now = $now->fetch(PDO::FETCH_OBJ);

        if ($estado->link_valido_fecha_contrasena < $res_now->fecha) {
            $m = 'El link para cambiar contraseña ya ha expirado debes volver a solicitar un nuevo link para realizar esta operación';
            $state = 6;
            jsonReturn($state, $m);
        }

        $this->_DB->beginTransaction();
        $pass = $this->password($data->pass);

        $q = $this->_DB->prepare("UPDATE usuario SET clave = ?, fecha_recupera_clave = now(), intentos = ?, estado = ?, link_recupera_contrasena = ? WHERE login = ?");
        $q->bindParam(1, $pass, PDO::PARAM_STR);
        $q->bindValue(2, 0, PDO::PARAM_INT);
        $q->bindValue(3, 'A', PDO::PARAM_STR);
        $q->bindValue(4, 0, PDO::PARAM_INT);
        $q->bindParam(5, base64_decode($data->email), PDO::PARAM_STR);
        $q->execute();

        if ($q->rowCount() != 1) {
            $m = 'No se pudo procesar la petición inténtelo nuevamente';
            $state = 7;
            jsonReturn($state, $m);
        }

        if (!$state) {
            $this->_DB->commit();
            $result = array('state' => 1, 'msg' => 'La contraseña se ha restaurado correctamente');

        } else {
            $this->_DB->rollBack();
            $result = array('state' => 0, 'msg' => 'Error');
        }
        $this->_DB = null;
        echo json_encode($result);
    }

    /**
     * @param $user_id
     */
    private function cerrarSession($user_id)
    {
        require_once '../requires/funciones.php';
        $query = $this->_DB->prepare("SELECT
                                                fecha_ini,
                                                usulog_id
                                            FROM
                                                usuario_log
                                            WHERE
                                                usuario_id = :u
                                            ORDER BY
                                                usulog_id DESC
                                            LIMIT 1");
        $query->execute(array(':u' => $user_id));
        $result = $query->fetch(PDO::FETCH_OBJ);

        $fechaFin = date('Y-m-d H:i:s');
        $tiempo = date("H:i", strtotime("00:00") + strtotime($fechaFin) - strtotime($result->fecha_ini));

        $error = '';
        $this->_DB->beginTransaction();

        $stmt = $this->_DB->prepare("UPDATE usuario_log SET tiempo = :tiempo, fecha_fin = :dateEnd WHERE usulog_id = :idlog");

        if (!$stmt->execute(array(':dateEnd' => $fechaFin, ':tiempo' => $tiempo, ':idlog' => $result->usulog_id))) {
            $error = 'Error ' . $this->_DB->errorInfo();
        }


        $q = $this->_DB->prepare("UPDATE usuario SET token_itainment = ?, estado_token = ? WHERE usuario_id = ?");
        $q->bindParam(1, tokenItainment($user_id), PDO::PARAM_INT);
        $q->bindValue(2, 0, PDO::PARAM_INT);
        $q->bindParam(3, $user_id, PDO::PARAM_INT);
        $q->execute();

        if ($q->rowCount() != 1) {
            $error = 'Error ' . $this->_DB->errorInfo();
        }

        if ($error == '') {
            $this->_DB->commit();
        } else {
            $this->_DB->rollBack();
        }
        $this->_DB = null;
    }

    /**
     * @param $usuario
     */
    public
    function usuarioAutoExclusionApuestas($usuario)
    {
        require_once '../requires/funciones.php';
        $valida = $this->valida_token($usuario->user_id, $usuario->token);
        if (!$valida) {
            $m = 'Alguien ingresó con tu cuenta desde otro equipo vuelve a iniciar session';
            $state = 99;
            jsonReturn($state, $m);
        }

        require_once '../requires/global.php';
        $query_hash = $this->_DB->prepare("SELECT clave FROM usuario WHERE usuario_id = :u");
        $query_hash->execute(array(':u' => $usuario->user_id));
        $hash = $query_hash->fetch(PDO::FETCH_OBJ);
        $validar = $this->validar_password($hash->clave, $usuario->contrasena);

        $query = $this->_DB->prepare("SELECT estado FROM usuario WHERE usuario_id = :u AND estado = :e AND estado_jugador = :ej");
        $query->execute(array(':u' => $usuario->user_id, ':e' => 'A', ':ej' => 'AC'));

        if ($query->rowCount() == 1 and $validar) {

            $userExit = $this->_DB->prepare("SELECT COUNT(*) FROM usuario_autoexclusion_apuesta WHERE usuario_id = ?");
            $userExit->bindParam(1, $usuario->user_id, PDO::PARAM_INT);
            $userExit->execute();

            if ($userExit->rowCount() > 1) {
                $m = 'Las preguntas de seguridad ya han sido guardadas para este usuario';
                $state = 98;
                jsonReturn($state, $m);
            }

            $fecha = date('Y-m-d H:i:s');
            $fecha_fin = strtotime('+30 day', strtotime($fecha));
            $fecha_fin = date('Y-m-d H:i:s', $fecha_fin);

            $state = '';
            $this->_DB->beginTransaction();

            $stmt = $this->_DB->prepare("INSERT INTO usuario_autoexclusion_apuesta (usuario_id, limite_diario, limite_semanal, limite_mensual, mandante, fecha_crea, fecha_fin )
                        VALUES (?, ?, ?, ?, ?, NOW(), ?)");
            $stmt->bindParam(1, $usuario->user_id, PDO::PARAM_INT);
            $stmt->bindParam(2, $usuario->limiteDiarioApuestas->valor, PDO::PARAM_INT);
            $stmt->bindParam(3, $usuario->limiteSemanalApuestas->valor, PDO::PARAM_INT);
            $stmt->bindParam(4, $usuario->limiteMensualApuestas->valor, PDO::PARAM_INT);
            $stmt->bindValue(5, MANDANTE, PDO::PARAM_INT);
            $stmt->bindParam(6, $fecha_fin, PDO::PARAM_STR);

            if (!$stmt->execute()) {
                $state = 'Error al ingresar los limites intentelo nuevamente';
            }

            if ($state == '' and $stmt->rowCount() == 1) {
                $this->_DB->commit();
                $result = array('state' => 1, 'msg' => 'Se ingresaron los nuevos limites correctamente.');
            } else {
                $this->_DB->rollBack();
                $result = array('state' => 0, 'msg' => 'Error');
            }
        } else {
            $result = array('state' => 2, 'msg' => 'La contraseña ingresada no es correcta.');
        }
        $this->_DB = null;
        echo json_encode($result);
    }

    /**
     * @param $usuario
     */
    public function usuarioAutoExclusionDeposito($usuario)
    {
        require_once '../requires/funciones.php';
        $valida = $this->valida_token($usuario->user_id, $usuario->token);
        if (!$valida) {
            $m = 'Alguien ingresó con tu cuenta desde otro equipo vuelve a iniciar session';
            $state = 99;
            jsonReturn($state, $m);
        }

        require_once '../requires/global.php';
        $query_hash = $this->_DB->prepare("SELECT clave FROM usuario WHERE usuario_id = :u");
        $query_hash->execute(array(':u' => $usuario->user_id));
        $hash = $query_hash->fetch(PDO::FETCH_OBJ);
        $validar = $this->validar_password($hash->clave, $usuario->contrasena);

        if (!$validar) {
            $error = 'La contraseña ingresada no es correcta.';
            $state = 2;
            jsonReturn($state, $error);
        }

        $query = $this->_DB->prepare("SELECT estado FROM usuario WHERE usuario_id = :u AND estado = :e AND estado_jugador = :ej");
        $query->execute(array(':u' => $usuario->user_id, ':e' => 'A', ':ej' => 'AC'));

        if ($query->rowCount() != 1) {
            $error = 'El usuario actual no se encuentra activo';
            $state = 3;
            jsonReturn($state, $error);
        }

        $q = $this->_DB->prepare("SELECT limitedep_diario, limitedep_semanal, limitedep_mensuaL FROM registro
                                                            WHERE usuario_id = ? AND mandante = ?");
        $q->bindParam(1, $usuario->user_id, PDO::PARAM_INT);
        $q->bindValue(2, MANDANTE, PDO::PARAM_INT);
        $q->execute();
        $res = $q->fetch(PDO::FETCH_OBJ);

        if ($res->limitedep_diario < $usuario->limiteDiarioApuestas->valor) {
            $error = "El limite de deposito diario no puede ser mayor que el actual.";
            $state = 3;
            jsonReturn($state, $error);
        }

        if ($res->limitedep_semanal < $usuario->limiteSemanalApuestas->valor) {
            $error = "El limite de deposito semenal no puede ser mayor que el actual.";
            $state = 4;
            jsonReturn($state, $error);
        }

        if ($res->limitedep_mensuaL < $usuario->limiteMensualApuestas->valor) {
            $error = "El limite de deposito mensual no puede ser mayor que el actual.";
            $state = 5;
            jsonReturn($state, $error);
        }

        $this->_DB->beginTransaction();
        $stmt = $this->_DB->prepare("UPDATE registro
                                                            SET limitedep_diario = ?, limitedep_semanal = ?, limitedep_mensual = ?, fecha_limite_deposito = NOW()
                                                            WHERE usuario_id = ? AND mandante = ?");
        $stmt->bindParam(1, $usuario->limiteDiarioApuestas->valor, PDO::PARAM_INT);
        $stmt->bindParam(2, $usuario->limiteSemanalApuestas->valor, PDO::PARAM_INT);
        $stmt->bindParam(3, $usuario->limiteMensualApuestas->valor, PDO::PARAM_INT);
        $stmt->bindParam(4, $usuario->user_id, PDO::PARAM_INT);
        $stmt->bindValue(5, MANDANTE, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() != 1) {
            $error = 'Error al ingresar el limite de deposito inténtelo nuevamente';
            $state = 6;
            jsonReturn($state, $error);
        }

        if (!$state) {
            $this->_DB->commit();
            $result = array('state' => 1, 'msg' => 'Los limites de deposito se actualizaron correctamente.');
        } else {
            $this->_DB->rollBack();
            $result = array('state' => 0, 'msg' => $state);
        }

        $this->_DB = null;
        echo json_encode($result);
    }

    public
    function autoExclusionLimiteDepositoPeticion($usuario)
    {
        require_once '../requires/funciones.php';
        $valida = $this->valida_token($usuario->user_id, $usuario->token);
        if (!$valida) {
            $m = 'Alguien ingresó con tu cuenta desde otro equipo vuelve a iniciar session';
            $state = 99;
            jsonReturn($state, $m);
        }

        require_once '../requires/global.php';
        $query_hash = $this->_DB->prepare("SELECT clave FROM usuario WHERE usuario_id = :u");
        $query_hash->execute(array(':u' => $usuario->user_id));
        $hash = $query_hash->fetch(PDO::FETCH_OBJ);
        $validar = $this->validar_password($hash->clave, $usuario->contrasena);

        if (!$validar) {
            $error = 'La contraseña ingresada no es correcta.';
            $state = 2;
            jsonReturn($state, $error);
        }

        $query = $this->_DB->prepare("SELECT estado FROM usuario WHERE usuario_id = :u AND estado = :e AND estado_jugador = :ej");
        $query->execute(array(':u' => $usuario->user_id, ':e' => 'A', ':ej' => 'AC'));

        if ($query->rowCount() != 1) {
            $error = 'El usuario actual no se encuentra activo';
            $state = 3;
            jsonReturn($state, $error);
        }

        $q = $this->_DB->prepare("SELECT limitedep_diario, limitedep_semanal, limitedep_mensuaL FROM registro
                                                            WHERE usuario_id = ? AND mandante = ?");
        $q->bindParam(1, $usuario->user_id, PDO::PARAM_INT);
        $q->bindValue(2, MANDANTE, PDO::PARAM_INT);
        $q->execute();
        $res = $q->fetch(PDO::FETCH_OBJ);

        $query = $this->_DB->prepare("SELECT fecha_limit_pet, NOW() AS hoy FROM solicitud_limite_temp WHERE usuario_id = ? ORDER BY id DESC");
        $query->bindParam(1, $usuario->user_id, PDO::PARAM_INT);
        $query->execute();

        $res = $query->fetch(PDO::FETCH_OBJ);
        if (strtotime($res->fecha_limit_pet) > strtotime($res->hoy)) {
            $error = "Tenemos una solicitud pendiente por aprobar, espera que nuestros operadores se comunicaran a la mayor brevedad posible.";
            $state = 4;
            jsonReturn($state, $error);
        }

        $this->_DB->beginTransaction();
        $stmt = $this->_DB->prepare("INSERT INTO solicitud_limite_temp
                                                             (usuario_id,limitedep_diario, limitedep_semanal, limitedep_mensual, mandante, fecha_limit_pet) VALUES (?, ?, ?, ?, ?, NOW() + INTERVAL 3 DAY)");
        $stmt->bindParam(1, $usuario->user_id, PDO::PARAM_INT);
        $stmt->bindParam(2, $usuario->limiteDiarioApuestas->valor, PDO::PARAM_INT);
        $stmt->bindParam(3, $usuario->limiteSemanalApuestas->valor, PDO::PARAM_INT);
        $stmt->bindParam(4, $usuario->limiteMensualApuestas->valor, PDO::PARAM_INT);
        $stmt->bindValue(5, MANDANTE, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() != 1) {
            $error = 'No fue posible enviar la solicitud para aumentar el limite de deposito inténtelo nuevamente';
            $state = 6;
            jsonReturn($state, $error);
        }

        if (!$state) {
            $this->_DB->commit();
            $result = array('state' => 1, 'msg' => 'La solicitud para aumentar los limites de deposito se envío correctamente. en menos de 72 horas tendra una respuesta por parte de nuestros operadores');
        } else {
            $this->_DB->rollBack();
            $result = array('state' => 0, 'msg' => $state);
        }

        $this->_DB = null;
        echo json_encode($result);
    }

    /**
     * @param $usuario
     */
    public
    function usuarioAutoExclusionTiempo($usuario)
    {
        require_once '../requires/funciones.php';
        $valida = $this->valida_token($usuario->user_id, $usuario->token);
        if (!$valida) {
            $m = 'Alguien ingresó con tu cuenta desde otro equipo vuelve a iniciar session';
            $state = 99;
            jsonReturn($state, $m);
        }

        require_once '../requires/global.php';
        $query_hash = $this->_DB->prepare("SELECT
                                                    clave
                                                FROM
                                                    usuario
                                                WHERE
                                                    usuario_id = :u");
        $query_hash->execute(array(':u' => $usuario->user_id));
        $hash = $query_hash->fetch(PDO::FETCH_OBJ);

        $validar = $this->validar_password($hash->clave, $usuario->contrasena);

        $query = $this->_DB->prepare("SELECT estado FROM usuario WHERE usuario_id = :u");
        $query->execute(array(':u' => $usuario->user_id));

        if ($query->rowCount() > 0 && $validar) {

            $fecha = date('Y-m-d');
            $nuevafecha = strtotime('+' . $usuario->limiteDias->value . ' day', strtotime($fecha));
            $nuevafecha = date('Y-m-d', $nuevafecha);
            $tipoJuego = 1;
            $mandante = 0;

            $stmt = $this->_DB->prepare("INSERT INTO usuario_autoexclusion_juego (usuario_id, tipojuego_id, fecha_fin, mandante, fecha_crea )
                        VALUES ( ?, ?, ?, ?, NOW() )");

            $stmt->bindParam(1, $usuario->user_id, PDO::PARAM_INT);
            $stmt->bindParam(2, $tipoJuego, PDO::PARAM_INT);
            $stmt->bindParam(3, $nuevafecha, PDO::PARAM_STR);
            $stmt->bindParam(4, $mandante, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $result = array('state' => 1, 'msg' => 'Se Ingresaron los nuevos limites de tiempo correctamente.');
            } else {
                $result = array('state' => 0, 'msg' => 'Ocurrió un erro inténtalo nuevamente.');
            }
        } else {
            $result = array('state' => 2, 'msg' => 'La contraseña ingresada no es correcta.');
        }
        $this->_DB = null;
        echo json_encode($result);
    }

    /**
     * @param $user_id
     * @param $token
     */
    public
    function verLimiteApuesta($user_id, $token)
    {
        require_once '../requires/funciones.php';
        $valida = $this->valida_token($user_id, $token);
        if (!$valida) {
            $m = 'Alguien ingresó con tu cuenta desde otro equipo vuelve a iniciar session';
            $state = 99;
            jsonReturn($state, $m);
        }

        $stmt = $this->_DB->prepare("SELECT
                                            limite_diario,
                                            limite_mensual,
                                            limite_semanal,
                                            fecha_fin
                                        FROM
                                            usuario_autoexclusion_apuesta
                                        WHERE usuario_id = :u");
        $stmt->execute(array(':u' => $user_id));
        if ($stmt->rowCount() == 1) {
            $result = array('state' => 1, 'msg' => 'Limites establecidos', 'datos' => $stmt->fetch(PDO::FETCH_OBJ));
        } else {
            $result = array('state' => 0, 'msg' => "Aun no se han establecidos limites.");
        }
        $this->_DB = null;
        echo json_encode($result);
    }

    /**
     * @param $user_id
     * @param $token
     */
    public
    function verLimiteDeposito($user_id, $token)
    {
        require_once '../requires/funciones.php';
        $valida = $this->valida_token($user_id, $token);
        if (!$valida) {
            $m = 'Alguien ingresó con tu cuenta desde otro equipo vuelve a iniciar session';
            $state = 99;
            jsonReturn($state, $m);
        }

        $stmt = $this->_DB->prepare("SELECT
                                            limitedep_diario AS limite_diario,
                                            limitedep_semanal AS limite_semanal,
                                            limitedep_mensual AS limite_mensual,
                                            fecha_limite_deposito AS fecha_fin
                                        FROM
                                            registro
                                        WHERE usuario_id = :u");
        $stmt->execute(array(':u' => $user_id));
        if ($stmt->rowCount() == 1) {
            $result = array('state' => 1, 'msg' => 'Limites establecidos', 'datos' => $stmt->fetch(PDO::FETCH_OBJ));
        } else {
            $result = array('state' => 0, 'msg' => "Aun no se han establecidos limites de deposito.");
        }
        $this->_DB = null;
        echo json_encode($result);
    }

    /**
     * @param $user_id
     * @param $token
     */
    public
    function verLimiteTiempo($user_id, $token)
    {
        require_once '../requires/funciones.php';
        $valida = $this->valida_token($user_id, $token);
        if (!$valida) {
            $m = 'Alguien ingresó con tu cuenta desde otro equipo vuelve a iniciar session';
            $state = 99;
            jsonReturn($state, $m);
        }

        $stmt = $this->_DB->prepare("SELECT
                                                           fecha_fin, DATEDIFF(fecha_fin, curdate()) dias
                                                        FROM
                                                          usuario_autoexclusion_juego
                                                        WHERE
                                                          usuario_id = 20 AND fecha_fin > NOW()
                                                        ORDER BY
                                                          fecha_fin DESC
                                                        LIMIT 1");
        $stmt->execute(array(':u' => $user_id));
        if ($stmt->rowCount() == 1) {
            $result = array('state' => 1, 'msg' => 'fecha para jugar en nuestra plataforma', 'datos' => $stmt->fetch(PDO::FETCH_OBJ));
        } else {
            $result = array('state' => 0, 'msg' => "Aun no se han establecidos limites de tiempo de juego.");
        }
        $this->_DB = null;
        echo json_encode($result);
    }

    /**
     * @param $usuario
     */
    public function cancelarCuenta($usuario)
    {
        require_once '../requires/global.php';
        require_once '../requires/funciones.php';
        $valida = $this->valida_token($usuario->user_id, $usuario->token);
        if (!$valida) {
            $m = 'Alguien ingresó con tu cuenta desde otro equipo vuelve a iniciar session';
            $state = 99;
            jsonReturn($state, $m);
        }

        $query_hash = $this->_DB->prepare("SELECT clave FROM usuario WHERE usuario_id = :id AND token_session=:t");
        $query_hash->execute(array(':id' => $usuario->user_id, ':t' => $usuario->token));
        $hash = $query_hash->fetch(PDO::FETCH_OBJ);
        $validar = $this->validar_password($hash->clave, $usuario->contrasena);

        if (isset($usuario->answer)) {
            if ($usuario->confirm != $usuario->answer) {
                $m = "Debe confirmar la decision en los dos checkbox";
                $s = 1;
                jsonReturn($s, $m);
            }
        } else {
            $m = "Debe confirmar la decision en los dos checkbox";
            $s = 2;
            jsonReturn($s, $m);
        }

        if (!$validar) {
            $m = "La contraseña ingresada no es correcta.";
            $s = 3;
            jsonReturn($s, $m);
        }

        /*$cuenta = $this->_DB->prepare("SELECT * FROM cuenta_cobro WHERE cuentabanco_id = ? AND usuario_id = ? AND estado = ? OR estado = ?");
        $cuenta->bindParam(1, $cuenta, PDO::PARAM_INT);
        $cuenta->bindParam(2, $usuario, PDO::PARAM_INT);
        $cuenta->bindValue(3, 'C', PDO::PARAM_STR);
        $cuenta->bindValue(4, 'A', PDO::PARAM_STR);
        $cuenta->execute();

        echo $cuenta->rowCount();Exit();*/

        /*if ($cuenta->rowCount() > 1) {
            $m = "No es posible cancelar la cuenta del usuario ya que el usuario tiene cuentas pendientes por cobrar o aprobar";
            $s = 4;
            jsonReturn($s, $m);
        }*/

        $saldo = $this->_DB->prepare("SELECT creditos_base, creditos FROM registro WHERE usuario_id = ? AND mandante = ?");
        $saldo->bindParam(1, $usuario->user_id, PDO::PARAM_INT);
        $saldo->bindValue(2, MANDANTE, PDO::PARAM_INT);
        $saldo->execute();
        $res = $saldo->fetch(PDO::FETCH_OBJ);

        if ($res->creditos_base > 0) {
            $m = "No es posible cancelar la cuenta del usuario ya que el usuario tiene un saldo de créditos por " . number_format($res->creditos_base);
            $s = 5;
            jsonReturn($s, $m);
        }

        if ($res->creditos > 0) {
            $m = "No es posible cancelar la cuenta del usuario ya que el usuario tiene un saldo de premio de " . number_format($res->creditos);
            $s = 6;
            jsonReturn($s, $m);
        }


        $doc_pendiente = $this->_DB->prepare("SELECT * FROM cuenta_cobro WHERE aprobacion = ? AND usuario_id = ? AND mandante = ?");
        $doc_pendiente->bindValue(1, 1, PDO::PARAM_INT);
        $doc_pendiente->bindParam(2, $usuario->user_id, PDO::PARAM_INT);
        $doc_pendiente->bindValue(2, MANDANTE, PDO::PARAM_INT);
        $doc_pendiente->execute();

        if ($doc_pendiente->rowCount() > 0) {
            $m = "No es posible cancelar la cuenta del usuario ya que el usuario tiene documentos de retiro pendientes por aprobar";
            $s = 7;
            jsonReturn($s, $m);
        }

        $stmt = $this->_DB->prepare("UPDATE usuario SET estado_jugador = :ej, estado = :i, estado_esp = :ep, fecha_retiro = :fr, hora_retiro = curTime() WHERE usuario_id = :u AND token_session =:t");
        $stmt->execute(array(':ej' => 'CT', ':u' => $usuario->user_id, ':i' => 'I', ':ep' => 'I', ':t' => $usuario->token, ':fr' => date('Y-m-d')));
        if ($stmt->rowCount() == 1) {
            $result = array('state' => 1, 'msg' => 'Su cuenta a sido cancelada.');
        } else {
            $result = array('state' => 0, 'msg' => 'Ocurrió un error inesperado en el sistema, inténtalo nuevamente si el problema persiste comuníquese con un administrador');
        }
        $this->_DB = null;
        echo json_encode($result);
    }

    /**
     * @param $user_id
     * @param $token
     */
    public
    function messages($user_id, $token)
    {
        require_once '../requires/funciones.php';
        $valida = $this->valida_token($user_id, $token);
        if (!$valida) {
            $m = 'Alguien ingresó con tu cuenta desde otro equipo vuelve a iniciar session';
            $state = 99;
            jsonReturn($state, $m);
        }

        $stmt = $this->_DB->prepare('SELECT
                                            leido
                                        FROM
                                            mensajes
                                        WHERE
                                           id_user = :u
                                        AND leido != 0
                                        ORDER BY
                                            leido ASC,
                                            fecha DESC');
        $stmt->execute(array(':u' => $user_id));
        if ($stmt->rowCount() > 0) {
            $result = array('state' => 1);
        } else {
            $result = array('state' => 0);
        }
        $this->_DB = null;
        echo json_encode($result);
    }

    /**
     * @param $user_id
     * @param $token
     */
    public
    function cargaMensajes($user_id, $token)
    {
        require_once '../requires/funciones.php';
        $valida = $this->valida_token($user_id, $token);
        if (!$valida) {
            $m = 'Alguien ingresó con tu cuenta desde otro equipo vuelve a iniciar session';
            $state = 99;
            jsonReturn($state, $m);
        }

        $stmt = $this->_DB->prepare('SELECT
                                                        id_message,
                                                        remitente,
                                                        leido,
                                                        fecha,
                                                        asunto,
                                                        texto
                                                    FROM
                                                        mensajes
                                                    WHERE
                                                        id_user = :u
                                                    ORDER BY
                                                        leido ASC,
                                            fecha DESC');
        $stmt->execute(array(':u' => $user_id));
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->_DB = null;
        echo json_encode($result);
    }


    /**
     * @param $user_id
     * @param $token
     * @param $id
     */
    public
    function Leer_mensajes($user_id, $token, $id)
    {
        require_once '../requires/funciones.php';
        $valida = $this->valida_token($user_id, $token);
        $state = '';
        if (!$valida) {
            $m = 'Alguien ingresó con tu cuenta desde otro equipo vuelve a iniciar session';
            $state = 99;
            jsonReturn($state, $m);
        }

        $this->_DB->beginTransaction();
        $mensaje = $this->_DB->prepare("UPDATE mensajes SET leido = :l WHERE id_message = :id");
        if (!$mensaje->execute(array(':l' => 1, ':id' => $id))) {
            $state = 'Error ' . $this->_DB->errorInfo();
        }

        if (!$state) {
            $this->_DB->commit();
            $stmt = $this->_DB->prepare('SELECT asunto, texto, leido
                                            FROM mensajes
                                            WHERE id_message = :id');
            $stmt->execute(array(':id' => $id));

            if ($stmt->rowCount() > 0) {
                $data = $stmt->fetch(PDO::FETCH_OBJ);
                $result = array('state' => 1, 'data' => $data);
            } else {
                $result = array('state' => 2, 'data' => 'No se encontraron datos');
            }

        } else {
            $this->_DB->rollBack();
            $result = array('state' => 0, 'data' => 'Ocurrió un error inesperado en el sistema, inténtalo nuevamente si el problema persiste comuníquese con un administrador');
        }
        $this->_DB = null;
        echo json_encode($result);
    }

    /**
     * @param $user_id
     * @param $token
     */
    public
    function consultaSaldoRetiros($user_id, $token)
    {
        require_once '../requires/funciones.php';
        $valida = $this->valida_token($user_id, $token);
        if (!$valida) {
            $m = 'Alguien ingresó con tu cuenta desde otro equipo vuelve a iniciar session';
            $state = 99;
            jsonReturn($state, $m);
        }

        $stmt = $this->_DB->prepare("SELECT
                                            r.creditos , r.creditos_base
                                        FROM
                                            registro r INNER JOIN configuracion c ON c.config_id = 1
                                        WHERE
                                            usuario_id = :u");
        $stmt->execute(array(':u' => $user_id));
        if ($stmt->rowCount() > 0) {

            $n_cuentas = $this->_DB->prepare("SELECT
                                                          u.id,
                                                          CONCAT(b.descripcion, ' - ', u.n_cuenta) cuenta
                                                        FROM usuario_banco u INNER JOIN banco b ON b.banco_id = u.banco_id
                                                          INNER JOIN usuario x ON x.usuario_id = u.usuario_id
                                                        WHERE x.usuario_id = :i AND u.estado = :e");
            $n_cuentas->execute(array(':i' => $user_id, ':e' => 'A'));
            $cuentas = $n_cuentas->fetchAll(PDO::FETCH_ASSOC);

            $pregunta = $this->_DB->prepare("SELECT
                                                    ps . id, ps . pregunta
                                                FROM
                                                    preguntas_seguridad ps
                                                INNER JOIN usuario_pregunta_seguridad up ON ps . id = up . id_pregunta
                                                INNER JOIN usuario u ON u . usuario_id = up . id_user
            AND u . usuario_id = :u
                                                ORDER BY
                                                    RAND()
                                                LIMIT 1");
            $pregunta->execute(array(':u' => $user_id));

            if ($pregunta->rowCount() > 0) {
                $res = $pregunta->fetch(PDO::FETCH_OBJ);
            } else {
                $res = array('id' => 0, 'pregunta' => 'Para realizar un retiro debe de responder las preguntas de seguridad.');
            }

            $data = $stmt->fetch(PDO::FETCH_OBJ);
            $result = array('state' => 1, 'data' => $data->creditos, 'creditoBase' => $data->creditos_base, 'cuentas' => $cuentas, 'pregunta' => $res);
        } else {
            $result = array('state' => 0, 'msg' => 'No se encontró saldo.');
        }
        $this->_DB = null;
        echo json_encode($result);
    }

    /**
     * @param $user_id
     */
    public
    function pregunta_nueva_contrasena($user_id)
    {
        $pregunta = $this->_DB->prepare("SELECT
                                                    ps.id, ps.pregunta
                                                FROM
                                                    preguntas_seguridad ps
                                                INNER JOIN usuario_pregunta_seguridad up ON ps . id = up . id_pregunta
                                                INNER JOIN usuario u ON u . usuario_id = up . id_user
            AND u . usuario_id = :u
                                                ORDER BY
                                                    RAND()
                                                LIMIT 1");
        $pregunta->execute(array(':u' => $user_id));

        if ($pregunta->rowCount() > 0) {
            $res = $pregunta->fetch(PDO::FETCH_OBJ);
        } else {
            $res = array('id' => 0, 'pregunta' => 'Para realizar un retiro debe de responder las preguntas de seguridad.');
        }

        $result = array('pregunta' => $res);
        $this->_DB = null;
        echo json_encode($result);
    }

    /**
     * @param $user
     */
    public function trabajeConNosotros($user)
    {
        require_once '../requires/global.php';
        require_once '../requires/funciones.php';

        if (!isset($user->captcha)) {
            $state = 59;
            $m = 'Captcha no valida';
        }

        $postdata = http_build_query(
            array(
                'secret' => '6Le7xQ8UAAAAAHv8ow4yOvdSGU8Xa2Wfx6mZDeri', //secret KEy provided by google
                'response' => $user->captcha,                    // g-captcha-response string sent from client
                'remoteip' => $_SERVER['REMOTE_ADDR']
            )
        );

//Build options for the post request
        $opts = array('http' =>
            array(
                'method' => 'POST',
                'header' => 'Content-type: application/x-www-form-urlencoded',
                'content' => $postdata
            )
        );

        $context = stream_context_create($opts);

        /* Send request to Googles siteVerify API*/
        $response = file_get_contents("https://www.google.com/recaptcha/api/siteverify", false, $context);

        $response = json_decode($response, true);

        if ($response["success"] === false) {
            $state = 65;
            $m = 'Captcha no valida';
            jsonReturn($state, $m);
        }

        if (!is_numeric($user->depto_id->depto_id)) {
            $state = 98;
            $msg = 'Datos invalidos';
            jsonReturn($state, $msg);
        }

        if (!filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
            $state = 97;
            $msg = 'Correo invalido';
            jsonReturn($state, $msg);
        }

        if (!is_numeric($user->documento)) {
            $state = 96;
            $msg = 'Datos invalidos';
            jsonReturn($state, $msg);
        }

        if (!is_numeric($user->ciudad_id->ciudad_id)) {
            $state = 95;
            $msg = 'Datos invalidos';
            jsonReturn($state, $msg);
        }

        $stmt = $this->_DB->prepare("INSERT INTO usuarios_trabaje_con_nosotros(nombre, apellido, documento, email, departamento, ciudad, direccion, telefono, movil, mensaje)
            VALUES(:nombre, :apellido, :documento, :email, :departamento, :ciudad, :dir, :telefono, :movil, :msg)");
        $stmt->execute(array(':nombre' => $user->nombre, ':apellido' => $user->apellido, ':documento' => $user->documento, ':email' => $user->email, ':departamento' => $user->depto_id->depto_id, ':ciudad' => $user->ciudad_id->ciudad_id, ':dir' => $user->dir, 'telefono' => $user->tel, ':movil' => $user->movil, ':msg' => $user->msg));
        if ($stmt->rowCount() == 1) {
            $result = array('state' => 1, 'msg' => 'El registro ha sido correcto nuestros operadores se contactaran con usted lo mas pronto posible.');
        } else {
            $result = array('state' => 2, 'msg' => 'error ' . $this->_DB->errorInfo());
        }
        $this->_DB = null;
        echo json_encode($result);
    }

    public
    function preguntas_seguridad()
    {
        $stmt = $this->_DB->prepare('SELECT DISTINCT * FROM preguntas_seguridad ORDER BY RAND() LIMIT 3');
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $result = array('state' => 1, 'data' => $data);
        } else {
            $result = array('state' => 0, 'data' => 'No se encontraron datos');
        }
        $this->_DB = null;
        echo json_encode($result);

    }

    /**
     * @param $usuario
     */
    public function guarda_preguntas_seguridad($usuario)
    {
        require_once '../requires/funciones.php';
        $valida = $this->valida_token($usuario->user_id, $usuario->token);
        if (!$valida) {
            $m = 'Alguien ingresó con tu cuenta desde otro equipo vuelve a iniciar session';
            $state = 99;
            jsonReturn($state, $m);
        }

        require_once '../requires/global.php';
        $pregunta = $this->_DB->prepare("SELECT up.id FROM usuario_pregunta_seguridad up INNER JOIN usuario u ON u . usuario_id = up . id_user WHERE u . usuario_id = :u");
        $pregunta->execute(array(':u' => $usuario->user_id,));
        if ($pregunta->rowCount() >= 3) {
            $s = 23;
            $m = 'Error el usuario ya tiene las preguntas de seguridad guardadas';
            jsonReturn($s, $m);
        }

        $error = '';
        $this->_DB->beginTransaction();

        $stmt = $this->_DB->prepare("INSERT INTO usuario_pregunta_seguridad(id_user, id_pregunta, respuesta) VALUES(:u, :ip, AES_ENCRYPT( :pregunta, '" . CLAVE_ENCRYPT . "'))");

        if (!$stmt->execute(array(':u' => $usuario->user_id, ':ip' => $usuario->p1, ':pregunta' => strtoupper($usuario->r1)))) {
            $error .= 'Error 1';
        }

        $stmt = $this->_DB->prepare("INSERT INTO usuario_pregunta_seguridad(id_user, id_pregunta, respuesta) VALUES(:u, :ip, AES_ENCRYPT( :pregunta, '" . CLAVE_ENCRYPT . "'))");

        if (!$stmt->execute(array(':u' => $usuario->user_id, ':ip' => $usuario->p2, ':pregunta' => strtoupper($usuario->r2)))) {
            $error .= 'Error 2';
        }

        $stmt = $this->_DB->prepare("INSERT INTO usuario_pregunta_seguridad(id_user, id_pregunta, respuesta) VALUES(:u, :ip, AES_ENCRYPT( :pregunta, '" . CLAVE_ENCRYPT . "'))");

        if (!$stmt->execute(array(':u' => $usuario->user_id, ':ip' => $usuario->p3, ':pregunta' => strtoupper($usuario->r3)))) {
            $error .= 'Error 3';
        }

        if ($error == '') {
            $this->_DB->commit();

            //add 3dementes
            $this->updateSession('pregunta_seguridad', 1);

            $result = array('state' => 1, 'msg' => 'Las preguntas de seguridad se han guardado correctamente, si por alguna razón las olvida, debe comunicarse con nuestros operadores para restaurarlas nuevamente.');
        } else {
            $this->_DB->rollBack();
            $result = array('state' => 0, 'msg' => $error);
        }
        $this->_DB = null;
        echo json_encode($result);
    }

    /**
     * @param $user_id
     * @param $token
     */
    public
    function limite_dias($user_id, $token)
    {
        require_once '../requires/funciones.php';
        $valida = $this->valida_token($user_id, $token);
        if (!$valida) {
            $m = 'Alguien ingresó con tu cuenta desde otro equipo vuelve a iniciar session';
            $state = 99;
            jsonReturn($state, $m);
        }
        $stmt = $this->_DB->prepare("SELECT
                                        id,
                                        valor
                                    FROM
                                        limites_autoexclusion
                                    WHERE
                                        clase = :c
            AND tipo = :t ORDER BY valor ASC");
        $stmt->execute(array(':c' => 'T', ':t' => 0));
        $result = array('state' => 1, 'dia' => $stmt->fetchAll(PDO::FETCH_ASSOC));
        $this->_DB = null;
        echo json_encode($result);
    }

    /**
     * @param $user_id
     * @param $token
     */
    public
    function limite_tiempo($user_id, $token)
    {
        require_once '../requires/funciones.php';
        $valida = $this->valida_token($user_id, $token);
        if (!$valida) {
            $m = 'Alguien ingresó con tu cuenta desde otro equipo vuelve a iniciar session';
            $state = 99;
            jsonReturn($state, $m);
        }
        $stmt = $this->_DB->prepare("SELECT id, valor FROM limites_autoexclusion
                                    WHERE clase = :c AND tipo = :t ORDER BY valor ASC");
        $stmt->execute(array(':c' => 'A', ':t' => 'D'));
        $result1 = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $this->_DB->prepare("SELECT id, valor FROM limites_autoexclusion
                                    WHERE clase = :c AND tipo = :t ORDER BY valor ASC");
        $stmt->execute(array(':c' => 'A', ':t' => 'S'));
        $result2 = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $this->_DB->prepare("SELECT id, valor FROM limites_autoexclusion
                                    WHERE clase = :c AND tipo = :t ORDER BY valor ASC");
        $stmt->execute(array(':c' => 'A', ':t' => 'M'));
        $result3 = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = array('dia' => $result1, 'semana' => $result2, 'mes' => $result3);
        $this->_DB = null;
        echo json_encode($result);
    }

    /**
     * @param $user_id
     * @param $token
     */
    public
    function limite_deposito($user_id, $token)
    {
        require_once '../requires/funciones.php';
        $valida = $this->valida_token($user_id, $token);
        if (!$valida) {
            $m = 'Alguien ingresó con tu cuenta desde otro equipo vuelve a iniciar session';
            $state = 99;
            jsonReturn($state, $m);
        }
        $stmt = $this->_DB->prepare("SELECT id, valor FROM limites_autoexclusion
                                    WHERE clase = :c AND tipo = :t ORDER BY valor ASC");
        $stmt->execute(array(':c' => 'D', ':t' => 'D'));
        $result1 = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $this->_DB->prepare("SELECT id, valor FROM limites_autoexclusion
                                    WHERE clase = :c AND tipo = :t ORDER BY valor ASC");
        $stmt->execute(array(':c' => 'D', ':t' => 'S'));
        $result2 = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $this->_DB->prepare("SELECT id, valor FROM limites_autoexclusion
                                    WHERE clase = :c AND tipo = :t ORDER BY valor ASC");
        $stmt->execute(array(':c' => 'D', ':t' => 'M'));
        $result3 = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = array('dia' => $result1, 'semana' => $result2, 'mes' => $result3);
        $this->_DB = null;
        echo json_encode($result);
    }

    public function valida_email_preregistro($mail)
    {
        require_once '../requires/global.php';
        require_once '../requires/funciones.php';

        $mail = base64_decode($mail);
        $mail = explode('/', $mail);
        $error = '';
        $this->_DB->beginTransaction();
        $stmt = $this->_DB->prepare("SELECT estado_link FROM preregistro WHERE mail = ? AND id = ?");
        $stmt->bindParam(1, strtoupper($mail[0]), PDO::PARAM_STR);
        $stmt->bindParam(2, $mail[1], PDO::PARAM_INT);
        $stmt->execute();
        if ($stmt->rowCount() != 1) {
            $msg = "El correo ingresado no se encuentra en nuestra base de datos";
            $state = 1;
            jsonReturn($state, $msg);
        }

        $dato = $stmt->fetch(PDO::FETCH_OBJ);

        if ($dato->estado_link == 1) {
            $msg = "Esta operación ya se ha realizado";
            $state = 2;
            jsonReturn($state, $msg);
        }

        $activa = $this->_DB->prepare("UPDATE preregistro SET fecha_activa_correo = NOW(), estado_link = ? WHERE mail = ? AND id = ?");
        $activa->bindValue(1, 1, PDO::PARAM_INT);
        $activa->bindParam(2, strtoupper($mail[0]), PDO::PARAM_STR);
        $activa->bindParam(3, $mail[1], PDO::PARAM_STR);

        if (!$activa->execute()) {
            $msg = "No ha sido posible cambiar el estado del usuario.";
            $state = 3;
            jsonReturn($state, $msg);
        }

        if (!$state) {
            $this->_DB->commit();
            $re = "¡Felicitaciones,\n
                        Tú información ha sido Verificada exitosamente! \n
                        Ya eres parte de las Segundas Olimpiadas Wplay.co y te deseamos suerte de campeón. \n
                        Recuerda que el usuario es tu correo electrónico y la clave la que elegiste al inscribirte";
            $result = array('state' => 1, 'msg' => $re);
        } else {
            $this->_DB->rollBack();
            $error = "No pudimos comprobar tus datos exitosamente, pero ¡RELÁJATE! Inscríbete nuevamente a nuestras
            Segundas Olimpiadas Wplay.co ¡Ten cuidado, ingresa tus datos correctamente!";
            $result = array('state' => 0, 'msg' => 'Ha ocurrido el siguiente error: ' . $error);
        }
        echo json_encode($result);
    }

    /**
     * @param $user_id
     * @param $token
     * @return bool
     */
    private
    function valida_token($user_id, $token)
    {
        require_once '../requires/funciones.php';
        if (!is_numeric($user_id)) {
            $m = 'La identificación del usuario no es valida';
            $state = 88;
            jsonReturn($state, $m);
        }
        $stmt = $this->_DB->prepare("SELECT * FROM usuario WHERE usuario_id = :u AND token_session = :t");
        $stmt->execute(array(':u' => $user_id, ':t' => $token));
        if ($stmt->rowCount() == 1) {
            return true;
        } else {
            return false;
        }
    }

}
