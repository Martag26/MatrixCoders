<?php

/**
 * Clase de conexión a la base de datos.
 *
 * Gestiona la creación de la conexión PDO con el servidor MySQL
 * utilizado por la aplicación MatrixCoders.
 */
class Database
{
    /**
     * Crea y devuelve una conexión PDO a la base de datos.
     *
     * Configura el modo de errores para que PDO lance excepciones
     * ante cualquier fallo, facilitando la detección de errores.
     * Si la conexión falla, detiene la ejecución mostrando el mensaje de error.
     *
     * @return PDO Instancia de la conexión activa a la base de datos.
     */
    public function connect()
    {
        try {
            // Crear la conexión PDO al servidor MySQL local con la base de datos del proyecto
            $conexion = new PDO(
                "mysql:host=localhost;dbname=matrixcoders_bd", "root", ""
            );

            // Configurar PDO para que lance excepciones ante errores de SQL
            $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            return $conexion;

        } catch (PDOException $e) {
            // Detener la ejecución y mostrar el mensaje de error si la conexión falla
            die("Error de conexión: " . $e->getMessage());
        }
    }
}
