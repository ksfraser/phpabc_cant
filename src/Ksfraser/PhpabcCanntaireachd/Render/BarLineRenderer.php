<?php
namespace Ksfraser\PhpabcCanntaireachd\Render;

/**
 * Base class for rendering bar lines in ABC notation.
 */
class BarLineRenderer {
    /**
     * Bar line type (e.g. '|', '||', '|:', ':|', '[:', ':]')
     * @var string
     */
    protected $barLineType;

    /**
     * @param string $barLineType
     */
    public function __construct(string $barLineType = '|') {
        $this->barLineType = $barLineType;
    }

    /**
     * Render the bar line as a string.
     * @return string
     */
    public function render(): string {
        return $this->barLineType;
    }

    /**
     * Get all supported barline strings for ABC spec by detecting subclasses.
     * @return array
     */
    public static function getSupportedBarLines() {
        static $cached = null;
        if ($cached !== null) {
            return $cached;
        }
        $supported = [];
        foreach (get_declared_classes() as $class) {
            if (is_subclass_of($class, __CLASS__) && strpos($class, __NAMESPACE__ . '\\') === 0) {
                $obj = new $class();
                $barLine = $obj->render();
                $supported[$barLine] = true;
            }
        }
        $cached = array_keys($supported);
        return $cached;
    }
}
