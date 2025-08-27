<?php
namespace Ksfraser\PhpabcCanntaireachd;

/**
 * Trait ErrorFormatterTrait
 * Provides utility for formatting error messages consistently.
 */
trait ErrorFormatterTrait {
    /**
     * Format an error message for ABC validation.
     * @param int $tuneIndex
     * @param string|null $tuneX
     * @param int|null $lineNum
     * @param string $message
     * @return string
     */
    public static function formatError($tuneIndex, $tuneX, $lineNum, $message) {
        $xStr = ($tuneX !== null) ? $tuneX : '?';
        $lineStr = ($lineNum !== null) ? " line $lineNum" : "";
        return "Tune " . ($tuneIndex+1) . " (X:$xStr)" . $lineStr . ": $message";
    }
}
