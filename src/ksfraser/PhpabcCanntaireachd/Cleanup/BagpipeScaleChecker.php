<?php

declare(strict_types=1);

namespace ksfraser\PhpabcCanntaireachd\Cleanup;

use ksfraser\PhpabcCanntaireachd\Cleanup\Model\AbcBodyLine;
use ksfraser\PhpabcCanntaireachd\Cleanup\Model\AbcNoteModel;

/**
 * Checks that the bagpipe part can actually be played.
 *
 * The Highland pipe scale runs from the low G to the high D:
 *
 *     G  A  B  c  d  e  f  g  a  b  c'  d'
 *
 * A note outside that range cannot be played on the chanter, and a note
 * outside the scale needs an accidental that the pipe does not have.
 *
 * @package ksfraser\PhpabcCanntaireachd\Cleanup
 *
 * @since 2.2.0
 */
class BagpipeScaleChecker
{
    /** @var string The lowest note of the pipe scale: the low G. */
    public const LOWEST_NOTE = 'G';

    /** @var string The highest note of the pipe scale: the high D. */
    public const HIGHEST_NOTE = 'd';

    /** @var int[] The pitch classes of the pipe scale, in semitones. */
    public const SCALE = [7, 9, 11, 0, 2, 4, 5];

    /**
     * The notes of a voice that are out of the playable range.
     *
     * @param AbcBodyLine[] $bodyLines
     * @param string $voiceId
     * @return string[] The offending notes, e.g. ["E", "f'"].
     */
    public function findOutOfRange(array $bodyLines, string $voiceId): array
    {
        $lowest = new AbcPitch(self::LOWEST_NOTE);
        $highest = new AbcPitch(self::HIGHEST_NOTE, "'");
        $offenders = [];

        foreach ($this->notesOf($bodyLines, $voiceId) as $note) {
            $pitch = new AbcPitch($note->getPitch(), $note->getOctave(), $note->getAccidentals());
            $semitone = $pitch->getSemitone();
            if ($semitone < $lowest->getSemitone() || $semitone > $highest->getSemitone()) {
                $offenders[$pitch->render() . $note->getAccidentals()] = true;
            }
        }

        return array_keys($offenders);
    }

    /**
     * The notes of a voice that are not in the pipe scale.
     *
     * @param AbcBodyLine[] $bodyLines
     * @param string $voiceId
     * @return string[]
     */
    public function findOutOfScale(array $bodyLines, string $voiceId): array
    {
        $offenders = [];
        foreach ($this->notesOf($bodyLines, $voiceId) as $note) {
            if ($note->getAccidentals() !== '') {
                $offenders[$note->getPitch() . $note->getOctave() . $note->getAccidentals()] = true;
                continue;
            }
            $pitch = new AbcPitch($note->getPitch(), $note->getOctave());
            $class = $pitch->getSemitone() % 12;
            if ($class < 0) {
                $class += 12;
            }
            if (!in_array($class, self::SCALE, true)) {
                $offenders[$pitch->render()] = true;
            }
        }
        return array_keys($offenders);
    }

    /**
     * The notes of a voice.
     *
     * @param AbcBodyLine[] $bodyLines
     * @param string $voiceId
     * @return AbcNoteModel[]
     */
    private function notesOf(array $bodyLines, string $voiceId): array
    {
        $notes = [];
        foreach ($bodyLines as $bodyLine) {
            if ($bodyLine->getVoice() !== $voiceId) {
                continue;
            }
            foreach ($bodyLine->getBars() as $bar) {
                foreach ($bar->getNotes() as $note) {
                    if ($note->isRest()) {
                        continue;
                    }
                    $notes[] = $note;
                }
            }
        }
        return $notes;
    }
}
