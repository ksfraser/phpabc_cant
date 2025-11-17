<?php
/**
 * Instrument Family Classification
 *
 * Categorizes musical instruments into families for orchestral score ordering
 * and transpose mode determination.
 *
 * @package Ksfraser\PhpabcCanntaireachd\VoiceOrdering
 */

namespace Ksfraser\PhpabcCanntaireachd\VoiceOrdering;

class InstrumentFamily
{
    // Instrument family constants with orchestral score order priority
    const WOODWINDS = 'woodwinds';          // Priority 1
    const BRASS = 'brass';                   // Priority 2
    const PERCUSSION = 'percussion';         // Priority 3
    const STRINGS = 'strings';               // Priority 4
    const KEYBOARDS = 'keyboards';           // Priority 5
    const VOCALS = 'vocals';                 // Priority 6
    const BAGPIPES = 'bagpipes';             // Priority 7 (can also be in woodwinds)
    const OTHER = 'other';                   // Priority 99 (unknown)

    /**
     * Get orchestral score ordering priority for a family
     *
     * Lower numbers appear first in the score
     *
     * @param string $family Instrument family name
     * @return int Priority (1-99)
     */
    public static function getPriority(string $family): int
    {
        $priorities = [
            self::WOODWINDS => 1,
            self::BRASS => 2,
            self::PERCUSSION => 3,
            self::STRINGS => 4,
            self::KEYBOARDS => 5,
            self::VOCALS => 6,
            self::BAGPIPES => 7,
            self::OTHER => 99,
        ];

        return $priorities[strtolower($family)] ?? 99;
    }

    /**
     * Get all defined instrument families
     *
     * @return string[] Array of family names
     */
    public static function getAllFamilies(): array
    {
        return [
            self::WOODWINDS,
            self::BRASS,
            self::PERCUSSION,
            self::STRINGS,
            self::KEYBOARDS,
            self::VOCALS,
            self::BAGPIPES,
            self::OTHER,
        ];
    }

    /**
     * Check if a family is valid
     *
     * @param string $family Family name to check
     * @return bool True if valid family
     */
    public static function isValid(string $family): bool
    {
        return in_array(strtolower($family), self::getAllFamilies(), true);
    }

    /**
     * Get specific instrument ordering within woodwinds family
     *
     * @return string[] Ordered array of woodwind instrument names
     */
    public static function getWoodwindOrder(): array
    {
        return [
            'piccolo',
            'flute',
            'oboe',
            'english horn',
            'clarinet',
            'bass clarinet',
            'bassoon',
            'contrabassoon',
            'saxophone', // alto, tenor, baritone
        ];
    }

    /**
     * Get specific instrument ordering within brass family
     *
     * @return string[] Ordered array of brass instrument names
     */
    public static function getBrassOrder(): array
    {
        return [
            'french horn',
            'horn',
            'trumpet',
            'cornet',
            'trombone',
            'bass trombone',
            'tuba',
            'euphonium',
            'baritone',
        ];
    }

    /**
     * Get specific instrument ordering within strings family
     *
     * @return string[] Ordered array of string instrument names
     */
    public static function getStringsOrder(): array
    {
        return [
            'violin i',
            'violin 1',
            'violin ii',
            'violin 2',
            'viola',
            'cello',
            'violoncello',
            'double bass',
            'bass',
            'contrabass',
            'harp',
        ];
    }

    /**
     * Get specific instrument ordering within percussion family
     *
     * @return string[] Ordered array of percussion instrument names
     */
    public static function getPercussionOrder(): array
    {
        return [
            'timpani',
            'snare',
            'snare drum',
            'tenor',
            'tenor drum',
            'bass drum',
            'bass',
            'cymbals',
            'triangle',
            'tambourine',
            'glockenspiel',
            'xylophone',
            'marimba',
            'vibraphone',
        ];
    }

    /**
     * Get specific instrument ordering within vocals
     *
     * @return string[] Ordered array of vocal part names
     */
    public static function getVocalsOrder(): array
    {
        return [
            'soprano',
            'alto',
            'tenor',
            'bass',
            'voice',
            'vocals',
        ];
    }

    /**
     * Get specific instrument ordering within bagpipes family
     *
     * @return string[] Ordered array of bagpipe types
     */
    public static function getBagpipesOrder(): array
    {
        return [
            'bagpipes',
            'highland bagpipes',
            'great highland bagpipes',
            'uilleann pipes',
            'smallpipes',
            'border pipes',
        ];
    }

    /**
     * Get specific instrument ordering within keyboards family
     *
     * @return string[] Ordered array of keyboard instrument names
     */
    public static function getKeyboardsOrder(): array
    {
        return [
            'piano',
            'organ',
            'harpsichord',
            'celesta',
            'synthesizer',
        ];
    }
}
