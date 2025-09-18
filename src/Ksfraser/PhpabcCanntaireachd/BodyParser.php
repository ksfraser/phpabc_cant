<?php
namespace Ksfraser\PhpabcCanntaireachd;

/**
 * Parser for music body lines
 */
class BodyParser implements AbcLineParser {
    public function canParse(string $line): bool {
        // Body lines are anything that doesn't match other parsers
        // This should be the last parser in the chain
        return true;
    }

    public function parse(string $line, AbcTune $tune): bool {
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
    
    public function validate(string $line): bool {
        // Validate notes, barlines, lyrics, etc. - standard ABC notation
        return preg_match('/^[\|\[\]a-gA-GzZ0-9\s,:!\'\"\/\^_\.\-]+$/', $line);
    }
}
