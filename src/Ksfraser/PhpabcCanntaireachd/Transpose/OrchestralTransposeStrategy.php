<?php
namespace Ksfraser\PhpabcCanntaireachd\Transpose;

/**
 * Orchestral Score Transpose Strategy
 * 
 * For traditional orchestral/concert band scores.
 * Each instrument uses its standard orchestral transpose setting
 * (written pitch for transposing instruments).
 */
class OrchestralTransposeStrategy implements TransposeStrategy
{
    /**
     * {@inheritdoc}
     * 
     * Orchestral mode: Each instrument uses standard orchestral transpose values
     * - Concert pitch instruments: transpose=0
     * - Bb instruments: transpose=2 (Trumpet, Clarinet, Tenor Sax)
     * - Eb instruments: transpose=9 (Alto Sax, Baritone Sax)
     * - F instruments: transpose=7 (French Horn, English Horn)
     */
    public function getTranspose(string $instrumentName): int
    {
        return InstrumentTransposeMapper::getTranspose($instrumentName);
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'orchestral';
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription(): string
    {
        return 'Orchestral Score Mode: Each instrument uses standard orchestral transpose values';
    }
}
