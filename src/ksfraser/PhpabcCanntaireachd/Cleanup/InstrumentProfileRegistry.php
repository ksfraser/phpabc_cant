<?php

declare(strict_types=1);

namespace ksfraser\PhpabcCanntaireachd\Cleanup;

/**
 * The instrument profiles of a pipe band score.
 *
 * The profiles are the attributes each instrument should have in a V: header.
 * They follow the pipe band score convention: the grace notes point up, the
 * notes point down, the pipe band drums sit on a one line percussion staff,
 * and the brass instruments are written a tone below the bagpipes.
 *
 * A profile is looked up by the name of the voice or by its indicator, so a
 * voice called "Snare" and one called "S" both find the snare profile.
 *
 * @package ksfraser\PhpabcCanntaireachd\Cleanup
 *
 * @since 2.2.0
 */
class InstrumentProfileRegistry
{
    /** @var array<string, InstrumentProfile> */
    private array $profiles = [];

    /**
     * @param InstrumentProfile[]|null $profiles The profiles to use. Defaults
     *                                          to the pipe band profiles.
     */
    public function __construct(?array $profiles = null)
    {
        if ($profiles === null) {
            $this->registerDefaults();
            return;
        }
        foreach ($profiles as $profile) {
            $this->add($profile);
        }
    }

    /**
     * The pipe band and orchestral/marching band profiles.
     */
    private function registerDefaults(): void
    {
        // Pipe band voices
        $this->add((new InstrumentProfile('M', 'Melody', 'Melody'))->setTranspose(1));
        $this->add((new InstrumentProfile('Bagpipes', 'Bagpipes', 'Bagpipes')));
        $this->add((new InstrumentProfile('Seconds', 'Seconds', 'Seconds'))->setTranspose(1));
        $this->add((new InstrumentProfile('P', 'Pipes', 'Pipes')));
        $this->add((new InstrumentProfile('H', 'Harmony', 'Harmony')));
        $this->add((new InstrumentProfile('C', 'C-Harmony', 'C-Harmony')));

        // Pipe band drums: one line percussion staff.
        $this->add(
            (new InstrumentProfile('S', 'Snare', 'Snare'))
                ->setStafflines(1)->setClef('perc')->setMiddle('G')->setPipeBandDrum(true)
        );
        $this->add(
            (new InstrumentProfile('B', 'Bass', 'Bass'))
                ->setStafflines(1)->setClef('perc')->setMiddle('G')->setPipeBandDrum(true)
        );
        $this->add(
            (new InstrumentProfile('T', 'Tenor', 'Tenor'))
                ->setStafflines(1)->setClef('perc')->setMiddle('G')->setPipeBandDrum(true)
        );

        // Drum set
        $this->add(
            (new InstrumentProfile('Drums', 'Drums', 'Drums'))
                ->setStafflines(5)->setClef('perc')->setMiddle('C')
        );

        // Brass band / orchestral instruments
        $this->add(
            (new InstrumentProfile('Trumpet', 'Trumpet', 'Trpt'))
                ->setTranspose(2)->setClef('treble')
        );
        $this->add(
            (new InstrumentProfile('Clarinet', 'Clarinet', 'Cl'))
                ->setTranspose(2)->setClef('treble')
        );
        $this->add(
            (new InstrumentProfile('Flute', 'Flute', 'Fl'))
                ->setTranspose(0)->setClef('treble')
        );
        $this->add(
            (new InstrumentProfile('AltoSax', 'Alto Sax', 'A.Sax'))
                ->setTranspose(3)->setClef('treble')
        );
        $this->add(
            (new InstrumentProfile('TenorSax', 'Tenor Sax', 'T.Sax'))
                ->setTranspose(14)->setClef('treble')
        );
        $this->add(
            (new InstrumentProfile('BariSax', 'Bari Sax', 'Bar.Sax'))
                ->setTranspose(15)->setClef('treble')
        );
        $this->add(
            (new InstrumentProfile('Trombone', 'Trombone', 'Tbn'))
                ->setTranspose(0)->setClef('bass')
        );
        $this->add(
            (new InstrumentProfile('Tuba', 'Tuba', 'Tuba'))
                ->setTranspose(0)->setClef('bass')
        );
        $this->add(
            (new InstrumentProfile('FrenchHorn', 'French Horn', 'Hn'))
                ->setTranspose(7)->setClef('treble')
        );

        // Guitar / Bass Guitar (written an octave above sounding pitch)
        $this->add(
            (new InstrumentProfile('Guitar', 'Guitar', 'Gtr'))
                ->setOctave(1)->setClef('treble')
        );
        $this->add(
            (new InstrumentProfile('BGuitar', 'Bass Guitar', 'B.Gtr'))
                ->setOctave(1)->setClef('bass')
        );

        // Piano (sounds as written, but typically written two staves)
        $this->add((new InstrumentProfile('Piano', 'Piano', 'Pno'))->setOctave(-1));

        // Legacy brass band (kept for compatibility)
        $this->add(
            (new InstrumentProfile('BB', 'Brass Bass', 'ABCD-C'))
                ->setTranspose(-1)->setOctave(-1)->setClef('bass')
        );
        $this->add((new InstrumentProfile('BC', 'Brass Chord', 'ABCD-D'))->setTranspose(-1));
        $this->add((new InstrumentProfile('BD', 'Brass Chord 2', 'ABCD-B'))->setTranspose(-1));
    }

    /**
     * Add a profile. It can be found by its name, its short name and its
     * indicator.
     */
    public function add(InstrumentProfile $profile): void
    {
        $this->profiles[$this->key($profile->getIndicator())] = $profile;
        $this->profiles[$this->key($profile->getName())] = $profile;
        $this->profiles[$this->key($profile->getSname())] = $profile;
    }

    /**
     * The profile of an instrument, or null when it is not one we know.
     *
     * @param string $name The voice name or the voice indicator.
     * @return InstrumentProfile|null
     */
    public function find(string $name): ?InstrumentProfile
    {
        return $this->profiles[$this->key($name)] ?? null;
    }

    /**
     * @return InstrumentProfile[]
     */
    public function all(): array
    {
        $unique = [];
        foreach ($this->profiles as $profile) {
            $unique[$profile->getIndicator()] = $profile;
        }
        return array_values($unique);
    }

    /**
     * The lookup key of a name.
     */
    private function key(string $name): string
    {
        return strtolower(trim($name));
    }
}
