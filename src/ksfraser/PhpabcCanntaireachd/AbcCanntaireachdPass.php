<?php

declare(strict_types=1);

namespace ksfraser\PhpabcCanntaireachd;

use ksfraser\PhpabcCanntaireachd\Log\FlowLog;
use ksfraser\PhpabcCanntaireachd\Voices\TargetVoiceResolver;

/**
 * Class AbcCanntaireachdPass
 *
 * Generates canntaireachd for the target voices of an ABC tune and emits it as
 * a `w:` line directly beneath the ABC line it belongs to.
 *
 * The pass is line-preserving: every input line is emitted unchanged, in its
 * original position and with its original spacing. The only additions are the
 * generated `w:` lines.
 *
 * Voice handling:
 *  - a "V:<id>" header sets the current voice, and declares its name;
 *  - an inline "[V:<id>]" marker switches the current voice and carries the
 *    music of that line;
 *  - a bare music line belongs to the current voice.
 *
 * A voice is a target when its identifier or its declared name matches
 * {@see TargetVoiceResolver::DEFAULT_TARGET_VOICES} ("Melody", "Bagpipes",
 * "Pipes"), case-insensitively. All lines of every target voice are processed.
 *
 * If a target music line already has `w:` lines beneath it they are kept, and
 * the generated canntaireachd is added after them.
 *
 * @package ksfraser\PhpabcCanntaireachd
 *
 * @requirement FR2, FR8
 *
 * @since 2.1.0
 */
class AbcCanntaireachdPass
{
    /** @var TokenDictionary */
    private TokenDictionary $dict;

    /** @var TargetVoiceResolver */
    private TargetVoiceResolver $resolver;

    /**
     * Constructor for AbcCanntaireachdPass.
     * Uses DI to inject TokenDictionary.
     *
     * @param TokenDictionary|array $dict The token dictionary for translations.
     * @param string[]|null $targetVoices Voice names that receive canntaireachd.
     * @requirement FR7
     */
    public function __construct($dict, ?array $targetVoices = null)
    {
        if ($dict instanceof TokenDictionary) {
            $this->dict = $dict;
        } else {
            $td = new TokenDictionary();
            $td->prepopulate((array) $dict);
            $this->dict = $td;
        }
        $this->resolver = new TargetVoiceResolver($targetVoices);
    }

    /**
     * Processes the array of ABC lines to generate canntaireachd for the target voices.
     *
     * @param string[] $lines The input ABC lines.
     * @return array{lines: string[], canntDiff: array<int, array{line: string, generated: string}>}
     *         The processed lines with canntaireachd added, and a diff log.
     * @requirement FR2, FR8
     */
    public function process(array $lines): array
    {
        $canntDiff = [];
        $output = [];

        $generator = new CanntGenerator($this->dict);
        $voiceNames = $this->resolver->collectVoiceNames($lines);
        $currentVoiceId = null;
        $currentVoiceName = null;

        $count = count($lines);
        for ($i = 0; $i < $count; $i++) {
            $line = $lines[$i];
            $trimmed = trim($line);

            // A new tune starts a new voice context.
            if (preg_match('/^X:/i', $trimmed) === 1) {
                $currentVoiceId = null;
                $currentVoiceName = null;
                $output[] = $line;
                continue;
            }

            // A voice header declares the current voice and, optionally, its name.
            if (preg_match('/^V:\s*(\S+)(.*)$/', $trimmed, $matches) === 1) {
                $currentVoiceId = $matches[1];
                $currentVoiceName = TargetVoiceResolver::extractName($matches[2]);
                if ($currentVoiceName !== null) {
                    $voiceNames[$currentVoiceId] = $currentVoiceName;
                }
                $output[] = $line;
                continue;
            }

            // An inline [V:id] marker switches the current voice.
            if (preg_match('/^\[V:([^\]]+)\]/', $trimmed, $matches) === 1) {
                $currentVoiceId = $matches[1];
                $currentVoiceName = $voiceNames[$currentVoiceId] ?? null;
            }

            $isTarget = $this->resolver->isTarget($currentVoiceId, $currentVoiceName);
            if (!$isTarget || !$this->isMusicLine($line)) {
                $output[] = $line;
                continue;
            }

            $output[] = $line;

            // Keep any hand-written lyrics that already sit under this line …
            while ($i + 1 < $count && preg_match('/^w:/i', trim($lines[$i + 1])) === 1) {
                $output[] = $lines[++$i];
            }

            // … and add the generated canntaireachd underneath them.
            $canntText = $this->normaliseCanntaireachd($generator->generateForNotes($line));
            if ($canntText !== '' && $canntText !== '[?]') {
                $output[] = 'w: ' . $canntText;
                $canntDiff[] = [
                    'line' => $line,
                    'generated' => $canntText,
                ];
            }
        }

        return ['lines' => $output, 'canntDiff' => $canntDiff];
    }

    /**
     * Normalise generated canntaireachd: single spaces, " | " between bars.
     *
     * @param string $cannt
     * @return string
     */
    private function normaliseCanntaireachd(string $cannt): string
    {
        $cannt = preg_replace('/\s*\|\s*/', ' | ', $cannt) ?? $cannt;
        $cannt = preg_replace('/ {2,}/', ' ', $cannt) ?? $cannt;
        return trim($cannt);
    }

    /**
     * Determines if canntaireachd should be added for this voice.
     *
     * @param string|null $voiceId   Voice identifier.
     * @param string|null $voiceName Declared voice name.
     * @return bool True if canntaireachd should be added.
     */
    public function shouldAddCanntaireachd(?string $voiceId, ?string $voiceName = null): bool
    {
        return $this->resolver->isTarget($voiceId, $voiceName);
    }

    /**
     * Determines if a line is a music line (contains notes or bars).
     *
     * @param string $line The line to check.
     * @return bool True if it's a music line.
     */
    private function isMusicLine(string $line): bool
    {
        $trimmed = trim($line);
        if ($trimmed === '') {
            return false;
        }
        // Comments and %% directives are not music.
        if (str_starts_with($trimmed, '%')) {
            return false;
        }
        // Header fields (X:, T:, K:, w:, …) are not music.
        if (preg_match('/^[A-Za-z]:/', $trimmed) === 1) {
            return false;
        }
        // Strip an inline voice marker before looking for notes.
        $body = preg_replace('/^\[V:[^\]]*\]/', '', $trimmed) ?? $trimmed;
        $body = trim($body);
        if ($body === '') {
            return false;
        }
        // A music line carries at least one note or rest. A line of nothing
        // but bar lines has no syllables to sing.
        return preg_match('/[A-Ga-gzZ]/', $body) === 1;
    }

    /**
     * Log the pass entry/exit for flow tracing.
     *
     * @param string $message
     * @return void
     */
    private function logFlow(string $message): void
    {
        FlowLog::log('AbcCanntaireachdPass: ' . $message, true);
    }
}
