<?php 

namespace Ksfraser\PhpabcCanntaireachd;
/**
 * Class AbcCanntaireachdPass
 *
 * Processes ABC lines and generates canntaireachd lyrics using the translation pipeline.
 *
 * @uml
 * @startuml
 * class AbcCanntaireachdPass {
 *   - dict: TokenDictionary
 *   + process(lines: array): array
 * }
 * AbcCanntaireachdPass --> BagpipeAbcToCanntTranslator : uses
 * BagpipeAbcToCanntTranslator --> TokenDictionary : uses
 * @enduml
 *
 * @sequence
 * @startuml
 * participant User
 * participant AbcCanntaireachdPass
 * participant BagpipeAbcToCanntTranslator
 * participant TokenDictionary
 * User -> AbcCanntaireachdPass: process(lines)
 * AbcCanntaireachdPass -> BagpipeAbcToCanntTranslator: translate(note)
 * BagpipeAbcToCanntTranslator -> TokenDictionary: convertAbcToCannt(token)
 * TokenDictionary --> BagpipeAbcToCanntTranslator: canntToken
 * BagpipeAbcToCanntTranslator --> AbcCanntaireachdPass: canntToken
 * AbcCanntaireachdPass --> User: output
 * @enduml
 *
 * @flowchart
 * @startuml
 * start
 * :Receive ABC lines;
 * :Validate lines;
 * :For each music line, split into tokens;
 * :For each token, create AbcNote and translate;
 * :Collect canntaireachd tokens;
 * :Output original and w: lines;
 * stop
 * @enduml
 */
class AbcCanntaireachdPass {
    private $dict;
    /**
     * @param array $dict
     */
    public function __construct($dict) { $this->dict = $dict; }
    /**
     * @param array $lines
     * @return array
     */
    public function process(array $lines): array {
        $canntDiff = [];
        $output = AbcProcessor::validateCanntaireachd($lines, $canntDiff);

        // Use BagpipeAbcToCanntTranslator for translation
        $translator = new \Ksfraser\PhpabcCanntaireachd\BagpipeAbcToCanntTranslator($this->dict);
        $translatedOutput = [];
        foreach ($output as $line) {
            // Only translate music lines (not headers/comments)
            if ($this->isMusicLine($line)) {
                $musicTokens = preg_split('/\s+/', trim($line));
                $canntTokens = array_map(function($token) use ($translator) {
                    $note = new \Ksfraser\PhpabcCanntaireachd\AbcNote($token);
                    return $translator->translate($note);
                }, $musicTokens);
                $canntTextAligned = trim(implode(' ', $canntTokens));
                $translatedOutput[] = $line;
                if ($canntTextAligned && $canntTextAligned !== '[?]') {
                    $translatedOutput[] = 'w: ' . $canntTextAligned;
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
        // Accept lines starting with [V:...] as music lines
        if (preg_match('/^\[V:[^\]]+\]/', $trimmed)) {
            return true;
        }
        // Accept lines with music notation (notes, bars, etc.)
        if (preg_match('/[A-Ga-gzZ\|\[\]\{\}]/', $trimmed)) {
            return true;
        }
        return false;
    }
}

class CanntGenerator {
    private $trie;

    public function __construct($dictionary) {
        $this->trie = new Trie();
        foreach ($dictionary as $pattern => $replacement) {
            $this->trie->insert($pattern, $replacement);
        }
    }

    public function generateForNotes($line) {
        return $this->trie->search($line);
    }
}

class TrieNode {
    public $children = [];
    public $isEndOfWord = false;
    public $replacement = null;
}

class Trie {
    private $root;

    public function __construct() {
        $this->root = new TrieNode();
    }

    public function insert($pattern, $replacement) {
        $node = $this->root;
        for ($i = 0; $i < strlen($pattern); $i++) {
            $char = $pattern[$i];
            if (!isset($node->children[$char])) {
                $node->children[$char] = new TrieNode();
            }
            $node = $node->children[$char];
        }
        $node->isEndOfWord = true;
        $node->replacement = $replacement;
    }

    public function search($input) {
        $logFile = __DIR__ . '/trie_debug.log'; // Define log file path
        $result = '';
        $node = $this->root;
        $buffer = '';

        for ($i = 0; $i < strlen($input); $i++) {
            $char = $input[$i];
            error_log("Processing character: $char"); // Log each character being processed
            file_put_contents($logFile, "Processing character: $char\n", FILE_APPEND); // Log each character being processed to file

            if (isset($node->children[$char])) {
                $buffer .= $char;
                $node = $node->children[$char];
                error_log("Current buffer: $buffer"); // Log the current buffer
                file_put_contents($logFile, "Current buffer: $buffer\n", FILE_APPEND); // Log the current buffer to file

                if ($node->isEndOfWord) {
                    error_log("Match found for buffer: $buffer -> Replacement: {$node->replacement}"); // Log the match
                    file_put_contents($logFile, "Match found for buffer: $buffer -> Replacement: {$node->replacement}\n", FILE_APPEND); // Log the match to file
                    $result .= $node->replacement;
                    $node = $this->root;
                    $buffer = '';
                }
            } else {
                if ($buffer !== '') {
                    error_log("No match for buffer: $buffer"); // Log no match
                    file_put_contents($logFile, "No match for buffer: $buffer\n", FILE_APPEND); // Log no match to file
                }
                $result .= $buffer . $char;
                $node = $this->root;
                $buffer = '';
            }
        }

        if ($buffer !== '') {
            error_log("Remaining buffer not matched: $buffer"); // Log remaining buffer
            file_put_contents($logFile, "Remaining buffer not matched: $buffer\n", FILE_APPEND); // Log remaining buffer to file
        }

        return $result . $buffer;
    }

    public function addToken($token, $replacement) {
        $logFile = __DIR__ . '/trie_debug.log'; // Define log file path
        $node = $this->root;
        for ($i = 0; $i < strlen($token); $i++) {
            $char = $token[$i];
            if (!isset($node->children[$char])) {
                $node->children[$char] = new TrieNode();
            }
            $node = $node->children[$char];
        }
        $node->isEndOfWord = true;
        $node->replacement = $replacement;
        error_log("Added token: $token -> Replacement: $replacement"); // Log token addition
        file_put_contents($logFile, "Added token: $token -> Replacement: $replacement\n", FILE_APPEND); // Log token addition to file
    }
}
