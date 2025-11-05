<?php
namespace Ksfraser\PhpabcCanntaireachd;

use Ksfraser\PhpabcCanntaireachd\Log\CanntLog;
/**
 * Class CanntGenerator
 *
 * Generates canntaireachd lyrics for ABC note bodies using a token dictionary.
 * Supports legacy dictionary loading and robust token matching for bagpipe voice conversion.
 * Follows SRP by focusing solely on canntaireachd generation.
 * Uses DI for TokenDictionary.
 *
 * @requirement FR2, FR8
 * @uml
 * @startuml
 * class CanntGenerator {
 *   - dict: TokenDictionary
 *   + __construct(dict: TokenDictionary)
 *   + generateForNotes(noteBody: string): string
 * }
 * CanntGenerator --> TokenDictionary : uses
 * @enduml
 *
 * @sequence
 * @startuml
 * participant User
 * participant CanntGenerator
 * participant TokenDictionary
 * User -> CanntGenerator: generateForNotes(noteBody)
 * CanntGenerator -> TokenDictionary: searchCannt(noteBody)
 * TokenDictionary --> CanntGenerator: canntText
 * CanntGenerator --> User: canntText
 * @enduml
 */

class CanntGenerator {
    protected $dict;

    /**
<<<<<<< HEAD
     * Constructor for CanntGenerator.
     * Loads dictionary from file if not provided.
     * Uses DI for TokenDictionary.
     *
     * @param TokenDictionary|null $dict The token dictionary.
     * @requirement FR7
     */
    public function __construct(?TokenDictionary $dict = null) {
=======
     * Summary of __construct
     * @param TokenDictionary $dict
     * @return void
     */
    public function __construct($dict = null) {
>>>>>>> 4113fb97ff103f0af8d41462ff6994831d290ccf
        if ($dict !== null) {
            $this->dict = $dict;
            return;
        }
        // Use TokenDictionary constructor which automatically loads abc_dict.php
        $this->dict = new TokenDictionary();
    }

    /**
     * Generates canntaireachd lyrics for the given ABC note body.
     * Uses proper ABC tokenization with longest-match-first logic.
     *
     * @param string $noteBody The ABC music line.
     * @return string The canntaireachd text.
     * @requirement FR2, FR8
     */
    public function generateForNotes(string $noteBody): string {
        $noteBody = trim($noteBody);
    CanntLog::log("generateForNotes input: $noteBody", true);
        if ($noteBody === '') return '[?]';
        
        // Strip voice prefixes like [V:Bagpipes] from the beginning of the line
        $noteBody = preg_replace('/^\[V:[^\]]*\]/', '', $noteBody);
        $noteBody = trim($noteBody);
        
<<<<<<< HEAD
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
            foreach ($dictKeys as $key) {
                if ($key === '') continue;
                if (strpos($input, $key) === 0) {
                    // Found a match, convert to canntaireachd
                    $canntToken = $this->dict->convertAbcToCannt($key);
                    if ($canntToken !== null) {
                        $result .= $canntToken;
                    } else {
                        $result .= '[' . $key . ']'; // Unknown token
                    }
                    $input = substr($input, strlen($key));
                    $matched = true;
                    break;
                }
            }
            if (!$matched) {
                // Fallback: try to parse a single note using regex (like AbcNoteTokenizer)
                if (preg_match("/^([_=^]?)([a-gA-GzZ])([,']*)([0-9]+\/?[0-9]*|\/{1,}|)([^\s]*)/", $input, $m)) {
                    $noteStr = $m[0];
                    // For complex notes not in dictionary, wrap in brackets
                    $result .= '[' . $noteStr . ']';
                    $input = substr($input, strlen($noteStr));
                } else {
                    // Skip unknown character
                    $result .= '[' . substr($input, 0, 1) . ']';
                    $input = substr($input, 1);
                }
            }
            // Trim leading whitespace
            $input = ltrim($input);
        }
=======
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
>>>>>>> 4113fb97ff103f0af8d41462ff6994831d290ccf
        return $result;
    }
}
