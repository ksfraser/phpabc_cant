<?php
namespace Ksfraser\PhpabcCanntaireachd\Transpose;

/**
 * Instrument Transpose Mapper
 * 
 * Maps instrument names to standard orchestral transpose values.
 * Handles abbreviations and variations of instrument names.
 */
class InstrumentTransposeMapper
{
    /**
     * Standard transpose values by instrument family
     * 
     * Transpose values in semitones:
     * - 0 = Concert pitch (C instruments)
     * - 2 = Bb instruments (up major 2nd)
     * - 9 = Eb instruments (up major 6th)
     * - 7 = F instruments (up perfect 5th)
     * - -3 = Eb bass instruments (down minor 3rd)
     */
    private static $transposeMap = [
        /* Concert Pitch (transpose=0) */
        'piano' => 0,
        'keyboard' => 0,
        'flute' => 0,
        'piccolo' => 0,
        'oboe' => 0,
        'bassoon' => 0,
        'contrabassoon' => 0,
        'trombone' => 0,
        'tuba' => 0,
        'euphonium' => 0,
        'violin' => 0,
        'viola' => 0,
        'cello' => 0,
        'bass' => 0,
        'doublebass' => 0,
        'contrabass' => 0,
        'guitar' => 0,
        'harp' => 0,
        'timpani' => 0,
        'percussion' => 0,
        'drums' => 0,
        'snare' => 0,
        'tenor' => 0, // Tenor drum
        'bassdrum' => 0,
        'glockenspiel' => 0,
        'xylophone' => 0,
        'marimba' => 0,
        'vibraphone' => 0,
        
        /* Bb Instruments (transpose=2, up major 2nd) */
        'trumpet' => 2,
        'cornet' => 2,
        'flugelhorn' => 2,
        'clarinet' => 2,
        'bassclarinet' => 2, // Often written at concert pitch or Bb
        'tenorsax' => 2,
        'tenorsaxophone' => 2,
        'sopranosax' => 2,
        'sopranosaxophone' => 2,
        
        /* Eb Instruments (transpose=9, up major 6th) */
        'altosax' => 9,
        'altosaxophone' => 9,
        'baritonesax' => 9,
        'baritonesaxophone' => 9,
        'ebclarinet' => 9,
        'ebhorn' => 9,
        
        /* F Instruments (transpose=7, up perfect 5th) */
        'horn' => 7,
        'frenchhorn' => 7,
        'englishhorn' => 7,
        'coranglais' => 7,
        
        /* Bagpipes (transpose=0 or 2 depending on convention) */
        'bagpipe' => 0,
        'bagpipes' => 0,
        'highlandbagpipe' => 0,
        'pipe' => 0,
        'chanter' => 0,
    ];

    /**
     * Get transpose value for an instrument
     *
     * @param string $instrumentName Instrument name
     * @return int Transpose value in semitones
     */
    public static function getTranspose(string $instrumentName): int
    {
        $normalized = self::normalizeInstrumentName($instrumentName);
        
        // Check exact match first
        if (isset(self::$transposeMap[$normalized])) {
            return self::$transposeMap[$normalized];
        }
        
        // Check for partial matches (contains)
        foreach (self::$transposeMap as $key => $transpose) {
            if (strpos($normalized, $key) !== false || strpos($key, $normalized) !== false) {
                return $transpose;
            }
        }
        
        // Default to concert pitch if unknown
        return 0;
    }

    /**
     * Normalize instrument name for matching
     *
     * @param string $name Instrument name
     * @return string Normalized name
     */
    private static function normalizeInstrumentName(string $name): string
    {
        $name = strtolower(trim($name));
        /* Remove common prefixes/suffixes */
        $name = preg_replace('/^(the|a|an)\s+/i', '', $name);
        /* Remove spaces, hyphens, underscores */
        $name = str_replace([' ', '-', '_', '.'], '', $name);
        /* Handle common abbreviations */
        $abbreviations = [
            'tpt' => 'trumpet',
            'tbn' => 'trombone',
            'cl' => 'clarinet',
            'bcl' => 'bassclarinet',
            'fl' => 'flute',
            'ob' => 'oboe',
            'bsn' => 'bassoon',
            'hn' => 'horn',
            'fhn' => 'frenchhorn',
            'sax' => 'saxophone',
            'as' => 'altosax',
            'ts' => 'tenorsax',
            'bs' => 'baritonesax',
            'ss' => 'sopranosax',
            'vln' => 'violin',
            'vla' => 'viola',
            'vc' => 'cello',
            'cb' => 'contrabass',
            'db' => 'doublebass',
            'gtr' => 'guitar',
            'pno' => 'piano',
            'kbd' => 'keyboard',
            'perc' => 'percussion',
            'timp' => 'timpani',
        ];
        
        if (isset($abbreviations[$name])) {
            return $abbreviations[$name];
        }
        
        return $name;
    }

    /**
     * Get all supported instruments
     *
     * @return array Instrument names mapped to transpose values
     */
    public static function getAllTransposes(): array
    {
        return self::$transposeMap;
    }

    /**
     * Check if an instrument is a transposing instrument
     *
     * @param string $instrumentName Instrument name
     * @return bool True if transpose != 0
     */
    public static function isTransposingInstrument(string $instrumentName): bool
    {
        return self::getTranspose($instrumentName) !== 0;
    }
}
