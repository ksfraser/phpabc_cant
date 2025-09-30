<?php
namespace Ksfraser\PhpabcCanntaireachd;

use Ksfraser\PhpabcCanntaireachd\Exceptions\TokenMappingException;
require_once __DIR__ . '/TokenMappingHelpers.php';

class TokenNormalizerForBar {
    /**
     * Normalize all tokens in a bar (array), stripping durations and symbols.
     * Returns array of normalized tokens.
     */
    public static function normalizeTokens(array $tokens): array {
        $norms = [];
        foreach ($tokens as $token) {
            try {
                $norms[] = TokenNormalizer::normalize($token);
            } catch (TokenMappingException $e) {
                // Log the exception and fallback
                $msg = sprintf("TokenNormalizerForBar: Exception for token='%s': %s\n", $token, $e->getMessage());
                file_put_contents(__DIR__ . '/../../cannt_debug.log', $msg, FILE_APPEND);
                $norms[] = $token; // fallback to raw token
            }
        }
        return $norms;
    }
}
