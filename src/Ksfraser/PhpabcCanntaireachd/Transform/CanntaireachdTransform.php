<?php

declare(strict_types=1);

namespace Ksfraser\PhpabcCanntaireachd\Transform;

use Ksfraser\PhpabcCanntaireachd\Tune\AbcTune;
use Ksfraser\PhpabcCanntaireachd\TokenDictionary;
use Ksfraser\PhpabcCanntaireachd\CanntGenerator;

/**
 * Transform to add canntaireachd syllables to Bagpipes voices
 * 
 * Business Rule:
 * Canntaireachd syllables are ONLY added to Bagpipes-family voices:
 * - Bagpipes, Pipes, P (case-insensitive)
 * - NOT added to Melody (M) or other voices
 * 
 * For each bar in a Bagpipes voice:
 * - Extract notes (ignoring grace notes for syllables)
 * - Look up each note in token dictionary
 * - Generate canntaireachd syllable
 * - Add as w: line after music line
 * 
 * @package Ksfraser\PhpabcCanntaireachd\Transform
 * 
 * @uml
 * @startuml
 * class CanntaireachdTransform implements AbcTransform {
 *   - dict: TokenDictionary
 *   - generator: CanntGenerator
 *   + __construct(dict: TokenDictionary)
 *   + transform(tune: AbcTune): AbcTune
 *   - shouldAddCanntaireachd(voiceId: string): bool
 *   - processVoice(voiceId: string, bars: array): void
 * }
 * CanntaireachdTransform --> TokenDictionary
 * CanntaireachdTransform --> CanntGenerator
 * @enduml
 */
class CanntaireachdTransform implements AbcTransform
{
    /**
     * Bagpipes voice identifiers (case-insensitive)
     * These are the ONLY voices that get canntaireachd
     */
    private const BAGPIPES_IDS = ['Bagpipes', 'Pipes', 'P'];

    /**
     * @var TokenDictionary Token dictionary for note-to-syllable mapping
     */
    private TokenDictionary $dict;

    /**
     * @var CanntGenerator Generator for canntaireachd syllables
     */
    private CanntGenerator $generator;

    /**
     * Constructor
     * 
     * @param TokenDictionary $dict Token dictionary for translations
     */
    public function __construct(TokenDictionary $dict)
    {
        $this->dict = $dict;
        $this->generator = new CanntGenerator($dict);
    }

    /**
     * Transform the tune by adding canntaireachd to Bagpipes voices
     * 
     * @param AbcTune $tune The tune to transform
     * @return AbcTune The transformed tune
     */
    public function transform(AbcTune $tune): AbcTune
    {
        // Get all voice IDs from the tune
        $voices = $tune->getVoices();

        foreach ($voices as $voiceId => $voice) {
            // Only process Bagpipes-family voices
            if (!$this->shouldAddCanntaireachd($voiceId)) {
                continue;
            }

            // Get bars for this voice
            $bars = $tune->getBarsForVoice($voiceId);
            if (!$bars || count($bars) === 0) {
                continue;
            }

            // Process each bar to add canntaireachd
            $this->processVoiceBars($bars);
        }

        return $tune;
    }

    /**
     * Determine if canntaireachd should be added for a voice
     * 
     * @param string $voiceId Voice identifier
     * @return bool True if this is a Bagpipes-family voice
     */
    private function shouldAddCanntaireachd(string $voiceId): bool
    {
        foreach (self::BAGPIPES_IDS as $bagpipesId) {
            if (strcasecmp($voiceId, $bagpipesId) === 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * Process bars to add canntaireachd syllables
     * 
     * @param array $bars Array of AbcBar objects
     * @return void
     */
    private function processVoiceBars(array $bars): void
    {
        foreach ($bars as $bar) {
            // Check if bar has notes
            if (!isset($bar->notes) || !is_array($bar->notes) || count($bar->notes) === 0) {
                continue;
            }

            // Generate canntaireachd for the entire bar
            // CanntGenerator expects the full music line text
            $barContent = $this->getBarContent($bar);
            if (empty($barContent)) {
                continue;
            }

            // Generate canntaireachd syllables for this bar
            $canntText = $this->generator->generateForNotes($barContent);
            if ($canntText && $canntText !== '[?]') {
                // Split syllables and assign to individual notes
                $this->assignSyllablesToNotes($bar->notes, $canntText);
            }
        }
    }

    /**
     * Get the music content of a bar as a string
     * 
     * @param mixed $bar Bar object
     * @return string Bar content as ABC text
     */
    private function getBarContent($bar): string
    {
        // Try to get contentText property
        if (isset($bar->contentText)) {
            return $bar->contentText;
        }

        // Fall back to concatenating notes
        if (isset($bar->notes) && is_array($bar->notes)) {
            $result = '';
            foreach ($bar->notes as $note) {
                $result .= $this->getNoteText($note);
            }
            return $result;
        }

        return '';
    }

    /**
     * Extract note text from a note object
     * 
     * @param mixed $note Note object
     * @return string Note text
     */
    private function getNoteText($note): string
    {
        // Try various methods to get the note text
        if (method_exists($note, '__toString')) {
            return (string)$note;
        }
        if (method_exists($note, 'get_body_out')) {
            return $note->get_body_out();
        }
        if (isset($note->pitch)) {
            $text = '';
            if (isset($note->sharpflat)) {
                $text .= $note->sharpflat;
            }
            $text .= $note->pitch;
            if (isset($note->octave)) {
                $text .= $note->octave;
            }
            if (isset($note->length)) {
                $text .= $note->length;
            }
            return $text;
        }
        return '';
    }

    /**
     * Assign canntaireachd syllables to individual notes
     * 
     * @param array $notes Array of note objects
     * @param string $canntText Canntaireachd text with syllables
     * @return void
     */
    private function assignSyllablesToNotes(array $notes, string $canntText): void
    {
        // Split canntText into syllables (by spaces and bars)
        $syllables = preg_split('/[\s|]+/', trim($canntText), -1, PREG_SPLIT_NO_EMPTY);
        
        // Assign syllables to notes (one per note)
        $syllableIndex = 0;
        foreach ($notes as $note) {
            if ($syllableIndex >= count($syllables)) {
                break;
            }
            
            // Set canntaireachd for this note
            if (method_exists($note, 'setCanntaireachd')) {
                $note->setCanntaireachd($syllables[$syllableIndex]);
                $syllableIndex++;
            }
        }
    }
}
