<?php

class Database
{

    public function connect()
    {

        try {
            $conexion = new PDO(
                "mysql:host=localhost;dbname=matrixcoders_bd", "root",""
            );

            $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            return $conexion;
        } catch (PDOException $e) {
            die("Error de conexión: " . $e->getMessage());
        }
    }
}
