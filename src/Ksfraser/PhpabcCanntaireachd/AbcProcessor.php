<?php
namespace Ksfraser\PhpabcCanntaireachd;

use Ksfraser\PhpabcCanntaireachd\HeaderExtractorTrait;

class AbcProcessor {
    use HeaderExtractorTrait;
    public static function process($abcContent, $dict, $headerTable = null) {
        $lines = explode("\n", $abcContent);
        $passes = [
            new AbcTuneNumberValidatorPass(),
            new AbcVoicePass(),
            new AbcLyricsPass($dict),
            new AbcCanntaireachdPass($dict),
            new AbcVoiceOrderPass(),
            new AbcTimingValidator()
        ];
        $canntDiff = [];
        $errors = [];

        // Extract header fields from ABC (e.g., C: composer, B: book)
        $headerFields = self::extractHeaders($lines, ['C','B','K','T','M','L','Q']);
        $tuneFields = [
            'composer' => isset($headerFields['C']) ? $headerFields['C'] : null,
            'book' => isset($headerFields['B']) ? $headerFields['B'] : null,
            'key' => isset($headerFields['K']) ? $headerFields['K'] : null,
            'title' => isset($headerFields['T']) ? $headerFields['T'] : null,
            'meter' => isset($headerFields['M']) ? $headerFields['M'] : null,
            'notelength' => isset($headerFields['L']) ? $headerFields['L'] : null,
            'tempo' => isset($headerFields['Q']) ? $headerFields['Q'] : null
        ];

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
            // Check for various melody voice patterns
            if (preg_match('/^V:Melody/', $line) || preg_match('/^V:M\s/', $line) || preg_match('/name="Melody"/', $line)) {
                $hasMelody = true;
            }
            // Check for bagpipe voice patterns
            if (preg_match('/^V:Bagpipes/', $line) || preg_match('/^V:B\s/', $line) || preg_match('/name="Bagpipes"/', $line)) {
                $hasBagpipes = true;
            }
        }
        return [$hasMelody, $hasBagpipes];
    }
    public static function copyMelodyToBagpipes($lines, $hasMelody, $hasBagpipes) {
        // Split the file into individual tunes and process each one
        $tunes = self::splitTunes($lines);
        
        $output = [];
        foreach ($tunes as $index => $tuneLines) {
            // Process each tune separately
            [$tuneHasMelody, $tuneHasBagpipes] = self::detectVoices($tuneLines);
            
            if ($tuneHasMelody && !$tuneHasBagpipes) {
                $tuneLines = self::copyMelodyToBagpipesInTune($tuneLines);
            }
            
            $output = array_merge($output, $tuneLines);
            
            // Add 2 blank lines between tunes (except after the last tune)
            if ($index < count($tunes) - 1) {
                $blankRenderer = new BlankLineRenderer(2);
                $output = array_merge($output, $blankRenderer->render());
            }
        }
        
        return $output;
    }
    
    private static function splitTunes($lines) {
        $tunes = [];
        $currentTune = [];
        
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if (preg_match('/^X:/', $line)) {
                // Start of new tune
                if (!empty($currentTune)) {
                    // Remove trailing blank lines from previous tune
                    while (!empty($currentTune) && trim(end($currentTune)) === '') {
                        array_pop($currentTune);
                    }
                    $tunes[] = $currentTune;
                }
                $currentTune = [$line];
            } elseif (!empty($currentTune) || $trimmed !== '') {
                // Add line to current tune, or start new tune if this is content before first X:
                $currentTune[] = $line;
            }
        }
        
        // Don't forget the last tune
        if (!empty($currentTune)) {
            // Remove trailing blank lines
            while (!empty($currentTune) && trim(end($currentTune)) === '') {
                array_pop($currentTune);
            }
            $tunes[] = $currentTune;
        }
        
        return $tunes;
    }
    
    private static function copyMelodyToBagpipesInTune($lines) {
        $output = [];
        $currentVoice = null;
        $voiceContent = [];
        $melodyContents = []; // Store melody voice content for bagpipe creation

        foreach ($lines as $line) {
            $trimmed = trim($line);
            
            // Check if this is a voice header
            if (preg_match('/^V:/', $line)) {
                // Save previous voice if any
                if ($currentVoice) {
                    $output = array_merge($output, $voiceContent);
                }
                
                $currentVoice = $line;
                $voiceContent = [$line];
                
                // Check if this is a melody voice
                $isMelody = preg_match('/^V:Melody/', $line) || 
                           preg_match('/^V:M\s/', $line) || 
                           preg_match('/name="Melody"/', $line);
                
                if ($isMelody) {
                    // Start collecting content for this melody voice
                    $melodyContents[] = [];
                }
            } elseif ($currentVoice) {
                // This line belongs to the current voice
                $voiceContent[] = $line;
                
                // If this is a melody voice, collect content for copying
                if (!empty($melodyContents)) {
                    $lastIndex = count($melodyContents) - 1;
                    $melodyContents[$lastIndex][] = $line;
                }
            } else {
                // This is a header or other content before first voice
                $output[] = $line;
            }
        }
        
        // Handle the last voice
        if ($currentVoice) {
            $output = array_merge($output, $voiceContent);
        }
        
        // Add bagpipe voices for each melody voice found
        foreach ($melodyContents as $content) {
            $output[] = 'V:Bagpipes name="Bagpipes" sname="Bagpipes"';
            $output[] = '%canntaireachd: <add your canntaireachd here>';
            $output = array_merge($output, $content);
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
        $headers = [];
        $voiceBlocks = [];
        $currentVoice = null;
        $currentBlock = [];

        // Parse into headers and voice blocks
        foreach ($output as $line) {
            if (preg_match('/^V:/', $line)) {
                // Save previous voice block
                if ($currentVoice) {
                    $voiceBlocks[$currentVoice] = $currentBlock;
                }
                $currentVoice = $line;
                $currentBlock = [$line];
            } elseif ($currentVoice) {
                $currentBlock[] = $line;
            } else {
                $headers[] = $line;
            }
        }

        // Save the last voice block
        if ($currentVoice) {
            $voiceBlocks[$currentVoice] = $currentBlock;
        }

        // Sort voice blocks
        $defaults = [
            'Bagpipes' => 0, 'Flute' => 1, 'Tenor' => 2, 'Clarinet' => 3, 'Trombone' => 4, 'Tuba' => 5,
            'Alto' => 6, 'Trumpet' => 7, 'Guitar' => 8, 'Piano' => 9, 'Drums' => 10, 'BassGuitar' => 11
        ];

        uasort($voiceBlocks, function($a, $b) use ($defaults) {
            preg_match('/^V:([^\s]+)/', $a[0], $ma);
            preg_match('/^V:([^\s]+)/', $b[0], $mb);
            $ca = $defaults[$ma[1]] ?? 99;
            $cb = $defaults[$mb[1]] ?? 99;
            return $ca <=> $cb;
        });

        // Separate drum voices
        $drumBlocks = [];
        $otherBlocks = [];
        foreach ($voiceBlocks as $voice => $block) {
            if (stripos($voice, 'drum') !== false) {
                $drumBlocks[$voice] = $block;
            } else {
                $otherBlocks[$voice] = $block;
            }
        }

        // Reconstruct output
        $result = $headers;
        foreach ($otherBlocks as $block) {
            $result = array_merge($result, $block);
        }
        foreach ($drumBlocks as $block) {
            $result = array_merge($result, $block);
        }

        return $result;
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


