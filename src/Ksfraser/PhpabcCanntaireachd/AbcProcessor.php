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
     * @enduml
     */
     
    /**
     * Refactored: Copy melody to bagpipes using context class for SRP.
     */
    // ...existing code...
    private static function copyMelodyToBagpipesInTune($lines) {
        $copier = new \Ksfraser\PhpabcCanntaireachd\Tune\MelodyToBagpipesCopier();
        return $copier->copy($lines);
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
            } else if ($hasStartedTunes && !empty($currentTune)) {
                // Add line to current tune if we've started processing tunes
                $currentTune[] = $line;
            } else if (!$hasStartedTunes && $trimmed !== '') {
                // Content before first tune - start a tune with this content
                $currentTune = [$line];
                $hasStartedTunes = true;
            } else if ($hasStartedTunes && empty($currentTune) && $trimmed !== '') {
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
        $melodyHeaderLine = null;
        $bagpipesHeaderPresent = false;
        // First pass: find melody voice and check for Bagpipes header
                $voiceId = $m[1];
                if (preg_match('/name=.*Melody/', $line)) {
                    $hasMelody = true;
                if ($voiceId === 'M' || $voiceId === 'Melody' || preg_match('/name=.*Melody/', $line)) {
                }
            }
        }

                if ($voiceId === 'Bagpipes') {
                    $bagpipesHeaderPresent = true;
            return $lines;
        }

        // Second pass: copy lines and collect melody content
        $melodyContent = [];
        foreach ($lines as $line) {
            $output = [];
            $hasMelody = false;
            $melodyVoiceId = null;
            $melodyHeaderLine = null;
            $bagpipesHeaderPresent = false;
            // First pass: find melody voice and check for Bagpipes header
            foreach ($lines as $line) {
                if (preg_match('/^V:([^
                }
                    $voiceId = $m[1];
                    if ($voiceId === 'M' || $voiceId === 'Melody' || preg_match('/name=.*Melody/', $line)) {
                        $hasMelody = true;
                        $melodyVoiceId = $voiceId;
                        $melodyHeaderLine = $line;
                    }
                    if ($voiceId === 'Bagpipes') {
                        $bagpipesHeaderPresent = true;
                    }
                }
            }
            if (!$hasMelody) {
                return $lines;
            }
            }
            if (!$inserted) {
                $output[] = $bagpipesHeader;
            }
        } else {
            $output = $lines;
        }
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
        // (Body already restored in previous patch)
    }
    
    private static function reorderVoicesInTune($lines) {
        $headers = [];
        $voiceBlocks = [];
        $currentVoice = null;
        $currentBlock = [];

        // Parse into headers and voice blocks
            foreach ($lines as $line) {
                    // Save previous voice block
        }
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
        foreach ($lines as $line) {
            $melodyHeaderLine = null;
            $bagpipesHeaderPresent = false;
            // First pass: find melody voice and check for Bagpipes header
            if (preg_match('/^V:([^\s]+)/', $line, $m)) {
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


