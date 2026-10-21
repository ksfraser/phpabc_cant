<?php

declare(strict_types=1);

namespace ksfraser\PhpabcCanntaireachd\Cleanup;

use ksfraser\PhpabcCanntaireachd\Cleanup\Model\AbcBarModel;
use ksfraser\PhpabcCanntaireachd\Cleanup\Model\AbcBodyLine;
use ksfraser\PhpabcCanntaireachd\Cleanup\Model\AbcNoteModel;
use ksfraser\PhpabcCanntaireachd\Cleanup\Model\AbcTuneModel;

/**
 * Lays the bars of a tune out again, four to a line.
 *
 * A pipe band score writes four bars to a line, or five when the tune starts
 * with an anacrusis, so that the first full line is not short. Every line
 * carries the voice it belongs to and the meter in force, so that the score
 * reads correctly, and every line but the last ends with the "$" line ending
 * marker.
 *
 * The lyrics under a line are only moved when they can be split into one
 * group per bar, which is the case when the number of groups matches the
 * number of bars. When it does not, the line is left as it is and the tune is
 * reported: the lyrics cannot be moved without guessing.
 *
 * @package ksfraser\PhpabcCanntaireachd\Cleanup
 *
 * @since 2.2.0
 */
class BarReflower
{
    /** @var int How many bars go on a line. */
    private int $barsPerLine;

    /** @var bool Add the "$" line ending marker. */
    private bool $lineEnding;

    /**
     * @param int $barsPerLine How many bars go on a line.
     * @param bool $lineEnding Add the "$" line ending marker.
     */
    public function __construct(int $barsPerLine = 4, bool $lineEnding = true)
    {
        $this->barsPerLine = max(1, $barsPerLine);
        $this->lineEnding = $lineEnding;
    }

    /**
     * Lay the body of a tune out again.
     *
     * @param AbcTuneModel $tune
     * @return array{lines: AbcBodyLine[], notes: string[]} The new body lines,
     *         and what was done.
     */
    public function reflow(AbcTuneModel $tune): array
    {
        $notes = [];
        $bodyLines = $tune->getBodyLines();
        if ($bodyLines === []) {
            return ['lines' => [], 'notes' => $notes];
        }

        $voices = $this->groupByVoice($bodyLines);
        $result = [];
        $lastVoice = array_key_last($voices);
        $index = 0;

        foreach ($voices as $voiceId => $lines) {
            $reflowed = $this->reflowVoice($tune, $voiceId, $lines, $notes);
            foreach ($reflowed as $line) {
                $result[] = $line;
                $index++;
            }
            if ($voiceId !== $lastVoice) {
                $notes[] = "voice $voiceId: " . count($reflowed) . ' lines';
            }
        }

        // The "$" marker goes on every line but the last.
        $last = count($result) - 1;
        foreach ($result as $index => $line) {
            if ($index === $last) {
                continue;
            }
            if ($this->lineEnding && !str_ends_with($line->getLineEnding(), '$')) {
                $line->setLineEnding(' $');
            }
        }

        return ['lines' => $result, 'notes' => $notes];
    }

    /**
     * The body lines of a tune, grouped by the voice they belong to.
     *
     * @param AbcBodyLine[] $bodyLines
     * @return array<string, AbcBodyLine[]>
     */
    private function groupByVoice(array $bodyLines): array
    {
        $voices = [];
        $current = '';
        foreach ($bodyLines as $bodyLine) {
            if ($bodyLine->getVoice() !== '') {
                $current = $bodyLine->getVoice();
            }
            $voices[$current][] = $bodyLine;
        }
        return $voices;
    }

    /**
     * Lay the lines of one voice out again.
     *
     * @param AbcTuneModel $tune
     * @param string $voiceId
     * @param AbcBodyLine[] $lines
     * @param string[] $notes
     * @return AbcBodyLine[]
     */
    private function reflowVoice(AbcTuneModel $tune, string $voiceId, array $lines, array &$notes): array
    {
        // The lyrics can only be moved when they can be split per bar.
        $splitLyrics = [];
        foreach ($lines as $line) {
            $bars = count($line->getBars());
            $groups = count($line->getLyricBars());
            if ($line->getLyrics() !== [] && $groups !== $bars) {
                $notes[] = sprintf(
                    'voice %s: the lyrics of a line have %d groups for %d bars, the line was left as it is',
                    $voiceId,
                    $groups,
                    $bars
                );
                return $lines;
            }
            $splitLyrics[] = $line->getLyrics() === [] ? [] : $line->getLyricBars();
        }

        // Gather the bars of the voice, with their lyrics.
        $bars = [];
        $lyrics = [];
        foreach ($lines as $index => $line) {
            foreach ($line->getBars() as $barIndex => $bar) {
                $bars[] = $bar;
                $lyrics[] = $splitLyrics[$index][$barIndex] ?? null;
            }
        }

        if ($bars === []) {
            return $lines;
        }

        // The first line holds five bars when the tune starts with an
        // anacrusis, so that the first full line is not short.
        $firstLineSize = $this->barsPerLine;
        if ($this->isAnacrusis($tune, $bars[0])) {
            $firstLineSize = $this->barsPerLine + 1;
        }

        $out = [];
        $meter = $tune->getMeter();
        $key = $tune->getKey();
        $index = 0;
        $count = count($bars);

        while ($index < $count) {
            $size = $index === 0 ? $firstLineSize : $this->barsPerLine;
            $group = array_slice($bars, $index, $size);
            $groupLyrics = array_slice($lyrics, $index, $size);
            $index += count($group);

            // The key is only written when it changes.
            $bodyLine = new AbcBodyLine($group, $voiceId, $meter, $index === 0 ? $key : '');
            $lyricText = $this->buildLyrics($groupLyrics);
            if ($lyricText !== null) {
                $bodyLine->setLyrics([$lyricText]);
            }
            $out[] = $bodyLine;
        }

        return $out;
    }

    /**
     * The lyrics line for a group of bars.
     *
     * @param array<int, string|null> $groups
     * @return string|null
     */
    private function buildLyrics(array $groups): ?string
    {
        $hasLyrics = false;
        foreach ($groups as $group) {
            if ($group !== null) {
                $hasLyrics = true;
            }
        }
        if (!$hasLyrics) {
            return null;
        }
        $parts = [];
        foreach ($groups as $group) {
            $parts[] = $group ?? '';
        }
        return 'w: ' . implode('|', $parts);
    }

    /**
     * Is this bar an anacrusis — a bar that is shorter than the meter?
     *
     * @param AbcTuneModel $tune
     * @param AbcBarModel $bar
     * @return bool
     */
    private function isAnacrusis(AbcTuneModel $tune, AbcBarModel $bar): bool
    {
        $barDuration = $this->barDuration($tune);
        if ($barDuration <= 0) {
            return false;
        }
        $duration = $this->durationOf($bar, $tune);
        return $duration > 0 && $duration < $barDuration;
    }

    /**
     * The length of a full bar, in the unit of length of the tune.
     *
     * @param AbcTuneModel $tune
     * @return float
     */
    private function barDuration(AbcTuneModel $tune): float
    {
        $meter = $this->fraction($tune->getMeter());
        $unit = $this->fraction($tune->getHeaderValue('L') ?? '');
        if ($meter === null || $unit === null || $unit[1] == 0) {
            return 0.0;
        }
        return $meter[0] * ($unit[1] / $meter[1]);
    }

    /**
     * The length of a bar, in the unit of length of the tune.
     *
     * @param AbcBarModel $bar
     * @param AbcTuneModel $tune
     * @return float
     */
    private function durationOf(AbcBarModel $bar, AbcTuneModel $tune): float
    {
        $unit = $this->fraction($tune->getHeaderValue('L') ?? '');
        if ($unit === null || $unit[1] == 0) {
            return 0.0;
        }
        $total = 0.0;
        foreach ($bar->getNotes() as $note) {
            $total += $this->noteLength($note, $unit);
        }
        return $total;
    }

    /**
     * The length of a note, in the unit of length of the tune.
     *
     * @param AbcNoteModel $note
     * @param array{0: float, 1: float} $unit
     * @return float
     */
    private function noteLength(AbcNoteModel $note, array $unit): float
    {
        $length = $note->getLength();
        if ($length === '') {
            return 1.0;
        }
        if (str_contains($length, '/')) {
            [$numerator, $denominator] = array_pad(explode('/', $length, 2), 2, '1');
            $numerator = (float)$numerator;
            $denominator = (float)$denominator;
            if ($denominator == 0) {
                return 1.0;
            }
            return $numerator / $denominator;
        }
        return (float)$length;
    }

    /**
     * A fraction such as "2/4" as its numerator and denominator.
     *
     * @param string $text
     * @return array{0: float, 1: float}|null
     */
    private function fraction(string $text): ?array
    {
        $text = trim($text);
        if (preg_match('#^(\d+)\s*/\s*(\d+)$#', $text, $matches) !== 1) {
            return null;
        }
        return [(float)$matches[1], (float)$matches[2]];
    }
}
