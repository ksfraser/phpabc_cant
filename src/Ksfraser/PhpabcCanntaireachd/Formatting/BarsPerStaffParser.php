<?php
namespace Ksfraser\PhpabcCanntaireachd\Formatting;

use Ksfraser\PhpabcCanntaireachd\AbcFormattingLine;
use Ksfraser\PhpabcCanntaireachd\AbcLineParser;

/**
 * Parser for bars per staff directive (%%barsperstaff)
 * Extends FormattingDirectiveParser for common formatting functionality.
 *
 * @requirement FR10 (formatting directive parsing)
 */
class BarsPerStaffParser extends FormattingDirectiveParser implements AbcLineParser
{
    /**
     * @param mixed $line
     * @return bool
     */
    public function canParse($line) {
        return $this->isFormattingDirective($line) && $this->getDirectiveType($line) === 'barsperstaff';
    }

    /**
     * @param mixed $line
     * @param mixed $tune
     * @return bool
     */
    public function parse($line, $tune) {
        if (!preg_match('/^%%\s*barsperstaff\s+(.+)$/i', trim($line), $matches)) {
            return false;
        }

        $value = trim($matches[1]);

        // Validate bars per staff value (should be a positive integer)
        if (!$this->isValidBarsPerStaff($value)) {
            $tune->add(new AbcFormattingLine("%% Invalid barsperstaff value: $value"));
            return true;
        }

        // Create standardized formatting line
        $correctedLine = "%% barsperstaff $value";
        $tune->add(new AbcFormattingLine($correctedLine));
        return true;
    }

    /**
     * @param mixed $line
     * @return bool
     */
    public function validate($line) {
        if (!preg_match('/^%%\s*barsperstaff\s+(.+)$/i', trim($line), $matches)) {
            return false;
        }

        $value = trim($matches[1]);
        return $this->isValidBarsPerStaff($value);
    }

    /**
     * Validates if a string is a valid bars per staff value.
     *
     * @param string $value The bars per staff value to validate.
     * @return bool True if valid.
     */
    private function isValidBarsPerStaff(string $value): bool {
        // Accept positive integers like: 4, 8, 12, etc.
        return preg_match('/^\d+$/', $value) === 1 && (int)$value > 0;
    }
}