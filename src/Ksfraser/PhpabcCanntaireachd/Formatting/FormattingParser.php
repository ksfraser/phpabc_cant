<?php
namespace Ksfraser\PhpabcCanntaireachd\Formatting;

use Ksfraser\PhpabcCanntaireachd\AbcLineParser;
use Ksfraser\PhpabcCanntaireachd\AbcFormattingLine;

/**
 * Parser for formatting directive lines (%%pagewidth, %%landscape, etc.)
 * Delegates to specialized formatting parsers.
 */
class FormattingParser implements AbcLineParser
{
    /**
     * @param mixed $line
     * @return bool
     */
    public function canParse($line) {
        return preg_match('/^%%\s*\w+/', trim($line)) === 1 && !preg_match('/^%%MIDI\s+/', trim($line));
    }

    /**
     * @param mixed $line
     * @param mixed $tune
     * @return bool
     */
    public function parse($line, $tune) {
        if (!$this->canParse($line)) {
            return false;
        }

        // Try specialized formatting parsers in order of specificity
        $formattingParsers = [
            new PageWidthParser(),
            new PageHeightParser(),
            new PageOrientationParser(),
            new MarginParser(),
            new ScaleParser(),
            new StaffWidthParser(),
            new FontParser(),
            new StaffSeparationParser(),
            new PageControlParser(),
            new ScoreParser(),
            new BarsPerStaffParser(),
            new TextParser(),
            new SpacingParser(),
            new BreakParser(),
        ];

        foreach ($formattingParsers as $parser) {
            if ($parser->canParse($line)) {
                return $parser->parse($line, $tune);
            }
        }

        // Fallback: handle unknown formatting directives
        $tune->add(new AbcFormattingLine($line));
        return true;
    }

    /**
     * @param mixed $line
     * @return bool
     */
    public function validate($line) {
        if (!$this->canParse($line)) {
            return false;
        }

        // Try specialized formatting parsers for validation
        $formattingParsers = [
            new PageWidthParser(),
            new PageHeightParser(),
            new PageOrientationParser(),
            new MarginParser(),
            new ScaleParser(),
            new StaffWidthParser(),
            new FontParser(),
            new StaffSeparationParser(),
            new PageControlParser(),
            new ScoreParser(),
            new BarsPerStaffParser(),
            new TextParser(),
            new SpacingParser(),
            new BreakParser(),
        ];

        foreach ($formattingParsers as $parser) {
            if ($parser->canParse($line)) {
                return $parser->validate($line);
            }
        }

        // Unknown formatting directive - consider valid for now
        return true;
    }
}