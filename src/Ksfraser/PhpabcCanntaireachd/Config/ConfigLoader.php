<?php
namespace Ksfraser\PhpabcCanntaireachd\Config;

/**
 * Configuration file loader
 * Supports JSON, YAML, INI, and PHP array formats
 */
class ConfigLoader
{
    /**
     * Load configuration from a file
     * Format is auto-detected from file extension
     * 
     * @param string $filePath Path to configuration file
     * @return array Configuration array
     * @throws \InvalidArgumentException If file doesn't exist or format unsupported
     * @throws \RuntimeException If file cannot be parsed
     */
    public static function loadFromFile(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new \InvalidArgumentException("Configuration file not found: $filePath");
        }

        if (!is_readable($filePath)) {
            throw new \InvalidArgumentException("Configuration file not readable: $filePath");
        }

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        switch ($extension) {
            case 'json':
                return self::loadJSON($filePath);
            
            case 'yml':
            case 'yaml':
                return self::loadYAML($filePath);
            
            case 'ini':
                return self::loadINI($filePath);
            
            case 'php':
                return self::loadPHP($filePath);
            
            default:
                throw new \InvalidArgumentException(
                    "Unsupported configuration file format: .$extension (supported: json, yml, yaml, ini, php)"
                );
        }
    }

    /**
     * Load JSON configuration file
     * 
     * @param string $filePath Path to JSON file
     * @return array Configuration array
     * @throws \RuntimeException If JSON is invalid
     */
    private static function loadJSON(string $filePath): array
    {
        $content = file_get_contents($filePath);
        if ($content === false) {
            throw new \RuntimeException("Failed to read file: $filePath");
        }

        $config = json_decode($content, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException(
                "Invalid JSON in $filePath: " . json_last_error_msg()
            );
        }

        // Remove comment keys (JSON doesn't support comments, so we use _comment keys)
        return self::removeCommentKeys($config);
    }

    /**
     * Load YAML configuration file
     * 
     * @param string $filePath Path to YAML file
     * @return array Configuration array
     * @throws \RuntimeException If YAML is invalid or extension not available
     */
    private static function loadYAML(string $filePath): array
    {
        // Check if yaml extension is available
        if (!function_exists('yaml_parse_file')) {
            // Fallback: try symfony/yaml if available
            if (class_exists('\Symfony\Component\Yaml\Yaml')) {
                return \Symfony\Component\Yaml\Yaml::parseFile($filePath);
            }
            
            throw new \RuntimeException(
                "YAML support not available. Install yaml PHP extension or symfony/yaml package."
            );
        }

        $config = yaml_parse_file($filePath);
        
        if ($config === false) {
            throw new \RuntimeException("Failed to parse YAML file: $filePath");
        }

        return $config;
    }

    /**
     * Load INI configuration file
     * 
     * @param string $filePath Path to INI file
     * @return array Configuration array
     * @throws \RuntimeException If INI is invalid
     */
    private static function loadINI(string $filePath): array
    {
        $config = parse_ini_file($filePath, true, INI_SCANNER_TYPED);
        
        if ($config === false) {
            throw new \RuntimeException("Failed to parse INI file: $filePath");
        }

        return $config;
    }

    /**
     * Load PHP configuration file (returns array)
     * 
     * @param string $filePath Path to PHP file
     * @return array Configuration array
     * @throws \RuntimeException If PHP file doesn't return an array
     */
    private static function loadPHP(string $filePath): array
    {
        $config = require $filePath;
        
        if (!is_array($config)) {
            throw new \RuntimeException(
                "PHP configuration file must return an array: $filePath"
            );
        }

        return $config;
    }

    /**
     * Remove comment keys from configuration array (recursive)
     * JSON doesn't support comments, so we use keys starting with _comment
     * 
     * @param array $config Configuration array
     * @return array Configuration without comment keys
     */
    private static function removeCommentKeys(array $config): array
    {
        foreach ($config as $key => $value) {
            if (is_string($key) && (strpos($key, '_comment') === 0 || strpos($key, '_description') === 0)) {
                unset($config[$key]);
            } elseif (is_array($value)) {
                $config[$key] = self::removeCommentKeys($value);
            }
        }
        
        return $config;
    }

    /**
     * Try to load configuration from multiple paths
     * Returns first file that exists and can be loaded
     * 
     * @param array $paths Array of file paths to try
     * @return array|null Configuration array or null if no files found
     */
    public static function loadFirstAvailable(array $paths): ?array
    {
        foreach ($paths as $path) {
            if (file_exists($path)) {
                try {
                    return self::loadFromFile($path);
                } catch (\Exception $e) {
                    // Log error but continue trying other files
                    error_log("Failed to load config from $path: " . $e->getMessage());
                    continue;
                }
            }
        }
        
        return null;
    }
}
