<?php
namespace Ksfraser\PhpabcCanntaireachd;

/**
 * Parser for voice lines (V:)
 */
class VoiceParser implements AbcLineParser {
    public function canParse(string $line): bool {
        return preg_match('/^V:/', trim($line));
    }

    public function parse(string $line, AbcTune $tune): bool {
        if (!preg_match('/^V:/', trim($line))) {
            return false;
        }

        // Always preserve V: header line
        $abcLine = new AbcLine();
        $abcLine->setHeaderLine($line);
        $tune->add($abcLine);

        return true;
    }
}
