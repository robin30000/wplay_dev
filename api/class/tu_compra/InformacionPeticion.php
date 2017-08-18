<?php
	/*
	* Clase que contiene todos los atributos que se deben enviar en POST tanto obligatorios como opcionales
	* Author: Selecta Consulting group
	* Fecha Creación: Octubre 28 de 2016
	* Ultima Modificacion: Noviembre 25 de 2016
	*/
	class InformacionPeticion
	{

		//Variables obligatorias para las peticiones de pago, deben enviarse siempre
		public $usuario, $factura, $valor, $descripcionFactura, $tokenSeguridad, $terminal, $usuario_conectado_id;

		//Variables opcionales dependiendo de la peticion que se desee realizar
		public $tipoMoneda, $lenguaje, $recurrencia, $periodo, $metodoPago, $valorBase, $valorIva, $tokenTarjeta;

		//Variables obligatorias del USUARIO ( si no son enviadas el sistemas las preguntara)
		public $documentoComprador, $tipoDocumento, $nombreComprador, $apellidoComprador, $correoComprador, $telefonoComprador;
		public $celularComprador, $direccionComprador, $ciudadComprador, $paisComprador, $twitterComprador;
		public $campoExtra1,$campoExtra2,$campoExtra3,$campoExtra4,$campoExtra5,$campoExtra6,$campoExtra7,$campoExtra8,$campoExtra9;

		//Variables booleanas //Estas se usan para consultar estados de transacciones y que devuelva los campos de acuerdo a la necesidad
		public $idfactura, $metodoDePago, $banco, $valorPagado, $valorTotal, $isValorBase,$isValorIva,$valorReteiva,$valorReteica,$valorRetefuente;
		public $descripcion,$descripcion2,$detalle,$fechaPago,$fechaPagopse,$horaPago,$transaccionConfirmada,$codigoAutorizacion,$numeroCoutas;
		public $isCorreoComprador,$isNombreComprador,$isApellidosComprador,$isDocumentoComprador,$isTelefonoComprador,$isDireccionComprador,$ipComprador;
		public $isCiudadComprador,$isPaisComprador,$estadoPago,$razonRechazo,$numeroTransaccion,$paisemisor,$valorComisionbancaria,$valorDepositoBanco;
		public $bancoRecaudador,$codigoTransaccion,$isCampoextra1,$isCampoextra9,$descripciontransaccion;

		function __construct()
		{

		}
	}

?>
