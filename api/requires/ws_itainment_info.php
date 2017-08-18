<?php

function ws_itainment_info($request)
{
	// Archivo que incluye todas las variables globales necesarias
	include "global.php";
	include "funciones.php";
	date_default_timezone_set('America/Bogota');

	//Abre la conexi�n a la base de datos
	$Conn=ConectarBD($bd_servidor,$bd_usuario,$bd_clave,$bd_nombre);

	//Recibe los par�metros pasados en la URL
	$req = $request;

	//Divide el request en las tres partes correspondientes
	list($key_casino, $key_usuario, $ip_usuario) = explode('_',$req);

	//Inicializa la respuesta
	$respuesta = "";

	//Valida los campos ingresados
	$seguir = true;
	if (strlen($req)<=0)
		$seguir = false;

	//Formatea las variables separadas
	if ($seguir)
	{
		//Valida los campos pasados
		if (!ValidarCampo($key_casino,"S","T",500))
			$seguir = false;
		if (!ValidarCampo($key_usuario,"S","T",500))
			$seguir = false;
		if (!ValidarCampo($ip_usuario,"N","T",20))
			$seguir = false;
	}

	if ($seguir)
	{
		//Verifica si la clave de casino suministrada es correcta
		$key_ref = hash_hmac("sha256", $key_casino_ref, $auth_casino, false);
		if ($key_casino!=$key_ref)
			$respuesta = "ERROR_99_No dispone de los privilegios suficientes para acceder a este servicio.";
		else
		{
			//Valida que el usuario suministrado exista
			$datosSql = "select a.usuario_id,a.estado_token,aes_decrypt(a.nombre,'".$clave_encrypt."') nombre,case when b.perfil_id='USUONLINE' then 'USER' when b.perfil_id='SA' or b.perfil_id like 'ADMIN%' then 'ADMIN' else 'OTHER' end perfil,round(case when c.usuario_id is null then 0 else c.creditos+c.creditos_base end,0) saldo,case when c.fecha_casino is null then 'N' when extract(minute from timediff(now(), timestamp(c.fecha_casino)))>60 then 'S' else 'N' end caduco,a.referido_id from ".$winplay.".usuario a inner join ".$winplay.".usuario_perfil b on (a.mandante=b.mandante and a.usuario_id=b.usuario_id) left outer join ".$winplay.".registro c on (a.mandante=c.mandante and a.usuario_id=c.usuario_id) where a.mandante=".$mandante." and a.token_itainment=".$key_usuario;
			$datos_RS_query=ProcesarConsulta($datosSql,$Conn);
			$datos_RS=mysql_fetch_array($datos_RS_query);
			if(($datos_RS==0))
				$respuesta = "ERROR_99_El usuario suministrado no se encuentra registrado en la base de datos.";
			else
			{
				if ($datos_RS["caduco"]=="S")
					$respuesta = "ERROR_01_La sesion ha caducado.";
				else
				{
					if ($datos_RS["estado_token"]==1)
						$respuesta = "ERROR_02_La sesion ya fue iniciada previamente.";
					else
					{
						//Valida que el usuario suministrado exista
						$sponsor_id = $datos_RS["referido_id"];
						$puntoSql = "select a.usuario_id from ".$winplay.".punto_venta a where a.mandante=".$mandante." and a.ip='".$ip_usuario."'";
						$punto_RS_query=ProcesarConsulta($puntoSql,$Conn);
						$punto_RS=mysql_fetch_array($punto_RS_query);
						if(!($punto_RS==0))
							$sponsor_id = $punto_RS["usuario_id"];
						
						//Armar la nueva descripci�n
						$strSql = array();
						$contSql = 0;
						
						//Adiciona el query para actualizar el nuevo token del usuario
						$contSql = $contSql + 1;
						$strSql[$contSql] = "update ".$winplay.".usuario set estado_token=1 where usuario_id=".$datos_RS["usuario_id"];
							
                        //cierra conexiones de bases de datos
                        mysql_close($Conn);
                        $Conn = null;

                        //Ejecuta las instrucciones SQL
                        $retorno = EjecutarQuery3($bd_servidor, $bd_usuario, $bd_clave, $bd_nombre, $contSql, $strSql);
					
						//Devuelve la respuesta
						$respuesta = "OK_".$datos_RS["perfil"]."_".$key_ref."_".$datos_RS["usuario_id"]."_".$datos_RS["nombre"]."_".$datos_RS["saldo"]."00_".$sponsor_id."_INI";
					}
				}
			}
		}
	}
	else
		$respuesta = "ERROR_99_No se pudo procesar su solicitud debido a inconsistencias halladas en los parametros suministrados.";

	//cierra conexiones de bases de datos
	mysql_close($Conn);
	$Conn=null;

	//Libera la memoria del query
	mysql_free_result($datos_RS_query);

	//Devuelve la respuesta
	return $respuesta;
	
}
?>