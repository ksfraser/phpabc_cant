<?php
namespace Ksfraser\PhpabcCanntaireachd;
require_once __DIR__ . '/AbcLine.php';

use Ksfraser\PhpabcCanntaireachd\AbcLine;

/**
 * Parser for music body lines
 */
class BodyParser implements AbcLineParser {
    /**
     * @param string $line
     * @return bool
     */
    public function canParse($line) {
        // Body lines are anything that doesn't match other parsers
        // This should be the last parser in the chain
        return true;
    }

    /**
     * @param string $line
     * @param AbcTune $tune
     * @return bool
     */
    public function parse($line, $tune) {
        $abcLine = new AbcLine();

        // Handle embedded instructions like [K:...], [M:...], etc.
        if (preg_match_all('/\[([A-Z]):([^\]]+)\]/', $line, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $key = $match[1];
                $value = trim($match[2]);
                // Add as inline header
                $inlineLine = new AbcLine();
                $inlineLine->setHeaderLine("[$key:$value]");
                $abcLine->add($inlineLine);
                // Remove the inline instruction from the line
                $line = str_replace($match[0], '', $line);
            }
        }

        // Parse remaining bars
        foreach (preg_split('/\|/', $line) as $barText) {
            $barText = trim($barText);
            if ($barText !== '') {
                $abcLine->add(new AbcBar($barText));
            }
        }

        if ($abcLine->hasContent()) {
            $tune->add($abcLine);
        }

        return true;
    }
    
    /**
     * @param string $line
     * @return bool
     */
    public function validate($line) {
        // Validate notes, barlines, lyrics, etc. - standard ABC notation
        return preg_match('/^[\|\[\]a-gA-GzZ0-9\s,:!\'\"\/\^_\.\-]+$/', $line);
    }
}
