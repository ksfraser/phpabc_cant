<?php
namespace Ksfraser\Database;

/**
 * Central DB manager for loading config, secrets, and providing PDO connection.
 * Designed for reuse across projects.
 */
class DbManager
{
    /** @var array */
    protected static $config = null;
    /** @var \PDO|null */
    protected static $pdo = null;

    /**
     * Load DB config from file, environment, or Symfony secrets.
     * @param string|null $configFile
     * @return array
     */
    public static function getConfig($configFile = null)
    {
        if (self::$config !== null) return self::$config;
        $config = [];
        // Try Symfony secrets if available
        if (class_exists('Symfony\\Component\\Runtime\\Secrets\\getSecret')) {
            $getSecret = \Closure::fromCallable(['Symfony\\Component\\Runtime\\Secrets', 'getSecret']);
            $env = getenv();
            $config = [
                'mysql_user' => $env['MYSQL_USER'] ?? $getSecret('MYSQL_USER') ?? null,
                'mysql_pass' => $env['MYSQL_PASS'] ?? $getSecret('MYSQL_PASS') ?? null,
                'mysql_db'   => $env['MYSQL_DB']   ?? $getSecret('MYSQL_DB')   ?? null,
                'mysql_host' => $env['MYSQL_HOST'] ?? $getSecret('MYSQL_HOST') ?? 'localhost',
                'mysql_port' => $env['MYSQL_PORT'] ?? $getSecret('MYSQL_PORT') ?? 3306,
            ];
        }
        // Fallback to config file
        if ($configFile === null) {
            $configFile = __DIR__ . '/../../../config/db_config.php';
        }
        if (file_exists($configFile)) {
            $fallback = include($configFile);
            foreach ($fallback as $k => $v) {
                if (!isset($config[$k]) || $config[$k] === null) $config[$k] = $v;
            }
        }
        // Build DSN if missing
        if (!isset($config['dsn'])) {
            $config['dsn'] = "mysql:host={$config['mysql_host']};port={$config['mysql_port']};dbname={$config['mysql_db']};charset=utf8mb4";
        }
        self::$config = $config;
        return $config;
    }

    /**
     * Get a singleton PDO connection
     * @return \PDO
     */
    public static function getPdo($configFile = null)
    {
        if (self::$pdo !== null) return self::$pdo;
        $config = self::getConfig($configFile);
        self::$pdo = new \PDO($config['dsn'], $config['mysql_user'], $config['mysql_pass']);
        return self::$pdo;
    }

    /**
     * Helper for running a query and returning results
     * @param string $sql
     * @param array $params
     * @return array
     */
    public static function fetchAll($sql, $params = [])
    {
        $pdo = self::getPdo();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Helper for running a query and returning a single row
     * @param string $sql
     * @param array $params
     * @return array|false
     */
    public static function fetchOne($sql, $params = [])
    {
        $pdo = self::getPdo();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Helper for running a query and returning a single value
     * @param string $sql
     * @param array $params
     * @return mixed
     */
    public static function fetchValue($sql, $params = [])
    {
        $pdo = self::getPdo();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    /**
     * Helper for executing a statement (insert/update/delete)
     * @param string $sql
     * @param array $params
     * @return int Rows affected
     */
    public static function execute($sql, $params = [])
    {
        $pdo = self::getPdo();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }
}
