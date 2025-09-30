<?php
namespace Ksfraser\PhpabcCanntaireachd;

class CanntaireachdBarProcessor
{
    protected $dictionary;

    public function __construct($dictionary)
    {
        $this->dictionary = $dictionary;
    }

    /**
     * Convert an array of ABC tokens to a canntaireachd string.
     * @param array $tokens
     * @return string
     */
    public function tokensToCanntaireachd(array $tokens): string
    {
        $canntArr = [];
        foreach ($tokens as $token) {
            if (trim($token) === '' || $token === '|' || $token === '||' || $token === '|:' || $token === ':') {
                continue;
            }
            $cannt = $this->getCannt($token);
            if ($cannt === null || $cannt === $token) {
                $canntArr[] = $token;
            } else {
                $canntArr[] = $cannt;
            }
        }
        return implode(' ', $canntArr);
    }

    /**
     * Lookup canntaireachd syllable for a token.
     * @param string $token
     * @return string|null
     */
    protected function getCannt(string $token): ?string
    {
        // Normalize token: strip duration, keep gracenotes and pitch
        $normalized = $token;
        // Remove trailing digits, slashes, dots (duration)
        $normalized = preg_replace('/[0-9.\/]+$/', '', $normalized);
        // Remove spaces
        $normalized = trim($normalized);
        return $this->dictionary[$normalized] ?? $token;
    }
}
