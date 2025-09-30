<?php
namespace Ksfraser\PhpabcCanntaireachd;

class Tokenizer
{
    /**
     * Tokenize an ABC bar string into tokens suitable for dictionary lookup.
     * This should handle gracenotes, embellishments, and note+duration.
     * @param string $bar
     * @return array
     */
    public function tokenize(string $bar): array
    {
        // Simple regex: match {grace}Note, Note, or {grace}Note+duration
        // e.g. {g}A, A, {g}A3, A3, etc.
    // Improved regex: match {grace}Note[digits], Note[digits], {grace}Note, Note
    preg_match_all('/\{[^}]+\}[A-Ga-g][0-9]*|[A-Ga-g][0-9]*/', $bar, $matches);
    return array_filter($matches[0], function($t) { return trim($t) !== ''; });
    }
}
