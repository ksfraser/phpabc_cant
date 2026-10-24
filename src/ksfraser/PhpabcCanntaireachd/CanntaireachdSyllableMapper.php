<?php

declare(strict_types=1);

namespace ksfraser\PhpabcCanntaireachd;


use ksfraser\PhpabcCanntaireachd\Exceptions\TokenMappingException;
use ksfraser\PhpabcCanntaireachd\TokenNormalizer;

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
