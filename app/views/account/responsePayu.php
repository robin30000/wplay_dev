<?php

//if ($_SERVER["REQUEST_METHOD"] == "POST") {
    /*https://sandbox.checkout.payulatam.com/ppp-web-gateway-payu/response/pse/?merchantId=509414&merchant_name=Alquila+Global+Group+SAS&merchant_address=Julio+TamayoAlquila+Global+Groupcr+35a+%23+15b+35+oficina+815medellin++Colombia&telephone=3216388992&merchant_url=&transactionState=4&lapTransactionState=APPROVED&message=Aprobada&referenceCode=Recarga+3619&reference_pol=841384374&transactionId=14b09c41-1ec7-4252-a05f-e1a9816cfe2b&description=Deposito+para+compra+de+cr%C3%A9dito&trazabilityCode=1295687&cus=1295687&orderLanguage=es&extra1=20&extra2=&extra3=&polTransactionState=4&signature=f374e516641401fe61d3b627a0d9e6c2&polResponseCode=1&lapResponseCode=APPROVED&risk=.00&polPaymentMethod=25&lapPaymentMethod=PSE&polPaymentMethodType=4&lapPaymentMethodType=PSE&installmentsNumber=1&TX_VALUE=30000.00&TX_TAX=.00&currency=COP&lng=es&pseCycle=-1&buyerEmail=ROBIN_3X%40HOTMAIL.COM&pseBank=BANCO%20UNION%20COLOMBIANO&pseReference1=190.8.239.40&pseReference2=CC&pseReference3=80907664&authorizationCode=#/co/response*/

$transactionId = $_POST['codigoFactura'];
$description = 'Deposito';
$value = $_POST['valorFactura'];
$currency = 'COP';
$lapPaymentMethod = $_POST['metodoPago'];
$buyerEmail = '';
$date = date('Y-m-d');

header("Location: https://promo.wplay.co/apuestas");

/*header("Location: https://promo.wplay.co/cuenta-juego/banca/respuesta/payu?transactionId=" . $transactionId . "&description=" . $description . "&value=" . $value . "&currency=" . $currency . "&lapPaymentMethod=" . $lapPaymentMethod . "&buyerEmail=" . $buyerEmail . "&date=" . $date);*/


    /*header("Location: https://promo.wplay.co/apuestas");*/
/*} else {
    exit();
}*/

