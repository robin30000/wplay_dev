<?php
/*
* Author: Selecta Consulting group
* Fecha Creación: Octubre 25 de 2016
* Ultima Modificacion: Noviembre 28 de 2016
*
*/

//include 'Constantes.php';
include 'InformacionPeticion.php';
include 'ResultadoPeticion.php';
include 'lib.php';

class TuCompraServicio
{
    protected $fields = " ";
    protected $conexion;

    function __construct()
    {
    }

    //Este metodo permite enviar la solicitud a tuCompra haciendo un redireccionamiento POST de los parametros que se deben enviar
/*    function solicitudTransaccion($informacionPeticion, $url_tuCompra)*/
 /*   function solicitudTransaccion($informacionPeticion)
    {
        try {
            $this->fields = 'usuario=' . $informacionPeticion->usuario .
                '&factura=' . $informacionPeticion->factura .
                '&valor=' . $informacionPeticion->valor .
                '&descripcionFactura=' . str_replace(" ", "+", $informacionPeticion->descripcionFactura) .
                '&tokenSeguridad=' . $informacionPeticion->tokenSeguridad;

            if ($informacionPeticion->metodoPago != null) {
                if (trim($informacionPeticion->metodoPago) != '') {
                    $this->fields = $this->fields . '&metodoPago=' . $informacionPeticion->metodoPago;
                }
            }

            if ($informacionPeticion->documentoComprador != null) {
                if (trim($informacionPeticion->documentoComprador) != '') {
                    $this->fields = $this->fields . '&documentoComprador=' . $informacionPeticion->documentoComprador;
                }
            }

            if ($informacionPeticion->tipoDocumento != null) {
                if (trim($informacionPeticion->tipoDocumento) != '') {
                    $this->fields = $this->fields . '&tipoDocumento=' . $informacionPeticion->tipoDocumento;
                }
            }

            if ($informacionPeticion->nombreComprador != null) {
                if (trim($informacionPeticion->nombreComprador) != '') {
                    $this->fields = $this->fields . '&nombreComprador=' . $informacionPeticion->nombreComprador;
                }
            }

            if ($informacionPeticion->apellidoComprador != null) {
                if (trim($informacionPeticion->apellidoComprador) != '') {
                    $this->fields = $this->fields . '&apellidoComprador=' . $informacionPeticion->apellidoComprador;
                }
            }

            if ($informacionPeticion->correoComprador != null) {
                if (trim($informacionPeticion->correoComprador) != '') {
                    $this->fields = $this->fields . '&correoComprador=' . $informacionPeticion->correoComprador;
                }
            }

            if ($informacionPeticion->telefonoComprador != null) {
                if (trim($informacionPeticion->telefonoComprador) != '') {
                    $this->fields = $this->fields . '&telefonoComprador=' . $informacionPeticion->telefonoComprador;
                }
            }

            if ($informacionPeticion->celularComprador != null) {
                if (trim($informacionPeticion->celularComprador) != '') {
                    $this->fields = $this->fields . '&celularComprador=' . $informacionPeticion->celularComprador;
                }
            }

            if ($informacionPeticion->direccionComprador != null) {
                if (trim($informacionPeticion->direccionComprador) != '') {
                    $this->fields = $this->fields . '&direccionComprador=' . $informacionPeticion->direccionComprador;
                }
            }

            if ($informacionPeticion->paisComprador != null) {
                if (trim($informacionPeticion->paisComprador) != '') {
                    $this->fields = $this->fields . '&paisComprador=' . $informacionPeticion->paisComprador;
                }
            }

            if ($informacionPeticion->ciudadComprador != null) {
                if (trim($informacionPeticion->ciudadComprador) != '') {
                    $this->fields = $this->fields . '&ciudadComprador=' . $informacionPeticion->ciudadComprador;
                }
            }

            if ($informacionPeticion->twitterComprador != null) {
                if (trim($informacionPeticion->twitterComprador) != '') {
                    $this->fields = $this->fields . '&twitterComprador=' . $informacionPeticion->twitterComprador;
                }
            }

            if ($informacionPeticion->recurrencia != null) {
                if (trim($informacionPeticion->recurrencia) != '') {
                    $this->fields = $this->fields . '&recurrencia=' . $informacionPeticion->recurrencia;
                }
            }

            if ($informacionPeticion->periodo != null) {
                if (trim($informacionPeticion->periodo) != '') {
                    $this->fields = $this->fields . '&periodo=' . $informacionPeticion->periodo;
                }
            }

            if ($informacionPeticion->tokenTarjeta != null) {
                if (trim($informacionPeticion->tokenTarjeta) != '') {
                    $this->fields = $this->fields . '&tokenTarjeta=' . $informacionPeticion->tokenTarjeta;
                }
            }

            if ($informacionPeticion->recurrencia != null) {
                if (trim($informacionPeticion->recurrencia) != '') {
                    $this->fields = $this->fields . '&recurrencia=' . $informacionPeticion->recurrencia;
                }
            }

            if($this->insertarSolicitudTransaccion($informacionPeticion)){
              header('HTTP/1.1 307 Temporary Redirect');
              header('Location:' . $url_tuCompra . '?' . $this->fields);
              exit();
            }
        } catch (Exception $err) {
            echo $err;
        }
    }*/

    //Metodo que permite obtener las configuraciones de tu compra
/*    function obtenerConfiguracionTuCompra()
    {
        $conexion = new ConectorBD();
        $configuracionesTuCompra = array();
        if ($conexion->initConexion() == "OK") {
            $sql = " SELECT "
                . " url_base_consulta"
                . " ,url_base_compra"
                . " ,usuario"
                . " ,cliente"
                . " ,password"
                . " ,llave_terminal"
                . " FROM configuracion_tu_compra";
            if ($resultado_data = $conexion->consultar($sql)) {
                while ($fila = $resultado_data->fetch_assoc()) {
                    array_push($configuracionesTuCompra, $fila);
                }
                return $configuracionesTuCompra;
            } else {
                $configuracionesTuCompra = null;
                return $configuracionesTuCompra;
            }
        } else {
            $configuracionesTuCompra = null;
            return $configuracionesTuCompra;
        }
    }*/

    //Obtiene todas las solicitudes que por algun motivo nunca tuvieron respuesta y no se genero el registro en usuario detalle de transaccion
    function obtenerSolicitudesSinDetalle()
    {
        $conexion = new ConectorBD();
        $transaccionesPendientes = array();
        if ($conexion->initConexion() == "OK") {
            $sql = "SELECT "
                . "transaccion_id"
                . ", id_usuario_tu_compra"
                . ", id_usuario"
                . ", perfil_id"
                . ", num_factura"
                . ", descripcion_factura"
                . ", valor_factura"
                . ", token"
                . ", id_terminal"
                . ", fecha"
                . ", hora"
                . " FROM usuario_solicitud_transaccion AS us"
                . " INNER JOIN usuario_perfil AS up on up.usuario_id = us.id_usuario "
                . " WHERE transaccion_id not in (SELECT id_transaccion FROM usuario_detalle_transaccion)"
                . " AND TIMESTAMPDIFF(MINUTE, concat(fecha,' ',hora), now()) > 3 ";
            if ($resultado_data = $conexion->consultar($sql)) {
                while ($fila = $resultado_data->fetch_assoc()) {
                    array_push($transaccionesPendientes, $fila);
                }
                return $transaccionesPendientes;
            } else {
                $transaccionesPendientes = null;
                return $transaccionesPendientes;
            }
        } else {
            $transaccionesPendientes = null;
            return $transaccionesPendientes;
        }
    }

    //Obtiene todas las transacciones que devolvieron estado pendiente y que su ultima fecha de verificacion es mayor a 1 minuto
    function obtenerTransaccionesPendientes()
    {
        $conexion = new ConectorBD();
        $transaccionesPendientes = array();
        if ($conexion->initConexion() == "OK") {
            $sql = "SELECT "
                . "dtransaccion_id"
                . ", us.transaccion_id"
                . ", ud.id_transaccion"
                . ", num_factura"
                . ", estado"
                . ", id_usuario"
                . ", valor_factura"
                . ", codigo_autorizacion"
                . ", firma_tu_compra"
                . ", numeroTransaccion"
                . ", fechaPago"
                . ", perfil_id"
                . ", horaPago"
                . ", fecha_ultima_ver"
                . ", hora_ultima_ver"
                . ", notificado"
                . " FROM usuario_detalle_transaccion AS ud"
                . " INNER JOIN usuario_solicitud_transaccion AS us on ud.id_transaccion = us.transaccion_id"
                . " INNER JOIN usuario_perfil AS up on up.usuario_id = us.id_usuario "
                . " WHERE estado = '0' AND TIMESTAMPDIFF(MINUTE, concat(fecha_ultima_ver, ' ',hora_ultima_ver), now()) > 1";
            if ($resultado_data = $conexion->consultar($sql)) {
                while ($fila = $resultado_data->fetch_assoc()) {
                    array_push($transaccionesPendientes, $fila);
                }
                return $transaccionesPendientes;
            } else {
                $transaccionesPendientes = null;
                return $transaccionesPendientes;
            }
        } else {
            $transaccionesPendientes = null;
            return $transaccionesPendientes;
        }
    }

    //Obtiene todos los metodos de pagos
    function obtenerMetodosDePago()
    {
        $conexion = new ConectorBD();
        $metodosPagos = array();
        if ($conexion->initConexion() == "OK") {
            $sql = "SELECT "
                . "id"
                . ",descripcion"
                . " FROM metodo_pago";
            if ($resultado_data = $conexion->consultar($sql)) {
                while ($fila = $resultado_data->fetch_assoc()) {
                    array_push($metodosPagos, $fila);
                }
                return $metodosPagos;
            } else {
                $metodosPagos = null;
                return $metodosPagos;
            }
        } else {
            $metodosPagos = null;
            return $metodosPagos;
        }
    }

    //Obtiene todos los creditos del jugador
    function obtenerCreditosDelJugador($usuario_id)
    {
        $conexion = new ConectorBD();
        $creditosJugador = array();
        if ($conexion->initConexion() == "OK") {
            $sql = "SELECT "
                . " registro_id"
                . " ,creditos"
                . " ,creditos_ant"
                . " FROM registro"
                . " WHERE usuario_id='" . $usuario_id . "'";;
            if ($resultado_data = $conexion->consultar($sql)) {
                while ($credito = $resultado_data->fetch_assoc()) {
                    array_push($creditosJugador, $credito);
                }
                return $creditosJugador;
            } else {
                $creditosJugador = null;
                return $creditosJugador;
            }
        } else {
            $creditosJugador = null;
            return $creditosJugador;
        }
    }

    //Obtiene las configuracion de creditos para el perfil indicado
    function obtenerConfiguracionCredito($pefil_usuario)
    {
        $conexion = new ConectorBD();
        $configuracionCredito = array();
        if ($conexion->initConexion() == "OK") {
            $sql = "SELECT "
                . " id_credito"
                . ",cantidad_creditos"
                . ",valor"
                . " FROM configuracion_credito WHERE id_perfil='" . $pefil_usuario . "'";;
            if ($resultado_data = $conexion->consultar($sql)) {
                while ($credito = $resultado_data->fetch_assoc()) {
                    array_push($configuracionCredito, $credito);
                }
                return $configuracionCredito;
            } else {
                $configuracionCredito = null;
                return $configuracionCredito;
            }
        } else {
            $configuracionCredito = null;
            return $configuracionCredito;
        }
    }

    //Obtener la solicitud de la transaccion de acuerdo a la factura generada
    function obtenerSolicitudTransaccion($numeroFactura)
    {
        $conexion = new ConectorBD();
        $solicitudTransaccion = array();
        if ($conexion->initConexion() == "OK") {
          $sql = "SELECT "
              . "transaccion_id"
              . ", id_usuario_tu_compra"
              . ", id_usuario"
              . ", perfil_id"
              . ", num_factura"
              . ", descripcion_factura"
              . ", valor_factura"
              . ", token"
              . ", id_terminal"
              . ", fecha"
              . ", hora"
              . " FROM usuario_solicitud_transaccion AS us"
              . " INNER JOIN usuario_perfil AS up on up.usuario_id = us.id_usuario "
              . " WHERE us.num_factura='" . $numeroFactura . "'";
            if ($resultado_data = $conexion->consultar($sql)) {
                while ($transaccion = $resultado_data->fetch_assoc()) {
                    array_push($solicitudTransaccion, $transaccion);
                }
                return $solicitudTransaccion;
            } else {
                $solicitudTransaccion = null;
                return $solicitudTransaccion;
            }
        } else {
            $solicitudTransaccion = null;
            return $solicitudTransaccion;
        }
    }

    //Actualiza los creditos del jugador
    function actualizarCreditosJugador($creditosJugador)
    {


        
        $conexion = new ConectorBD();
        //Se conecta y verifica la conexion con la base de dtos
        if ($conexion->initConexion() == "OK") {
            //Se indica la tabla que se desea actualizar
            $tabla = 'registro';
            //Se indican los campos que se actualizaran
            $data = array();
            $data['creditos'] = $creditosJugador['creditos'];
            $data['creditos_ant'] = $creditosJugador['creditos_ant'];
            //Se indica la condicion bajo la cual se actualizara el registro, en este caso sobre su primary key
            $condicion = "usuario_id = " . $creditosJugador['usuario_id'];
            //Se actualiza la informacion en la base de datos  y se verifica que haya sido exitoso
            if ($conexion->actualizarRegistro($tabla, $data, $condicion)) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    //Inserta la solicitud de la transaccion
    function insertarSolicitudTransaccion($informacionPeticion){
      $conexion = new ConectorBD();
      //Se verifica la conexion con la base de datos
      if ($conexion->initConexion() == "OK") {
          //Se indica la tabla a la cual se le desea insertar el registro
          $tabla = 'usuario_solicitud_transaccion';
          //Se indican los campos que se insertaran
          $data = array();
          $data['id_usuario'] = $informacionPeticion->usuario_conectado_id;//ESTO SE DEBE MODIFICAR Y ENVIAR EL ID DEL USUARIO CONECTADO ACTUALMENTE
          $data['valor_factura'] = $informacionPeticion->valor;
          $data['id_usuario_tu_compra'] = $informacionPeticion->usuario;
          $data['num_factura'] = $informacionPeticion->factura;
          $data['descripcion_factura'] = $informacionPeticion->descripcionFactura;
          $data['token'] = $informacionPeticion->tokenSeguridad;
          $data['id_terminal'] =$informacionPeticion->terminal;
          $data['fecha'] = date("Y-m-d");
          $data['hora'] = date("H:i:s");
          //Se inserta la informacion en la base de datos y se verifica que haya sido exitoso
          if ($conexion->insertData($tabla, $data)) {
              return true;
          } else {
              return false;
          }
      } else {
          return false;
      }
    }

    //Inserta el detalle de la transaccion
    function insertarDetalleTransaccion($resultado)
    {
        $conexion = new ConectorBD();
        //Se verifica la conexion con la base de datos
        if ($conexion->initConexion() == "OK") {
            //Se indica la tabla a la cual se le desea insertar el registro
            $tabla = 'usuario_detalle_transaccion';
            //Se indican los campos que se insertaran
            $data = array();
            $data['id_transaccion'] = intval($resultado['id_transaccion']);
            $data['estado'] = intval($resultado['estado']);
            $data['codigo_autorizacion'] = $resultado['codigo_autorizacion'];
            $data['firma_tu_compra'] = $resultado['firma_tu_compra'];
            $data['numeroTransaccion'] = $resultado['numeroTransaccion'];
            $data['fechaPago'] = $resultado['fechaPago'];
            $data['horaPago'] = $resultado['horaPago'];
            $data['fecha_ultima_ver'] = $resultado['fecha_ultima_ver'];
            $data['hora_ultima_ver'] = $resultado['hora_ultima_ver'];
            $data['notificado'] = intval($resultado['notificado']);
            //Se inserta la informacion en la base de datos y se verifica que haya sido exitoso
            if ($conexion->insertData($tabla, $data)) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    //Actualiza el detalle de la transaccion
    function actualizarDetalleTransaccion($resultado)
    {
        $conexion = new ConectorBD();
        //Se conecta y verifica la conexion con la base de dtos
        if ($conexion->initConexion() == "OK") {
            //Se indica la tabla que se desea actualizar
            $tabla = 'usuario_detalle_transaccion';
            //Se indican los campos que se actualizaran
            $data = array();
            $data['id_transaccion'] = intval($resultado['id_transaccion']);
            $data['estado'] = intval($resultado['estado']);
            $data['codigo_autorizacion'] = $resultado['codigo_autorizacion'];
            $data['firma_tu_compra'] = $resultado['firma_tu_compra'];
            $data['numeroTransaccion'] = $resultado['numeroTransaccion'];
            $data['fechaPago'] = $resultado['fechaPago'];
            $data['horaPago'] = $resultado['horaPago'];
            $data['fecha_ultima_ver'] = $resultado['fecha_ultima_ver'];
            $data['hora_ultima_ver'] = $resultado['hora_ultima_ver'];
            //Se indica la condicion bajo la cual se actualizara el registro, en este caso sobre su primary key
            $condicion = "dtransaccion_id = " . $resultado['dtransaccion_id'];
            //Se actualiza la informacion en la base de datos  y se verifica que haya sido exitoso
            if ($conexion->actualizarRegistro($tabla, $data, $condicion)) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    //Consulta el estado de la transaccion y devuelve un json con todos los parametros que se requieren
    function consultarEstadoTransaccion($url_base, $usuario, $password, $cliente, $serialComercio)
    {
        $result = array();
        //Se indica la url rest que se desea consultar
        $url = $url_base . "/" . $usuario . "/" . $password . "/" . $cliente . "/" . $serialComercio;
        echo "<script type='text/javascript'>console.log('$url');</script>";
        //Comienza consumo Rest php
        $conexion = curl_init();
        curl_setopt($conexion, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($conexion, CURLOPT_HEADER, 0);
        curl_setopt($conexion, CURLOPT_URL, $url);
        $resultado = curl_exec($conexion);
        curl_close($conexion);
        //Se retorna el resultado del JSON en una variable cadena
        return $resultado;
    }

    //Procesa el estado de la transaccion devuelta en el json e identifica si esta ya ha sido aprobada o por el contrario fue rechazada
    function procesarTransaccionDevuelta($resultado, $fila)
    {

        if ($resultado != '') {
            $resultado = json_decode($resultado);
            foreach ($resultado as $resultado_json) {
                $resultData = array();
                switch ($resultado_json->transaccionAprobada) {
                    case '1': //Exitosa pago ahorros/corriente
                        $resultData = $this->procesarCreditosJugador($fila);
                    case '-1': //Rechazada ahorros/corriente
                    case '0': //Pendiente
                    case '2': //Abortada
                    case '3': //Reversada
                        $listMetodosPagos = $this->obtenerMetodosDePago();
                        foreach ($listMetodosPagos as $metodo) {
                            if (($metodo['descripcion'] == 'visa' && $resultado_json->metodoPago == $metodo['id'])
                                || ($metodo['descripcion'] == 'American Express' && $resultado_json->metodoPago == $metodo['id'])
                                || ($metodo['descripcion'] == 'Diners Club' && $resultado_json->metodoPago == $metodo['id'])
                            ) {
                                if ($resultado_json->codigoAutorizacion != '0000') {
                                    //Pago aprobado con tarjeta de credito visa
                                    $resultData = $this->procesarCreditosJugador($fila);
                                } else {
                                    $resultData['notificado'] = 0;
                                }
                            } else if ($metodo['descripcion'] == 'Mastercard' && $resultado_json->metodoPago == $metodo['id']) {
                                if ($resultado_json->codigoAutorizacion != '00') {
                                    //Pago Exitoso Tarjeta credito Master card
                                    $resultData = $this->procesarCreditosJugador($fila);
                                } else {
                                    $resultData['notificado'] = 0;
                                }
                            }
                        }
                        $resultData['id_transaccion'] = $fila['transaccion_id'];
                        $resultData['num_factura'] = $resultado_json->codigoFactura;
                        $resultData['estado'] = $resultado_json->transaccionAprobada;
                        $resultData['codigo_autorizacion'] = $resultado_json->codigoAutorizacion;
                        $resultData['firma_tu_compra'] = $resultado_json->firmaTuCompra;
                        $resultData['numeroTransaccion'] = $resultado_json->numeroTransaccion;
                        $resultData['fechaPago'] = date("Y-m-d", strtotime($resultado_json->fechaPago));
                        $resultData['horaPago'] = date("H:i:s", strtotime($resultado_json->horaPago));
                        $resultData['fecha_ultima_ver'] = date("Y-m-d");
                        $resultData['hora_ultima_ver'] = date("H:i:s");
                        return $resultData;
                        break;
                }
            }
        }
    }

    //Procesa los creditos del jugador de la siguiente manera:
    /*
    1. Obtiene los creditos actuales del jugador
    2. Obtiene la configuracion de credito del perfil especifico
    3. Verifica que la configuracion exista, si esta no existe agregara en los creditos del jugador la misma cantidad del valor comprado
    4. Actualiza los creditos del jugador
    */
    function procesarCreditosJugador($fila)
    {
        // var_dump($fila);
        // exit();
        $resultData = $fila;
        $creditos_jugador = $this->obtenerCreditosDelJugador($fila['id_usuario']);
        $configuracion_creditos = $this->obtenerConfiguracionCredito($fila['perfil_id']);
        $credito_a_actualizar = array();
        if (count($creditos_jugador) > 0) {
            foreach ($creditos_jugador as $credito) {
                if (count($configuracion_creditos) > 0) {
                    foreach ($configuracion_creditos as $confCred) {
                        $creditos_recibidos = (($confCred['cantidad_creditos'] * $fila['valor_factura']) / $confCred['valor']);
                        $credito_a_actualizar['creditos'] = floatval($credito['creditos']) + floatval($creditos_recibidos);
                    }
                } else {
                    $credito_a_actualizar['creditos'] = $credito['creditos'] + $fila['valor_factura'];
                }
                $credito_a_actualizar['creditos_ant'] = $credito['creditos'];
                $credito_a_actualizar['usuario_id'] = $fila['id_usuario'];

                $resultData['creditos'] = $credito_a_actualizar['creditos'];
                $resultData['creditos_ant'] = $credito_a_actualizar['creditos_ant'];

                $this->actualizarCreditosJugador($credito_a_actualizar);
            }
            $resultData['notificado'] = 1;
        } else {
            $resultData['notificado'] = 0;
        }
        return $resultData;
    }
}

?>
