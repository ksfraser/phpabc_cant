<?php

declare(strict_types=1);

namespace ksfraser\PhpabcCanntaireachd\Cleanup\Model;

/**
 * An ABC file: the tunes it holds, and the lines that sit between them.
 *
 * @package ksfraser\PhpabcCanntaireachd\Cleanup\Model
 *
 * @since 2.2.0
 */
class AbcDocument
{
    /** @var AbcTuneModel[] */
    private array $tunes = [];

    /** @var string[] The lines before the first tune. */
    private array $preamble = [];

    /** @var string[] The lines between the tunes. */
    private array $separators = [];

    /** @var string[] The include block of the file. */
    private array $includes = [];

    /**
     * @return AbcTuneModel[]
     */
    public function getTunes(): array
    {
        return $this->tunes;
    }

    /**
     * @param AbcTuneModel[] $tunes
     */
    public function setTunes(array $tunes): void
    {
        $this->tunes = $tunes;
    }

    public function addTune(AbcTuneModel $tune): void
    {
        $this->tunes[] = $tune;
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
    public function getSeparators(): array
    {
        return $this->separators;
    }

    /**
     * @param string[] $separators
     */
    public function setSeparators(array $separators): void
    {
        $this->separators = $separators;
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
}
