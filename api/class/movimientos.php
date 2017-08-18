<?php

/**
 * Created by PhpStorm.
 * User: Robin
 * Date: 1/02/2017
 * Time: 8:43 PM
 */
require_once 'conexion.php';

class Movimiento
{
    private $_BD;

    /**
     * movimientos constructor.
     * @param opcion $
     */
    public function __construct()
    {
        $this->_BD = new Conection();
    }

    private function valida_token($user_id, $token)
    {
        $stmt = $this->_BD->prepare("SELECT * FROM usuario WHERE usuario_id = :u AND token_session = :t");
        $stmt->execute(array(':u' => $user_id, ':t' => $token));
        if ($stmt->rowCount() == 1) {
            return true;
        } else {
            return false;
        }
    }

    public function movimientos($opcion, $user_id, $token)
    {
        try {
            /*$valida = $this->valida_token($user_id, $token);
            if ($valida) {*/
            require_once '../requires/global.php';
            switch ($opcion) {
                case 'saldo':

                    $stmt = $this->_BD->prepare("SELECT
                                                creditos saldo,
                                                creditos_base saldo_total,
                                                creditos_bono saldo_bono
                                             FROM registro a INNER JOIN usuario u ON u.usuario_id = a.usuario_id
                                             WHERE
                                                u.usuario_id = :u");
                    $stmt->execute(array(':u' => $user_id));

                    $response = $stmt->fetchAll();

                    echo json_encode($response);

                    break;

                case 'deposito':

                    $consulta = $this->_BD->prepare("SELECT
                                ur.recarga_id,
                              aes_decrypt(u.nombre, '" . CLAVE_ENCRYPT . "') as descripcion,
                                ur.valor,
                                ur.fecha_crea
                            FROM
                                usuario_recarga ur
                            INNER JOIN punto_venta pv ON ur.puntoventa_id = pv.usuario_id
                            INNER JOIN usuario u ON pv.usuario_id = u.usuario_id
                            WHERE
                                ur.usuario_id = :u ORDER BY ur.recarga_id DESC");

                    $consulta->execute(array(':u' => $user_id));

                    $response = $consulta->fetchAll();

                    echo json_encode($response);

                    break;

                case 'apuesta':

                    $consulta = $this->_BD->prepare("SELECT
                                                    i.ticket_id,
                                                    i.vlr_apuesta,
                                                    i.fecha_crea,
                                                    i.hora_crea,
                                                    CASE i.premiado WHEN  'S' THEN i.vlr_premio ELSE 0 END valor_premio,
                                                    CASE i.bet_status WHEN 'c' THEN 'Abierto' WHEN 'S' THEN 'Ganado' WHEN 'N' THEN 'Perdido' WHEN 'A' THEN 'No accion' WHEN 'R' THEN 'Pendiente' WHEN 'W' THEN 'Pendiente' WHEN 'J' THEN 'Rechazado' WHEN 'M' THEN 'Rechazado' WHEN 'T' THEN 'Cerrada' END bet_status
                                                FROM
                                                    it_ticket_enc i
                                                INNER JOIN usuario u ON u.usuario_id = i.usuario_id
                                                WHERE
                                                    u.usuario_id = :u
                                                ORDER BY
                                                    i.fecha_crea DESC");
                    $consulta->execute(array(':u' => $user_id));

                    $response = $consulta->fetchAll();

                    echo json_encode($response);

                    break;

                case 'docPendiente':

                    $consulta = $this->_BD->prepare("SELECT
                                                          c.cuenta_id AS numero_fact,
                                                          concat(c.fecha_crea,' ',c.hora_crea) fecha_crea,
                                                          c.valor,
                                                          CASE c.tipo
                                                          WHEN '6' THEN
                                                            'Efectivo'
                                                          ELSE
                                                            'Bancario'
                                                          END AS medio_pago,
                                                          c.valor_retencion,
                                                          c.valor + c.valor_retencion AS valor_bruto,
                                                          CASE WHEN EXTRACT(HOUR FROM TIMEDIFF(NOW(), (concat(c.fecha_crea,' ',c.hora_crea)))) > co.tiempo_max_pago_notas_ret_horas THEN 'Caducado' ELSE CASE c.estado WHEN 'A' THEN 'Activa' ELSE 'Pendiente' END END estado
                                                        FROM
                                                          cuenta_cobro c INNER JOIN usuario u ON u.usuario_id = c.usuario_id
                                                          INNER JOIN configuracion co ON co.config_id = 1
                                                        WHERE
                                                          u.usuario_id = :u
                                                          AND (c.estado = :e OR c.estado = :f) ORDER BY c.fecha_crea DESC");
                    $consulta->execute(array(':u' => $user_id, ':e' => 'A', ':f' => 'P'));

                    $response = $consulta->fetchAll();

                    echo json_encode($response);

                    break;

                case 'docPagado':

                    $consulta = $this->_BD->prepare("SELECT
                                c.cuenta_id AS numero_fact,
                               concat(c.fecha_crea,' ',c.hora_crea) fecha_crea,
                                c.valor,
                                c.valor_retencion,
                                c.valor + c.valor_retencion AS valor_bruto,
                                CASE c.tipo
                            WHEN '6' THEN
                                'Medio efectivo'
                            ELSE
                                'Medio bancario'
                            END AS medio_pago
                            FROM
                                cuenta_cobro c INNER JOIN usuario u ON  u.usuario_id=c.usuario_id
                            WHERE
                                u.usuario_id = :u
                            AND c.estado = :e ORDER BY c.fecha_crea DESC");
                    $consulta->execute(array(':u' => $user_id, ':e' => 'I'));

                    $response = $consulta->fetchAll();

                    echo json_encode($response);

                    break;

                case 'docCancel':

                    $consulta = $this->_BD->prepare("SELECT
                                c.cuenta_id AS numero_fact,
                                concat(c.fecha_crea,' ',c.hora_crea) fecha_crea,
                                c.fecha_cancela_doc,
                                c.valor + c.valor_retencion AS valor_bruto,
                                c.valor,c.valor_retencion
                            FROM
                                cuenta_cobro c INNER JOIN usuario u ON  u.usuario_id=c.usuario_id
                            WHERE
                                u.usuario_id = :u
                            AND c.estado = :e ORDER BY c.fecha_crea DESC");
                    $consulta->execute(array(':u' => $user_id, ':e' => 'C'));

                    $response = $consulta->fetchAll();

                    echo json_encode($response);

                    break;

                case 'ajusteEntrada':

                    $consulta = $this->_BD->prepare("SELECT
                                                                  m.descripcion,
                                                                  su.valor,
                                                                  su.fecha_crea,
                                                                  su.dir_ip,
                                                                  su.observ
                                                                FROM
                                                                  saldo_usuonline_ajuste su
                                                                  INNER JOIN usuario u ON u.usuario_id = su.usuario_id
                                                                  INNER JOIN motivo_ajuste m ON su.motivo_id = m.motivo_id
                                                                WHERE
                                                                  u.usuario_id = :u3
                                                                  AND su.tipo_id = :ti
                                                                ORDER BY
                                                                  su.fecha_crea DESC");
                    $consulta->execute(array(':u3' => $user_id, ':ti' => 'E'));

                    $response = $consulta->fetchAll();

                    echo json_encode($response);

                    break;

                case 'ajusteSalida':

                    $consulta = $this->_BD->prepare("SELECT
                                                                  m.descripcion,
                                                                  su.valor,
                                                                  su.fecha_crea,
                                                                  su.dir_ip,
                                                                  su.observ
                                                                FROM
                                                                  saldo_usuonline_ajuste su
                                                                  INNER JOIN usuario u ON u.usuario_id = su.usuario_id
                                                                  INNER JOIN motivo_ajuste m ON su.motivo_id = m.motivo_id
                                                                WHERE
                                                                  u.usuario_id = :u3
                                                                  AND su.tipo_id = :ti
                                                                ORDER BY
                                                                  su.fecha_crea DESC");

                    $consulta->execute(array(':u3' => $user_id, ':ti' => 'S'));

                    $response = $consulta->fetchAll();

                    echo json_encode($response);

                    break;

                case 'saldoDiscriminado':

                    $stmt = $this->_BD->prepare("SELECT
                                              CASE WHEN x.recargas IS NULL THEN 0 ELSE x.recargas END recargas,
                                              CASE WHEN x.apuestas IS NULL THEN 0 ELSE x.apuestas END apuestas,
                                              CASE WHEN x.premios IS NULL THEN 0 ELSE x.premios END premios,
                                              CASE WHEN x.notas_pendiente IS NULL THEN 0 ELSE x.notas_pendiente END notas_pendiente,
                                              CASE WHEN x.notas_pagadas IS NULL THEN 0 ELSE x.notas_pagadas END notas_pagadas,
                                              CASE WHEN x.ajuste_entrada IS NULL THEN 0 ELSE x.ajuste_entrada END ajuste_entrada,
                                              CASE WHEN x.ajuste_salida IS NULL THEN 0 ELSE x.ajuste_salida END ajuste_salida,
                                             (CASE WHEN x.recargas IS NULL THEN 0 ELSE x.recargas END
                                            - CASE WHEN x.apuestas IS NULL THEN 0 ELSE x.apuestas END
                                            + CASE WHEN x.premios IS NULL THEN 0 ELSE x.premios END
                                            - CASE WHEN x.notas_pendiente IS NULL THEN 0 ELSE x.notas_pendiente END
                                            - CASE WHEN x.notas_pagadas IS NULL THEN 0 ELSE x.notas_pagadas END
                                            + CASE WHEN x.ajuste_entrada IS NULL THEN 0 ELSE x.ajuste_entrada END
                                            - CASE WHEN x.ajuste_salida IS NULL THEN 0 ELSE x.ajuste_salida END) total
                                            FROM (
                                            SELECT
                                            (SELECT sum(x.valor) FROM usuario_recarga x WHERE x.usuario_id=a.usuario_id) recargas,
                                            (SELECT sum(CASE x.eliminado WHEN 'S' THEN 0 ELSE x.vlr_apuesta END) FROM it_ticket_enc x WHERE x.usuario_id=a.usuario_id) apuestas,
                                            (SELECT sum(x.vlr_premio) FROM it_ticket_enc x WHERE x.premiado='S' AND x.usuario_id=a.usuario_id) premios,
                                            (SELECT sum(x.valor+x.valor_retencion) FROM cuenta_cobro x WHERE (x.estado='P' OR x.estado='A') AND x.usuario_id=a.usuario_id) notas_pendiente,
                                            (SELECT sum(x.valor+x.valor_retencion) FROM cuenta_cobro x WHERE x.estado='I' AND x.usuario_id=a.usuario_id) notas_pagadas,
                                            (SELECT sum(x.valor) FROM saldo_usuonline_ajuste x WHERE x.tipo_id='E' AND x.usuario_id=a.usuario_id) ajuste_entrada,
                                            (SELECT sum(x.valor) FROM saldo_usuonline_ajuste x WHERE x.tipo_id='S' AND x.usuario_id=a.usuario_id) ajuste_salida
                                            FROM usuario a
                                            WHERE a.usuario_id=:u) x");
                    $stmt->execute(array(':u' => $user_id));

                    $response = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $this->_DB = null;
                    echo json_encode($response);


                    break;

                default:
                    break;
            }
            /*} else {
                $response = array('state' => 99, 'msg' => 'alguien ingresó con tu cuenta desde otro equipo vuelve a iniciar session');
            }
            echo json_encode($response);*/

        } catch (PDOException $e) {
            echo json_encode($e->getMessage());
        }
    }
}
