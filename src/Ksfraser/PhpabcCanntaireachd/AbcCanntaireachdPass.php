<?php 

namespace Ksfraser\PhpabcCanntaireachd;
/**
 * Class AbcCanntaireachdPass
 *
 * Processes ABC lines and generates canntaireachd lyrics using the translation pipeline.
 * Follows SRP by handling only canntaireachd generation in the processing pipeline.
 * Uses DI for TokenDictionary injection.
 *
 * @requirement FR2, FR8
 * @uml
 * @startuml
 * class AbcCanntaireachdPass {
 *   - dict: TokenDictionary
 *   + __construct(dict: TokenDictionary)
 *   + process(lines: array): array
 *   + isMusicLine(line: string): bool
 * }
 * AbcCanntaireachdPass --> CanntGenerator : uses
 * CanntGenerator --> TokenDictionary : uses
 * TokenDictionary --> Trie : uses
 * @enduml
 *
 * @sequence
 * @startuml
 * participant User
 * participant AbcCanntaireachdPass
 * participant CanntGenerator
 * participant TokenDictionary
 * participant Trie
 * User -> AbcCanntaireachdPass: process(lines)
 * AbcCanntaireachdPass -> CanntGenerator: generateForNotes(line)
 * CanntGenerator -> TokenDictionary: searchCannt(noteBody)
 * TokenDictionary -> Trie: search(input)
 * Trie --> TokenDictionary: result
 * TokenDictionary --> CanntGenerator: canntText
 * CanntGenerator --> AbcCanntaireachdPass: canntText
 * AbcCanntaireachdPass --> User: output
 * @enduml
 *
 * @flowchart
 * @startuml
 * start
 * :Receive ABC lines;
 * :Validate lines;
 * :For each music line in Bagpipes voice;
 * :Generate canntaireachd using Trie;
 * :Add w: line below music line;
 * :Output processed lines;
 * stop
 * @enduml
 */
require_once __DIR__ . '/Trie.php';

class AbcCanntaireachdPass {
    private $dict;
    /**
     * Constructor for AbcCanntaireachdPass.
     * Uses DI to inject TokenDictionary.
     *
     * @param TokenDictionary $dict The token dictionary for translations.
     * @requirement FR7
     */
    public function __construct($dict) {
        if ($dict instanceof TokenDictionary) {
            $this->dict = $dict;
        } else {
            $td = new TokenDictionary();
            $td->prepopulate($dict);
            $this->dict = $td;
        }
    }
    /**
     * Processes the array of ABC lines to generate canntaireachd for Bagpipes voices.
     * Follows OCP by allowing extension without modification.
     *
     * @param array $lines The input ABC lines.
     * @return array The processed lines with canntaireachd added, and diff log.
     * @requirement FR2, FR8
     */
    public function process(array $lines): array {
        $canntDiff = [];
        $output = $lines;

        // Use CanntGenerator for translation
        $generator = new CanntGenerator($this->dict);
        $translatedOutput = [];
        $currentVoice = null;
        foreach ($output as $line) {
            // Check for voice definition
            if (preg_match('/^V:([^\s]+)/', trim($line), $matches)) {
                $currentVoice = $matches[1];
                $translatedOutput[] = $line;
            } elseif ($this->isMusicLine($line) && $currentVoice === 'Bagpipes') {
                $translatedOutput[] = $line;
                $canntText = $generator->generateForNotes($line);
                if ($canntText && $canntText !== '[?]') {
                    $translatedOutput[] = 'w: ' . $canntText;
                    $canntDiff[] = [
                        'line' => $line,
                        'generated' => $canntText
                    ];
                }
            } else {
                $translatedOutput[] = $line;
            }
        }
        // Remove TIMING markers from the output while keeping logs
        foreach ($translatedOutput as &$line) {
            if (strpos($line, 'TIMING') !== false) {
                error_log("Timing issue detected: $line");
                $line = str_replace(' TIMING', '', $line);
            }
        }
        return ['lines' => $translatedOutput, 'canntDiff' => $canntDiff];
    }
    
    // Lyrics generation now handled by LyricsGenerator class

    /**
     * Determines if a line is a music line (contains notes or bars).
     * Follows SRP by handling only line type detection.
     *
     * @param string $line The line to check.
     * @return bool True if it's a music line.
     */
    private function isMusicLine($line) {
        $trimmed = trim($line);
        // Exclude empty lines and comments
        if ($trimmed === '' || preg_match('/^%/', $trimmed)) {
            return false;
        }
        // Exclude header lines (X:, T:, M:, L:, K:, etc.)
        if (preg_match('/^[A-Z]:/', $trimmed)) {
            return false;
        }
        // Exclude voice definition lines (V: at start)
        if (preg_match('/^V:/', $trimmed)) {
            return false;
        }
        // Exclude lyrics lines (w: at start)
        if (preg_match('/^w:/', $trimmed)) {
            return false;
        }
        // Accept lines starting with [V:...] as music lines
        if (preg_match('/^\[V:[^\]]+\]/', $trimmed)) {
            return true;
        }
        // Accept lines with actual notes (letters A-G, a-g, or rests z Z)
        if (preg_match('/[A-Ga-gzZ]/', $trimmed)) {
            return true;
        }
        return false;
    }
}

class CanntGenerator {
    private $dict;

    public function __construct($dictionary) {
        $this->dict = $dictionary;
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
        
        // Use proper ABC tokenization instead of flawed Trie search
        $result = $this->tokenizeAndConvert($noteBody);
        error_log("generateForNotes output: $result"); // Log output
        file_put_contents($logFile, "generateForNotes output: $result\n", FILE_APPEND); // Log output to file
        return $result ?: '[?]';
    }

    /**
     * Tokenize ABC input and convert to canntaireachd using longest-match-first logic.
     * @param string $input The ABC music line.
     * @return string The canntaireachd text.
     */
    private function tokenizeAndConvert(string $input): string {
        $result = '';
        $dictKeys = array_keys($this->dict->getAllTokens());
        // Sort keys by length descending for longest-match-first
        usort($dictKeys, function($a, $b) { return strlen($b) - strlen($a); });
        
        while (strlen($input) > 0) {
            $matched = false;
            
            // First try regex parsing for complex ABC notes
            if (preg_match("/^([_=^]?)([a-gA-GzZ])([,']*)([0-9]+\/?[0-9]*|\/{1,}|)/", $input, $m)) {
                $noteStr = $m[0];
                $baseNote = $m[2]; // The actual note letter (A-G, a-g, z, Z)
                // Look up the base note in the dictionary
                $canntToken = $this->dict->convertAbcToCannt($baseNote);
                if ($canntToken !== null) {
                    $result .= $canntToken;
                    $input = substr($input, strlen($noteStr));
                    $matched = true;
                    error_log("Regex matched '$noteStr', base note '$baseNote' -> '$canntToken'");
                }
            }
            
            // If regex didn't match, try dictionary keys
            if (!$matched) {
                foreach ($dictKeys as $key) {
                    if ($key === '') continue;
                    if (strpos($input, $key) === 0) {
                        // Found a match, convert to canntaireachd
                        $canntToken = $this->dict->convertAbcToCannt($key);
                        if ($canntToken !== null) {
                            $result .= $canntToken;
                            error_log("Matched '$key' -> '$canntToken'");
                        } else {
                            $result .= '[' . $key . ']'; // Unknown token
                            error_log("Matched '$key' but no cannt_token");
                        }
                        $input = substr($input, strlen($key));
                        $matched = true;
                        break;
                    }
                }
            }
            
            if (!$matched) {
                // Skip unknown character
                $char = substr($input, 0, 1);
                $result .= '[' . $char . ']';
                $input = substr($input, 1);
                error_log("Unknown char '$char'");
            }
            // Trim leading whitespace
            $input = ltrim($input);
        }
        return $result;
    }
}
