<?php
namespace Ksfraser\PhpabcCanntaireachd\Formatting;

use Ksfraser\PhpabcCanntaireachd\AbcFormattingLine;
use Ksfraser\PhpabcCanntaireachd\AbcLineParser;

/**
 * Parser for margin directives (%%leftmargin, %%rightmargin, %%topmargin, %%bottommargin)
 * Extends FormattingDirectiveParser for common formatting functionality.
 *
 * @requirement FR10 (formatting directive parsing)
 */
class MarginParser extends FormattingDirectiveParser implements AbcLineParser
{
    /**
     * @param mixed $line
     * @return bool
     */
    public function canParse($line) {
        $directiveType = $this->getDirectiveType($line);
        return $this->isFormattingDirective($line) &&
               in_array($directiveType, ['leftmargin', 'rightmargin', 'topmargin', 'bottommargin']);
    }

    /**
     * @param mixed $line
     * @param mixed $tune
     * @return bool
     */
    public function parse($line, $tune) {
        if (!preg_match('/^%%\s*(leftmargin|rightmargin|topmargin|bottommargin)\s+(.+)$/i', trim($line), $matches)) {
            return false;
        }

        $marginType = strtolower($matches[1]);
        $value = trim($matches[2]);

        // Validate margin value
        if (!$this->isValidDimension($value)) {
            $tune->add(new AbcFormattingLine("%% Invalid $marginType value: $value"));
            return true;
        }

        // Create standardized formatting line
        $correctedLine = "%% $marginType $value";
        $tune->add(new AbcFormattingLine($correctedLine));
        return true;
    }

    /**
     * @param mixed $line
     * @return bool
     */
    public function validate($line) {
        if (!preg_match('/^%%\s*(leftmargin|rightmargin|topmargin|bottommargin)\s+(.+)$/i', trim($line), $matches)) {
            return false;
        }

        $value = trim($matches[2]);
        return $this->isValidDimension($value);
    }

    /**
     * Validates if a string is a valid dimension value.
     *
     * @param string $value The dimension value to validate.
     * @return bool True if valid.
     */
    private function isValidDimension(string $value): bool {
        // Accept values like: 1cm, 0.5in, 10mm, 5pt, etc.
        return preg_match('/^\d+(\.\d+)?\s*(in|cm|mm|pt|px)?$/i', $value) === 1;
    }
}