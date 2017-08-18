<?php

date_default_timezone_set('America/Bogota');


class PasarelaDePagos
{
    //Estas url son las que se cargan de la base de datos de la tabla configuracion_tu_compra
    protected $valor;
    protected $url_tuCompra;
    protected $terminalKey;
    protected $usuario;
    protected $cliente;
    protected $password;

    public function consumo($value)
    {

        require_once '../pasarela_de_pagos/TuCompraServicio.php';
        require_once '../requires/funciones.php';

        $this->valor = puntos($value);

        //Instanciacion de la clase que contiene los metodos de redireccionamiento al servicio
        $servicioClase = new TuCompraServicio();

        $configuracion_tu_compra = $servicioClase->obtenerConfiguracionTuCompra();

        foreach ($configuracion_tu_compra as $config) {
            $this->url_tuCompra = $config['url_base_compra'];
            $this->terminalKey = $config['llave_terminal'];
            $this->usuario = $config['usuario'];
            $this->cliente = $config['cliente'];
            $this->password = $config['password'];
        }

        //Clase que contiene todos los atributos necesarios que solicita tuCompra
        $informacionPeticion = new InformacionPeticion();

        //Definicion de cada una de las propiedades necesarias obligatorias
        $informacionPeticion->usuario = $this->cliente;
        $informacionPeticion->factura = rand(1, 1000000);
        $informacionPeticion->valor = $this->valor;
        $informacionPeticion->usuario_conectado_id = $_POST['usuario_conectado_id'];
        $informacionPeticion->descripcionFactura = "Deposito para compra de crédito " . $_POST['documento'];
        $informacionPeticion->terminal = $this->terminalKey;

        //Definicion de las propiedades Opcionales (En caso de no enviarse estas el usuario las debera digitar manualmente )
        $informacionPeticion->documentoComprador = $_POST['documento'];
        $informacionPeticion->nombreComprador = $_POST['nombre'];
        $informacionPeticion->apellidoComprador = $_POST['apellido'];
        $informacionPeticion->telefonoComprador = $_POST['telefono'];
        $informacionPeticion->celularComprador = $_POST['movil'];
        $informacionPeticion->paisComprador = $_POST['pais'];
        $informacionPeticion->ciudadComprador = $_POST['ciudad'];
        $informacionPeticion->direccionComprador = $_POST['direccion'];
        $informacionPeticion->correoComprador = $_POST['mail'];
        //$informacionPeticion->metodoPago = 41;

        //Definicion del token de seguridad: Este siempre debera ser: llave de la terminal + hora en formato HH:mm con la hora de bogota/Colombia
        $informacionPeticion->tokenSeguridad = md5($this->terminalKey + date('H:i', time()));

        //Llamado al metodo que redireccionara a tu compra
        $servicioClase->solicitudTransaccion($informacionPeticion, $this->url_tuCompra);
    }
}