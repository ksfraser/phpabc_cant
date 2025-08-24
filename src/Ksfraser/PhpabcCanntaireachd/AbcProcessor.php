<?php
namespace Ksfraser\PhpabcCanntaireachd;

class AbcProcessor {
    public static function process($abcContent, $dict) {
        $lines = explode("\n", $abcContent);
        [$hasMelody, $hasBagpipes] = self::detectVoices($lines);
        $output = self::copyMelodyToBagpipes($lines, $hasMelody, $hasBagpipes);
        $lyricsWords = [];
        $output = self::handleLyrics($output, $dict, $lyricsWords);
        if ($lyricsWords) {
            $output[] = 'W: ' . implode(' ', $lyricsWords);
        }
        $canntDiff = [];
        $output = self::validateCanntaireachd($output, $canntDiff);
        $output = self::reorderVoices($output);
        return [
            'lines' => $output,
            'canntDiff' => $canntDiff
        ];
    }

    private static function detectVoices($lines) {
        $hasMelody = false;
        $hasBagpipes = false;
        foreach ($lines as $line) {
            if (preg_match('/^V:Melody/', $line)) $hasMelody = true;
            if (preg_match('/^V:Bagpipes/', $line)) $hasBagpipes = true;
        }
        return [$hasMelody, $hasBagpipes];
    }

    private static function copyMelodyToBagpipes($lines, $hasMelody, $hasBagpipes) {
        $output = [];
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
        return $output;
    }

    private static function handleLyrics($output, $dict, &$lyricsWords) {
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
        return array_values($output);
    }

    private static function validateCanntaireachd($output, &$canntDiff) {
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
        return $output;
    }

    private static function reorderVoices($output) {
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
        uasort($voiceLines, function($a, $b) use ($defaults) {
            preg_match('/^V:([^\s]+)/', $a, $ma);
            preg_match('/^V:([^\s]+)/', $b, $mb);
            $ca = $defaults[$ma[1]] ?? 99;
            $cb = $defaults[$mb[1]] ?? 99;
            return $ca <=> $cb;
        });
        return array_merge($otherLines, array_values($voiceLines), $drumLines);
    }
}
