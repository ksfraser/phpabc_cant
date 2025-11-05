<?php
namespace Ksfraser\PhpabcCanntaireachd\Formatting;

use Ksfraser\PhpabcCanntaireachd\AbcFormattingLine;
use Ksfraser\PhpabcCanntaireachd\AbcLineParser;

/**
 * Parser for %%scale directive
 * Extends FormattingDirectiveParser for common formatting functionality.
 *
 * @requirement FR10 (formatting directive parsing)
 */
class ScaleParser extends FormattingDirectiveParser implements AbcLineParser
{
    /**
     * @param mixed $line
     * @return bool
     */
    public function canParse($line) {
        return $this->isFormattingDirective($line) && $this->getDirectiveType($line) === 'scale';
    }

    /**
     * @param mixed $line
     * @param mixed $tune
     * @return bool
     */
    public function parse($line, $tune) {
        if (!preg_match('/^%%\s*scale\s+(.+)$/i', trim($line), $matches)) {
            return false;
        }

        $value = trim($matches[1]);

        // Validate scale value (should be a number, typically 0.5 to 2.0)
        if (!$this->isValidScale($value)) {
            $tune->add(new AbcFormattingLine("%% Invalid scale value: $value (should be between 0.1 and 5.0)"));
            return true;
        }

        // Create standardized formatting line
        $correctedLine = "%% scale $value";
        $tune->add(new AbcFormattingLine($correctedLine));
        return true;
    }

    /**
     * @param mixed $line
     * @return bool
     */
    public function validate($line) {
        if (!preg_match('/^%%\s*scale\s+(.+)$/i', trim($line), $matches)) {
            return false;
        }

        $value = trim($matches[1]);
        return $this->isValidScale($value);
    }

    /**
     * Validates if a string is a valid scale value.
     *
     * @param string $value The scale value to validate.
     * @return bool True if valid.
     */
    private function isValidScale(string $value): bool {
        if (!is_numeric($value)) {
            return false;
        }

        $scale = (float)$value;
        return $scale >= 0.1 && $scale <= 5.0;
    }
}