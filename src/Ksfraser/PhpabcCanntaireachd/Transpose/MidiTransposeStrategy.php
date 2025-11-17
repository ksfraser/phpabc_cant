<?php
namespace Ksfraser\PhpabcCanntaireachd\Transpose;

/**
 * MIDI Import Transpose Strategy
 * 
 * For ABC files imported from MIDI or created from audio.
 * All voices have transpose=0 (absolute pitch, no transposition).
 */
class MidiTransposeStrategy implements TransposeStrategy
{
    /**
     * {@inheritdoc}
     * 
     * MIDI mode: All instruments at concert pitch (transpose=0)
     */
    public function getTranspose(string $instrumentName): int
    {
        return 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'midi';
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription(): string
    {
        return 'MIDI Import Mode: All voices at concert pitch (transpose=0)';
    }
}
