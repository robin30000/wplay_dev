<?php

require_once 'conexion.php';

class Documentos
{

    private $_DB;

    public function __construct()
    {
        $this->_DB = new Conection;
    }

    public function buscar($id)
    {

        $stmt = $this->_DB->prepare('SELECT * FROM documentos WHERE nombre = :id');
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($result);

    }

    public function preguntas()
    {

        $stmt = $this->_DB->prepare('SELECT * FROM preguntas ');
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($result);
    }

}
