<?php

namespace Ksfraser\PhpabcCanntaireachd;
use Ksfraser\PhpabcCanntaireachd\Exceptions\TokenMappingException;


use Ksfraser\PhpabcCanntaireachd\Log\CanntLog;

class TokenNormalizer {
    public static function normalize($token) {
        if ($token === null || $token === '') {
            throw new TokenMappingException("Normalization failed for empty token");
        }
        // Split multi-note tokens (e.g., 'A2B' => ['A','B'])
        // Match gracenote+pitch+duration, e.g. '{g}A3', '{d}B2', 'A2', 'B', etc.
        $pattern = '/(\{[a-z]+\})?[A-Ga-g](?:[0-9.\/\-]*)?/';
        preg_match_all($pattern, $token, $matches);
        $norms = [];
        foreach ($matches[0] as $match) {
            // Strip duration/symbols after pitch
            $norm = preg_replace('/(\{[a-z]+\})?([A-Ga-g]).*/', '$1$2', $match);
            $norm = trim($norm);
            CanntLog::log("TokenNormalizer: token='$token' part='$match' normalized='$norm'", true);
            if ($norm === null || $norm === '') {
                throw new TokenMappingException("Normalization failed for token part: $match");
            }
            $norms[] = $norm;
        }
        if (count($norms) === 1) {
            return $norms[0];
        }
        return $norms;
    }
}

class TokenToCanntMapper {
    protected $dictionary;
    public function __construct($dictionary) {
        $this->dictionary = $dictionary;
    }
    public function map($token) {
        $norm = TokenNormalizer::normalize($token);
        CanntLog::log("TokenToCanntMapper: token='$token' normalized='$norm'", true);
        if (isset($this->dictionary[$norm])) {
            CanntLog::log("TokenToCanntMapper: MAPPED '$norm' => '{$this->dictionary[$norm]}'", true);
            return $this->dictionary[$norm];
        }
        CanntLog::log("TokenToCanntMapper: NO MAPPING for '$norm'", true);
        throw new TokenMappingException("No canntaireachd mapping for token: $norm");
    }
}
