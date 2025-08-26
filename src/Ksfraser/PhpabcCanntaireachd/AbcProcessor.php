<?php
namespace Ksfraser\PhpabcCanntaireachd;

class AbcProcessor {
    public static function process($abcContent, $dict, $headerTable = null) {
        $lines = explode("\n", $abcContent);
        $passes = [
            new AbcTuneNumberValidatorPass(),
            new AbcVoicePass(),
            new AbcLyricsPass($dict),
            new AbcCanntaireachdPass(),
            new AbcVoiceOrderPass(),
            new AbcTimingValidator()
        ];
        $canntDiff = [];
        $errors = [];

        // Extract header fields from ABC (e.g., C: composer, B: book)
        $tuneFields = [];
        foreach ($lines as $line) {
            if (preg_match('/^C:\s*(.+)$/', $line, $m)) {
                $tuneFields['composer'] = trim($m[1]);
            }
            if (preg_match('/^B:\s*(.+)$/', $line, $m)) {
                $tuneFields['book'] = trim($m[1]);
            }
        }

        // Match and update header fields
        $suggestions = [];
        if ($headerTable) {
            $matcher = new AbcHeaderFieldMatcher($headerTable);
            $suggestions = $matcher->processTuneFields($tuneFields);
        }

        foreach ($passes as $pass) {
            if ($pass instanceof AbcTuneNumberValidatorPass) {
                $result = $pass->validate($lines);
                $lines = $result['lines'];
                if (!empty($result['errors'])) {
                    foreach ($result['errors'] as $err) {
                        $errors[] = 'TUNE NUMBER: ' . $err;
                    }
                }
            } elseif ($pass instanceof AbcLyricsPass) {
                $result = $pass->process($lines);
                $lines = $result['lines'];
                if (!empty($result['lyricsWords'])) {
                    $lines[] = 'W: ' . implode(' ', $result['lyricsWords']);
                }
            } elseif ($pass instanceof AbcCanntaireachdPass) {
                $result = $pass->process($lines);
                $lines = $result['lines'];
                $canntDiff = $result['canntDiff'];
            } elseif ($pass instanceof AbcTimingValidator) {
                $result = $pass->validate($lines);
                $lines = $result['lines'];
                if (!empty($result['errors'])) {
                    $errors = array_map(function($e){return 'TIMING: '.$e;}, $result['errors']);
                }
            } else {
                $lines = $pass->process($lines);
            }
        }

        // Add suggestions as comments for review
        foreach ($suggestions as $s) {
            $lines[] = "% Suggested: {$s['field']} '{$s['value']}' ~ '{$s['bestMatch']}' (score: {$s['score']})";
        }

        return [
            'lines' => $lines,
            'canntDiff' => $canntDiff,
            'errors' => $errors
        ];
    }
    // Make internal pass methods public static for use by pass classes
    public static function detectVoices($lines) {
        $hasMelody = false;
        $hasBagpipes = false;
        foreach ($lines as $line) {
            if (preg_match('/^V:Melody/', $line)) $hasMelody = true;
            if (preg_match('/^V:Bagpipes/', $line)) $hasBagpipes = true;
        }
        return [$hasMelody, $hasBagpipes];
    }
    public static function copyMelodyToBagpipes($lines, $hasMelody, $hasBagpipes) {
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
    public static function handleLyrics($output, $dict, &$lyricsWords) {
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
    public static function validateCanntaireachd($output, &$canntDiff) {
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
    public static function reorderVoices($output) {
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
    public static function renderVoices(array $voiceBars, AbcProcessorConfig $config): array {
        $output = [];
        if ($config->voiceOutputStyle === 'grouped') {
            foreach ($voiceBars as $voice => $bars) {
                $lines = [];
                for ($i = 0; $i < count($bars); $i += $config->barsPerLine) {
                    $lineBars = array_slice($bars, $i, $config->barsPerLine);
                    $line = implode('|', $lineBars);
                    if ($config->joinBarsWithBackslash) {
                        $line = implode(' \n', $lineBars);
                    }
                    $lines[] = $line;
                }
                $output[] = "V:$voice";
                $output = array_merge($output, $lines);
            }
        } else if ($config->voiceOutputStyle === 'interleaved') {
            $maxBars = max(array_map('count', $voiceBars));
            for ($i = 0; $i < $maxBars; $i += $config->interleaveBars) {
                foreach ($voiceBars as $voice => $bars) {
                    $lineBars = array_slice($bars, $i, $config->interleaveBars);
                    if (empty($lineBars)) continue;
                    $line = implode('|', $lineBars);
                    if ($config->joinBarsWithBackslash) {
                        $line = implode(' \n', $lineBars);
                    }
                    $output[] = "V:$voice";
                    $output[] = $line;
                }
            }
        }
        return $output;
    }
}

class AbcProcessorConfig {
    public $voiceOutputStyle = 'grouped'; // 'grouped' or 'interleaved'
    public $interleaveBars = 1; // X bars per voice before switching (if interleaved)
    public $barsPerLine = 4; // How many bars per ABC line
    public $joinBarsWithBackslash = false; // true: use \ to join bars, false: one line per typeset line
}
