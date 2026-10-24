<?php

declare(strict_types=1);

namespace ksfraser\PhpabcCanntaireachd;

use ksfraser\PhpabcCanntaireachd\Exceptions\TokenMappingException;
use ksfraser\PhpabcCanntaireachd\Log\CanntLog;

/**
 * Normalises an ABC token to the form used as a dictionary key.
 *
 * Split out of the former TokenMappingHelpers.php so that the class name
 * matches its file name and Composer can autoload it (PSR-4).
 */
class TokenNormalizer {
    public static function normalize($token) {
        if ($token === null || $token === '') {
            throw new TokenMappingException("Normalization failed for empty token");
        }
        // Preserve bar lines unchanged
        if ($token === '|' || $token === '||') {
            return $token;
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
        if (empty($norms)) {
            throw new TokenMappingException("Normalization failed for token: $token");
        }
        if (count($norms) === 1) {
            return $norms[0];
        }
        return $norms;
    }
}
