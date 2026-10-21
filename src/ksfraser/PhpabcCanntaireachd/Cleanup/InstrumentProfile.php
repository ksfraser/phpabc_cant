<?php

declare(strict_types=1);

namespace ksfraser\PhpabcCanntaireachd\Cleanup;

/**
 * The attributes a voice should have: its name, its stems, its clef and its
 * transposition.
 *
 * @package ksfraser\PhpabcCanntaireachd\Cleanup
 *
 * @since 2.2.0
 */
class InstrumentProfile
{
    /** @var string The voice indicator, e.g. "M" or "Bagpipes". */
    private string $indicator;

    /** @var string The name of the instrument, e.g. "Melody". */
    private string $name;

    /** @var string The short name, e.g. "ABCD-D". */
    private string $sname;

    /** @var string The stem of the notes: up, down or auto. */
    private string $stem = 'down';

    /** @var string The stem of the grace notes: up, down or auto. */
    private string $gstem = 'up';

    /** @var string|null The clef, e.g. "treble", "bass" or "perc". */
    private ?string $clef = null;

    /** @var int The octave transposition. */
    private int $octave = 0;

    /** @var int The transposition in semitones. */
    private int $transpose = 0;

    /** @var int|null The number of staff lines, for a percussion staff. */
    private ?int $stafflines = null;

    /** @var string|null The note on the middle line of a percussion staff. */
    private ?string $middle = null;

    /** @var bool Is this one of the pipe band drums? */
    private bool $pipeBandDrum = false;

    /**
     * @param string $indicator
     * @param string $name
     * @param string $sname
     */
    public function __construct(string $indicator, string $name, string $sname = '')
    {
        $this->indicator = $indicator;
        $this->name = $name;
        $this->sname = $sname !== '' ? $sname : $name;
    }

    public function getIndicator(): string
    {
        return $this->indicator;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSname(): string
    {
        return $this->sname;
    }

    public function getStem(): string
    {
        return $this->stem;
    }

    public function setStem(string $stem): self
    {
        $this->stem = $stem;
        return $this;
    }

    public function getGstem(): string
    {
        return $this->gstem;
    }

    public function setGstem(string $gstem): self
    {
        $this->gstem = $gstem;
        return $this;
    }

    public function getClef(): ?string
    {
        return $this->clef;
    }

    public function setClef(?string $clef): self
    {
        $this->clef = $clef;
        return $this;
    }

    public function getOctave(): int
    {
        return $this->octave;
    }

    public function setOctave(int $octave): self
    {
        $this->octave = $octave;
        return $this;
    }

    public function getTranspose(): int
    {
        return $this->transpose;
    }

    public function setTranspose(int $transpose): self
    {
        $this->transpose = $transpose;
        return $this;
    }

    public function getStafflines(): ?int
    {
        return $this->stafflines;
    }

    public function setStafflines(?int $stafflines): self
    {
        $this->stafflines = $stafflines;
        return $this;
    }

    public function getMiddle(): ?string
    {
        return $this->middle;
    }

    public function setMiddle(?string $middle): self
    {
        $this->middle = $middle;
        return $this;
    }

    public function isPipeBandDrum(): bool
    {
        return $this->pipeBandDrum;
    }

    public function setPipeBandDrum(bool $pipeBandDrum): self
    {
        $this->pipeBandDrum = $pipeBandDrum;
        return $this;
    }
}
