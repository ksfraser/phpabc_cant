<?php

declare(strict_types=1);

namespace ksfraser\PhpabcCanntaireachd\Tune;

use ksfraser\PhpabcCanntaireachd\AbcEmbellishment as BaseEmbellishment;

/**
 * Tune-scoped embellishment wrapper.
 *
 * @package ksfraser\PhpabcCanntaireachd\Tune
 */
class AbcEmbellishment extends BaseEmbellishment
{
    private string $note;

    public function __construct(string $note)
    {
        $this->note = $note;
    }

    public function getNote(): string
    {
        return $this->note;
    }
}
