<?php
namespace Ksfraser\PhpabcCanntaireachd\Formatting;

use Ksfraser\PhpabcCanntaireachd\AbcFormattingLine;
use Ksfraser\PhpabcCanntaireachd\AbcLineParser;

/**
 * Parser for break directives (%%staffbreak, %%linebreak)
 * Extends FormattingDirectiveParser for common formatting functionality.
 *
 * @requirement FR10 (formatting directive parsing)
 */
class BreakParser extends FormattingDirectiveParser implements AbcLineParser
{
    private const VALID_BREAK_TYPES = [
        'staffbreak', 'linebreak'
    ];

    /**
     * @param mixed $line
     * @return bool
     */
    public function canParse($line) {
        $directiveType = $this->getDirectiveType($line);
        return $this->isFormattingDirective($line) &&
               in_array($directiveType, self::VALID_BREAK_TYPES);
    }

    /**
     * @param mixed $line
     * @param mixed $tune
     * @return bool
     */
    public function parse($line, $tune) {
        if (!preg_match('/^%%\s*(staffbreak|linebreak)(\s+(.+))?$/i', trim($line), $matches)) {
            return false;
        }

        $breakType = strtolower($matches[1]);
        $value = isset($matches[3]) ? trim($matches[3]) : null;

        // Validate value if provided (some break directives might have optional parameters)
        if ($value !== null && !empty($value)) {
            // For now, accept numeric values or specific keywords
            if (!preg_match('/^(force|\d+|\d+\.\d+)$/i', $value)) {
                $tune->add(new AbcFormattingLine("%% Invalid $breakType value: $value"));
                return true;
            }
        }

        // Create standardized formatting line
        $correctedLine = "%% $breakType";
        if ($value !== null && !empty($value)) {
            $correctedLine .= " $value";
        }
        $tune->add(new AbcFormattingLine($correctedLine));
        return true;
    }

    /**
     * @param mixed $line
     * @return bool
     */
    public function validate($line) {
        if (!preg_match('/^%%\s*(staffbreak|linebreak)(\s+(.+))?$/i', trim($line), $matches)) {
            return false;
        }

        $value = isset($matches[3]) ? trim($matches[3]) : null;

        // If value is provided, do basic validation
        if ($value !== null && !empty($value)) {
            return preg_match('/^(force|\d+|\d+\.\d+)$/i', $value);
        }

        return true;
    }
}