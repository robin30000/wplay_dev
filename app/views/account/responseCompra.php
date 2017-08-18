<?php

/*
    ["transaccionAprobada"]=> string(1) "1"
    ["codigoFactura"]=> string(10) "Recarga 28"
    ["valorFactura"]=> string(7) "12000.0"
    ["codigoAutorizacion"]=> string(2) "00"
    ["numeroTransaccion"]=> string(6) "561878"
    ["firmaTuCompra"]=> string(32) "16bf603ebbc5dca666f7f1f319751771"
    ["campoExtra1"]=> string(2) "20"
    ["campoExtra2"]=> string(0) ""
    ["campoExtra3"]=> string(0) ""
    ["metodoPago"]=> string(2) "41"
    ["nombreMetodo"]=> string(34) "Cuenta Ahorro/Corriente - TuCompra"
*/

$transactionId = $_POST['codigoFactura'];
$description = 'Deposito';
$value = $_POST['valorFactura'];
$currency = 'COP';
$lapPaymentMethod = $_POST['metodoPago'];
$buyerEmail = '';
$date = date('Y-m-d');

header("Location: https://wplay.co/apuestas");

/*header("Location: https://promo.wplay.co/cuenta-juego/banca/respuesta/tu-compra?transactionId=" . $transactionId . "&description=" . $description . "&value=" . $value . "&currency=" . $currency . "&lapPaymentMethod=" . $lapPaymentMethod . "&buyerEmail=" . $buyerEmail . "&date=" . $date);*/

/*header("Location: https://promo.wplay.co/cuenta-juego/banca/respuesta/tu-compra?transactionId=" . $transactionId . "&description=" . $description . "&value=" . $value . "&currency=" . $currency . "&lapPaymentMethod=" . $lapPaymentMethod . "&buyerEmail=" . $buyerEmail . "&date=" . $date);*/

