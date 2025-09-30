<?php

namespace Ksfraser\PhpabcCanntaireachd;
use Ksfraser\PhpabcCanntaireachd\Exceptions\TokenMappingException;

class TokenNormalizer {
    public static function normalize($token) {
        // Remove trailing digits after pitch, keep gracenote+pitch
        $norm = preg_replace('/([A-Ga-g])\d+$/', '$1', $token);
        error_log("TokenNormalizer: token='$token' normalized='$norm'");
        if ($norm === null || $norm === '') {
            throw new TokenMappingException("Normalization failed for token: $token");
        }
        return $norm;
    }
}

class TokenToCanntMapper {
    protected $dictionary;
    public function __construct($dictionary) {
        $this->dictionary = $dictionary;
    }
    public function map($token) {
        $norm = TokenNormalizer::normalize($token);
        error_log("TokenToCanntMapper: token='$token' normalized='$norm'");
        if (isset($this->dictionary[$norm])) {
            error_log("TokenToCanntMapper: MAPPED '$norm' => '{$this->dictionary[$norm]}'");
            return $this->dictionary[$norm];
        }
        error_log("TokenToCanntMapper: NO MAPPING for '$norm'");
        throw new TokenMappingException("No canntaireachd mapping for token: $norm");
    }
}
