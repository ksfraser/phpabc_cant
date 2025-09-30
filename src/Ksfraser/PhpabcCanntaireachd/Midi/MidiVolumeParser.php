<?php
namespace Ksfraser\PhpabcCanntaireachd\Midi;

use Ksfraser\PhpabcCanntaireachd\AbcLineParser;
use Ksfraser\PhpabcCanntaireachd\AbcMidiLine;
use Ksfraser\PhpabcCanntaireachd\AbcTune;

/**
 * Parser for %%MIDI vol directives
 */
class MidiVolumeParser implements AbcLineParser
{
    // Dynamic marking to MIDI volume mapping
    private static $dynamicMap = [
        'ppp' => 20,   // pianississimo
        'pp' => 35,    // pianissimo  
        'p' => 50,     // piano
        'mp' => 65,    // mezzo-piano
        'mf' => 80,    // mezzo-forte
        'f' => 95,     // forte
        'ff' => 110,   // fortissimo
        'fff' => 127,  // fortississimo
    ];

    /**
     * @param mixed $line
     * @return bool
     */
    public function canParse($line) {
        return preg_match('/^%%MIDI\s+vol(?:ume)?\s+/i', trim($line));
    }

    /**
     * @param mixed $line
     * @param mixed $tune
     * @return bool
     */
    public function parse($line, $tune) {
        if (!preg_match('/^%%MIDI\s+vol(?:ume)?\s+(.+?)(?:\s+%.*)?$/i', trim($line), $matches)) {
            return false;
        }

        $volumeSpec = trim($matches[1]);
        $volume = null;
        $originalSpec = $volumeSpec;

        // Try to parse as numeric value first
        if (is_numeric($volumeSpec)) {
            $volume = (int)$volumeSpec;
        } else {
            // Try to match dynamic marking
            $volumeSpec = strtolower($volumeSpec);
            if (isset(self::$dynamicMap[$volumeSpec])) {
                $volume = self::$dynamicMap[$volumeSpec];
            } else {
                // Unknown dynamic marking, try to extract number if embedded
                if (preg_match('/(\d+)/', $volumeSpec, $numMatches)) {
                    $volume = (int)$numMatches[1];
                }
            }
        }

        // Validate and clamp volume
        if ($volume === null || $volume < 0) {
            $volume = 80; // Default to mf
        } elseif ($volume > 127) {
            $volume = 127; // Max MIDI volume
        }

        // Create corrected line with numeric volume
        $correctedLine = "%%MIDI vol $volume";
        
        // Add original dynamic marking as comment if it was a dynamic
        if (!is_numeric($originalSpec) && isset(self::$dynamicMap[strtolower($originalSpec)])) {
            $correctedLine .= " % $originalSpec";
        }

        $tune->add(new AbcMidiLine($correctedLine));
        return true;
    }

    /**
     * @param mixed $line
     * @return bool
     */
    public function validate($line) {
        if (!preg_match('/^%%MIDI\s+vol(?:ume)?\s+(.+?)(?:\s+%.*)?$/i', trim($line), $matches)) {
            return false;
        }

        $volumeSpec = trim($matches[1]);
        
        // Valid if numeric and in range
        if (is_numeric($volumeSpec)) {
            $volume = (int)$volumeSpec;
            return $volume >= 0 && $volume <= 127;
        }
        
        // Valid if it's a known dynamic marking
        return isset(self::$dynamicMap[strtolower($volumeSpec)]);
    }
}
