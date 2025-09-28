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
        error_log("generateForNotes input: $noteBody"); // Log input
        if ($noteBody === '') return '[?]';
        
        // Strip voice prefixes like [V:Bagpipes] from the beginning of the line
        $noteBody = preg_replace('/^\[V:[^\]]*\]/', '', $noteBody);
        $noteBody = trim($noteBody);
        
        // Attempt to split on whitespace first
        $parts = preg_split('/\s+/', $noteBody);
        $out = [];
        foreach ($parts as $part) {
            $part = trim($part);
            if ($part === '' || $part === '|' ) continue;
            error_log("Processing part: $part"); // Log each part
            // Process the part by finding longest matching tokens
            $remaining = $part;
            while ($remaining !== '') {
                $found = false;
                for ($len = strlen($remaining); $len > 0; $len--) {
                    $token = substr($remaining, 0, $len);
                    $norm = preg_replace('/\d+/', '', $token);
                    $cannt = $this->dict->convertAbcToCannt($token);
                    if ($cannt === null) $cannt = $this->dict->convertAbcToCannt($norm);
                    if ($cannt !== null) {
                        error_log("Matched token: $token -> Cannt: $cannt"); // Log matched token
                        $out[] = $cannt;
                        $remaining = substr($remaining, $len);
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    // Fallback: wrap the normalized token
                    $norm = preg_replace('/\d+/', '', $remaining);
                    $cannt = '[' . ($norm === '' ? $remaining : $norm) . ']';
                    error_log("Unmatched token: $remaining -> Fallback: $cannt"); // Log unmatched token
                    $out[] = $cannt;
                    $remaining = '';
                }
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
        return $result;
    }
}
