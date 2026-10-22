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
                $group = trim($note->getGrace(), '{}');
                if ($group !== '') {
                    $melody = $note->getPitch() . $note->getOctave();
                    if ($previous !== null
                        && $this->fingering->isInScale($previous)
                        && $this->fingering->isInScale($melody)) {
                        // A grace group is a sequence of graces, and each one
                        // is played from where the one before it left the
                        // hand. The last one of them is the one next to the
                        // melody note, and that is the one that has to sound
                        // something else.
                        $chain = $this->fingering->getGraceChain($group, $previous);
                        $last = end($chain['steps']);
                        foreach ($chain['steps'] as $step) {
                            if ($step === $last && $this->fingering->soundsSameNote($step['note'], $melody)) {
                                $problems[] = sprintf(
                                    'a %s on a %s cannot be heard: it sounds the note it is on',
                                    $step['note'],
                                    $melody
                                );
                                break;
                            }
                        }
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
