<?php

declare(strict_types=1);

namespace ksfraser\PhpabcCanntaireachd\Cleanup;

use ksfraser\PhpabcCanntaireachd\Cleanup\Model\AbcBodyLine;
use ksfraser\PhpabcCanntaireachd\Cleanup\Model\AbcNoteModel;

/**
 * Puts the pipe band drums on their one line percussion staff, and on the two
 * notes that staff has.
 *
 * The snare, bass and tenor drums are written on a one line staff with the
 * middle of the staff at G, so the only notes they play are A and c.
 *
 * Three things can be wrong with a drum part:
 *
 *  - the clef is missing, or is "none" instead of the percussion clef. That
 *    is fixed.
 *  - the part is written on a different pair of notes, e.g. on d and f. When
 *    a single transposition brings every note onto A or c, the part is
 *    transposed.
 *  - one note is the wrong case, e.g. "a" or "C" where "A" or "c" was
 *    meant. That is fixed.
 *
 * A part that is mostly A and c with one odd note is a typo, and is reported
 * rather than changed: there is no way to tell what it should have been.
 *
 * Voices that are not pipe band drums — a drum set, cymbals, a timpani — are
 * left alone.
 *
 * @package ksfraser\PhpabcCanntaireachd\Cleanup
 *
 * @since 2.2.0
 */
class DrumVoiceNormaliser
{
    /** @var string The two notes a pipe band drum plays. */
    public const DRUM_NOTES = ['A', 'c'];

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
     * Is this voice one of the pipe band drums?
     *
     * @param string $voiceId
     * @param string $voiceName
     * @return bool
     */
    public function isPipeBandDrum(string $voiceId, string $voiceName): bool
    {
        $profile = $this->profiles->find($voiceName) ?? $this->profiles->find($voiceId);
        return $profile !== null && $profile->isPipeBandDrum();
    }

    /**
     * The clef a pipe band drum should have.
     *
     * @return array{stafflines: string, clef: string, middle: string}
     */
    public function getPercussionStaff(): array
    {
        return ['stafflines' => '1', 'clef' => 'perc', 'middle' => 'G'];
    }

    /**
     * The pitches a voice plays, e.g. ['A', 'c'].
     *
     * @param AbcBodyLine[] $bodyLines
     * @param string $voiceId
     * @return string[]
     */
    public function collectPitches(array $bodyLines, string $voiceId): array
    {
        $pitches = [];
        foreach ($bodyLines as $bodyLine) {
            if ($bodyLine->getVoice() !== $voiceId) {
                continue;
            }
            foreach ($bodyLine->getBars() as $bar) {
                foreach ($bar->getNotes() as $note) {
                    if ($note->isRest()) {
                        continue;
                    }
                    $pitches[$note->getPitch() . $note->getOctave()] = true;
                }
            }
        }
        return array_keys($pitches);
    }

    /**
     * The transposition that brings every pitch onto A or c, if there is one.
     *
     * @param string[] $pitches
     * @return int|null The number of semitones, or null when there is none.
     */
    public function findTransposition(array $pitches): ?int
    {
        if ($pitches === []) {
            return null;
        }
        for ($semitones = -24; $semitones <= 24; $semitones++) {
            $allOnTarget = true;
            foreach ($pitches as $pitch) {
                if (!$this->isOnTarget($this->shift($pitch, $semitones))) {
                    $allOnTarget = false;
                    break;
                }
            }
            if ($allOnTarget) {
                return $semitones;
            }
        }
        return null;
    }

    /**
     * The transposition that fixes a note that is the wrong case.
     *
     * @param string $pitch
     * @return int|null
     */
    public function findCaseCorrection(string $pitch): ?int
    {
        $letter = $pitch[0] ?? '';
        $marks = substr($pitch, 1);
        foreach (self::DRUM_NOTES as $target) {
            if (strcasecmp($letter, $target[0]) === 0) {
                $shift = (new AbcPitch($target, $marks))->getSemitone()
                    - (new AbcPitch($letter, $marks))->getSemitone();
                if ($shift !== 0) {
                    return $shift;
                }
            }
        }
        return null;
    }

    /**
     * What is wrong with a drum part.
     *
     * @param string[] $pitches
     * @return array{action: string, transposition?: int, notes?: string[]}
     *         The action is one of "ok", "transpose", "case" or "typo".
     */
    public function diagnose(array $pitches): array
    {
        if ($pitches === []) {
            return ['action' => 'ok'];
        }

        $onTarget = true;
        $wrongCase = [];
        foreach ($pitches as $pitch) {
            if (!$this->isOnTarget($pitch)) {
                $onTarget = false;
                if ($this->findCaseCorrection($pitch) !== null) {
                    $wrongCase[] = $pitch;
                }
            }
        }

        if ($onTarget) {
            return ['action' => 'ok'];
        }

        // One note of the wrong case among notes that are otherwise right.
        if (count($wrongCase) === 1 && count($pitches) === 2) {
            return ['action' => 'case', 'notes' => $wrongCase];
        }

        $transposition = $this->findTransposition($pitches);
        if ($transposition !== null) {
            return ['action' => 'transpose', 'transposition' => $transposition];
        }

        return ['action' => 'typo', 'notes' => $pitches];
    }

    /**
     * Move a note onto the drum notes.
     *
     * @param AbcNoteModel $note
     * @param int $semitones
     * @return AbcNoteModel
     */
    public function transposeNote(AbcNoteModel $note, int $semitones): AbcNoteModel
    {
        $pitch = (new AbcPitch($note->getPitch(), $note->getOctave(), $note->getAccidentals()))
            ->transpose($semitones);
        return $note->withPitch($pitch->getLetter(), $this->octaveMarksOf($pitch));
    }

    /**
     * The octave marks of a pitch.
     */
    private function octaveMarksOf(AbcPitch $pitch): string
    {
        $rendered = $pitch->render();
        return substr($rendered, 1);
    }

    /**
     * Is this pitch one of the two drum notes?
     *
     * @param string $pitch
     * @return bool
     */
    private function isOnTarget(string $pitch): bool
    {
        $letter = $pitch[0] ?? '';
        $marks = substr($pitch, 1);
        if ($marks !== '') {
            return false;
        }
        foreach (self::DRUM_NOTES as $target) {
            if ($letter === $target) {
                return true;
            }
        }
        return false;
    }

    /**
     * A pitch shifted by a number of semitones, as ABC writes it.
     *
     * @param string $pitch
     * @param int $semitones
     * @return string
     */
    private function shift(string $pitch, int $semitones): string
    {
        $letter = $pitch[0] ?? 'c';
        $marks = substr($pitch, 1);
        return (new AbcPitch($letter, $marks))->transpose($semitones)->render();
    }
}
