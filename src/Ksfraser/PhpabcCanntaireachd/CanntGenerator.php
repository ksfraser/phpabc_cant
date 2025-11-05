<?php
namespace Ksfraser\PhpabcCanntaireachd;
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

    /**
     * Generates canntaireachd lyrics for the given ABC note body.
     * Uses Trie for efficient pattern matching.
     *
     * @param string $noteBody The ABC music line.
     * @return string The canntaireachd text.
     * @requirement FR2, FR8
     */
    public function generateForNotes(string $noteBody): string {
        $logFile = __DIR__ . '/cannt_generator_debug.log'; // Define log file path
        $noteBody = trim($noteBody);
        error_log("generateForNotes input: $noteBody"); // Log input
        file_put_contents($logFile, "generateForNotes input: $noteBody\n", FILE_APPEND); // Log input to file
        if ($noteBody === '') return '[?]';
        
        // Strip voice prefixes like [V:Bagpipes] from the beginning of the line
        $noteBody = preg_replace('/^\[V:[^\]]*\]/', '', $noteBody);
        $noteBody = trim($noteBody);
        
        // Use Trie to process the entire line
        $result = $this->dict->searchCannt($noteBody);
        error_log("generateForNotes output: $result"); // Log output
        file_put_contents($logFile, "generateForNotes output: $result\n", FILE_APPEND); // Log output to file
        return $result ?: '[?]';
    }
}
