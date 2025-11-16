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
     * Constructor for CanntGenerator.
     * Loads dictionary from file if not provided.
     * Uses DI for TokenDictionary.
     *
     * @param TokenDictionary|null $dict The token dictionary.
     * @requirement FR7
     */
    public function __construct(?TokenDictionary $dict = null) {
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
        
        // Debug: check if dictionary has tokens
        $allTokens = $this->dict->getAllTokens();
        error_log("CanntGenerator dict has " . count($allTokens) . " tokens");
        if (count($allTokens) > 0) {
            $sample = array_slice($allTokens, 0, 3, true);
            error_log("Sample tokens: " . json_encode($sample));
        }
        
        // Use proper ABC tokenization instead of flawed Trie search
        $result = $this->tokenizeAndConvert($noteBody);
        error_log("generateForNotes output: $result"); // Log output
        return $result ?: '[?]';
    }

    /**
     * Tokenize ABC input and convert to canntaireachd using longest-match-first logic.
     * @param string $input The ABC music line.
     * @return string The canntaireachd text.
     */
    private function tokenizeAndConvert(string $input): string {
        error_log("tokenizeAndConvert called with input: '$input'");
        $result = '';
        
        // Use regex to tokenize ABC notation properly
        // This regex matches: grace notes, accidentals, notes, octaves, lengths, rests, barlines, spaces
        $pattern = '/(\{[^{}]+\})|([_=^])|([a-gA-GzZ])|([,\']*)|(\d+\/?\d*)|(\|)|(\s+)/';
        
        if (preg_match_all($pattern, $input, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE)) {
            $tokens = [];
            $currentToken = '';
            $i = 0;
            
            while ($i < count($matches)) {
                $match = $matches[$i];
                $token = $match[0][0];
                
                // Handle grace notes
                if (preg_match('/^\{/', $token)) {
                    $currentToken .= $token;
                    $i++;
                    continue;
                }
                
                // Handle accidentals
                if (in_array($token, ['_', '=', '^'])) {
                    $currentToken .= $token;
                    $i++;
                    continue;
                }
                
                // Handle notes
                if (preg_match('/^[a-gA-GzZ]$/', $token)) {
                    $currentToken .= $token;
                    $i++;
                    
                    // Look ahead for octave marks
                    while ($i < count($matches) && preg_match('/^[,|\']+$/', $matches[$i][0][0])) {
                        $currentToken .= $matches[$i][0][0];
                        $i++;
                    }
                    
                    // Look ahead for length
                    if ($i < count($matches) && preg_match('/^\d+\/?\d*$/', $matches[$i][0][0])) {
                        // Skip length - don't add to token
                        $i++;
                    }
                    
                    // Now we have a complete note token, look it up
                    error_log("tokenizeAndConvert: looking up token '$currentToken'");
                    $cannt = $this->dict->convertAbcToCannt($currentToken);
                    error_log("tokenizeAndConvert: cannt result: " . var_export($cannt, true));
                    if ($cannt !== null) {
                        $result .= $cannt . ' ';
                    } else {
                        $result .= '[' . $currentToken . '] ';
                    }
                    $currentToken = '';
                    continue;
                }
                
                // Handle barlines
                if ($token === '|') {
                    $result .= '| ';
                    $i++;
                    continue;
                }
                
                // Skip lengths (already handled above), spaces, and other elements
                $i++;
            }
        }
        
        return rtrim($result, ' ');
    }
}
