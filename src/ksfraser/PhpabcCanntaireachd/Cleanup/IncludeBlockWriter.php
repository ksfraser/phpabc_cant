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
 * The first include is active; the rest are kept as comments so that they can
 * be switched on again without hunting for the file.
 *
 * @package ksfraser\PhpabcCanntaireachd\Cleanup
 *
 * @since 2.2.0
 */
class IncludeBlockWriter
{
    /** @var string The include that is always active. */
    public const ACTIVE_INCLUDE = 'common.abh';

    /**
     * The includes that are kept as comments, in the order they are written.
     *
     * @var string[]
     */
    public const COMMENTED_INCLUDES = [
        'format_layout.abh',
        'format_fonts.abh',
        'voices.abh',
        'bagpipe_chords.abh',
        'midi.abh',
        'guitar_chords.abh',
        'gchords.abh',
        'percussion.abh',
        'jazz.fmt',
    ];

    /** @var string The directory the include files live in. */
    private string $includeRoot;

    /** @var string[] */
    private array $commentedIncludes;

    /**
     * @param string $includeRoot The directory the include files live in.
     * @param string[]|null $commentedIncludes The includes kept as comments.
     */
    public function __construct(string $includeRoot = '.', ?array $commentedIncludes = null)
    {
        $this->includeRoot = rtrim($includeRoot, '/');
        $this->commentedIncludes = $commentedIncludes ?? self::COMMENTED_INCLUDES;
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
        $lines[] = sprintf('%%%%abc-include "%s"', self::ACTIVE_INCLUDE);
        $lines[] = '';
        foreach ($this->commentedIncludes as $include) {
            $lines[] = sprintf('%% %%abc-include "%s"', $this->relativePath($tuneDirectory, $include));
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
