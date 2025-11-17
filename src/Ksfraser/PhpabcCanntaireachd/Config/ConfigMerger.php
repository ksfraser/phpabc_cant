<?php
namespace Ksfraser\PhpabcCanntaireachd\Config;

/**
 * Configuration merger
 * Merges multiple configuration arrays with precedence rules
 */
class ConfigMerger
{
    /**
     * Merge configurations with precedence
     * Later configurations override earlier ones
     * 
     * @param array $configs Array of configuration arrays (lowest to highest precedence)
     * @return array Merged configuration
     */
    public static function merge(array ...$configs): array
    {
        $result = [];
        
        foreach ($configs as $config) {
            $result = self::mergeRecursive($result, $config);
        }
        
        return $result;
    }

    /**
     * Recursively merge two configuration arrays
     * 
     * @param array $base Base configuration (lower precedence)
     * @param array $override Override configuration (higher precedence)
     * @return array Merged configuration
     */
    private static function mergeRecursive(array $base, array $override): array
    {
        foreach ($override as $key => $value) {
            if (is_array($value) && isset($base[$key]) && is_array($base[$key])) {
                // Both are arrays - merge recursively
                $base[$key] = self::mergeRecursive($base[$key], $value);
            } else {
                // Override the value
                $base[$key] = $value;
            }
        }
        
        return $base;
    }

    /**
     * Filter null values from configuration
     * Used to clean up configurations before merging
     * 
     * @param array $config Configuration array
     * @return array Configuration without null values
     */
    public static function filterNulls(array $config): array
    {
        foreach ($config as $key => $value) {
            if (is_null($value)) {
                unset($config[$key]);
            } elseif (is_array($value)) {
                $config[$key] = self::filterNulls($value);
                // Remove empty arrays
                if (empty($config[$key])) {
                    unset($config[$key]);
                }
            }
        }
        
        return $config;
    }
}
