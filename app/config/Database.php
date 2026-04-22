<?php

class Database {
    private $db_path;
    private $conn;
    private $lastError;

    public function __construct() {
        $this->db_path = __DIR__ . '/../../storage/cafeteria.sqlite';
    }

    public function connect() {
        $this->conn = null;
        $this->lastError = null;

        try {
            if (!extension_loaded('pdo_sqlite')) {
                $this->lastError = 'Missing PHP extension: pdo_sqlite. Enable it in php.ini and restart PHP.';
                return null;
            }

            $storageDir = dirname($this->db_path);
            if (!is_dir($storageDir)) {
                mkdir($storageDir, 0777, true);
            }

            $isFirstRun = !file_exists($this->db_path);

            $this->conn = new PDO('sqlite:' . $this->db_path);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->conn->exec('PRAGMA foreign_keys = ON');

            if ($isFirstRun) {
                $this->initializeSchema();
            }
        } catch (PDOException $e) {
            $this->lastError = 'Connection Error: ' . $e->getMessage();
        }

        return $this->conn;
    }

    public function getLastError() {
        return $this->lastError;
    }

    private function initializeSchema() {
        $schemaPath = __DIR__ . '/../../database.sql';
        if (!file_exists($schemaPath)) {
            return;
        }

        $sql = file_get_contents($schemaPath);
        if ($sql !== false) {
            $this->conn->exec($sql);
        }
    }
}
