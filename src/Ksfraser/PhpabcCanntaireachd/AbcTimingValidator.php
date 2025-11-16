<?php
namespace Ksfraser\PhpabcCanntaireachd;

use Ksfraser\PhpabcCanntaireachd\Log\FlowLog;
/**
 * Validates bar timing in ABC notation using M: and L: headers.
 * Adds 'TIMING' at the end of bars with issues and logs errors.
 */
class AbcTimingValidator {
    private $addTimingMarkers = false;

    /**
     * Validate bar timing for ABC content.
     * @param array $lines ABC file lines
     * @return array [lines, errors]
     */
    public function __construct($addTimingMarkers = false) {
        $this->addTimingMarkers = $addTimingMarkers;
    }

    public function validate(array $lines, $logFlow = false): array {
        FlowLog::log('AbcTimingValidator::validate ENTRY', true);
        $meter = null;
        $unit = null;
        $errors = [];
        $output = [];
        // Find M: and L: headers
        foreach ($lines as $line) {
            if (preg_match('/^M:\s*([\d\/]+)/', $line, $m)) {
                $meter = $m[1];
            }
            if (preg_match('/^L:\s*([\d\/]+)/', $line, $l)) {
                $unit = $l[1];
            }
            if ($meter && $unit) break;
        }
        // Calculate expected beats per bar
        $beatsPerBar = $this->beatsPerBar($meter, $unit);
        $inVoice = false;
        $barNum = 0;
        foreach ($lines as $line) {
            $newLine = $line;
            if (preg_match('/^V:/', $line)) {
                $inVoice = true;
                $output[] = $newLine;
                continue;
            }
            if ($inVoice && $this->isMusicLine($line)) {
                // Split line by bar, including content before first |
                $parts = preg_split('/(\|)/', $line, -1, PREG_SPLIT_DELIM_CAPTURE);
                $barNumInLine = 0;
                
                // Handle content before first | as the first bar
                if (!empty($parts) && $parts[0] !== '' && $parts[0] !== '|') {
                    $barNum++;
                    $barNumInLine++;
                    $bar = $parts[0];
                    $beats = $this->countBeats($bar, $unit, $meter);
                    $isPickup = $barNum == 1 && $beats < $beatsPerBar;
                    if (!$isPickup && $beats > $beatsPerBar) {
                        $errors[] = "Bar $barNum: Expected $beatsPerBar beats, found $beats";
                        if ($this->addTimingMarkers) {
                            $parts[0] .= rtrim($bar) . ' TIMING';
                        }
                        else
                        {
                            $parts[0] = rtrim($bar);
                        }
                    }
                }
                
                // Handle bars after |
                for ($i = 0; $i < count($parts); $i++) {
                    if ($parts[$i] === '|') {
                        $barNum++;
                        $barNumInLine++;
                        $bar = ($i+1 < count($parts)) ? $parts[$i+1] : '';
                        if (trim($bar) === '') continue; // Skip empty bars
                        $beats = $this->countBeats($bar, $unit, $meter);
                        $isPickup = $barNum == 1 && $beats < $beatsPerBar;
                        if (!$isPickup && $beats > $beatsPerBar) {
                            $errors[] = "Bar $barNum: Expected $beatsPerBar beats, found $beats";
                            if ($this->addTimingMarkers) {
                                $parts[$i+1] .= rtrim($bar) . ' TIMING';
                            } else {
                                $parts[$i+1] = rtrim($bar);
                            }
                        }
                    }
                }
                $newLine = implode('', $parts);
                $output[] = $newLine;
            } else {
                $output[] = $newLine;
            }
        }
    $result = ['lines' => $output, 'errors' => $errors];
    FlowLog::log('AbcTimingValidator::validate EXIT', true);
    return $result;
    }
    private function replaceNthBar($line, $n, $bar) {
        // Replace the nth bar in a line (1-based)
        $count = 0;
        return preg_replace_callback('/\|([^|]*)/', function($matches) use (&$count, $n, $bar) {
            $count++;
            if ($count === $n) {
                return '|' . $bar;
            }
            return $matches[0];
        }, $line);
    }
    private function beatsPerBar($meter, $unit) {
        // Return the number of beats per bar from the meter (numerator)
        if (!$meter) return 0;
        [$num, $den] = explode('/', $meter);
        return (float)$num;
    }
    private function countBeats($bar, $unit, $meter) {
        // Count notes/rests in bar, convert to beats based on meter
        if (!$unit || !$meter) return 0;
        $beats = 0;

        error_log("TIMING_LOG: Processing bar: '$bar'");

        // Parse meter and unit
        [$meterNum, $meterDen] = explode('/', $meter);
        [$unitNum, $unitDen] = explode('/', $unit);

        // Conversion factor: how many meter beats per L: unit
        $conversion = $meterDen / ($unitNum * $unitDen);
        error_log("TIMING_LOG: Meter=$meter ($meterNum/$meterDen), Unit=$unit ($unitNum/$unitDen), Conversion factor=$conversion");

        // Remove grace notes as they don't count towards timing
        $originalBar = $bar;
        $bar = preg_replace('/\{[^}]*\}/', '', $bar);
        if ($originalBar !== $bar) {
            error_log("TIMING_LOG: Removed grace notes from '$originalBar' -> '$bar'");
        }

        // Process each character in the bar
        error_log("TIMING_LOG: Processing characters:");
        for ($i = 0; $i < strlen($bar); $i++) {
            $char = $bar[$i];
            if (preg_match('/[A-Ga-gzZ_^=,\'\d\/\[\]]/', $char)) {
                error_log("TIMING_LOG:   Char '$char' at position $i: COUNTED");
            } else if ($char === ' ') {
                error_log("TIMING_LOG:   Char '$char' at position $i: IGNORED (space)");
            } else if ($char === '|') {
                error_log("TIMING_LOG:   Char '$char' at position $i: IGNORED (bar line)");
            } else {
                error_log("TIMING_LOG:   Char '$char' at position $i: IGNORED (other)");
            }
        }

        // Match notes/rests with optional accidentals, octave, and length
        // Pattern: optional accidental + note/rest + optional octave + optional length
        preg_match_all('/([_^=]?)([A-Ga-gzZ])([,,\']*)(\d+(?:\/\d+)?)?/', $bar, $matches, PREG_SET_ORDER);

        error_log("TIMING_LOG: Found " . count($matches) . " note/rest matches:");
        foreach ($matches as $index => $m) {
            $fullMatch = $m[0];
            $accidental = $m[1] ?? '';
            $note = $m[2];
            $octave = $m[3] ?? '';
            $len = $m[4] ?? '';

            error_log("TIMING_LOG:   Match " . ($index + 1) . ": '$fullMatch' (accidental='$accidental', note='$note', octave='$octave', length='$len')");

            if ($len) {
                if (strpos($len, '/') !== false) {
                    [$n, $d] = explode('/', $len);
                    $noteLength = $n / $d;
                    error_log("TIMING_LOG:     Length '$len' = $n/$d = $noteLength L: units");
                } else {
                    $noteLength = (float)$len;
                    error_log("TIMING_LOG:     Length '$len' = $noteLength L: units");
                }
            } else {
                $noteLength = 1; // Default note length = 1 L: unit
                error_log("TIMING_LOG:     No length specified, using default = $noteLength L: unit");
            }

            $noteBeats = $noteLength * $conversion;
            $beats += $noteBeats;
            error_log("TIMING_LOG:     Beats added: $noteLength * $conversion = $noteBeats (running total: $beats)");
        }

        // Handle chords [CEG] - count as single note with the length of the first note
        preg_match_all('/\[([^\]]+)\]/', $bar, $chordMatches);
        error_log("TIMING_LOG: Found " . count($chordMatches[0]) . " chord matches:");
        foreach ($chordMatches[0] as $index => $chord) {
            error_log("TIMING_LOG:   Chord " . ($index + 1) . ": '$chord'");
            // Remove the chord from bar temporarily to avoid double counting
            $bar = str_replace($chord, '', $bar);
            // Count the first note of the chord
            if (preg_match('/([_^=]?)([A-Ga-gzZ])([,,\']*)(\d+(?:\/\d+)?)?/', $chord, $chordMatch)) {
                $accidental = $chordMatch[1] ?? '';
                $note = $chordMatch[2];
                $octave = $chordMatch[3] ?? '';
                $len = $chordMatch[4] ?? '';

                error_log("TIMING_LOG:     First note in chord: accidental='$accidental', note='$note', octave='$octave', length='$len'");

                if ($len) {
                    if (strpos($len, '/') !== false) {
                        [$n, $d] = explode('/', $len);
                        $noteLength = $n / $d;
                        error_log("TIMING_LOG:       Length '$len' = $n/$d = $noteLength L: units");
                    } else {
                        $noteLength = (float)$len;
                        error_log("TIMING_LOG:       Length '$len' = $noteLength L: units");
                    }
                } else {
                    $noteLength = 1;
                    error_log("TIMING_LOG:       No length specified, using default = $noteLength L: unit");
                }

                $noteBeats = $noteLength * $conversion;
                $beats += $noteBeats;
                error_log("TIMING_LOG:       Beats added: $noteLength * $conversion = $noteBeats (running total: $beats)");
            } else {
                error_log("TIMING_LOG:       No valid note found in chord, skipping");
            }
        }

        $finalBeats = round($beats, 2);
        error_log("TIMING_LOG: BAR TOTAL: $finalBeats beats");
        return $finalBeats;
    }
    
    private function isMusicLine($line) {
        // Check if line contains actual music notation (not just comments)
        $trimmed = trim($line);
        
        // Skip empty lines
        if ($trimmed === '') {
            return false;
        }
        
        // Skip comment lines
        if (preg_match('/^%/', $trimmed)) {
            return false;
        }
        
        // Skip header lines (X:, T:, M:, L:, K:, etc.)
        if (preg_match('/^[A-Z]:/', $trimmed)) {
            return false;
        }
        
        // Skip voice definition lines
        if (preg_match('/^V:/', $trimmed)) {
            return false;
        }
        
        // Skip lyrics lines
        if (preg_match('/^w:/', $trimmed)) {
            return false;
        }
        
        // Skip MIDI and other directive lines
        if (preg_match('/^%%/', $trimmed) || preg_match('/^I:/', $trimmed)) {
            return false;
        }
        
        // Check for actual music notation: notes, rests, bar lines, chords, grace notes
        if (preg_match('/[A-Ga-gzZ]|\||\[|\]|\{|\}/', $trimmed)) {
            return true;
        }
        
        return false;
    }
}
