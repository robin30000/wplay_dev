<?php

/**
 * Created by PhpStorm.
 * User: Robin
 * Date: 2/02/2017
 * Time: 2:08 PM
 */
require_once 'conexion.php';

class CuentaCobro
{
    private $_DB;

    public function __construct()
    {
        $this->_DB = new Conection();
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

    public function cuenta($user_id, $token)
    {
        require_once '../requires/funciones.php';
        $valida = $this->valida_token($user_id, $token);
        if (!$valida) {
            $m = 'Alguien ingresó con tu cuenta desde otro equipo vuelve a iniciar session';
            $state = 3;
            jsonReturn($state, $m);
        }

        require_once "../requires/global.php";
        $consulta = $this->_DB->prepare("SELECT
                                            a.cuenta_id,
                                            CONCAT(a.fecha_crea,' ', a.hora_crea) AS fecha,
                                            a.valor + a.valor_retencion AS valor_bruto,
                                            a.valor,
                                            a.valor_retencion,
                                            CASE a.tipo
                                                WHEN 1 THEN 'Bancario'
                                                /*WHEN 2 THEN 'Tarjeta Crédito'
                                                WHEN 3 THEN 'Instrumento Ofrecido Por Entidad'
                                                WHEN 4 THEN 'Transferencia Giro'
                                                WHEN 5 THEN 'Tarjeta Prepago'*/
                                                WHEN 6 THEN 'Efectivo'
                                                /*WHEN 7 THEN 'Otro Medio'*/
                                            END tipo,
                                                CASE a.estado 
                                                WHEN 'I' THEN 'Pagada'
                                                WHEN 'P' THEN 'Pendiente aprobación'
                                                WHEN 'C' THEN 'Cancelada por usuario'
                                                WHEN 'A' THEN 'Pendiente pago' END estado,
                                            CASE a.tipo_saldo
                                            WHEN 0 THEN 'Saldo premios' ELSE 'Saldo créditos'
                                            END tipo_saldo,
                                            a.valor_retencion
                                            FROM
                                                cuenta_cobro a
                                            INNER JOIN usuario u ON a.usuario_id = u.usuario_id
                                            WHERE
                                            a.tipo = 6
                                            AND u.usuario_id = :u
                                            AND u.estado_jugador = 'AC'
                                            AND (a.estado = 'A' OR a.estado = 'P')
                                            AND u.estado = 'A' AND a.mandante = " . MANDANTE . "
                                            ORDER BY
                                                a.cuenta_id DESC");
        $consulta->execute(array(':u' => $user_id));

        $response = $consulta->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($response);
    }

    public function accountAll($user_id, $token)
    {
        require_once '../requires/funciones.php';
        $valida = $this->valida_token($user_id, $token);
        if (!$valida) {
            $m = 'Alguien ingresó con tu cuenta desde otro equipo vuelve a iniciar session';
            $state = 3;
            jsonReturn($state, $m);
        }

        require_once "../requires/global.php";
        $consulta = $this->_DB->prepare("SELECT
                                            a.cuenta_id,
                                            CONCAT(a.fecha_crea,' ', a.hora_crea) AS fecha,
                                            a.valor + a.valor_retencion AS valor_bruto,
                                            a.valor,
                                            a.valor_retencion,
                                            CASE a.tipo
                                                WHEN 1 THEN 'Bancario'
                                                /*WHEN 2 THEN 'Tarjeta Crédito'
                                                WHEN 3 THEN 'Instrumento Ofrecido Por Entidad'
                                                WHEN 4 THEN 'Transferencia Giro'
                                                WHEN 5 THEN 'Tarjeta Prepago'*/
                                                WHEN 6 THEN 'Efectivo'
                                                /*WHEN 7 THEN 'Otro Medio'*/
                                            END tipo,
                                                CASE a.estado 
                                                WHEN 'I' THEN 'Pagada'
                                                WHEN 'P' THEN 'Pendiente aprobación'
                                                WHEN 'C' THEN 'Cancelada por usuario'
                                                WHEN 'A' THEN 'Pendiente pago' END estado,
                                            CASE a.tipo_saldo
                                            WHEN 0 THEN 'Saldo premios' ELSE 'Saldo créditos'
                                            END tipo_saldo,
                                            a.valor_retencion
                                            FROM
                                                cuenta_cobro a
                                            INNER JOIN usuario u ON a.usuario_id = u.usuario_id
                                            WHERE
                                            u.usuario_id = :u
                                            AND u.estado_jugador = 'AC'
                                            AND (a.estado = 'A' OR a.estado = 'P')
                                            AND u.estado = 'A' AND a.mandante = " . MANDANTE . "
                                            ORDER BY
                                                a.cuenta_id DESC");
        $consulta->execute(array(':u' => $user_id));

        $response = $consulta->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($response);
    }

    public function cancelDocCuentaCobro($user_id, $token, $cuenta_id)
    {
        require_once '../requires/funciones.php';
        $valida = $this->valida_token($user_id, $token);
        if (!$valida) {
            $m = 'Alguien ingresó con tu cuenta desde otro equipo vuelve a iniciar session';
            $state = 3;
            jsonReturn($state, $m);
        }
        require_once '../requires/global.php';
        $q = $this->_DB->prepare("SELECT estado, tipo_saldo, (valor_retencion + valor) AS valor_restaurar FROM cuenta_cobro WHERE usuario_id = ? AND mandante = ? AND cuenta_id = ?");
        $q->bindParam(1, $user_id, PDO::PARAM_INT);
        $q->bindValue(2, MANDANTE, PDO::PARAM_INT);
        $q->bindParam(3, $cuenta_id, PDO::PARAM_INT);
        $q->execute();
        if ($q->rowCount() != 1) {
            $m = 'No se encontraron cuentas con los datos enviados';
            $state = 4;
            jsonReturn($state, $m);
        }

        $res = $q->fetch(PDO::FETCH_OBJ);

        if ($res->estado == 'C') {
            $m = 'El documento de retiro # ' . $cuenta_id . ' se encuentra cancelado por el usuario';
            $state = 4;
            jsonReturn($state, $m);
        }

        if (($res->estado == 'I') AND ($res->aprobacion == 0 || $res->aprobacion == 2)) {
            $m = 'El documento de retiro # ' . $cuenta_id . ' ya ha sido cobrado por el usuario';
            $state = 4;
            jsonReturn($state, $m);
        }

        $this->_DB->beginTransaction();

        /**
         * 0 saldo de premios
         * 1 créditos de participación
         */

        switch ($res->tipo_saldo) {
            case 0 :
                $query = $this->_DB->prepare("UPDATE registro SET creditos = creditos + ? WHERE usuario_id = ?");
                $query->bindParam(1, $res->valor_restaurar, PDO::PARAM_INT);
                $query->bindParam(2, $user_id, PDO::PARAM_INT);
                $query->execute();
                break;
            case 1 :
                $query = $this->_DB->prepare("UPDATE registro SET creditos_base = creditos_base + ? WHERE usuario_id = ?");
                $query->bindParam(1, $res->valor_restaurar, PDO::PARAM_INT);
                $query->bindParam(2, $user_id, PDO::PARAM_INT);
                $query->execute();
                break;
        }

        if ($query->rowCount() != 1) {
            $m = 'Ha ocurrido un error interno inténtalo nuevamente si el problema persiste comunicarse con un administrador del sistema';
            $state = 5;
            jsonReturn($state, $m);
        }

        $estado = $this->_DB->prepare("UPDATE cuenta_cobro SET estado = ?, fecha_cancela_doc = NOW() WHERE cuenta_id = ?");
        $estado->bindValue(1, 'C', PDO::PARAM_STR);
        $estado->bindParam(2, $cuenta_id, PDO::PARAM_INT);
        $estado->execute();

        if ($estado->rowCount() != 1) {
            $m = 'Ha ocurrido un error interno inténtalo nuevamente si el problema persiste comunicarse con un administrador del sistema';
            $state = 6;
            jsonReturn($state, $m);
        }

        if (!$state) {
            $this->_DB->commit();
            $result = array('state' => 1, 'msg' => 'El documento de retiro ' . $cuenta_id . ' ha sido cancelado por el usuario');
        } else {
            $this->_DB->rollBack();
            $result = array('state' => 1, 'msg' => 'Ocurrió un error inesperado en el sistema, inténtalo nuevamente si el problema persiste comuníquese con un administrador');
        }
        $this->_DB = null;
        echo json_encode($result);
    }
}
