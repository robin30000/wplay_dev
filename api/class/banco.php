<?php

require_once 'conexion.php';

class Banco
{

    private $_DB;

    public function __construct()
    {
        $this->_DB = new Conection;
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

    /**
     * funcion que trae todos los bancos
     */
    public function bancos()
    {
        try {
            /*require_once '../requires/funciones.php';
            $valida = $this->valida_token($user_id, $token);
            if (!$valida) {
                $m = 'Alguien ingresó con tu cuenta desde otro equipo vuelve a iniciar session';
                $state = 99;
                jsonReturn($state, $m);
            }*/
            $sql = $this->_DB->prepare('SELECT * FROM banco');
            $sql->execute();
            $result = $sql->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($result);
        } catch (Exception $e) {
            echo json_encode($e->getMessage());

        }
    }

    /**
     * @param $datos = objeto con los datos para realizar la consulta
     * esta function se encarga de registrar las cuentas bancarias por usuario
     */
    public function bankAccount($user)
    {
        try {
            require_once '../requires/funciones.php';
            $valida = $this->valida_token($user->id, $user->token_session);
            if (!$valida) {
                $m = 'Alguien ingresó con tu cuenta desde otro equipo vuelve a iniciar session';
                $state = 99;
                jsonReturn($state, $m);
            }

            if (strlen($user->cBancaria) > 19 AND strlen($user->cBancaria < 6)) {
                $m = 'El numero de dígitos de la cuenta no son correctos.';
                $state = 98;
                jsonReturn($state, $m);
            }

            $cuentas = $this->_DB->prepare("SELECT tipo_cuenta
                                                                FROM
                                                                  usuario_banco ub
                                                                  INNER JOIN usuario u ON u.usuario_id = ub.usuario_id
                                                                WHERE
                                                                  u.usuario_id = ? AND ub.estado = ?");
            $cuentas->bindParam(1, $user->id, PDO::PARAM_INT);
            $cuentas->bindValue(1, 'A', PDO::PARAM_INT);
            $cuentas->execute();

            if ($cuentas->rowCount() > 2) {
                $m = 'Por usuario solo se le es permitido tener máximo 2 cuentas registradas.';
                $state = 97;
                jsonReturn($state, $m);
            }

            $banco = $this->_DB->prepare("SELECT
                                                        ub.banco_id
                                                    FROM
                                                        usuario_banco ub
                                                    INNER JOIN usuario u ON u.usuario_id = ub.usuario_id
                                                    WHERE
                                                        ub.n_cuenta = :nc
                                                    AND u.usuario_id = :u
                                                                AND ub.banco_id = :b");
            $banco->execute(array(':nc' => $user->cBancaria, ':u' => $user->id, ':b' => $user->banco));
            if ($banco->rowCount() == 1) {
                $m = 'Esta cuenta ya se encuentra registrada.';
                $state = 96;
                jsonReturn($state, $m);
            }

            $stmt = $this->_DB->prepare('INSERT INTO usuario_banco ( usuario_id, banco_id, tipo_cuenta, tipo_cuenta_usuario, n_cuenta) VALUES (?, ?, ?, ?, ?)');
            $stmt->bindParam(1, $user->id, PDO::PARAM_INT);
            $stmt->bindParam(2, $user->banco, PDO::PARAM_INT);
            $stmt->bindParam(3, $user->tCuenta->option, PDO::PARAM_INT);
            $stmt->bindParam(4, $user->conTCliente->option, PDO::PARAM_INT);
            $stmt->bindParam(5, $user->cBancaria, PDO::PARAM_STR);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                $result = array('state' => 1, 'msg' => "Cuenta registrada.");
            } else {
                $result = array('state' => 2, 'msg' => "Ocurrió un error interno por favor vuelve a intentarlo." . $this->_DB->errorInfo());
            }
            echo json_encode($result);
        } catch (Exception $e) {
            echo json_encode($e->getMessage());
        }
    }

    /**
     * @param $user_id
     * @param $token
     */
    public function getBankAccount($user_id, $token)
    {
        try {
            require_once '../requires/funciones.php';
            $valida = $this->valida_token($user_id, $token);
            if (!$valida) {
                $m = 'Alguien ingresó con tu cuenta desde otro equipo vuelve a iniciar session';
                $state = 99;
                jsonReturn($state, $m);
            }
            $stmt = $this->_DB->prepare("SELECT
                                                      ub.id,
                                                      b.descripcion,
                                                      ub.n_cuenta,
                                                      CASE ub.tipo_cuenta
                                                      WHEN 1
                                                        THEN 'Ahorros'
                                                      ELSE 'Corriente' END tipo_cuenta,
                                                      CASE ub.estado WHEN 'P' THEN 'Pendiente' WHEN 'A' THEN 'Activa' WHEN 'C' THEN 'Cancelada' END estado
                                                    FROM
                                                      usuario_banco ub
                                                      INNER JOIN banco b ON b.banco_id = ub.banco_id
                                                      INNER JOIN usuario u ON u.usuario_id = ub.usuario_id
                                                    WHERE
                                                      u.usuario_id = ? AND ub.estado != ?");
            $stmt->bindParam(1, $user_id, PDO::PARAM_INT);
            $stmt->bindValue(2, 'C', PDO::PARAM_STR);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                $result = array('state' => 1, 'dato' => $stmt->fetchAll(PDO::FETCH_ASSOC));
            } else {
                $result = array('state' => 0, 'msg' => "No se encontraron cuentas registradas.");
            }
            echo json_encode($result);
        } catch (Exception $e) {
            echo json_encode($e->getMessage());
        }
    }

    public function cliente_cuenta()
    {
        /*require_once '../requires/funciones.php';
        $valida = $this->valida_token($user_id, $token);
        if (!$valida) {
            $m = 'Alguien ingresó con tu cuenta desde otro equipo vuelve a iniciar session';
            $state = 99;
            jsonReturn($state, $m);
        }*/

        $cliente = $this->_DB->prepare("SELECT * FROM tipo_cliente_banco");
        $cliente->execute();
        $res_cliente = $cliente->fetchAll(PDO::FETCH_ASSOC);

        $cuenta = $this->_DB->prepare("SELECT * FROM tipo_cuenta_banco");
        $cuenta->execute();
        $res_cuenta = $cuenta->fetchAll(PDO::FETCH_ASSOC);

        $result = array('tCliente' => $res_cliente, 'tCuenta' => $res_cuenta);
        echo json_encode($result);

    }

    public function deleteBank($user_id, $token, $bank_id)
    {
        require_once '../requires/funciones.php';
        $valida = $this->valida_token($user_id, $token);
        if (!$valida) {
            $m = 'Alguien ingresó con tu cuenta desde otro equipo vuelve a iniciar session';
            $state = 99;
            jsonReturn($state, $m);
        }

        $stateBank = $this->_DB->prepare("SELECT COUNT(*) AS count
                                            FROM cuenta_cobro
                                            WHERE cuentabanco_id = ? AND usuario_id = ? AND (estado = ? OR estado = ?)");
        $stateBank->bindParam(1, $bank_id, PDO::PARAM_INT);
        $stateBank->bindParam(2, $user_id, PDO::PARAM_INT);
        $stateBank->bindValue(3, 'A', PDO::PARAM_STR);
        $stateBank->bindValue(4, 'P', PDO::PARAM_STR);
        $stateBank->execute();
        $resData = $stateBank->fetch(PDO::FETCH_OBJ);

        if ($resData->count > 0){
            $m = 'La cuenta que desea eliminar tiene consignaciones banacarias activas o pendientes por aprobar';
            $state = 98;
            jsonReturn($state, $m);
        }

        $q = $this->_DB->prepare("UPDATE usuario_banco SET estado = ? WHERE id = ?");
        $q->bindValue(1, 'C', PDO::PARAM_INT);
        $q->bindParam(2, $bank_id, PDO::PARAM_INT);
        $q->execute();

        if ($q->rowCount() == 1) {
            $res = array('state' => 1, 'msg' => 'La cuenta bancaria fue eliminada con exito');
        } else {
            $res = array('state' => 0, 'msg' => 'No fue posible lograr la operación solicitada inténtalo nuevamente');
        }
        echo json_encode($res);
    }
}