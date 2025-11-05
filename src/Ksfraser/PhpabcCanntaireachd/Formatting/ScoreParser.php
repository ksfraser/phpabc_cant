<?php
namespace Ksfraser\PhpabcCanntaireachd\Formatting;

use Ksfraser\PhpabcCanntaireachd\AbcFormattingLine;
use Ksfraser\PhpabcCanntaireachd\AbcLineParser;

/**
 * Parser for score layout directive (%%score)
 * Extends FormattingDirectiveParser for common formatting functionality.
 *
 * @requirement FR10 (formatting directive parsing)
 */
class ScoreParser extends FormattingDirectiveParser implements AbcLineParser
{
    /**
     * @param mixed $line
     * @return bool
     */
    public function canParse($line) {
        return $this->isFormattingDirective($line) && $this->getDirectiveType($line) === 'score';
    }

    /**
     * @param mixed $line
     * @param mixed $tune
     * @return bool
     */
    public function parse($line, $tune) {
        if (!preg_match('/^%%\s*score\s+(.+)$/i', trim($line), $matches)) {
            return false;
        }

        $scoreSpec = trim($matches[1]);

        // Basic validation - score specification should not be empty
        if (empty($scoreSpec)) {
            $tune->add(new AbcFormattingLine("%% Invalid score specification: empty value"));
            return true;
        }

        // Validate score specification format (basic check for common patterns)
        if (!$this->isValidScoreSpecification($scoreSpec)) {
            $tune->add(new AbcFormattingLine("%% Invalid score specification: $scoreSpec"));
            return true;
        }

        // Create standardized formatting line
        $correctedLine = "%% score $scoreSpec";
        $tune->add(new AbcFormattingLine($correctedLine));
        return true;
    }

    /**
     * @param mixed $line
     * @return bool
     */
    public function validate($line) {
        if (!preg_match('/^%%\s*score\s+(.+)$/i', trim($line), $matches)) {
            return false;
        }

        $scoreSpec = trim($matches[1]);
        return !empty($scoreSpec) && $this->isValidScoreSpecification($scoreSpec);
    }

    /**
     * Validate score specification format
     * Score specs typically use voice numbers, brackets, and operators like (1 2) {3 4}
     *
     * @param string $spec
     * @return bool
     */
    private function isValidScoreSpecification($spec) {
        // Basic validation: should contain at least one digit (voice number)
        // and only allowed characters: digits, spaces, parentheses, braces, asterisks
        return preg_match('/\d/', $spec) &&
               !preg_match('/[^0-9\s\(\)\{\}\*]/', $spec);
    }
}