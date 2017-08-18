<?php


class ConectorBD
{
    private $host = 'localhost';//67.225.146.17';
    private $user = 'root';
    private $password = '';
    private $nombre_db = 'wplay';
    private $conexion;

    function initConexion()
    {
        $this->conexion = new mysqli($this->host, $this->user, $this->password, $this->nombre_db);
        if ($this->conexion->connect_error) {
            return "Error:" . $this->conexion->connect_error;
        } else {
            return "OK";
        }
    }

    function ejecutarQuery($query)
    {
        return $this->conexion->query($query);
    }

    function cerrarConexion()
    {
        $this->conexion->close();
    }

    function getConexion()
    {
        return $this->conexion;
    }

    function insertData($tabla, $data)
    {
        $sql = 'INSERT INTO ' . $tabla . ' (';
        $i = 1;
        foreach ($data as $key => $value) {
            $sql .= $key;
            if ($i < count($data)) {
                $sql .= ', ';
            } else $sql .= ')';
            $i++;
        }
        $sql .= ' VALUES (';
        $i = 1;
        $campos = 3;
        if($tabla == 'usuario_solicitud_transaccion'){
          $campos = 2;
        }
        foreach ($data as $key => $value) {
            if ($i < $campos) {
                $sql .= $value;
            } else {
                $sql .= "'" . $value . "'";
            }
            if ($i < count($data)) {
                $sql .= ', ';
            } else $sql .= ');';
            $i++;
        }
        //return $sql;
        return $this->ejecutarQuery($sql);
    }

    function actualizarRegistro($tabla, $data, $condicion)
    {
        $sql = 'UPDATE ' . $tabla . ' SET ';
        $i = 1;
        foreach ($data as $key => $value) {
            if ($i < 3) {
                $sql .= $key . '=' . $value;
            } else {
                $sql .= $key . "='" . $value . "'";
            }
            if ($i < sizeof($data)) {
                $sql .= ', ';
            } else $sql .= ' WHERE ' . $condicion . ';';
            $i++;
        }
        return $this->ejecutarQuery($sql);
    }

    function consultar($sql)
    {
        return $this->ejecutarQuery($sql);
    }
}

?>
