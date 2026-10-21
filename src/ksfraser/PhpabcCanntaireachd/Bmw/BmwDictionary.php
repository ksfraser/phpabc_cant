<?php

declare(strict_types=1);

namespace ksfraser\PhpabcCanntaireachd\Bmw;

/**
 * The Bagpipe Music Writer to ABC token dictionary.
 *
 * The dictionary itself is a data file, bmw_dict.php, next to this class. The
 * tokens are matched longest first, so that "Fr_8" is found before "F", and a
 * token that is not in the dictionary is left as it was.
 *
 * @package ksfraser\PhpabcCanntaireachd\Bmw
 *
 * @since 2.2.0
 */
class BmwDictionary
{
    /** @var array<string, string> BMW token => ABC. */
    private array $tokens = [];

    /** @var array<string, string> BMW header text => ABC. */
    private array $headers = [];

    /** @var string[] The token keys, longest first. */
    private array $tokenKeys = [];

    /** @var string[] The header keys, longest first. */
    private array $headerKeys = [];

    /**
     * @param array<string, string>|null $tokens
     * @param array<string, string>|null $headers
     */
    public function __construct(?array $tokens = null, ?array $headers = null)
    {
        if ($tokens === null || $headers === null) {
            $this->loadDefaultDictionary();
            if ($tokens !== null) {
                $this->tokens = $tokens;
            }
            if ($headers !== null) {
                $this->headers = $headers;
            }
        } else {
            $this->tokens = $tokens;
            $this->headers = $headers;
        }
        $this->sortKeys();
    }

    /**
     * Read the dictionary file.
     *
     * @return void
     */
    private function loadDefaultDictionary(): void
    {
        $path = dirname(__DIR__) . '/bmw_dict.php';
        if (!is_file($path)) {
            return;
        }
        $bmw = [];
        $bmw_header = [];
        include $path;
        if (is_array($bmw)) {
            foreach ($bmw as $key => $row) {
                if (!is_array($row) || !isset($row['abc'])) {
                    continue;
                }
                $this->tokens[(string)$key] = (string)$row['abc'];
                // Some of the keys are written with spaces around them, which
                // mattered when the conversion was a search and replace over
                // the whole line. The tokens are matched word by word now, so
                // the key without the spaces is the one that is used.
                $trimmed = trim((string)$key);
                if ($trimmed !== '' && !isset($this->tokens[$trimmed])) {
                    $this->tokens[$trimmed] = (string)$row['abc'];
                }
            }
        }
        if (is_array($bmw_header)) {
            foreach ($bmw_header as $key => $value) {
                if ((string)$key !== '') {
                    $this->headers[(string)$key] = (string)$value;
                }
            }
        }
    }

    /**
     * Sort the keys, longest first, so that the longest match wins.
     *
     * @return void
     */
    private function sortKeys(): void
    {
        $this->tokenKeys = array_keys($this->tokens);
        usort($this->tokenKeys, static fn ($a, $b) => strlen($b) <=> strlen($a));

        $this->headerKeys = array_keys($this->headers);
        usort($this->headerKeys, static fn ($a, $b) => strlen($b) <=> strlen($a));
    }

    /**
     * The ABC for a BMW token, or null when the token is not in the
     * dictionary.
     *
     * @param string $token
     * @return string|null
     */
    public function getToken(string $token): ?string
    {
        return $this->tokens[$token] ?? null;
    }

    /**
     * The ABC for a header of the file, or null when it is not in the
     * dictionary.
     *
     * @param string $header
     * @return string|null
     */
    public function getHeader(string $header): ?string
    {
        return $this->headers[$header] ?? null;
    }

    /**
     * The token keys, longest first.
     *
     * @return string[]
     */
    public function getTokenKeys(): array
    {
        return $this->tokenKeys;
    }

    /**
     * The header keys, longest first.
     *
     * @return string[]
     */
    public function getHeaderKeys(): array
    {
        return $this->headerKeys;
    }

    /**
     * @return array<string, string>
     */
    public function getTokens(): array
    {
        return $this->tokens;
    }

    /**
     * @return array<string, string>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }
}
