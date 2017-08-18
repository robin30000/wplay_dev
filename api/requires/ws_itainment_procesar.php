<?php

function ws_itainment_procesar($request)
{
    /*  ESTADOS DE LAS APUESTAS DE ITAINMENT
    C = Open (Abierto)
    S = Won (Gan�)
    N = Lost (Perdi�)
    A = Void (No Acci�n)
    R = Pending (Pendiente)
    W = Waiting (En Espera)
    J = Rejected (Rechazada)
    M = RejectedByMTS (Rechazada por Regla)
    T = Cashout (Retiro Voluntario)
    */

    // Archivo que incluye todas las variables globales necesarias
    include "global.php";
    include "funciones.php";
    date_default_timezone_set('America/Bogota');

    //Abre la conexi�n a la base de datos
    $Conn = ConectarBD($bd_servidor, $bd_usuario, $bd_clave, $bd_nombre);

    //Recibe los par�metros pasados en la URL
    $req = $request;
    $obj = json_decode($req);
    $chequeo = "_FALSE";

    //Inicializa la respuesta
    $respuesta = "";

    //Valida los campos ingresados
    $seguir = true;
    if (count($obj) <= 0)
        $seguir = false;

    //Formatea las variables separadas
    if ($seguir) {
        //Captura las variables
        $key_winplay = $obj->{'KeyWinplay'};
        $tipo = DepurarCaracteres($obj->{'TypeWinplay'});
        $key_usuario = DepurarCaracteres($obj->{'Token'});
        $trans_id = DepurarCaracteres($obj->{'TransactionID'});
        $valor = DepurarCaracteres($obj->{'valor'});
        $ticket_id = DepurarCaracteres($obj->{'ticketid'});
        $game_reference = DepurarCaracteres($obj->{'GameReference'});
        $bet_status = DepurarCaracteres($obj->{'BetStatus'});
        $cant_lineas = DepurarCaracteres($obj->{'EventCount'});
        $cant_banker = DepurarCaracteres($obj->{'BankerCount'});
        $premio_proy = DepurarCaracteres($obj->{'PremioProyectado'});
        $bonus_id = DepurarCaracteres($obj->{'BonusId'});
        $bonusplan_id = DepurarCaracteres($obj->{'BonusPlanId'});
		$frontend = DepurarCaracteres($obj->{'frontend'});
		$betmode = DepurarCaracteres($obj->{'betmode'});
		$dir_ip = DepurarCaracteres($obj->{'ClienteIP'});
		$extuser_id = DepurarCaracteres($obj->{'usuarioid'});

        //Valida los campos pasados
        if (!ValidarCampo($key_winplay, "S", "T", 500))
            $seguir = false;
        if (!ValidarCampo($tipo, "S", "T", 15))
            $seguir = false;
        else {
            if ($tipo != "BET" and $tipo != "WIN" and $tipo != "REFUND" and $tipo != "LOSS" and $tipo != "BETCHECK" and $tipo != "NEWDEBIT" AND $tipo != "NEWCREDIT" and $tipo != "CASHOUT" and $tipo != "STAKEDECREASE" and $tipo != "WINBONUS")
                $seguir = false;
        }
        if (!ValidarCampo($valor, "S", "N", 20))
            $seguir = false;
        if (!ValidarCampo($ticket_id, "S", "T", 15))
            $seguir = false;
        if (!ValidarCampo($game_reference, "S", "T", 50))
            $seguir = false;
        if (!ValidarCampo($bet_status, "S", "T", 1))
            $seguir = false;
        if (!ValidarCampo($trans_id, "S", "T", 20))
            $seguir = false;
		if (!ValidarCampo($extuser_id, "S", "N", 20))
            $seguir = false;
    }

    //Verifica si a�n se puede continuar luego de validaci�n previa de campos
    if ($seguir) {

        //Verifica si la clave de integraci�n suministrada es correcta
        $key_ref = hash_hmac("sha256", $key_casino_ref, $auth_casino, false);
        if ($key_winplay != $key_ref)
            $respuesta = "ERROR_99_No dispone de los privilegios suficientes para acceder a este servicio.";
        else {
            //Valida que el usuario suministrado exista
            $datosSql = "select a.usuario_id,aes_decrypt(a.nombre,'".$clave_encrypt."') nombre,case when b.perfil_id='USUONLINE' then 'USER' when b.perfil_id='SA' or b.perfil_id like 'ADMIN%' then 'ADMIN' else 'OTHER' end perfil,round(case when c.usuario_id is null then 0 else c.creditos+c.creditos_base end,0) saldo from " . $winplay . ".usuario a inner join " . $winplay . ".usuario_perfil b on (a.mandante=b.mandante and a.usuario_id=b.usuario_id) left outer join " . $winplay . ".registro c on (a.mandante=c.mandante and a.usuario_id=c.usuario_id) where a.mandante=" . $mandante . " and a.usuario_id=" . $extuser_id;
            $datos_RS_query = ProcesarConsulta($datosSql, $Conn);
            $datos_RS = mysql_fetch_array($datos_RS_query);
            if (($datos_RS == 0))
                $respuesta = "ERROR_90_El usuario suministrado no se encuentra registrado en la base de datos.";
            else {
                //Armar la nueva descripci�n
                $strSql = array();
                $contSql = 0;

                //Captura los valores necesarios
                $perfil = $datos_RS["perfil"];
                $usuario_id = $datos_RS["usuario_id"];
                $nombre = $datos_RS["nombre"];

                //Actualiza el saldo
                $valor = $valor / 100;		
				
				$excvalorSql = "select case when b.usuario_id is null then 'NE' when '".$tipo."'<>'BET' then 'NE' else 'E' end tipo,";
				$excvalorSql = $excvalorSql . "case when sum(case when date(substr(a.fecha_crea,1,10))=date(now()) then a.vlr_apuesta else 0 end)+".$valor." > b.limite_diario then 'LD' ";
				$excvalorSql = $excvalorSql . "when sum(case when date(substr(a.fecha_crea,1,10)) between date_sub(date(now()), interval 7 day) and date(now()) then a.vlr_apuesta else 0 end)+".$valor." > b.limite_semanal then 'LS' ";
				$excvalorSql = $excvalorSql . "when sum(a.vlr_apuesta)+".$valor." > b.limite_mensual then 'LM' ";
				$excvalorSql = $excvalorSql . "when ".$valor.">b.limite_diario then 'LD' ";
				$excvalorSql = $excvalorSql . "else 'NA' end clase ";
				$excvalorSql = $excvalorSql . "from ".$winplay.".configuracion x ";
				$excvalorSql = $excvalorSql . "left outer join ".$winplay.".it_ticket_enc a on (a.mandante=x.mandante and date(a.fecha_crea) between date_sub(now(), interval 30 day) and now() and a.usuario_id=".$usuario_id.")";
				$excvalorSql = $excvalorSql . "left outer join ".$winplay.".usuario_autoexclusion_apuesta b on (b.mandante=x.mandante and b.usuario_id=".$usuario_id." and date(now()) between date(substr(b.fecha_crea,1,10)) and date(b.fecha_fin)) ";
				$excvalorSql = $excvalorSql . "where x.mandante=".$mandante;
				$excvalor_RS_query = ProcesarConsulta($excvalorSql, $Conn);

                $excvalor_RS = mysql_fetch_array($excvalor_RS_query);
                if ($excvalor_RS["tipo"] == "E" and $excvalor_RS["clase"] != "NA") {
                    if ($excvalor_RS["clase"] == "LD")
                        $respuesta = "ERROR_10_El usuario ha superado el tope diario configurado en el modulo de autoexclusion.";
                    else {
                        if ($excvalor_RS["clase"] == "LS")
                            $respuesta = "ERROR_10_El usuario ha superado el tope semanal configurado en el modulo de autoexclusion.";
                        else
                            $respuesta = "ERROR_10_El usuario ha superado el tope mensual configurado en el modulo de autoexclusion.";
                    }
                } else {
                    //Valida la autoexclusion por juego
                    $excjuegoSql = "select case when b.usuario_id is null then 'NE' else 'E' end tipo ";
                    $excjuegoSql = $excjuegoSql . "from " . $winplay . ".configuracion x ";
                    $excjuegoSql = $excjuegoSql . "left outer join " . $winplay . ".usuario_autoexclusion_juego b on (x.mandante=b.mandante and b.usuario_id=" . $usuario_id . " and b.tipojuego_id=1 and now() between date(b.fecha_crea) and date(b.fecha_fin)) ";
                    $excjuegoSql = $excjuegoSql . "where x.mandante=" . $mandante;
                    $excjuego_RS_query = ProcesarConsulta($excjuegoSql, $Conn);
                    $excjuego_RS = mysql_fetch_array($excjuego_RS_query);
                    if ($excjuego_RS["tipo"] == "E")
                        $respuesta = "ERROR_10_El usuario tiene una autoexclusion vigente.";
                    else {
                        //Valida el n�mero de la transaccion
                        $validaSql = "select a.transaccion_id from " . $winplay . ".it_transaccion a where a.mandante=" . $mandante . " and a.transaccion_id='" . $trans_id . "'";
                        $valida_RS_query = ProcesarConsulta($validaSql, $Conn);
                        $valida_RS = mysql_fetch_array($valida_RS_query);
                        if (($valida_RS == 0)) {
                            //Valida  el n�mero del ticket
                            $ticket_existe = false;
                            $premio_pagado = "N";
                            $validaSql = "select a.it_ticket_id,a.premio_pagado from " . $winplay . ".it_ticket_enc a where a.mandante=" . $mandante . " and a.ticket_id='" . $ticket_id . "'";
                            $valida_RS_query = ProcesarConsulta($validaSql, $Conn);
                            $valida_RS = mysql_fetch_array($valida_RS_query);
                            if (!($valida_RS == 0)) {
                                $ticket_existe = true;
                                $premio_pagado = $valida_RS["premio_pagado"];
                            }

                            //Verifica cual es el tipo de informaci�n requerida
                            switch ($tipo) {
                                //Tipo cuando realizan la grabaci�n de una apuesta
                                case "BET":
                                    //Valida los campos pasados
                                    if (!ValidarCampo($cant_lineas, "S", "N", 3))
                                        $seguir = false;
                                    if (!ValidarCampo($cant_banker, "S", "N", 3))
                                        $seguir = false;
                                    if (!ValidarCampo($premio_proy, "S", "N", 15))
                                        $seguir = false;
									if (!ValidarCampo($frontend, "S", "T", 10))
                                        $seguir = false;
									if (!ValidarCampo($betmode, "S", "T", 50))
                                        $seguir = false;
									if (!ValidarCampo($dir_ip, "N", "T", 30))
										$seguir = false;

                                    //Verifica si debe continuar
                                    if ($seguir) {
                                        //Valida que el n�mero del ticket exista
                                        if ($ticket_existe) {
                                            $seguir = false;
                                            $respuesta = "ERROR_03_" . $valida_RS["it_ticket_id"] . "_El numero de ticket ya se encuentra registrado en la base de datos.";
                                        } else {
                                            //Recorre todas las lineas del ticket para grabar el detalle
                                            foreach ($obj->{'EventsDescription'} as $item) {
                                                //Captura los elementos del detalle
                                                $evento = DepurarCaracteres($item->{'evento'});
                                                $fecha = DepurarCaracteres($item->{'fecha'});
                                                $hora = DepurarCaracteres($item->{'hora'});
                                                $evento_id = DepurarCaracteres($item->{'eventoid'});
                                                $agrupador = DepurarCaracteres($item->{'agrupador'});
                                                $agrupador_id = DepurarCaracteres($item->{'agrupadorid'});
                                                $opcion = DepurarCaracteres($item->{'opcion'});
                                                $logro = DepurarCaracteres($item->{'logro'});
												$sportid = DepurarCaracteres($item->{'sportid'});
												$matchid = DepurarCaracteres($item->{'matchid'});
                                                //$premio_proy = $premio_proy/100;
                                                $premio_proy = 0;

                                                //Valida que los campos del detalle esten correctos
                                                if (!ValidarCampo($evento, "S", "T", 100)) {
                                                    $seguir = false;
                                                    break;
                                                }
                                                if (!ValidarCampo($fecha, "S", "F", 10)) {
                                                    $seguir = false;
                                                    break;
                                                }
                                                if (!ValidarCampo($hora, "S", "H", 5)) {
                                                    $seguir = false;
                                                    break;
                                                }
                                                if (!ValidarCampo($evento_id, "S", "N", 20)) {
                                                    $seguir = false;
                                                    break;
                                                }
                                                if (!ValidarCampo($agrupador, "S", "T", 100)) {
                                                    $seguir = false;
                                                    break;
                                                }
                                                if (!ValidarCampo($agrupador_id, "S", "T", 15)) {
                                                    $seguir = false;
                                                    break;
                                                }
                                                if (!ValidarCampo($opcion, "S", "T", 100)) {
                                                    $seguir = false;
                                                    break;
                                                }
                                                if (!ValidarCampo($logro, "S", "N", 15)) {
                                                    $seguir = false;
                                                    break;
                                                }
												if (!ValidarCampo($sportid, "S", "N", 15)) {
                                                    $seguir = false;
                                                    break;
                                                }
												if (!ValidarCampo($matchid, "S", "N", 15)) {
                                                    $seguir = false;
                                                    break;
                                                }

                                                //Inserta la cabecera del ticket 
                                                $contSql = $contSql + 1;
                                                $strSql[$contSql] = "insert into " . $winplay . ".it_ticket_det (ticket_id,apuesta,apuesta_id,agrupador,agrupador_id,opcion,logro,fecha_evento,hora_evento,sportid,matchid,mandante) values ('" . $ticket_id . "','" . $evento . "'," . $evento_id . ",'" . $agrupador . "','" . $agrupador_id . "','" . $opcion . "'," . $logro . ",'" . $fecha . "','" . $hora . "',".$sportid.",".$matchid."," . $mandante . ")";
                                            }

                                            //Verifica si el detalle pudo ser procesado
                                            if ($seguir) {
                                                //Trae el valor de creditos base actual
                                                $creditos_base = 0;
                                                $saldo_actual = 0;
                                                $baseSql = "select a.creditos_base,round(a.creditos_base+a.creditos) saldo_actual from " . $winplay . ".registro a where a.mandante=" . $mandante . " and a.usuario_id=" . $usuario_id;
                                                $base_RS_query = ProcesarConsulta($baseSql, $Conn); 
                                                $base_RS = mysql_fetch_array($base_RS_query);
                                                if (!($base_RS == 0)) {
                                                    $creditos_base = $base_RS["creditos_base"];
                                                    $saldo_actual = $base_RS["saldo_actual"];
                                                }

                                                //Verifica si tiene saldo para ejecutar la transacci�n
                                                if (floatval($saldo_actual) >= floatval($valor)) {
													//Trae el tipo de apuesta según de donde se esté creando la apuesta
													$tipo_apuesta = "N";
													$referido_id = 0;
													$referidoSql = "select case when not b.usuario_id is null then 'J' when a.referido_id>0 and c.perfil_id<>'USUONLINE' then 'A' else 'N' end tipo_apuesta,case when not b.usuario_id is null then b.usuario_id when a.referido_id>0 and c.perfil_id<>'USUONLINE' then a.referido_id else 0 end referido_id from ".$winplay.".usuario a left outer join ".$winplay.".punto_venta b on (b.ip='".$dir_ip."' and b.ip!='0' and b.ip!='' and not b.ip is null)	left outer join ".$winplay.".usuario_perfil c on (a.referido_id=c.usuario_id) where a.usuario_id=".$usuario_id;
													$referido_RS_query = ProcesarConsulta($referidoSql, $Conn);
													$referido_RS = mysql_fetch_array($referido_RS_query);
													if (!($referido_RS == 0)) {
														$tipo_apuesta = $referido_RS["tipo_apuesta"];
														$referido_id = $referido_RS["referido_id"];
													}
													
													//Calcula el valor de creditos base que tiene que restar de acuerdo al valor de la apuesta
                                                    if ($valor > $creditos_base) {
                                                        $valor_base = $creditos_base;
                                                        $valor_adicional = $valor - $creditos_base;
                                                    } else {
                                                        $valor_base = $valor;
                                                        $valor_adicional = 0;
                                                    }
													
                                                    //Inserta la cabecera del ticket
                                                    $contSql = $contSql + 1;
                                                    $strSql[$contSql] = "insert into " . $winplay . ".it_ticket_enc (transaccion_id,ticket_id,vlr_apuesta,vlr_apuesta_creditos,vlr_apuesta_premios,vlr_premio,usuario_id,game_reference,bet_status,cant_lineas,fecha_crea,hora_crea,frontend,betmode,dir_ip,tipo_apuesta,referido_id,mandante) values ('" . $trans_id . "','" . $ticket_id . "'," . $valor . ",". $valor_base . "," . $valor_adicional . "," . $premio_proy . "," . $usuario_id . ",'" . $game_reference . "','" . $bet_status . "'," . $cant_lineas . ",'" . date('Y-m-d') . "','" . date('H:i:s') . "','".$frontend."','".$betmode."','".$dir_ip."','".$tipo_apuesta."',".$referido_id."," . $mandante . ")";

                                                    //Inserta el log de auditor�a de la apuesta
                                                    $contSql = $contSql + 1;
                                                    $strSql[$contSql] = "insert into " . $winplay . ".it_transaccion (fecha_crea,hora_crea,tipo,ticket_id,game_reference,usuario_id,bet_status,valor,transaccion_id,mandante) values ('" . date('Y-m-d') . "','" . date('H:i:s') . "','" . $tipo . "','" . $ticket_id . "','" . $game_reference . "','" . $usuario_id . "','" . $bet_status . "'," . $valor . ",'" . $trans_id . "'," . $mandante . ")";

                                                    //Resta el valor de la apuesta del valor del cr�dito disponible
                                                    $contSql = $contSql + 1;
                                                    $strSql[$contSql] = "update " . $winplay . ".registro set creditos_base_ant=creditos_base,creditos_ant=creditos,creditos_base=creditos_base-" . $valor_base . ",creditos=creditos-" . $valor_adicional . " where mandante=" . $mandante . " and usuario_id=" . $usuario_id;
                                                } else {
                                                    $seguir = false;
                                                    $respuesta = "ERROR_10_El usuario no dispone del saldo suficiente para ejecutar estar esta operacion.";
                                                }
                                            } else {
                                                $seguir = false;
                                                $respuesta = "ERROR_99_No se pudo procesar su solicitud debido a inconsistencias halladas en los parametros suministrados.";
                                            }
                                        }
                                    } else {
                                        $seguir = false;
                                        $respuesta = "ERROR_99_No se pudo procesar su solicitud debido a inconsistencias halladas en los parametros suministrados.";
                                    }

                                    break;

                                // Tipo cuando hay un ticket ganador
                                case "WIN":
                                    //Verifica si debe continuar
                                    if ($seguir) {
                                        //Valida que el n�mero del ticket exista
                                        if (!$ticket_existe) {
                                            $seguir = false;
                                            $respuesta = "ERROR_02_El numero de ticket suministrado no se encuentra registrado en la base de datos.";
                                        } else {
                                            if ($premio_pagado == "S") {
                                                $seguir = false;
                                                $respuesta = "ERROR_04_El numero de ticket suministrado ya fue pagado previamente.";
                                            } else {
                                                //Inserta el log de auditor�a del ticket ganador
                                                $contSql = $contSql + 1;
                                                $strSql[$contSql] = "insert into " . $winplay . ".it_transaccion (fecha_crea,hora_crea,tipo,ticket_id,game_reference,usuario_id,bet_status,valor,transaccion_id,mandante) values ('" . date('Y-m-d') . "','" . date('H:i:s') . "','" . $tipo . "','" . $ticket_id . "','" . $game_reference . "','" . $usuario_id . "','" . $bet_status . "'," . $valor . ",'" . $trans_id . "'," . $mandante . ")";

                                                //Actualiza el estado del ticket como ganador
                                                $contSql = $contSql + 1;
                                                $strSql[$contSql] = "update " . $winplay . ".it_ticket_enc set premiado='S',premio_pagado='S',fecha_pago='" . date('Y-m-d') . "',hora_pago='" . date('H:i:s') . "',vlr_premio=" . $valor . ",estado='I',bet_status='" . $bet_status . "',fecha_cierre='" . date('Y-m-d') . "',hora_cierre='" . date('H:i:s') . "' where mandante=" . $mandante . " and ticket_id='" . $ticket_id . "'";

                                                //Actualiza el saldo del usuario
                                                $contSql = $contSql + 1;
                                                $strSql[$contSql] = "update " . $winplay . ".registro set creditos=creditos+" . $valor . " where mandante=" . $mandante . " and usuario_id=" . $usuario_id;
                                            }
                                        }
                                    } else {
                                        $seguir = false;
                                        $respuesta = "ERROR_99_No se pudo procesar su solicitud debido a inconsistencias halladas en los parametros suministrados.";
                                    }

                                    break;

                                // Tipo cuando hay un ticket perdedor
                                case "LOSS":
                                    //Verifica si debe continuar
                                    if ($seguir) {
                                        //Valida que el n�mero del ticket exista
                                        if (!$ticket_existe) {
                                            $seguir = false;
                                            $respuesta = "ERROR_02_El numero de ticket suministrado no se encuentra registrado en la base de datos.";
                                        } else {
                                            //Inserta el log de auditor�a de la apuesta perdida
                                            $contSql = $contSql + 1;
                                            $strSql[$contSql] = "insert into " . $winplay . ".it_transaccion (fecha_crea,hora_crea,tipo,ticket_id,game_reference,usuario_id,bet_status,valor,transaccion_id,mandante) values ('" . date('Y-m-d') . "','" . date('H:i:s') . "','" . $tipo . "','" . $ticket_id . "','" . $game_reference . "','" . $usuario_id . "','" . $bet_status . "'," . $valor . ",'" . $trans_id . "'," . $mandante . ")";

                                            //Actualiza el estado del ticket como ganador
                                            $contSql = $contSql + 1;
                                            $strSql[$contSql] = "update " . $winplay . ".it_ticket_enc set premiado='N',premio_pagado='N',fecha_pago=null,hora_pago=null,vlr_premio=0,estado='I',bet_status='" . $bet_status . "',fecha_cierre='" . date('Y-m-d') . "',hora_cierre='" . date('H:i:s') . "' where mandante=" . $mandante . " and ticket_id='" . $ticket_id . "'";
                                        }
                                    } else {
                                        $seguir = false;
                                        $respuesta = "ERROR_99_No se pudo procesar su solicitud debido a inconsistencias halladas en los parametros suministrados.";
                                    }

                                    break;

                                // Tipo cuando hay un reembolso
                                case "REFUND":
                                    //Verifica si debe continuar
                                    if ($seguir) {
                                        //Valida que el n�mero del ticket exista
                                        if (!$ticket_existe) {
                                            $seguir = false;
                                            $respuesta = "ERROR_02_El numero de ticket suministrado no se encuentra registrado en la base de datos.";
                                        } else {
                                            //Inserta el log de auditor�a del reembolso del dinero
                                            $contSql = $contSql + 1;
                                            $strSql[$contSql] = "insert into " . $winplay . ".it_transaccion (fecha_crea,hora_crea,tipo,ticket_id,game_reference,usuario_id,bet_status,valor,transaccion_id,mandante) values ('" . date('Y-m-d') . "','" . date('H:i:s') . "','" . $tipo . "','" . $ticket_id . "','" . $game_reference . "','" . $usuario_id . "','" . $bet_status . "'," . $valor . ",'" . $trans_id . "'," . $mandante . ")";

                                            //Actualiza el ticket como eliminado
                                            $contSql = $contSql + 1;
                                            $strSql[$contSql] = "update " . $winplay . ".it_ticket_enc set bet_status='" . $bet_status . "',eliminado='S',estado='I',fecha_cierre='" . date('Y-m-d') . "',hora_cierre='" . date('H:i:s') . "' where mandante=" . $mandante . " and ticket_id='" . $ticket_id . "'";

                                            //Actualiza el saldo del usuario
                                            $contSql = $contSql + 1;
                                            $strSql[$contSql] = "update " . $winplay . ".registro set creditos_base=creditos_base+" . $valor . " where mandante=" . $mandante . " and usuario_id=" . $usuario_id;
                                        }
                                    } else {
                                        $seguir = false;
                                        $respuesta = "ERROR_99_No se pudo procesar su solicitud debido a inconsistencias halladas en los parametros suministrados.";
                                    }

                                    break;

                                // Tipo cuando hay un chequeo de apuesta v�lida
                                case "BETCHECK":
                                    //Verifica si debe continuar
                                    if ($seguir) {
                                        //Valida que el n�mero del ticket exista
                                        if ($ticket_existe)
                                            $chequeo = "_TRUE";
                                        else {
                                            $seguir = false;
                                            $respuesta = "ERROR_96_El numero de ticket suministrado no se encuentra registrado en la base de datos.";
                                        }
                                    } else {
                                        $seguir = false;
                                        $respuesta = "ERROR_99_No se pudo procesar su solicitud debido a inconsistencias halladas en los parametros suministrados.";
                                    }

                                    break;

                                // Tipo cuando realizan un d�bito
                                case "NEWDEBIT":
                                    //Verifica si debe continuar
                                    if ($seguir) {
                                        //Valida que el n�mero del ticket exista
                                        if (!$ticket_existe) {
                                            $seguir = false;
                                            $respuesta = "ERROR_02_El numero de ticket suministrado no se encuentra registrado en la base de datos.";
                                        } else {
                                            //Inserta el log de auditor�a del new debit
                                            $contSql = $contSql + 1;
                                            $strSql[$contSql] = "insert into " . $winplay . ".it_transaccion (fecha_crea,hora_crea,tipo,ticket_id,game_reference,usuario_id,bet_status,valor,transaccion_id,mandante) values ('" . date('Y-m-d') . "','" . date('H:i:s') . "','" . $tipo . "','" . $ticket_id . "','" . $game_reference . "','" . $usuario_id . "','" . $bet_status . "'," . $valor . ",'" . $trans_id . "'," . $mandante . ")";

                                            //Actualiza el estado del ticket como ganador
                                            $contSql = $contSql + 1;
                                            $strSql[$contSql] = "update " . $winplay . ".it_ticket_enc set bet_status='" . $bet_status . "',premiado=case when vlr_premio-" . $valor . "=0 then 'N' else premiado end,premio_pagado=case when vlr_premio-" . $valor . "=0 then 'N' else premio_pagado end,vlr_premio=vlr_premio-" . $valor . " where mandante=" . $mandante . " and ticket_id='" . $ticket_id . "'";

                                            //Actualiza el saldo del usuario
                                            $contSql = $contSql + 1;
                                            $strSql[$contSql] = "update " . $winplay . ".registro set creditos=creditos-" . $valor . " where mandante=" . $mandante . " and usuario_id=" . $usuario_id;
                                        }
                                    } else {
                                        $seguir = false;
                                        $respuesta = "ERROR_99_No se pudo procesar su solicitud debido a inconsistencias halladas en los parametros suministrados.";
                                    }

                                    break;

                                // Tipo cuando realizan un cr�dito
                                case "NEWCREDIT":
                                    //Verifica si debe continuar
                                    if ($seguir) {
                                        //Valida que el n�mero del ticket exista
                                        if (!$ticket_existe) {
                                            $seguir = false;
                                            $respuesta = "ERROR_02_El numero de ticket suministrado no se encuentra registrado en la base de datos.";
                                        } else {
                                            //Inserta el log de auditor�a del new credit
                                            $contSql = $contSql + 1;
                                            $strSql[$contSql] = "insert into " . $winplay . ".it_transaccion (fecha_crea,hora_crea,tipo,ticket_id,game_reference,usuario_id,bet_status,valor,transaccion_id,mandante) values ('" . date('Y-m-d') . "','" . date('H:i:s') . "','" . $tipo . "','" . $ticket_id . "','" . $game_reference . "','" . $usuario_id . "','" . $bet_status . "'," . $valor . ",'" . $trans_id . "'," . $mandante . ")";

                                            //Actualiza el estado del ticket como ganador
                                            $contSql = $contSql + 1;
                                            $strSql[$contSql] = "update " . $winplay . ".it_ticket_enc set bet_status='" . $bet_status . "',premiado='S',premio_pagado='S',vlr_premio=vlr_premio+" . $valor . " where mandante=" . $mandante . " and ticket_id='" . $ticket_id . "'";

                                            //Actualiza el saldo del usuario
                                            $contSql = $contSql + 1;
                                            $strSql[$contSql] = "update " . $winplay . ".registro set creditos=creditos+" . $valor . " where mandante=" . $mandante . " and usuario_id=" . $usuario_id;
                                        }
                                    } else {
                                        $seguir = false;
                                        $respuesta = "ERROR_99_No se pudo procesar su solicitud debido a inconsistencias halladas en los parametros suministrados.";
                                    }

                                    break;

                                // Tipo cuando realizan un cr�dito
                                case "STAKEDECREASE":
                                    //Verifica si debe continuar
                                    if ($seguir) {
                                        //Valida que el n�mero del ticket exista
                                        if (!$ticket_existe) {
                                            $seguir = false;
                                            $respuesta = "ERROR_02_El numero de ticket suministrado no se encuentra registrado en la base de datos.";
                                        } else {
                                            //Valida que estado le debe poner al ticket
                                            $estado_ticket = "I";
                                            $strFechaCierre = ",fecha_cierre='" . date('Y-m-d') . "',hora_cierre='" . date('H:i:s') . "'";
                                            if ($bet_status == "R" or $bet_status == "W") {
                                                $estado_ticket = "A";
                                                $strFechaCierre = "";
                                            }

                                            //Inserta el log de auditor�a del stake decrease
                                            $contSql = $contSql + 1;
                                            $strSql[$contSql] = "insert into " . $winplay . ".it_transaccion (fecha_crea,hora_crea,tipo,ticket_id,game_reference,usuario_id,bet_status,valor,transaccion_id,mandante) values ('" . date('Y-m-d') . "','" . date('H:i:s') . "','" . $tipo . "','" . $ticket_id . "','" . $game_reference . "','" . $usuario_id . "','" . $bet_status . "'," . $valor . ",'" . $trans_id . "'," . $mandante . ")";

                                            //Actualiza el estado del ticket como ganador
                                            $contSql = $contSql + 1;
                                            $strSql[$contSql] = "update " . $winplay . ".it_ticket_enc set estado='" . $estado_ticket . "',bet_status='" . $bet_status . "'" . $strFechaCierre . " where mandante=" . $mandante . " and ticket_id='" . $ticket_id . "'";

                                            //Actualiza el saldo del usuario
                                            $contSql = $contSql + 1;
                                            $strSql[$contSql] = "update " . $winplay . ".registro set creditos=creditos+" . $valor . " where mandante=" . $mandante . " and usuario_id=" . $usuario_id;
                                        }
                                    } else {
                                        $seguir = false;
                                        $respuesta = "ERROR_99_No se pudo procesar su solicitud debido a inconsistencias halladas en los parametros suministrados.";
                                    }

                                    break;

                                // Tipo cuando realizan un cr�dito
                                case "CASHOUT":
                                    //Verifica si debe continuar
                                    if ($seguir) {
                                        //Valida que el n�mero del ticket exista
                                        if (!$ticket_existe) {
                                            $seguir = false;
                                            $respuesta = "ERROR_02_El numero de ticket suministrado no se encuentra registrado en la base de datos.";
                                        } else {
                                            //Inserta el log de auditor�a del cashout
                                            $contSql = $contSql + 1;
                                            $strSql[$contSql] = "insert into " . $winplay . ".it_transaccion (fecha_crea,hora_crea,tipo,ticket_id,game_reference,usuario_id,bet_status,valor,transaccion_id,mandante) values ('" . date('Y-m-d') . "','" . date('H:i:s') . "','" . $tipo . "','" . $ticket_id . "','" . $game_reference . "','" . $usuario_id . "','" . $bet_status . "'," . $valor . ",'" . $trans_id . "'," . $mandante . ")";

                                            //Actualiza el estado del ticket como ganador
                                            $contSql = $contSql + 1;
                                            $strSql[$contSql] = "update " . $winplay . ".it_ticket_enc set bet_status='" . $bet_status . "',estado='I',premiado='S',premio_pagado='S',vlr_premio=" . $valor . ",fecha_cierre='" . date('Y-m-d') . "',hora_cierre='" . date('H:i:s') . "' where mandante=" . $mandante . " and ticket_id='" . $ticket_id . "'";

                                            //Actualiza el saldo del usuario
                                            $contSql = $contSql + 1;
                                            $strSql[$contSql] = "update " . $winplay . ".registro set creditos=creditos+" . $valor . " where mandante=" . $mandante . " and usuario_id=" . $usuario_id;
                                        }
                                    } else {
                                        $seguir = false;
                                        $respuesta = "ERROR_99_No se pudo procesar su solicitud debido a inconsistencias halladas en los parametros suministrados.";
                                    }

                                    break;

                                // Tipo cuando se libera un bono
                                case "WINBONUS":
                                    //Valida los campos obligatorios
                                    if (!ValidarCampo($bonus_id, "S", "N", 20))
                                        $seguir = false;
                                    if (!ValidarCampo($bonusplan_id, "S", "N", 20))
                                        $seguir = false;

                                    //Verifica si debe continuar
                                    if ($seguir) {
                                        //Valida si el bono existe y no ha sido cerrado
                                        $validabonoSql = "select a.id_externo,a.estado from " . $winplay . ".bono_log a where a.mandante=" . $mandante . " and a.id_externo=" . $bonus_id;
                                        $validabono_RS_query = ProcesarConsulta($validabonoSql, $Conn);
                                        $validabono_RS = mysql_fetch_array($validabono_RS_query);
                                        if (($validabono_RS == 0)) {
                                            $seguir = false;
                                            $respuesta = "ERROR_15_El numero de bono suministrado no se encuentra registrado en la base de datos.";
                                        } else {
                                            if ($validabono_RS["estado"] != "A") {
                                                $seguir = false;
                                                $respuesta = "ERROR_16_No se pudo procesar el bono porque ya fue procesado previamente o su estado actual no admite la liberacion.";
                                            } else {
                                                //Verifica el perfil del usuario
                                                if ($perfil == "USER") {
                                                    //Actualiza el saldo de creditos de participaci�n y el saldo de bonos
                                                    $contSql = $contSql + 1;
                                                    $strSql[$contSql] = "update " . $winplay . ".registro set creditos_base_ant=creditos_base,creditos_base=creditos_base+" . $valor . ",creditos_bono_ant=creditos_bono,creditos_bono=creditos_bono-" . $valor . " where mandante=" . $mandante . " and usuario_id=" . $usuario_id;

                                                    //Actualiza el estado del log de bonos
                                                    $contSql = $contSql + 1;
                                                    $strSql[$contSql] = "update " . $winplay . ".bono_log set estado='L',fecha_cierre='" . date('Y-m-d H:i:s') . "',transaccion_id='" . $trans_id . "' where mandante=" . $mandante . " and id_externo=" . $bonus_id;
                                                }
                                            }
                                        }
                                    } else {
                                        $seguir = false;
                                        $respuesta = "ERROR_99_No se pudo procesar su solicitud debido a inconsistencias halladas en los parametros suministrados.";
                                    }

                                    break;

                                // Tipo cuando se libera un bono
                                case "CODEBONUS":
                                    //Valida los campos obligatorios
                                    if (!ValidarCampo($bonus_id, "S", "N", 20))
                                        $seguir = false;
                                    if (!ValidarCampo($bonusplan_id, "S", "N", 20))
                                        $seguir = false;

                                    //Verifica si debe continuar
                                    if ($seguir) {
                                        //Valida si el bono existe y no ha sido cerrado
                                        $validabonoSql = "select a.id_externo,a.estado from " . $winplay . ".bono_log a where a.mandante=" . $mandante . " and a.id_externo=" . $bonus_id;
                                        $validabono_RS_query = ProcesarConsulta($validabonoSql, $Conn);
                                        $validabono_RS = mysql_fetch_array($validabono_RS_query);
                                        if (($validabono_RS == 0)) {
                                            $seguir = false;
                                            $respuesta = "ERROR_15_El numero de bono suministrado no se encuentra registrado en la base de datos.";
                                        } else {
                                            if ($validabono_RS["estado"] != "A") {
                                                $seguir = false;
                                                $respuesta = "ERROR_16_No se pudo procesar el bono porque ya fue procesado previamente o su estado actual no admite la liberacion.";
                                            } else {
                                                //Verifica el perfil del usuario
                                                if ($perfil == "USER") {
                                                    //Actualiza el saldo de creditos de participaci�n y el saldo de bonos
                                                    $contSql = $contSql + 1;
                                                    $strSql[$contSql] = "update " . $winplay . ".registro set creditos_bono_ant=creditos_bono,creditos_bono=creditos_bono+" . $valor . " where mandante=" . $mandante . " and usuario_id=" . $usuario_id;

                                                    //Actualiza el estado del log de bonos
                                                    $contSql = $contSql + 1;
                                                    $strSql[$contSql] = "insert into bono_log (usuario_id,tipo,valor,fecha_crea,estado,error_id,id_externo,mandante) values (" . $usuario_id . ",'C'," . $valor . ",'" . date('Y-m-d H:i:s') . "','A','0'," . $bonus_id . "," . $mandante . ")";
                                                }
                                            }
                                        }
                                    } else {
                                        $seguir = false;
                                        $respuesta = "ERROR_99_No se pudo procesar su solicitud debido a inconsistencias halladas en los parametros suministrados.";
                                    }

                                    break;
                            }
                        } else
                            $chequeo = "_TRUE";

                        //Si debe continuar el proceso
                        if ($seguir) {
							//Se arma el nuevo token de itainment
							$x = strlen($usuario_id);
							$r = 15;
							$d = $r - $x;
							$token_nuevo = $usuario_id.GenerarClaveTicket2($d);

							//Adiciona el query para actualizar el nuevo token del usuario
							$contSql = $contSql + 1;
							$strSql[$contSql] = "UPDATE ".$winplay.".usuario SET token_itainment=".$token_nuevo." WHERE usuario_id=".$usuario_id;
							
                            //cierra conexiones de bases de datos
                            mysql_close($Conn);
                            $Conn = null;

                            //Ejecuta las instrucciones SQL
                            $retorno = EjecutarQuery3($bd_servidor, $bd_usuario, $bd_clave, $bd_nombre, $contSql, $strSql);

                            //Devuelve la respuesta de acuerdo al retorno
                            if (!$retorno) {
                                //Abre la conexi�n a la base de datos
                                $Conn = ConectarBD($bd_servidor, $bd_usuario, $bd_clave, $bd_nombre);

                                //Trae el saldo del usuario
                                $saldo = 0;
                                $saldoSql = "select round(a.creditos+a.creditos_base,0) saldo from " . $winplay . ".registro a where a.mandante=" . $mandante . " and a.usuario_id=" . $usuario_id;
                                $saldo_RS_query = ProcesarConsulta($saldoSql, $Conn);
                                $saldo_RS = mysql_fetch_array($saldo_RS_query);
                                if (!($saldo_RS == 0))
                                    $saldo = $saldo_RS["saldo"];

                                $respuesta = "OK_" . $perfil . "_" . $key_ref . "_" . $usuario_id . "_" . $nombre . "_" . $saldo . "00" . $chequeo . "_" . $token_nuevo . "_INI";
                            } else
                                $respuesta = "ERROR_01_Ocurrio un error inesperado y no se pudo procesar la solicitud.";
                        } else {
                            if (strlen($respuesta) <= 0)
                                $respuesta = "ERROR_99_No se pudo procesar su solicitud debido a inconsistencias halladas en los parametros suministrados.";
                        }
                    }
                }
            }
        }
    } else
        $respuesta = "ERROR_99_No se pudo procesar su solicitud debido a inconsistencias halladas en los parametros suministrados.";

    //cierra conexiones de bases de datos
    mysql_close($Conn);
    $Conn = null;

    //Libera la memoria del query
    mysql_free_result($datos_RS_query);

    //Devuelve la respuesta
    return $respuesta;

}

?>