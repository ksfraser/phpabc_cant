<?php

declare(strict_types=1);

namespace ksfraser\PhpabcCanntaireachd\Bmw;

/**
 * Converts a Bagpipe Music Writer file into ABC.
 *
 * The BMW file has a header block of its own, then a record for the title,
 * the type, the composer and the foot, then the music. The music is written
 * in BMW's own notation, and each of its tokens is looked up in the
 * dictionary.
 *
 * The tokens are matched longest first, and a token that is not in the
 * dictionary is left as it was, so that nothing is lost.
 *
 * @package ksfraser\PhpabcCanntaireachd\Bmw
 *
 * @since 2.2.0
 */
class BmwToAbcConverter
{
    /**
     * The record types of a BMW file, and the ABC header each becomes.
     *
     * @var array<string, string>
     */
    private const RECORD_HEADERS = [
        'T' => 'T',
        'Y' => 'R',
        'M' => 'C',
        'F' => 'O',
    ];

    /** @var BmwDictionary */
    private BmwDictionary $dictionary;

    /**
     * @param BmwDictionary|null $dictionary
     */
    public function __construct(?BmwDictionary $dictionary = null)
    {
        $this->dictionary = $dictionary ?? new BmwDictionary();
    }

    /**
     * Convert a BMW file into ABC.
     *
     * @param string $content The contents of the BMW file.
     * @return string The ABC.
     */
    public function convert(string $content): string
    {
        $lines = preg_split('/\r?\n/', $content);
        if ($lines === false) {
            return '';
        }

        $out = [];
        foreach ($lines as $line) {
            foreach ($this->convertLine($line) as $converted) {
                $out[] = $converted;
            }
        }

        return implode("\n", $out);
    }

    /**
     * Convert one line of the BMW file.
     *
     * @param string $line
     * @return string[] The lines of ABC it becomes.
     */
    public function convertLine(string $line): array
    {
        $trimmed = trim($line);
        if ($trimmed === '') {
            return [''];
        }

        // A record of the title, the type, the composer or the foot.
        if (preg_match('/^"([^"]*)",\(([TYMF])[^)]*\)/', $trimmed, $matches) === 1) {
            $header = self::RECORD_HEADERS[$matches[2]] ?? null;
            if ($header === null) {
                return [$trimmed];
            }
            return [$header . ':' . $matches[1]];
        }

        // The header of the tune: the key, the meter, the bar lines.
        if (str_starts_with($trimmed, '&')) {
            $rest = trim(substr($trimmed, 1));
            // The key is written in words, e.g. "sharpf sharpc".
            foreach ($this->dictionary->getHeaderKeys() as $key) {
                if (str_starts_with($rest, $key)) {
                    $abc = $this->dictionary->getHeader($key) ?? '';
                    $rest = trim(substr($rest, strlen($key)));
                    return [trim($this->join($abc, $this->convertTokens($rest)))];
                }
            }
            return [$this->convertTokens($rest)];
        }

        // A header of the file.
        foreach ($this->dictionary->getHeaderKeys() as $key) {
            if (str_starts_with($trimmed, $key)) {
                $abc = $this->dictionary->getHeader($key) ?? '';
                $rest = trim(substr($trimmed, strlen($key)));
                $out = trim($this->join($abc, $this->convertTokens($rest)));
                return $out === '' ? [''] : [$out];
            }
        }

        // The music.
        return [$this->convertTokens($trimmed)];
    }

    /**
     * Join a header and the text that follows it. A header that ends in a
     * colon is followed straight away, as the ABC convention has it.
     *
     * @param string $header
     * @param string $rest
     * @return string
     */
    private function join(string $header, string $rest): string
    {
        if ($rest === '') {
            return $header;
        }
        if (str_ends_with($header, ':')) {
            return $header . $rest;
        }
        return $header . ' ' . $rest;
    }

    /**
     * Convert the tokens of a line of music.
     *
     * The line is split into words, and each word is looked up longest
     * first. This is what the original converter could not do: it replaced
     * over the whole line, so a doubling became "{gde}" and then the "gd" was
     * replaced again, leaving "{{d}e}".
     *
     * @param string $line
     * @return string
     */
    public function convertTokens(string $line): string
    {
        $words = preg_split('/(\s+)/', $line, -1, PREG_SPLIT_DELIM_CAPTURE);
        if ($words === false) {
            return $line;
        }

        foreach ($words as $index => $word) {
            if (trim($word) === '') {
                continue;
            }
            $words[$index] = $this->convertWord($word);
        }

        return implode('', $words);
    }

    /**
     * Convert one word of a line of music.
     *
     * @param string $word
     * @return string
     */
    private function convertWord(string $word): string
    {
        foreach ($this->dictionary->getTokenKeys() as $key) {
            if ($key !== '' && str_starts_with($word, $key)) {
                $abc = $this->dictionary->getToken($key) ?? '';
                return $abc . substr($word, strlen($key));
            }
        }
        // Not a token we know: leave it as it was.
        return $word;
    }
}
