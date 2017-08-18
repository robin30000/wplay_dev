<?php

class Conection extends PDO
{

    private $tipo_de_base = 'mysql';
    private $host = 'mysql.local';
    private $nombre_de_base = 'wplay_prd';
    private $usuario = 'wplay_user';
    private $contrasena = 'btz*346Asyu11';

    private $opciones = array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8', PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    public function __construct()
    {
        try {
            parent::__construct($this->tipo_de_base . ':host=' . $this->host . ';dbname=' . $this->nombre_de_base, $this->usuario, $this->contrasena, $this->opciones);
        } catch (PDOException $e) {
            echo 'Ha surgido un error y no se puede conectar a la base de datos. Detalle: ' . $e->getMessage();
            exit;
        }
    }

}
