<?php

/**
 * Created by PhpStorm.
 * User: Robin
 * Date: 26/03/2017
 * Time: 11:30 PM
 */
require_once '../class/conexion.php';

class olimpiadas
{
    private $_DB;

    private function validate()
    {

    }

    public function __construct()
    {
        $this->_DB = new Conection();
    }

    public function cargaRanking()
    {
        try {
            $stmt = $this->_DB->query("SELECT z.*,
										@rownum := @rownum + 1 AS posicion
										FROM (
										SELECT x.nombre,sum(x.valor) valor
										FROM (
										SELECT
										r.usuario_id,
										r.nombre,
										r.valor
										FROM
										ranking_promo_actual r
										WHERE
										r.tipo_registro = 1
										UNION														
										SELECT
										r.usuario_id,
										r.nombre,
										sum(r.valor) valor
										FROM
										ranking_promo r
										GROUP BY
										r.usuario_id,
										r.nombre
										ORDER BY
										valor DESC
										) x
										INNER JOIN registro y ON (x.usuario_id=y.usuario_id)
										INNER JOIN preregistro w ON (y.preregistro_id=w.id)
										WHERE w.tipo_registro=1
										GROUP BY x.nombre
										ORDER BY valor DESC
										) z,
										(SELECT @rownum := 0) h
										LIMIT 15");
            $stmt->execute();
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (PDOException $e) {

        }
    }

    public function rankingHoy($user)
    {
        try {
            $stmt = $this->_DB->prepare("SELECT
                                                        r.nombre,
                                                        r.valor
                                                    FROM
                                                        ranking_promo_actual r
                                                    WHERE
                                                        r.tipo_registro = :tp
                                                    LIMIT 10");
            $stmt->execute(array(':tp' => 1));
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (PDOException $e) {

        }
    }

    public function rankingUsuario($datos)
    {
        try {
            $stmt = $this->_DB->prepare("SELECT posicion,valor, tipo_registro
                                            FROM ranking_promo_actual
                                            WHERE usuario_id = :u");
            $stmt->execute(array(':u' => $datos->usuario_id));
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (PDOException $e) {

        }
    }

    public function cargaRankingSemana()
    {
        try {
            $stmt = $this->_DB->prepare("SELECT z.*,
@rownum := @rownum + 1 AS posicion
FROM (
SELECT
                                            r.nombre,
                                            r.valor
                                        FROM
                                            ranking_promo_actual r
                                        WHERE
                                            r.tipo_registro = :tp
                                        GROUP BY
                                            r.nombre
                                        ORDER BY
                                            r.valor DESC
                                        LIMIT 15
) z,
(SELECT @rownum := 0) h");
            $stmt->execute(array(':tp' => 1));
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($result);
        } catch (PDOException $e) {

        }
    }

    public function ganadores()
    {
        try {
            $stmt = $this->_DB->query("SELECT
                                                 @rownum := @rownum + 1 AS posicion,
                                                 a.nombre,
                                                 round(a.valor, 0) valor
                                                FROM
                                                 ranking_promo a,
                                                 (SELECT @rownum := 0) h
                                                WHERE
                                                 a.tipo_registro = 1
                                                AND a.fecha = '2017-04-02'
                                                ORDER BY
                                                 a.valor DESC
                                                LIMIT 10");
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($result);
        } catch (PDOException $e) {

        }
    }

    public function ganadores1()
    {
        try {
            $stmt = $this->_DB->query("SELECT
                                         @rownum := @rownum + 1 AS posicion,
                                         a.nombre,
                                         round(a.valor, 0) valor
                                        FROM
                                         ranking_promo a,
                                         (SELECT @rownum := 0) h
                                        WHERE
                                         a.tipo_registro = 1
                                        AND a.fecha = '2017-04-09'
                                        ORDER BY
                                         a.valor DESC
                                        LIMIT 10");
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($result);
        } catch (PDOException $e) {

        }
    }

    public function ganadores2()
    {
        try {
            $stmt = $this->_DB->query("SELECT
                                         @rownum := @rownum + 1 AS posicion,
                                         a.nombre,
                                         round(a.valor, 0) valor
                                        FROM
                                         ranking_promo a,
                                         (SELECT @rownum := 0) h
                                        WHERE
                                         a.tipo_registro = 1
                                        AND a.fecha = '2017-04-16'
                                        ORDER BY
                                         a.valor DESC
                                        LIMIT 10");
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($result);
        } catch (PDOException $e) {

        }
    }

    public function ganadores4()
    {
        try {
            $stmt = $this->_DB->query("SELECT
                                         @rownum := @rownum + 1 AS posicion,
                                         a.nombre,
                                         round(a.valor, 0) valor
                                        FROM
                                         ranking_promo a,
                                         (SELECT @rownum := 0) h
                                        WHERE
                                         a.tipo_registro = 1
                                        AND a.fecha = '2017-04-23'
                                        ORDER BY
                                         a.valor DESC
                                        LIMIT 10");
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($result);
        } catch (PDOException $e) {

        }
    }

    public function ganadores5()
    {
        try {
            $stmt = $this->_DB->query("SELECT
                                         @rownum := @rownum + 1 AS posicion,
                                         a.nombre,
                                         round(a.valor, 0) valor
                                        FROM
                                         ranking_promo a,
                                         (SELECT @rownum := 0) h
                                        WHERE
                                         a.tipo_registro = 1
                                        AND a.fecha = '2017-04-30'
                                        ORDER BY
                                         a.valor DESC
                                        LIMIT 20");
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($result);
        } catch (PDOException $e) {

        }
    }

    public function ganadoresxdia($datos)
    {
        if ($datos->dia == 1) {
            $fecha = date('Y-m-j');
            $nuevafecha = strtotime('- 1 day', strtotime($fecha));
            $nuevafecha = date('Y-m-j', $nuevafecha);
        } else {

            $nuevafecha = $datos->dia;
        }
        try {
            $stmt = $this->_DB->prepare("SELECT
                                                 @rownum := @rownum + 1 AS posicion,
                                                 a.nombre,
                                                 round(a.valor, 0) valor
                                                FROM
                                                 ranking_promo a,
                                                 (SELECT @rownum := 0) h
                                                WHERE
                                                 a.tipo_registro = 1
                                                AND a.fecha =?
                                                ORDER BY
                                                 a.valor DESC
                                                LIMIT 10");
            $stmt->bindParam(1, $nuevafecha, PDO::PARAM_STR);

            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($result);
        } catch (PDOException $e) {

        }
    }
}
