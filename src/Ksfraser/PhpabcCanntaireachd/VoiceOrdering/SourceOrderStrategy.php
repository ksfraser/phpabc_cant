<?php
/**
 * Source Order Strategy
 *
 * Preserves the original voice order from the source ABC file.
 * This is the default behavior and requires no reordering.
 *
 * @package Ksfraser\PhpabcCanntaireachd\VoiceOrdering
 */

namespace Ksfraser\PhpabcCanntaireachd\VoiceOrdering;

use Ksfraser\PhpabcCanntaireachd\Voices\AbcVoice;

class SourceOrderStrategy implements VoiceOrderingStrategy
{
    /**
     * Order voices in their original source file order
     *
     * @param AbcVoice[] $voices Array of AbcVoice objects
     * @return AbcVoice[] Same array unchanged
     */
    public function orderVoices(array $voices): array
    {
        // Return voices in original order (no reordering needed)
        return $voices;
    }

    /**
     * Get strategy name
     *
     * @return string
     */
    public function getName(): string
    {
        return 'source';
    }

    /**
     * Get strategy description
     *
     * @return string
     */
    public function getDescription(): string
    {
        return 'Preserves voice order from source ABC file';
    }
}
