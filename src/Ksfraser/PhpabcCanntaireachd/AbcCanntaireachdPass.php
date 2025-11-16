<?php 

namespace Ksfraser\PhpabcCanntaireachd;

use Ksfraser\PhpabcCanntaireachd\Log\FlowLog;
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
            $trimmed = trim($line);
            // Check for voice definition (both standalone and inline)
            if (preg_match('/^V:([^\s]+)/', $trimmed, $matches)) {
                $currentVoice = $matches[1];
                $translatedOutput[] = $line;
            } elseif (preg_match('/^\[V:([^\]]+)\]/', $trimmed, $matches)) {
                // Handle inline voice markers like [V:M]
                $currentVoice = $matches[1];
                error_log("AbcCanntaireachdPass: Found inline voice marker [V:$currentVoice]");
                $translatedOutput[] = $line;
                if ($this->shouldAddCanntaireachd($currentVoice)) {
                    error_log("AbcCanntaireachdPass: shouldAddCanntaireachd returned TRUE for $currentVoice");
                    $canntText = $generator->generateForNotes($line);
                    error_log("AbcCanntaireachdPass: generated canntText: '$canntText'");
                    if ($canntText && $canntText !== '[?]') {
                        $translatedOutput[] = 'w: ' . $canntText;
                        $canntDiff[] = [
                            'line' => $line,
                            'generated' => $canntText
                        ];
                    }
                } else {
                    error_log("AbcCanntaireachdPass: shouldAddCanntaireachd returned FALSE for $currentVoice");
                }
            } elseif ($this->isMusicLine($line) && $this->shouldAddCanntaireachd($currentVoice)) {
                $translatedOutput[] = $line;
                error_log("AbcCanntaireachdPass processing line: '$line'");
                $canntText = $generator->generateForNotes($line);
                error_log("AbcCanntaireachdPass generated cannt: '$canntText'");
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
        
        return ['lines' => $translatedOutput, 'canntDiff' => $canntDiff];
    }
    
    /**
     * Determines if canntaireachd should be added for this voice.
     * @param string|null $voice The voice ID
     * @return bool True if canntaireachd should be added
     */
    private function shouldAddCanntaireachd($voice): bool {
        if (!$voice) {
            return false;
        }
        $voiceLower = strtolower($voice);
        // Only add canntaireachd to Bagpipes voices, not Melody
        // Match "Bagpipes", "Bagpipe", "Pipes", or "Pipe" but not just "P"
        return in_array($voiceLower, ['bagpipes', 'pipes', 'bagpipe', 'pipe']);
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
        if ($dictionary instanceof TokenDictionary) {
            $this->dict = $dictionary;
        } elseif ($dictionary === null) {
            // Auto-create TokenDictionary if null
            $this->dict = new TokenDictionary();
        } else {
            $this->dict = $dictionary;
        }
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
            
            // Skip bar lines and whitespace
            if (preg_match("/^[\s|]+/", $input, $m)) {
                if (strpos($m[0], '|') !== false) {
                    $result .= ' | '; // Add bar line with spaces
                }
                $input = substr($input, strlen($m[0]));
                continue;
            }
            
            // First try regex parsing for complex ABC notes
            if (preg_match("/^([_=^]?)([a-gA-GzZ])([,']*)([0-9]+\/?[0-9]*|\/{1,}|)/", $input, $m)) {
                $noteStr = $m[0];
                $baseNote = $m[2]; // The actual note letter (A-G, a-g, z, Z)
                // Look up the base note in the dictionary
                $canntToken = $this->dict->convertAbcToCannt($baseNote);
                if ($canntToken !== null) {
                    if ($result !== '' && !preg_match('/[\s|]$/', $result)) {
                        $result .= ' '; // Add space before syllable
                    }
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
                            if ($result !== '' && !preg_match('/[\s|]$/', $result)) {
                                $result .= ' '; // Add space before syllable
                            }
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
                // Skip unknown character (numbers, punctuation, etc.)
                $input = substr($input, 1);
            }
        }
        return trim($result);
    }
}
