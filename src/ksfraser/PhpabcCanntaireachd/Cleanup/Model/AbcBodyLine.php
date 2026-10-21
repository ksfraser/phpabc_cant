<?php

declare(strict_types=1);

namespace ksfraser\PhpabcCanntaireachd\Cleanup\Model;

/**
 * One line of the body of an ABC tune.
 *
 * A body line carries the inline markers that say which voice and which meter
 * it belongs to ("[V:M][M:2/4]"), and the bars of music itself.
 *
 * @package ksfraser\PhpabcCanntaireachd\Cleanup\Model
 *
 * @since 2.2.0
 */
class AbcBodyLine
{
    /** @var string The voice this line belongs to, e.g. "M". */
    private string $voice = '';

    /** @var string The meter in force on this line, e.g. "2/4". */
    private string $meter = '';

    /** @var string The key in force on this line, e.g. "HP". */
    private string $key = '';

    /** @var AbcBarModel[] */
    private array $bars = [];

    /** @var string The line ending marker, "$" when the line continues. */
    private string $lineEnding = '';

    /** @var string[] The lyrics or canntaireachd lines that follow this one. */
    private array $lyrics = [];

    /**
     * @param AbcBarModel[] $bars
     */
    public function __construct(array $bars = [], string $voice = '', string $meter = '', string $key = '')
    {
        $this->bars = $bars;
        $this->voice = $voice;
        $this->meter = $meter;
        $this->key = $key;
    }

    /**
     * @return AbcBarModel[]
     */
    public function getBars(): array
    {
        return $this->bars;
    }

    /**
     * @param AbcBarModel[] $bars
     */
    public function setBars(array $bars): void
    {
        $this->bars = $bars;
    }

    public function addBar(AbcBarModel $bar): void
    {
        $this->bars[] = $bar;
    }

    public function getVoice(): string
    {
        return $this->voice;
    }

    public function setVoice(string $voice): void
    {
        $this->voice = $voice;
    }

    public function getMeter(): string
    {
        return $this->meter;
    }

    public function setMeter(string $meter): void
    {
        $this->meter = $meter;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function setKey(string $key): void
    {
        $this->key = $key;
    }

    /**
     * @return string[]
     */
    public function getLyrics(): array
    {
        return $this->lyrics;
    }

    public function setLyrics(array $lyrics): void
    {
        $this->lyrics = $lyrics;
    }

    public function addLyrics(string $lyrics): void
    {
        $this->lyrics[] = $lyrics;
    }

    /**
     * The lyrics of this line, split into one group per bar.
     *
     * @return string[]
     */
    public function getLyricBars(): array
    {
        $groups = [];
        foreach ($this->lyrics as $lyrics) {
            $text = preg_replace('/^w:\s*/i', '', $lyrics) ?? $lyrics;
            foreach (explode('|', $text) as $group) {
                $groups[] = $group;
            }
        }
        return $groups;
    }

    public function getLineEnding(): string
    {
        return $this->lineEnding;
    }

    public function setLineEnding(string $lineEnding): void
    {
        $this->lineEnding = $lineEnding;
    }

    /**
     * The inline markers of this line, e.g. "[V:M][M:2/4]".
     */
    public function renderMarkers(): string
    {
        $out = '';
        if ($this->voice !== '') {
            $out .= '[V:' . $this->voice . ']';
        }
        if ($this->key !== '') {
            $out .= '[K:' . $this->key . ']';
        }
        if ($this->meter !== '') {
            $out .= '[M:' . $this->meter . ']';
        }
        return $out;
    }

    /**
     * The line as it is written in the ABC file.
     */
    public function render(): string
    {
        $out = $this->renderMarkers();
        foreach ($this->bars as $bar) {
            $out .= $bar->render();
        }
        return $out . $this->lineEnding;
    }

    /**
     * The line and the lyrics that follow it.
     *
     * @return string[]
     */
    public function renderWithLyrics(): array
    {
        $out = [$this->render()];
        foreach ($this->lyrics as $lyrics) {
            $out[] = $lyrics;
        }
        return $out;
    }
}
