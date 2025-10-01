<?php
namespace Ksfraser\PhpabcCanntaireachd;

use Ksfraser\PhpabcCanntaireachd\HeaderExtractorTrait;
use Ksfraser\PhpabcCanntaireachd\Voices\InstrumentVoiceFactory;

class AbcProcessor {
    use HeaderExtractorTrait;
    /**
     * Process ABC content using the AbcProcessingPipeline.
     *
     * @param string $abcContent
     * @param TokenDictionary $dict
     * @param array|null $headerTable
     * @return array
     * @throws Exceptions\AbcProcessingException
     * @uml
     * @startuml
     * :explode abcContent into lines;
     * :initialize passes array;
     * :extract header fields;
     * :match/update header fields;
     * :run AbcProcessingPipeline;
     * :return result array;
     * @enduml
     */
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
        $suggestions = [];
        if ($headerTable) {
            $table = $headerTable instanceof AbcHeaderFieldTable ? $headerTable : new AbcHeaderFieldTable();
            if (is_array($headerTable)) {
                foreach ($headerTable as $field => $values) {
                    foreach ((array)$values as $value) {
                        $table->addFieldValue($field, $value);
                    }
                }
            }
            $matcher = new AbcHeaderFieldMatcher($table);
            $suggestions = $matcher->processTuneFields($tuneFields);
        }
        $pipeline = new AbcProcessingPipeline($passes);
        return $pipeline->run($lines, $headerFields, $suggestions);
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
            // Check for bagpipe voice patterns - be more specific to avoid matching V:Bass etc.
            if (preg_match('/^V:Bagpipes/', $line) || preg_match('/name="Bagpipes"/', $line)) {
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
        $hasStartedTunes = false;
        
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
                $hasStartedTunes = true;
            } elseif ($hasStartedTunes && !empty($currentTune)) {
                // Add line to current tune if we've started processing tunes
                $currentTune[] = $line;
            } elseif (!$hasStartedTunes && $trimmed !== '') {
                // Content before first tune - start a tune with this content
                $currentTune = [$line];
                $hasStartedTunes = true;
            } elseif ($hasStartedTunes && empty($currentTune) && $trimmed !== '') {
                // Content after tunes - this shouldn't happen with proper ABC format
                // but handle it by creating a new "pseudo-tune"
                $currentTune = [$line];
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
        $hasMelody = false;
        $melodyVoiceId = null;

        // First pass: find melody voice
        foreach ($lines as $line) {
            if (preg_match('/^V:([^\s]+)/', $line, $m)) {
                $voiceId = $m[1];
                if (preg_match('/name=.*Melody/', $line)) {
                    $hasMelody = true;
                    $melodyVoiceId = $voiceId;
                }
            }
        }

        if (!$hasMelody) {
            return $lines;
        }

        // Second pass: copy lines and collect melody content
        $melodyContent = [];
        foreach ($lines as $line) {
            $output[] = $line;

            // Collect melody content
            if (preg_match('/^\[V:' . preg_quote($melodyVoiceId, '/') . '\](.*)$/i', $line, $m)) {
                // This is melody content, copy it for bagpipe
                $bagpipeLine = '[V:Bagpipes]' . $m[1];
                $melodyContent[] = $bagpipeLine;
            }
        }

        // Add bagpipe voice with copied content (header line should be added in header logic, not here)
        if (!empty($melodyContent)) {
            $output[] = '%canntaireachd: <add your canntaireachd here>';
            $output = array_merge($output, $melodyContent);
        }

        return $output;
    }

    /**
     * Check if a line should be considered voice content
     *
     * @param string $line
     * @return bool
     */
    private static function isVoiceContent(string $line): bool {
        $trimmed = trim($line);

        // Empty lines are voice content (separators)
        if ($trimmed === '') {
            return true;
        }

        // Comments are voice content
        if (preg_match('/^%/', $line)) {
            return true;
        }

        // Lyrics are voice content
        if (preg_match('/^w:/', $line)) {
            return true;
        }

        // Music notation is voice content (contains notes, rests, bar lines, etc.)
        if (preg_match('/[A-Ga-gz]|\\||\\[|\\]|\\(|\\)|!|"/', $line)) {
            return true;
        }

        // Voice-specific directives are voice content
        if (preg_match('/^\\[[A-Za-z]/', $line)) {
            return true;
        }

        // Grace notes and ornaments are voice content
        if (preg_match('/[~{}]/', $line)) {
            return true;
        }

        // Header fields that can appear within voices (like K:, M:, L:) are voice content
        if (preg_match('/^[A-Z]:/', $line)) {
            return true;
        }

        // Everything else (tune headers, MIDI directives, etc.) is not voice content
        return false;
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
        // Split output into individual tunes
        $tunes = [];
        $currentTune = [];
        $hasStartedTunes = false;
        
        foreach ($output as $line) {
            if (preg_match('/^X:/', $line)) {
                // Start of new tune
                if (!empty($currentTune)) {
                    $tunes[] = $currentTune;
                }
                $currentTune = [$line];
                $hasStartedTunes = true;
            } elseif ($hasStartedTunes && !empty($currentTune)) {
                // Add line to current tune
                $currentTune[] = $line;
            } elseif (!$hasStartedTunes && trim($line) !== '') {
                // Content before first tune
                $currentTune = [$line];
                $hasStartedTunes = true;
            }
        }
        
        // Don't forget the last tune
        if (!empty($currentTune)) {
            $tunes[] = $currentTune;
        }
        
        // Process each tune separately
        $result = [];
        foreach ($tunes as $tuneLines) {
            $result = array_merge($result, self::reorderVoicesInTune($tuneLines));
            // Add blank lines between tunes
            $result = array_merge($result, ['', '']);
        }
        
        return $result;
    }
    
    private static function reorderVoicesInTune($lines) {
        $headers = [];
        $voiceBlocks = [];
        $currentVoice = null;
        $currentBlock = [];

        // Parse into headers and voice blocks
        foreach ($lines as $line) {
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

        // Reconstruct tune
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
    /**
     * Parse a V: line and return an AbcVoice using InstrumentVoiceFactory
     * Example V: line: V:T name="Trumpet" sname="Trumpet" stem=up gstem=up octave=0 transpose=0 clef="treble"
     */
    public static function parseVoiceLine($line) {
        if (!preg_match('/^V:([^\s]+)(.*)$/', $line, $m)) {
            return null;
        }
        $indicator = $m[1];
        $params = $m[2];
        $name = '';
        $sname = '';
        $stem = null;
        $gstem = null;
        $octave = 0;
        $transpose = 0;
        $callback = null;
        $clef = null;
        if (preg_match('/name="([^"]+)"/', $params, $mm)) {
            $name = $mm[1];
        }
        if (preg_match('/sname="([^"]+)"/', $params, $mm)) {
            $sname = $mm[1];
        }
        if (preg_match('/stem=([a-zA-Z]+)/', $params, $mm)) {
            $stem = $mm[1];
        }
        if (preg_match('/gstem=([a-zA-Z]+)/', $params, $mm)) {
            $gstem = $mm[1];
        }
        if (preg_match('/octave=(-?\d+)/', $params, $mm)) {
            $octave = (int)$mm[1];
        }
        if (preg_match('/transpose=(-?\d+)/', $params, $mm)) {
            $transpose = (int)$mm[1];
        }
        if (preg_match('/clef="([^"]+)"/', $params, $mm)) {
            $clef = $mm[1];
        }
        // Optionally parse callback if present
        if (preg_match('/callback=([a-zA-Z0-9_]+)/', $params, $mm)) {
            $callback = $mm[1];
        }
        return InstrumentVoiceFactory::createVoiceFromParams($indicator, $name, $sname, $stem, $gstem, $octave, $transpose, $callback, $clef);
    }
}


