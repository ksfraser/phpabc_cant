<?php

declare(strict_types=1);

namespace ksfraser\PhpabcCanntaireachd\Voices;

/**
 * Decides which ABC voices are canntaireachd targets.
 *
 * A voice is a target when its voice identifier ("V:Bagpipes") or its declared
 * name ("V:M name=\"Melody\"") matches one of the configured names,
 * case-insensitively. The identifier and the name are checked independently,
 * so both of these are recognised:
 *
 *   V:Melody
 *   V:M name="Melody" sname="Melody"
 *
 * This class owns only the *matching* decision. Deciding which lines belong
 * to a voice, and what to do with them, belongs to the caller.
 *
 * @package ksfraser\PhpabcCanntaireachd\Voices
 *
 * @since 2.1.0
 */
class TargetVoiceResolver
{
    /**
     * Voice names that receive canntaireachd by default.
     *
     * @var string[]
     */
    public const DEFAULT_TARGET_VOICES = [
        'melody',
        'bagpipes',
        'bagpipe',
        'pipes',
        'pipe',
    ];

    /**
     * Lower-cased target names.
     *
     * @var string[]
     */
    private array $targets;

    /**
     * @param string[]|null $targets Voice names to treat as targets. Defaults to
     *                                {@see self::DEFAULT_TARGET_VOICES}.
     */
    public function __construct(?array $targets = null)
    {
        $targets = $targets ?? self::DEFAULT_TARGET_VOICES;
        $this->targets = array_values(array_map(
            static fn ($name) => strtolower(trim((string) $name)),
            $targets
        ));
        $this->targets = array_values(array_filter($this->targets, static fn ($name) => $name !== ''));
    }

    /**
     * Is this voice a canntaireachd target?
     *
     * @param string|null $voiceId   Voice identifier, e.g. "Bagpipes" from "V:Bagpipes".
     * @param string|null $voiceName Declared voice name, e.g. "Melody" from name="Melody".
     * @return bool
     */
    public function isTarget(?string $voiceId, ?string $voiceName = null): bool
    {
        if ($voiceId !== null && $this->matches($voiceId)) {
            return true;
        }
        if ($voiceName !== null && $this->matches($voiceName)) {
            return true;
        }
        return false;
    }

    /**
     * Does a single value match one of the target names?
     *
     * An exact match wins; a value that merely starts with a target name
     * followed by a separator ("Bagpipes II", "Pipes (B)") also matches.
     *
     * @param string $value
     * @return bool
     */
    private function matches(string $value): bool
    {
        $value = strtolower(trim($value));
        if ($value === '') {
            return false;
        }
        if (in_array($value, $this->targets, true)) {
            return true;
        }
        foreach ($this->targets as $target) {
            foreach ([' ', '(', '-', '_'] as $separator) {
                if (str_starts_with($value, $target . $separator)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Extract the name="..." value from the parameters of a V: header.
     *
     * @param string $params Everything after the voice identifier on a V: line.
     * @return string|null The declared name, or null when there is none.
     */
    public static function extractName(string $params): ?string
    {
        if (preg_match('/\bname="([^"]*)"/i', $params, $matches) === 1) {
            return $matches[1];
        }
        return null;
    }

    /**
     * Map every voice identifier declared in the file to its declared name.
     *
     * A pre-scan is needed because a voice is often introduced inline
     * ("[V:M] ...") long before — or without — a "V:M name=..." header, and
     * the header may appear after the body lines it describes.
     *
     * @param string[] $lines
     * @return array<string,string> Voice identifier => declared name.
     */
    public function collectVoiceNames(array $lines): array
    {
        $names = [];
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if (preg_match('/^V:\s*(\S+)(.*)$/', $trimmed, $matches) !== 1) {
                continue;
            }
            $name = self::extractName($matches[2]);
            if ($name !== null && $name !== '') {
                $names[$matches[1]] = $name;
            }
        }
        return $names;
    }
}
