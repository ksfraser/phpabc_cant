<?php

namespace Ksfraser\PhpabcCanntaireachd;
use Ksfraser\PhpabcCanntaireachd\Exceptions\TokenMappingException;

class TokenNormalizer {
    public static function normalize($token) {
        // Split multi-note tokens (e.g., 'A2B' => ['A','B'])
        // Match gracenote+pitch+duration, e.g. '{g}A3', '{d}B2', 'A2', 'B', etc.
        $pattern = '/(\{[a-z]+\})?[A-Ga-g](?:[0-9.\/\-]*)?/';
        preg_match_all($pattern, $token, $matches);
        $norms = [];
        foreach ($matches[0] as $match) {
            // Strip duration/symbols after pitch
            $norm = preg_replace('/(\{[a-z]+\})?([A-Ga-g]).*/', '$1$2', $match);
            $norm = trim($norm);
            file_put_contents(__DIR__ . '/../../cannt_debug.log', "TokenNormalizer: token='$token' part='$match' normalized='$norm'\n", FILE_APPEND);
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
        // Debug output to file only
        file_put_contents(__DIR__ . '/../../cannt_debug.log', "TokenToCanntMapper: token='$token' normalized='$norm'\n", FILE_APPEND);
        if (isset($this->dictionary[$norm])) {
            file_put_contents(__DIR__ . '/../../cannt_debug.log', "TokenToCanntMapper: MAPPED '$norm' => '{$this->dictionary[$norm]}'\n", FILE_APPEND);
            return $this->dictionary[$norm];
        }
        file_put_contents(__DIR__ . '/../../cannt_debug.log', "TokenToCanntMapper: NO MAPPING for '$norm'\n", FILE_APPEND);
        throw new TokenMappingException("No canntaireachd mapping for token: $norm");
    }
}
