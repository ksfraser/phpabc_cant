<?php

namespace ksfraser\PhpabcCanntaireachd;

use ksfraser\PhpabcCanntaireachd\Exceptions\TokenMappingException;
use ksfraser\PhpabcCanntaireachd\Log\CanntLog;

/**
 * Maps a normalised ABC token to its canntaireachd syllable.
 *
 * Split out of the former TokenMappingHelpers.php so that the class name
 * matches its file name and Composer can autoload it (PSR-4).
 */
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
