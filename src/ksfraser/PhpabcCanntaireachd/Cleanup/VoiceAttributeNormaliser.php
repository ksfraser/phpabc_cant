<?php

declare(strict_types=1);

namespace ksfraser\PhpabcCanntaireachd\Cleanup;

/**
 * Gives every voice the attributes its instrument should have.
 *
 * A voice in a pipe band score should say what it is and how it is written:
 * the name and the short name so that it appears on the score, the stems, the
 * clef, and the transposition. This class fills in what is missing from the
 * instrument profile, and reports what it changed.
 *
 * @package ksfraser\PhpabcCanntaireachd\Cleanup
 *
 * @since 2.2.0
 */
class VoiceAttributeNormaliser
{
    /** @var InstrumentProfileRegistry */
    private InstrumentProfileRegistry $profiles;

    /**
     * @param InstrumentProfileRegistry|null $profiles
     */
    public function __construct(?InstrumentProfileRegistry $profiles = null)
    {
        $this->profiles = $profiles ?? new InstrumentProfileRegistry();
    }

    /**
     * The attributes a voice is missing, and the ones that are wrong.
     *
     * @param VoiceHeader $voice
     * @return array<string, string> The changes, as "attribute: old => new".
     */
    public function findChanges(VoiceHeader $voice): array
    {
        $profile = $this->profiles->find($voice->getName()) ?? $this->profiles->find($voice->getIndicator());
        if ($profile === null) {
            return [];
        }

        $changes = [];

        // The name and the short name, so that the voice appears on the score.
        if (!$voice->has('name')) {
            $changes['name'] = '(none) => ' . $profile->getName();
        }
        if (!$voice->has('sname')) {
            $changes['sname'] = '(none) => ' . $profile->getSname();
        }

        // The stems.
        if (!$voice->has('stem')) {
            $changes['stem'] = '(none) => ' . $profile->getStem();
        }
        if (!$voice->has('gstem')) {
            $changes['gstem'] = '(none) => ' . $profile->getGstem();
        }

        // The clef, the transposition and the octave.
        $clef = $profile->getClef();
        if ($clef !== null && !$voice->has('clef')) {
            $changes['clef'] = '(none) => ' . $clef;
        }
        if ($profile->getTranspose() !== 0 && !$voice->has('transpose')) {
            $changes['transpose'] = '(none) => ' . $profile->getTranspose();
        }
        if ($profile->getOctave() !== 0 && !$voice->has('octave')) {
            $changes['octave'] = '(none) => ' . $profile->getOctave();
        }

        // A percussion staff.
        if ($profile->getStafflines() !== null) {
            if (!$voice->has('stafflines')) {
                $changes['stafflines'] = '(none) => ' . $profile->getStafflines();
            }
            if ($profile->getMiddle() !== null && !$voice->has('middle')) {
                $changes['middle'] = '(none) => ' . $profile->getMiddle();
            }
        }

        return $changes;
    }

    /**
     * The voice line with the missing attributes filled in.
     *
     * @param string $line The V: line.
     * @return string
     */
    public function normalise(string $line): string
    {
        $voice = VoiceHeader::parse($line);
        if ($voice === null) {
            return $line;
        }
        $profile = $this->profiles->find($voice->getName()) ?? $this->profiles->find($voice->getIndicator());
        if ($profile === null) {
            return $line;
        }

        if (!$voice->has('name')) {
            $voice->set('name', $profile->getName());
        }
        if (!$voice->has('sname')) {
            $voice->set('sname', $profile->getSname());
        }
        if (!$voice->has('stem')) {
            $voice->set('stem', $profile->getStem());
        }
        if (!$voice->has('gstem')) {
            $voice->set('gstem', $profile->getGstem());
        }
        if ($profile->getClef() !== null && !$voice->has('clef')) {
            $voice->set('clef', $profile->getClef());
        }
        if ($profile->getTranspose() !== 0 && !$voice->has('transpose')) {
            $voice->set('transpose', (string)$profile->getTranspose());
        }
        if ($profile->getOctave() !== 0 && !$voice->has('octave')) {
            $voice->set('octave', (string)$profile->getOctave());
        }
        if ($profile->getStafflines() !== null) {
            if (!$voice->has('stafflines')) {
                $voice->set('stafflines', (string)$profile->getStafflines());
            }
            if ($profile->getMiddle() !== null && !$voice->has('middle')) {
                $voice->set('middle', $profile->getMiddle());
            }
        }

        return $voice->render();
    }
}
