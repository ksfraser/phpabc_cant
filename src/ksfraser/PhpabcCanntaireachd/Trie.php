<?php

declare(strict_types=1);

namespace ksfraser\PhpabcCanntaireachd;

use ksfraser\PhpabcCanntaireachd\Log\DebugLog;

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
        DebugLog::log("Added token: $token -> Replacement: $replacement");
    }

    public function search($input) {
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
                DebugLog::log("Token '$token' matched -> '{$node->replacement}'");
            } else {
                // Unknown token, wrap in brackets
                $result .= '[' . $token . ']';
                DebugLog::log("Token '$token' not found, wrapped as '[$token]'");
            }
        }

        return $result;
    }
}
?>