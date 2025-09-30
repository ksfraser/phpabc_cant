<?php
namespace Ksfraser\PhpabcCanntaireachd;

require_once __DIR__ . '/TokenMappingHelpers.php';

use Ksfraser\PhpabcCanntaireachd\Exceptions\TokenMappingException;
use Ksfraser\PhpabcCanntaireachd\TokenNormalizer;

class CanntaireachdSyllableMapper {
    /**
     * Map a single ABC token to a canntaireachd syllable using the dictionary.
     * Returns the syllable or throws TokenMappingException if not found.
     */
    public static function mapToken($token, $dictionary) {
        $norm = TokenNormalizer::normalize($token);
        if (isset($dictionary[$norm])) {
            return $dictionary[$norm];
        }
        throw new TokenMappingException("No canntaireachd mapping for token: $token (normalized: $norm)");
    }
}
