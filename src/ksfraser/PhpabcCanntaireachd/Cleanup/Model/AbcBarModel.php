<?php

declare(strict_types=1);

namespace ksfraser\PhpabcCanntaireachd\Cleanup\Model;

/**
 * One bar of an ABC music line: the notes it holds, any text that stands
 * between them, and the bar line that closes it.
 *
 * The text is kept, because a clean up must not lose any of the music: a
 * construct this project does not know about yet is carried through as it
 * was written.
 *
 * @package ksfraser\PhpabcCanntaireachd\Cleanup\Model
 *
 * @since 2.2.0
 */
class AbcBarModel
{
    /**
     * The items of the bar, in the order they are written.
     *
     * Each item is ["note", AbcNoteModel] or ["other", string].
     *
     * @var array<int, array{0: string, 1: mixed}>
     */
    private array $items = [];

    /** @var string The bar line that closes this bar, e.g. "|" or ":|]". */
    private string $barLine = '';

    /** @var string A bar line that opens this bar, e.g. "[:" for a repeat. */
    private string $openingBarLine = '';

    /**
     * @param array<int, array{0: string, 1: mixed}> $items
     */
    public function __construct(array $items = [], string $barLine = '', string $openingBarLine = '')
    {
        $this->items = $items;
        $this->barLine = $barLine;
        $this->openingBarLine = $openingBarLine;
    }

    /**
     * The items of the bar.
     *
     * @return array<int, array{0: string, 1: mixed}>
     */
    public function getItems(): array
    {
        return $this->items;
    }

    public function addNote(AbcNoteModel $note): void
    {
        $this->items[] = ['note', $note];
    }

    public function addText(string $text): void
    {
        if ($text === '') {
            return;
        }
        $this->items[] = ['other', $text];
    }

    /**
     * The notes of the bar.
     *
     * @return AbcNoteModel[]
     */
    public function getNotes(): array
    {
        $notes = [];
        foreach ($this->items as $item) {
            if ($item[0] === 'note') {
                $notes[] = $item[1];
            }
        }
        return $notes;
    }

    /**
     * Replace the notes of the bar, keeping the text between them.
     *
     * @param AbcNoteModel[] $notes
     */
    public function setNotes(array $notes): void
    {
        $items = [];
        $index = 0;
        foreach ($this->items as $item) {
            if ($item[0] === 'note') {
                if (!isset($notes[$index])) {
                    continue;
                }
                $items[] = ['note', $notes[$index]];
                $index++;
                continue;
            }
            $items[] = $item;
        }
        // Any note that is left over is appended.
        for (; $index < count($notes); $index++) {
            $items[] = ['note', $notes[$index]];
        }
        $this->items = $items;
    }

    public function getBarLine(): string
    {
        return $this->barLine;
    }

    public function setBarLine(string $barLine): void
    {
        $this->barLine = $barLine;
    }

    public function getOpeningBarLine(): string
    {
        return $this->openingBarLine;
    }

    public function setOpeningBarLine(string $openingBarLine): void
    {
        $this->openingBarLine = $openingBarLine;
    }

    /**
     * The pitch letters used in this bar, e.g. ['G', 'A', 'c'].
     *
     * @return string[]
     */
    public function getPitches(): array
    {
        $pitches = [];
        foreach ($this->getNotes() as $note) {
            if ($note->isRest()) {
                continue;
            }
            $pitches[$note->getPitch() . $note->getOctave()] = true;
        }
        return array_keys($pitches);
    }

    /**
     * The bar as it is written in the ABC file.
     */
    public function render(): string
    {
        $out = '';
        foreach ($this->items as $item) {
            $out .= $item[0] === 'note' ? $item[1]->render() : $item[1];
        }
        return $out . $this->barLine;
    }
}
