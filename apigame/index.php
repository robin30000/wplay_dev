<?php

include "../api/requires/ws_itainment_info.php";

include "../api/requires/ws_itainment_procesar.php";

date_default_timezone_set('America/Bogota');


$req_dump = print_r($_REQUEST, true);

/* FUNCIONESS */


function Encriptar2($cadena)
{

    $key = "EA8WKJG8OGJQHEYH";

    $encrypted = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $cadena, MCRYPT_MODE_CBC, $key));

    return str_replace(array('+', '/', '='), array('_', '-', '.'), $encrypted); //Devuelve el string encriptado

}

function Desencriptar2($cadena)
{

    $key = "EA8WKJG8OGJQHEYH";

    $cadena = str_replace(array('_', '-', '.'), array('+', '/', '='), $cadena);

    $decrypted = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, base64_decode($cadena), MCRYPT_MODE_CBC, $key), "\0");

    return $decrypted; //Devuelve el string desencriptado

}

function SendPost($url, $header, $data)
{

    // use key 'http' even if you send the request to https://...

    $options = array(

        'http' => array(

            'header'  => $header,

            'method'  => 'POST',

            'content' => http_build_query($data),

        ),

    );

    $context = stream_context_create($options);

    $result = file_get_contents($url, false, $context);

    return $result;

}

/*Vbles WALLET*/

$siteid = 0;

$login = "";

$pass = "";

$walletcode = 283610;

$namerequest = ""; /*Nombre del tipo de solicitud

/* VBLES GENERAL */

$currency = "";

$success = 0;

$key_sha256 = "JJ7TJ4P0YD7N9QMX7X80VMSEJWKG87ERRLLB";

$key_prematch_general = "LM3IW5UXJF6CRGLKF33FX5SN806UPGDB2GTZ";

$ws_key_prematch = hash_hmac('sha256', $key_prematch_general, $key_sha256, false);

/*Vbles USER*/

$token = '';

$loginname = "";

$userperfil = "";

$usersaldo = 0;

$usernombre = 0;

$BonusAmountPROMO = 0;

/* --------------------- */

$note = trim(file_get_contents('php://input'));



$stringjson = "";

if ($note != "") {

    $log = date("F j, Y, g:i:s a") . "\r\n" .

        " REQUEST: " . file_get_contents('php://input');

    if (!is_dir('logs')) {
        // dir doesn't exist, make it

        mkdir('logs');

    }

//Save string to log, use FILE_APPEND to append.

    $fp = fopen('logs/log_' . date("Y-m-d") . '.log', 'a');

    fwrite($fp, $log);

    fclose($fp);

    $log = "\r\n" . "-------------------------" . "\r\n";

    $xml = simplexml_load_string($note);

    $token = $xml->Method->Params->Token->attributes()['Value'];

    $accion = $xml->Method->attributes()['Name'];

}

$stringjson = "";



if ($accion == "PlaceBet") {


    /*BET */

    $transactionID = 0;

    $betreferenceNum = 0;

    $betamount = 0;

    $GameReference = "";

    $Description = "";

    $ExternalUserID = "";

    $AffiliateUserId = "";

    $SiteId = 0;

    $FrontendType = "";

    $Bet = "";

    $IsSystem = false;

    $EventCount = 0;

    $BankerCount = 0;

    $Events = "";

    $BetMode = "";

    if (!empty($xml->Method->Params->Bet)) {


        $stringjson = "{";

        $stringjson = $stringjson . '"Token":"' . $xml->Method->Params->Token->attributes()['Value'] . '"';

        $stringjson = $stringjson . ',"KeyWinplay":"' . $ws_key_prematch . '"';

        $stringjson = $stringjson . ',"TypeWinplay":"BET"';

        $stringjson = $stringjson . ',"TransactionID":"' . $xml->Method->Params->TransactionID->attributes()['Value'] . '"';

        $stringjson = $stringjson . ',"valor":"' . $xml->Method->Params->BetAmount->attributes()['Value'] . '"';

        $stringjson = $stringjson . ',"ticketid":"' . $xml->Method->Params->BetReferenceNum->attributes()['Value'] . '"';

        $stringjson = $stringjson . ',"frontend":"' . $xml->Method->Params->FrontendType->attributes()['Value'] . '"';

        $stringjson = $stringjson . ',"betmode":"' . $xml->Method->Params->BetMode->attributes()['Value'] . '"';

        $stringjson = $stringjson . ',"GameReference":"' . $xml->Method->Params->GameReference->attributes()['Value'] . '"';

        $stringjson = $stringjson . ',"Description":"' . $xml->Method->Params->Description->attributes()['Value'] . '"';

        $stringjson = $stringjson . ',"usuarioid":"' . $xml->Method->Params->ExternalUserID->attributes()['Value'] . '"';

        $stringjson = $stringjson . ',"FrontendType":"' . $xml->Method->Params->FrontendType->attributes()['Value'] . '"';

        $stringjson = $stringjson . ',"BetStatus":"' . $xml->Method->Params->BetStatus->attributes()['Value'] . '"';

        $stringjson = $stringjson . ',"ClienteIP":"' . $xml->Method->Params->ClientIP->attributes()['Value'] . '"';

        $stringjson = $stringjson . ',"IsSystem":"' . $xml->Method->Params->Bet->IsSystem->attributes()['Value'] . '"';

        $stringjson = $stringjson . ',"EventCount":"' . $xml->Method->Params->Bet->EventCount->attributes()['Value'] . '"';

        $stringjson = $stringjson . ',"BankerCount":"' . $xml->Method->Params->Bet->BankerCount->attributes()['Value'] . '"';

        $stringjson = $stringjson . ',"PremioProyectado":"' . $xml->Method->Params->Bet->BetStake->Winnings->attributes()['Value'] . '"';

        $stringjson = $stringjson . ',"Events":"' . $xml->Method->Params->Bet->Events->attributes()['Value'] . '"';

        $stringjson = $stringjson . ',"EventsDescription":' . "[";

        $cont = 0;

        foreach ($xml->Method->Params->Bet->EventList->children() as $info) {

            if ($cont > 0) {

                $stringjson = $stringjson . ',';

            }

            $time = new DateTime($info->EventDate->attributes()['Value']);

            $stringjson = $stringjson . '{"evento":"' . $info->attributes()['Value'] . '","fecha":"' . $time->format('Y-m-d') . '","hora":"' . $time->format('H:i') . '","eventoid":"' . $info->EventID->attributes()['Value'] . '","sportid":"' . $info->SportID->attributes()['Value'] . '","matchid":"' . $info->ExtEventID->attributes()['Value'] . '","agrupador":"' . $info->Market->attributes()['Value'] . '","agrupadorid":"' . $info->Market->MarketID->attributes()['Value'] . '","opcion":"' . $info->Market->Outcome->attributes()['Value'] . '","logro":"' . $info->Market->Odds->attributes()['Value'] . '"}';


            $cont++;

        }

        $stringjson = $stringjson . "]";

        $stringjson = $stringjson . "}";
    } else {

        $action = "BETCHECK";

        $stringjson = "{";

        $stringjson = $stringjson . '"Token":"' . $xml->Method->Params->Token->attributes()['Value'] . '"';

        $stringjson = $stringjson . ',"KeyWinplay":"' . $ws_key_prematch . '"';

        $stringjson = $stringjson . ',"TypeWinplay":"BETCHECK"';

        $stringjson = $stringjson . ',"TransactionID":"' . $xml->Method->Params->TransactionID->attributes()['Value'] . '"';

        $stringjson = $stringjson . ',"valor":"' . $xml->Method->Params->BetAmount->attributes()['Value'] . '"';

        $stringjson = $stringjson . ',"ticketid":"' . $xml->Method->Params->BetReferenceNum->attributes()['Value'] . '"';

        $stringjson = $stringjson . ',"GameReference":"' . $xml->Method->Params->GameReference->attributes()['Value'] . '"';

        $stringjson = $stringjson . ',"Description":"' . $xml->Method->Params->Description->attributes()['Value'] . '"';

        $stringjson = $stringjson . ',"usuarioid":"' . $xml->Method->Params->ExternalUserID->attributes()['Value'] . '"';

        $stringjson = $stringjson . ',"FrontendType":"' . $xml->Method->Params->FrontendType->attributes()['Value'] . '"';

        $stringjson = $stringjson . ',"BetStatus":"' . $xml->Method->Params->BetStatus->attributes()['Value'] . '"';

        $stringjson = $stringjson . "}";

    }

}

if ($accion == "AwardWinnings") {

    $stringjson = "{";

    $stringjson = $stringjson . '"Token":"' . $xml->Method->Params->Token->attributes()['Value'] . '"';

    $stringjson = $stringjson . ',"KeyWinplay":"' . $ws_key_prematch . '"';

    $stringjson = $stringjson . ',"TypeWinplay":"WIN"';

    $stringjson = $stringjson . ',"TransactionID":"' . $xml->Method->Params->TransactionID->attributes()['Value'] . '"';

    $stringjson = $stringjson . ',"valor":"' . $xml->Method->Params->WinAmount->attributes()['Value'] . '"';

    $stringjson = $stringjson . ',"ticketid":"' . $xml->Method->Params->WinReferenceNum->attributes()['Value'] . '"';

    $stringjson = $stringjson . ',"GameReference":"' . $xml->Method->Params->GameReference->attributes()['Value'] . '"';

    $stringjson = $stringjson . ',"Description":"' . $xml->Method->Params->Description->attributes()['Value'] . '"';

    $stringjson = $stringjson . ',"usuarioid":"' . $xml->Method->Params->ExternalUserID->attributes()['Value'] . '"';

    $stringjson = $stringjson . ',"FrontendType":"' . $xml->Method->Params->FrontendType->attributes()['Value'] . '"';

    $stringjson = $stringjson . ',"BetStatus":"' . $xml->Method->Params->BetStatus->attributes()['Value'] . '"';

    $stringjson = $stringjson . "}";

}

if ($accion == "ObtainBonus") {

    $stringjson = "{";

    $stringjson = $stringjson . '"Token":"0"';

    $stringjson = $stringjson . ',"KeyWinplay":"' . $ws_key_prematch . '"';

    $stringjson = $stringjson . ',"TypeWinplay":"CODEBONUS"';

    $stringjson = $stringjson . ',"BonusPlanId":"' . $xml->Method->Params->BonusPlanId->attributes()['Value'] . '"';

    $stringjson = $stringjson . ',"BonusId":"0"';

    $stringjson = $stringjson . ',"usuarioid":"' . $xml->Method->Params->ExternalUserId->attributes()['Value'] . '"';

    $stringjson = $stringjson . ',"BonusCode":"' . $xml->Method->Params->BonusCode->attributes()['Value'] . '"';

    $stringjson = $stringjson . ',"valor":"' . $xml->Method->Params->BonusAmount->attributes()['Value'] . '"';

    $stringjson = $stringjson . ',"SiteId":"' . $xml->Method->Params->SiteId->attributes()['Value'] . '"';

    $stringjson = $stringjson . ',"BetStatus":"#"';

    $stringjson = $stringjson . ',"ticketid":"#"';

    $stringjson = $stringjson . ',"GameReference":"#"';

    $stringjson = $stringjson . ',"TransactionID":"0101"';

    $stringjson = $stringjson . "}";

}

if ($accion == "AwardBonus") {

    $stringjson = "{";

    $stringjson = $stringjson . '"Token":"' . $xml->Method->Params->Token->attributes()['Value'] . '"';

    $stringjson = $stringjson . ',"KeyWinplay":"' . $ws_key_prematch . '"';

    $stringjson = $stringjson . ',"TypeWinplay":"WINBONUS"';

    $stringjson = $stringjson . ',"TransactionID":"' . $xml->Method->Params->TransactionID->attributes()['Value'] . '"';

    $Description_Explode = explode("= ", $xml->Method->Params->Description->attributes()['Value']);

    $Description2_Explode = explode(",", $Description_Explode[1]);

    $stringjson = $stringjson . ',"valor":"' . $xml->Method->Params->BonusAmount->attributes()['Value'] . '"';

    $stringjson = $stringjson . ',"ticketid":"#"';

    $stringjson = $stringjson . ',"GameReference":"#"';

    $stringjson = $stringjson . ',"Description":"' . $xml->Method->Params->Description->attributes()['Value'] . '"';

    $stringjson = $stringjson . ',"BonusId":"' . $Description2_Explode[0] . '"';

    $stringjson = $stringjson . ',"BonusPlanId":"' . $Description_Explode[2] . '"';

    $stringjson = $stringjson . ',"usuarioid":"' . $xml->Method->Params->ExternalUserID->attributes()['Value'] . '"';

    $stringjson = $stringjson . ',"BetStatus":"#"';

    $stringjson = $stringjson . "}";

}

if ($accion == "BonusExpired") {

    $stringjson = "{";

    $stringjson = $stringjson . '"Token":"#"';

    $stringjson = $stringjson . ',"KeyWinplay":"' . $ws_key_prematch . '"';

    $stringjson = $stringjson . ',"TypeWinplay":"EXPIREDBONUS"';

    $stringjson = $stringjson . ',"Listabonus":[';

    $i = 0;

    foreach ($xml->Method->Params->BonusAccounts as $BonusAccount) {

        if ($i > 0) {
            $stringjson = $stringjson . ',';
        }
        $stringjson = $stringjson . '{"TransactionID":"#"';

        $stringjson = $stringjson . ',"ticketid":"#"';

        $stringjson = $stringjson . ',"GameReference":"#"';

        $stringjson = $stringjson . ',"Description":"#"';

        $stringjson = $stringjson . ',"valor":"#"';

        $stringjson = $stringjson . ',"BonusId":"' . $BonusAccount->BonusAccountId->attributes()['Value'] . '"';

        $stringjson = $stringjson . ',"BonusPlanId":"' . $BonusAccount->BonusPlanId->attributes()['Value'] . '"';

        $stringjson = $stringjson . ',"usuarioid":"' . $BonusAccount->ExternalUserID->attributes()['Value'] . '"';

        $stringjson = $stringjson . ',"BetStatus":"#"}';

        $i = $i + 1;

    }

    $stringjson = $stringjson . "]}";

}

if ($accion == "NewCredit") {

    $stringjson = "{";

    $stringjson = $stringjson . '"Token":"' . $xml->Method->Params->Token->attributes()['Value'] . '"';

    $stringjson = $stringjson . ',"KeyWinplay":"' . $ws_key_prematch . '"';

    $stringjson = $stringjson . ',"TypeWinplay":"NEWCREDIT"';

    $stringjson = $stringjson . ',"TransactionID":"' . $xml->Method->Params->TransactionID->attributes()['Value'] . '"';

    $stringjson = $stringjson . ',"valor":"' . $xml->Method->Params->NewCreditAmount->attributes()['Value'] . '"';

    $stringjson = $stringjson . ',"ticketid":"' . $xml->Method->Params->NewCreditReferenceNum->attributes()['Value'] . '"';

    $stringjson = $stringjson . ',"GameReference":"' . $xml->Method->Params->GameReference->attributes()['Value'] . '"';

    $stringjson = $stringjson . ',"Description":"' . $xml->Method->Params->Description->attributes()['Value'] . '"';

    $stringjson = $stringjson . ',"usuarioid":"' . $xml->Method->Params->ExternalUserID->attributes()['Value'] . '"';

    $stringjson = $stringjson . ',"FrontendType":"' . $xml->Method->Params->FrontendType->attributes()['Value'] . '"';

    $stringjson = $stringjson . ',"BetStatus":"' . $xml->Method->Params->BetStatus->attributes()['Value'] . '"';

    $stringjson = $stringjson . "}";

}

if ($accion == "NewDebit") {

    $stringjson = "{";

    $stringjson = $stringjson . '"Token":"' . $xml->Method->Params->Token->attributes()['Value'] . '"';

    $stringjson = $stringjson . ',"KeyWinplay":"' . $ws_key_prematch . '"';

    $stringjson = $stringjson . ',"TypeWinplay":"NEWDEBIT"';

    $stringjson = $stringjson . ',"TransactionID":"' . $xml->Method->Params->TransactionID->attributes()['Value'] . '"';

    $stringjson = $stringjson . ',"valor":"' . $xml->Method->Params->NewDebitAmount->attributes()['Value'] . '"';

    $stringjson = $stringjson . ',"ticketid":"' . $xml->Method->Params->NewDebitReferenceNum->attributes()['Value'] . '"';

    $stringjson = $stringjson . ',"GameReference":"' . $xml->Method->Params->GameReference->attributes()['Value'] . '"';

    $stringjson = $stringjson . ',"Description":"' . $xml->Method->Params->Description->attributes()['Value'] . '"';

    $stringjson = $stringjson . ',"usuarioid":"' . $xml->Method->Params->ExternalUserID->attributes()['Value'] . '"';

    $stringjson = $stringjson . ',"FrontendType":"' . $xml->Method->Params->FrontendType->attributes()['Value'] . '"';

    $stringjson = $stringjson . ',"BetStatus":"' . $xml->Method->Params->BetStatus->attributes()['Value'] . '"';

    $stringjson = $stringjson . "}";

}

if ($accion == "CashoutBet") {

    $stringjson = "{";

    $stringjson = $stringjson . '"Token":"' . $xml->Method->Params->Token->attributes()['Value'] . '"';

    $stringjson = $stringjson . ',"KeyWinplay":"' . $ws_key_prematch . '"';

    $stringjson = $stringjson . ',"TypeWinplay":"CASHOUT"';

    $stringjson = $stringjson . ',"TransactionID":"' . $xml->Method->Params->TransactionID->attributes()['Value'] . '"';

    $stringjson = $stringjson . ',"valor":"' . $xml->Method->Params->CashoutAmount->attributes()['Value'] . '"';

    $stringjson = $stringjson . ',"ticketid":"' . $xml->Method->Params->BetReferenceNum->attributes()['Value'] . '"';

    $stringjson = $stringjson . ',"GameReference":"' . $xml->Method->Params->GameReference->attributes()['Value'] . '"';

    $stringjson = $stringjson . ',"Description":"' . $xml->Method->Params->Description->attributes()['Value'] . '"';

    $stringjson = $stringjson . ',"usuarioid":"' . $xml->Method->Params->ExternalUserID->attributes()['Value'] . '"';

    $stringjson = $stringjson . ',"FrontendType":"' . $xml->Method->Params->FrontendType->attributes()['Value'] . '"';

    $stringjson = $stringjson . ',"BetStatus":"' . $xml->Method->Params->BetStatus->attributes()['Value'] . '"';

    $stringjson = $stringjson . "}";

}

if ($accion == "stakeDecrease") {

    $stringjson = "{";

    $stringjson = $stringjson . '"Token":"' . $xml->Method->Params->Token->attributes()['Value'] . '"';

    $stringjson = $stringjson . ',"KeyWinplay":"' . $ws_key_prematch . '"';

    $stringjson = $stringjson . ',"TypeWinplay":"STAKEDECREASE"';

    $stringjson = $stringjson . ',"TransactionID":"' . $xml->Method->Params->TransactionID->attributes()['Value'] . '"';

    $BonusAmountPROMO = $xml->Method->Params->stakeDecreaseAmount->attributes()['Value'];

    $stringjson = $stringjson . ',"valor":"' . $xml->Method->Params->stakeDecreaseAmount->attributes()['Value'] . '"';

    $stringjson = $stringjson . ',"ticketid":"' . $xml->Method->Params->stakeDecreaseReferenceNum->attributes()['Value'] . '"';

    $stringjson = $stringjson . ',"GameReference":"' . $xml->Method->Params->GameReference->attributes()['Value'] . '"';

    $stringjson = $stringjson . ',"Description":"' . $xml->Method->Params->Description->attributes()['Value'] . '"';

    $stringjson = $stringjson . ',"usuarioid":"' . $xml->Method->Params->ExternalUserID->attributes()['Value'] . '"';

    $stringjson = $stringjson . ',"FrontendType":"' . $xml->Method->Params->FrontendType->attributes()['Value'] . '"';

    $stringjson = $stringjson . ',"BetStatus":"' . $xml->Method->Params->BetStatus->attributes()['Value'] . '"';

    $stringjson = $stringjson . "}";

}

if ($accion == "RefundBet") {

    /*REFUND BET */

    $transactionID = 0;

    $betreferenceNum = 0;

    $betamount = 0;

    $GameReference = "";

    $Description = "";

    $ExternalUserID = "";

    $AffiliateUserId = "";

    $SiteId = 0;

    $FrontendType = "";

    $Bet = "";

    $IsSystem = false;

    $EventCount = 0;

    $BankerCount = 0;

    $Events = "";

    $BetMode = "";

    $stringjson = "{";

    $stringjson = $stringjson . '"Token":"' . $xml->Method->Params->Token->attributes()['Value'] . '"';

    $stringjson = $stringjson . ',"KeyWinplay":"' . $ws_key_prematch . '"';

    $stringjson = $stringjson . ',"TypeWinplay":"REFUND"';

    $stringjson = $stringjson . ',"TransactionID":"' . $xml->Method->Params->TransactionID->attributes()['Value'] . '"';

    $stringjson = $stringjson . ',"valor":"' . $xml->Method->Params->RefundAmount->attributes()['Value'] . '"';

    $stringjson = $stringjson . ',"ticketid":"' . $xml->Method->Params->BetReferenceNum->attributes()['Value'] . '"';

    $stringjson = $stringjson . ',"GameReference":"' . $xml->Method->Params->GameReference->attributes()['Value'] . '"';

    $stringjson = $stringjson . ',"Description":"' . $xml->Method->Params->Description->attributes()['Value'] . '"';

    $stringjson = $stringjson . ',"usuarioid":"' . $xml->Method->Params->ExternalUserID->attributes()['Value'] . '"';

    $stringjson = $stringjson . ',"FrontendType":"' . $xml->Method->Params->FrontendType->attributes()['Value'] . '"';

    $stringjson = $stringjson . ',"BetStatus":"' . $xml->Method->Params->BetStatus->attributes()['Value'] . '"';

    $stringjson = $stringjson . "}";

}

if ($accion == "LossSignal") {

    /*LOSS SIGNAL */

    $transactionID = 0;

    $betreferenceNum = 0;

    $betamount = 0;

    $GameReference = "";

    $Description = "";

    $ExternalUserID = "";

    $AffiliateUserId = "";

    $SiteId = 0;

    $FrontendType = "";

    $Bet = "";

    $IsSystem = false;

    $EventCount = 0;

    $BankerCount = 0;

    $Events = "";

    $BetMode = "";

    $stringjson = "{";

    $stringjson = $stringjson . '"Token":"' . $xml->Method->Params->Token->attributes()['Value'] . '"';

    $stringjson = $stringjson . ',"KeyWinplay":"' . $ws_key_prematch . '"';

    $stringjson = $stringjson . ',"TypeWinplay":"LOSS"';

    $stringjson = $stringjson . ',"TransactionID":"' . $xml->Method->Params->TransactionID->attributes()['Value'] . '"';

    $stringjson = $stringjson . ',"valor":"' . $xml->Method->Params->BetAmount->attributes()['Value'] . '"';

    $stringjson = $stringjson . ',"ticketid":"' . $xml->Method->Params->BetReferenceNum->attributes()['Value'] . '"';

    $stringjson = $stringjson . ',"GameReference":"' . $xml->Method->Params->GameReference->attributes()['Value'] . '"';

    $stringjson = $stringjson . ',"Description":"' . $xml->Method->Params->Description->attributes()['Value'] . '"';

    $stringjson = $stringjson . ',"usuarioid":"' . $xml->Method->Params->ExternalUserID->attributes()['Value'] . '"';

    $stringjson = $stringjson . ',"FrontendType":"' . $xml->Method->Params->FrontendType->attributes()['Value'] . '"';

    $stringjson = $stringjson . ',"BetStatus":"' . $xml->Method->Params->BetStatus->attributes()['Value'] . '"';

    $stringjson = $stringjson . "}";

}

$log = $log . "\r\n" . "\r\n" . "JSON: " . $stringjson;

if (!is_dir('logs')) {
    // dir doesn't exist, make it

    mkdir('logs');

}

$log = $log . "\r\n" . "-------------------------" . "\r\n";

//Save string to log, use FILE_APPEND to append.

$fp = fopen('logs/log_' . date("Y-m-d") . '.log', 'a');

fwrite($fp, $log);

fclose($fp);

$log = "\r\n" . "-------------------------" . "\r\n";

$error = "";

$textoerror = "";

$continuar = 0;

if ($note != "" && $action != "BETCHECK") {

    /* TOMA DATOS */

    $accion = $xml->Method->attributes()['Name'];

    $extraid = "111111";

    $already = "false";

    $continuar = 0;

    if ($accion == "GetBanners") {

        $continuar = 1;

    } elseif ($accion != "GetAccountDetails" && $accion != "GetBalance") {

        //$data          = array('req' => ($stringjson));

        $data = ($stringjson);

        $header = "Content-type: application/x-www-form-urlencodede";

        $result = ws_itainment_procesar($data);

        $result_decryp = ($result);

        $respuesta = preg_split("/_/", $result_decryp);

        $log = $log . "\r\n" . "RESPUESTA: " . $result_decryp . PHP_EOL .

            "" . PHP_EOL;

        if ($respuesta[0] == "OK") {

            if ($respuesta[2] == $ws_key_prematch) {

                $userperfil = $respuesta[1];

                $userid = $respuesta[3];

                $usernombre = $respuesta[4];

                $usersaldo = $respuesta[5];

                $already = $respuesta[6];

                $token_new = $respuesta[7];



                $continuar = 1;

            }

        } elseif ($respuesta[0] == "ERROR") {

            $error = $respuesta[1];

            $textoerror = $respuesta[2];

        } else {

            //$data          = array('req' => ($stringjson));

            $data = ($stringjson);

            $header = "Content-type: application/x-www-form-urlencoded\r\n";

            $result = ws_itainment_procesar($data);

            $result_decryp = ($result);

            $respuesta = preg_split("/_/", $result_decryp);

            $log = $log . "\r\n" . "RESPUESTA(REENVIO): " . $result_decryp . "\r\n" .

                "" . PHP_EOL;

            if ($respuesta[0] == "OK") {

                if ($respuesta[2] == $ws_key_prematch) {

                    $userperfil = $respuesta[1];

                    $userid = $respuesta[3];

                    $usernombre = $respuesta[4];

                    $usersaldo = $respuesta[5];

                    $token_new = $respuesta[7];

                    $already = "FALSE";

                    $continuar = 1;

                }

            } elseif ($respuesta[0] == "ERROR") {

                $error = $respuesta[1];

                $textoerror = $respuesta[2];

            } else {

                $error = "95";

                $textoerror = "Hubo un error desconocido en nuestro sistema.";

            }

        }

    } else {

        //$data          = array('req' => ("" . $ws_key_prematch . "_" . $token . "_INI"));

        $clienteIP = "00";

        if ($accion == "GetAccountDetails") {
            $clienteIP = $xml->Method->Params->ClientIP->attributes()['Value'];
        }

        $data = $ws_key_prematch . "_" . $token . "_" . $clienteIP . "_INI";

        $log = $log . $data . "\r\n";

        $header = "Content-type: application/x-www-form-urlencoded\r\n";

        $result = ws_itainment_info($data);

        $result_decryp = ($result);

        $respuesta = preg_split("/_/", $result_decryp);

        $log = $log . "\r\n" . "DATOS USER ENC: " . ("" . $ws_key_prematch . "_" . $token . "_INI") . "\r\n";

        $log = $log . "\r\n" . "DATOS USER DES: " . (("" . $ws_key_prematch . "_" . $token . "_INI")) . "\r\n";

        $log = $log . "\r\n" . "RESPUESTA: " . $result_decryp . "\r\n";

        if ($respuesta[0] == "OK") {

            if ($respuesta[2] == $ws_key_prematch) {

                $userperfil = $respuesta[1];

                $userid = $respuesta[3];

                $usernombre = $respuesta[4];

                $usersaldo = $respuesta[5];

                $userafiliacion = $respuesta[6];

                $continuar = 1;

            }

        } elseif ($respuesta[0] == "ERROR") {

            $error = $respuesta[1];

            $textoerror = $respuesta[2];

        } else {

            //$data          = array('req' => ("" . $ws_key_prematch . "_" . $token . "_INI"));

            $data = $ws_key_prematch . "_" . $token . "_INI";

            $header = "Content-type: application/x-www-form-urlencoded\r\n";

            $result = ws_itainment_info($data);

            $result_decryp = ($result);

            $respuesta = preg_split("/_/", $result_decryp);

            $log = $log . "\r\n" . "DATOS USER ENC: " . ("" . $ws_key_prematch . "_" . $token . "_INI") . "\r\n" .

                "" . "\r\n";

            $log = $log . "\r\n" . "DATOS USER DES: " . (("" . $ws_key_prematch . "_" . $token . "_INI")) . "\r\n" .

                "" . "\r\n";

            $log = $log . "\r\n" . "RESPUESTA: " . $result_decryp . "\r\n" .

                "" . "\r\n";

            if ($respuesta[0] == "OK") {

                if ($respuesta[2] == $ws_key_prematch) {

                    $userperfil = $respuesta[1];

                    $userid = $respuesta[3];

                    $usernombre = $respuesta[4];

                    $usersaldo = $respuesta[5];

                    $userafiliacion = $respuesta[6];

                    $continuar = 1;

                }

            } elseif ($respuesta[0] == "ERROR") {

                $error = $respuesta[1];

                $textoerror = $respuesta[2];

            } else {

                $error = "95";

                $textoerror = "Hubo un error desconocido en nuestro sistema.";

            }

        }

    }

    if ($continuar == 1) {

        $response = "";

        if ($xml->Method->attributes()['Name'] == "GetAccountDetails") {

            $response = '<PKT>' . "\r\n" . '

            <Result Name="GetAccountDetails" Success="1">' . "\r\n" . '

            <Returnset>' . "\r\n" . '

            <Token Type="string" Value="' . $xml->Method->Params->Token->attributes()['Value'] . '" />' . "\r\n" . '

            <LoginName Type="string" Value="' . $usernombre . '" />' . "\r\n" . '

            <Currency Type="string" Value="COP" />' . "\r\n" . '

            <Country Type="string" Value="CO" />' . "\r\n" . '

            <ExternalUserID Type="string" Value="' . $userid . '" />' . "\r\n" . '

            <AffiliationPath Type="string" Value="0:Intech,L4|1:Wplay,S6|2:Usuario' . $userafiliacion . ',' . $userafiliacion . '" />' . "\r\n" . '

            <ExternalUserType Type="int" Value="3" />' . "\r\n" . '

            <UserBalance Type="string" Value="' . $usersaldo . '" />' . "\r\n" . '

            </Returnset>' . "\r\n" . '

            </Result>' . "\r\n" . '

            </PKT>';

        }

        if ($xml->Method->attributes()['Name'] == "GetBalance") {

            $response = '<PKT>' . "\r\n" . '

            <Result Name="GetBalance" Success="1">' . "\r\n" . '

            <Returnset>' . "\r\n" . '

            <Token Type="string" Value="' . $xml->Method->Params->Token->attributes()['Value'] . '" />' . "\r\n" . '

            <Currency Type="string" Value="COP" />' . "\r\n" . '

            <ExternalUserID Type="string" Value="' . $userid . '" />' . "\r\n" . '

            <Balance Type="string" Value="' . $usersaldo . '" />' . "\r\n" . '

            </Returnset>' . "\r\n" . '

            </Result>' . "\r\n" . '

            </PKT>';

        }

        if ($xml->Method->attributes()['Name'] == "PlaceBet") {

            $response = '<PKT>' . "\r\n" . '

            <Result Name="PlaceBet" Success="1">' . "\r\n" . '

            <Returnset>' . "\r\n" . '

            <Token Type="string" Value="' . $token_new . '" />' . "\r\n" . '

            <ExtTransactionID Type="string" Value="' . $extraid . '" />' . "\r\n" . '

            <AlreadyProcessed Type="bool" Value="' . $already . '" />' . "\r\n" . '

            <Balance Type="string" Value="' . $usersaldo . '" />' . "\r\n" . '

            <RegulatorAssignedId Type="string" Value="' . $clave_ticket . '" />' . "\r\n" . '

            </Returnset>' . "\r\n" . '

            </Result>' . "\r\n" . '

            </PKT>';

        }

        if ($xml->Method->attributes()['Name'] == "AwardWinnings") {

            $response = '<PKT>' . "\r\n" . '

            <Result Name="AwardWinnings" Success="1">' . "\r\n" . '

            <Returnset>' . "\r\n" . '

            <Token Type="string" Value="' . $token_new . '" />' . "\r\n" . '

            <ExtTransactionID Type="string" Value="' . $extraid . '" />' . "\r\n" . '

            <AlreadyProcessed Type="bool" Value="' . $already . '" />' . "\r\n" . '

            <Balance Type="string" Value="' . $usersaldo . '" />' . "\r\n" . '

            </Returnset>' . "\r\n" . '

            </Result>' . "\r\n" . '

            </PKT>';

        }

        if ($xml->Method->attributes()['Name'] == "AwardBonus") {

            $response = '<PKT>' . "\r\n" . '

            <Result Name="AwardBonus" Success="1">' . "\r\n" . '

            <Returnset>' . "\r\n" . '

            <Token Type="string" Value="' . $token_new . '" />' . "\r\n" . '

            <ExtTransactionID Type="string" Value="' . $extraid . '" />' . "\r\n" . '

            <AlreadyProcessed Type="bool" Value="' . $already . '" />' . "\r\n" . '

            <Balance Type="string" Value="' . $usersaldo . '" />' . "\r\n" . '

            </Returnset>' . "\r\n" . '

            </Result>' . "\r\n" . '

            </PKT>';

        }

        if ($xml->Method->attributes()['Name'] == "stakeDecrease") {

            $response = '<PKT>' . "\r\n" . '

            <Result Name="StakeDecrease" Success="1">' . "\r\n" . '

            <Returnset>' . "\r\n" . '

            <Token Type="string" Value="' . $token_new . '" />' . "\r\n" . '

            <ExtTransactionID Type="string" Value="' . $extraid . '" />' . "\r\n" . '


            <AlreadyProcessed Type="bool" Value="' . $already . '" />' . "\r\n" . '

            <Balance Type="string" Value="' . $usersaldo . '" />' . "\r\n" . '

            </Returnset>' . "\r\n" . '

            </Result>' . "\r\n" . '

            </PKT>';

        }

        if ($xml->Method->attributes()['Name'] == "NewDebit") {

            $response = '<PKT>' . "\r\n" . '

            <Result Name="NewDebit" Success="1">' . "\r\n" . '

            <Returnset>' . "\r\n" . '

            <Token Type="string" Value="' . $token_new . '" />' . "\r\n" . '

            <ExtTransactionID Type="string" Value="' . $extraid . '" />' . "\r\n" . '

            <AlreadyProcessed Type="bool" Value="' . $already . '" />' . "\r\n" . '

            <Balance Type="string" Value="' . $usersaldo . '" />' . "\r\n" . '

            </Returnset>' . "\r\n" . '

            </Result>' . "\r\n" . '

            </PKT>';

        }

        if ($xml->Method->attributes()['Name'] == "NewCredit") {

            $response = '<PKT>' . "\r\n" . '

            <Result Name="NewDebit" Success="1">' . "\r\n" . '

            <Returnset>' . "\r\n" . '

            <Token Type="string" Value="' . $token_new . '" />' . "\r\n" . '

            <ExtTransactionID Type="string" Value="' . $extraid . '" />' . "\r\n" . '

            <AlreadyProcessed Type="bool" Value="' . $already . '" />' . "\r\n" . '

            <Balance Type="string" Value="' . $usersaldo . '" />' . "\r\n" . '

            </Returnset>' . "\r\n" . '

            </Result>' . "\r\n" . '

            </PKT>';

        }

        if ($xml->Method->attributes()['Name'] == "CashoutBet") {

            $response = '<PKT>' . "\r\n" . '

            <Result Name="NewDebit" Success="1">' . "\r\n" . '

            <Returnset>' . "\r\n" . '

            <Token Type="string" Value="' . $token_new . '" />' . "\r\n" . '

            <ExtTransactionID Type="string" Value="' . $extraid . '" />' . "\r\n" . '

            <AlreadyProcessed Type="bool" Value="' . $already . '" />' . "\r\n" . '

            <Balance Type="string" Value="' . $usersaldo . '" />' . "\r\n" . '

            </Returnset>' . "\r\n" . '

            </Result>' . "\r\n" . '

            </PKT>';

        }

        if ($xml->Method->attributes()['Name'] == "RefundBet") {

            $response = '<PKT>' . "\r\n" . '

            <Result Name="RefundBet" Success="1">' . "\r\n" . '

                <Returnset>' . "\r\n" . '

                    <Token Type="string" Value="' . $token_new . '" />' . "\r\n" . '

                    <ExtTransactionID Type="string" Value="' . $extraid . '" />' . "\r\n" . '

                    <AlreadyProcessed Type="bool" Value="' . $already . '" />' . "\r\n" . '

                    <Balance Type="string" Value="' . $usersaldo . '" />' . "\r\n" . '

                </Returnset>' . "\r\n" . '

            </Result>' . "\r\n" . '

            </PKT>';

        }

        // if ($xml->Method->attributes()['Name'] == "GetBanners" && $xml->Method->Params->GameType->attributes()['Value'] == "PreMatch") {

        //     $response = "<PKT>" . "\r\n" . "

        //         <Result Name='GetBanners' Success='1'>" . "\r\n" . "

        //         <Returnset>" . "\r\n" . "

        //         <Banner Type='string' Value='https://www.wplay.co/api/images_banner/banner_left_prematch.gif' Position='Left'

        //         Height='615' Width='100%'/>" . "\r\n" . "

        //         <Banner Type='string' Value='https://www.wplay.co/api/images_banner/banner_right_prematch.gif' Position='Right'

        //         Height='615' Width='250'/>" . "\r\n" . "

        //             </Returnset>" . "\r\n" . "

        //           </Result>" . "\r\n" . "

        //         </PKT>";

        // }

        // if ($xml->Method->attributes()['Name'] == "GetBanners" && $xml->Method->Params->GameType->attributes()['Value'] == "Live") {

        //     $response = "<PKT>" . "\r\n" . "

        //         <Result Name='GetBanners' Success='1'>" . "\r\n" . "

        //         <Returnset>" . "\r\n" . "

        //         <Banner Type='string' Value='https://www.wplay.co/api/images_banner/banner_left_vivo.gif' Position='Left'

        //         Height='615' Width='250'/>" . "\r\n" . "

        //         <Banner Type='string' Value='https://www.wplay.co/api/images_banner/banner_right_vivo.gif' Position='Right'

        //         Height='615' Width='250'/>" . "\r\n" . "

        //             </Returnset>" . "\r\n" . "

        //           </Result>" . "\r\n" . "

        //         </PKT>";

        // }

        if ($xml->Method->attributes()['Name'] == "LossSignal") {

            $response = '<PKT>' . "\r\n" . '

            <Result Name="LossSignal" Success="1">' . "\r\n" . '

                <Returnset>' . "\r\n" . '

                </Returnset>' . "\r\n" . '

            </Result>' . "\r\n" . '

            </PKT>';

        }

        header('Content-Type: application/xml');

        print $response;

        $log = $log . "\r\n" . "RESPUESTA (DINOS): " . "\r\n" . $response;

    } else {

        $response = '<PKT>' . PHP_EOL . '

        <Result Name="' . $xml->Method->attributes()['Name'] . '" Success="0"> <Returnset>' . PHP_EOL . '

        <Error Type="string" Value="' . $textoerror . '" />' . PHP_EOL . '

        <ErrorCode Type="int" Value="' . $error . '" /> </Returnset>' . PHP_EOL . '

        </Result>' . PHP_EOL . '

        </PKT>';

        header('Content-Type: application/xml');

        print $response;

        $log = $log . "\r\n" . "RESPUESTA (DINOS): " . "\r\n" . $response;

    }

}

if ($action == "BETCHECK") {

    //$data          = array('req' => ($stringjson));

    $data = ($stringjson);

    $header = "Content-type: application/x-www-form-urlencoded\r\n";

    $result = ws_itainment_procesar($data);

    $result_decryp = ($result);

    $respuesta = preg_split("/_/", $result_decryp);

    $log = $log . "\r\n" . "RESPUESTA (BETCHECK): " . $result_decryp . "\r\n" .

        "" . "\r\n";

    $betcheck = "";

    if ($respuesta[0] == "OK") {

        if ($respuesta[2] == $ws_key_prematch) {

            $userperfil = $respuesta[1];

            $userid = $respuesta[3];

            $usernombre = $respuesta[4];

            $usersaldo = $respuesta[5];

            $betcheck = $respuesta[6];

            $response = '<PKT>' . "\r\n" . '

                            <Result Name="' . $xml->Method->attributes()['Name'] . '" Success="1">' . "\r\n" . '

                                <Returnset>' . "\r\n" . '

                                    <Token Type="string" Value="' . $xml->Method->Params->Token->attributes()['Value'] . '" />' . "\r\n" . '

                                    <ExtTransactionID Type="string" Value="' . $extraid . '" />' . "\r\n" . '

                                    <AlreadyProcessed Type="bool" Value="' . $betcheck . '" />' . "\r\n" . '

                                    <Balance Type="string" Value="' . $usersaldo . '" />' . "\r\n" . '

                                </Returnset>' . "\r\n" . '

                            </Result>' . "\r\n" . '

                        </PKT>';

        }

    } elseif ($respuesta[0] == "ERROR") {

        $error = $respuesta[1];

        $textoerror = $respuesta[2];

        $response = '\r\n<PKT>' . "\r\n" . '

                            <Result Name="' . $xml->Method->attributes()['Name'] . '" Success="0"> <Returnset>' . "\r\n" . '

                                <Error Type="string" Value="' . $textoerror . '" />' . "\r\n" . '

                                <ErrorCode Type="int" Value="' . $error . '" /> </Returnset>' . "\r\n" . '

                            </Result>' . "\r\n" . '

                        </PKT>';

    } else {

        //$data          = array('req' => ($stringjson));

        $data = ($stringjson);

        $header = "Content-type: application/x-www-form-urlencoded\r\n";

        $result = ws_itainment_procesar($data);

        $result_decryp = ($result);

        $respuesta = preg_split("/_/", $result_decryp);

        $log = $log . "\r\n" . "RESPUESTA REENVIO (BETCHECK): " . $result_decryp . "\r\n" .

            "" . "\r\n";

        $betcheck = "";

        if ($respuesta[0] == "OK") {

            if ($respuesta[2] == $ws_key_prematch) {

                $userperfil = $respuesta[1];

                $userid = $respuesta[3];

                $usernombre = $respuesta[4];

                $usersaldo = $respuesta[5];

                $betcheck = $respuesta[6];

                $response = '<PKT>' . "\r\n" . '

                                <Result Name="' . $xml->Method->attributes()['Name'] . '" Success="1">' . "\r\n" . '

                                    <Returnset>' . "\r\n" . '

                                        <Token Type="string" Value="' . $xml->Method->Params->Token->attributes()['Value'] . '" />' . "\r\n" . '

                                        <ExtTransactionID Type="string" Value="' . $extraid . '" />' . "\r\n" . '

                                        <AlreadyProcessed Type="bool" Value="' . $betcheck . '" />' . "\r\n" . '

                                        <Balance Type="string" Value="' . $usersaldo . '" />' . "\r\n" . '

                                    </Returnset>' . "\r\n" . '

                                </Result>' . "\r\n" . '

                            </PKT>';

            }

        } elseif ($respuesta[0] == "ERROR") {

            $error = $respuesta[1];

            $textoerror = $respuesta[2];

            $response = '<PKT>' . "\r\n" . '

                                <Result Name="' . $xml->Method->attributes()['Name'] . '" Success="0"> ' . "\r\n" . '

                                    <Returnset>' . "\r\n" . '

                                        <Error Type="string" Value="' . $textoerror . '" />' . "\r\n" . '

                                        <ErrorCode Type="int" Value="' . $error . '" /> </Returnset>' . "\r\n" . '

                                    </Result>' . "\r\n" . '

                            </PKT>';

        } else {

            $error = "95";

            $textoerror = "Hubo un error desconocido en nuestro sistema.";

        }

    }

    header('Content-Type: application/xml');

    print $response;

    $log = $log . "\r\n" . "\r\n" . "RESPUESTA (DINOS): " . $response . "\r\n" .

        "" . "\r\n";

}

if (!is_dir('logs')) {

    // dir doesn't exist, make it

    mkdir('logs');

}

$log = $log . "\r\n" . "-------------------------" . "\r\n";

//Save string to log, use FILE_APPEND to append.

$fp = fopen('logs/log_' . date("Y-m-d") . '.log', 'a');

fwrite($fp, $log);

fclose($fp);
