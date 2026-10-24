<?php

declare(strict_types=1);

namespace ksfraser\PhpabcCanntaireachd\Cleanup;

/**
 * A V: header line, read into its voice indicator and its attributes.
 *
 * The attributes are written back in the order the pipe band scores use:
 * the stems, then the staff, the clef and the transposition, then the name
 * and the short name.
 *
 * @package ksfraser\PhpabcCanntaireachd\Cleanup
 *
 * @since 2.2.0
 */
class VoiceHeader
{
    /**
     * The order the attributes are written in.
     *
     * @var string[]
     */
    public const ATTRIBUTE_ORDER = [
        'gstem', 'stem', 'dyn', 'stafflines', 'clef', 'middle',
        'transpose', 'octave', 'name', 'sname', 'map',
    ];

    /** @var string */
    private string $indicator;

    /** @var array<string, string> */
    private array $attributes = [];

    /**
     * @param string $indicator
     * @param array<string, string> $attributes
     */
    public function __construct(string $indicator = '', array $attributes = [])
    {
        $this->indicator = $indicator;
        foreach ($attributes as $key => $value) {
            $this->attributes[$key] = $value;
        }
    }

    /**
     * Read a V: line.
     *
     * @param string $line
     * @return VoiceHeader|null
     */
    public static function parse(string $line): ?self
    {
        $trimmed = trim($line);
        if (preg_match('/^V:\s*(\S+)(.*)$/', $trimmed, $matches) !== 1) {
            return null;
        }
        $header = new self($matches[1]);
        $rest = trim($matches[2]);

        // name="..." and sname="..." may hold spaces, so they are read first.
        if (preg_match_all('/\b(name|sname)="([^"]*)"/i', $rest, $matches, PREG_SET_ORDER) > 0) {
            foreach ($matches as $match) {
                $key = strtolower($match[1]);
                $header->attributes[$key] = $match[2];
                $rest = str_replace($match[0], '', $rest);
            }
        }

        // The remaining attributes are key=value pairs.
        if (preg_match_all('/(\w+)=("[^"]*"|\S+)/', $rest, $matches, PREG_SET_ORDER) > 0) {
            foreach ($matches as $match) {
                $value = $match[2];
                if (strlen($value) > 1 && $value[0] === '"' && substr($value, -1) === '"') {
                    $value = substr($value, 1, -1);
                }
                $key = strtolower($match[1]);
                // A quoted value wins over a bare one.
                if (!isset($header->attributes[$key])) {
                    $header->attributes[$key] = $value;
                }
            }
        }

        return $header;
    }

    public function getIndicator(): string
    {
        return $this->indicator;
    }

    public function setIndicator(string $indicator): void
    {
        $this->indicator = $indicator;
    }

    /**
     * @return array<string, string>
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function get(string $key): ?string
    {
        return $this->attributes[strtolower($key)] ?? null;
    }

    public function has(string $key): bool
    {
        return isset($this->attributes[strtolower($key)]);
    }

    public function set(string $key, string $value): void
    {
        $this->attributes[strtolower($key)] = $value;
    }

    public function remove(string $key): void
    {
        unset($this->attributes[strtolower($key)]);
    }

    /**
     * The name of the voice, falling back to the indicator.
     */
    public function getName(): string
    {
        return $this->get('name') ?? $this->indicator;
    }

    /**
     * The short name of the voice, falling back to the name.
     */
    public function getSname(): string
    {
        return $this->get('sname') ?? $this->getName();
    }

    /**
     * The voice line as it is written in the ABC file.
     */
    public function render(): string
    {
        $indicator = $this->expandIndicator($this->indicator);

        $out = 'V:' . $indicator;

        $written = [];
        foreach (self::ATTRIBUTE_ORDER as $key) {
            if (!isset($this->attributes[$key])) {
                continue;
            }
            $written[$key] = true;
            $out .= ' ' . $key . '=' . $this->quoteIfNeeded($key, $this->attributes[$key]);
        }
        foreach ($this->attributes as $key => $value) {
            if (isset($written[$key])) {
                continue;
            }
            $out .= ' ' . $key . '=' . $this->quoteIfNeeded($key, $value);
        }

        return $out;
    }

    /**
     * Expand a short voice indicator to its full name on output.
     *
     * V:S  -> Snare
     * V:B  -> Bass
     * V:T  -> Tenor
     * V:M  -> Melody
     * V:H  -> Harmony
     * V:P  -> Pipes
     */
    private function expandIndicator(string $indicator): string
    {
        static $shortToLong = [
            'S' => 'Snare',
            'B' => 'Bass',
            'T' => 'Tenor',
            'M' => 'Melody',
            'H' => 'Harmony',
            'P' => 'Pipes',
        ];
        return $shortToLong[$indicator] ?? $indicator;
    }

    /**
     * The names are written in quotes, the other attributes are not.
     */
    private function quoteIfNeeded(string $key, string $value): string
    {
        if ($key === 'name' || $key === 'sname') {
            return '"' . $value . '"';
        }
        return $value;
    }
}
