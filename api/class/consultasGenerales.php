<?php

require_once 'conexion.php';
date_default_timezone_set('America/Bogota');

class ConsultasGenerales
{
    private $_DB;

    public function __construct()
    {
        $this->_DB = new Conection;
    }

    /**
     * @param $user_id
     * @param $token
     * @return bool
     */
    private function valida_token($user_id, $token)
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

    /**
     * función que trae todos los paises
     */
    public function paises()
    {
        $stmt = $this->_DB->query("SELECT pais_id, pais_nom FROM pais ORDER BY pais_nom");
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($result);
    }

    /**
     * función que trae los departamentos
     */

    public function departamentos($data)
    {
        $stmt = $this->_DB->prepare('SELECT
                                        depto_id,
                                        depto_nom
                                    FROM
                                        departamento
                                    WHERE
                                        pais_id = :id_pais
                                    ORDER BY
                                        depto_nom');
        $stmt->execute(array(':id_pais' => $data));
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($result);
    }

    /**
     * @param $data variable con el id del departamento que deseamos consultar sus ciudades
     * esta función trae las ciudades de x departamento
     */
    public function ciudad($data)
    {
        $stmt = $this->_DB->prepare("SELECT
                                            a.ciudad_id,
                                            a.ciudad_nom
                                        FROM
                                            ciudad a
                                        INNER JOIN departamento b ON (a.depto_id = b.depto_id)
                                        WHERE
                                            a.depto_id = :id
                                        ORDER BY
                                            a.ciudad_nom ASC");
        $stmt->execute(array(':id' => $data->depto_id));
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($result);
    }

    /**
     * @param $data contiene el id del departamento que deseamos consultar sus códigos postales
     * esta función trae los códigos postales de x departamento
     */
    public function ciudadesCodigos($data)
    {
        $stmt = $this->_DB->prepare("SELECT a.ciudad_id, a.ciudad_nom FROM ciudad a WHERE depto_id = :id");
        $stmt->execute(array(':id' => $data->depto_id));
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt1 = $this->_DB->prepare("SELECT
                                        id_codigo_postal,
                                        CONCAT(ciudad_municipio,' --- ',codigo_postal) codigos
                                    FROM
                                        codigo_postal
                                    WHERE
                                        id_departamento = :id
                                    ORDER BY
                                        ciudad_municipio");
        $stmt1->execute(array(':id' => $data->depto_id));
        $result1 = $stmt1->fetchAll(PDO::FETCH_ASSOC);

        $data = array('ciudades' => $result, 'codigos' => $result1);
        echo json_encode($data);
    }

    /**
     * @param $datos objeto con los datos del usuario que deseamos preregistrar
     * esta función realiza el prerégistro de los usuarios
     */
    public function preRegistro($datos)
    {
        try {
            require_once '../requires/global.php';
            require_once '../requires/funciones.php';
            $error = '';
            $this->_DB->beginTransaction();

            $sNom = !(empty($datos->sNom)) ? $datos->sNom : "";
            $sApe = !(empty($datos->sApe)) ? $datos->sApe : "";
            $fVence = !(empty($datos->fVence)) ? $datos->fVence : '1980-01-01';
            $fijo = !(empty($datos->fijo)) ? $datos->fijo : '';
            $referido = !(empty($datos->referido)) ? $datos->referido : 0;
            $segmento = !(empty($datos->segmento)) ? $datos->segmento : 0;

            $nombre = $datos->pNom . ' ' . $sNom . ' ' . $datos->pApe . ' ' . $sApe;

            $valid = $this->_DB->prepare("SELECT * FROM preregistro WHERE mail = ?");
            $valid->bindParam(1, strtoupper($datos->mail), PDO::PARAM_STR);
            $valid->execute();
            if ($valid->rowCount() > 0) {
                $state = 89;
                $m = 'El usuario ya se encuentra registrado';
                jsonReturn($state, $m);
            }

            $validDoc = $this->_DB->prepare("SELECT * FROM preregistro WHERE numDoc = ?");
            $validDoc->bindParam(1, $datos->numDoc, PDO::PARAM_INT);
            $validDoc->execute();
            if ($validDoc->rowCount() > 0) {
                $state = 88;
                $m = 'El usuario ya se encuentra registrado';
                jsonReturn($state, $m);
            }

            $MailExiste = $this->MailExiste($datos->mail);
            $cedulaExiste = $this->cedulaExiste($datos->numDoc);
            $validaExclusion = $this->validaExclusion($datos->numDoc);

            if ($segmento !== 0) {
                if (!is_numeric($segmento)) {
                    $m = 'El código del segmento no debe contener letras.';
                    $s = 23;
                    jsonReturn($s, $m);
                    return false;
                }

                $val_segmento = $this->validaSegmento($segmento);

                if (!$val_segmento) {
                    $m = 'El código del segmento no se encuentra en nuestra base de datos.';
                    $s = 24;
                    jsonReturn($s, $m);
                    return false;
                }
            }

            if ($referido == 0) {
                $referido = 0;
            } else {
                if (!is_numeric($referido)) {
                    $m = 'El código del referido no debe contener letras.';
                    $s = 25;
                    jsonReturn($s, $m);
                    return false;
                }

                $validareferido = $this->validaReferido($referido);
                if (!$validareferido) {
                    $m = 'El código del referido no se encuentra en nuestra base de datos.';
                    $s = 26;
                    jsonReturn($s, $m);
                    return false;
                }
            }

            if (!$validaExclusion) {
                $m = 'El documento ingresado se encuentra en la lista de exclusion.';
                $s = 27;
                jsonReturn($s, $m);
                return false;
            }

            if (!$MailExiste) {
                $m = 'El correo electrónico ya se encuentra registrado.';
                $s = 28;
                jsonReturn($s, $m);
                return false;
            }

            if (!$cedulaExiste) {
                $m = 'El documento de identidad ya se encuentra registrado.';
                $s = 29;
                jsonReturn($s, $m);
                return false;
            }

            $sentencia = $this->_DB->prepare("INSERT INTO preregistro (tDoc, numDoc, fExp, depart_exp, ciudad_exp,fVence, pNom, sNom, pApe, sApe, sexo, fnace, depart_res, pais, ciudad_res, dir, codP, mail, movil, fijo, clave_reg, t_and_c, created_at, estado, mandante, referido_id, depart_nac, ciudad_nac, segmento, tipo_registro) VALUES (:tDoc, :numDoc, :fExp, :depart_exp, :ciud_exp, :fVence, AES_ENCRYPT(:pNom, '" . CLAVE_ENCRYPT . "'), AES_ENCRYPT(:sNom, '" . CLAVE_ENCRYPT . "'), AES_ENCRYPT(:pApe, '" . CLAVE_ENCRYPT . "'), AES_ENCRYPT(:sApe, '" . CLAVE_ENCRYPT . "'), :sexo, :fnace, :depart_res, :nacionalidad, :ciud_res, AES_ENCRYPT(:dir, '" . CLAVE_ENCRYPT . "'), :codP, :mail, AES_ENCRYPT(:movil, '" . CLAVE_ENCRYPT . "'), AES_ENCRYPT(:fijo, '" . CLAVE_ENCRYPT . "'),
            :clave_reg, :t_and_c, NOW(), :estado, :mandante, :referido_id, :depart_nac, :ciud_nac, :segmento, :tipo)");

            $sentencia->execute(array(
                ':tDoc' => $datos->tDoc,
                ':numDoc' => $datos->numDoc,
                ':fExp' => $datos->fExp,
                ':depart_exp' => $datos->depart_exp->depto_id,
                ':ciud_exp' => $datos->ciud_exp->ciudad_id,
                ':fVence' => $fVence,
                ':pNom' => $datos->pNom,
                ':sNom' => $sNom,
                ':pApe' => $datos->pApe,
                ':sApe' => $sApe,
                ':sexo' => $datos->sexo,
                ':fnace' => $datos->fnace,
                ':depart_res' => $datos->depart_res->depto_id,
                ':nacionalidad' => $datos->nacionalidad->id,
                ':ciud_res' => $datos->ciud_res->ciudad_id,
                ':dir' => $datos->dir,
                ':codP' => $datos->codP->id_codigo_postal,
                ':mail' => strtoupper($datos->mail),
                ':movil' => $datos->movil,
                ':fijo' => $fijo,
                ':clave_reg' => $this->password($datos->clave_reg),
                ':t_and_c' => $datos->t_and_c,
                ':estado' => 'PENDIENTE',
                ':mandante' => MANDANTE,
                ':referido_id' => $validareferido,
                ':depart_nac' => $datos->depart_nac->depto_id,
                ':ciud_nac' => $datos->ciudad_nac->ciudad_id,
                ':segmento' => $segmento,
                ':tipo' => 1
            ));

            if ($sentencia->rowCount() != 1) {
                $state = 85;
                $m = 'Error registrando el usuario';
                jsonReturn($state, $m);
            }

            if ($error == "") {
                $this->_DB->commit();
                $res = array("state" => 1, "msg" => "El usuario se ha registrado correctamente nuestros operadores validaran la información una vez verificada te enviaremos un correo de bienvenida o de rechazo.");
            } else {
                $this->_DB->rollBack();
                $res = array('state' => 0, "msg" => $error);
            }
            echo json_encode($res);
        } catch (PDOException $e) {
            echo 'Exception -> ' . $this->_DB->errorInfo();
        }

    }

    public function updateRegistro($user_id, $token, $datos)
    {
        try {
            require_once '../requires/funciones.php';
            $valida = $this->valida_token($user_id, $token);
            if (!$valida) {
                $m = 'Alguien ingresó con tu cuenta desde otro equipo vuelve a iniciar session';
                $state = 99;
                jsonReturn($state, $m);
            }

            require_once '../requires/global.php';
            $state = '';
            $this->_DB->beginTransaction();

            $sNom = !(empty($datos->sNom)) ? $datos->sNom : "";
            $sApe = !(empty($datos->sApe)) ? $datos->sApe : "";
            $fVence = !(empty($datos->fVence)) ? $datos->fVence : '1980-01-01';
            $fijo = !(empty($datos->fijo)) ? $datos->fijo : '';
            $referido = !(empty($datos->referido)) ? $datos->referido : '0';
            $segmento = !(empty($datos->segmento)) ? $datos->segmento : '0';

            $nombre = $datos->pNom . ' ' . $sNom . ' ' . $datos->pApe . ' ' . $sApe;

            $doc = $this->_DB->prepare("SELECT usuario_id, preregistro_id, email FROM registro WHERE usuario_id = ?");
            $doc->bindParam(1, $user_id, PDO::PARAM_INT);
            $doc->execute();
            if ($doc->rowCount() != 1) {
                $msg = "No se encontró el registro del usuario con cc # " . $datos->numDoc;
                $state = 2;
                jsonReturn($state, $msg);
            }

            $res = $doc->fetch(PDO::FETCH_OBJ);

            $sentencia = $this->_DB->prepare("UPDATE preregistro SET tDoc = ?, fExp = ?, depart_exp = ?, ciudad_exp = ?,fVence = ?, sexo = ?, fnace = ?, depart_res = ?, pais = ?, ciudad_res = ?, dir = AES_ENCRYPT(?, '" . CLAVE_ENCRYPT . "'), codP = ?, movil = AES_ENCRYPT(?, '" . CLAVE_ENCRYPT . "'), fijo = AES_ENCRYPT(?, '" . CLAVE_ENCRYPT . "'), clave_reg = ?, t_and_c = ?, depart_nac = ?, ciudad_nac = ?, tipo_registro = ?, updated_at = NOW(), aceptaterminos_at = NOW() WHERE id= ?");

            $clave = $this->password($datos->clave_reg);

            $sentencia->bindParam(1, $datos->tDoc, PDO::PARAM_INT);
            $sentencia->bindParam(2, $datos->fExp, PDO::PARAM_STR);
            $sentencia->bindParam(3, $datos->depart_exp->depto_id, PDO::PARAM_INT);
            $sentencia->bindParam(4, $datos->ciud_exp->ciudad_id, PDO::PARAM_INT);
            $sentencia->bindParam(5, $fVence, PDO::PARAM_STR);
            $sentencia->bindParam(6, $datos->sexo, PDO::PARAM_INT, 1);
            $sentencia->bindParam(7, $datos->fnace, PDO::PARAM_STR);
            $sentencia->bindParam(8, $datos->depart_res->depto_id, PDO::PARAM_STR);
            $sentencia->bindParam(9, $datos->nacionalidad->id, PDO::PARAM_INT);
            $sentencia->bindParam(10, $datos->ciud_res->ciudad_id, PDO::PARAM_INT);
            $sentencia->bindParam(11, $datos->dir, PDO::PARAM_INT);
            $sentencia->bindParam(12, $datos->codP->id_codigo_postal, PDO::PARAM_INT);
            $sentencia->bindParam(13, $datos->movil, PDO::PARAM_INT);
            $sentencia->bindParam(14, $fijo, PDO::PARAM_INT);
            $sentencia->bindParam(15, $clave, PDO::PARAM_STR);
            $sentencia->bindParam(16, $datos->t_and_c, PDO::PARAM_INT);
            $sentencia->bindParam(17, $datos->depart_nac->depto_id, PDO::PARAM_INT);
            $sentencia->bindParam(18, $datos->ciudad_nac->ciudad_id, PDO::PARAM_INT);
            $sentencia->bindValue(19, 1, PDO::PARAM_INT);
            $sentencia->bindParam(20, $res->preregistro_id, PDO::PARAM_INT);
            $sentencia->execute();

            if ($sentencia->rowCount() != 1) {
                $msg = "No se posible actualizar el registro";
                $state = 3;
                jsonReturn($state, $msg);
            }

            /*echo $datos->movil . '/' . $datos->ciud_res->ciudad_id . '/' . $clave . '/' . $datos->fnace . '/' . $datos->dir . '/' . $datos->nacionalidad->id . '/' . $datos->sexo . '/' . $datos->tDoc . '/' . $datos->fExp . '/' . $datos->depart_exp->depto_id . '/' . $datos->ciud_exp->ciudad_id . '/' . $datos->depart_res->depto_id . '/' . $datos->ciud_res->ciudad_id . '/' . $datos->depart_nac->depto_id . '/' . $datos->ciudad_nac->ciudad_id . '/' . $datos->codP->id_codigo_postal . '/' . $fijo . '/' . $user_id;exit();*/

            $q = $this->_DB->prepare("UPDATE registro r INNER JOIN usuario u ON r.usuario_id = u.usuario_id
                  INNER JOIN usuario_otrainfo uo ON uo.usuario_id = u.usuario_id
                SET r.celular = AES_ENCRYPT(?, '" . CLAVE_ENCRYPT . "'), r.ciudad_id = ?, u.clave = ?, uo.fecha_nacim = ?,
                  uo.direccion = AES_ENCRYPT(?, '" . CLAVE_ENCRYPT . "'), uo.pais = ?, uo.sexo = ?, uo.tipo_doc = ?, uo.fecha_exp = ?,
                  uo.depart_exp = ?, uo.ciudad_exp = ?, uo.depart_res = ?, uo.ciudad_res = ?, uo.depart_nac = ?, uo.ciudad_nac = ?,
                  uo.cod_postal = ?, uo.fijo = AES_ENCRYPT(?, '" . CLAVE_ENCRYPT . "')
                WHERE u.usuario_id = ?");

            $q->bindParam(1, $datos->movil, PDO::PARAM_INT);
            $q->bindParam(2, $datos->ciud_res->ciudad_id, PDO::PARAM_INT);
            $q->bindParam(3, $clave, PDO::PARAM_STR);
            $q->bindParam(4, $datos->fnace, PDO::PARAM_STR);
            $q->bindParam(5, $datos->dir, PDO::PARAM_STR);
            $q->bindParam(6, $datos->nacionalidad->id, PDO::PARAM_INT);
            $q->bindParam(7, $datos->sexo, PDO::PARAM_INT);
            $q->bindParam(8, $datos->tDoc, PDO::PARAM_INT);
            $q->bindParam(9, $datos->fExp, PDO::PARAM_STR);
            $q->bindParam(10, $datos->depart_exp->depto_id, PDO::PARAM_INT);
            $q->bindParam(11, $datos->ciud_exp->ciudad_id, PDO::PARAM_INT);
            $q->bindParam(12, $datos->depart_res->depto_id, PDO::PARAM_INT);
            $q->bindParam(13, $datos->ciud_res->ciudad_id, PDO::PARAM_INT);
            $q->bindParam(14, $datos->depart_nac->depto_id, PDO::PARAM_INT);
            $q->bindParam(15, $datos->ciudad_nac->ciudad_id, PDO::PARAM_INT);
            $q->bindParam(16, $datos->codP->id_codigo_postal, PDO::PARAM_INT);
            $q->bindParam(17, $fijo, PDO::PARAM_INT);
            $q->bindParam(18, $user_id, PDO::PARAM_INT);
            $q->execute();

            if ($q->rowCount() == 0) {
                $msg = "No fue posible actualizar el usuario" . $this->_DB->errorInfo();
                $state = 4;
                jsonReturn($state, $msg);
            }

            /*//Inserta el nuevo registro en la base de datos que contiene la información complementaria
            $usuarioOtraInfo = $this->_DB->prepare("UPDATE usuario_otrainfo SET fecha_nacim = ?, direccion = ?, pais = ?, sexo = ?, tipo_doc = ?, fecha_exp = ?, depart_exp = ?, ciudad_exp = ?, depart_res = ?, ciudad_res = ?, depart_nac = ?, ciudad_nac = ?, cod_postal = ?, fijo = ? WHERE usuario_id = ?");

            $usuarioOtraInfo->bindParam(1, $datos->fnace, PDO::PARAM_STR);
            $usuarioOtraInfo->bindParam(2, $datos->dir, PDO::PARAM_STR);
            $usuarioOtraInfo->bindParam(3, $datos->nacionalidad->id, PDO::PARAM_INT);
            $usuarioOtraInfo->bindParam(4, $datos->sexo, PDO::PARAM_INT);
            $usuarioOtraInfo->bindParam(5, $datos->tDoc, PDO::PARAM_INT);
            $usuarioOtraInfo->bindParam(6, $datos->fExp, PDO::PARAM_STR);
            $usuarioOtraInfo->bindParam(7, $datos->depart_exp->depto_id, PDO::PARAM_INT);
            $usuarioOtraInfo->bindParam(8, $datos->ciud_exp->ciudad_id, PDO::PARAM_INT);
            $usuarioOtraInfo->bindParam(9, $datos->depart_res->depto_id, PDO::PARAM_INT);
            $usuarioOtraInfo->bindParam(10, $datos->ciud_res->ciudad_id, PDO::PARAM_INT);
            $usuarioOtraInfo->bindParam(11, $datos->depart_nac->depto_id, PDO::PARAM_INT);
            $usuarioOtraInfo->bindParam(12, $datos->ciudad_nac->ciudad_id, PDO::PARAM_INT);
            $usuarioOtraInfo->bindParam(13, $datos->codP->id_codigo_postal, PDO::PARAM_INT);
            $usuarioOtraInfo->bindParam(14, $fijo, PDO::PARAM_INT);
            $usuarioOtraInfo->bindParam(15, $user_id, PDO::PARAM_INT);
            $usuarioOtraInfo->execute();

            if ($usuarioOtraInfo->rowCount() != 1) {
                $msg = "No fue posible guardar la otra información del usuario.";
                $state = 6;
                jsonReturn($state, $msg);
            }*/

            $mensage = $this->_DB->prepare("INSERT INTO mensajes (para, remitente, leido, fecha, asunto, texto, id_user) VALUES (?, ?, ?, NOW(), ?, ?, ?)");
            $mensage->bindParam(1, $res->email, PDO::PARAM_STR);
            $mensage->bindValue(2, 'wplay', PDO::PARAM_STR);
            $mensage->bindValue(3, 0, PDO::PARAM_STR);
            $mensage->bindValue(4, 'WPlay', PDO::PARAM_STR);
            $mensage->bindValue(5, 'Gracias por actualizar tus datos', PDO::PARAM_STR);
            $mensage->bindParam(6, $user_id, PDO::PARAM_INT);
            $mensage->execute();

            if ($mensage->rowCount() != 1) {
                $msg = "No fue posible enviar el mensaje al usuario.";
                $state = 7;
                jsonReturn($state, $msg);
            }

            if (!$state) {
                $this->updateSession('tipo_registro', 1);
                $this->_DB->commit();
                $res = array("state" => 1, "msg" => "Gracias por actualizar tus datos,\n te damos la bienvenida a wplay.co");
            } else {
                $this->_DB->rollBack();
                $res = array('state' => 0, "msg" => $state);
            }
            echo json_encode($res);
        } catch (PDOException $e) {
            echo 'Exception -> ' . $this->_DB->errorInfo();
        }
    }

    public function updateFecha($data)
    {
        require_once '../requires/funciones.php';
        $valida = $this->valida_token($data->usuario_id, $data->token);
        if (!$valida) {
            $m = 'Alguien ingresó con tu cuenta desde otro equipo vuelve a iniciar session';
            $state = 99;
            jsonReturn($state, $m);
        }

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

        $id = $this->_DB->prepare("SELECT preregistro_id FROM registro WHERE usuario_id = ?");
        $id->bindParam(1, $data->usuario_id, PDO::PARAM_INT);
        $id->execute();

       /* if ($id->rowCount() != 1) {
            $m = 'Error usuario no encontrado';
            $state = 98;
            jsonReturn($state, $m);
        }*/

        $res_id = $id->fetch(PDO::FETCH_OBJ);
        $this->_DB->beginTransaction();

        $q = $this->_DB->prepare("UPDATE preregistro SET fnace = ?, corrige_fecha = ? WHERE id = ?");
        $q->bindParam(1, $data->fnace, PDO::PARAM_STR);
        $q->bindValue(2, 'N', PDO::PARAM_STR);
        $q->bindParam(3, $res_id->preregistro_id, PDO::PARAM_STR);
        $q->execute();

        /*if ($id->rowCount() != 1) {
            $m = 'Error usuario no encontrado - fecha';
            $state = 97;
            jsonReturn($state, $m);
        }*/

        $u = $this->_DB->prepare("UPDATE usuario_otrainfo SET fecha_nacim = ? WHERE usuario_id = ?");
        $u->bindParam(1, $data->fnace, PDO::PARAM_STR);
        $u->bindParam(2, $data->usuario_id, PDO::PARAM_INT);
        $u->execute();

       /* if ($u->rowCount() != 1) {
            $m = 'Error usuario no encontrado';
            $state = 96;
            jsonReturn($state, $m);
        }*/

        session_start();
        $_SESSION['c_fecha'] = 'N';

        if (!$state) {
            $res = array('state' => 1, 'msg' => 'Gracias por actualizar los datos');
            $this->_DB->commit();
        } else {
            $res = array('state' => 0, 'msg' => 'No pudimos actualizar el campo intentalo nuevamente');
            $this->_DB->rollBack();
        }
        $this->_DB = null;
        echo json_encode($res);
    }

    /**
     * @return string retorna un hash para generar la key de la contraseña
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
     * [password función que le asigna un hash a la contraseña]
     * @param  [type] $pass [password para encriptar]
     * @return [type]       [el password encriptado]
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
     * @param $mail variable con el email a verificar
     * @return bool
     * funcion que retorna true o false si el correo ya se encuentra activo en la tabla usuario
     */
    public function MailExiste($mail)
    {
        include_once '../requires/global.php';
        $stmt = $this->_DB->prepare("SELECT login FROM usuario WHERE login = :mail AND estado_jugador = :e");
        $stmt->execute(array(':mail' => strtoupper($mail), ':e' => 'AC'));
        if ($stmt->rowCount() > 0) {
            $result = false;
        } else {
            $result = true;
        }
        return $result;

    }

    public function validaExclusion($numDoc)
    {
        require_once '../requires/global.php';
        $query = $this->_DB->prepare("SELECT fecha_hora FROM usuario_excluidos WHERE cc = ?");
        $query->bindParam(1, $numDoc, PDO::PARAM_INT);
        $query->execute();
        if ($query->rowCount() > 0) {
            $result = 0;
        } else {
            $result = 1;
        }
        return $result;
    }

    public function validaSegmento($segmento)
    {
        $stmt = $this->_DB->prepare("SELECT usuario_id FROM usuario_perfil WHERE usuario_id = ? AND perfil_id LIKE 'PUNTO%'");
        $stmt->bindParam(1, $segmento, PDO::PARAM_INT);
        $stmt->execute();
        if ($stmt->rowCount() == 1) {
            $result = 1;
        } else {
            $result = 0;
        }
        return $result;
    }

    public function validaReferido($refer)
    {
        $stmt = $this->_DB->prepare("SELECT usuario_id FROM registro WHERE cedula = ?");
        $stmt->bindParam(1, $refer, PDO::PARAM_INT);
        $stmt->execute();
        if ($stmt->rowCount() == 1) {
            $res = $stmt->fetch(PDO::FETCH_OBJ);
            $result = $res->usuario_id;
        } else {
            $result = 0;
        }
        return $result;
    }

    public function in_array_strpos($word, $array)
    {

        foreach ($array as $a) {

            if (strpos($word, $a) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $doc variable con la cc a verificar
     * @return bool
     * funcion que retorna true o false si la cc ya se encuentra activo en la tabla registro
     */
    public function cedulaExiste($doc)
    {
        include_once '../requires/global.php';
        $stmt = $this->_DB->prepare("SELECT
                                            r.cedula
                                        FROM
                                            registro r
                                        INNER JOIN usuario u ON u.usuario_id = r.usuario_id
                                        WHERE
                                            r.cedula = :mail
                                        AND u.estado_jugador = :e");
        $stmt->execute(array(':mail' => $doc, ':e' => 'AC'));
        if ($stmt->rowCount() > 0) {
            $result = false;
        } else {
            $result = true;
        }
        return $result;
    }

    /**
     * @param $id variable con el id del usuario para consultar su saldo disponible
     * esta funcion consulta el saldo del usuario conectado
     */
    public function saldo_usuario($user_id, $token)
    {
        require_once '../requires/global.php';
        try {
            $valida = $this->valida_token($user_id, $token);
            if ($valida) {
                $saldo = $this->_DB->prepare("SELECT
                                            creditos saldo,
                                            creditos_base creditoParticipacion,
                                            creditos_bono
                                        FROM
                                            registro
                                        WHERE
                                            usuario_id = :u");
                $saldo->execute(array(':u' => $user_id));

                $men = $this->_DB->prepare("SELECT
                                            COUNT(m.leido) leido
                                        FROM
                                            mensajes m
                                        WHERE
                                            id_user = :u
                                        AND m.leido = 0");
                $men->execute(array(':u' => $user_id));

                $saldo = $saldo->fetch(PDO::FETCH_OBJ);
                $m = $men->fetch(PDO::FETCH_OBJ);

                $result = array('state' => 1, 'saldo' => $saldo->saldo, 'creditoParticipacion' => $saldo->creditoParticipacion, 'saldoBono' => $saldo->creditos_bono, 'msg' => $m->leido);

            } else {
                $result = array('state' => 99, 'msg' => 'alguien ingresó con tu cuenta desde otro equipo vuelve a iniciar session');
            }
            echo json_encode($result);
        } catch (PDOException $e) {
            echo json_encode($e->getMessage());
        }
    }

    public function getPoints()
    {
        $q = $this->_DB->query("SELECT latitud, longitud, telefono, direccion, nombre_contacto, descripcion, barrio FROM punto_venta WHERE latitud != '' AND longitud != ''");
        $q->execute();
        $result = $q->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($result);
    }


}
