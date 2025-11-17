<?php
/**
 * Voice Ordering Strategy Interface
 *
 * Defines the contract for voice ordering strategies.
 * Implementations can order voices by source order, orchestral score convention,
 * or custom user-defined rules.
 *
 * @package Ksfraser\PhpabcCanntaireachd\VoiceOrdering
 */

namespace Ksfraser\PhpabcCanntaireachd\VoiceOrdering;

use Ksfraser\PhpabcCanntaireachd\Voices\AbcVoice;

interface VoiceOrderingStrategy
{
    /**
     * Order the given voices according to this strategy
     *
     * @param AbcVoice[] $voices Array of AbcVoice objects to order
     * @return AbcVoice[] Ordered array of AbcVoice objects
     */
    public function orderVoices(array $voices): array;

    /**
     * Get the name of this ordering strategy
     *
     * @return string Strategy name (e.g., "source", "orchestral", "custom")
     */
    public function getName(): string;

    /**
     * Get a description of this ordering strategy
     *
     * @return string Human-readable description
     */
    public function getDescription(): string;
}
