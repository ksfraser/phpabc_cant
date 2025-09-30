<?php
namespace Ksfraser\PhpabcCanntaireachd;
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

    public function __construct(?TokenDictionary $dict = null) {
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
        $logFile = __DIR__ . '/cannt_generator_debug.log'; // Define log file path
        $noteBody = trim($noteBody);
        error_log("generateForNotes input: $noteBody"); // Log input
        file_put_contents($logFile, "generateForNotes input: $noteBody\n", FILE_APPEND); // Log input to file
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
            error_log("Processing part: $part");
            file_put_contents($logFile, "Processing part: $part\n", FILE_APPEND);
            $norm = preg_replace('/\d+/', '', $part);
            $cannt = $this->dict->convertAbcToCannt($part);
            if ($cannt === null) $cannt = $this->dict->convertAbcToCannt($norm);
            if ($cannt !== null) {
                error_log("Matched token: $part -> Cannt: $cannt");
                file_put_contents($logFile, "Matched token: $part -> Cannt: $cannt\n", FILE_APPEND);
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
        error_log("generateForNotes output: $result"); // Log output
        file_put_contents($logFile, "generateForNotes output: $result\n", FILE_APPEND); // Log output to file
        return $result;
    }
}
