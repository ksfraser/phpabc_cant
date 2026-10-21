<?php

declare(strict_types=1);

namespace ksfraser\PhpabcCanntaireachd\Cleanup;

/**
 * The order the header fields of a tune are written in.
 *
 * This is the order the pipe band scores use:
 *
 *   X: the tune number
 *   T: the title, and a second T: for the alternative title
 *   C: the composer
 *   B: the book
 *   O: the origin
 *   Z: the transcriber
 *   H: the history
 *   Q: the tempo
 *   K: the key
 *   M: the meter
 *   L: the unit of length
 *   R: the rhythm
 *   U: the redefinable symbols
 *   V: the voices
 *   I: the MIDI settings
 *
 * @package ksfraser\PhpabcCanntaireachd\Cleanup
 *
 * @since 2.2.0
 */
class HeaderOrderNormaliser
{
    /** @var string[] */
    public const DEFAULT_ORDER = [
        'X', 'T', 'C', 'B', 'O', 'Z', 'H', 'Q', 'K', 'M', 'L', 'R', 'U', 'V', 'I',
    ];

    /** @var string[] */
    private array $order;

    /**
     * @param string[]|null $order The header fields, in the order to write them.
     */
    public function __construct(?array $order = null)
    {
        $this->order = $order ?? self::DEFAULT_ORDER;
    }

    /**
     * @return string[]
     */
    public function getOrder(): array
    {
        return $this->order;
    }

    /**
     * The position of a header field in the order. A field that is not in the
     * order is written after the fields that are.
     *
     * @param string $field
     * @return int
     */
    public function getPosition(string $field): int
    {
        $position = array_search(strtoupper($field), $this->order, true);
        return $position === false ? count($this->order) : $position;
    }
}
