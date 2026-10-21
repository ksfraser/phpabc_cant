<?php

declare(strict_types=1);

namespace ksfraser\PhpabcCanntaireachd\Cleanup\Model;

use ksfraser\PhpabcCanntaireachd\Cleanup\Parser\AbcTokenizer;

/**
 * One tune of an ABC file.
 *
 * A tune holds its header fields (each field may appear more than once), the
 * include block, the voice headers, and the body lines.
 *
 * @package ksfraser\PhpabcCanntaireachd\Cleanup\Model
 *
 * @since 2.2.0
 */
class AbcTuneModel
{
    /** @var array<string, string[]> Header field letter => its lines. */
    private array $headers = [];

    /**
     * The header block, in the order it was written.
     *
     * Each entry is ['field' => string, 'line' => string]. A comment in the
     * header block has an empty field, so that it keeps its place when the
     * header fields are put in order.
     *
     * @var array<int, array{field: string, line: string}>
     */
    private array $headerBlock = [];

    /** @var string[] The %%abc-include block, in the order it was found. */
    private array $includes = [];

    /** @var string[] Lines that come before the first header field. */
    private array $preamble = [];

    /** @var string[] The %% directives of the tune, e.g. %%MIDI program 110. */
    private array $directives = [];

    /** @var AbcBodyLine[] */
    private array $bodyLines = [];

    /** @var string[] Comments that come after the body. */
    private array $trailing = [];

    /** @var string[] Comments that sit in the header block. */
    private array $headerComments = [];

    /** @var string The tune number, taken from the X: field. */
    private string $tuneNumber = '';

    /**
     * @return array<string, string[]>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @param string $field
     * @return string[]
     */
    public function getHeader(string $field): array
    {
        return $this->headers[$field] ?? [];
    }

    public function getHeaderValue(string $field): ?string
    {
        $lines = $this->getHeader($field);
        if ($lines === []) {
            return null;
        }
        $line = $lines[0];
        $pos = strpos($line, ':');
        return $pos === false ? $line : trim(substr($line, $pos + 1));
    }

    /**
     * @param string $field
     * @param string[] $lines
     */
    public function setHeader(string $field, array $lines): void
    {
        if ($lines === []) {
            unset($this->headers[$field]);
            return;
        }
        $this->headers[$field] = $lines;
        $this->rebuildHeaderBlock();
    }

    /**
     * Rebuild the header block from the header fields, keeping the comments
     * where they were.
     *
     * @return void
     */
    private function rebuildHeaderBlock(): void
    {
        $block = [];
        $fields = array_keys($this->headers);
        $index = 0;
        foreach ($this->headerBlock as $entry) {
            if ($entry['field'] === '') {
                $block[] = $entry;
                continue;
            }
            $position = array_search($entry['field'], $fields, true);
            if ($position === false) {
                continue;
            }
            // The comments that follow this field stay with it.
            for ($i = $index; $i < count($block); $i++) {
                $block[$i] = $block[$i];
            }
            $block[] = ['field' => $entry['field'], 'line' => $entry['line']];
            $index = count($block);
        }
        $this->headerBlock = $block;
    }

    public function addHeaderLine(string $field, string $line): void
    {
        $this->headers[$field][] = $line;
        $this->headerBlock[] = ['field' => $field, 'line' => $line];
    }

    /**
     * A comment in the header block. It keeps its place when the header
     * fields are put in order.
     *
     * @param string $line
     * @return void
     */
    public function addHeaderComment(string $line): void
    {
        $this->headerBlock[] = ['field' => '', 'line' => $line];
    }

    /**
     * The header block, in the order it was written.
     *
     * @return array<int, array{field: string, line: string}>
     */
    public function getHeaderBlock(): array
    {
        return $this->headerBlock;
    }

    public function removeHeader(string $field): void
    {
        unset($this->headers[$field]);
    }

    /**
     * @return string[]
     */
    public function getIncludes(): array
    {
        return $this->includes;
    }

    /**
     * @param string[] $includes
     */
    public function setIncludes(array $includes): void
    {
        $this->includes = $includes;
    }

    /**
     * @return string[]
     */
    public function getPreamble(): array
    {
        return $this->preamble;
    }

    /**
     * @param string[] $preamble
     */
    public function setPreamble(array $preamble): void
    {
        $this->preamble = $preamble;
    }

    /**
     * @return string[]
     */
    public function getDirectives(): array
    {
        return $this->directives;
    }

    /**
     * @param string[] $directives
     */
    public function setDirectives(array $directives): void
    {
        $this->directives = $directives;
    }

    /**
     * @return AbcBodyLine[]
     */
    public function getBodyLines(): array
    {
        return $this->bodyLines;
    }

    /**
     * @param AbcBodyLine[] $bodyLines
     */
    public function setBodyLines(array $bodyLines): void
    {
        $this->bodyLines = $bodyLines;
    }

    /**
     * @return string[]
     */
    public function getHeaderComments(): array
    {
        $comments = [];
        foreach ($this->headerBlock as $entry) {
            if ($entry['field'] === '') {
                $comments[] = $entry['line'];
            }
        }
        return $comments;
    }

    /**
     * @param string[] $headerComments
     */
    public function setHeaderComments(array $headerComments): void
    {
        $this->headerComments = $headerComments;
    }

    /**
     * @return string[]
     */
    public function getTrailing(): array
    {
        return $this->trailing;
    }

    /**
     * @param string[] $trailing
     */
    public function setTrailing(array $trailing): void
    {
        $this->trailing = $trailing;
    }

    public function getTuneNumber(): string
    {
        return $this->tuneNumber;
    }

    public function setTuneNumber(string $tuneNumber): void
    {
        $this->tuneNumber = $tuneNumber;
    }

    /**
     * The voice headers of this tune, e.g. 'V:M name="Melody"'.
     *
     * @return string[]
     */
    public function getVoiceHeaders(): array
    {
        return $this->getHeader('V');
    }

    /**
     * The title of the tune, from the first T: field.
     */
    public function getTitle(): string
    {
        return $this->getHeaderValue('T') ?? '';
    }

    /**
     * The meter of the tune, from the M: field.
     */
    public function getMeter(): string
    {
        return $this->getHeaderValue('M') ?? '';
    }

    /**
     * The key of the tune, from the K: field.
     */
    public function getKey(): string
    {
        return $this->getHeaderValue('K') ?? '';
    }

    /**
     * Is this a bagpipe tune? A tune in the Highland pipes key is one.
     */
    public function isBagpipeTune(): bool
    {
        $key = $this->getKey();
        return stripos($key, 'HP') !== false;
    }

    /**
     * The tunes as they would be written out.
     *
     * @param string[] $headerOrder The header fields, in the order to write them.
     * @return string[]
     */
    public function render(array $headerOrder): array
    {
        $lines = [];
        foreach ($this->preamble as $line) {
            $lines[] = $line;
        }
        foreach ($this->includes as $line) {
            $lines[] = $line;
        }
        // The header fields, in the order they are to be written. A comment
        // keeps the place of the field it follows.
        $block = $this->headerBlock;
        $positions = [];
        $lastFieldPosition = -1.0;
        foreach ($block as $index => $entry) {
            if ($entry['field'] === '') {
                $positions[$index] = $lastFieldPosition + 0.001;
                continue;
            }
            $found = array_search($entry['field'], $headerOrder, true);
            $lastFieldPosition = $found === false ? (float)count($headerOrder) : (float)$found;
            $positions[$index] = $lastFieldPosition;
        }
        $keys = array_keys($block);
        usort($keys, static function ($a, $b) use ($positions): int {
            if ($positions[$a] === $positions[$b]) {
                return $a <=> $b;
            }
            return $positions[$a] <=> $positions[$b];
        });
        foreach ($keys as $key) {
            $lines[] = $block[$key]['line'];
        }
        foreach ($this->headerComments as $line) {
            $lines[] = $line;
        }
        foreach ($this->directives as $line) {
            $lines[] = $line;
        }
        foreach ($this->bodyLines as $bodyLine) {
            foreach ($bodyLine->renderWithLyrics() as $line) {
                $lines[] = $line;
            }
        }
        foreach ($this->trailing as $line) {
            $lines[] = $line;
        }
        return $lines;
    }

    /**
     * Build the body line of a music line.
     *
     * The inline markers ("[V:M][M:2/4]") are read from the start of the
     * line, and the rest of the line is split into bars.
     *
     * @param string $line
     * @param AbcTokenizer $tokenizer
     * @return AbcBodyLine
     */
    public static function makeBodyLine(string $line, AbcTokenizer $tokenizer): AbcBodyLine
    {
        $voice = '';
        $meter = '';
        $key = '';
        $body = $line;

        // Read the inline markers. A marker that is not one we know is kept
        // as it was written.
        $kept = '';
        while (preg_match('/^\s*\[([A-Za-z]):([^\]]*)\]/', $body, $matches) === 1) {
            $field = strtoupper($matches[1]);
            $value = $matches[2];
            if ($field === 'V') {
                $voice = $value;
            } elseif ($field === 'M') {
                $meter = $value;
            } elseif ($field === 'K') {
                $key = $value;
            } else {
                $kept .= $matches[0];
            }
            $body = substr($body, strlen($matches[0]));
        }
        $body = $kept . $body;

        $bars = [];
        $items = [];
        $opening = '';
        $lineEnding = '';
        foreach ($tokenizer->tokenize($body) as $item) {
            if ($item['type'] === 'note') {
                $items[] = ['note', $item['note']];
                continue;
            }
            if ($item['type'] === 'other') {
                $items[] = ['other', $item['text']];
                continue;
            }
            if ($item['type'] === 'lineend') {
                $lineEnding = ' $';
                continue;
            }
            $barLine = $item['bar'];
            if (in_array($barLine, AbcTokenizer::OPENING_BAR_LINES, true)) {
                // An opening bar line starts the bar it precedes. It is kept
                // as it was written, so that the spacing of the line stays.
                $items[] = ['other', $barLine];
                $opening .= $barLine;
                continue;
            }
            if ($items === [] && $bars === []) {
                // A bar line before any note opens the first bar rather than
                // closing an empty one, so that the bar count of the line
                // matches the number of lyric groups under it.
                $items[] = ['other', $barLine];
                $opening .= $barLine;
                continue;
            }
            $bars[] = new AbcBarModel($items, $barLine, $opening);
            $items = [];
            $opening = '';
        }
        if ($items !== []) {
            $bars[] = new AbcBarModel($items, '', $opening);
        } elseif ($bars === [] && $opening !== '') {
            $bars[] = new AbcBarModel([], '', $opening);
        }

        $bodyLine = new AbcBodyLine($bars, $voice, $meter, $key);
        if ($lineEnding !== '') {
            $bodyLine->setLineEnding($lineEnding);
        }
        return $bodyLine;
    }
}
