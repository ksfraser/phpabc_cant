<?php
namespace Ksfraser\PhpabcCanntaireachd;
/**
 * Loader for all ABC note element classes (decorators, accidentals, lengths, octaves, etc).
 * Builds shortcut lists, compares for ambiguity, and provides gotchas list for parser.
 */
class NoteElementLoader {
    protected static $elementMaps = null;
    protected static $gotchas = null;

    /**
     * Load all note element classes and build shortcut maps for each type.
     * @return array [type => [shortcut => className, ...], ...]
     */
    public static function getElementMaps() {
        if (self::$elementMaps !== null) return self::$elementMaps;
        $maps = [];
        // Decorators
        $maps['decorator'] = \Ksfraser\PhpabcCanntaireachd\Decorator\DecoratorLoader::getDecoratorMap();

        // Dynamically load note element classes
        $dir = __DIR__ . '/NoteElement';
        $elementClasses = [];
        foreach (glob($dir . '/*.php') as $file) {
            $class = __NAMESPACE__ . '\\NoteElement\\' . basename($file, '.php');
            if (!class_exists($class)) {
                require_once $file;
            }
            if (method_exists($class, 'getShortcut') && method_exists($class, 'getType')) {
                $shortcut = $class::getShortcut();
                $type = $class::getType();
                $elementClasses[] = [ 'shortcut' => $shortcut, 'type' => $type, 'class' => $class ];
            }
        }
        // Sort by shortcut length descending for ambiguity resolution
        usort($elementClasses, function($a, $b) {
            return strlen($b['shortcut']) - strlen($a['shortcut']);
        });
        foreach ($elementClasses as $elem) {
            if (!isset($maps[$elem['type']])) $maps[$elem['type']] = [];
            $maps[$elem['type']][$elem['shortcut']] = $elem['class'];
        }
        self::$elementMaps = $maps;
        return $maps;
    }

    /**
     * Compare all shortcut lists and build a gotchas list of ambiguous shortcuts.
     * @return array [shortcut => [types...]]
     */
    public static function getGotchas() {
        if (self::$gotchas !== null) return self::$gotchas;
        $maps = self::getElementMaps();
        $shortcutTypes = [];
        foreach ($maps as $type => $map) {
            foreach ($map as $shortcut => $classOrDesc) {
                $shortcutTypes[$shortcut][] = $type;
            }
        }
        $gotchas = [];
        foreach ($shortcutTypes as $shortcut => $types) {
            if (count($types) > 1) {
                $gotchas[$shortcut] = $types;
            }
        }
        self::$gotchas = $gotchas;
        return $gotchas;
    }
}
