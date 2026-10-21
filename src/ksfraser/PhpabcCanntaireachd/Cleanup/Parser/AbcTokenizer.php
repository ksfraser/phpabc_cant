<?php

declare(strict_types=1);

namespace ksfraser\PhpabcCanntaireachd\Cleanup\Parser;

use ksfraser\PhpabcCanntaireachd\Cleanup\Model\AbcNoteModel;

/**
 * Splits an ABC music line into its notes and bar lines.
 *
 * The ABC grammar for one note is:
 *
 *     [!decoration!] [{grace}] ["chord"] [_ = ^] pitch [octave] [length]
 *
 * A grace-note group belongs to the note that follows it, so "{g}A" is one
 * note with the grace group attached — which is also how the canntaireachd
 * dictionary stores it.
 *
 * The tokenizer is deliberately not whitespace based: "A2B2" is two notes,
 * and "A2 B2" is two notes as well.
 *
 * @package ksfraser\PhpabcCanntaireachd\Cleanup\Parser
 *
 * @since 2.2.0
 */
class AbcTokenizer
{
    /**
     * The bar lines ABC recognises, longest first so that ":|]" wins over ":|".
     *
     * @var string[]
     */
    public const BAR_LINES = [
        ':|]', ':|1', ':|2', '[|:', '[:', ':]', '::', '|]', '[|', '|1', '|2', '[1', '[2',
        '||', ':|', '|:', '|',
    ];

    /**
     * Bar lines that open a bar rather than close it.
     *
     * @var string[]
     */
    public const OPENING_BAR_LINES = ['[:', '[|', '[1', '[2'];

    /**
     * Split a music line into its items.
     *
     * @param string $line The music, without the inline voice/meter markers.
     * @return array<int, array{type: string, note?: AbcNoteModel, bar?: string, text?: string}>
     *         One entry per note, per bar line, and per piece of text that is
     *         not a note.
     */
    public function tokenize(string $line): array
    {
        $items = [];
        $length = strlen($line);
        $i = 0;

        while ($i < $length) {
            $char = $line[$i];

            // Whitespace is kept: the lyrics under a line are aligned with
            // the notes, so the spacing of the music is part of the score.
            if ($char === ' ' || $char === "\t") {
                $space = '';
                while ($i < $length && ($line[$i] === ' ' || $line[$i] === "\t")) {
                    $space .= $line[$i];
                    $i++;
                }
                $items[] = ['type' => 'other', 'text' => $space];
                continue;
            }

            // A comment runs to the end of the line.
            if ($char === '%') {
                break;
            }

            // The "$" line ending marker.
            if ($char === '$') {
                $items[] = ['type' => 'lineend', 'text' => '$'];
                $i++;
                continue;
            }

            // A bar line.
            $barLine = $this->matchBarLine($line, $i);
            if ($barLine !== null) {
                $items[] = ['type' => 'bar', 'bar' => $barLine];
                $i += strlen($barLine);
                continue;
            }

            // A decoration: !trill!  It is kept as it was written.
            if ($char === '!') {
                $end = strpos($line, '!', $i + 1);
                if ($end === false) {
                    $items[] = ['type' => 'other', 'text' => $char];
                    $i++;
                    continue;
                }
                $items[] = ['type' => 'other', 'text' => substr($line, $i, $end - $i + 1)];
                $i = $end + 1;
                continue;
            }

            // A grace-note group: {g}
            $grace = '';
            $prefix = '';
            if ($char === '{') {
                $end = strpos($line, '}', $i + 1);
                if ($end === false) {
                    $i++;
                    continue;
                }
                $grace = substr($line, $i, $end - $i + 1);
                $prefix .= $grace;
                $i = $end + 1;
            }

            // A chord symbol: "dfa"
            $chord = '';
            if ($i < $length && $line[$i] === '"') {
                $end = strpos($line, '"', $i + 1);
                if ($end === false) {
                    $i++;
                    continue;
                }
                $chord = substr($line, $i + 1, $end - $i - 1);
                $prefix .= '"' . $chord . '"';
                $i = $end + 1;
            }

            // Accidentals: _ (flat), = (natural), ^ (sharp)
            $accidentals = '';
            while ($i < $length && ($line[$i] === '_' || $line[$i] === '=' || $line[$i] === '^')) {
                $accidentals .= $line[$i];
                $prefix .= $line[$i];
                $i++;
            }

            // The pitch.
            if ($i >= $length || preg_match('/^[A-Ga-gzZ]$/', $line[$i]) !== 1) {
                // Not a note after all. The text is kept, so that a clean up
                // does not lose any of the music.
                $items[] = ['type' => 'other', 'text' => $prefix . ($i < $length ? $line[$i] : '')];
                $prefix = '';
                $i++;
                continue;
            }
            $pitch = $line[$i];
            $i++;

            // Octave marks.
            $octave = '';
            while ($i < $length && ($line[$i] === "'" || $line[$i] === ',')) {
                $octave .= $line[$i];
                $i++;
            }

            // The length.
            $noteLength = '';
            if (preg_match('/^(\d+(?:\/\d+)?|\/+)/', substr($line, $i), $matches) === 1) {
                $noteLength = $matches[0];
                $i += strlen($noteLength);
            }

            $items[] = [
                'type' => 'note',
                'note' => new AbcNoteModel($pitch, $octave, $noteLength, $grace, $accidentals, $chord),
            ];
            $prefix = '';
        }

        return $items;
    }

    /**
     * The bar line at the given position, if there is one.
     *
     * @param string $line
     * @param int $offset
     * @return string|null
     */
    public function matchBarLine(string $line, int $offset): ?string
    {
        foreach (self::BAR_LINES as $barLine) {
            if (substr($line, $offset, strlen($barLine)) === $barLine) {
                return $barLine;
            }
        }
        return null;
    }
}
