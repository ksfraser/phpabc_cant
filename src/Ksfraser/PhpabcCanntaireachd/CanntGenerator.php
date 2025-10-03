<?php
namespace Ksfraser\PhpabcCanntaireachd;

use Ksfraser\PhpabcCanntaireachd\Log\CanntLog;
/**
 * Class CanntGenerator
 *
 * Generates canntaireachd lyrics for ABC note bodies using a token dictionary.
 * Supports legacy dictionary loading and robust token matching for bagpipe voice conversion.
 *
 * SOLID: Single Responsibility (canntaireachd generation), DI (dictionary injection), DRY (token matching logic).
 *
 * @package Ksfraser\PhpabcCanntaireachd
 *
 * @property TokenDictionary $dict Token dictionary for ABC-to-canntaireachd conversion
 *
 * @method __construct(TokenDictionary|null $dict) Inject or load token dictionary
 * @method generateForNotes(string $noteBody): string Generate canntaireachd lyrics for ABC note body
 *
 * @uml
 * @startuml
 * class CanntGenerator {
 *   - dict: TokenDictionary
 *   + __construct(dict: TokenDictionary)
 *   + generateForNotes(noteBody: string): string
 * }
 * CanntGenerator --> TokenDictionary
 * @enduml
 */

class CanntGenerator {
    protected $dict;

    /**
     * Summary of __construct
     * @param TokenDictionary $dict
     * @return void
     */
    public function __construct($dict = null) {
        if ($dict !== null) {
            $this->dict = $dict;
            return;
        }
        $td = new TokenDictionary();
        // Try to load legacy abc_dict.php if available
        $legacyPath = __DIR__ . '/../phpabc_canntaireachd/abc_dict.php';
        if (file_exists($legacyPath)) {
            // include inside isolated scope to avoid leaking symbols
            $abc = [];
            try {
                include $legacyPath; // populates $abc in many legacy files
            } catch (\Throwable $e) {
                // ignore
            }
            if (!empty($abc) && is_array($abc)) {
                $pre = [];
                foreach ($abc as $k => $v) {
                    $pre[$k] = [
                        'cannt_token' => $v['cannt'] ?? ($v['cannt_token'] ?? null),
                        'bmw_token' => $v['bmw'] ?? null,
                        'description' => $v['desc'] ?? null,
                    ];
                }
                $td->prepopulate($pre);
            }
        }
        $this->dict = $td;
    }

    public function generateForNotes(string $noteBody): string {
        $noteBody = trim($noteBody);
    CanntLog::log("generateForNotes input: $noteBody", true);
        if ($noteBody === '') return '[?]';
        
        // Strip voice prefixes like [V:Bagpipes] from the beginning of the line
        $noteBody = preg_replace('/^\[V:[^\]]*\]/', '', $noteBody);
        $noteBody = trim($noteBody);
        
        // Attempt to split on whitespace first
        $parts = preg_split('/\s+/', $noteBody);
        $out = [];
        foreach ($parts as $part) {
            $part = trim($part);
            if ($part === '' || $part === '|') continue;
            CanntLog::log("Processing part: $part", true);
            $norm = preg_replace('/\d+/', '', $part);
            $cannt = $this->dict->convertAbcToCannt($part);
            if ($cannt === null) $cannt = $this->dict->convertAbcToCannt($norm);
            if ($cannt !== null) {
                CanntLog::log("Matched token: $part -> Cannt: $cannt", true);
                $out[] = $cannt;
            } else {
                $out[] = '[' . ($norm === '' ? $part : $norm) . ']';
            }
        }
        // Ensure we always return a non-empty string
        if (empty($out)) {
            $safe = trim(preg_replace('/\s+/', ' ', $noteBody));
            if ($safe === '') return '[?]';
            return '[' . $safe . ']';
        }
        $result = implode(' ', $out);
    CanntLog::log("generateForNotes output: $result", true);
        return $result;
    }
}
