<?php

declare(strict_types=1);

namespace ksfraser\PhpabcCanntaireachd\Cleanup;

/**
 * The %%abc-include block that a tune starts with.
 *
 * The pipe band scores share one set of definitions — the MIDI settings, the
 * fonts, the percussion maps, the layout — through include files, so that
 * every tune in the book is formatted the same way:
 *
 *     %%abc-include "common.abh"
 *
 *     % %abc-include "../../../format_layout.abh"
 *     % %abc-include "../../../format_fonts.abh"
 *     ...
 *
 * Every one of them is written out. The scores used to include a single
 * "common.abh", a file that included all the others, but neither abc2midi nor
 * abc2ps follows an include into another include, so a meta-include would
 * leave the tunes without their MIDI settings, their fonts and their
 * percussion maps. The includes are therefore written out in full.
 *
 * @package ksfraser\PhpabcCanntaireachd\Cleanup
 *
 * @since 2.2.0
 */
class IncludeBlockWriter
{
    /**
     * The includes, in the order they are written.
     *
     * @var string[]
     */
    public const INCLUDES = [
        'format_layout.abh',
        'format_fonts.abh',
        'voices.abh',
        'bagpipe_chords.abh',
        'midi.abh',
        'guitar_chords.fmt',
        'gchords.fmt',
        'percussion.abh',
        'jazz.fmt',
    ];

    /** @var string The directory the include files live in. */
    private string $includeRoot;

    /** @var string[] */
    private array $includes;

    /**
     * @param string $includeRoot The directory the include files live in.
     * @param string[]|null $includes The includes to write.
     */
    public function __construct(string $includeRoot = '.', ?array $includes = null)
    {
        $this->includeRoot = rtrim($includeRoot, '/');
        $this->includes = $includes ?? self::INCLUDES;
    }

    /**
     * The include block of a tune.
     *
     * @param string $tuneDirectory The directory the tune is written to; the
     *                               include paths are relative to it.
     * @return string[]
     */
    public function build(string $tuneDirectory): array
    {
        $lines = [];
        foreach ($this->includes as $include) {
            $lines[] = sprintf('%%%%abc-include "%s"', $this->relativePath($tuneDirectory, $include));
        }
        $lines[] = '';
        return $lines;
    }

    /**
     * The include files a tune refers to, whether they are active or not.
     *
     * @param string[] $includeLines The include lines of the tune.
     * @return string[]
     */
    public function extractTargets(array $includeLines): array
    {
        $targets = [];
        foreach ($includeLines as $line) {
            if (preg_match('/abc-include\s+"([^"]*)"/i', $line, $matches) === 1) {
                $targets[] = $matches[1];
            }
        }
        return $targets;
    }

    /**
     * The include targets that do not exist on disk.
     *
     * @param string[] $includeLines The include lines of the tune.
     * @param string $tuneDirectory The directory the tune is written to.
     * @return string[]
     */
    public function findMissing(array $includeLines, string $tuneDirectory): array
    {
        $missing = [];
        foreach ($this->extractTargets($includeLines) as $target) {
            $path = $this->resolve($tuneDirectory, $target);
            if ($path !== null && !file_exists($path)) {
                $missing[] = $target;
            }
        }
        return $missing;
    }

    /**
     * The path of an include file, relative to the tune.
     *
     * @param string $tuneDirectory
     * @param string $include
     * @return string
     */
    public function relativePath(string $tuneDirectory, string $include): string
    {
        $from = realpath($tuneDirectory);
        $to = realpath($this->includeRoot);
        if ($from === false || $to === false) {
            return $include;
        }
        $relative = $this->relativePathBetween($from, $to);
        return $relative . $include;
    }

    /**
     * The path of an include file on disk.
     *
     * @param string $tuneDirectory
     * @param string $include
     * @return string|null
     */
    public function resolve(string $tuneDirectory, string $include): ?string
    {
        if ($include === '') {
            return null;
        }
        if ($include[0] === '/') {
            return $include;
        }
        return $tuneDirectory . '/' . $include;
    }

    /**
     * The relative path from one directory to another, e.g. "../../..".
     *
     * @param string $from
     * @param string $to
     * @return string
     */
    private function relativePathBetween(string $from, string $to): string
    {
        $fromParts = explode('/', trim($from, '/'));
        $toParts = explode('/', trim($to, '/'));

        while ($fromParts !== [] && $toParts !== [] && $fromParts[0] === $toParts[0]) {
            array_shift($fromParts);
            array_shift($toParts);
        }

        $target = implode('/', $toParts);
        if ($target === '') {
            return str_repeat('../', count($fromParts));
        }
        return str_repeat('../', count($fromParts)) . $target . '/';
    }
}
