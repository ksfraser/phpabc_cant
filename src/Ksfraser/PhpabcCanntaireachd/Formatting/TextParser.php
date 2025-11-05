<?php
namespace Ksfraser\PhpabcCanntaireachd\Formatting;

use Ksfraser\PhpabcCanntaireachd\AbcFormattingLine;
use Ksfraser\PhpabcCanntaireachd\AbcLineParser;

/**
 * Parser for text directives (%%text, %%center)
 * Extends FormattingDirectiveParser for common formatting functionality.
 *
 * @requirement FR10 (formatting directive parsing)
 */
class TextParser extends FormattingDirectiveParser implements AbcLineParser
{
    private const VALID_TEXT_TYPES = [
        'text', 'center'
    ];

    /**
     * @param mixed $line
     * @return bool
     */
    public function canParse($line) {
        $directiveType = $this->getDirectiveType($line);
        return $this->isFormattingDirective($line) &&
               in_array($directiveType, self::VALID_TEXT_TYPES);
    }

    /**
     * @param mixed $line
     * @param mixed $tune
     * @return bool
     */
    public function parse($line, $tune) {
        if (!preg_match('/^%%\s*(text|center)\s+(.+)$/i', trim($line), $matches)) {
            return false;
        }

        $textType = strtolower($matches[1]);
        $textContent = trim($matches[2]);

        // Basic validation - text content should not be empty
        if (empty($textContent)) {
            $tune->add(new AbcFormattingLine("%% Invalid $textType directive: empty content"));
            return true;
        }

        // Create standardized formatting line
        $correctedLine = "%% $textType $textContent";
        $tune->add(new AbcFormattingLine($correctedLine));
        return true;
    }

    /**
     * @param mixed $line
     * @return bool
     */
    public function validate($line) {
        if (!preg_match('/^%%\s*(text|center)\s+(.+)$/i', trim($line), $matches)) {
            return false;
        }

        $textContent = trim($matches[2]);
        return !empty($textContent);
    }
}