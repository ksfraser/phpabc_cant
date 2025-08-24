<?php
namespace Ksfraser\PhpabcCanntaireachd;

class AbcProcessor {
    public static function process($abcContent, $dict) {
        $lines = explode("\n", $abcContent);
        $hasMelody = false;
        $hasBagpipes = false;
        $output = [];
        $lyricsWords = [];
        $canntDiff = [];
        // Pass 1: detect voices
        foreach ($lines as $line) {
            if (preg_match('/^V:Melody/', $line)) $hasMelody = true;
            if (preg_match('/^V:Bagpipes/', $line)) $hasBagpipes = true;
        }
        // Pass 2: copy Melody to Bagpipes if needed
        if ($hasMelody && !$hasBagpipes) {
            foreach ($lines as $line) {
                if (preg_match('/^V:Melody/', $line)) {
                    $output[] = 'V:Bagpipes name="Bagpipes" sname="Bagpipes"';
                } elseif (preg_match('/^w:(.*)$/', $line, $m)) {
                    // Copy w: lines to Bagpipes
                } elseif (!preg_match('/^V:/', $line)) {
                    $output[] = $line;
                }
            }
        } else {
            $output = $lines;
        }
        // Pass 3: handle w:/W: lyrics/canntaireachd
        foreach ($output as $idx => $line) {
            if (preg_match('/^w:(.*)$/', $line, $m)) {
                $words = preg_split('/\s+/', trim($m[1]));
                $allInDict = true;
                foreach ($words as $word) {
                    if (!isset($dict[$word])) {
                        $allInDict = false;
                        break;
                    }
                }
                if (!$allInDict) {
                    $lyricsWords = array_merge($lyricsWords, $words);
                    unset($output[$idx]);
                }
            }
        }
        if ($lyricsWords) {
            $output[] = 'W: ' . implode(' ', $lyricsWords);
        }
        // Pass 4: validate canntaireachd and log differences
        $newCannt = '<add your canntaireachd here>';
        foreach ($output as $idx => $line) {
            if (preg_match('/^%canntaireachd:(.*)$/', $line, $m)) {
                $oldCannt = trim($m[1]);
                if ($oldCannt !== $newCannt) {
                    $canntDiff[] = "Bagpipes: '$oldCannt' => '$newCannt'";
                    $output[$idx] = "%canntaireachd: $newCannt";
                }
            }
        }
        return [
            'lines' => $output,
            'canntDiff' => $canntDiff
        ];
    }
}
