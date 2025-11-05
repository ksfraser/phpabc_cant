<?php
namespace Ksfraser\PhpabcCanntaireachd;

/**
 * Trie data structure for efficient pattern matching and token replacement.
 * Follows SRP by handling only Trie operations.
 *
 * @requirement FR8
 * @uml
 * @startuml
 * class TrieNode {
 *   + children: array
 *   + isEndOfWord: bool
 *   + replacement: string|null
 * }
 * class Trie {
 *   - root: TrieNode
 *   + __construct()
 *   + insert(pattern: string, replacement: string)
 *   + addToken(token: string, replacement: string)
 *   + search(input: string): string
 * }
 * Trie --> TrieNode : uses
 * @enduml
 */

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

    public function search($input) {
        $logFile = __DIR__ . '/trie_debug.log'; // Define log file path
        $result = '';
        $tokens = preg_split('/(\s+)/', $input, -1, PREG_SPLIT_DELIM_CAPTURE);

        foreach ($tokens as $token) {
            if (trim($token) === '') {
                // This is whitespace, add it as is
                $result .= $token;
                continue;
            }

            // Try to find the token in the Trie
            $node = $this->root;
            $found = false;
            for ($i = 0; $i < strlen($token); $i++) {
                $char = $token[$i];
                if (isset($node->children[$char])) {
                    $node = $node->children[$char];
                    if ($node->isEndOfWord) {
                        $found = true;
                        break;
                    }
                } else {
                    break;
                }
            }

            if ($found && $node->isEndOfWord) {
                $result .= $node->replacement;
                error_log("Token '$token' matched -> '{$node->replacement}'");
                file_put_contents($logFile, "Token '$token' matched -> '{$node->replacement}'\n", FILE_APPEND);
            } else {
                // Unknown token, wrap in brackets
                $result .= '[' . $token . ']';
                error_log("Token '$token' not found, wrapped as '[$token]'");
                file_put_contents($logFile, "Token '$token' not found, wrapped as '[$token]'\n", FILE_APPEND);
            }
        }

        return $result;
    }
}
?>