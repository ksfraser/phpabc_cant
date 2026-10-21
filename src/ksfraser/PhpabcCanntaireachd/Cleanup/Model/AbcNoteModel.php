<?php

declare(strict_types=1);

namespace ksfraser\PhpabcCanntaireachd\Cleanup\Model;

/**
 * One note of an ABC music line.
 *
 * A note carries everything the ABC grammar allows in front of, around and
 * behind the pitch: a grace-note group, accidentals, a chord, decorations, the
 * pitch itself with its octave marks, and the note length.
 *
 * @package ksfraser\PhpabcCanntaireachd\Cleanup\Model
 *
 * @since 2.2.0
 */
class AbcNoteModel
{
    /** @var string Grace-note group, including the braces, e.g. "{g}". */
    private string $grace = '';

    /** @var string Accidentals: any of "_", "=" and "^". */
    private string $accidentals = '';

    /** @var string Chord symbol without the quotes, e.g. "dfa". */
    private string $chord = '';

    /** @var string Decorations, e.g. "trill" from !trill!. */
    private string $decorations = '';

    /** @var string The pitch letter: a-g, A-G, or z/Z for a rest. */
    private string $pitch = '';

    /** @var string Octave marks: any of "'" and ",". */
    private string $octave = '';

    /** @var string The note length, e.g. "2", "3/2" or "". */
    private string $length = '';

    /** @var string The broken rhythm marker: "<" or ">". */
    private string $rhythm = '';

    /**
     * @param string $pitch
     * @param string $octave
     * @param string $length
     * @param string $grace
     * @param string $accidentals
     * @param string $chord
     * @param string $decorations
     * @param string $rhythm
     */
    public function __construct(
        string $pitch = '',
        string $octave = '',
        string $length = '',
        string $grace = '',
        string $accidentals = '',
        string $chord = '',
        string $decorations = '',
        string $rhythm = ''
    ) {
        $this->pitch = $pitch;
        $this->octave = $octave;
        $this->length = $length;
        $this->grace = $grace;
        $this->accidentals = $accidentals;
        $this->chord = $chord;
        $this->decorations = $decorations;
        $this->rhythm = $rhythm;
    }

    public function getPitch(): string
    {
        return $this->pitch;
    }

    public function setPitch(string $pitch): void
    {
        $this->pitch = $pitch;
    }

    public function getOctave(): string
    {
        return $this->octave;
    }

    public function setOctave(string $octave): void
    {
        $this->octave = $octave;
    }

    public function getLength(): string
    {
        return $this->length;
    }

    /**
     * The broken rhythm marker of the note: "<" or ">".
     */
    public function getRhythm(): string
    {
        return $this->rhythm;
    }

    public function setRhythm(string $rhythm): void
    {
        $this->rhythm = $rhythm;
    }

    public function setLength(string $length): void
    {
        $this->length = $length;
    }

    public function getGrace(): string
    {
        return $this->grace;
    }

    public function setGrace(string $grace): void
    {
        $this->grace = $grace;
    }

    public function getAccidentals(): string
    {
        return $this->accidentals;
    }

    public function setAccidentals(string $accidentals): void
    {
        $this->accidentals = $accidentals;
    }

    public function getChord(): string
    {
        return $this->chord;
    }

    public function setChord(string $chord): void
    {
        $this->chord = $chord;
    }

    public function getDecorations(): string
    {
        return $this->decorations;
    }

    public function setDecorations(string $decorations): void
    {
        $this->decorations = $decorations;
    }

    /**
     * Is this a rest rather than a note?
     */
    public function isRest(): bool
    {
        return $this->pitch === 'z' || $this->pitch === 'Z';
    }

    /**
     * The pitch as it appears in the dictionary: the grace group, the
     * accidentals and the pitch letter, e.g. "{g}A" or "A".
     */
    public function getDictionaryKey(): string
    {
        return $this->grace . $this->accidentals . $this->pitch;
    }

    /**
     * The note as it is written in the ABC file.
     */
    public function render(): string
    {
        $out = '';
        if ($this->grace !== '') {
            $out .= $this->grace;
        }
        if ($this->decorations !== '') {
            $out .= '!' . $this->decorations . '!';
        }
        if ($this->chord !== '') {
            $out .= '"' . $this->chord . '"';
        }
        $out .= $this->accidentals . $this->pitch . $this->octave . $this->length . $this->rhythm;
        return $out;
    }

    /**
     * A copy of this note with a different pitch letter and octave marks.
     */
    public function withPitch(string $pitch, string $octave): self
    {
        return new self(
            $pitch,
            $octave,
            $this->length,
            $this->grace,
            $this->accidentals,
            $this->chord,
            $this->decorations,
            $this->rhythm
        );
    }

    /**
     * A copy of this note with a different grace-note group.
     */
    public function withGrace(string $grace): self
    {
        return new self(
            $this->pitch,
            $this->octave,
            $this->length,
            $grace,
            $this->accidentals,
            $this->chord,
            $this->decorations,
            $this->rhythm
        );
    }
}
