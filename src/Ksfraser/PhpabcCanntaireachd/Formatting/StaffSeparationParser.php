<?php
namespace Ksfraser\PhpabcCanntaireachd\Formatting;

use Ksfraser\PhpabcCanntaireachd\AbcFormattingLine;
use Ksfraser\PhpabcCanntaireachd\AbcLineParser;

/**
 * Parser for staff separation directives (%%staffsep, %%sysstaffsep)
 * Extends FormattingDirectiveParser for common formatting functionality.
 *
 * @requirement FR10 (formatting directive parsing)
 */
class StaffSeparationParser extends FormattingDirectiveParser implements AbcLineParser
{
    private const VALID_SEPARATION_TYPES = [
        'staffsep', 'sysstaffsep'
    ];

    /**
     * @param mixed $line
     * @return bool
     */
    public function canParse($line) {
        $directiveType = $this->getDirectiveType($line);
        return $this->isFormattingDirective($line) &&
               in_array($directiveType, self::VALID_SEPARATION_TYPES);
    }

    /**
     * @param mixed $line
     * @param mixed $tune
     * @return bool
     */
    public function parse($line, $tune) {
        if (!preg_match('/^%%\s*(staffsep|sysstaffsep)\s+(.+)$/i', trim($line), $matches)) {
            return false;
        }

        $sepType = strtolower($matches[1]);
        $value = trim($matches[2]);

        // Validate separation value (should be a valid dimension)
        if (!$this->isValidDimension($value)) {
            $tune->add(new AbcFormattingLine("%% Invalid $sepType value: $value"));
            return true;
        }

        // Create standardized formatting line
        $correctedLine = "%% $sepType $value";
        $tune->add(new AbcFormattingLine($correctedLine));
        return true;
    }

    /**
     * @param mixed $line
     * @return bool
     */
    public function validate($line) {
        if (!preg_match('/^%%\s*(staffsep|sysstaffsep)\s+(.+)$/i', trim($line), $matches)) {
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
        // Accept values like: 20pt, 15cm, 50mm, 100, etc.
        return preg_match('/^\d+(\.\d+)?\s*(in|cm|mm|pt|px)?$/i', $value) === 1;
    }
}