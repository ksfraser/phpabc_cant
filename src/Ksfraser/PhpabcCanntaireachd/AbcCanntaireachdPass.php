<?php
namespace Ksfraser\PhpabcCanntaireachd;
class AbcCanntaireachdPass {
    private $dict;
    public function __construct($dict) { $this->dict = $dict; }
    public function process(array $lines): array {
        $canntDiff = [];
        $output = AbcProcessor::validateCanntaireachd($lines, $canntDiff);

        // Generate canntaireachd w: lines for bagpipe voices
        $output = $this->generateCanntaireachdLyrics($output);

        // Remove TIMING markers from the output while keeping logs
        foreach ($output as &$line) {
            if (strpos($line, 'TIMING') !== false) {
                // Log the timing issue but do not insert it into the tune
                error_log("Timing issue detected: $line");
                $line = str_replace(' TIMING', '', $line);
            }
        }

        return ['lines' => $output, 'canntDiff' => $canntDiff];
    }
    
    private function generateCanntaireachdLyrics($lines) {
        $canntGenerator = new CanntGenerator($this->dict);

        // Log dictionary contents for debugging
        error_log("Dictionary contents: " . print_r($this->dict, true));

        $output = [];
        $inBagpipeVoice = false;
        $hasLyrics = false;

        foreach ($lines as $line) {
            if (preg_match('/^V:Bagpipes/', $line)) {
                $inBagpipeVoice = true;
                $hasLyrics = false;
                $output[] = $line;
            } elseif (preg_match('/^V:/', $line)) {
                $inBagpipeVoice = false;
                $hasLyrics = false;
                $output[] = $line;
            } elseif ($inBagpipeVoice) {
                if (preg_match('/^w:/', $line)) {
                    // Already has lyrics, don't generate
                    $hasLyrics = true;
                    $output[] = $line;
                } elseif (!$hasLyrics && $this->isMusicLine($line)) {
                    // Remove voice tag before generating canntaireachd
                    $musicLine = preg_replace('/^\[V:[^\]]+\]/', '', $line);

                    // Log input to generateForNotes for debugging
                    error_log("INPUT to generateForNotes: $musicLine");

                    // Use the Trie to convert the music line to canntaireachd
                    $canntText = $canntGenerator->generateForNotes($musicLine);

                    if ($canntText && $canntText !== '[?]') {
                        $output[] = $line; // Add the music line first
                        $output[] = 'w: ' . $canntText; // Add the w: line below
                    } else {
                        $output[] = $line;
                    }
                } else {
                    $output[] = $line;
                }
            } else {
                $output[] = $line;
            }
        }

        return $output;
    }

    private function isMusicLine($line) {
        // A music line is one that is not a header (V:, X:, T:, etc.), comment (%), or empty
        return !preg_match('/^[VXT:]/', $line) && trim($line) !== '' && !preg_match('/^%/', $line);
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
        $result = '';
        $node = $this->root;
        $buffer = '';

        for ($i = 0; $i < strlen($input); $i++) {
            $char = $input[$i];
            error_log("Processing character: $char"); // Log each character being processed

            if (isset($node->children[$char])) {
                $buffer .= $char;
                $node = $node->children[$char];
                error_log("Current buffer: $buffer"); // Log the current buffer

                if ($node->isEndOfWord) {
                    error_log("Match found for buffer: $buffer -> Replacement: {$node->replacement}"); // Log the match
                    $result .= $node->replacement;
                    $node = $this->root;
                    $buffer = '';
                }
            } else {
                $result .= $buffer . $char;
                $node = $this->root;
                $buffer = '';
            }
        }

        return $result . $buffer;
    }
}
