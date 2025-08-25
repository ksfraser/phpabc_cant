<?php
namespace Ksfraser\PhpabcCanntaireachd;
/**
 * Parses ABC files into AbcTune objects, handling multiple tunes per file.
 */
class AbcFileParser {
    /**
     * Parse ABC file content into an array of AbcTune objects.
     * @param string $abcContent
     * @return AbcTune[]
     */
    public function parse($abcContent): array {
        $lines = preg_split('/\r?\n/', $abcContent);
        $tunes = [];
        $currentTune = null;
        foreach ($lines as $idx => $line) {
            if (preg_match('/^X:/', $line)) {
                // Ensure blank line before X: header
                if ($idx > 0 && trim($lines[$idx-1]) !== '') {
                    $tunes[] = $currentTune;
                    $currentTune = null;
                }
                if ($currentTune) $tunes[] = $currentTune;
                $currentTune = new AbcTune();
                $currentTune->addHeader('X', substr($line, 2));
            } elseif ($currentTune && preg_match('/^([A-Z]):(.*)/', $line, $m)) {
                $currentTune->addHeader($m[1], trim($m[2]));
            } elseif ($currentTune && trim($line) === '') {
                // Blank line inside tune: add as line (for hidden voices/data)
                $currentTune->add(new AbcLine());
            } elseif ($currentTune) {
                // Parse bars for each line
                $abcLine = new AbcLine();
                foreach (preg_split('/\|/', $line) as $barText) {
                    $barText = trim($barText);
                    if ($barText !== '') {
                        $abcLine->add(new AbcBar($barText));
                    }
                }
                $currentTune->add($abcLine);
            }
        }
        if ($currentTune) $tunes[] = $currentTune;
        // Remove nulls
        return array_filter($tunes);
    }
}
