<?php
namespace Ksfraser\PhpabcCanntaireachd\Voices;

use Ksfraser\PhpabcCanntaireachd\BagpipeAbcToCanntTranslator;

class BagpipeVoice extends AbcVoice {
    /**
     * Override base renderLyrics to include canntaireachd line.
     * @param BagpipeAbcToCanntTranslator $generator
     * @param string $musicLine
     * @return array of strings (w: lines)
     */
    public function renderLyrics($generator = null, $musicLine = null): array
    {
        // If generator and musicLine are provided, use them; else fallback to parent
        if ($generator && $musicLine) {
            return $this->renderLyricsWithCannt($generator, $musicLine);
        }
        // Fallback: just render lyrics if no generator/musicLine
        return parent::renderLyrics();
    }
    /**
     * Render canntaireachd line and lyrics lines for this voice.
     * @param BagpipeAbcToCanntTranslator $generator
     * @param string $musicLine
     * @return array of strings (w: lines)
     */
    public function renderLyricsWithCannt($generator, $musicLine): array
    {
        $out = [];
        // Generate canntaireachd for the music line
        $musicTokens = preg_split('/\s+/', trim($musicLine));
        $canntTokens = array_map(function($token) use ($generator) {
            $note = new \Ksfraser\PhpabcCanntaireachd\AbcNote($token);
            return $generator->translate($note);
        }, $musicTokens);
        $canntTextAligned = trim(implode(' ', $canntTokens));
        if ($canntTextAligned && $canntTextAligned !== '[?]') {
            $out[] = 'w: ' . $canntTextAligned;
        }
        // Add all lyrics lines (from parent)
        foreach (parent::renderLyrics() as $lyricLine) {
            $out[] = $lyricLine;
        }
        return $out;
    }
}
