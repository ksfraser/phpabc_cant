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
        // Pass 5: reorder voices by channel, drums last
        $voiceLines = [];
        $otherLines = [];
        $drumLines = [];
        $defaults = [];
        // Load MIDI defaults
        try {
            $pdo = new \PDO('sqlite:' . __DIR__ . '/../../MIDI_DefaultsTable.db');
            $stmt = $pdo->query('SELECT voice_name, midi_channel FROM abc_midi_defaults');
            foreach ($stmt as $row) {
                $defaults[$row['voice_name']] = $row['midi_channel'];
            }
        } catch (\Exception $e) {
            // fallback: hardcoded
            $defaults = [
                'Bagpipes' => 0, 'Flute' => 1, 'Tenor' => 2, 'Clarinet' => 3, 'Trombone' => 4, 'Tuba' => 5,
                'Alto' => 6, 'Trumpet' => 7, 'Guitar' => 8, 'Piano' => 9, 'Drums' => 10, 'BassGuitar' => 11
            ];
        }
        foreach ($output as $line) {
            if (preg_match('/^V:([^\s]+)/', $line, $m)) {
                $voice = $m[1];
                if (stripos($voice, 'drum') !== false) {
                    $drumLines[] = $line;
                } else {
                    $voiceLines[$voice] = $line;
                }
            } else {
                $otherLines[] = $line;
            }
        }
        // Sort voices by channel
        uasort($voiceLines, function($a, $b) use ($defaults) {
            preg_match('/^V:([^\s]+)/', $a, $ma);
            preg_match('/^V:([^\s]+)/', $b, $mb);
            $ca = $defaults[$ma[1]] ?? 99;
            $cb = $defaults[$mb[1]] ?? 99;
            return $ca <=> $cb;
        });
        // Rebuild output: other lines, sorted voices, drums last
        $output = array_merge($otherLines, array_values($voiceLines), $drumLines);
        return [
            'lines' => $output,
            'canntDiff' => $canntDiff
        ];
    }
}
