<?php
// Inclusion du fichier de configuration contenant les constantes de connexion
require_once('./config/config.php');

class Database
{
    private static $instance = null; // Instance unique de la base de données (Singleton)
    private $host;
    private $port;
    private $dbname;
    private $user;
    private $password;
    private $pdo;

    // Constructeur initialisant les paramètres de connexion et la base de données
    public function __construct()
    {
        $this->host = DB_HOST;
        $this->port = DB_PORT;
        $this->dbname = DB_NAME;
        $this->user = DB_USER;
        $this->password = DB_PASSWORD;
        $this->initDatabase();
    }

    // Méthode statique pour récupérer l'instance unique de la classe (Singleton)
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // Initialise la base de données si elle n'existe pas
    private function initDatabase(): void
    {
        $dsn = "pgsql:host=" . $this->host . ";port=" . $this->port;

        try {
            // Connexion temporaire pour vérifier l'existence de la base de données
            $tempPDO = new PDO($dsn, $this->user, $this->password);
            $tempPDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Vérifie si la base de données existe
            $stmt = $tempPDO->prepare("SELECT 1 FROM pg_database WHERE datname = :dbname");
            $stmt->execute(['dbname' => $this->dbname]);
            $exists = $stmt->fetchColumn();

            // Si la base de données n'existe pas, la créer
            if (!$exists) {
                $tempPDO->exec("CREATE DATABASE " . $this->dbname);
            }

            // Fermer la connexion temporaire
            $tempPDO = null;

            // Se connecter à la base de données principale
            $this->connect();
        } catch (PDOException $e) {
            throw new Exception($e->getMessage());
        }
    }

    // Établit une connexion à la base de données
    private function connect(): void
    {
        $dsn = "pgsql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->dbname;
        try {
            $this->pdo = new PDO($dsn, $this->user, $this->password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            throw new Exception($e->getMessage());
        }
    }

    // Prépare et exécute une requête SQL avec des paramètres
    public function prepareExecute(string $sql, array $params = []): ?PDOStatement
    {
        if ($this->pdo === null) {
            $this->connect();
        }

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            throw new Exception($e->getMessage());
        }
    }

    // Récupère plusieurs lignes d'une requête SQL sous forme de tableau associatif
    public function fetchAll(string $sql, array $params = []): array
    {
        $stmt = $this->prepareExecute($sql, $params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Récupère une seule ligne d'une requête SQL sous forme de tableau associatif
    public function fetch(string $sql, array $params = [])
    {
        $stmt = $this->prepareExecute($sql, $params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    // Récupère une seule valeur d'une requête SQL
    public function fetchCol(string $sql, array $params = [])
    {
        $stmt = $this->prepareExecute($sql, $params);
        return $stmt->fetchColumn();
    }

    // Exécute une requête SQL et retourne le nombre de lignes affectées
    public function execute(string $sql, array $params = []): int
    {
        $stmt = $this->prepareExecute($sql, $params);
        return $stmt->rowCount();
    }

    // Récupère l'ID du dernier enregistrement inséré
    public function lastInsertId(): int
    {
        if ($this->pdo === null) {
            $this->connect();
        }
        return intval($this->pdo->lastInsertId());
    }
}
