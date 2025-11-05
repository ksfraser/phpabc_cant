<?php
namespace Ksfraser\PhpabcCanntaireachd\Formatting;

use Ksfraser\PhpabcCanntaireachd\AbcFormattingLine;
use Ksfraser\PhpabcCanntaireachd\AbcLineParser;

/**
 * Parser for page control directives (%%newpage, %%continueall, %%breakall)
 * Extends FormattingDirectiveParser for common formatting functionality.
 *
 * @requirement FR10 (formatting directive parsing)
 */
class PageControlParser extends FormattingDirectiveParser implements AbcLineParser
{
    private const VALID_PAGE_CONTROL_TYPES = [
        'newpage', 'continueall', 'breakall'
    ];

    /**
     * @param mixed $line
     * @return bool
     */
    public function canParse($line) {
        $directiveType = $this->getDirectiveType($line);
        return $this->isFormattingDirective($line) &&
               in_array($directiveType, self::VALID_PAGE_CONTROL_TYPES);
    }

    /**
     * @param mixed $line
     * @param mixed $tune
     * @return bool
     */
    public function parse($line, $tune) {
        if (!preg_match('/^%%\s*(newpage|continueall|breakall)(\s+(.+))?$/i', trim($line), $matches)) {
            return false;
        }

        $controlType = strtolower($matches[1]);
        $value = isset($matches[3]) ? trim($matches[3]) : null;

        // Validate value if provided (some directives might have optional parameters)
        if ($value !== null && !empty($value)) {
            // For now, accept any value - could be enhanced with specific validation
            if (!preg_match('/^(on|off|\d+|\d+\.\d+|[a-zA-Z0-9\s]+)$/i', $value)) {
                $tune->add(new AbcFormattingLine("%% Invalid $controlType value: $value"));
                return true;
            }
        }

        // Create standardized formatting line
        $correctedLine = "%% $controlType";
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
        if (!preg_match('/^%%\s*(newpage|continueall|breakall)(\s+(.+))?$/i', trim($line), $matches)) {
            return false;
        }

        $value = isset($matches[3]) ? trim($matches[3]) : null;

        // If value is provided, do basic validation
        if ($value !== null && !empty($value)) {
            return preg_match('/^(on|off|\d+|\d+\.\d+|[a-zA-Z0-9\s]+)$/i', $value);
        }

        return true;
    }
}