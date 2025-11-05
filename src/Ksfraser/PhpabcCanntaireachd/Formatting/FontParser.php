<?php
namespace Ksfraser\PhpabcCanntaireachd\Formatting;

use Ksfraser\PhpabcCanntaireachd\AbcFormattingLine;
use Ksfraser\PhpabcCanntaireachd\AbcLineParser;

/**
 * Parser for font directives (%%titlefont, %%composerfont, %%vocalfont, etc.)
 * Extends FormattingDirectiveParser for common formatting functionality.
 *
 * @requirement FR10 (formatting directive parsing)
 */
class FontParser extends FormattingDirectiveParser implements AbcLineParser
{
    private const VALID_FONT_TYPES = [
        'titlefont', 'subtitlefont', 'composerfont', 'footerfont',
        'vocalfont', 'gchordfont', 'textfont', 'wordsfont'
    ];

    /**
     * @param mixed $line
     * @return bool
     */
    public function canParse($line) {
        $directiveType = $this->getDirectiveType($line);
        return $this->isFormattingDirective($line) &&
               in_array($directiveType, self::VALID_FONT_TYPES);
    }

    /**
     * @param mixed $line
     * @param mixed $tune
     * @return bool
     */
    public function parse($line, $tune) {
        if (!preg_match('/^%%\s*(' . implode('|', self::VALID_FONT_TYPES) . ')\s+(.+)$/i', trim($line), $matches)) {
            return false;
        }

        $fontType = strtolower($matches[1]);
        $fontSpec = trim($matches[2]);

        // Basic validation - font spec should not be empty
        if (empty($fontSpec)) {
            $tune->add(new AbcFormattingLine("%% Invalid $fontType specification: empty value"));
            return true;
        }

        // Create standardized formatting line
        $correctedLine = "%% $fontType $fontSpec";
        $tune->add(new AbcFormattingLine($correctedLine));
        return true;
    }

    /**
     * @param mixed $line
     * @return bool
     */
    public function validate($line) {
        if (!preg_match('/^%%\s*(' . implode('|', self::VALID_FONT_TYPES) . ')\s+(.+)$/i', trim($line), $matches)) {
            return false;
        }

        $fontSpec = trim($matches[2]);
        return !empty($fontSpec);
    }
}