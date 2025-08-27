<?php

/**
 * Clase Database - Gestión de Conexión a Base de Datos
 * 
 * Implementa el patrón Singleton para asegurar una única conexión
 * Utiliza PDO para máxima seguridad y compatibilidad
 */

require_once __DIR__ . '/../../config/constants.php';

class Database
{
    private static $instance = null;
    private $connection;
    private $host;
    private $dbName;
    private $username;
    private $password;
    private $charset;

    /**
     * Constructor privado para implementar Singleton
     */
    private function __construct()
    {
        $this->host = DB_HOST;
        $this->dbName = DB_NAME;
        $this->username = DB_USER;
        $this->password = DB_PASS;
        $this->charset = DB_CHARSET;

        $this->connect();
    }

    /**
     * Obtiene la instancia única de Database
     * 
     * @return Database
     */
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    /**
     * Establece la conexión a la base de datos
     * 
     * @throws PDOException
     */
    private function connect(): void
    {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->dbName};charset={$this->charset}";

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$this->charset}"
            ];

            $this->connection = new PDO($dsn, $this->username, $this->password, $options);

            // Configurar timezone
            $this->connection->exec("SET time_zone = '+00:00'");

            if (APP_DEBUG) {
                error_log("✅ Conexión a base de datos establecida correctamente");
            }
        } catch (PDOException $e) {
            error_log("❌ Error conectando a base de datos: " . $e->getMessage());
            throw new PDOException("Error de conexión a base de datos: " . $e->getMessage());
        }
    }

    /**
     * Obtiene la conexión PDO
     * 
     * @return PDO
     */
    public function getConnection(): PDO
    {
        // Verificar si la conexión sigue activa
        if ($this->connection === null) {
            $this->connect();
        }

        return $this->connection;
    }

    /**
     * Ejecuta una consulta preparada de forma segura
     * 
     * @param string $sql
     * @param array $params
     * @return PDOStatement
     */
    public function query(string $sql, array $params = []): PDOStatement
    {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("❌ Error en consulta SQL: " . $e->getMessage());
            error_log("📄 SQL: " . $sql);
            error_log("📊 Parámetros: " . json_encode($params));
            throw $e;
        }
    }

    /**
     * Obtiene el último ID insertado
     * 
     * @return string
     */
    public function lastInsertId(): string
    {
        return $this->connection->lastInsertId();
    }

    /**
     * Inicia una transacción
     * 
     * @return bool
     */
    public function beginTransaction(): bool
    {
        return $this->connection->beginTransaction();
    }

    /**
     * Confirma una transacción
     * 
     * @return bool
     */
    public function commit(): bool
    {
        return $this->connection->commit();
    }

    /**
     * Revierte una transacción
     * 
     * @return bool
     */
    public function rollback(): bool
    {
        return $this->connection->rollback();
    }

    /**
     * Verifica si hay una transacción activa
     * 
     * @return bool
     */
    public function inTransaction(): bool
    {
        return $this->connection->inTransaction();
    }

    /**
     * Previene la clonación del objeto
     */
    private function __clone() {}

    /**
     * Previene la deserialización del objeto
     */
    public function __wakeup()
    {
        throw new Exception("No se puede deserializar el singleton Database");
    }
}
