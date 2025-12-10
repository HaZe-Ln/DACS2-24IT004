<?php
class PDODatabase
{
    private static $instance = null;
    private $pdo;

    private function __construct()
    {
        $host = getenv("DB_HOST") ?? 'localhost';
        $port = getenv("DB_PORT") ?? '3306'; 
        $db   = getenv('DB_DATABASE') ?? 'test';
        $user = getenv('DB_USER') ?? 'root';
        $pass = getenv('DB_PASSWORD') ?? '';
        $charset = "utf8mb4";

        $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        $this->pdo = new PDO($dsn, $user, $pass, $options);

        $this->pdo->exec("SET time_zone = '+07:00';");
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new PDODatabase();
        }
        return self::$instance;
    }

    public function getConnection()
    {
        return $this->pdo;
    }
}
