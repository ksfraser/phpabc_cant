<?php
namespace Ksfraser\PhpabcCanntaireachd\Formatting;

use Ksfraser\PhpabcCanntaireachd\AbcFormattingLine;
use Ksfraser\PhpabcCanntaireachd\AbcLineParser;

/**
 * Parser for %%pagewidth directive
 * Extends FormattingDirectiveParser for common formatting functionality.
 *
 * @requirement FR10 (formatting directive parsing)
 */
class PageWidthParser extends FormattingDirectiveParser implements AbcLineParser
{
    /**
     * @param mixed $line
     * @return bool
     */
    public function canParse($line) {
        return $this->isFormattingDirective($line) && $this->getDirectiveType($line) === 'pagewidth';
    }

    /**
     * @param mixed $line
     * @param mixed $tune
     * @return bool
     */
    public function parse($line, $tune) {
        if (!preg_match('/^%%\s*pagewidth\s+(.+)$/i', trim($line), $matches)) {
            return false;
        }

        $value = trim($matches[1]);

        // Validate page width value (should be a valid dimension like 8.5in, 21cm, etc.)
        if (!$this->isValidDimension($value)) {
            $tune->add(new AbcFormattingLine("%% Invalid pagewidth value: $value"));
            return true;
        }

        // Create standardized formatting line
        $correctedLine = "%% pagewidth $value";
        $tune->add(new AbcFormattingLine($correctedLine));
        return true;
    }

    /**
     * @param mixed $line
     * @return bool
     */
    public function validate($line) {
        if (!preg_match('/^%%\s*pagewidth\s+(.+)$/i', trim($line), $matches)) {
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
        // Accept values like: 8.5in, 21cm, 210mm, 100pt, etc.
        return preg_match('/^\d+(\.\d+)?\s*(in|cm|mm|pt|px)?$/i', $value) === 1;
    }
}