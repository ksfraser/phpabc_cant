<?php
namespace Ksfraser\PhpabcCanntaireachd\Formatting;

use Ksfraser\PhpabcCanntaireachd\AbcFormattingLine;
use Ksfraser\PhpabcCanntaireachd\AbcLineParser;

/**
 * Parser for %%landscape and %%portrait directives
 * Extends FormattingDirectiveParser for common formatting functionality.
 *
 * @requirement FR10 (formatting directive parsing)
 */
class PageOrientationParser extends FormattingDirectiveParser implements AbcLineParser
{
    /**
     * @param mixed $line
     * @return bool
     */
    public function canParse($line) {
        $directiveType = $this->getDirectiveType($line);
        return $this->isFormattingDirective($line) &&
               ($directiveType === 'landscape' || $directiveType === 'portrait');
    }

    /**
     * @param mixed $line
     * @param mixed $tune
     * @return bool
     */
    public function parse($line, $tune) {
        $directiveType = $this->getDirectiveType($line);

        if (!preg_match('/^%%\s*(landscape|portrait)(\s+(on|off))?$/i', trim($line), $matches)) {
            return false;
        }

        $orientation = strtolower($matches[1]);
        $state = isset($matches[3]) ? strtolower($matches[3]) : 'on';

        // Validate orientation
        if (!in_array($orientation, ['landscape', 'portrait'])) {
            $tune->add(new AbcFormattingLine("%% Invalid page orientation: $orientation"));
            return true;
        }

        // Validate state
        if (!in_array($state, ['on', 'off'])) {
            $tune->add(new AbcFormattingLine("%% Invalid orientation state: $state (must be 'on' or 'off')"));
            return true;
        }

        // Create standardized formatting line
        $correctedLine = "%% $orientation $state";
        $tune->add(new AbcFormattingLine($correctedLine));
        return true;
    }

    /**
     * @param mixed $line
     * @return bool
     */
    public function validate($line) {
        if (!preg_match('/^%%\s*(landscape|portrait)(\s+(on|off))?$/i', trim($line), $matches)) {
            return false;
        }

        $orientation = strtolower($matches[1]);
        $state = isset($matches[3]) ? strtolower($matches[3]) : 'on';

        return in_array($orientation, ['landscape', 'portrait']) &&
               in_array($state, ['on', 'off']);
    }
}