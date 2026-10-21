<?php

declare(strict_types=1);

namespace ksfraser\PhpabcCanntaireachd\Tune;

use ksfraser\PhpabcCanntaireachd\Voices\TargetVoiceResolver;

/**
 * Copies melody lines to a Bagpipes voice in ABC tune data.
 *
 * The copier is line-preserving: lines that are not part of the melody voice
 * are emitted unchanged, in their original order and with their original
 * spacing. When the tune already has a bagpipe voice nothing is copied at
 * all.
 *
 * The result is the standard ABC layout — all the voice headers together,
 * then the bodies:
 *
 *   V:M name="Melody"
 *   V:Bagpipes name="Bagpipes" sname="Bagpipes"
 *   [V:M] A4 | B4 |
 *   [V:Bagpipes] A4 | B4 |
 *
 * SRP: Handles only the melody-to-bagpipes transformation.
 *
 * @package ksfraser\PhpabcCanntaireachd\Tune
 *
 * @since 2.1.0
 */
class MelodyToBagpipesCopier
{
    /** @var TargetVoiceResolver */
    private TargetVoiceResolver $resolver;

    public function __construct(?TargetVoiceResolver $resolver = null)
    {
        $this->resolver = $resolver ?? new TargetVoiceResolver();
    }

    /**
     * Copy the melody voice into a new Bagpipes voice.
     *
     * @param string[] $lines ABC lines for a single tune.
     * @return string[] Modified lines with a Bagpipes voice added if needed.
     */
    public function copy(array $lines): array
    {
        $melodyVoiceId = $this->findMelodyVoiceId($lines);
        if ($melodyVoiceId === null) {
            return $lines;
        }
        if ($this->hasBagpipeVoice($lines, $melodyVoiceId)) {
            return $lines;
        }

        $output = [];
        $headerInserted = false;
        $pendingCopies = [];

        foreach ($lines as $line) {
            $trimmed = trim($line);

            // The Bagpipes voice header goes directly after the melody header.
            if (!$headerInserted && $this->isVoiceHeaderFor($trimmed, $melodyVoiceId)) {
                $output[] = $line;
                $output[] = 'V:Bagpipes name="Bagpipes" clef=treble octave=0';
                $headerInserted = true;
                continue;
            }

            $output[] = $line;

            // A music line of the melody voice is copied, with the voice
            // marker rewritten to the new voice.
            if ($this->isMelodyMusicLine($trimmed, $melodyVoiceId)) {
                $pendingCopies[] = $this->copyLineForBagpipes($trimmed);
            }
        }

        // The copied music follows the melody's own music.
        if ($pendingCopies !== []) {
            $output = $this->insertAfterMelodyMusic($output, $melodyVoiceId, $pendingCopies);
        }

        return $output;
    }

    /**
     * The identifier of the melody voice, or null when the tune has none.
     *
     * @param string[] $lines
     * @return string|null
     */
    private function findMelodyVoiceId(array $lines): ?string
    {
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if (preg_match('/^V:\s*(\S+)(.*)$/', $trimmed, $matches) !== 1) {
                continue;
            }
            $name = TargetVoiceResolver::extractName($matches[2]);
            $id = $matches[1];
            if (strcasecmp($id, 'melody') === 0 || strcasecmp($id, 'm') === 0) {
                return $id;
            }
            if ($name !== null && strcasecmp(trim($name), 'melody') === 0) {
                return $id;
            }
        }
        return null;
    }

    /**
     * Does the tune already declare a bagpipe voice?
     *
     * @param string[] $lines
     * @param string $melodyVoiceId The melody voice, which is not a bagpipe voice.
     * @return bool
     */
    private function hasBagpipeVoice(array $lines, string $melodyVoiceId): bool
    {
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if (preg_match('/^V:\s*(\S+)(.*)$/', $trimmed, $matches) !== 1) {
                continue;
            }
            if ($matches[1] === $melodyVoiceId) {
                continue;
            }
            $name = TargetVoiceResolver::extractName($matches[2]);
            if ($name !== null && strcasecmp(trim($name), 'melody') === 0) {
                continue;
            }
            if ($this->resolver->isTarget($matches[1], $name)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Is this line the header of the given voice?
     *
     * @param string $trimmed
     * @param string $voiceId
     * @return bool
     */
    private function isVoiceHeaderFor(string $trimmed, string $voiceId): bool
    {
        return preg_match('/^V:\s*(\S+)/', $trimmed, $matches) === 1
            && $matches[1] === $voiceId;
    }

    /**
     * Is this line music belonging to the melody voice?
     *
     * @param string $trimmed
     * @param string $voiceId
     * @return bool
     */
    private function isMelodyMusicLine(string $trimmed, string $voiceId): bool
    {
        if (trim($trimmed) === '' || str_starts_with($trimmed, '%')) {
            return false;
        }
        if (preg_match('/^\[V:([^\]]+)\]/', $trimmed, $matches) === 1) {
            return $matches[1] === $voiceId;
        }
        // A bare music line belongs to the current voice, which is the one
        // declared by the most recent V: header.
        return preg_match('/^V:/', $trimmed) !== 1
            && preg_match('/^[A-Za-z]:/', $trimmed) !== 1
            && preg_match('/[A-Ga-gzZ]/', $trimmed) === 1;
    }

    /**
     * Rewrite a melody line so that it belongs to the Bagpipes voice.
     *
     * @param string $trimmed
     * @return string
     */
    private function copyLineForBagpipes(string $trimmed): string
    {
        return preg_replace('/^\[V:[^\]]+\]/', '[V:Bagpipes]', $trimmed) ?? $trimmed;
    }

    /**
     * Insert the copied music directly after the melody's own music.
     *
     * @param string[] $output
     * @param string $voiceId
     * @param string[] $copies
     * @return string[]
     */
    private function insertAfterMelodyMusic(array $output, string $voiceId, array $copies): array
    {
        $result = [];
        $inserted = false;
        $currentVoice = null;

        foreach ($output as $line) {
            $trimmed = trim($line);
            if (preg_match('/^V:\s*(\S+)/', $trimmed, $matches) === 1) {
                $currentVoice = $matches[1];
            }
            $result[] = $line;

            if (!$inserted && $currentVoice === $voiceId && $this->isMelodyMusicLine($trimmed, $voiceId)) {
                foreach ($copies as $copy) {
                    $result[] = $copy;
                }
                $inserted = true;
            }
        }

        if (!$inserted) {
            foreach ($copies as $copy) {
                $result[] = $copy;
            }
        }

        return $result;
    }
}
