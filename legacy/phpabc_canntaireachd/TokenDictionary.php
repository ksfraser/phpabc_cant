<?php
namespace Ksfraser\PhpabcCanntaireachd;

/**
 * TokenDictionary manages ABC/canntaireachd/BMW token mappings and CRUD operations.
 */
class TokenDictionary
{
    /** @var array */
    protected $tokens = [];

    /**
     * Prepopulate from abc_dict.php
     * @param array $dict
     */
    public function prepopulate(array $dict)
    {
        foreach ($dict as $abc => $row) {
            $this->tokens[$abc] = [
                'abc_token' => $abc,
                'cannt_token' => $row['cannt'] ?? ($row['cannt_token'] ?? null),
                'bmw_token' => $row['bmw'] ?? ($row['bmw_token'] ?? null),
                'description' => $row['desc'] ?? ($row['description'] ?? null),
            ];
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
}
