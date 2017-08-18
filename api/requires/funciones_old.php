<?php

function tokenItainment($u)
{
    $x = strlen($u);
    $r = 15;
    $d = $r - $x;
    return $u.GenerarClaveTicket2($d);
}

function jsonReturn($state, $msg)
{

    echo json_encode(array('state' => $state, 'msg' => $msg));

    exit();
}

//Funci�n para conectar a la base de datos

function ConectarBD($servidor_sel, $usuario_sel, $clave_sel, $bd_sel)
{
    // Verificando servidor, usuario y contrase�a

    if (!($conexion = mysql_pconnect($servidor_sel, $usuario_sel, $clave_sel))) {
        exit();
    }

    // Verificando base de datos

    if (!mysql_select_db($bd_sel, $conexion)) {
        exit();
    }

    //Returna el resultado de la conexi�n

    return $conexion;

}

function Encriptar($cadena)
{

    $key = "EA8WKJG8OGJQHEYH";

    $encrypted = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $cadena, MCRYPT_MODE_CBC, $key));

    return str_replace(array('+', '/', '='), array('_', '-', '.'), $encrypted); //Devuelve el string encriptado

}

function Desencriptar($cadena)
{

    $key = "EA8WKJG8OGJQHEYH";

    $cadena = str_replace(array('_', '-', '.'), array('+', '/', '='), $cadena);

    $decrypted = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, base64_decode($cadena), MCRYPT_MODE_CBC, $key), "\0");

    return $decrypted; //Devuelve el string desencriptado

}

//Esta funci�n procesa todas las sentencias SQL de consulta

function ProcesarConsulta($sentencia, $conexion)
{

    $procesar = mysql_query($sentencia, $conexion) or die(mysql_error());

    return $procesar;

}

function CeldaVacia()
{

    $parrilla2 = "<td width='29%' id='columna_0' class='parrilla_vacio'>";

    $parrilla2 = $parrilla2 . "<div id='opcion_0' class='div_apuesta_opcion'>";

    $parrilla2 = $parrilla2 . "<table width='100%' cellspacing='0'>";

    $parrilla2 = $parrilla2 . "<tr>";

    $parrilla2 = $parrilla2 . "<td width='100%' class='parrilla_logro_texto' id='logrotxt_0'>&nbsp;&nbsp;&nbsp;<span class='parrilla_logro_valor' id='logro_0'>&nbsp;</span></td>";

    $parrilla2 = $parrilla2 . "</tr>";

    $parrilla2 = $parrilla2 . "</table>";

    $parrilla2 = $parrilla2 . "</div>";

    $parrilla2 = $parrilla2 . "</td>";

    return $parrilla2;

}

function validateDate($date)
{

    list($year, $month, $day) = explode('-', $date);

    if (is_numeric($year) && is_numeric($month) && is_numeric($day)) {
        return checkdate($month, $day, $year);
    }

    return false;

}

function DepurarCaracteres($texto_depurar)
{

    $texto_depurar = str_replace("'", "", $texto_depurar);

    $texto_depurar = str_replace('"', "", $texto_depurar);

    $texto_depurar = str_replace(">", "", $texto_depurar);

    $texto_depurar = str_replace("<", "", $texto_depurar);

    $texto_depurar = str_replace("[", "", $texto_depurar);

    $texto_depurar = str_replace("]", "", $texto_depurar);

    $texto_depurar = str_replace("{", "", $texto_depurar);

    $texto_depurar = str_replace("}", "", $texto_depurar);

    $texto_depurar = str_replace("�", "", $texto_depurar);

    $texto_depurar = str_replace("`", "", $texto_depurar);

    $texto_depurar = str_replace("|", "", $texto_depurar);

    $texto_depurar = str_replace("�", "", $texto_depurar);

    $texto_depurar = str_replace("�", "", $texto_depurar);

    $texto_depurar = str_replace("%", "", $texto_depurar);

    $texto_depurar = str_replace("&", "", $texto_depurar);

    $texto_depurar = str_replace("�", "", $texto_depurar);

    $texto_depurar = str_replace("~", "", $texto_depurar);

    $texto_depurar = str_replace("+", "", $texto_depurar);

    $texto_depurar = str_replace("^", "", $texto_depurar);

    return $texto_depurar;

}

function ValidarCampo($valor, $obligatorio, $tipo_dato, $longitud)
{

    //Pregunta si el campo es obligatorio

    if ($obligatorio == "S") {

        //Valida que el campo contenga alg�n valor y que su tama�o no sobrepase el permitido

        if (strlen($valor) <= 0 or strlen($valor) > $longitud) {
            return false;
        }

        //Valida el tipo de campo

        switch ($tipo_dato) {

            case "N": //Tipo n�mero

                if (!is_numeric($valor)) {
                    return false;
                }

                break;

            case "E": //Tipo email

                if (!filter_var(filter_var($valor, FILTER_SANITIZE_EMAIL), FILTER_VALIDATE_EMAIL)) {
                    return false;
                }

                break;

            case "F": //Tipo fecha

                if (strlen($valor) != "10") {
                    return false;
                } else {

                    if (!validateDate($valor)) {
                        return false;
                    }

                }

                break;

            case "H": //Tipo hora

                if (strlen($valor) != 5) {
                    return false;
                } else {

                    $separado = split("[:]", $valor);

                    if ((floatval($separado[0]) < 0 and floatval($separado[0]) > 23) or (floatval($separado[1]) < 0 and floatval($separado[1]) > 59)) {
                        return false;
                    }

                }

                break;

        }

    } else {

        //Depura valor

        $valor = str_replace("_empty", "", $valor);

        //No es obligatorio pero contiene alg�n valor

        if (strlen($valor) > 0) {

            //Valida que no sobrepase la longitud maxima del campo

            if (strlen($valor) > $longitud) {
                return false;
            }

            //Valida el tipo de campo

            switch ($tipo_dato) {

                case "N": //Tipo n�mero

                    if (!is_numeric($valor)) {
                        return false;
                    }

                    break;

                case "E": //Tipo email

                    if (!filter_var($valor, FILTER_VALIDATE_EMAIL)) {
                        return false;
                    }

                    break;

                case "F": //Tipo fecha

                    if (strlen($valor) != 10) {
                        return false;
                    } else {

                        if (!validateDate($valor)) {
                            return false;
                        }

                    }

                    break;

                case "H": //Tipo fecha

                    if (strlen($valor) != 5) {
                        return false;
                    } else {

                        $separado = split("[:]", $valor);

                        if ((floatval($separado[0]) < 0 and floatval($separado[0]) > 23) or (floatval($separado[1]) < 0 and floatval($separado[1]) > 59)) {
                            return false;
                        }

                    }

                    break;

            }

        }

    }

    //Retorna campo OK

    return true;

}

function ValidarArregloNum($arreglo, $obligatorio)
{

    //Pregunta si el campo es obligatorio

    if ($obligatorio == "S") {

        //Valida si el arreglo contiene alg�n valor

        if (strlen($arreglo) <= 0) {
            return false;
        } else {

            //Convierte el string en un arreglo

            $arreglo_conv = explode(",", $arreglo);

            foreach ($arreglo_conv as $valor) {

                //Pregunta si el valor es num�rico

                if (!is_numeric($valor)) {
                    return false;
                } else {

                    //Pregunta si el valor en cero o negativo

                    if (floatval($valor) <= 0) {
                        return false;
                    }

                }

            }

        }

    } else {

        if (strlen($arreglo) > 0) {

            //Convierte el string en un arreglo

            $arreglo_conv = explode(",", $arreglo);

            foreach ($arreglo_conv as $valor) {

                //Pregunta si el valor es num�rico

                if (!is_numeric($valor)) {
                    return false;
                } else {

                    //Pregunta si el valor en cero o negativo

                    if (floatval($valor) <= 0) {
                        return false;
                    }

                }

            }

        }

    }

    //Retorna campo OK

    return true;

}

function CambioCaracter($palabra)
{

    $texto = $palabra;

    //Vocales min�sculas

    $texto = str_replace("á", "�", $texto);

    $texto = str_replace("é", "�", $texto);

    $texto = str_replace("í", "�", $texto);

    $texto = str_replace("ó", "�", $texto);

    $texto = str_replace("ú", "�", $texto);

    //Vocales may�sculas

    $texto = str_replace("Á", "�", $texto);

    $texto = str_replace("É", "�", $texto);

    $texto = str_replace("Í", "�", $texto);

    $texto = str_replace("Ó", "�", $texto);

    $texto = str_replace("Ú", "�", $texto);

    //�es

    $texto = str_replace("ñ", "�", $texto);

    $texto = str_replace("Ñ", "�", $texto);

    $function_ret = $texto;

    return $function_ret;

}

function EjecutarQuery()
{

    global $bd_servidor, $bd_usuario, $bd_clave, $bd_nombre, $contSql, $strSql;

    // Abre la conexi�n a la base de datos

    $db = ConectarBD($bd_servidor, $bd_usuario, $bd_clave, $bd_nombre);

    mysql_query("SET NAMES 'latin1'");

    mysql_select_db($bd_nombre, $db);

    $error = 0; //variable para detectar error.

    mysql_query("BEGIN"); // Inicio de Transacci�n

    // Recorre todas las instrucciones para ejecutarlas

    if ($contSql > 0) {

        for ($i = 1; $i <= $contSql; $i++) {

            $result = mysql_query($strSql[$i]);

            if (!$result) {

                $error = 1;

                break;

            }

        }

    }

    // Verifica si hubo error en alguno de los SQL

    if ($error) {

        mysql_query("ROLLBACK");

        return $error;

    } else {

        mysql_query("COMMIT");

    }

    //Cierra la conexion con la base de datos

    mysql_close($db);

    $db = null;

    // Retorna si hay error o no

    return $error;

}

function EjecutarQuery3($bd_servidor, $bd_usuario, $bd_clave, $bd_nombre, $contSql, $strSql)
{
    // Abre la conexión a la base de datos
    $db = ConectarBD($bd_servidor, $bd_usuario, $bd_clave, $bd_nombre);
    mysql_query("SET NAMES 'utf8'");
    mysql_select_db($bd_nombre, $db);

    $error = 0; //variable para detectar error.
    mysql_query("BEGIN"); // Inicio de Transacción

    // Recorre todas las instrucciones para ejecutarlas
    if ($contSql > 0) {
        for ($i = 1; $i <= $contSql; $i++) {
            $result = mysql_query($strSql[$i]);
            if (!$result) {
                $error = 1;
                break;
            }
        }
    }

    // Verifica si hubo error en alguno de los SQL
    if ($error) {
        mysql_query("ROLLBACK");
    } else {
        mysql_query("COMMIT");
    }

    //Cierra la conexion con la base de datos
    mysql_close($db);
    $db = null;

    // Retorna si hay error o no
    return $error;
}

function FormatoFecha($fecha)
{

    if ($fecha == "" || is_null($fecha)) {
        return "";
    } else {

        $mes_fecha = intval(substr($fecha, 5, 2));

        switch ($mes_fecha) {

            case 1:

                $mes_txt = "Ene";

                break;

            case 2:

                $mes_txt = "Feb";

                break;

            case 3:

                $mes_txt = "Mar";

                break;

            case 4:

                $mes_txt = "Abr";

                break;

            case 5:

                $mes_txt = "May";

                break;

            case 6:

                $mes_txt = "Jun";

                break;

            case 7:

                $mes_txt = "Jul";

                break;

            case 8:

                $mes_txt = "Ago";

                break;

            case 9:

                $mes_txt = "Sep";

                break;

            case 10:

                $mes_txt = "Oct";

                break;

            case 11:

                $mes_txt = "Nov";

                break;

            case 12:

                $mes_txt = "Dic";

                break;

        }

        return substr($fecha, 8, 2) . "-" . $mes_txt . "-" . substr($fecha, 0, 4);

    }

}

function FormatoFechaHora($fecha)
{

    $mes_fecha = intval(substr($fecha, 5, 2));

    switch ($mes_fecha) {

        case 1:

            $mes_txt = "Ene";

            break;

        case 2:

            $mes_txt = "Feb";

            break;

        case 3:

            $mes_txt = "Mar";

            break;

        case 4:

            $mes_txt = "Abr";

            break;

        case 5:

            $mes_txt = "May";

            break;

        case 6:

            $mes_txt = "Jun";

            break;

        case 7:

            $mes_txt = "Jul";

            break;

        case 8:

            $mes_txt = "Ago";

            break;

        case 9:

            $mes_txt = "Sep";

            break;

        case 10:

            $mes_txt = "Oct";

            break;

        case 11:

            $mes_txt = "Nov";

            break;

        case 12:

            $mes_txt = "Dic";

            break;

    }

    return substr($fecha, 8, 2) . "-" . $mes_txt . "-" . substr($fecha, 0, 4) . " " . substr($fecha, 11, 8);

}

function NombreMes($mes_num)
{

    switch ($mes_num) {

        case 1:

            $mes_txt = "Enero";

            break;

        case 2:

            $mes_txt = "Febrero";

            break;

        case 3:

            $mes_txt = "Marzo";

            break;

        case 4:

            $mes_txt = "Abril";

            break;

        case 5:

            $mes_txt = "Mayo";

            break;

        case 6:

            $mes_txt = "Junio";

            break;

        case 7:

            $mes_txt = "Julio";

            break;

        case 8:

            $mes_txt = "Agosto";

            break;

        case 9:

            $mes_txt = "Septiembre";

            break;

        case 10:

            $mes_txt = "Octubre";

            break;

        case 11:

            $mes_txt = "Noviembre";

            break;

        case 12:

            $mes_txt = "Diciembre";

            break;

    }

    return $mes_txt;

}

function EjecutarQuery2()
{

    global $bd_servidor, $bd_usuario, $bd_clave, $bd_nombre, $contSql, $strSql;

    // Abre la conexi�n a la base de datos

    $db = ConectarBD($bd_servidor, $bd_usuario, $bd_clave, $bd_nombre);

    mysql_query("SET NAMES 'utf8'");

    mysql_select_db($bd_nombre, $db);

    $error = 0; //variable para detectar error.

    mysql_query("BEGIN"); // Inicio de Transacci�n

    // Recorre todas las instrucciones para ejecutarlas

    if ($contSql > 0) {

        for ($i = 1; $i <= $contSql; $i++) {

            $result = mysql_query($strSql[$i]);

            if (!$result) {

                $error = 1;

                break;

            }

        }

    }

    // Verifica si hubo error en alguno de los SQL

    if ($error) {

        mysql_query("ROLLBACK");

    } else {

        mysql_query("COMMIT");

    }

    //Cierra la conexion con la base de datos

    mysql_close($db);

    $db = null;

    // Retorna si hay error o no

    return $error;

}

function CambioCaract($palabra)
{

    $texto = $palabra;

    //Vocales min�sculas

    $texto = str_replace("�", "&aacute;", $texto);

    $texto = str_replace("�", "&eacute;", $texto);

    $texto = str_replace("�", "&iacute;", $texto);

    $texto = str_replace("�", "&oacute;", $texto);

    $texto = str_replace("�", "&uacute;", $texto);

    //Vocales may�sculas

    $texto = str_replace("�", "&Aacute;", $texto);

    $texto = str_replace("�", "&Eacute;", $texto);

    $texto = str_replace("�", "&Iacute;�", $texto);

    $texto = str_replace("�", "&Oacute;", $texto);

    $texto = str_replace("�", "&Uacute;", $texto);

    //�es

    $texto = str_replace("�", "&ntilde;", $texto);

    $texto = str_replace("�", "&Ntilde;", $texto);

    return $texto;

}

function CaracterGuardar($palabra)
{

    $texto = $palabra;

    //Vocales min�sculas

    $texto = str_replace("á", "�", $texto);

    $texto = str_replace("é", "�", $texto);

    $texto = str_replace("í", "�", $texto);

    $texto = str_replace("ó", "�", $texto);

    $texto = str_replace("ú", "�", $texto);

    //Vocales may�sculas

    $texto = str_replace("Á", "�", $texto);

    $texto = str_replace("É", "�", $texto);

    $texto = str_replace("Í", "�", $texto);

    $texto = str_replace("Ó", "�", $texto);

    $texto = str_replace("Ú", "�", $texto);

    //�es

    $texto = str_replace("ñ", "�", $texto);

    $texto = str_replace("Ñ", "�", $texto);

    return $texto;

}

function GenerarClaveTicket($length)
{

    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';

    $randomString = '';

    for ($i = 0; $i < $length; $i++) {

        $randomString .= $characters[rand(0, strlen($characters) - 1)];

    }

    return $randomString;

}

function GenerarClaveTicket2($length)
{

    $characters = '0123456789';

    $randomString = '';

    for ($i = 0; $i < $length; $i++) {

        $randomString .= $characters[rand(0, strlen($characters) - 1)];

    }

    return $randomString;

}

function centimos()
{

    global $importe_parcial;

    $importe_parcial = number_format($importe_parcial, 2, ".", "") * 100;

    if ($importe_parcial > 0) {
        $num_letra = " con " . decena_centimos($importe_parcial);
    } else {
        $num_letra = "";
    }

    return $num_letra;

}

function unidad_centimos($numero)
{

    switch ($numero) {

        case 9: {

            $num_letra = "nueve";

            break;

        }

        case 8: {

            $num_letra = "ocho";

            break;

        }

        case 7: {

            $num_letra = "siete";

            break;

        }

        case 6: {

            $num_letra = "seis";

            break;

        }

        case 5: {

            $num_letra = "cinco";

            break;

        }

        case 4: {

            $num_letra = "cuatro";

            break;

        }

        case 3: {

            $num_letra = "tres";

            break;

        }

        case 2: {

            $num_letra = "dos";

            break;

        }

        case 1: {

            $num_letra = "un";

            break;

        }

    }

    return $num_letra;

}

function decena_centimos($numero)
{

    if ($numero >= 10) {

        if ($numero >= 90 && $numero <= 99) {

            if ($numero == 90) {
                return "noventa";
            } else if ($numero == 91) {
                return "noventa y uno";
            } else {
                return "noventa y " . unidad_centimos($numero - 90);
            }

        }

        if ($numero >= 80 && $numero <= 89) {

            if ($numero == 80) {
                return "ochenta";
            } else if ($numero == 81) {
                return "ochenta y uno";
            } else {
                return "ochenta y " . unidad_centimos($numero - 80);
            }

        }

        if ($numero >= 70 && $numero <= 79) {

            if ($numero == 70) {
                return "setenta";
            } else if ($numero == 71) {
                return "setenta y uno";
            } else {
                return "setenta y " . unidad_centimos($numero - 70);
            }

        }

        if ($numero >= 60 && $numero <= 69) {

            if ($numero == 60) {
                return "sesenta";
            } else if ($numero == 61) {
                return "sesenta y uno";
            } else {
                return "sesenta y " . unidad_centimos($numero - 60);
            }

        }

        if ($numero >= 50 && $numero <= 59) {

            if ($numero == 50) {
                return "cincuenta";
            } else if ($numero == 51) {
                return "cincuenta y uno";
            } else {
                return "cincuenta y " . unidad_centimos($numero - 50);
            }

        }

        if ($numero >= 40 && $numero <= 49) {

            if ($numero == 40) {
                return "cuarenta";
            } else if ($numero == 41) {
                return "cuarenta y uno";
            } else {
                return "cuarenta y " . unidad_centimos($numero - 40);
            }

        }

        if ($numero >= 30 && $numero <= 39) {

            if ($numero == 30) {
                return "treinta";
            } else if ($numero == 91) {
                return "treinta y uno";
            } else {
                return "treinta y " . unidad_centimos($numero - 30);
            }

        }

        if ($numero >= 20 && $numero <= 29) {

            if ($numero == 20) {
                return "veinte";
            } else if ($numero == 21) {
                return "veintiun";
            } else {
                return "veinti" . unidad_centimos($numero - 20);
            }

        }

        if ($numero >= 10 && $numero <= 19) {

            if ($numero == 10) {
                return "diez";
            } else if ($numero == 11) {
                return "once";
            } else if ($numero == 11) {
                return "doce";
            } else if ($numero == 11) {
                return "trece";
            } else if ($numero == 11) {
                return "catorce";
            } else if ($numero == 11) {
                return "quince";
            } else if ($numero == 11) {
                return "dieciseis";
            } else if ($numero == 11) {
                return "diecisiete";
            } else if ($numero == 11) {
                return "dieciocho";
            } else if ($numero == 11) {
                return "diecinueve";
            }

        }

    } else {
        return unidad_centimos($numero);
    }

}

function unidad($numero)
{

    switch ($numero) {

        case 9: {

            $num = "nueve";

            break;

        }

        case 8: {

            $num = "ocho";

            break;

        }

        case 7: {

            $num = "siete";

            break;

        }

        case 6: {

            $num = "seis";

            break;

        }

        case 5: {

            $num = "cinco";

            break;

        }

        case 4: {

            $num = "cuatro";

            break;

        }

        case 3: {

            $num = "tres";

            break;

        }

        case 2: {

            $num = "dos";

            break;

        }

        case 1: {

            $num = "uno";

            break;

        }

    }

    return $num;

}

function decena($numero)
{

    if ($numero >= 90 && $numero <= 99) {

        $num_letra = "noventa ";

        if ($numero > 90) {
            $num_letra = $num_letra . "y " . unidad($numero - 90);
        }

    } else if ($numero >= 80 && $numero <= 89) {

        $num_letra = "ochenta ";

        if ($numero > 80) {
            $num_letra = $num_letra . "y " . unidad($numero - 80);
        }

    } else if ($numero >= 70 && $numero <= 79) {

        $num_letra = "setenta ";

        if ($numero > 70) {
            $num_letra = $num_letra . "y " . unidad($numero - 70);
        }

    } else if ($numero >= 60 && $numero <= 69) {

        $num_letra = "sesenta ";

        if ($numero > 60) {
            $num_letra = $num_letra . "y " . unidad($numero - 60);
        }

    } else if ($numero >= 50 && $numero <= 59) {

        $num_letra = "cincuenta ";

        if ($numero > 50) {
            $num_letra = $num_letra . "y " . unidad($numero - 50);
        }

    } else if ($numero >= 40 && $numero <= 49) {

        $num_letra = "cuarenta ";

        if ($numero > 40) {
            $num_letra = $num_letra . "y " . unidad($numero - 40);
        }

    } else if ($numero >= 30 && $numero <= 39) {

        $num_letra = "treinta ";

        if ($numero > 30) {
            $num_letra = $num_letra . "y " . unidad($numero - 30);
        }

    } else if ($numero >= 20 && $numero <= 29) {

        if ($numero == 20) {
            $num_letra = "veinte ";
        } else {
            $num_letra = "veinti" . unidad($numero - 20);
        }

    } else if ($numero >= 10 && $numero <= 19) {

        switch ($numero) {

            case 10: {

                $num_letra = "diez ";

                break;

            }

            case 11: {

                $num_letra = "once ";

                break;

            }

            case 12: {

                $num_letra = "doce ";

                break;

            }

            case 13: {

                $num_letra = "trece ";

                break;

            }

            case 14: {

                $num_letra = "catorce ";

                break;

            }

            case 15: {

                $num_letra = "quince ";

                break;

            }

            case 16: {

                $num_letra = "dieciseis ";

                break;

            }

            case 17: {

                $num_letra = "diecisiete ";

                break;

            }

            case 18: {

                $num_letra = "dieciocho ";

                break;

            }

            case 19: {

                $num_letra = "diecinueve ";

                break;

            }

        }

    } else {
        $num_letra = unidad($numero);
    }

    return $num_letra;

}

function centena($numero)
{

    if ($numero >= 100) {

        if ($numero >= 900 & $numero <= 999) {

            $num_letra = "novecientos ";

            if ($numero > 900) {
                $num_letra = $num_letra . decena($numero - 900);
            }

        } else if ($numero >= 800 && $numero <= 899) {

            $num_letra = "ochocientos ";

            if ($numero > 800) {
                $num_letra = $num_letra . decena($numero - 800);
            }

        } else if ($numero >= 700 && $numero <= 799) {

            $num_letra = "setecientos ";

            if ($numero > 700) {
                $num_letra = $num_letra . decena($numero - 700);
            }

        } else if ($numero >= 600 && $numero <= 699) {

            $num_letra = "seiscientos ";

            if ($numero > 600) {
                $num_letra = $num_letra . decena($numero - 600);
            }

        } else if ($numero >= 500 && $numero <= 599) {

            $num_letra = "quinientos ";

            if ($numero > 500) {
                $num_letra = $num_letra . decena($numero - 500);
            }

        } else if ($numero >= 400 && $numero <= 499) {

            $num_letra = "cuatrocientos ";

            if ($numero > 400) {
                $num_letra = $num_letra . decena($numero - 400);
            }

        } else if ($numero >= 300 && $numero <= 399) {

            $num_letra = "trescientos ";

            if ($numero > 300) {
                $num_letra = $num_letra . decena($numero - 300);
            }

        } else if ($numero >= 200 && $numero <= 299) {

            $num_letra = "doscientos ";

            if ($numero > 200) {
                $num_letra = $num_letra . decena($numero - 200);
            }

        } else if ($numero >= 100 && $numero <= 199) {

            if ($numero == 100) {
                $num_letra = "cien ";
            } else {
                $num_letra = "ciento " . decena($numero - 100);
            }

        }

    } else {
        $num_letra = decena($numero);
    }

    return $num_letra;

}

function cien()
{

    global $importe_parcial;

    $parcial = 0;
    $car = 0;

    while (substr($importe_parcial, 0, 1) == 0) {
        $importe_parcial = substr($importe_parcial, 1, strlen($importe_parcial) - 1);
    }

    if ($importe_parcial >= 1 && $importe_parcial <= 9.99) {
        $car = 1;
    } else if ($importe_parcial >= 10 && $importe_parcial <= 99.99) {
        $car = 2;
    } else if ($importe_parcial >= 100 && $importe_parcial <= 999.99) {
        $car = 3;
    }

    $parcial = substr($importe_parcial, 0, $car);

    $importe_parcial = substr($importe_parcial, $car);

    $num_letra = centena($parcial) . centimos();

    return $num_letra;

}

function cien_mil()
{

    global $importe_parcial;

    $parcial = 0;
    $car = 0;

    while (substr($importe_parcial, 0, 1) == 0) {
        $importe_parcial = substr($importe_parcial, 1, strlen($importe_parcial) - 1);
    }

    if ($importe_parcial >= 1000 && $importe_parcial <= 9999.99) {
        $car = 1;
    } else if ($importe_parcial >= 10000 && $importe_parcial <= 99999.99) {
        $car = 2;
    } else if ($importe_parcial >= 100000 && $importe_parcial <= 999999.99) {
        $car = 3;
    }

    $parcial = substr($importe_parcial, 0, $car);

    $importe_parcial = substr($importe_parcial, $car);

    if ($parcial > 0) {

        if ($parcial == 1) {
            $num_letra = "mil ";
        } else {
            $num_letra = centena($parcial) . " mil ";
        }

    }

    return $num_letra;

}

function millon()
{

    global $importe_parcial;

    $parcial = 0;
    $car = 0;

    while (substr($importe_parcial, 0, 1) == 0) {
        $importe_parcial = substr($importe_parcial, 1, strlen($importe_parcial) - 1);
    }

    if ($importe_parcial >= 1000000 && $importe_parcial <= 9999999.99) {
        $car = 1;
    } else if ($importe_parcial >= 10000000 && $importe_parcial <= 99999999.99) {
        $car = 2;
    } else if ($importe_parcial >= 100000000 && $importe_parcial <= 999999999.99) {
        $car = 3;
    }

    $parcial = substr($importe_parcial, 0, $car);

    $importe_parcial = substr($importe_parcial, $car);

    if ($parcial == 1) {
        $num_letras = "un mill�n ";
    } else {
        $num_letras = centena($parcial) . " millones ";
    }

    return $num_letras;

}

function convertir_a_letras($numero)
{

    global $importe_parcial;

    $importe_parcial = $numero;

    if ($numero < 1000000000) {

        if ($numero >= 1000000 && $numero <= 999999999.99) {
            $num_letras = millon() . cien_mil() . cien();
        } else if ($numero >= 1000 && $numero <= 999999.99) {
            $num_letras = cien_mil() . cien();
        } else if ($numero >= 1 && $numero <= 999.99) {
            $num_letras = cien();
        } else if ($numero >= 0.01 && $numero <= 0.99) {

            if ($numero == 0.01) {
                $num_letras = "uno";
            } else {
                $num_letras = convertir_a_letras(($numero * 100) . "/100");
            }

        }

    }

    return $num_letras;

}

function pseudoLlaveAleatoria()
{
    if (function_exists('openssl_random_pseudo_bytes')) {
        $rnd = openssl_random_pseudo_bytes(256, $strong);
        if ($strong == true) {
            return $rnd;
        }
    }
    $sha = '';
    $rnd = '';
    for ($i = 0; $i < 256; $i++) {
        $sha = hash('sha256', $sha . mt_rand());
        $char = mt_rand(0, 62);
        $rnd .= chr(hexdec($sha[$char] . $sha[$char + 1]));
    }
    return $rnd;
}

/**
 * [password función que le asigna un hash a la contraseña]
 * @param  [type] $pass [password para encriptar]
 * @return [type]       [el password encriptado]
 */
function password($pass)
{
    $salt = pseudoLlaveAleatoria();
    for ($i = 0; $i < 5; $i++) {
        $hash = hash('sha384', $hash . $salt . $pass);
    }
    return base64_encode($salt) . '$' . $hash;
}

function ObtenerIP()
{
    if ($_SERVER['HTTP_X_FORWARDED_FOR'] != '') {
        $client_ip = (!empty($_SERVER['REMOTE_ADDR'])) ? $_SERVER['REMOTE_ADDR'] : ((!empty($_ENV['REMOTE_ADDR'])) ? $_ENV['REMOTE_ADDR'] : "unknown");

        // los proxys van añadiendo al final de esta cabecera
        // las direcciones ip que van "ocultando". Para localizar la ip real
        // del usuario se comienza a mirar por el principio hasta encontrar
        // una dirección ip que no sea del rango privado. En caso de no
        // encontrarse ninguna se toma como valor el REMOTE_ADDR

        $entries = split('[, ]', $_SERVER['HTTP_X_FORWARDED_FOR']);

        reset($entries);
        while (list(, $entry) = each($entries)) {
            $entry = trim($entry);
            if (preg_match("/^([0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)/", $entry, $ip_list)) {
                $private_ip = array(
                    '/^0\./',
                    '/^127\.0\.0\.1/',
                    '/^192\.168\..*/',
                    '/^172\.((1[6-9])|(2[0-9])|(3[0-1]))\..*/',
                    '/^10\..*/');

                $found_ip = preg_replace($private_ip, $client_ip, $ip_list[1]);

                if ($client_ip != $found_ip) {
                    $client_ip = $found_ip;
                    break;
                }
            }
        }
    } else {
        $client_ip = (!empty($_SERVER['REMOTE_ADDR'])) ? $_SERVER['REMOTE_ADDR'] : ((!empty($_ENV['REMOTE_ADDR'])) ? $_ENV['REMOTE_ADDR'] : "unknown");
    }
    return $client_ip;
}

function puntos($s)
{
    $s = str_replace('"', '', $s);
    $s = str_replace(':', '', $s);
    $s = str_replace('.', '', $s);
    $s = str_replace(',', '', $s);
    $s = str_replace(';', '', $s);
    return $s;
}

function porcentaje($cantidad, $porciento)
{
    return $cantidad * ($porciento / 100);
}
