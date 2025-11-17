<?php
/**
 * Custom Order Strategy
 *
 * Orders voices according to user-defined custom configuration.
 * Supports both exact name matching and pattern matching.
 *
 * @package Ksfraser\PhpabcCanntaireachd\VoiceOrdering
 */

namespace Ksfraser\PhpabcCanntaireachd\VoiceOrdering;

use Ksfraser\PhpabcCanntaireachd\Voices\AbcVoice;

class CustomOrderStrategy implements VoiceOrderingStrategy
{
    /**
     * @var array Custom voice order configuration
     */
    private $customOrder;

    /**
     * Constructor
     *
     * @param array $customOrder Ordered array of voice name patterns
     */
    public function __construct(array $customOrder)
    {
        $this->customOrder = $customOrder;
    }

    /**
     * Order voices according to custom configuration
     *
     * @param AbcVoice[] $voices Array of AbcVoice objects
     * @return AbcVoice[] Ordered array of AbcVoice objects
     */
    public function orderVoices(array $voices): array
    {
        if (empty($voices) || empty($this->customOrder)) {
            return $voices;
        }

        // Create array with voice data for sorting
        $voiceData = [];
        foreach ($voices as $index => $voice) {
            $voiceName = $voice->getVoiceIndicator();
            $priority = $this->getCustomPriority($voiceName);

            $voiceData[] = [
                'voice' => $voice,
                'originalIndex' => $index,
                'priority' => $priority,
                'name' => $voiceName,
            ];
        }

        // Sort by custom priority, then original index
        usort($voiceData, function($a, $b) {
            // First by custom priority
            if ($a['priority'] !== $b['priority']) {
                return $a['priority'] <=> $b['priority'];
            }

            // Then by original index to maintain stable sort
            return $a['originalIndex'] <=> $b['originalIndex'];
        });

        // Extract sorted voices
        $orderedVoices = [];
        foreach ($voiceData as $data) {
            $orderedVoices[] = $data['voice'];
        }

        return $orderedVoices;
    }

    /**
     * Get custom priority for a voice name
     *
     * @param string $voiceName Voice name
     * @return int Priority (lower = earlier in output)
     */
    private function getCustomPriority(string $voiceName): int
    {
        $normalized = strtolower(trim($voiceName));

        // Check for exact match first
        foreach ($this->customOrder as $index => $pattern) {
            $normalizedPattern = strtolower(trim($pattern));

            // Exact match
            if ($normalized === $normalizedPattern) {
                return $index;
            }
        }

        // Check for pattern match (contains)
        foreach ($this->customOrder as $index => $pattern) {
            $normalizedPattern = strtolower(trim($pattern));

            // Pattern matching (case-insensitive contains)
            if (strpos($normalized, $normalizedPattern) !== false) {
                return $index;
            }
        }

        // Not found in custom order - goes last
        return 999;
    }

    /**
     * Get the custom order configuration
     *
     * @return array
     */
    public function getCustomOrder(): array
    {
        return $this->customOrder;
    }

    /**
     * Get strategy name
     *
     * @return string
     */
    public function getName(): string
    {
        return 'custom';
    }

    /**
     * Get strategy description
     *
     * @return string
     */
    public function getDescription(): string
    {
        return 'Orders voices according to user-defined custom configuration';
    }
}
