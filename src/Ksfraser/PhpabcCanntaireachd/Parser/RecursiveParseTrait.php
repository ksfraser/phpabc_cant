<?php
namespace Ksfraser\PhpabcCanntaireachd\Parser;
/**
 * Trait RecursiveParseTrait
 * Provides shared recursive parsing logic for ABC file, tune, bar, etc.
 */
trait RecursiveParseTrait {
    /**
     * Split lines and trim empty ones.
     * @param string $content
     * @return array
     */
    protected function splitLines($content) {
        $lines = preg_split('/\r?\n/', $content);
        return array_filter(array_map('trim', $lines), function($l) { return $l !== ''; });
    }
    /**
     * Strip barline chars using preg_replace (pattern can be injected).
     * @param string $text
     * @param string $pattern
     * @return string
     */
    protected function stripBarlineChars($text, $pattern = '/^[|:\\s]+|[|:\\s]+$/') {
        return preg_replace($pattern, '', trim($text));
    }
    /**
     * Split tokens on whitespace and trim.
     * @param string $text
     * @return array
     */
    protected function splitTokens($text) {
        $tokens = preg_split('/\s+/', $text);
        return array_filter(array_map('trim', $tokens), function($t) { return $t !== ''; });
    }
}
