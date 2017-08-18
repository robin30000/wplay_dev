<?php

require_once 'conexion.php';
error_reporting(0);

/**
 * Class cuentaCobro
 */
class cuentaCobro
{

    private $_DB;
    public $pregunta;
    public $formaPago;
    public $preguntaSeguridad;
    public $cuenta_banco_id;
    public $user;
    public $token;
    public $valor;

    public function __construct($user)
    {
        $this->_DB = new Conection();
        $this->pregunta = $user->pregunta;
        $this->preguntaSeguridad = $user->preguntaSeguridad;
        $this->formaPago = $user->formaPagos->value;
        $this->tipoSaldo = $user->tipoSaldo->value;
        $this->cuenta_banco_id = $user->cuenta;
        $this->user = $user->id;
        $this->token = $user->token;
        $this->valor = $user->valor;

    }

    private function valida_token($user_id, $token)
    {
        $stmt = $this->_DB->prepare("SELECT * FROM usuario WHERE usuario_id = :u AND token_session = :t");
        $stmt->execute(array(':u' => $user_id, ':t' => $token));
        if ($stmt->rowCount() == 1) {
            return true;
        } else {
            return false;
        }
    }

    public function guardaCuentaCobro()
    {
        require_once "../requires/global.php";
        require_once "../requires/funciones.php";

        if ($this->cuenta_banco_id == '') {
            $this->cuenta_banco_id = 0;
        }

        $valorDocRetiro = puntos($this->valor);

        $this->valor = puntos($this->valor);
        if (!is_numeric($this->valor)) {
            $m = 'El valor a retirar no es valido';
            $state = 1;
            jsonReturn($state, $m);
        }

        if ($this->valor <= 0) {
            $m = 'El valor a retirar no es valido';
            $state = 2;
            jsonReturn($state, $m);
        }

        if ($this->cuenta_banco_id != 0) {
            $q = $this->_DB->query("SELECT retiro_min_bancario AS retiro_min_bancario FROM configuracion WHERE config_id = 1");
            $q->execute();
            $res_q = $q->fetch(PDO::FETCH_OBJ);
            if ($this->valor < $res_q->retiro_min_bancario) {
                $m = 'El valor mínimo para realizar retiros por transferencia bancaria es de ' . number_format($res_q->retiro_min_bancario);
                $state = 6;
                jsonReturn($state, $m);
            }
        }

        $valida = $this->valida_token($this->user, $this->token);

        if (!$valida) {
            $m = 'Alguien ingresó con tu cuenta desde otro equipo vuelve a iniciar session';
            $state = 99;
            jsonReturn($state, $m);
        }

        $verifica_pregunta = $this->_DB->prepare("SELECT * FROM usuario_pregunta_seguridad WHERE id_user = :u AND id_pregunta = :id_p AND aes_decrypt(respuesta, '" . CLAVE_ENCRYPT . "') = :r");
        $verifica_pregunta->execute(array(':u' => $this->user, ':id_p' => $this->preguntaSeguridad, ':r' => strtoupper($this->pregunta)));

        if ($verifica_pregunta->rowCount() == 0) {
            $m = 'La respuesta de la pregunta de seguridad esta equivocada. inténtelo nuevamente';
            $state = 2;
            jsonReturn($state, $m);
        }

        switch ($this->tipoSaldo) {
            case 0:
                //Valida si el usuario tiene saldo de premios para retirar
                $validaSql = $this->_DB->prepare("SELECT a.creditos disponible FROM registro a WHERE a.mandante= :m AND a.usuario_id= :u");
                $validaSql->execute(array('m' => MANDANTE, ':u' => $this->user));
                break;
            case 1:
                //Valida si el usuario tiene creditos base para retirar
                $validaSql = $this->_DB->prepare("SELECT a.creditos_base disponible FROM registro a WHERE a.mandante= :m AND a.usuario_id= :u");
                $validaSql->execute(array('m' => MANDANTE, ':u' => $this->user));
                break;
            default:
                # code...
                break;
        }

        if ($validaSql->rowCount() != 1) {
            $m = 'No tiene el saldo suficiente para realizar este retiro.';
            $state = 3;
            jsonReturn($state, $m);
        }

        $valida_RS = $validaSql->fetch(PDO::FETCH_OBJ);

        if (floatval($valida_RS->disponible) < floatval($this->valor) or floatval($this->valor) <= 0) {
            $m = "No tiene saldo suficiente para realizar un retiro por el valor de " . number_format($this->valor);
            $state = 4;
            jsonReturn($state, $m);
        }

        //Valida que el usuario contenga por lo menos una recarga
        $valida2Sql = $this->_DB->prepare("SELECT recarga_id FROM usuario_recarga WHERE mandante= :m AND usuario_id= :u LIMIT 1");
        $valida2Sql->execute(array(':u' => $this->user, ':m' => MANDANTE));

        if ($valida2Sql->rowCount() == 0) {
            $m = 'Hemos detectado que nunca ha realizado una recarga por lo tanto su operación no puede ser procesada.';
            $state = 5;
            jsonReturn($state, $m);
        }

        //consulta para conocer los topes del documento de retiro
        $topes = $this->_DB->prepare("SELECT cant_max_notas_ret_x_dia_usu, val_max_notas_ret_cobra_x_dia_usu, val_max_una_nota_ret, requiere_aprobacion FROM configuracion WHERE config_id = ?");
        $topes->bindValue(1, 1, PDO::PARAM_INT);
        $topes->execute();
        $resTopes = $topes->fetch(PDO::FETCH_OBJ);

        //Verifica el tope de retiro minimo para el usuario
        $limites = $this->_DB->prepare("SELECT SUM(valor) cupo, COUNT(cuenta_id) filas FROM cuenta_cobro WHERE usuario_id = ? AND fecha_crea = curdate() AND estado = ?");
        $limites->bindParam(1, $this->user, PDO::PARAM_INT);
        $limites->bindValue(2, 'A', PDO::PARAM_STR);
        $limites->execute();
        $resLimites = $limites->fetch(PDO::FETCH_OBJ);

        //valida la cantidad de notas generadas por dia
        if ($resLimites->filas >= $resTopes->cant_max_notas_ret_x_dia_usu) {
            $m = 'El numero maximo de documentos de retiro por dias es de ' . $resTopes->cant_max_notas_ret_x_dia_usu;
            $state = 6;
            jsonReturn($state, $m);
        }
        //valida que el valor del documento no sea mayor que el permitido
        if ($this->valor > $resTopes->val_max_una_nota_ret) {
            $m = 'El valor maximo por documento de retiro es de ' . number_format($resTopes->val_max_una_nota_ret);
            $state = 7;
            jsonReturn($state, $m);
        }
        //valida que el cupo por dia no sea sobrepasado
        if ($resLimites->cupo + $this->valor > $resTopes->val_max_notas_ret_cobra_x_dia_usu) {
            $m = 'El cupo maximo para retirar por dia es de ' . number_format($resTopes->val_max_notas_ret_cobra_x_dia_usu);
            $state = 8;
            jsonReturn($state, $m);
        }

        $aprobacion = ($this->valor >= $resTopes->requiere_aprobacion) ? 1 : 0;
        $estado_cuenta = ($this->valor >= $resTopes->requiere_aprobacion) ? 'I' : 'A';

        //Busca el consecutivo para la recarga
        $cuenta_id = 0;

        $consecSql = $this->_DB->prepare("SELECT a.numero FROM consecutivo a WHERE a.tipo = :r FOR UPDATE");
        $consecSql->execute(array(':r' => 'RET'));
        if (!($consecSql->rowCount() == 0)) {
            $consec_RS = $consecSql->fetch(PDO::FETCH_OBJ);
            $cuenta_id = $consec_RS->numero;
        }

        $this->_DB->beginTransaction();

        $strSql = $this->_DB->prepare("UPDATE consecutivo SET numero=numero+1 WHERE tipo= :r");
        if (!$strSql->execute(array(':r' => 'RET'))) {
            $m = 'No se actualizo el consecutivo';
            $state = 9;
            jsonReturn($state, $m);
        }

        $quantityOrig = $this->valor;

        if ($this->tipoSaldo == 0) {
            $topeRete = $this->_DB->prepare("SELECT porcen_iva, porcen_retencion FROM configuracion WHERE config_id = :ci AND mandante = :m");
            $topeRete->execute(array(':m' => MANDANTE, ':ci' => 1));
            if ($topeRete->rowCount() == 1) {
                $resTopeRete = $topeRete->fetch(PDO::FETCH_OBJ);
                if ($this->valor >= $resTopeRete->porcen_iva) {
                    $valorRetencion = porcentaje($this->valor, $resTopeRete->porcen_retencion);
                    $porcentajeRetencion = $resTopeRete->porcen_retencion;
                    $this->valor = $this->valor - $valorRetencion;
                } else {
                    $valorRetencion = 0;
                    $porcentajeRetencion = 0;
                }
            } else {
                $m = 'Error Configuración';
                $state = 10;
                jsonReturn($state, $m);
            }
        } elseif ($this->tipoSaldo == 1) {
            $topeRete = $this->_DB->prepare("SELECT porcen_retrecarga FROM configuracion WHERE config_id = 1 AND mandante = 0");
            $topeRete->execute();
            if ($topeRete->rowCount() == 1) {
                $resTopeRete = $topeRete->fetch(PDO::FETCH_OBJ);
                $valorRetencion = porcentaje($this->valor, $resTopeRete->porcen_retrecarga);
                $porcentajeRetencion = $resTopeRete->porcen_retrecarga;
                $this->valor = $this->valor - $valorRetencion;
            } else {
                $m = 'Error Configuración 1';
                $state = 11;
                jsonReturn($state, $m);
            }
        }

        //Genera la clave para el retiro
        $clave = GenerarClaveTicket(5);
        $dir_ip = ObtenerIP();

        if ($aprobacion) {
            $estado_cuenta = 'P';
        }

            //Actualiza la cantidad de creditos recargados para el usuario especifico
            $contSql = $this->_DB->prepare("INSERT INTO cuenta_cobro (cuenta_id,usuario_id,fecha_crea,valor,estado,clave,mandante,dir_ip,cuentabanco_id, tipo, tipo_saldo, porcen_retencion, valor_retencion, hora_crea) VALUES (?, ?, CURDATE(), ?, ?, aes_encrypt(?,'" . CLAVE_ENCRYPT . "'), ?, ?, ?, ?, ?, ?, ?, CURTIME())");
        $contSql->bindParam(1, $cuenta_id, PDO::PARAM_INT);
        $contSql->bindParam(2, $this->user, PDO::PARAM_INT);
        $contSql->bindParam(3, $this->valor, PDO::PARAM_INT);
        $contSql->bindParam(4, $estado_cuenta, PDO::PARAM_STR, 1);
        $contSql->bindParam(5, $clave, PDO::PARAM_STR, 5);
        $contSql->bindValue(6, MANDANTE, PDO::PARAM_INT);
        $contSql->bindParam(7, $dir_ip, PDO::PARAM_STR, 20);
        $contSql->bindParam(8, $this->cuenta_banco_id, PDO::PARAM_INT);
        $contSql->bindParam(9, $this->formaPago, PDO::PARAM_INT);
        $contSql->bindParam(10, $this->tipoSaldo, PDO::PARAM_INT);
        $contSql->bindParam(11, $porcentajeRetencion, PDO::PARAM_INT);
        $contSql->bindParam(12, $valorRetencion, PDO::PARAM_INT);
        $contSql->execute();
        if ($contSql->rowCount() != 1) {
            $m = 'Se encontraron inconsistencias al generar el documento de retiro.';
            $state = 12;
            jsonReturn($state, $m);
        }

        //Actualiza el saldo de retiro
        switch ($this->tipoSaldo) {
            case 0:
                //Actualiza el saldo créditos
                $strSql1 = $this->_DB->prepare("UPDATE registro SET creditos_ant=creditos,creditos=creditos- :v WHERE mandante= :m AND usuario_id= :u");
                if (!$strSql1->execute(array(':u' => $this->user, ':m' => MANDANTE, ':v' => $valorDocRetiro))) {
                    $m = "No pudo actualizarse el saldo";
                    $state = 13;
                    jsonReturn($state, $m);
                }
                break;
            case 1:
                //Actualiza el saldo créditos base
                $strSql1 = $this->_DB->prepare("UPDATE registro SET creditos_base_ant=creditos_base,creditos_base=registro.creditos_base- :v WHERE mandante= :m AND usuario_id= :u");
                if (!$strSql1->execute(array(':u' => $this->user, ':m' => MANDANTE, ':v' => $valorDocRetiro))) {
                    $m = "No pudo actualizarse el saldo";
                    $state = 14;
                    jsonReturn($state, $m);
                }
                break;
            default:
                echo 'error opcion';
                break;
        }

        if (!$state) {

            $aprobacion_cuenta = $this->_DB->prepare("SELECT estado FROM cuenta_cobro WHERE cuenta_id = ? AND usuario_id = ? AND  mandante = ?");
            $aprobacion_cuenta->bindParam(1, $cuenta_id, PDO::PARAM_INT);
            $aprobacion_cuenta->bindParam(2, $this->user, PDO::PARAM_INT);
            $aprobacion_cuenta->bindValue(3, MANDANTE, PDO::PARAM_INT);
            $aprobacion_cuenta->execute();
            $cuenta_aprueba = $aprobacion_cuenta->fetch(PDO::FETCH_OBJ);

            /*if ($cuenta_aprueba->estado != 'I') {

            }*/

            $infoSql = $this->_DB->prepare("SELECT
                                                    CONCAT(a.fecha_crea,' ',a.hora_crea) AS fecha_crea, 
                                                    AES_DECRYPT(a.clave, '" . CLAVE_ENCRYPT . "') clave,
                                                    a.valor,
                                                    r.cedula,
                                                    AES_DECRYPT(b.nombre, '" . CLAVE_ENCRYPT . "') nombre,
                                                    tipo,
                                                    CASE a.estado WHEN 'A' THEN 'Aprobada' ELSE 'Requiere aprobación' END estado, a.valor_retencion
                                                FROM
                                                    cuenta_cobro a
                                                INNER JOIN usuario b ON (
                                                    a.mandante = b.mandante
                                                    AND a.usuario_id = b.usuario_id
                                                )
                                                INNER JOIN registro r ON r.usuario_id = b.usuario_id
                                                WHERE
                                                    a.mandante = :m
                                                AND a.cuenta_id = :cu
                                                AND a.usuario_id = :u
                                                AND a.impresa = 'N'");

            $infoSql->execute(array(':cu' => $cuenta_id, ':m' => MANDANTE, ':u' => $this->user));

            if ($infoSql->rowCount() == 1) {

                $info_RS = $infoSql->fetch(PDO::FETCH_OBJ);
                $fecha_crea = $info_RS->fecha_crea;
                $clave = $info_RS->clave;
                $valor = $info_RS->valor;
                $tipo = $info_RS->tipo;
                $valor_retencion = $info_RS->valor_retencion;
                $cuenta_txt = str_pad($cuenta_id, 10, "0", STR_PAD_LEFT);
                $medio = ($tipo == 1) ? 'Consignación Bancaria' : 'Efectivo';

                $html = new stdClass();
                $html->Documento_N = $cuenta_txt;
                $html->Medio_de_pago = $medio;
                $html->valorRetencion = $valor_retencion;
                $html->porcentajeRetencion = $porcentajeRetencion;

                if ($tipo == 1) {
                    $infoBanco = $this->_DB->prepare("SELECT
                                                                    b.n_cuenta,
                                                                    b.tipo_cuenta,
                                                                    ba.descripcion
                                                                FROM
                                                                    usuario_banco b
                                                                INNER JOIN cuenta_cobro c ON b.id = c.cuentabanco_id
                                                                INNER JOIN banco ba ON ba.banco_id = b.banco_id
                                                                WHERE
                                                                    c.cuenta_id = :cu");
                    $infoBanco->execute(array(':cu' => $cuenta_id));
                    $res = $infoBanco->fetch(PDO::FETCH_OBJ);

                    $html->Banco = $res->descripcion;
                    $html->Numero_Cuenta = $res->n_cuenta;
                    $html->Tipo_Cuenta = $res->tipo_cuenta;
                }

                $html->Fecha = $fecha_crea;
                $html->quantityOrig = $quantityOrig;

                if ($this->tipoSaldo == 1) {
                    $html->saldoType = 'Saldo créditos';
                    $html->retencion = 'Costo admin';
                }

                if ($this->tipoSaldo == 0) {
                    $html->saldoType = 'Saldo premios';
                    $html->retencion = 'Retefuente';
                }

                if ($this->formaPago == 6) {
                    $html->efecty = 1;
                    $html->clave = $clave;
                    $pie = $this->_DB->query("SELECT pie_nota FROM configuracion WHERE config_id = 1");
                    $pie->execute();
                    $resPie = $pie->fetch(PDO::FETCH_OBJ);
                    $html->pie_nota = $resPie->pie_nota;
                } else {
                    $html->reBank = 1;
                }
                $html->Valor_retirar = $valor;
                $this->_DB->commit();
                $result = array('state' => 1, 'msg' => 'Registro ok', 'cuenta' => $cuenta_id, 'html' => $html);
            } else {
                $result = array('state' => 0, 'msg' => 'Ocurrió un error inesperado en el sistema, inténtalo nuevamente si el problema persiste comuníquese con un administrador');
            }
        } else {
            $this->_DB->rollBack();
            $result = array('state' => 2, 'msg' => 'Ocurrió un error inesperado en el sistema, inténtalo nuevamente si el problema persiste comuníquese con un administrador" ');
        }
        $this->_DB = null;
        echo json_encode($result);
    }

    public function inprimeCuenta($cuenta_id, $user)
    {
        require_once '../requires/funciones.php';
        require_once '../requires/global.php';
        $infoSql = $this->_DB->prepare("SELECT
                                                    CONCAT(a.fecha_crea,' ',a.hora_crea) AS fecha_crea, 
                                                    AES_DECRYPT(a.clave, '" . CLAVE_ENCRYPT . "') clave,
                                                    a.valor,
                                                    a.valor_retencion,
                                                    r.cedula,
                                                    AES_DECRYPT(b.nombre, '" . CLAVE_ENCRYPT . "') nombre,
                                                    tipo,
                                                    CASE a.estado WHEN 'A' THEN 'Aprobada' ELSE 'Requiere aprobación' END estado
                                                FROM
                                                    cuenta_cobro a
                                                INNER JOIN usuario b ON (
                                                    a.mandante = b.mandante
                                                    AND a.usuario_id = b.usuario_id
                                                )
                                                INNER JOIN registro r ON r.usuario_id = b.usuario_id
                                                WHERE
                                                    a.mandante = :m
                                                AND a.cuenta_id = :cu
                                                AND a.usuario_id = :u
                                                AND a.impresa = 'N'");

        $infoSql->execute(array(':cu' => $cuenta_id, ':m' => MANDANTE, ':u' => $user));

        if ($infoSql->rowCount() == 1) {
            $info_RS = $infoSql->fetch(PDO::FETCH_OBJ);
            $fecha_crea = $info_RS->fecha_crea;
            $clave = $info_RS->clave;
            $valor = $info_RS->valor;
            $tipo = $info_RS->tipo;
            $cuenta_txt = str_pad($cuenta_id, 10, "0", STR_PAD_LEFT);
            $medio = ($tipo == 1) ? 'Consignación Bancaria' : 'Efectivo';
            $valor_retencion = $info_RS->valor_retencion;

            $html = new stdClass();
            $html->Documento_N = $cuenta_txt;
            $html->Medio_de_pago = $medio;
            $html->valorRetencion = $valor_retencion;

            if ($tipo == 1) {
                $infoBanco = $this->_DB->prepare("SELECT
                                                                    b.n_cuenta,
                                                                    b.tipo_cuenta,
                                                                    ba.descripcion
                                                                FROM
                                                                    usuario_banco b
                                                                INNER JOIN cuenta_cobro c ON b.id = c.cuentabanco_id
                                                                INNER JOIN banco ba ON ba.banco_id = b.banco_id
                                                                WHERE
                                                                    c.cuenta_id = :cu");
                $infoBanco->execute(array(':cu' => $cuenta_id));
                $res = $infoBanco->fetch(PDO::FETCH_OBJ);

                $html->Banco = $res->descripcion;
                $html->Numero_Cuenta = $res->n_cuenta;
                $html->Tipo_Cuenta = $res->tipo_cuenta;
            }

            $html->Fecha = $fecha_crea;
            $html->quantityOrig = $valor;

            if ($this->tipoSaldo == 1) {
                $html->saldoType = 'Créditos participación';
                $html->retencion = 'Descuento admin: ';
            }

            if ($this->tipoSaldo == 0) {
                $html->saldoType = 'Saldo premios';
                $html->retencion = 'Retefuente';
            }

            if ($this->formaPago == 6) {
                $html->efecty = 1;
                $html->clave = $clave;
            } else {
                $html->reBank = 1;
            }
            $html->Valor_retirar = $valor;
        }
    }
}
