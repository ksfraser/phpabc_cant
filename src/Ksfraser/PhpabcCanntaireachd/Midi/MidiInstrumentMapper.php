<?php
namespace Ksfraser\PhpabcCanntaireachd;

/**
 * MIDI instrument mapping and validation
 */
class MidiInstrumentMapper
{
    private static $instrumentMap = [
        // Piano
        0 => ['name' => 'Acoustic Grand Piano', 'short' => 'Piano'],
        1 => ['name' => 'Bright Acoustic Piano', 'short' => 'Piano'],
        2 => ['name' => 'Electric Grand Piano', 'short' => 'Piano'],
        3 => ['name' => 'Honky-tonk Piano', 'short' => 'Piano'],
        4 => ['name' => 'Electric Piano 1', 'short' => 'EPiano'],
        5 => ['name' => 'Electric Piano 2', 'short' => 'EPiano'],
        6 => ['name' => 'Harpsichord', 'short' => 'Harpsichord'],
        7 => ['name' => 'Clavinet', 'short' => 'Clavinet'],

        // Chromatic Percussion
        8 => ['name' => 'Celesta', 'short' => 'Celesta'],
        9 => ['name' => 'Glockenspiel', 'short' => 'Glock'],
        10 => ['name' => 'Music Box', 'short' => 'MusicBox'],
        11 => ['name' => 'Vibraphone', 'short' => 'Vibes'],
        12 => ['name' => 'Marimba', 'short' => 'Marimba'],
        13 => ['name' => 'Xylophone', 'short' => 'Xylophone'],
        14 => ['name' => 'Tubular Bells', 'short' => 'Bells'],
        15 => ['name' => 'Dulcimer', 'short' => 'Dulcimer'],

        // Organ
        16 => ['name' => 'Drawbar Organ', 'short' => 'Organ'],
        17 => ['name' => 'Percussive Organ', 'short' => 'Organ'],
        18 => ['name' => 'Rock Organ', 'short' => 'Organ'],
        19 => ['name' => 'Church Organ', 'short' => 'Organ'],
        20 => ['name' => 'Reed Organ', 'short' => 'Organ'],
        21 => ['name' => 'Accordion', 'short' => 'Accordion'],
        22 => ['name' => 'Harmonica', 'short' => 'Harmonica'],
        23 => ['name' => 'Tango Accordion', 'short' => 'Accordion'],

        // Guitar
        24 => ['name' => 'Acoustic Guitar (nylon)', 'short' => 'Guitar'],
        25 => ['name' => 'Acoustic Guitar (steel)', 'short' => 'Guitar'],
        26 => ['name' => 'Electric Guitar (jazz)', 'short' => 'Guitar'],
        27 => ['name' => 'Electric Guitar (clean)', 'short' => 'Guitar'],
        28 => ['name' => 'Electric Guitar (muted)', 'short' => 'Guitar'],
        29 => ['name' => 'Overdriven Guitar', 'short' => 'Guitar'],
        30 => ['name' => 'Distortion Guitar', 'short' => 'Guitar'],
        31 => ['name' => 'Guitar Harmonics', 'short' => 'Guitar'],

        // Bass
        32 => ['name' => 'Acoustic Bass', 'short' => 'Bass'],
        33 => ['name' => 'Electric Bass (finger)', 'short' => 'Bass'],
        34 => ['name' => 'Electric Bass (pick)', 'short' => 'Bass'],
        35 => ['name' => 'Fretless Bass', 'short' => 'Bass'],
        36 => ['name' => 'Slap Bass 1', 'short' => 'Bass'],
        37 => ['name' => 'Slap Bass 2', 'short' => 'Bass'],
        38 => ['name' => 'Synth Bass 1', 'short' => 'Bass'],
        39 => ['name' => 'Synth Bass 2', 'short' => 'Bass'],

        // Strings
        40 => ['name' => 'Violin', 'short' => 'Violin'],
        41 => ['name' => 'Viola', 'short' => 'Viola'],
        42 => ['name' => 'Cello', 'short' => 'Cello'],
        43 => ['name' => 'Contrabass', 'short' => 'Bass'],
        44 => ['name' => 'Tremolo Strings', 'short' => 'Strings'],
        45 => ['name' => 'Pizzicato Strings', 'short' => 'Strings'],
        46 => ['name' => 'Orchestral Harp', 'short' => 'Harp'],
        47 => ['name' => 'Timpani', 'short' => 'Timpani'],

        // Ensemble
        48 => ['name' => 'String Ensemble 1', 'short' => 'Strings'],
        49 => ['name' => 'String Ensemble 2', 'short' => 'Strings'],
        50 => ['name' => 'Synth Strings 1', 'short' => 'Strings'],
        51 => ['name' => 'Synth Strings 2', 'short' => 'Strings'],
        52 => ['name' => 'Choir Aahs', 'short' => 'Choir'],
        53 => ['name' => 'Voice Oohs', 'short' => 'Voice'],
        54 => ['name' => 'Synth Choir', 'short' => 'Choir'],
        55 => ['name' => 'Orchestra Hit', 'short' => 'Orchestra'],

        // Brass
        56 => ['name' => 'Trumpet', 'short' => 'Trumpet'],
        57 => ['name' => 'Trombone', 'short' => 'Trombone'],
        58 => ['name' => 'Tuba', 'short' => 'Tuba'],
        59 => ['name' => 'Muted Trumpet', 'short' => 'Trumpet'],
        60 => ['name' => 'French Horn', 'short' => 'Horn'],
        61 => ['name' => 'Brass Section', 'short' => 'Brass'],
        62 => ['name' => 'Synth Brass 1', 'short' => 'Brass'],
        63 => ['name' => 'Synth Brass 2', 'short' => 'Brass'],

        // Reed
        64 => ['name' => 'Soprano Sax', 'short' => 'Sax'],
        65 => ['name' => 'Alto Sax', 'short' => 'Sax'],
        66 => ['name' => 'Tenor Sax', 'short' => 'Sax'],
        67 => ['name' => 'Baritone Sax', 'short' => 'Sax'],
        68 => ['name' => 'Oboe', 'short' => 'Oboe'],
        69 => ['name' => 'English Horn', 'short' => 'Horn'],
        70 => ['name' => 'Bassoon', 'short' => 'Bassoon'],
        71 => ['name' => 'Clarinet', 'short' => 'Clarinet'],

        // Pipe
        72 => ['name' => 'Piccolo', 'short' => 'Piccolo'],
        73 => ['name' => 'Flute', 'short' => 'Flute'],
        74 => ['name' => 'Recorder', 'short' => 'Recorder'],
        75 => ['name' => 'Pan Flute', 'short' => 'Flute'],
        76 => ['name' => 'Blown Bottle', 'short' => 'Bottle'],
        77 => ['name' => 'Shakuhachi', 'short' => 'Shakuhachi'],
        78 => ['name' => 'Whistle', 'short' => 'Whistle'],
        79 => ['name' => 'Ocarina', 'short' => 'Ocarina'],

        // Synth Lead
        80 => ['name' => 'Lead 1 (square)', 'short' => 'Lead'],
        81 => ['name' => 'Lead 2 (sawtooth)', 'short' => 'Lead'],
        82 => ['name' => 'Lead 3 (calliope)', 'short' => 'Lead'],
        83 => ['name' => 'Lead 4 (chiff)', 'short' => 'Lead'],
        84 => ['name' => 'Lead 5 (charang)', 'short' => 'Lead'],
        85 => ['name' => 'Lead 6 (voice)', 'short' => 'Lead'],
        86 => ['name' => 'Lead 7 (fifths)', 'short' => 'Lead'],
        87 => ['name' => 'Lead 8 (bass + lead)', 'short' => 'Lead'],

        // Synth Pad
        88 => ['name' => 'Pad 1 (new age)', 'short' => 'Pad'],
        89 => ['name' => 'Pad 2 (warm)', 'short' => 'Pad'],
        90 => ['name' => 'Pad 3 (polysynth)', 'short' => 'Pad'],
        91 => ['name' => 'Pad 4 (choir)', 'short' => 'Pad'],
        92 => ['name' => 'Pad 5 (bowed)', 'short' => 'Pad'],
        93 => ['name' => 'Pad 6 (metallic)', 'short' => 'Pad'],
        94 => ['name' => 'Pad 7 (halo)', 'short' => 'Pad'],
        95 => ['name' => 'Pad 8 (sweep)', 'short' => 'Pad'],

        // Synth Effects
        96 => ['name' => 'FX 1 (rain)', 'short' => 'FX'],
        97 => ['name' => 'FX 2 (soundtrack)', 'short' => 'FX'],
        98 => ['name' => 'FX 3 (crystal)', 'short' => 'FX'],
        99 => ['name' => 'FX 4 (atmosphere)', 'short' => 'FX'],
        100 => ['name' => 'FX 5 (brightness)', 'short' => 'FX'],
        101 => ['name' => 'FX 6 (goblins)', 'short' => 'FX'],
        102 => ['name' => 'FX 7 (echoes)', 'short' => 'FX'],
        103 => ['name' => 'FX 8 (sci-fi)', 'short' => 'FX'],

        // Ethnic
        104 => ['name' => 'Sitar', 'short' => 'Sitar'],
        105 => ['name' => 'Banjo', 'short' => 'Banjo'],
        106 => ['name' => 'Shamisen', 'short' => 'Shamisen'],
        107 => ['name' => 'Koto', 'short' => 'Koto'],
        108 => ['name' => 'Kalimba', 'short' => 'Kalimba'],
        109 => ['name' => 'Bagpipe', 'short' => 'Bagpipe'],
        110 => ['name' => 'Fiddle', 'short' => 'Fiddle'],
        111 => ['name' => 'Shanai', 'short' => 'Shanai'],

        // Percussive
        112 => ['name' => 'Tinkle Bell', 'short' => 'Bell'],
        113 => ['name' => 'Agogo', 'short' => 'Agogo'],
        114 => ['name' => 'Steel Drums', 'short' => 'SteelDrum'],
        115 => ['name' => 'Woodblock', 'short' => 'Woodblock'],
        116 => ['name' => 'Taiko Drum', 'short' => 'Taiko'],
        117 => ['name' => 'Melodic Tom', 'short' => 'Tom'],
        118 => ['name' => 'Synth Drum', 'short' => 'Drum'],
        119 => ['name' => 'Reverse Cymbal', 'short' => 'Cymbal'],

        // Sound Effects
        120 => ['name' => 'Guitar Fret Noise', 'short' => 'FX'],
        121 => ['name' => 'Breath Noise', 'short' => 'FX'],
        122 => ['name' => 'Seashore', 'short' => 'FX'],
        123 => ['name' => 'Bird Tweet', 'short' => 'FX'],
        124 => ['name' => 'Telephone Ring', 'short' => 'FX'],
        125 => ['name' => 'Helicopter', 'short' => 'FX'],
        126 => ['name' => 'Applause', 'short' => 'FX'],
        127 => ['name' => 'Gunshot', 'short' => 'FX'],
    ];

    /**
     * Get instrument info by program number
     */
    public static function getInstrument(int $program): ?array
    {
        return self::$instrumentMap[$program] ?? null;
    }

    /**
     * Get instrument name by program number
     */
    public static function getInstrumentName(int $program): string
    {
        $instrument = self::getInstrument($program);
        return $instrument ? $instrument['name'] : "Program $program";
    }

    /**
     * Get short instrument name by program number
     */
    public static function getInstrumentShortName(int $program): string
    {
        $instrument = self::getInstrument($program);
        return $instrument ? $instrument['short'] : "P$program";
    }

    /**
     * Find program number by instrument name (partial match)
     */
    public static function findProgramByName(string $name): ?int
    {
        $name = strtolower($name);
        foreach (self::$instrumentMap as $program => $instrument) {
            if (strtolower($instrument['name']) === $name ||
                strtolower($instrument['short']) === $name) {
                return $program;
            }
        }
        return null;
    }
}
