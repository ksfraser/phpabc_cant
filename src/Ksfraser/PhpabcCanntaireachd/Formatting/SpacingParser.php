<?php
namespace Ksfraser\PhpabcCanntaireachd\Formatting;

use Ksfraser\PhpabcCanntaireachd\AbcFormattingLine;
use Ksfraser\PhpabcCanntaireachd\AbcLineParser;

/**
 * Parser for spacing directives (%%vskip, %%musicspace, %%titlespace, etc.)
 * Extends FormattingDirectiveParser for common formatting functionality.
 *
 * @requirement FR10 (formatting directive parsing)
 */
class SpacingParser extends FormattingDirectiveParser implements AbcLineParser
{
    private const VALID_SPACING_TYPES = [
        'vskip', 'musicspace', 'titlespace', 'subtitlespace',
        'composerspace', 'partsspace', 'voicespace', 'gchordspace', 'wordsspace'
    ];

    /**
     * @param mixed $line
     * @return bool
     */
    public function canParse($line) {
        $directiveType = $this->getDirectiveType($line);
        return $this->isFormattingDirective($line) &&
               in_array($directiveType, self::VALID_SPACING_TYPES);
    }

    /**
     * @param mixed $line
     * @param mixed $tune
     * @return bool
     */
    public function parse($line, $tune) {
        if (!preg_match('/^%%\s*(' . implode('|', self::VALID_SPACING_TYPES) . ')\s+(.+)$/i', trim($line), $matches)) {
            return false;
        }

        $spacingType = strtolower($matches[1]);
        $value = trim($matches[2]);

        // Validate spacing value (should be a valid dimension or number)
        if (!$this->isValidSpacingValue($value)) {
            $tune->add(new AbcFormattingLine("%% Invalid $spacingType value: $value"));
            return true;
        }

        // Create standardized formatting line
        $correctedLine = "%% $spacingType $value";
        $tune->add(new AbcFormattingLine($correctedLine));
        return true;
    }

    /**
     * @param mixed $line
     * @return bool
     */
    public function validate($line) {
        if (!preg_match('/^%%\s*(' . implode('|', self::VALID_SPACING_TYPES) . ')\s+(.+)$/i', trim($line), $matches)) {
            return false;
        }

        $value = trim($matches[2]);
        return $this->isValidSpacingValue($value);
    }

    /**
     * Validates if a string is a valid spacing value.
     *
     * @param string $value The spacing value to validate.
     * @return bool True if valid.
     */
    private function isValidSpacingValue(string $value): bool {
        // Accept values like: 1.5cm, 20pt, 10, 0.5in, etc.
        return preg_match('/^\d+(\.\d+)?\s*(in|cm|mm|pt|px)?$/i', $value) === 1;
    }
}