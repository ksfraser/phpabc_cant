<?php
namespace Ksfraser\PhpabcCanntaireachd\Decorator;
/**
 * Dynamically loads all Decorator classes in the Decorator directory.
 * Each class must implement getLatinName() and getShortcuts() static methods.
 * Returns an array of [shortcut => className, ...] for parser use.
 */
class DecoratorLoader {
    /**
     * Generate a regex pattern matching all decorator shortcuts and latin names.
     * @return string Regex pattern (e.g., /^(shortcut1|shortcut2|...)/)
     */
    public static function getRegex() {
        $map = self::getDecoratorMap();
        if (empty($map)) return '/^$/';
        $keys = array_filter(array_keys($map), function($s) { return $s !== ''; });
        $escaped = array_map(function($s) {
            return preg_quote($s, '/');
        }, $keys);
        $pattern = '/^(' . implode('|', $escaped) . ')/';
        return $pattern;
    }
    protected static $decoratorMap = null;

    /**
     * Load all decorator classes and build shortcut map (with caching).
     * @return array [shortcut => className, ...]
     */
    public static function getDecoratorMap() {
        if (self::$decoratorMap !== null) return self::$decoratorMap;
        $map = [];
        $dir = __DIR__;
        foreach (glob($dir . '/*Decorator.php') as $file) {
            $class = __NAMESPACE__ . '\\' . basename($file, '.php');
            if (!class_exists($class)) {
                require_once $file;
            }
            if (method_exists($class, 'getLatinName') && method_exists($class, 'getShortcuts')) {
                $latin = $class::getLatinName();
                $shortcuts = $class::getShortcuts();
                foreach ($shortcuts as $shortcut) {
                    $map[$shortcut] = $class;
                }
                // Optionally map latin name too
                $map[$latin] = $class;
            }
        }
        self::$decoratorMap = $map;
        return $map;
    }
}
