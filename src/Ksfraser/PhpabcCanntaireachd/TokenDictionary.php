<?php
namespace Ksfraser\PhpabcCanntaireachd;

require_once __DIR__ . '/Trie.php';

/**
 * TokenDictionary manages ABC/canntaireachd/BMW token mappings and CRUD operations.
 * Follows SRP by handling only token management.
 * Uses Trie for efficient lookups.
 *
 * @requirement FR7, FR8
 * @uml
 * @startuml
 * class TokenDictionary {
 *   - tokens: array
 *   - trie: Trie
 *   + __construct()
 *   + prepopulate(dict: array)
 *   + convertAbcToCannt(abc: string): string|null
 *   + searchCannt(input: string): string
 *   + getAllTokens(): array
 * }
 * TokenDictionary --> Trie : uses
 * @enduml
 */
class TokenDictionary
{
    /** @var array */
    protected $tokens = [];
    /** @var Trie */
    protected $trie;

    public function __construct() {
        $this->trie = new Trie();
    }

    /**
     * Prepopulate from abc_dict.php
     * @param array $dict
     */
    public function prepopulate(array $dict)
    {
        foreach ($dict as $abc => $row) {
            $this->tokens[$abc] = [
                'abc_token' => $abc,
                'cannt_token' => $row['cannt_token'] ?? null,
                'bmw_token' => $row['bmw_token'] ?? null,
                'description' => $row['description'] ?? null,
            ];
            if ($row['cannt_token']) {
                $this->trie->addToken($abc, $row['cannt_token']);
            }
        }
    }

    /**
     * Add or update a token row
     */
    public function addOrUpdateToken($abc, $cannt = null, $bmw = null, $desc = null)
    {
        if (isset($this->tokens[$abc])) {
            if ($bmw !== null) {
                $this->tokens[$abc]['bmw_token'] = $bmw;
            }
            if ($cannt !== null) {
                $this->tokens[$abc]['cannt_token'] = $cannt;
            }
            if ($desc !== null) {
                $this->tokens[$abc]['description'] = $desc;
            }
        } else {
            $this->tokens[$abc] = [
                'abc_token' => $abc,
                'cannt_token' => $cannt,
                'bmw_token' => $bmw,
                'description' => $desc,
            ];
        }
    }

    /**
     * Get a token row by ABC token
     */
    public function getToken($abc)
    {
        return $this->tokens[$abc] ?? null;
    }

    /**
     * Delete a token row by ABC token
     */
    public function deleteToken($abc)
    {
        unset($this->tokens[$abc]);
    }

    /**
     * Convert ABC to canntaireachd
     */
    public function convertAbcToCannt($abc)
    {
        return $this->tokens[$abc]['cannt_token'] ?? null;
    }

    /**
     * Convert BMW to ABC
     */
    public function convertBmwToAbc($bmw)
    {
        foreach ($this->tokens as $row) {
            if ($row['bmw_token'] === $bmw) {
                return $row['abc_token'];
            }
        }
        return null;
    }

    /**
     * Convert canntaireachd token to BMW token
     */
    public function convertCanntToBmw($cannt)
    {
        foreach ($this->tokens as $row) {
            if (($row['cannt_token'] ?? null) === $cannt) {
                return $row['bmw_token'] ?? null;
            }
        }
        return null;
    }

    /**
     * Convert multiple ABC tokens to canntaireachd
     */
    public function convertMultipleAbcToCannt(array $abcs)
    {
        $results = [];
        foreach ($abcs as $abc) {
            $results[$abc] = $this->convertAbcToCannt($abc);
        }
        return $results;
    }

    /**
     * Convert multiple BMW tokens to ABC
     */
    public function convertMultipleBmwToAbc(array $bmws)
    {
        $results = [];
        foreach ($bmws as $bmw) {
            $results[$bmw] = $this->convertBmwToAbc($bmw);
        }
        return $results;
    }

    /**
     * Convert multiple canntaireachd tokens to BMW tokens
     */
    public function convertMultipleCanntToBmw(array $cannts)
    {
        $results = [];
        foreach ($cannts as $cannt) {
            $results[$cannt] = $this->convertCanntToBmw($cannt);
        }
        return $results;
    }

    /**
     * Bulk import tokens
     */
    public function bulkImport(array $rows)
    {
        foreach ($rows as $row) {
            $this->addOrUpdateToken($row['abc'], $row['cannt'], $row['bmw'], $row['desc'] ?? null);
        }
    }

    /**
     * Get all tokens
     */
    public function getAllTokens()
    {
        return $this->tokens;
    }

    /**
     * Convert ABC to BMW
     */
    public function convertAbcToBmw($abc)
    {
        return $this->tokens[$abc]['bmw_token'] ?? null;
    }

    /**
     * Search for patterns in input using Trie
     */
    public function searchCannt($input) {
        return $this->trie->search($input);
    }
}
