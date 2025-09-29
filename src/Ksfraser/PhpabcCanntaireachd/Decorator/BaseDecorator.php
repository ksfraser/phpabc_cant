<?php
namespace Ksfraser\PhpabcCanntaireachd\Decorator;
/**
 * Base class for all ABC decorators.
 * Provides generic getLatinName() and getShortcuts() static methods.
 */
abstract class BaseDecorator {
    protected static $latinName = '';
    protected static $shortcuts = [];

    public static function getLatinName() {
        return static::$latinName;
    }
    public static function getShortcuts() {
        return static::$shortcuts;
    }
    public function render() {
        return '!' . strtolower(static::$latinName) . '!';
    }
}
