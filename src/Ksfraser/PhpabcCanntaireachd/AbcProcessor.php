<?php
namespace Ksfraser\PhpabcCanntaireachd;

use Ksfraser\PhpabcCanntaireachd\HeaderExtractorTrait;
use Ksfraser\PhpabcCanntaireachd\Voices\InstrumentVoiceFactory;
use Ksfraser\PhpabcCanntaireachd\Log\FlowLog;

class AbcProcessor {
    /**
     * Stub for detectVoices to prevent pipeline errors.
     * @param array $lines
     * @return array
     */
    public static function detectVoices($lines) {
        $hasMelody = false;
        $hasBagpipes = false;
        
        foreach ($lines as $line) {
            $trimmed = trim($line);
            // Check for standalone V: headers
            if (preg_match('/^V:\s*([Mm]|[Mm]elody)\b/i', $trimmed)) {
                $hasMelody = true;
            } elseif (preg_match('/^V:\s*([Bb]agpipes?|[Pp]ipes?|[Pp])\b/i', $trimmed)) {
                $hasBagpipes = true;
            }
            // Check for inline voice markers
            if (preg_match('/^\[V:([Mm]|[Mm]elody)\]/i', $trimmed)) {
                $hasMelody = true;
            } elseif (preg_match('/^\[V:([Bb]agpipes?|[Pp]ipes?|[Pp])\]/i', $trimmed)) {
                $hasBagpipes = true;
            }
        }
        
        return [$hasMelody, $hasBagpipes];
    }
    /**
     * Public static process method for integration and edge-case tests.
     * Accepts ABC string and a dictionary (array or TokenDictionary), returns result array.
     * @param string $abcContent
     * @param array|TokenDictionary $dict
     * @param array|null $headerTable
     * @return array
     */
    public static function process($abcContent, $dict, $headerTable = null, $convert = false, $logFlow = false) {
        if (is_array($dict)) {
            $dictObj = new TokenDictionary();
            $dictObj->prepopulate($dict);
        } else {
            $dictObj = $dict;
        }
        $lines = preg_split('/\r?\n/', $abcContent);
        $tunes = self::splitTunes($lines);
        $allLines = [];
        $allCanntDiff = [];
        $allErrors = [];
        $passes = [
            new \Ksfraser\PhpabcCanntaireachd\AbcTuneNumberValidatorPass(),
            new \Ksfraser\PhpabcCanntaireachd\AbcLyricsPass($dictObj),
            new \Ksfraser\PhpabcCanntaireachd\AbcVoicePass(), // Copy melody to Bagpipes if needed
            new \Ksfraser\PhpabcCanntaireachd\AbcCanntaireachdPass($dictObj),
            new \Ksfraser\PhpabcCanntaireachd\AbcVoiceOrderPass(),
            new \Ksfraser\PhpabcCanntaireachd\AbcTimingValidator(),
            new \Ksfraser\PhpabcCanntaireachd\AbcFormattingPass()
        ];
        $pipeline = new \Ksfraser\PhpabcCanntaireachd\AbcProcessingPipeline($passes);
        $headerFields = $headerTable ?? [];
        $suggestions = [];
        try {
            if ($logFlow) {
                FlowLog::log('AbcProcessor::process ENTRY', true);
            }
            foreach ($tunes as $tuneLines) {
                $result = $pipeline->run($tuneLines, $headerFields, $suggestions, $logFlow);
                if (!empty($result['lines'])) {
                    // Ensure a blank line between tunes except before the first
                    if (!empty($allLines)) {
                        $allLines[] = '';
                    }
                    $allLines = array_merge($allLines, $result['lines']);
                }
                if (!empty($result['canntDiff'])) {
                    $allCanntDiff = array_merge($allCanntDiff, $result['canntDiff']);
                }
                if (!empty($result['errors'])) {
                    $allErrors = array_merge($allErrors, $result['errors']);
                }
            }
            if ($logFlow) {
                FlowLog::log('AbcProcessor::process EXIT', true);
            }
        } catch (\Exception $ex) {
            if ($logFlow) {
                FlowLog::log('AbcProcessor::process EXCEPTION: ' . $ex->getMessage(), true);
            }
            $allErrors[] = $ex->getMessage();
        }
        return [
            'lines' => $allLines,
            'canntDiff' => $allCanntDiff,
            'errors' => $allErrors
        ];
    }
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
                // (removed duplicate copyMelodyToBagpipesInTune)
     */
     
    /**
     * Refactored: Copy melody to bagpipes using context class for SRP.
     */
    /**
     * Stub for copyMelodyToBagpipes to prevent pipeline errors.
     * @param array $lines
     * @param mixed $hasMelody
     * @param mixed $hasBagpipes
     * @return array
     */
    public static function copyMelodyToBagpipes($lines, $hasMelody, $hasBagpipes) {
        FlowLog::log('AbcProcessor::copyMelodyToBagpipes called', true);
        // Create a separate Bagpipes voice with copied melody
        if (!$hasMelody || $hasBagpipes) {
            // No melody to copy, or bagpipes already exists
            return $lines;
        }
        
        $melodyMusicLines = [];
        $insertBeforeIndex = -1;
        $inMelodyVoice = false;
        
        // Find melody lines and position to insert Bagpipes
        foreach ($lines as $idx => $line) {
            $trimmed = trim($line);
            
            // Track V:M or V:Melody headers
            if (preg_match('/^V:\s*([Mm]|[Mm]elody)\b/i', $trimmed)) {
                $inMelodyVoice = true;
                continue;
            } elseif (preg_match('/^V:/i', $trimmed)) {
                $inMelodyVoice = false;
                continue;
            }
            
            // Handle inline [V:M] markers with music
            if (preg_match('/^\[V:([Mm]|[Mm]elody)\](.*)/i', $trimmed, $matches)) {
                $inMelodyVoice = true;
                if ($insertBeforeIndex === -1) {
                    $insertBeforeIndex = $idx; // Insert Bagpipes section before first [V:M] line
                }
                // Extract music (everything after the marker)
                if (!empty($matches[2])) {
                    $melodyMusicLines[] = $matches[2];
                }
                continue;
            } elseif (preg_match('/^\[V:/i', $trimmed)) {
                $inMelodyVoice = false;
                continue;
            }
            
            // Collect music lines that belong to melody voice
            if ($inMelodyVoice && $trimmed !== '' && !preg_match('/^[A-Z%]:/i', $trimmed)) {
                if ($insertBeforeIndex === -1) {
                    $insertBeforeIndex = $idx;
                }
                $melodyMusicLines[] = $line;
            }
        }
        
        // If we found melody music, insert V:Bagpipes section
        if (!empty($melodyMusicLines) && $insertBeforeIndex >= 0) {
            $bagpipesSection = [];
            $bagpipesSection[] = 'V:Bagpipes name="Bagpipes" sname="Bagpipes"';
            foreach ($melodyMusicLines as $musicLine) {
                $bagpipesSection[] = $musicLine;
            }
            
            // Insert before the first melody music line
            array_splice($lines, $insertBeforeIndex, 0, $bagpipesSection);
        }
        
        FlowLog::log('AbcProcessor::copyMelodyToBagpipes: copied melody to Bagpipes voice', true);
        return $lines;
    }

    /**
     * Stub for validateCanntaireachd to prevent pipeline errors.
     * @param array $lines
     * @param array &$canntDiff
     * @return array
     */
    public static function validateCanntaireachd($lines, &$canntDiff) {
        FlowLog::log('AbcProcessor::validateCanntaireachd called', true);
        // TODO: Implement real logic
        $canntDiff = [];
        return $lines;
    }

    /**
     * Stub for reorderVoices to prevent pipeline errors.
     * @param array $lines
     * @return array
     */
    public static function reorderVoices($lines) {
        FlowLog::log('AbcProcessor::reorderVoices called', true);
        // TODO: Implement real logic
        return $lines;
    }

    /**
     * Stub for handleLyrics to prevent pipeline errors.
     * @param array $lines
     * @param mixed $dict
     * @param array &$lyricsWords
     * @return array
     */
    public static function handleLyrics($lines, $dict, &$lyricsWords) {
        FlowLog::log('AbcProcessor::handleLyrics called', true);
        // TODO: Implement real logic
        $lyricsWords = [];
        return $lines;
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
    
    // (removed broken copyMelodyToBagpipesInTune implementation)

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
    // (removed broken/partial code at end of file)
}


