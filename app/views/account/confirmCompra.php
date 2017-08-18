<?php

header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token , Authorization');
date_default_timezone_set('America/Bogota');

require_once "../../../api/requires/global.php";
require_once "../../../api/requires/funciones.php";
require_once "../../../api/requires/funciones_itainment.php";
require_once '../../../api/class/conexion.php';
$_DB = new Conection;


/*$stmt = $_DB->prepare("INSERT INTO log_pasarela (
    merchant_id,state_pol,response_code_pol,reference_sale,reference_pol,sign,payment_method,payment_method_type,value,tax,transaction_date,currency,email_buyer,cus,pse_bank,description,billing_address,
    attempts,authorization_code,bank_id,customer_number,date,error_code_bank,error_message_bank,ip,
    payment_method_id,payment_request_state,response_message_pol,transaction_bank_id,transaction_id,
    payment_method_name ) VALUES (
        :merchant_id,:state_pol,:response_code_pol,:reference_sale,:reference_pol,:sign,:payment_method,:payment_method_type,:value,:tax,:transaction_date,:currency,:email_buyer,:cus,:pse_bank,:description,:billing_address,:attempts,:authorization_code,:bank_id,:customer_number,:date,
        :error_code_bank,:error_message_bank,:ip,:payment_method_id,:payment_request_state,
        :response_message_pol,:transaction_bank_id,:transaction_id,:payment_method_name
    )");

$stmt->execute(array(
    ':merchant_id' => $_POST['merchant_id'],
    ':state_pol' => $_POST['state_pol'],
    ':response_code_pol' => $_POST['response_code_pol'],
    ':reference_sale' => $_POST['reference_sale'],
    ':reference_pol' => $_POST['reference_pol'],
    ':sign' => $_POST['sign'],
    ':payment_method' => $_POST['payment_method'],
    ':payment_method_type' => $_POST['payment_method_type'],
    ':value' => $_POST['value'],
    ':tax' => $_POST['tax'],
    ':transaction_date' => $_POST['transaction_date'],
    ':currency' => $_POST['currency'],
    ':email_buyer' => $_POST['email_buyer'],
    ':cus' => $_POST['cus'],
    ':pse_bank' => $_POST['pse_bank'],
    ':description' => $_POST['description'],
    ':billing_address' => $_POST['billing_address'],
    ':attempts' => $_POST['attempts'],
    ':authorization_code' => $_POST['authorization_code'],
    ':bank_id' => $_POST['bank_id'],
    ':customer_number' => $_POST['customer_number'],
    ':date' => $_POST['date'],
    ':error_code_bank' => $_POST['error_code_bank'],
    ':error_message_bank' => $_POST['error_message_bank'],
    ':ip' => $_POST['ip'],
    ':payment_method_id' => $_POST['payment_method_id'],
    ':payment_request_state' => $_POST['payment_request_state'],
    ':response_message_pol' => $_POST['response_message_pol'],
    ':transaction_bank_id' => $_POST['transaction_bank_id'],
    ':transaction_id' => $_POST['transaction_id'],
    ':payment_method_name' => $_POST['payment_method_name'],
));*/

/**
 * codigoFactura= Código enviado por el comercio.
 * valorFactura= Valor pagado por el usuario, este valor es enviado por el
 * comercio.
 * transaccionAprobada= Marcara el número “1" exitosa, “0” pendiente, “-1”
 * rechazada, “2” abortada.
 * codigoAutorizacion= Esta variable es solo usada por visa y mastercard al
 * procesar una transacción exitosa, esté código solo es emitido por la entidad
 * financiera.
 * firmaTuCompra= Firma digital entregada por tu compra.
 * numeroTransaccion= Esta variable es usada para el caso de PSE como
 * código de transacción aprobada o rechazada, para visa es una secuencia de la
 * transacción mas no determina si la transacción fue aprobada. (Este código solo
 * es emitido por la entidad financiera).
 * metodoPago= Número que se genera para identificar el tipo de franquicia o
 * entidad bancaria.
 * Variables default que se entregan en la url de confirmación
 * codigoFactura= Código enviado por el comercio.
 * valorFactura= Valor pagado por el usuario, este valor es enviado por el
 * comercio.
 * transaccionAprobada= Marcara el número “1" exitosa, “0”pendiente, “-1”
 * rechazada, “2” abortada, “3” reversada.
 * codigoAutorizacion=Esta variable es solo usada por visa y mastercard al
 * procesar una transacción, este código solo es emitido por la entidad financiera.
 * Servicio de Recaudo TuCompra Página 8
 *
 * INTEGRACION VERSION 3.1
 *
 *
 * firmaTuCompra= firma digital entregada por tu compra
 * numeroTransaccion= Esta variable es usada para el caso de PSE como
 * código de transacción aprobada o rechazada, para visa es una secuencia de la
 * transacción mas no determina si la transacción fue aprobada. (Este código solo
 * es emitido por la entidad financiera).
 * metodoPago= Número que se genera para identificar el tipo de franquicia o
 * entidad bancaria.
 */
$deposito = explode(" ", $_POST['codigoFactura']);

if ($deposito[0] == 'Recarga') {
    $factura = $deposito[1];
} else {
    $factura = $_POST['codigoFactura'];
}

$p = $_DB->prepare("INSERT INTO test_compra (valor1, valor2) VALUES (:n, :v)");
$p->execute(array(':n' => 'aa', ':v' => "UPDATE factura_pasarela SET status = 6, id_usuario_recarga = 0, estado_transaccion =  " . $_POST['transaccionAprobada'] . ", codigo_autorizacion = " . $_POST['codigoAutorizacion'] . ", firma_tu_compra = " . $_POST['firmaTuCompra'] . ", numero_transaccion = " . $_POST['numeroTransaccion'] . ", metodo_pago = " . $_POST['metodoPago'] . " WHERE id = $factura"));


$idUser = $_DB->prepare("SELECT id_usuario, valor, status FROM factura_pasarela WHERE id = :id");
$idUser->execute(array(':id' => $factura));
$idUser = $idUser->fetch(PDO::FETCH_OBJ);

$valor = $idUser->valor;
$usuario_id = $idUser->id_usuario;
$id_usuario_recarga = 28941;

if ($valor < 0) {
    exit();
}

if ($_POST['transaccionAprobada'] == 1) {
    if ($idUser->status == 'draft_enviado' || $idUser->status == 'draft_creado') {
        $configTuCompra = $_DB->prepare("SELECT
                                        url_base_compra,
                                        llave_terminal,
                                        usuario,
                                        cliente
                                    FROM
                                        configuracion_pasarela
                                    WHERE
                                        id_configuracion = :origen");
        $configTuCompra->execute(array(':origen' => 1));
        $resConfiguracionTuCompra = $configTuCompra->fetch(PDO::FETCH_OBJ);

        //$token = md5($resConfiguracionTuCompra->llave_terminal . '~' . $resConfiguracionTuCompra->cliente . '~' . $_POST['reference_sale'] . '~' . $_POST['value'] . '~' . 'COP');

        $mediopago_id = 1;

        //Valida si el usuario existe, tiene el perfil correcto y esta activo
        $validaSql = $_DB->prepare("SELECT
                                a.usuario_id
                            FROM
                                usuario a
                            INNER JOIN registro r ON r.usuario_id = a.usuario_id
                            INNER JOIN usuario_perfil b ON (
                                a.mandante = b.mandante
                                AND a.usuario_id = b.usuario_id
                            )
                            WHERE
                                a.mandante = 0
                            AND r.usuario_id = :d
                            AND a.estado_jugador = :e
                            AND b.perfil_id = :u");
        $validaSql->execute(array(':u' => 'USUONLINE', ':e' => 'AC', ':d' => $usuario_id));

        $valida_RS = $validaSql->fetch(PDO::FETCH_OBJ);
        $usuario_id = $valida_RS->usuario_id;

        //Busca el consecutivo para la recarga
        $error = '';
        $_DB->beginTransaction();

        //Aumenta el consecutivo en 1
        $contSql = $_DB->prepare("UPDATE consecutivo SET numero=numero+1 WHERE tipo=:r");

        if (!$contSql->execute(array(':r' => 'REC'))) {
            $error .= 'Error 0 ';
        }

        $recarga_id = 0;
        $consecSql = $_DB->prepare("SELECT a.numero FROM consecutivo a WHERE a.tipo=:r FOR UPDATE");
        $consecSql->execute(array(':r' => 'REC'));

        if ($consecSql->rowCount() > 0) {
            $consec_RS = $consecSql->fetch(PDO::FETCH_OBJ);
            $recarga_id = $consec_RS->numero;
        }

        $updateEstado = $_DB->prepare("UPDATE factura_pasarela SET status = :s, estado_transaccion = :tranApro, codigo_autorizacion = :codAut, firma_tu_compra = :firmCom, numero_transaccion = :numTrans, metodo_pago = :metPago, recarga_id = :ur, proveedor_id = :pro WHERE id = :id");
        if (!$updateEstado->execute(array(':s' => 4, ':tranApro' => $_POST['transaccionAprobada'], ':codAut' => $_POST['codigoAutorizacion'], ':firmCom' => $_POST['firmaTuCompra'], ':numTrans' => $_POST['numeroTransaccion'], ':metPago' => $_POST['metodoPago'], ':id' => $factura, ':ur' => $recarga_id, ':pro' => $id_usuario_recarga))) {
            $error .= 'Error factura' . $_DB->errorInfo();
        }

        //Verifica si el usuario no tiene recargas y el promocional 1 está activo
        $entrada_prom = false;
        $promocional_id = 0;
        $valor_promocional = 0;
        $porcen_regalo_recarga = 0;

        //Valida si es la primer depósito del usuario
        $validaSql = $_DB->prepare("SELECT count(a.recarga_id) cantidad FROM usuario_recarga a WHERE a.mandante=:m AND a.usuario_id=:u");
        $validaSql->execute(array(':m' => MANDANTE, ':u' => $usuario_id));
        $valida_RS = $validaSql->fetch(PDO::FETCH_OBJ);


        if ($valida_RS->cantidad <= 0) {
            //Valida si existe algún bono creado para primer depósito
            $valida2Sql = $_DB->prepare("SELECT a.bono_id,a.codigo,a.bonusplanid FROM bono a WHERE a.mandante=:m AND now() BETWEEN date(a.fecha_ini) AND date(a.fecha_fin) AND a.tipo='PD'");
            $valida2Sql->execute(array(':m' => MANDANTE));
            $valida2_RS = $valida2Sql->fetch(PDO::FETCH_OBJ);
            if ($valida2Sql->rowCount() > 0) {
                //Llama la función para adjudicar el bono por primer depósito
                $strEstado = "I";
                $primer_deposito = IT_Agregar_Bono_Deposito($walletcode_it, $usuario_id, $codigo_pais_id, $valor . "00", $valida2_RS->bonusplanid);

                //Verifica si hubo error
                $obj = json_decode($primer_deposito);
                $valor_bono = $obj->{'bonus'} / 100;
                if ($obj->{'error'} == "0") {
                    $strEstado = "A";

                    //Actualiza el saldo de créditos de bonos
                    $strSql5 = $_DB->prepare("UPDATE registro SET creditos_bono_ant=creditos_bono,creditos_bono=creditos_bono+" . $valor_bono . " WHERE mandante=:m AND usuario_id=:u");
                    if (!$strSql5->execute(array(':u' => $usuario_id, ':m' => MANDANTE))) {
                        $error .= 'Error 6' . $_DB->errorInfo();
                    }
                }

                //Inserta el registro con el log
                $strSql4 = $_DB->prepare("INSERT INTO bono_log (usuario_id, tipo, valor, fecha_crea, estado, error_id, id_externo, mandante) VALUES (:usuario_id, :tipo, :valor, :fecha_crea, :estado, :error_id, :id_externo, :m)");
                if (!$strSql4->execute(array(':usuario_id' => $usuario_id, ':tipo' => 'PD', ':valor' => $valor_bono, ':fecha_crea' => date('Y-m-d H:i:s'), ':estado' => $strEstado, ':error_id' => $obj->{'error'}, ':id_externo' => $obj->{'bonus_cuentaid'}, ':m' => MANDANTE))) {
                    $error .= 'Error 7' . $_DB->errorInfo();
                }
            }
        } else {
            //Valida si existe algún bono creado para primer depósito
            $valida2Sql = $_DB->prepare("SELECT a.bono_id,a.codigo,a.bonusplanid FROM bono a WHERE a.mandante=:m AND now() BETWEEN date(a.fecha_ini) AND date(a.fecha_fin) AND a.tipo='D'");
            $valida2Sql->execute(array(':m' => MANDANTE));
            $valida2_RS = $valida2Sql->fetch(PDO::FETCH_OBJ);
            if ($valida2Sql->rowCount() > 0) {
                //Llama la función para adjudicar el bono por primer depósito
                $strEstado = "I";
                $primer_deposito = IT_Agregar_Bono_Deposito($walletcode_it, $usuario_id, $codigo_pais_id, $valor . "00", $valida2_RS->bonusplanid);

                //Verifica si hubo error
                $obj = json_decode($primer_deposito);
                $valor_bono = $obj->{'bonus'} / 100;
                if ($obj->{'error'} == "0") {
                    $strEstado = "A";
                    //Actualiza el saldo de créditos de bonos
                    $strSql5 = $_DB->prepare("UPDATE registro SET creditos_bono_ant=creditos_bono,creditos_bono=creditos_bono+" . $valor_bono . " WHERE mandante=:m AND usuario_id=:u");
                    if (!$strSql5->execute(array(':u' => $usuario_id, ':m' => MANDANTE))) {
                        $error .= 'Error 6' . $_DB->errorInfo();
                    }
                }

                //Inserta el registro con el log
                $strSql4 = $_DB->prepare("INSERT INTO bono_log (usuario_id, tipo, valor, fecha_crea, estado, error_id, id_externo, mandante) VALUES (:usuario_id, :tipo, :valor, :fecha_crea, :estado, :error_id, :id_externo, :m)");
                if (!$strSql4->execute(array(':usuario_id' => $usuario_id, ':tipo' => 'D', ':valor' => $valor_bono, ':fecha_crea' => date('Y-m-d H:i:s'), ':estado' => $strEstado, ':error_id' => $obj->{'error'}, ':id_externo' => $obj->{'bonus_cuentaid'}, ':m' => MANDANTE))) {
                    $error .= 'Error 7' . $_DB->errorInfo();
                }
            }
        }

        //Aumenta el saldo
        $strSql = $_DB->prepare("UPDATE registro SET creditos_base=creditos_base+( :v *(1+ :pr /100))+ :vl WHERE mandante=:m AND usuario_id= :u");
        if (!$strSql->execute(array(':m' => MANDANTE, ':u' => $usuario_id, ':vl' => $valor_promocional, ':pr' => $porcen_regalo_recarga, ':v' => $valor))) {
            $error .= 'Error 1' . $_DB->errorInfo();
        }

        //Calculamos el valor del IVA
        $valor_base = round($valor / (1 + ($porcen_iva / 100)), 0);
        $valor_iva = $valor - $valor_base;
        $porcen_iva = 0;

        //Inserta el registro con la trazabilidad de la recarga
        $contSql1 = $_DB->prepare("INSERT INTO usuario_recarga (recarga_id, usuario_id, fecha_crea, puntoventa_id, valor, porcen_regalo_recarga, mandante, dir_ip, promocional_id, valor_promocional, host,mediopago_id, porcen_iva, valor_iva) VALUES (:recarga, :usuario_id, NOW(), :session_u, :valor_base, :porcen_regalo_recarga, :m, :dir_ip, :promocional_id, :valor_promocional, :servidor, :mediopago_id, :porcen_iva, :valor_iva)");

        if (!$contSql1->execute(array(':recarga' => $recarga_id, ':usuario_id' => $usuario_id, ':session_u' => $id_usuario_recarga, ':valor_base' => $valor_base, ':porcen_regalo_recarga' => $porcen_regalo_recarga, ':m' => MANDANTE, ':dir_ip' => ObtenerIP(), ':promocional_id' => $promocional_id, ':valor_promocional' => $valor_promocional, ':servidor' => $servidor, ':mediopago_id' => $mediopago_id, ':porcen_iva' => $porcen_iva, ':valor_iva' => $valor_iva))) {
            $error .= 'Error 2';
        }

        //Inserta el flujo de caja con la entrada del dinero al punto de venta
        $strSql2 = $_DB->prepare("INSERT INTO flujo_caja (fecha_crea,hora_crea,usucrea_id,tipomov_id,valor,recarga_id,porcen_iva,valor_iva,mandante) VALUES ('" . date('Y-m-d') . "','" . date('H:i') . "'," . $id_usuario_recarga . ",'E'," . $valor_base . "," . $recarga_id . "," . $porcen_iva . "," . $valor_iva . "," . $mandante . ")");

        if (!$strSql2->execute()) {
            $error .= 'Error 3 ' . $_DB->errorInfo();
        }


        //Inserta la factura
        $strSql3 = $_DB->prepare("INSERT INTO factura (recarga_id) VALUES (:r)");
        if (!$strSql3->execute(array(':r' => $recarga_id))) {
            $error .= 'Error 4' . $_DB->errorInfo();
        }

        if ($error == '') {
            $_DB->commit();
            $result = array('state' => 1);
        } else {
            $_DB->rollBack();
            $result = array('state' => 0);
        }

        echo json_encode($result);

    } elseif ($idUser->status == 'pendiente') {
        $configTuCompra = $_DB->prepare("SELECT
                                        url_base_compra,
                                        llave_terminal,
                                        usuario,
                                        cliente
                                    FROM
                                        configuracion_pasarela
                                    WHERE
                                        id_configuracion = :origen");
        $configTuCompra->execute(array(':origen' => 1));
        $resConfiguracionTuCompra = $configTuCompra->fetch(PDO::FETCH_OBJ);

        //$token = md5($resConfiguracionTuCompra->llave_terminal . '~' . $resConfiguracionTuCompra->cliente . '~' . $_POST['reference_sale'] . '~' . $_POST['value'] . '~' . 'COP');

        $mediopago_id = 1;

        //Valida si el usuario existe, tiene el perfil correcto y esta activo
        $validaSql = $_DB->prepare("SELECT
                                a.usuario_id
                            FROM
                                usuario a
                            INNER JOIN registro r ON r.usuario_id = a.usuario_id
                            INNER JOIN usuario_perfil b ON (
                                a.mandante = b.mandante
                                AND a.usuario_id = b.usuario_id
                            )
                            WHERE
                                a.mandante = 0
                            AND r.usuario_id = :d
                            AND a.estado_jugador = :e
                            AND b.perfil_id = :u");
        $validaSql->execute(array(':u' => 'USUONLINE', ':e' => 'AC', ':d' => $usuario_id));

        $valida_RS = $validaSql->fetch(PDO::FETCH_OBJ);
        $usuario_id = $valida_RS->usuario_id;

        //Busca el consecutivo para la recarga
        $error = '';
        $_DB->beginTransaction();

        //Aumenta el consecutivo en 1
        $contSql = $_DB->prepare("UPDATE consecutivo SET numero=numero+1 WHERE tipo=:r");

        if (!$contSql->execute(array(':r' => 'REC'))) {
            $error .= 'Error 0 ';
        }

        $recarga_id = 0;
        $consecSql = $_DB->prepare("SELECT a.numero FROM consecutivo a WHERE a.tipo=:r FOR UPDATE");
        $consecSql->execute(array(':r' => 'REC'));

        if ($consecSql->rowCount() > 0) {
            $consec_RS = $consecSql->fetch(PDO::FETCH_OBJ);
            $recarga_id = $consec_RS->numero;
        }

        $updateEstado = $_DB->prepare("UPDATE factura_pasarela SET status = :s, estado_transaccion = :tranApro, codigo_autorizacion = :codAut, firma_tu_compra = :firmCom, numero_transaccion = :numTrans, metodo_pago = :metPago, recarga_id = :ur, proveedor_id = :pro WHERE id = :id");
        if (!$updateEstado->execute(array(':s' => 4, ':tranApro' => $_POST['transaccionAprobada'], ':codAut' => $_POST['codigoAutorizacion'], ':firmCom' => $_POST['firmaTuCompra'], ':numTrans' => $_POST['numeroTransaccion'], ':metPago' => $_POST['metodoPago'], ':id' => $factura, ':ur' => $recarga_id, ':pro' => $id_usuario_recarga))) {
            $error .= 'Error factura' . $_DB->errorInfo();
        }

        //Verifica si el usuario no tiene recargas y el promocional 1 está activo
        $entrada_prom = false;
        $promocional_id = 0;
        $valor_promocional = 0;
        $porcen_regalo_recarga = 0;

        //Valida si es la primer depósito del usuario
        $validaSql = $_DB->prepare("SELECT count(a.recarga_id) cantidad FROM usuario_recarga a WHERE a.mandante=:m AND a.usuario_id=:u");
        $validaSql->execute(array(':m' => MANDANTE, ':u' => $usuario_id));
        $valida_RS = $validaSql->fetch(PDO::FETCH_OBJ);


        if ($valida_RS->cantidad <= 0) {
            //Valida si existe algún bono creado para primer depósito
            $valida2Sql = $_DB->prepare("SELECT a.bono_id,a.codigo,a.bonusplanid FROM bono a WHERE a.mandante=:m AND now() BETWEEN date(a.fecha_ini) AND date(a.fecha_fin) AND a.tipo='PD'");
            $valida2Sql->execute(array(':m' => MANDANTE));
            $valida2_RS = $valida2Sql->fetch(PDO::FETCH_OBJ);
            if ($valida2Sql->rowCount() > 0) {
                //Llama la función para adjudicar el bono por primer depósito
                $strEstado = "I";
                $primer_deposito = IT_Agregar_Bono_Deposito($walletcode_it, $usuario_id, $codigo_pais_id, $valor . "00", $valida2_RS->bonusplanid);

                //Verifica si hubo error
                $obj = json_decode($primer_deposito);
                $valor_bono = $obj->{'bonus'} / 100;
                if ($obj->{'error'} == "0") {
                    $strEstado = "A";

                    //Actualiza el saldo de créditos de bonos
                    $strSql5 = $_DB->prepare("UPDATE registro SET creditos_bono_ant=creditos_bono,creditos_bono=creditos_bono+" . $valor_bono . " WHERE mandante=:m AND usuario_id=:u");
                    if (!$strSql5->execute(array(':u' => $usuario_id, ':m' => MANDANTE))) {
                        $error .= 'Error 6' . $_DB->errorInfo();
                    }
                }

                //Inserta el registro con el log
                $strSql4 = $_DB->prepare("INSERT INTO bono_log (usuario_id, tipo, valor, fecha_crea, estado, error_id, id_externo, mandante) VALUES (:usuario_id, :tipo, :valor, :fecha_crea, :estado, :error_id, :id_externo, :m)");
                if (!$strSql4->execute(array(':usuario_id' => $usuario_id, ':tipo' => 'PD', ':valor' => $valor_bono, ':fecha_crea' => date('Y-m-d H:i:s'), ':estado' => $strEstado, ':error_id' => $obj->{'error'}, ':id_externo' => $obj->{'bonus_cuentaid'}, ':m' => MANDANTE))) {
                    $error .= 'Error 7' . $_DB->errorInfo();
                }
            }
        } else {
            //Valida si existe algún bono creado para primer depósito
            $valida2Sql = $_DB->prepare("SELECT a.bono_id,a.codigo,a.bonusplanid FROM bono a WHERE a.mandante=:m AND now() BETWEEN date(a.fecha_ini) AND date(a.fecha_fin) AND a.tipo='D'");
            $valida2Sql->execute(array(':m' => MANDANTE));
            $valida2_RS = $valida2Sql->fetch(PDO::FETCH_OBJ);
            if ($valida2Sql->rowCount() > 0) {
                //Llama la función para adjudicar el bono por primer depósito
                $strEstado = "I";
                $primer_deposito = IT_Agregar_Bono_Deposito($walletcode_it, $usuario_id, $codigo_pais_id, $valor . "00", $valida2_RS->bonusplanid);

                //Verifica si hubo error
                $obj = json_decode($primer_deposito);
                $valor_bono = $obj->{'bonus'} / 100;
                if ($obj->{'error'} == "0") {
                    $strEstado = "A";
                    //Actualiza el saldo de créditos de bonos
                    $strSql5 = $_DB->prepare("UPDATE registro SET creditos_bono_ant=creditos_bono,creditos_bono=creditos_bono+" . $valor_bono . " WHERE mandante=:m AND usuario_id=:u");
                    if (!$strSql5->execute(array(':u' => $usuario_id, ':m' => MANDANTE))) {
                        $error .= 'Error 6' . $_DB->errorInfo();
                    }
                }

                //Inserta el registro con el log
                $strSql4 = $_DB->prepare("INSERT INTO bono_log (usuario_id, tipo, valor, fecha_crea, estado, error_id, id_externo, mandante) VALUES (:usuario_id, :tipo, :valor, :fecha_crea, :estado, :error_id, :id_externo, :m)");
                if (!$strSql4->execute(array(':usuario_id' => $usuario_id, ':tipo' => 'D', ':valor' => $valor_bono, ':fecha_crea' => date('Y-m-d H:i:s'), ':estado' => $strEstado, ':error_id' => $obj->{'error'}, ':id_externo' => $obj->{'bonus_cuentaid'}, ':m' => MANDANTE))) {
                    $error .= 'Error 7' . $_DB->errorInfo();
                }
            }
        }

        //Aumenta el saldo
        $strSql = $_DB->prepare("UPDATE registro SET creditos_base=creditos_base+( :v *(1+ :pr /100))+ :vl WHERE mandante=:m AND usuario_id= :u");
        if (!$strSql->execute(array(':m' => MANDANTE, ':u' => $usuario_id, ':vl' => $valor_promocional, ':pr' => $porcen_regalo_recarga, ':v' => $valor))) {
            $error .= 'Error 1' . $_DB->errorInfo();
        }

        //Calculamos el valor del IVA
        $valor_base = round($valor / (1 + ($porcen_iva / 100)), 0);
        $valor_iva = $valor - $valor_base;
        $porcen_iva = 0;

        //Inserta el registro con la trazabilidad de la recarga
        $contSql1 = $_DB->prepare("INSERT INTO usuario_recarga (recarga_id, usuario_id, fecha_crea, puntoventa_id, valor, porcen_regalo_recarga, mandante, dir_ip, promocional_id, valor_promocional, host,mediopago_id, porcen_iva, valor_iva) VALUES (:recarga, :usuario_id, NOW(), :session_u, :valor_base, :porcen_regalo_recarga, :m, :dir_ip, :promocional_id, :valor_promocional, :servidor, :mediopago_id, :porcen_iva, :valor_iva)");

        if (!$contSql1->execute(array(':recarga' => $recarga_id, ':usuario_id' => $usuario_id, ':session_u' => $id_usuario_recarga, ':valor_base' => $valor_base, ':porcen_regalo_recarga' => $porcen_regalo_recarga, ':m' => MANDANTE, ':dir_ip' => ObtenerIP(), ':promocional_id' => $promocional_id, ':valor_promocional' => $valor_promocional, ':servidor' => $servidor, ':mediopago_id' => $mediopago_id, ':porcen_iva' => $porcen_iva, ':valor_iva' => $valor_iva))) {
            $error .= 'Error 2';
        }

        //Inserta el flujo de caja con la entrada del dinero al punto de venta
        $strSql2 = $_DB->prepare("INSERT INTO flujo_caja (fecha_crea,hora_crea,usucrea_id,tipomov_id,valor,recarga_id,porcen_iva,valor_iva,mandante) VALUES ('" . date('Y-m-d') . "','" . date('H:i') . "'," . $id_usuario_recarga . ",'E'," . $valor_base . "," . $recarga_id . "," . $porcen_iva . "," . $valor_iva . "," . $mandante . ")");

        if (!$strSql2->execute()) {
            $error .= 'Error 3 ' . $_DB->errorInfo();
        }


        //Inserta la factura
        $strSql3 = $_DB->prepare("INSERT INTO factura (recarga_id) VALUES (:r)");
        if (!$strSql3->execute(array(':r' => $recarga_id))) {
            $error .= 'Error 4' . $_DB->errorInfo();
        }

        if ($error == '') {
            $_DB->commit();
            $result = array('state' => 1);
        } else {
            $_DB->rollBack();
            $result = array('state' => 0);
        }
        echo json_encode($result);
    }
} elseif ($_POST['transaccionAprobada'] == -1) {
    if ($idUser->status == 'draft_enviado' || $idUser->status == 'draft_creado') {
        $configTuCompra = $_DB->prepare("SELECT
                                        url_base_compra,
                                        llave_terminal,
                                        usuario,
                                        cliente
                                    FROM
                                        configuracion_pasarela
                                    WHERE
                                        id_configuracion = :origen");
        $configTuCompra->execute(array(':origen' => 1));
        $resConfiguracionTuCompra = $configTuCompra->fetch(PDO::FETCH_OBJ);

        //$token = md5($resConfiguracionTuCompra->llave_terminal . '~' . $resConfiguracionTuCompra->cliente . '~' . $_POST['reference_sale'] . '~' . $_POST['value'] . '~' . 'COP');
        $_DB->beginTransaction();

        //Aumenta el consecutivo en 1

        $recarga_id = 0;
        $updateEstado = $_DB->prepare("UPDATE factura_pasarela SET status = :s, estado_transaccion = :tranApro, codigo_autorizacion = :codAut, firma_tu_compra = :firmCom, numero_transaccion = :numTrans, metodo_pago = :metPago, recarga_id = :ur, proveedor_id = :pro WHERE id = :id");
        if (!$updateEstado->execute(array(':s' => 6, ':tranApro' => $_POST['transaccionAprobada'], ':codAut' => $_POST['codigoAutorizacion'], ':firmCom' => $_POST['firmaTuCompra'], ':numTrans' => $_POST['numeroTransaccion'], ':metPago' => $_POST['metodoPago'], ':id' => $factura, ':ur' => $recarga_id, ':pro' => $id_usuario_recarga))) {
            $error .= 'Error factura' . $_DB->errorInfo();
        }

        if ($error == '') {
            $_DB->commit();
            $result = array('state' => 1);
        } else {
            $_DB->rollBack();
            $result = array('state' => 0);
        }
    }
} elseif ($_POST['transaccionAprobada'] == 0) {
    if ($idUser->status == 'draft_enviado' || $idUser->status == 'draft_creado') {
        $configTuCompra = $_DB->prepare("SELECT
                                        url_base_compra,
                                        llave_terminal,
                                        usuario,
                                        cliente
                                    FROM
                                        configuracion_pasarela
                                    WHERE
                                        id_configuracion = :origen");
        $configTuCompra->execute(array(':origen' => 1));
        $resConfiguracionTuCompra = $configTuCompra->fetch(PDO::FETCH_OBJ);

        //$token = md5($resConfiguracionTuCompra->llave_terminal . '~' . $resConfiguracionTuCompra->cliente . '~' . $_POST['reference_sale'] . '~' . $_POST['value'] . '~' . 'COP

        //Busca el consecutivo para la recarga
        $error = '';
        $_DB->beginTransaction();

        $recarga_id = 0;
        $updateEstado = $_DB->prepare("UPDATE factura_pasarela SET status = :s, estado_transaccion = :tranApro, codigo_autorizacion = :codAut, firma_tu_compra = :firmCom, numero_transaccion = :numTrans, metodo_pago = :metPago, recarga_id = :ur, proveedor_id = :pro WHERE id = :id");
        if (!$updateEstado->execute(array(':s' => 7, ':tranApro' => $_POST['transaccionAprobada'], ':codAut' => $_POST['codigoAutorizacion'], ':firmCom' => $_POST['firmaTuCompra'], ':numTrans' => $_POST['numeroTransaccion'], ':metPago' => $_POST['metodoPago'], ':id' => $factura, ':ur' => $recarga_id, ':pro' => $id_usuario_recarga))) {
            $error .= 'Error factura' . $_DB->errorInfo();
        }

        if ($error == '') {
            $_DB->commit();
            $result = array('state' => 1);
        } else {
            $_DB->rollBack();
            $result = array('state' => 0);
        }
    }
} elseif ($_POST['transaccionAprobada'] == 2) {
    if ($idUser->status == 'draft_enviado' || $idUser->status == 'draft_creado') {
        $configTuCompra = $_DB->prepare("SELECT
                                        url_base_compra,
                                        llave_terminal,
                                        usuario,
                                        cliente
                                    FROM
                                        configuracion_pasarela
                                    WHERE
                                        id_configuracion = :origen");
        $configTuCompra->execute(array(':origen' => 1));
        $resConfiguracionTuCompra = $configTuCompra->fetch(PDO::FETCH_OBJ);

        //$token = md5($resConfiguracionTuCompra->llave_terminal . '~' . $resConfiguracionTuCompra->cliente . '~' . $_POST['reference_sale'] . '~' . $_POST['value'] . '~' . 'COP');


        //Busca el consecutivo para la recarga
        $error = '';
        $_DB->beginTransaction();

        $recarga_id = 0;
        $updateEstado = $_DB->prepare("UPDATE factura_pasarela SET status = :s, estado_transaccion = :tranApro, codigo_autorizacion = :codAut, firma_tu_compra = :firmCom, numero_transaccion = :numTrans, metodo_pago = :metPago, recarga_id = :ur, proveedor_id = :pro WHERE id = :id");
        if (!$updateEstado->execute(array(':s' => 3, ':tranApro' => $_POST['transaccionAprobada'], ':codAut' => $_POST['codigoAutorizacion'], ':firmCom' => $_POST['firmaTuCompra'], ':numTrans' => $_POST['numeroTransaccion'], ':metPago' => $_POST['metodoPago'], ':id' => $factura, ':ur' => $recarga_id, ':pro' => $id_usuario_recarga))) {
            $error .= 'Error factura' . $_DB->errorInfo();
        }

        if ($error == '') {
            $_DB->commit();
            $result = array('state' => 1);
        } else {
            $_DB->rollBack();
            $result = array('state' => 0);
        }

    }
}





