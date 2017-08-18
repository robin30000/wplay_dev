<?php

$url_IT_productivo = 'http://api-itainment.biahosted.com/wm/IntegrationService.asmx?wsdl';
$url_IT_desarrollador = 'http://api-itainment.uat.biahosted.com/integrationApi/IntegrationService.asmx?wsdl';

$url_IT=$url_IT_desarrollador;

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
    $result  = file_get_contents($url, false, $context);
    return $result;
}

function It_Obtener_Codigo_Error($error){
    switch ($error) {
        case "NoError":
            return 0;
            break;
        case "Success":
            return 0;
            break;
        case "UserRegDate":
            return 1;
            break;
        case "ClientNotFound":
            return 2;
            break;
        case "BonusPlanNotFound":
            return 3;
            break;
        case "HasOpenBonus":
            return 4;
            break;
        case "BonusCodeError":
            return 5;
            break;
        case "MinDepositError":
            return 6;
            break;
        case "CountryError":
            return 7;
            break;
        case "PlanAmountLimit":
            return 8;
            break;
        case "PlanCountLimit":
            return 9;
            break;
        case "InternalError":
            return 10;
            break;
        case "ZeroBonus":
            return 11;
            break;
        case "DuplicateUserName":
            return 12;
            break;
        case "UserRejected":
            return 13;
            break;
        case "ProviderError":
            return 14;
            break;
        case "User already exist":
            return 15;
            break;
    }

}

function IT_Crear_Usuario($walletcode,$usuario_nombre,$usuario_id,$usuario_pais,$usuario_moneda,$usuario_afiliacionpath,$usuario_saldo){
    global $url_IT;
    $wsdl = $url_IT;

    $trace      = true;
    $exceptions = false;

    $xml_array = array();
    $xml_array['LoginName']            = $usuario_nombre;
    $xml_array['Country']              = $usuario_pais;
    $xml_array['Currency']            = $usuario_moneda;
    $xml_array['ExternalUserId']              = $usuario_id;
    $xml_array['AffiliationPath']          = $usuario_afiliacionpath;
    $xml_array['UserBalance'] = $usuario_saldo;

    $client   = new SoapClient($wsdl, array('trace' => $trace, 'exceptions' => $exceptions));
    $response = $client->CreateUser(array('extUser' => $xml_array, 'walletcode' => $walletcode));

    $mensaje= $response->CreateBonusByCodeMessageResult;
    /*

    RESPONSE

    “Success” - The user was successfully created.
    “DuplicateUserName” - The user name already exists in the database for the application.
    “UserRejected” - The user was not created, for a reason defined by the provider.
    “ProviderError” - The provider returned an error that is not described by other enumeration values.
    "User already exist" - In case when user already exists.
    */

    $data = [ 'mensaje' => $mensaje];

    $json= json_encode( $data );
    return $json;
}

function IT_Agregar_Bono_Codigo($walletcode,$usuario_id,$usuario_pais,$usuario_deposito,$usuario_fecha_registro,$bono_codigo,$bono_planid){
    global $url_IT;
    $wsdl = $url_IT;

    $trace      = true;
    $exceptions = false;

    $xml_array = array();
    $xml_array['ExtUserId']            = $usuario_id;
    $xml_array['WalletCode']           = $walletcode;
    $xml_array['Country']              = $usuario_pais;
    $xml_array['BonusCode']            = $bono_codigo;
    $xml_array['Deposit']              = $usuario_deposito;
    $xml_array['BonusPlanId']          = $bono_planid;
    $xml_array['UserRegistrationDate'] = $usuario_fecha_registro;

    $client   = new SoapClient($wsdl, array('trace' => $trace, 'exceptions' => $exceptions));
    $response = $client->CreateBonusByCode(array('uDeposit' => $xml_array));

    $error= $response->CreateBonusByCodeMessageResult->Error;
    $bonus= $response->CreateBonusByCodeMessageResult->Bonus;
    $bonus_accountid= $response->CreateBonusByCodeMessageResult->BonusAccountId;

    $data = [ 'error' => It_Obtener_Codigo_Error($error), 'bonus' => $bonus,'bonus_cuentaid' => $bonus_accountid ];

    /*
	Field type of BonusError:

	NoError – means bonus account was created and bonus was given.
	InternalError – error in the Sportsbook system.
	ClientNotFound – client was not found by the ExtUserId request param.
	BonusPlanNotFound – bonus plan was not found by Code request param or Bonus plan is not active.
	HasOpenBonus – client already has opened bonus account of the requested bonus campaign.
	BonusCodeError – code is null or empty.
	MinDepositError – deposit amount in the request does not fit bonus plan requirements.
	CountryError – country in the request does not fit bonus plan requirements.
	PlanAmountLimit – amount of given bonuses exceeded the total amount of bonus plan.
	PlanCountLimit – count of bonuses exceeds the parameter of bonus plan.

    */

    $json= json_encode( $data );
    return $json;
}

function IT_Agregar_Bono_Deposito($walletcode,$usuario_id,$usuario_pais,$usuario_deposito,$bono_planid){
    global $url_IT;
    $wsdl = $url_IT;

    $trace      = true;
    $exceptions = false;
    $xml_array = array();

    $xml_array['ExtUserId']   = strval($usuario_id);
    $xml_array['WalletCode']  = $walletcode;
    $xml_array['Country']     = $usuario_pais;
    $xml_array['Deposit']     = $usuario_deposito;
    $xml_array['BonusPlanId'] = strval($bono_planid);


    $client   = new SoapClient($wsdl, array('trace' => $trace, 'exceptions' => $exceptions));
    $response = $client->CreateBonusByDeposit(array('uDeposit' => $xml_array));

    $error= $response->CreateBonusByDepositMessageResult->Error;
    $bonus= $response->CreateBonusByDepositMessageResult->Bonus;
    $bonus_accountid= $response->CreateBonusByDepositMessageResult->BonusAccountId;

    $data = [ 'error' => It_Obtener_Codigo_Error($error), 'bonus' => $bonus,'bonus_cuentaid' => $bonus_accountid,'error_message' => $error];
    /*
	Field type of BonusError:

	NoError – means bonus account was created and bonus was given.
	InternalError – error in the Sportsbook system.
	ClientNotFound – client was not found by the ExtUserId request param.
	BonusPlanNotFound – bonus plan was not found by Code request param or Bonus plan is not active.
	HasOpenBonus – client already has opened bonus account of the requested bonus campaign.
	BonusCodeError – code is null or empty.
	MinDepositError – deposit amount in the request does not fit bonus plan requirements.
	CountryError – country in the request does not fit bonus plan requirements.
	PlanAmountLimit – amount of given bonuses exceeded the total amount of bonus plan.
	PlanCountLimit – count of bonuses exceeds the parameter of bonus plan.

    */

    $json= json_encode( $data );
    return $json;
}

function IT_Obtener_Informacion_Cliente($walletcode,$usuario_id){
    global $url_IT;
    $wsdl = $url_IT;

    $trace      = true;
    $exceptions = false;
    $xml_array = array();

    $xml_array['ExtUserId']   = $usuario_id;
    $xml_array['WalletCode']  = $walletcode;

    $client   = new SoapClient($wsdl, array('trace' => $trace, 'exceptions' => $exceptions));
    $response = $client->GetClientBonusInfo(array('extUser' => $usuario_id, 'walletcode' => $walletcode));

    $fecha_creado= $response->GetClientBonusInfoMessageResult->CreatedDate;
    $fecha_actualizado= $response->GetClientBonusInfoMessageResult->UpdatedDate;
    $fecha_expiracion= $response->GetClientBonusInfoMessageResult->ExpiredDate;
    $monto= $response->GetClientBonusInfoMessageResult->Amount;
    $bono_inicial= $response->GetClientBonusInfoMessageResult->InitialBonus;
    $rollower_required= $response->GetClientBonusInfoMessageResult->RolloverRequired;
    $rollower_remain= $response->GetClientBonusInfoMessageResult->RolloverRemain;
    $bono_estado= $response->GetClientBonusInfoMessageResult->BonusStatus;

    $data = [ 'fecha_creado' => $fecha_creado, 'fecha_actualizado' => $fecha_actualizado,'fecha_expiracion' => $fecha_expiracion,'monto' => $monto,'bono_inicial' => $bono_inicial,'rollower_required' => $rollower_required,'rollower_remain' => $rollower_remain,'bono_estado' => $bono_estado ];

    $json= json_encode( $data );
    return $json;
}

function IT_Obtener_Todos_Bonos($walletcode,$usuario_id){
    global $url_IT;
    $wsdl = $url_IT;

    $trace      = true;
    $exceptions = false;
    $xml_array = array();

    $xml_array['ExtUserId']   = $usuario_id;
    $xml_array['WalletCode']  = $walletcode;

    //$client   = new SoapClient($wsdl, array('trace' => $trace, 'exceptions' => $exceptions));
    //$response = $client->GetCurrentBonusCampsByExtUserId($xml_array);

    $data          = $xml_array;
    $header        = "";
    $result   		= SendPost('http://api-itainment.uat.biahosted.com/integrationApi/IntegrationService.asmx/GetCurrentBonusCampsByExtUserId',$header, $data);



    $xml = new SimpleXMLElement($result);


    $data_all='{"bonus":[';
    $i=0;
    foreach ($xml->BonusPlan as $bonus) {


        $BonusPlanId=$bonus->BonusPlanId;
        $Nombre=$bonus->Name;
        $Description=$bonus->Description;
        $BonusType=$bonus->BonusType;
        $IsEnabled=$bonus->IsEnabled;
        $BonusCode=$bonus->BonusCode;
        $StartDate=$bonus->StartDate;
        $EndDate=$bonus->EndDate;
        $ExpirationPeriod=$bonus->ExpirationPeriod;
        $BonusType_Texto="";

        if($BonusType==1){
            $BonusType_Texto="C";
        }elseif($BonusType==2){
            $BonusType_Texto="D";
        }elseif($BonusType==3){
            $BonusType_Texto="PD";
        }elseif($BonusType==4){
            $BonusType_Texto="F";
        }


        if($i==0){
            $data ='{"tipo":"'.$BonusType_Texto.'","nombre":"'.$Nombre.'","descripcion":"'.$Description.'","codigo":"'.$BonusCode.'","fecha_inicio":"'.$StartDate.'","expiracion":"'.$ExpirationPeriod.'","fecha_fin":"'.$EndDate.'","habilitado":"'.$IsEnabled.'","bonusplanid":"'.$BonusPlanId.'"}';
            $i=$i+1;

        }else{
            $data =',{"tipo":"'.$BonusType_Texto.'","nombre":"'.$Nombre.'","descripcion":"'.$Description.'","codigo":"'.$BonusCode.'","fecha_inicio":"'.$StartDate.'","expiracion":"'.$ExpirationPeriod.'","fecha_fin":"'.$EndDate.'","habilitado":"'.$IsEnabled.'","bonusplanid":"'.$BonusPlanId.'"}';
            $i=$i+1;

        }
        $data_all=$data_all.$data;

    }

    $data_all=$data_all."]}";

    return $data_all;
}

function IT_Obtener_Detalle_Bono($bono_id){
    global $url_IT;
    $wsdl = $url_IT;

    $trace      = true;
    $exceptions = false;


    $client   = new SoapClient($wsdl, array('trace' => $trace, 'exceptions' => $exceptions));
    $response = $client->GetActiveBonusesByPlanId(array('bonusPlanId' => $bono_id));

    $plan_id= $response->GetActiveBonusesByPlanIdResult->PlanId;
    $plan_name= $response->GetActiveBonusesByPlanIdResult->PlanName;
    $fecha_inicio= $response->GetActiveBonusesByPlanIdResult->SDate;
    $fecha_fin= $response->GetActiveBonusesByPlanIdResult->EDate;
    $usuarios= $response->GetActiveBonusesByPlanIdResult->Players;

    $data = [ 'plan_id' => $plan_id, 'plan_name' => $plan_name,'fecha_inicio' => $fecha_inicio,'fecha_fin' => $fecha_fin];
    $json= json_encode( $data );
    return $json;
}

function IT_Obtener_Detalles_Apuesta($betId){
    global $url_IT;
    $wsdl = $url_IT;

    $trace      = true;
    $exceptions = false;


    $client   = new SoapClient($wsdl, array('trace' => $trace, 'exceptions' => $exceptions));
    $response = $client->GetBetDetails(array('betId' => $betId));


    $error= $response->GetBetDetailsResponseMessageResult->IsError;
    if($error==""){
        $Bet= $response->GetBetDetailsResponseMessageResult->Bet;
        $TotalApuesta =$Bet->TotalStake;
        $PotentionalWin =$Bet->PotentionalWin;
        $Winnings =$Bet->Winnings;
        $BetStatus =$Bet->BetStatus;
        $BetType =$Bet->BetType;
        $BetStatus =$Bet->BetStatus;
        $BetStakes =$Bet->BetStakes;
        $BetMarkets =$Bet->BetMarkets;
        $CreatedDate=$Bet->CreatedDate;
        $SettlementDate=$Bet->SettlementDate;
        $ClientId=$Bet->ClientId;
        $ClientName=$Bet->ClientName;
        $BetTypesStr=$Bet->BetTypesStr;
        $CurrencySign=$Bet->CurrencySign;
        $CurrChar=$Bet->CurrChar;
        $Bonus=$Bet->Bonus;
        $IsPaid=$Bet->IsPaid;
        $PrintCount=$Bet->PrintCount;
        $PaidOutDate=$Bet->PaidOutDate;
        $RealWin=$Bet->RealWin;

        $BetMarket =$BetMarkets->BetMarket;

        $respuesta=$respuesta." - ".$TotalApuesta;

        if(is_array($BetMarket)){
            foreach ($BetMarket as $detalle)
            {
                $EventName =$detalle->EventName;
                $ChampName =$detalle->ChampName;
                $EventDate =$detalle->EventDate;
                $BetId =$detalle->BetId;
                $BetMarketId =$detalle->BetMarketId;
                $EvetCode=$detalle->EvetCode;
                $BankerCount=$detalle->BankerCount;
                $IsBanker=$detalle->IsBanker;

                $Odds=$detalle->Odds;
                foreach ($Odds as $Odd)
                {
                    $BetMarketId = $Odd->BetMarketId;
                    $OddId=$Odd->OddId;
                    $SelectionName = $Odd->SelectionName;
                    $Price = $Odd->Price;
                    $IsSettledInOT = $Odd->IsSettledInOT;
                    $SelectionId = $Odd->SelectionId;
                    $PrintMarketName = $Odd->PrintMarketName;
                    $PrintSelectionName = $Odd->PrintSelectionName;
                    $respuesta=$respuesta." - ".$OddId;

                }

            }
        }else
        {
            $detalle=$BetMarket;
            $EventName =$detalle->EventName;
            $ChampName =$detalle->ChampName;
            $EventDate =$detalle->EventDate;
            $BetId =$detalle->BetId;
            $BetMarketId =$detalle->BetMarketId;
            $EvetCode=$detalle->EvetCode;
            $BankerCount=$detalle->BankerCount;
            $IsBanker=$detalle->IsBanker;

            $Odds=$detalle->Odds;

            $Odd = $Odds ->Odd;
            $BetMarketId = $Odd->BetMarketId;
            $OddId=$Odd->OddId;
            $SelectionName = $Odd->SelectionName;
            $Price = $Odd->Price;
            $IsSettledInOT = $Odd->IsSettledInOT;
            $SelectionId = $Odd->SelectionId;
            $PrintMarketName = $Odd->PrintMarketName;
            $PrintSelectionName = $Odd->PrintSelectionName;
            $respuesta=$respuesta." - ".$OddId;


        }

    }
    return "";

}


function IT_Obtener_Detalles_Apuesta_XML($betId){
    global $url_IT;
    $wsdl = $url_IT;

    $trace      = true;
    $exceptions = false;


    $client   = new SoapClient($wsdl, array('trace' => $trace, 'exceptions' => $exceptions));
    $response = $client->GetBetDetails(array('betId' => $betId));
    $respuesta="H";


    $xml='<rows><page>1</page><total>100</total><records>1000</records>';

    if($error==""){
        $Bet= $response->GetBetDetailsResponseMessageResult->Bet;
        $TotalApuesta =$Bet->TotalStake;
        $PotentionalWin =$Bet->PotentionalWin;
        $Winnings =$Bet->Winnings;
        $BetStatus =$Bet->BetStatus;
        $BetType =$Bet->BetType;
        $BetStatus =$Bet->BetStatus;
        $BetStakes =$Bet->BetStakes;
        $BetMarkets =$Bet->BetMarkets;
        $CreatedDate=$Bet->CreatedDate;
        $SettlementDate=$Bet->SettlementDate;
        $ClientId=$Bet->ClientId;
        $ClientName=$Bet->ClientName;
        $BetTypesStr=$Bet->BetTypesStr;
        $CurrencySign=$Bet->CurrencySign;
        $CurrChar=$Bet->CurrChar;
        $Bonus=$Bet->Bonus;
        $IsPaid=$Bet->IsPaid;
        $PrintCount=$Bet->PrintCount;
        $PaidOutDate=$Bet->PaidOutDate;
        $RealWin=$Bet->RealWin;

        $BetMarket =$BetMarkets->BetMarket;
        $Ganador="";
        $Estado="";


        if($BetStatus == "C"){
            $Ganador="";
            $Estado="Abierto";
        }else if($BetStatus == "S"){
            $Ganador="SI";
            $Estado="Ganador";
            $script="<script>$('#premio-numero').html(".$Winnings.");</script>";
        }else if($BetStatus == "N"){
            $Ganador="NO";
            $Estado="Perdedor";
        }else if($BetStatus == "A"){
            $Ganador="";
            $Estado="No Accion";
        }else if($BetStatus == "R"){
            $Ganador="";
            $Estado="Pendiente";
        }else if($BetStatus == "W"){
            $Ganador="";
            $Estado="En espera";
        }else if($BetStatus == "J"){
            $Ganador="";
            $Estado="Rechazado";
        }else if($BetStatus == "M"){
            $Ganador="";
            $Estado="Rechazado por regla";
        }else if($BetStatus == "T"){
            $Ganador="";
            $Estado="Retiro Voluntario";
            $script="<script>$('#premio-numero').html(".$Winnings.");</script>";
        }



        if(is_array($BetMarket)){
            foreach ($BetMarket as $detalle)
            {
                $EventName =$detalle->EventName;
                $ChampName =$detalle->ChampName;
                $EventDate2 =$detalle->EventDate;
                $EventDate = strtotime($EventDate2);

                $BetId =$detalle->BetId;
                $BetMarketId =$detalle->BetMarketId;
                $EvetCode=$detalle->EvetCode;
                $BankerCount=$detalle->BankerCount;
                $IsBanker=$detalle->IsBanker;

                $Odds=$detalle->Odds;
                $respuesta=$respuesta." - ".$EventName;
                $Odd = $Odds ->Odd;

                $BetMarketId = $Odd->BetMarketId;
                $OddId=$Odd->OddId;
                $SelectionName = $Odd->SelectionName;
                $Price = $Odd->Price;
                $IsSettledInOT = $Odd->IsSettledInOT;
                $SelectionId = $Odd->SelectionId;
                $PrintMarketName = $Odd->PrintMarketName;
                $PrintSelectionName = $Odd->PrintSelectionName;
                $respuesta=$respuesta." - ".$OddId;



                $SelectionName_Explode = explode(":", $SelectionName);


                $xml=$xml."<row id='".$OddId."'><cell>".$OddId."</cell><cell><![CDATA[".$EventName."]]></cell><cell>".date('Y-m-d',$EventDate)."</cell><cell>".date('H:i:s',$EventDate)."</cell><cell><![CDATA[".$SelectionName_Explode[0]."]]></cell><cell><![CDATA[".$SelectionName_Explode[1]."]]></cell><cell>".$Price."</cell><cell>-</cell><cell></cell></row>";


            }
        }else
        {
            $detalle=$BetMarket;
            $EventName =$detalle->EventName;
            $ChampName =$detalle->ChampName;
            $EventDate2 =$detalle->EventDate;
            $EventDate = strtotime($EventDate2);

            $BetId =$detalle->BetId;
            $BetMarketId =$detalle->BetMarketId;
            $EvetCode=$detalle->EvetCode;
            $BankerCount=$detalle->BankerCount;
            $IsBanker=$detalle->IsBanker;

            $Odds=$detalle->Odds;
            $respuesta=$respuesta." - ".$EventName;

            $Odd = $Odds ->Odd;
            $BetMarketId = $Odd->BetMarketId;
            $OddId=$Odd->OddId;
            $SelectionName = $Odd->SelectionName;
            $Price = $Odd->Price;
            $IsSettledInOT = $Odd->IsSettledInOT;
            $SelectionId = $Odd->SelectionId;
            $PrintMarketName = $Odd->PrintMarketName;
            $PrintSelectionName = $Odd->PrintSelectionName;
            $respuesta=$respuesta." - ".$OddId;

            $xml=$xml."<row id='".$OddId."'><cell>".$OddId."</cell><cell><![CDATA[".$EventName."]]></cell><cell>".date('Y-m-d',$EventDate)."</cell><cell>".date('H:i:s',$EventDate)."</cell><cell><![CDATA[".$SelectionName_Explode[0]."]]></cell><cell><![CDATA[".$SelectionName_Explode[1]."]]></cell><cell>".$Price."</cell><cell>-</cell><cell></cell></row>";



        }


    }
    $xml=$xml."</rows>";
    return $xml;
}



function IT_Obtener_Detalles_Apuesta_HTML($betId){
    global $url_IT;
    $wsdl = $url_IT;

    $trace      = true;
    $exceptions = false;


    $client   = new SoapClient($wsdl, array('trace' => $trace, 'exceptions' => $exceptions));
    $response = $client->GetBetDetails(array('betId' => $betId));
    $respuesta="H";


    $html='<div class="div-bet-detail-patern div-grid-own">';

    if($error==""){
        $Bet= $response->GetBetDetailsResponseMessageResult->Bet;
        $TotalApuesta =$Bet->TotalStake;
        $PotentionalWin =$Bet->PotentionalWin;
        $Winnings =$Bet->Winnings;
        $BetStatus =$Bet->BetStatus;
        $BetType =$Bet->BetType;
        $BetStatus =$Bet->BetStatus;
        $BetStakes =$Bet->BetStakes;
        $BetMarkets =$Bet->BetMarkets;
        $CreatedDate=$Bet->CreatedDate;
        $SettlementDate=$Bet->SettlementDate;
        $ClientId=$Bet->ClientId;
        $ClientName=$Bet->ClientName;
        $BetTypesStr=$Bet->BetTypesStr;
        $CurrencySign=$Bet->CurrencySign;
        $CurrChar=$Bet->CurrChar;
        $Bonus=$Bet->Bonus;
        $IsPaid=$Bet->IsPaid;
        $PrintCount=$Bet->PrintCount;
        $PaidOutDate=$Bet->PaidOutDate;
        $RealWin=$Bet->RealWin;

        $BetMarket =$BetMarkets->BetMarket;
        $Ganador="";
        $Estado="";
        $script="<script>$('#premio-numero').html();</script>";


        if($BetStatus == "C"){
            $Ganador="";
            $Estado="Abierto";
        }else if($BetStatus == "S"){
            $Ganador="SI";
            $Estado="Ganador";
            $script="<script>$('#premio-numero').html(".$Winnings.");</script>";
        }else if($BetStatus == "N"){
            $Ganador="NO";
            $Estado="Perdedor";
        }else if($BetStatus == "A"){
            $Ganador="";
            $Estado="No Accion";
        }else if($BetStatus == "R"){
            $Ganador="";
            $Estado="Pendiente";
        }else if($BetStatus == "W"){
            $Ganador="";
            $Estado="En espera";
        }else if($BetStatus == "J"){
            $Ganador="";
            $Estado="Rechazado";
        }else if($BetStatus == "M"){
            $Ganador="";
            $Estado="Rechazado por regla";
        }else if($BetStatus == "T"){
            $Ganador="";
            $Estado="Retiro Voluntario";
            $script="<script>$('#premio-numero').html(".$Winnings.");</script>";
        }

        $html=$html.$script;
        $html=$html.'<div class="div-row">
						<div class="div-colum div-25">
							<div class="div-title">Valor Apostado: </div>
						</div>
						<div class="div-colum div-75">
							<div class="div-content"> '.number_format($TotalApuesta,2).' </div>
						</div>
					</div>';

        $html=$html.'<div class="div-row">
						<div class="div-colum div-25">
							<div class="div-title">Premio Proyectado: </div>
						</div>
						<div class="div-colum div-75">
							<div class="div-content">'.number_format($Winnings,2).' </div>
						</div>
					</div>';
        $html=$html.'<div class="div-row">
						<div class="div-colum div-25">
							<div class="div-title">Estado: </div>
						</div>
						<div class="div-colum div-75">
							<div class="div-content"> '.$Estado.' </div>
						</div>
					</div>';
        $html=$html.'<div class="div-row">
						<div class="div-colum div-25">
							<div class="div-title">Ganador: </div>
						</div>
						<div class="div-colum div-75">
							<div class="div-content"> '.$Ganador.' </div>
						</div>
					</div>';
        $html=$html.'<div class="div-row">';

        $html=$html.'<div class="div-grid-table">';

        $html=$html.'<div class="div-grid-table-header">';

        $html=$html.'<div class="div-row div-30">
								<div class="div-colum div-100 text-center">
									<div class="div-title">Apuesta</div>
								</div>
							</div>';

        $html=$html.'<div class="div-row div-10">
								<div class="div-colum div-100 text-center">
									<div class="div-title">Fecha</div>
								</div>
							</div>';

        $html=$html.'<div class="div-row div-10">
								<div class="div-colum div-100 text-center">
									<div class="div-title">Hora</div>
								</div>
							</div>';

        $html=$html.'<div class="div-row div-10">
								<div class="div-colum div-100 text-center">
									<div class="div-title">Categoria</div>
								</div>
							</div>';

        $html=$html.'<div class="div-row div-25">
								<div class="div-colum div-100 text-center">
									<div class="div-title">Opcion Seleccionada</div>
								</div>
							</div>';

        $html=$html.'<div class="div-row div-10">
								<div class="div-colum div-100 text-center">
									<div class="div-title">Logro</div>
								</div>
							</div>';

        $html=$html.'<div class="div-row div-5">
								<div class="div-colum div-100 text-center">
									<div class="div-title">Acertado</div>
								</div>
							</div>';




        $html=$html.'</div>';

        $html=$html.'<div class="div-grid-table-content">';

        if(is_array($BetMarket)){
            foreach ($BetMarket as $detalle)
            {
                $EventName =$detalle->EventName;
                $ChampName =$detalle->ChampName;
                $EventDate2 =$detalle->EventDate;
                $EventDate = strtotime($EventDate2);

                $BetId =$detalle->BetId;
                $BetMarketId =$detalle->BetMarketId;
                $EvetCode=$detalle->EvetCode;
                $BankerCount=$detalle->BankerCount;
                $IsBanker=$detalle->IsBanker;

                $Odds=$detalle->Odds;
                $respuesta=$respuesta." - ".$EventName;
                $Odd = $Odds ->Odd;

                $BetMarketId = $Odd->BetMarketId;
                $OddId=$Odd->OddId;
                $SelectionName = $Odd->SelectionName;
                $Price = $Odd->Price;
                $IsSettledInOT = $Odd->IsSettledInOT;
                $SelectionId = $Odd->SelectionId;
                $PrintMarketName = $Odd->PrintMarketName;
                $PrintSelectionName = $Odd->PrintSelectionName;
                $respuesta=$respuesta." - ".$OddId;

                $html=$html.'<div class="div-grid-table-content-child">';

                $html=$html.'<div class="div-row div-30">
										<div class="div-colum div-100 text-center">
											<div class="div-content">'.$EventName.'</div>
										</div>
									</div>';

                $html=$html.'<div class="div-row div-10">
										<div class="div-colum div-100 text-center">
											<div class="div-content">'.date('Y-m-d',$EventDate).'</div>
										</div>
									</div>';

                $html=$html.'<div class="div-row div-10">
										<div class="div-colum div-100 text-center">
											<div class="div-content">'.date('H:i:s',$EventDate).'</div>
										</div>
									</div>';

                $SelectionName_Explode = explode(":", $SelectionName);

                $html=$html.'<div class="div-row div-10">
										<div class="div-colum div-100 text-center">
											<div class="div-content">'.$SelectionName_Explode[0].'</div>
										</div>
									</div>';

                $html=$html.'<div class="div-row div-25">
										<div class="div-colum div-100 text-center">
											<div class="div-content">'.$SelectionName_Explode[1].'</div>
										</div>
									</div>';

                $html=$html.'<div class="div-row div-10">
										<div class="div-colum div-100 text-center">
											<div class="div-content">'.$Price.'</div>
										</div>
									</div>';

                $html=$html.'<div class="div-row div-5">
										<div class="div-colum div-100 text-center">
											<div class="div-content"></div>
										</div>
									</div>';
                $html=$html.'</div>';



            }
        }else
        {
            $detalle=$BetMarket;
            $EventName =$detalle->EventName;
            $ChampName =$detalle->ChampName;
            $EventDate2 =$detalle->EventDate;
            $EventDate = strtotime($EventDate2);

            $BetId =$detalle->BetId;
            $BetMarketId =$detalle->BetMarketId;
            $EvetCode=$detalle->EvetCode;
            $BankerCount=$detalle->BankerCount;
            $IsBanker=$detalle->IsBanker;

            $Odds=$detalle->Odds;
            $respuesta=$respuesta." - ".$EventName;

            $Odd = $Odds ->Odd;
            $BetMarketId = $Odd->BetMarketId;
            $OddId=$Odd->OddId;
            $SelectionName = $Odd->SelectionName;
            $Price = $Odd->Price;
            $IsSettledInOT = $Odd->IsSettledInOT;
            $SelectionId = $Odd->SelectionId;
            $PrintMarketName = $Odd->PrintMarketName;
            $PrintSelectionName = $Odd->PrintSelectionName;
            $respuesta=$respuesta." - ".$OddId;

            $html=$html.'<div class="div-grid-table-content-child">';

            $html=$html.'<div class="div-row div-30">
									<div class="div-colum div-100 text-center">
										<div class="div-content">'.$EventName.'</div>
									</div>
								</div>';

            $html=$html.'<div class="div-row div-10">
									<div class="div-colum div-100 text-center">
										<div class="div-content">'.date('Y-m-d',$EventDate).'</div>
									</div>
								</div>';

            $html=$html.'<div class="div-row div-10">
									<div class="div-colum div-100 text-center">
										<div class="div-content">'.date('H:i:s',$EventDate).'</div>
									</div>
								</div>';

            $SelectionName_Explode = explode(":", $SelectionName);

            $html=$html.'<div class="div-row div-10">
									<div class="div-colum div-100 text-center">
										<div class="div-content">'.$SelectionName_Explode[0].'</div>
									</div>
								</div>';

            $html=$html.'<div class="div-row div-25">
									<div class="div-colum div-100 text-center">
										<div class="div-content">'.$SelectionName_Explode[1].'</div>
									</div>
								</div>';

            $html=$html.'<div class="div-row div-10">
									<div class="div-colum div-100 text-center">
										<div class="div-content">'.$Price.'</div>
									</div>
								</div>';

            $html=$html.'<div class="div-row div-5">
									<div class="div-colum div-100 text-center">
										<div class="div-content"></div>
									</div>
								</div>';
            $html=$html.'</div>';
            $html=$html.'</div>';

        }

        $html=$html.'</div>';
        $html=$html.'</div>';


        $html=$html.'<div class="div-grid-table-movil">';

        $html=$html.'<div class="div-grid-table-content">';

        if(is_array($BetMarket)){
            foreach ($BetMarket as $detalle)
            {
                $EventName =$detalle->EventName;
                $ChampName =$detalle->ChampName;
                $EventDate2 =$detalle->EventDate;
                $EventDate = strtotime($EventDate2);

                $BetId =$detalle->BetId;
                $BetMarketId =$detalle->BetMarketId;
                $EvetCode=$detalle->EvetCode;
                $BankerCount=$detalle->BankerCount;
                $IsBanker=$detalle->IsBanker;

                $Odds=$detalle->Odds;
                $respuesta=$respuesta." - ".$EventName;
                $Odd = $Odds ->Odd;

                $BetMarketId = $Odd->BetMarketId;
                $OddId=$Odd->OddId;
                $SelectionName = $Odd->SelectionName;
                $Price = $Odd->Price;
                $IsSettledInOT = $Odd->IsSettledInOT;
                $SelectionId = $Odd->SelectionId;
                $PrintMarketName = $Odd->PrintMarketName;
                $PrintSelectionName = $Odd->PrintSelectionName;
                $respuesta=$respuesta." - ".$OddId;

                $html=$html.'<div class="div-grid-table-content-child">';
                $html=$html.'<div class="div-grid-table-content-child-title">'.$EventName.'</div>';

                $html=$html.'<div class="div-grid-table-content-child-content" style="display:none;">';

                $html=$html.'<div class="div-row">';
                $html=$html.'<div class="div-colum">
											<div class="div-title">Apuesta</div>
										</div>
										<div class="div-colum">
											<div class="div-content">'.$EventName.'</div>
										</div>';
                $html=$html.'</div>';

                $html=$html.'<div class="div-row">';
                $html=$html.'<div class="div-colum">
											<div class="div-title">Fecha</div>
										</div>
										<div class="div-colum">
											<div class="div-content">'.$EventDate.' '.date('Y-m-d',$EventDate).'</div>
										</div>';
                $html=$html.'</div>';

                $html=$html.'<div class="div-row">';
                $html=$html.'<div class="div-colum">
											<div class="div-title">Hora</div>
										</div>
										<div class="div-colum">
											<div class="div-content">'.$EventDate.' '.date('H:i:s',$EventDate).'</div>
										</div>';
                $html=$html.'</div>';

                $SelectionName_Explode = explode(":", $SelectionName);

                $html=$html.'<div class="div-row">';
                $html=$html.'<div class="div-colum">
											<div class="div-title">Categoria</div>
										</div>
										<div class="div-colum">
											<div class="div-content">'.$SelectionName_Explode[0].'</div>
										</div>';
                $html=$html.'</div>';

                $html=$html.'<div class="div-row">';
                $html=$html.'<div class="div-colum">
											<div class="div-title">Opcion Seleccionada</div>
										</div>
										<div class="div-colum">
											<div class="div-content">'.$SelectionName_Explode[1].'</div>
										</div>';
                $html=$html.'</div>';

                $html=$html.'<div class="div-row">';
                $html=$html.'<div class="div-colum">
											<div class="div-title">Logro</div>
										</div>
										<div class="div-colum">
											<div class="div
											-content">'.$Price.'</div>
										</div>';
                $html=$html.'</div>';

                $html=$html.'<div class="div-row">';
                $html=$html.'<div class="div-colum">
											<div class="div-title">Acertado</div>
										</div>
										<div class="div-colum">
											<div class="div-content"></div>
										</div>';
                $html=$html.'</div>';

                $html=$html.'</div>';
                $html=$html.'</div>';



            }
        }else
        {
            $detalle=$BetMarket;
            $EventName =$detalle->EventName;
            $ChampName =$detalle->ChampName;
            $EventDate2 =$detalle->EventDate;
            $EventDate = strtotime($EventDate2);

            $BetId =$detalle->BetId;
            $BetMarketId =$detalle->BetMarketId;
            $EvetCode=$detalle->EvetCode;
            $BankerCount=$detalle->BankerCount;
            $IsBanker=$detalle->IsBanker;

            $Odds=$detalle->Odds;
            $respuesta=$respuesta." - ".$EventName;

            $Odd = $Odds ->Odd;
            $BetMarketId = $Odd->BetMarketId;
            $OddId=$Odd->OddId;
            $SelectionName = $Odd->SelectionName;
            $Price = $Odd->Price;
            $IsSettledInOT = $Odd->IsSettledInOT;
            $SelectionId = $Odd->SelectionId;
            $PrintMarketName = $Odd->PrintMarketName;
            $PrintSelectionName = $Odd->PrintSelectionName;
            $respuesta=$respuesta." - ".$OddId;

            $html=$html.'<div class="div-grid-table-content-child">';
            $html=$html.'<div class="div-grid-table-content-child-title">'.$EventName.'</div>';

            $html=$html.'<div class="div-grid-table-content-child-content" style="display:none;">';

            $html=$html.'<div class="div-row">';
            $html=$html.'<div class="div-colum">
										<div class="div-title">Apuesta</div>
									</div>
									<div class="div-colum">
										<div class="div-content">'.$EventName.'</div>
									</div>';
            $html=$html.'</div>';

            $html=$html.'<div class="div-row">';
            $html=$html.'<div class="div-colum">
										<div class="div-title">Fecha</div>
									</div>
									<div class="div-colum">
										<div class="div-content">'.$EventDate.' '.date('Y-m-d',$EventDate).'</div>
									</div>';
            $html=$html.'</div>';

            $html=$html.'<div class="div-row">';
            $html=$html.'<div class="div-colum">
										<div class="div-title">Hora</div>
									</div>
									<div class="div-colum">
										<div class="div-content">'.$EventDate.' '.date('H:i:s',$EventDate).'</div>
									</div>';
            $html=$html.'</div>';

            $SelectionName_Explode = explode(":", $SelectionName);

            $html=$html.'<div class="div-row">';
            $html=$html.'<div class="div-colum">
										<div class="div-title">Categoria</div>
									</div>
									<div class="div-colum">
										<div class="div-content">'.$SelectionName_Explode[0].'</div>
									</div>';
            $html=$html.'</div>';

            $html=$html.'<div class="div-row">';
            $html=$html.'<div class="div-colum">
										<div class="div-title">Opcion Seleccionada</div>
									</div>
									<div class="div-colum">
										<div class="div-content">'.$SelectionName_Explode[1].'</div>
									</div>';
            $html=$html.'</div>';

            $html=$html.'<div class="div-row">';
            $html=$html.'<div class="div-colum">
										<div class="div-title">Logro</div>
									</div>
									<div class="div-colum">
										<div class="div
										-content">'.$Price.'</div>
									</div>';
            $html=$html.'</div>';

            $html=$html.'<div class="div-row">';
            $html=$html.'<div class="div-colum">
										<div class="div-title">Acertado</div>
									</div>
									<div class="div-colum">
										<div class="div-content"></div>
									</div>';
            $html=$html.'</div>';

            $html=$html.'</div>';
            $html=$html.'</div>';


        }

        $html=$html.'</div>';

        $html=$html."
						<script>
						$('.div-grid-table-content-child-title').on('click', function(e) {
  							var child = $(this).parent().children('.div-grid-table-content-child-content');
							child.toggle();
					        $('.div-grid-table-content-child-content').not(child).hide();
					    });
					    </script>
					    ";


        $html=$html.'</div>';

    }
    $html=$html.'</div>';
    //$data = [ 'plan_id' => $plan_id, 'plan_name' => $plan_name,'fecha_inicio' => $fecha_inicio,'fecha_fin' => $fecha_fin];
    //$json= json_encode( $data );


    return $html;
}