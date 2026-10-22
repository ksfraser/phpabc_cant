<?php

declare(strict_types=1);

namespace ksfraser\PhpabcCanntaireachd\Bagpipe;

use ksfraser\PhpabcCanntaireachd\Cleanup\Model\AbcBodyLine;
use ksfraser\PhpabcCanntaireachd\Cleanup\Parser\AbcTokenizer;

/**
 * Checks the graces of a tune against the fingering of the note they are
 * played from.
 *
 * A grace is a brief change of the hole that sounds, so the finger it needs
 * has to be in the right place for the note before it.
 *
 * The G gracenote lifts the top index finger. On the high A that finger is
 * already up, and the note is played as a G strike instead — the thumb
 * closes, and the grace still sounds. On the high G the note is already a G,
 * and there is nothing to hear: a G gracenote cannot be played there.
 *
 * The note before the grace is worked out from the music itself, so the
 * caller does not have to say what it was.
 *
 * @package ksfraser\PhpabcCanntaireachd\Bagpipe
 *
 * @since 2.2.0
 */
class BagpipeGraceChecker
{
    /** @var BagpipeFingering */
    private BagpipeFingering $fingering;

    /** @var AbcTokenizer */
    private AbcTokenizer $tokenizer;

    /**
     * @param BagpipeFingering|null $fingering
     * @param AbcTokenizer|null $tokenizer
     */
    public function __construct(?BagpipeFingering $fingering = null, ?AbcTokenizer $tokenizer = null)
    {
        $this->fingering = $fingering ?? new BagpipeFingering();
        $this->tokenizer = $tokenizer ?? new AbcTokenizer();
    }

    /**
     * The graces of the tune that cannot be played.
     *
     * @param AbcBodyLine[] $bodyLines
     * @return string[] A message for each grace that cannot be played.
     */
    public function check(array $bodyLines): array
    {
        $problems = [];
        $previous = null;

        foreach ($bodyLines as $bodyLine) {
            foreach ($this->tokenizer->tokenize($bodyLine->render()) as $item) {
                if ($item['type'] !== 'note') {
                    continue;
                }
                $note = $item['note'];
                $grace = trim($note->getGrace(), '{}');
                if ($grace !== '') {
                    $name = $this->fingering->getGraceName($grace);
                    // Only the pipe voices are checked: a grace in a guitar
                    // or a brass part is not a pipe grace.
                    $melody = $note->getPitch() . $note->getOctave();
                    if ($this->fingering->getGrace($name) === null
                        || $previous === null
                        || !$this->fingering->isInScale($previous)
                        || !$this->fingering->isInScale($melody)) {
                        // Not a pipe grace, or not in the pipe scale.
                    } elseif (!$this->fingering->canPlayGrace($name, $previous, $melody)) {
                        if ($this->fingering->soundsSameNote($name, $melody)) {
                            $problems[] = sprintf(
                                'a %s on a %s cannot be heard: it sounds the note it is on',
                                $name,
                                $melody
                            );
                            continue;
                        }
                        $problems[] = sprintf(
                            'the %s cannot be played from the %s',
                            $name,
                            $previous
                        );
                    }
                }
                if ($note->isRest()) {
                    // A rest is not a note: the grace is played from the note
                    // before the rest.
                    continue;
                }
                $previous = $note->getPitch() . $note->getOctave();
            }
        }

        return $problems;
    }
}
