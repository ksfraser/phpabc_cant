<?php
namespace Ksfraser\PhpabcCanntaireachd\Transpose;

/**
 * Interface for transpose calculation strategies
 * 
 * Defines how to calculate transpose values for instruments
 * based on different use cases (MIDI import, bagpipe ensemble, orchestral score)
 */
interface TransposeStrategy
{
    /**
     * Calculate transpose value for an instrument
     *
     * @param string $instrumentName Name of the instrument/voice
     * @return int Transpose value (semitones, positive = up, negative = down)
     */
    public function getTranspose(string $instrumentName): int;

    /**
     * Get the strategy name
     *
     * @return string Strategy name (e.g., 'midi', 'bagpipe', 'orchestral')
     */
    public function getName(): string;

    /**
     * Get a description of this strategy
     *
     * @return string Human-readable description
     */
    public function getDescription(): string;
}
