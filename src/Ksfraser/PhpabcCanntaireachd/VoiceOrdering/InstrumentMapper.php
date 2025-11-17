<?php
/**
 * Instrument Mapper
 *
 * Maps voice names from ABC notation to instrument families.
 * Handles various naming conventions and patterns.
 *
 * @package Ksfraser\PhpabcCanntaireachd\VoiceOrdering
 */

namespace Ksfraser\PhpabcCanntaireachd\VoiceOrdering;

class InstrumentMapper
{
    /**
     * @var array Map of instrument patterns to families
     */
    private static $instrumentMap = [
        // Woodwinds
        'piccolo' => InstrumentFamily::WOODWINDS,
        'flute' => InstrumentFamily::WOODWINDS,
        'fl' => InstrumentFamily::WOODWINDS,
        'oboe' => InstrumentFamily::WOODWINDS,
        'ob' => InstrumentFamily::WOODWINDS,
        'english horn' => InstrumentFamily::WOODWINDS,
        'cor anglais' => InstrumentFamily::WOODWINDS,
        'clarinet' => InstrumentFamily::WOODWINDS,
        'cl' => InstrumentFamily::WOODWINDS,
        'bass clarinet' => InstrumentFamily::WOODWINDS,
        'bassoon' => InstrumentFamily::WOODWINDS,
        'bn' => InstrumentFamily::WOODWINDS,
        'contrabassoon' => InstrumentFamily::WOODWINDS,
        'saxophone' => InstrumentFamily::WOODWINDS,
        'sax' => InstrumentFamily::WOODWINDS,
        'alto sax' => InstrumentFamily::WOODWINDS,
        'tenor sax' => InstrumentFamily::WOODWINDS,
        'baritone sax' => InstrumentFamily::WOODWINDS,
        'soprano sax' => InstrumentFamily::WOODWINDS,

        // Brass
        'french horn' => InstrumentFamily::BRASS,
        'horn' => InstrumentFamily::BRASS,
        'hn' => InstrumentFamily::BRASS,
        'trumpet' => InstrumentFamily::BRASS,
        'tp' => InstrumentFamily::BRASS,
        'tpt' => InstrumentFamily::BRASS,
        'cornet' => InstrumentFamily::BRASS,
        'trombone' => InstrumentFamily::BRASS,
        'tb' => InstrumentFamily::BRASS,
        'tbn' => InstrumentFamily::BRASS,
        'bass trombone' => InstrumentFamily::BRASS,
        'tuba' => InstrumentFamily::BRASS,
        'euphonium' => InstrumentFamily::BRASS,
        'baritone' => InstrumentFamily::BRASS,

        // Percussion
        'timpani' => InstrumentFamily::PERCUSSION,
        'timp' => InstrumentFamily::PERCUSSION,
        'snare' => InstrumentFamily::PERCUSSION,
        'snare drum' => InstrumentFamily::PERCUSSION,
        'tenor' => InstrumentFamily::PERCUSSION,
        'tenor drum' => InstrumentFamily::PERCUSSION,
        'bass drum' => InstrumentFamily::PERCUSSION,
        'cymbals' => InstrumentFamily::PERCUSSION,
        'triangle' => InstrumentFamily::PERCUSSION,
        'tambourine' => InstrumentFamily::PERCUSSION,
        'glockenspiel' => InstrumentFamily::PERCUSSION,
        'xylophone' => InstrumentFamily::PERCUSSION,
        'marimba' => InstrumentFamily::PERCUSSION,
        'vibraphone' => InstrumentFamily::PERCUSSION,
        'percussion' => InstrumentFamily::PERCUSSION,
        'drums' => InstrumentFamily::PERCUSSION,

        // Strings
        'violin' => InstrumentFamily::STRINGS,
        'vln' => InstrumentFamily::STRINGS,
        'v1' => InstrumentFamily::STRINGS,
        'v2' => InstrumentFamily::STRINGS,
        'vi' => InstrumentFamily::STRINGS,
        'vii' => InstrumentFamily::STRINGS,
        'viola' => InstrumentFamily::STRINGS,
        'vla' => InstrumentFamily::STRINGS,
        'cello' => InstrumentFamily::STRINGS,
        'vc' => InstrumentFamily::STRINGS,
        'violoncello' => InstrumentFamily::STRINGS,
        'double bass' => InstrumentFamily::STRINGS,
        'contrabass' => InstrumentFamily::STRINGS,
        'bass' => InstrumentFamily::STRINGS, // Could be bass drum, context needed
        'harp' => InstrumentFamily::STRINGS,

        // Keyboards
        'piano' => InstrumentFamily::KEYBOARDS,
        'pno' => InstrumentFamily::KEYBOARDS,
        'organ' => InstrumentFamily::KEYBOARDS,
        'harpsichord' => InstrumentFamily::KEYBOARDS,
        'celesta' => InstrumentFamily::KEYBOARDS,
        'synthesizer' => InstrumentFamily::KEYBOARDS,
        'synth' => InstrumentFamily::KEYBOARDS,

        // Vocals
        'soprano' => InstrumentFamily::VOCALS,
        'alto' => InstrumentFamily::VOCALS,
        'voice' => InstrumentFamily::VOCALS,
        'vocals' => InstrumentFamily::VOCALS,

        // Bagpipes
        'bagpipes' => InstrumentFamily::BAGPIPES,
        'bagpipe' => InstrumentFamily::BAGPIPES,
        'pipes' => InstrumentFamily::BAGPIPES,
        'highland bagpipes' => InstrumentFamily::BAGPIPES,
        'great highland bagpipes' => InstrumentFamily::BAGPIPES,
        'uilleann pipes' => InstrumentFamily::BAGPIPES,
        'smallpipes' => InstrumentFamily::BAGPIPES,
        'border pipes' => InstrumentFamily::BAGPIPES,

        // Common generic names
        'melody' => InstrumentFamily::OTHER,
        'harmony' => InstrumentFamily::OTHER,
        'accompaniment' => InstrumentFamily::OTHER,
        'accomp' => InstrumentFamily::OTHER,
    ];

    /**
     * Map a voice name to an instrument family
     *
     * @param string $voiceName Voice name from ABC V: header
     * @return string Instrument family constant
     */
    public static function mapToFamily(string $voiceName): string
    {
        $normalized = strtolower(trim($voiceName));

        // Direct match
        if (isset(self::$instrumentMap[$normalized])) {
            return self::$instrumentMap[$normalized];
        }

        // Pattern matching for common variations
        foreach (self::$instrumentMap as $pattern => $family) {
            if (strpos($normalized, $pattern) !== false) {
                return $family;
            }
        }

        // Special handling for numbered instruments (Violin 1, Violin II, etc.)
        if (preg_match('/^(violin|vln|v)[\s\-_]*(i{1,3}|[12])/i', $normalized)) {
            return InstrumentFamily::STRINGS;
        }

        // Special handling for tenor (could be tenor drum or tenor voice)
        if ($normalized === 'tenor' || strpos($normalized, 'tenor') !== false) {
            // If it has drum/percussion context, it's percussion
            if (strpos($normalized, 'drum') !== false || strpos($normalized, 'perc') !== false) {
                return InstrumentFamily::PERCUSSION;
            }
            // If it's a vocal context or standalone, could be vocals or percussion
            // Default to percussion for bagpipe bands
            return InstrumentFamily::PERCUSSION;
        }

        // Default to OTHER for unrecognized instruments
        return InstrumentFamily::OTHER;
    }

    /**
     * Get the specific instrument name normalized
     *
     * @param string $voiceName Voice name from ABC V: header
     * @return string Normalized instrument name
     */
    public static function normalizeInstrumentName(string $voiceName): string
    {
        $normalized = strtolower(trim($voiceName));

        // Handle numbered instruments
        $normalized = preg_replace('/[\s\-_]+/', ' ', $normalized);
        $normalized = preg_replace('/\s+/', ' ', $normalized);

        // Normalize roman numerals to arabic
        $normalized = str_replace([' i ', ' ii ', ' iii ', ' iv '], [' 1 ', ' 2 ', ' 3 ', ' 4 '], ' ' . $normalized . ' ');
        $normalized = trim($normalized);

        return $normalized;
    }

    /**
     * Add a custom instrument mapping
     *
     * @param string $instrumentPattern Instrument name pattern
     * @param string $family Instrument family constant
     * @return void
     */
    public static function addMapping(string $instrumentPattern, string $family): void
    {
        $normalized = strtolower(trim($instrumentPattern));
        self::$instrumentMap[$normalized] = $family;
    }

    /**
     * Get all instrument mappings
     *
     * @return array Map of patterns to families
     */
    public static function getAllMappings(): array
    {
        return self::$instrumentMap;
    }
}
