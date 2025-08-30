<?php
namespace Ksfraser\PhpabcCanntaireachd;

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
        $noteBody = trim($noteBody);
        if ($noteBody === '') return '[?]';
        // Attempt to split on whitespace first
        $parts = preg_split('/\s+/', $noteBody);
        $out = [];
        foreach ($parts as $part) {
            $part = trim($part);
            if ($part === '' || $part === '|' ) continue;
            // If the token contains multiple notes concatenated (e.g., A3B or {g}A3B), split on lookahead for letters or brace
            $subtokens = preg_split('/(?=[A-Ga-g\{])/', $part);
            if ($subtokens === false) $subtokens = [$part];
            foreach ($subtokens as $st) {
                $st = trim($st);
                if ($st === '') continue;
                // Normalize by stripping duration digits and bar separators for lookup
                $norm = preg_replace('/\d+/', '', $st);
                $norm = trim($norm, "|\/\n\r\t ");
                $cannt = $this->dict->convertAbcToCannt($st);
                if ($cannt === null) $cannt = $this->dict->convertAbcToCannt($norm);
                if ($cannt === null) {
                    // Try small heuristics: if token has braces like {g}A -> try full without duration
                    if (preg_match('/^\{[^}]+\}[A-Ga-g]$/', $norm)) {
                        $cannt = $this->dict->convertAbcToCannt($norm);
                    }
                }
                if ($cannt === null) {
                    // Fallback: wrap the normalized token
                    $cannt = '[' . ($norm === '' ? $st : $norm) . ']';
                }
                $out[] = $cannt;
            }
        }
        // Ensure we always return a non-empty string
        if (empty($out)) {
            $safe = trim(preg_replace('/\s+/', ' ', $noteBody));
            if ($safe === '') return '[?]';
            return '[' . $safe . ']';
        }
        return implode(' ', $out);
    }
}
