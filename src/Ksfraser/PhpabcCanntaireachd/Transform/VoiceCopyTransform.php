<?php

declare(strict_types=1);

namespace Ksfraser\PhpabcCanntaireachd\Transform;

use Ksfraser\PhpabcCanntaireachd\Tune\AbcTune;

/**
 * Transform to copy Melody voice bars to Bagpipes voice
 * 
 * Business Rule:
 * IF Melody voice exists with bars (music content)
 * AND Bagpipes voice does NOT exist OR has no bars
 * THEN Copy all bars from Melody voice to new Bagpipes voice
 * 
 * Voice Identification (case-insensitive):
 * - Melody: M, Melody
 * - Bagpipes: Bagpipes, Pipes, P
 * 
 * @package Ksfraser\PhpabcCanntaireachd\Transform
 * 
 * @uml
 * @startuml
 * class VoiceCopyTransform implements AbcTransform {
 *   + transform(tune: AbcTune): AbcTune
 *   - findMelodyVoice(tune: AbcTune): ?string
 *   - findBagpipesVoice(tune: AbcTune): ?string
 *   - hasBars(tune: AbcTune, voiceId: string): bool
 *   - copyMelodyToBagpipes(tune: AbcTune, melodyId: string): void
 * }
 * @enduml
 */
class VoiceCopyTransform implements AbcTransform
{
    /**
     * Melody voice identifiers (case-insensitive)
     */
    private const MELODY_IDS = ['M', 'Melody'];

    /**
     * Bagpipes voice identifiers (case-insensitive)
     */
    private const BAGPIPES_IDS = ['Bagpipes', 'Pipes', 'P'];

    /**
     * Transform the tune by copying Melody to Bagpipes if needed
     * 
     * @param AbcTune $tune The tune to transform
     * @return AbcTune The transformed tune
     */
    public function transform(AbcTune $tune): AbcTune
    {
        // Find Melody voice (M or Melody)
        $melodyId = $this->findMelodyVoice($tune);
        if ($melodyId === null) {
            // No Melody voice found, nothing to do
            return $tune;
        }

        // Check if Melody has bars (music content)
        if (!$this->hasBars($tune, $melodyId)) {
            // Melody has no bars, nothing to copy
            return $tune;
        }

        // Find Bagpipes voice (Bagpipes, Pipes, or P)
        $bagpipesId = $this->findBagpipesVoice($tune);
        if ($bagpipesId !== null && $this->hasBars($tune, $bagpipesId)) {
            // Bagpipes already exists with bars, don't overwrite
            return $tune;
        }

        // Copy Melody bars to Bagpipes
        $this->copyMelodyToBagpipes($tune, $melodyId);

        return $tune;
    }

    /**
     * Find the Melody voice in the tune (case-insensitive)
     * 
     * @param AbcTune $tune The tune to search
     * @return string|null The voice ID if found, null otherwise
     */
    private function findMelodyVoice(AbcTune $tune): ?string
    {
        foreach (self::MELODY_IDS as $melodyId) {
            // Try exact match first
            if ($tune->hasVoice($melodyId)) {
                return $melodyId;
            }
            // Try case-insensitive match
            $voices = $tune->getVoices();
            foreach (array_keys($voices) as $voiceId) {
                if (strcasecmp($voiceId, $melodyId) === 0) {
                    return $voiceId;
                }
            }
        }
        return null;
    }

    /**
     * Find the Bagpipes voice in the tune (case-insensitive)
     * 
     * @param AbcTune $tune The tune to search
     * @return string|null The voice ID if found, null otherwise
     */
    private function findBagpipesVoice(AbcTune $tune): ?string
    {
        foreach (self::BAGPIPES_IDS as $bagpipesId) {
            // Try exact match first
            if ($tune->hasVoice($bagpipesId)) {
                return $bagpipesId;
            }
            // Try case-insensitive match
            $voices = $tune->getVoices();
            foreach (array_keys($voices) as $voiceId) {
                if (strcasecmp($voiceId, $bagpipesId) === 0) {
                    return $voiceId;
                }
            }
        }
        return null;
    }

    /**
     * Check if a voice has bars (music content)
     * 
     * @param AbcTune $tune The tune
     * @param string $voiceId The voice ID
     * @return bool True if the voice has bars, false otherwise
     */
    private function hasBars(AbcTune $tune, string $voiceId): bool
    {
        $bars = $tune->getBarsForVoice($voiceId);
        return $bars !== null && count($bars) > 0;
    }

    /**
     * Copy Melody bars to a new Bagpipes voice
     * 
     * @param AbcTune $tune The tune
     * @param string $melodyId The Melody voice ID
     * @return void
     */
    private function copyMelodyToBagpipes(AbcTune $tune, string $melodyId): void
    {
        // Get Melody bars
        $melodyBars = $tune->getBarsForVoice($melodyId);
        if ($melodyBars === null || count($melodyBars) === 0) {
            return;
        }

        // CRITICAL: Deep copy bars so Melody and Bagpipes have separate note objects
        // This ensures canntaireachd added to Bagpipes doesn't affect Melody
        $copiedBars = $this->deepCopyBars($melodyBars);

        // Create Bagpipes voice metadata
        $metadata = [
            'name' => 'Bagpipes',
            'sname' => 'Bagpipes',
            'clef' => 'treble',
            'octave' => 0
        ];

        // Add Bagpipes voice with copied bars
        $tune->addVoice('Bagpipes', $metadata, $copiedBars);
    }

    /**
     * Deep copy bars and their notes
     * 
     * @param array $bars Array of bar objects
     * @return array Deep copied bars
     */
    private function deepCopyBars(array $bars): array
    {
        $copiedBars = [];
        foreach ($bars as $bar) {
            // Use clone to create a shallow copy of the bar object
            $copiedBar = clone $bar;
            
            // Deep copy the notes array
            if (isset($bar->notes) && is_array($bar->notes)) {
                $copiedBar->notes = [];
                foreach ($bar->notes as $note) {
                    // Clone each note to create separate object
                    $copiedBar->notes[] = clone $note;
                }
            }
            
            $copiedBars[] = $copiedBar;
        }
        return $copiedBars;
    }
}
