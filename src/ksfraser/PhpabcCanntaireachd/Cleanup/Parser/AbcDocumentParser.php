<?php

declare(strict_types=1);

namespace ksfraser\PhpabcCanntaireachd\Cleanup\Parser;

use ksfraser\PhpabcCanntaireachd\Cleanup\Model\AbcDocument;
use ksfraser\PhpabcCanntaireachd\Cleanup\Model\AbcTuneModel;

/**
 * Reads an ABC file into a document of tunes.
 *
 * A tune starts at its X: field. Everything up to the first X: field is the
 * preamble of the file, and the blank lines between the tunes are kept so the
 * file can be written back out unchanged apart from the clean up.
 *
 * @package ksfraser\PhpabcCanntaireachd\Cleanup\Parser
 *
 * @since 2.2.0
 */
class AbcDocumentParser
{
    /** @var AbcTokenizer */
    private AbcTokenizer $tokenizer;

    public function __construct(?AbcTokenizer $tokenizer = null)
    {
        $this->tokenizer = $tokenizer ?? new AbcTokenizer();
    }

    /**
     * Read a file.
     *
     * @param string $path
     * @return AbcDocument
     */
    public function parseFile(string $path): AbcDocument
    {
        $content = file_get_contents($path);
        if ($content === false) {
            throw new \RuntimeException("Could not read $path");
        }
        return $this->parse($content);
    }

    /**
     * Read ABC text.
     *
     * @param string $content
     * @return AbcDocument
     */
    public function parse(string $content): AbcDocument
    {
        $document = new AbcDocument();
        $lines = preg_split('/\r?\n/', $content);
        if ($lines === false) {
            return $document;
        }

        $tune = null;
        $preamble = [];
        $separators = [];
        $pendingSeparator = [];

        foreach ($lines as $line) {
            if (preg_match('/^X:/', $line) === 1) {
                if ($tune !== null) {
                    $document->addTune($tune);
                    $separators[] = $pendingSeparator;
                    $pendingSeparator = [];
                } else {
                    $document->setPreamble($preamble);
                    $preamble = [];
                }
                $tune = new AbcTuneModel();
                $tune->setTuneNumber((string)(strpos($line, ':') === false ? '' : trim(substr($line, 2))));
                $this->addLine($document, $tune, $line);
                continue;
            }

            if ($tune === null) {
                // The include block may come before the first tune.
                if (preg_match('/abc-include\s+"([^"]*)"/i', trim($line)) === 1) {
                    $includes = $document->getIncludes();
                    $includes[] = $line;
                    $document->setIncludes($includes);
                    continue;
                }
                $preamble[] = $line;
                continue;
            }

            // A blank line ends the body of the tune; anything after it
            // belongs to the separator between the tunes.
            if (trim($line) === '') {
                if ($this->isInBody($tune)) {
                    $pendingSeparator[] = $line;
                    continue;
                }
            }

            $this->addLine($document, $tune, $line);
        }

        if ($tune !== null) {
            $document->addTune($tune);
            $separators[] = $pendingSeparator;
        } else {
            $document->setPreamble($preamble);
        }

        $document->setSeparators($separators);

        return $document;
    }

    /**
     * Has this voice already been defined in the header block? A "V:" line in
     * the body of a voice that is already defined is a voice marker, not a
     * second definition.
     */
    private function isVoiceDefined(AbcTuneModel $tune, string $voiceId): bool
    {
        foreach ($tune->getHeader('V') as $line) {
            $trimmed = trim($line);
            if (preg_match('/^V:\s*(\S+)(.*)$/', $trimmed, $matches) !== 1) {
                continue;
            }
            if ($matches[1] === $voiceId) {
                return true;
            }
            // The marker may name the voice instead of giving its identifier.
            if (preg_match('/\bname="([^"]*)"/i', $matches[2], $nameMatch) === 1
                && strcasecmp($nameMatch[1], $voiceId) === 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * Has the body of this tune started?
     */
    private function isInBody(AbcTuneModel $tune): bool
    {
        return $tune->getBodyLines() !== [];
    }

    /**
     * Add one line to a tune, in the place it belongs.
     */
    private function addLine(AbcDocument $document, AbcTuneModel $tune, string $line): void
    {
        $trimmed = trim($line);

        // The include block. It belongs to the file, so that every tune in
        // the file shares it.
        if (preg_match('/abc-include\s+"([^"]*)"/i', $trimmed, $matches) === 1) {
            $includes = $document->getIncludes();
            $includes[] = $line;
            $document->setIncludes($includes);
            return;
        }

        // A %% directive.
        if (str_starts_with($trimmed, '%%')) {
            $directives = $tune->getDirectives();
            $directives[] = $line;
            $tune->setDirectives($directives);
            return;
        }

        // A comment.
        if (str_starts_with($trimmed, '%')) {
            if ($tune->getBodyLines() === []) {
                // A comment in the header block stays with the headers.
                $tune->addHeaderComment($line);
                return;
            }
            $trailing = $tune->getTrailing();
            $trailing[] = $line;
            $tune->setTrailing($trailing);
            return;
        }

        // A header field. w: and W: are lyrics, not headers. A bare "V:"
        // line in the body of the tune is a voice marker, not a definition.
        if (preg_match('/^([A-Za-z]):(.*)$/', $trimmed, $matches) === 1) {
            $field = strtoupper($matches[1]);
            $voiceId = trim((string)(preg_match('/^(\S+)/', $matches[2], $voiceMatch) === 1 ? $voiceMatch[1] : ''));
            if ($field === 'V' && $tune->getBodyLines() !== [] && $this->isVoiceDefined($tune, $voiceId)) {
                // A voice marker in the body: it belongs to the music.
                $bodyLines = $tune->getBodyLines();
                $bodyLines[] = AbcTuneModel::makeBodyLine($line, $this->tokenizer);
                $tune->setBodyLines($bodyLines);
                return;
            }
            if ($field !== 'W') {
                $tune->addHeaderLine($field, $line);
                return;
            }
            // A lyrics or canntaireachd line belongs to the music above it.
            $bodyLines = $tune->getBodyLines();
            if ($bodyLines !== []) {
                $last = count($bodyLines) - 1;
                $bodyLines[$last]->addLyrics($line);
                $tune->setBodyLines($bodyLines);
            }
            return;
        }

        // A body line.
        $bodyLines = $tune->getBodyLines();
        $bodyLines[] = AbcTuneModel::makeBodyLine($line, $this->tokenizer);
        $tune->setBodyLines($bodyLines);
    }
}
