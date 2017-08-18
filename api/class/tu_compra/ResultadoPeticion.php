<?php

/**
 * Created by PhpStorm.
 * User: Robin
 * Date: 13/03/2017
 * Time: 6:11 PM
 */
class resultadoPeticion
{
    public $codigoFactura, $valorFactura, $transaccionAprobada, $codigoAutorizacion, $firmaTuCompra;
    public $campoExtra1, $campoExtra2, $campoExtra3, $numeroTransaccion, $metodoPago, $nombreMetodopago, $banco;
    public $valorBase, $valorIva, $valorReteiva, $valorReteica, $valorRetefuente, $descripcion, $descripcion2, $detalle, $fechaPago;
    public $numeroTarjeta, $numeroCuotas, $correoComprador, $nombreComprador, $apellidoComprador, $documentoComprador, $telefonoComprador;
    public $direccionComprador, $ipComprador, $ciudadComprador, $paisComprador, $estadoPago, $razonRechazo, $tipotarjeta, $categoriatarjeta;
    public $paisemisor, $telefonobancoemisor, $valorComisionbancaria, $valorDepositoBanco, $bancoRecaudador, $horaPago;
    public $campoExtra4, $campoExtra5, $campoExtra6, $campoExtra7, $campoExtra8, $campoExtra9;
    public $tipoCorte, $caja, $formaPago, $oficina, $cuentaBanco, $jornada, $tipoRegistro, $operador;
    public $tipoTransaccion, $descripcionTipoTransaccion, $fechaSaldoAplicado, $codBancoRecaudador, $esRecurrencia, $respuesta;

    function __construct()
    {
    }


}