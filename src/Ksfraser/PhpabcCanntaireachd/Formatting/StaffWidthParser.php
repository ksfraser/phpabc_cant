<?php
namespace Ksfraser\PhpabcCanntaireachd\Formatting;

use Ksfraser\PhpabcCanntaireachd\AbcFormattingLine;
use Ksfraser\PhpabcCanntaireachd\AbcLineParser;

/**
 * Parser for %%staffwidth directive
 * Extends FormattingDirectiveParser for common formatting functionality.
 *
 * @requirement FR10 (formatting directive parsing)
 */
class StaffWidthParser extends FormattingDirectiveParser implements AbcLineParser
{
    /**
     * @param mixed $line
     * @return bool
     */
    public function canParse($line) {
        return $this->isFormattingDirective($line) && $this->getDirectiveType($line) === 'staffwidth';
    }

    /**
     * @param mixed $line
     * @param mixed $tune
     * @return bool
     */
    public function parse($line, $tune) {
        if (!preg_match('/^%%\s*staffwidth\s+(.+)$/i', trim($line), $matches)) {
            return false;
        }

        $value = trim($matches[1]);

        // Validate staffwidth value (should be a valid dimension)
        if (!$this->isValidDimension($value)) {
            $tune->add(new AbcFormattingLine("%% Invalid staffwidth value: $value"));
            return true;
        }

        // Create standardized formatting line
        $correctedLine = "%% staffwidth $value";
        $tune->add(new AbcFormattingLine($correctedLine));
        return true;
    }

    /**
     * @param mixed $line
     * @return bool
     */
    public function validate($line) {
        if (!preg_match('/^%%\s*staffwidth\s+(.+)$/i', trim($line), $matches)) {
            return false;
        }

        $value = trim($matches[1]);
        return $this->isValidDimension($value);
    }

    /**
     * Validates if a string is a valid dimension value.
     *
     * @param string $value The dimension value to validate.
     * @return bool True if valid.
     */
    private function isValidDimension(string $value): bool {
        // Accept values like: 800, 600pt, 50cm, etc.
        return preg_match('/^\d+(\.\d+)?\s*(pt|cm|mm|in|px)?$/i', $value) === 1;
    }
}