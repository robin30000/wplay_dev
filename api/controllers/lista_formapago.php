<?php

	session_start();
	
	// Archivo que incluye todas las variables globales necesarias

	require_once "../requires/global.php";

	require_once "../requires/funciones.php";

	date_default_timezone_set('America/Bogota');

	

	//Abre la conexi�n a la base de datos

	$Conn=ConectarBD($bd_servidor,$bd_usuario,$bd_clave,$bd_nombre);



	//Recibe el parametro para ver si es en la busqueda o en la edici�n

	$tipo = DepurarCaracteres($_GET["tipo"]);

	$clase = DepurarCaracteres($_GET["clase"]);

	

	//Verifica cual es la clase seleccionada

	if ($clase=="C")

		$strForma = " where a.formapago_id=1";

	else

		$strForma = " where a.formapago_id<>1";



	//Trae la lista de elementos

	if ($tipo=="B")

	{

		$lista="<option selected value=''></option>";

	}

	else

	{

		$lista="";

	}

	$deporte_ant="";

	$primera=true;

	$listaSql="select a.formapago_id,a.descripcion from ".$winplay.".forma_pago a ".$strForma." and tipo='N' order by a.descripcion"; 

	$lista_RS_query=ProcesarConsulta($listaSql,$Conn);

	$lista_RS=mysql_fetch_array($lista_RS_query);

  	while(!($lista_RS==0))

  	{

    	$lista=$lista."<option value='".$lista_RS["formapago_id"]."'>".$lista_RS["descripcion"]."</option>";

		$lista_RS=mysql_fetch_array($lista_RS_query);

	}

	

	//cierra conexiones de bases de datos

	mysql_close($Conn);

	$Conn=null;

	

	//Libera la memoria del query

	mysql_free_result($lista_RS_query);



	$lista=$lista."</optgroup>";

	print $lista;



?>

