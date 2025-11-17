<?php
/**
 * Orchestral Order Strategy
 *
 * Orders voices according to standard orchestral score conventions:
 * Woodwinds, Brass, Percussion, Strings, Keyboards, Vocals, Bagpipes, Other
 *
 * Within each family, instruments are ordered by their traditional score position.
 *
 * @package Ksfraser\PhpabcCanntaireachd\VoiceOrdering
 */

namespace Ksfraser\PhpabcCanntaireachd\VoiceOrdering;

use Ksfraser\PhpabcCanntaireachd\Voices\AbcVoice;

class OrchestralOrderStrategy implements VoiceOrderingStrategy
{
    /**
     * Order voices according to orchestral score conventions
     *
     * @param AbcVoice[] $voices Array of AbcVoice objects
     * @return AbcVoice[] Ordered array of AbcVoice objects
     */
    public function orderVoices(array $voices): array
    {
        if (empty($voices)) {
            return $voices;
        }

        // Create array with voice data for sorting
        $voiceData = [];
        foreach ($voices as $index => $voice) {
            $voiceName = $voice->getVoiceIndicator();
            $family = InstrumentMapper::mapToFamily($voiceName);
            $familyPriority = InstrumentFamily::getPriority($family);
            $instrumentPriority = $this->getInstrumentPriority($voiceName, $family);

            $voiceData[] = [
                'voice' => $voice,
                'originalIndex' => $index,
                'family' => $family,
                'familyPriority' => $familyPriority,
                'instrumentPriority' => $instrumentPriority,
                'name' => $voiceName,
            ];
        }

        // Sort by family priority, then instrument priority, then original index
        usort($voiceData, function($a, $b) {
            // First by family priority
            if ($a['familyPriority'] !== $b['familyPriority']) {
                return $a['familyPriority'] <=> $b['familyPriority'];
            }

            // Then by instrument priority within family
            if ($a['instrumentPriority'] !== $b['instrumentPriority']) {
                return $a['instrumentPriority'] <=> $b['instrumentPriority'];
            }

            // Finally by original index to maintain stable sort
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
     * Get instrument priority within its family
     *
     * @param string $voiceName Voice name
     * @param string $family Instrument family
     * @return int Priority (lower = higher in score)
     */
    private function getInstrumentPriority(string $voiceName, string $family): int
    {
        $normalized = InstrumentMapper::normalizeInstrumentName($voiceName);

        // Get family-specific ordering
        $order = [];
        switch ($family) {
            case InstrumentFamily::WOODWINDS:
                $order = InstrumentFamily::getWoodwindOrder();
                break;
            case InstrumentFamily::BRASS:
                $order = InstrumentFamily::getBrassOrder();
                break;
            case InstrumentFamily::PERCUSSION:
                $order = InstrumentFamily::getPercussionOrder();
                break;
            case InstrumentFamily::STRINGS:
                $order = InstrumentFamily::getStringsOrder();
                break;
            case InstrumentFamily::KEYBOARDS:
                $order = InstrumentFamily::getKeyboardsOrder();
                break;
            case InstrumentFamily::VOCALS:
                $order = InstrumentFamily::getVocalsOrder();
                break;
            case InstrumentFamily::BAGPIPES:
                $order = InstrumentFamily::getBagpipesOrder();
                break;
            default:
                return 999; // Unknown instruments go last
        }

        // Find position in family-specific order
        foreach ($order as $index => $instrumentPattern) {
            if (strpos($normalized, $instrumentPattern) !== false) {
                return $index;
            }
        }

        // Not found in specific order, use high priority (goes last in family)
        return 900;
    }

    /**
     * Get strategy name
     *
     * @return string
     */
    public function getName(): string
    {
        return 'orchestral';
    }

    /**
     * Get strategy description
     *
     * @return string
     */
    public function getDescription(): string
    {
        return 'Orders voices according to standard orchestral score conventions';
    }
}
