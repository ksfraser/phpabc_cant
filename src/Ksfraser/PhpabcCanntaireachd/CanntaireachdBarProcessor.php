<?php
namespace Ksfraser\PhpabcCanntaireachd;


require_once __DIR__ . '/TokenMappingHelpers.php';
use Ksfraser\PhpabcCanntaireachd\Exceptions\TokenMappingException;

class CanntaireachdBarProcessor
{
    /**
     * Convert an array of ABC tokens to a canntaireachd string using static mapping.
     * @param array $tokens
     * @param array $dictionary
     * @return string
     */
    public static function tokensToCanntaireachd(array $tokens, array $dictionary): string
    {
        $canntArr = [];
        $normalizedTokens = \Ksfraser\PhpabcCanntaireachd\TokenNormalizerForBar::normalizeTokens($tokens);
        // Flatten normalized tokens (TokenNormalizer::normalize can return array)
        $flatTokens = [];
        foreach ($normalizedTokens as $token) {
            if (is_array($token)) {
                foreach ($token as $t) {
                    $flatTokens[] = $t;
                }
            } else {
                $flatTokens[] = $token;
            }
        }
        foreach ($flatTokens as $token) {
            if (trim($token) === '' || $token === '|' || $token === '||' || $token === '|:' || $token === ':') {
                continue;
            }
            try {
                $canntArr[] = CanntaireachdSyllableMapper::mapToken($token, $dictionary);
            } catch (TokenMappingException $e) {
                // Output raw token if not mapped
                $canntArr[] = $token;
            }
        }
        return implode(' ', $canntArr);
    }
}
