<?php
namespace Ksfraser\PhpabcCanntaireachd;
/**
 * Sanity checks for Bagpipe voice style and structure.
 */
class AbcSanityChecker {
    /**
     * Check style for Bagpipe voice.
     * @param AbcTune $tune
     * @return array List of issues found
     */
    public function checkBagpipeStyle(AbcTune $tune): array {
        $issues = [];
        // Example: check meter and bar count
        $meter = $tune->getHeaders()['M'] ?? null;
        $lines = $tune->getLines();
        $barCount = 0;
        foreach ($lines as $line) {
            foreach ($line->subitems as $bar) {
                $barCount++;
            }
        }
        if ($meter === '4/4' && $barCount !== 8) {
            $issues[] = "4/4 Bagpipe tunes should have 8 bars, found $barCount.";
        }
        if ($meter === '2/4' && $barCount !== 8) {
            $issues[] = "2/4 marches should have 8 bars, found $barCount.";
        }
        // TODO: check for repeats, volta, 2nd endings, etc.
        return $issues;
    }
}
