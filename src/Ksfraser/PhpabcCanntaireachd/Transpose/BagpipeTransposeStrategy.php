<?php
namespace Ksfraser\PhpabcCanntaireachd\Transpose;

/**
 * Bagpipe Ensemble Transpose Strategy
 * 
 * For modern Highland bagpipe ensembles.
 * - Bagpipe voices: transpose=0 (written = sounding pitch)
 * - Concert pitch instruments: transpose=2 (up one whole step to match bagpipes)
 * 
 * Rationale: Highland bagpipes sound at approximately Bb major when written in A major.
 * Modern chanters typically tuned to 480Hz (slightly sharp of Bb).
 */
class BagpipeTransposeStrategy implements TransposeStrategy
{
    /**
     * {@inheritdoc}
     * 
     * Bagpipe mode:
     * - Bagpipe voices: transpose=0
     * - All other instruments: transpose=2 (up one whole step)
     */
    public function getTranspose(string $instrumentName): int
    {
        // Normalize instrument name (case-insensitive, remove spaces)
        $normalized = strtolower(str_replace([' ', '-', '_'], '', $instrumentName));

        // Bagpipe instruments stay at transpose=0
        if (preg_match('/bagpipe|pipe|chanter/', $normalized)) {
            return 0;
        }

        // All other instruments transpose up 2 semitones (one whole step)
        // to match bagpipe's written A = sounding Bb
        return 2;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'bagpipe';
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription(): string
    {
        return 'Bagpipe Ensemble Mode: Bagpipes transpose=0, concert pitch instruments transpose=2';
    }
}
