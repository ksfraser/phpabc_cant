<?php

declare(strict_types=1);

namespace ksfraser\PhpabcCanntaireachd\Cleanup;

/**
 * The pitch of an ABC note.
 *
 * ABC writes the pitch as a letter with octave marks: "C" is the C below
 * middle C, "c" is middle C, "c'" is the C above it. This class converts
 * between that notation and a number, so that pitches can be compared and
 * transposed.
 *
 * @package ksfraser\PhpabcCanntaireachd\Cleanup
 *
 * @since 2.2.0
 */
class AbcPitch
{
    /** @var int[] The natural semitone of each letter, C = 0. */
    private const SEMITONES = ['c' => 0, 'd' => 2, 'e' => 4, 'f' => 5, 'g' => 7, 'a' => 9, 'b' => 11];

    /** @var int[] The diatonic step of each letter, C = 0. */
    private const DIATONIC = ['c' => 0, 'd' => 1, 'e' => 2, 'f' => 3, 'g' => 4, 'a' => 5, 'b' => 6];

    /** @var string The pitch letter, lower case. */
    private string $letter;

    /** @var int The octave: 4 is middle C. */
    private int $octave;

    /** @var int The semitone offset of the accidental, if any. */
    private int $accidental;

    /**
     * @param string $letter The pitch letter, e.g. "A" or "c".
     * @param string $octaveMarks The octave marks of the note.
     * @param string $accidentals The accidentals of the note.
     */
    public function __construct(string $letter = 'c', string $octaveMarks = '', string $accidentals = '')
    {
        $this->letter = strtolower($letter);

        // Middle C is "c". A capital letter is an octave lower, and each
        // apostrophe is an octave higher.
        $octave = 4;
        if ($letter !== '' && $letter === strtoupper($letter)) {
            $octave--;
        }
        $octave += substr_count($octaveMarks, "'");
        $octave -= substr_count($octaveMarks, ',');
        $this->octave = $octave;

        $this->accidental = 0;
        foreach (str_split($accidentals) as $mark) {
            $this->accidental += match ($mark) {
                '^' => 1,
                '_' => -1,
                default => 0,
            };
        }
    }

    /**
     * The pitch letter, lower case.
     */
    public function getLetter(): string
    {
        return $this->letter;
    }

    public function getOctave(): int
    {
        return $this->octave;
    }

    public function getAccidental(): int
    {
        return $this->accidental;
    }

    /**
     * The pitch as a number of semitones, where middle C is 0.
     */
    public function getSemitone(): int
    {
        if (!isset(self::SEMITONES[$this->letter])) {
            return 0;
        }
        return self::SEMITONES[$this->letter] + 12 * ($this->octave - 4) + $this->accidental;
    }

    /**
     * The pitch as a diatonic step, where middle C is 0.
     */
    public function getDiatonic(): int
    {
        if (!isset(self::DIATONIC[$this->letter])) {
            return 0;
        }
        return self::DIATONIC[$this->letter] + 7 * ($this->octave - 4);
    }

    /**
     * The pitch shifted by a number of semitones.
     *
     * @param int $semitones
     * @return AbcPitch
     */
    public function transpose(int $semitones): AbcPitch
    {
        $target = $this->getSemitone() + $semitones;

        $bestLetter = 'c';
        $bestOctave = 4;
        $bestAccidental = 0;
        $bestDistance = PHP_INT_MAX;

        foreach (self::SEMITONES as $letter => $semitone) {
            for ($octave = 1; $octave <= 8; $octave++) {
                $natural = $semitone + 12 * ($octave - 4);
                $distance = $target - $natural;
                if (abs($distance) < $bestDistance) {
                    $bestDistance = abs($distance);
                    $bestLetter = $letter;
                    $bestOctave = $octave;
                    $bestAccidental = $distance;
                }
            }
        }

        $marks = '';
        if ($bestOctave < 4) {
            $marks = str_repeat(',', 3 - $bestOctave);
        } elseif ($bestOctave > 4) {
            $marks = str_repeat("'", $bestOctave - 4);
        }
        $letter = $bestOctave < 4 ? strtoupper($bestLetter) : $bestLetter;

        $accidentals = '';
        if ($bestAccidental > 0) {
            $accidentals = '^';
        } elseif ($bestAccidental < 0) {
            $accidentals = '_';
        }

        return new self($letter, $marks, $accidentals);
    }

    /**
     * The pitch as ABC writes it: the letter, in the right case, with the
     * octave marks.
     */
    public function render(): string
    {
        $letter = $this->letter;
        $octave = $this->octave;
        $marks = '';
        if ($octave < 4) {
            $marks = str_repeat(',', 3 - $octave);
            $letter = strtoupper($letter);
        } elseif ($octave > 4) {
            $marks = str_repeat("'", $octave - 4);
        }
        return $letter . $marks;
    }

    /**
     * The natural semitone of a letter.
     */
    public static function semitoneOf(string $letter): int
    {
        return self::SEMITONES[strtolower($letter)] ?? 0;
    }
}
