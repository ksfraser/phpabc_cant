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
        // TODO: Implement real voice detection logic if needed
        if (is_array($lines)) {
            return $lines;
        }
        return [];
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
        $abcText = is_array($lines) ? implode("\n", $lines) : (string)$lines;
        // Convert lines to AbcTune, ensure Bagpipes voice, and return updated lines
        $tune = \Ksfraser\PhpabcCanntaireachd\Tune\AbcTune::parse($abcText);
        if ($tune === null) {
            FlowLog::log('AbcProcessor::copyMelodyToBagpipes: failed to parse AbcTune', true);
            return $lines;
        }
        $svc = new \Ksfraser\PhpabcCanntaireachd\TuneService(new \Ksfraser\PhpabcCanntaireachd\CanntGenerator(null));
        $svc->ensureBagpipeVoice($tune);
        // Return the tune as lines, but fallback to original if output is empty
        $rendered = $tune->renderSelf();
        if (empty(trim($rendered))) {
            FlowLog::log('AbcProcessor::copyMelodyToBagpipes: renderSelf() produced empty output, returning original lines', true);
            return $lines;
        }
        $out = preg_split('/\r?\n/', $rendered);
        FlowLog::log('AbcProcessor::copyMelodyToBagpipes: Bagpipes voice ensured and canntaireachd generated', true);
        return $out;
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


