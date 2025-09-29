<?php
namespace Ksfraser\PhpabcCanntaireachd\Decorator;
/**
 * Dynamically loads all Decorator classes in the Decorator directory.
 * Each class must implement getLatinName() and getShortcuts() static methods.
 * Returns an array of [shortcut => className, ...] for parser use.
 */
class DecoratorLoader {
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
